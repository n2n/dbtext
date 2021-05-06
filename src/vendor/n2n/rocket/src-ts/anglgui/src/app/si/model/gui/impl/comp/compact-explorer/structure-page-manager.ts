import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiPage } from '../../model/si-page';
import { SiEntry, SiEntryState } from 'src/app/si/model/content/si-entry';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { Subscription, BehaviorSubject, Observable } from 'rxjs';
import { IllegalArgumentError } from 'src/app/si/util/illegal-argument-error';
import { SiPageCollection, SiEntryPosition } from '../../model/si-page-collection';
import { IllegalStateError } from 'src/app/util/err/illegal-state-error';
import { SiProp } from 'src/app/si/model/meta/si-prop';
import { SiEntryIdentifier } from 'src/app/si/model/content/si-entry-qualifier';

export class StructurePage {
	private _structureEntries = new Array<StructureEntry>();

	constructor(readonly siPage: SiPage, public offsetHeight: number|null) {
	}

	get loaded(): boolean {
		return !!this.siPage.entries;
	}

	isEmpty(): boolean {
		return this._structureEntries.length === 0;
	}

	clear() {
		let structureEntry: StructureEntry;
		while (structureEntry = this._structureEntries.pop()) {
			structureEntry.clear();
		}
	}

	get structureEntries(): Array<StructureEntry> {
		return this._structureEntries;
	}

	appendStructureEntry(structureEntry: StructureEntry) {
		this._structureEntries.push(structureEntry);
	}

	placeStructureEntryAt(index: number, structureEntry: StructureEntry) {
		if (this._structureEntries.length < index) {
			throw new IllegalArgumentError('Index out of bounds: ' + index + '; current page size: '
					+ this._structureEntries.length);
		}

		if (this._structureEntries[index]) {
			this._structureEntries[index].clear();
		}

		this._structureEntries[index] = structureEntry;
	}

	replaceStructureEntry(structureEntry: StructureEntry, replacementStructureEntry: StructureEntry): boolean {
		const i = this._structureEntries.indexOf(structureEntry);
		if (i > -1) {
			this.placeStructureEntryAt(i, replacementStructureEntry);
			return true;
		}

		return false;
	}


	// putStructureEntry(siEntry: SiEntry, structureEntry: StructureEntry) {
	// 	this.structureEntriesMap.set(siEntry, structureEntry);
	// }

	// getStructureEntryOf(siEntry: SiEntry) {
	// 	if (this.structureEntriesMap.has(siEntry)) {
	// 		return this.structureEntriesMap.get(siEntry);
	// 	}

	// 	throw new IllegalStateError('No StructureEntry available for ' + siEntry.identifier.toString());
	// }
}

export class StructureEntry {
	private subscription: Subscription;

	constructor(readonly siEntry: SiEntry, public fieldUiStructures: Array<UiStructure>,
			public controlUiContents: Array<UiContent>,
			private replacementCallback: (replacementEntry: SiEntry) => any) {

		this.subscription = siEntry.state$.subscribe((state) => {
			switch (state) {
				case SiEntryState.REPLACED:
					this.replacementCallback(siEntry.replacementEntry);
					this.clear();
					break;
				case SiEntryState.REMOVED:
					this.clearControls();
					this.clearSubscription();
			}
		});
	}

	clear() {
		this.clearFields();
		this.clearControls();
		this.clearSubscription();
	}

	private clearFields() {
		for (const uiStructure of this.fieldUiStructures) {
			uiStructure.dispose();
		}
		this.fieldUiStructures = [];
	}

	private clearControls() {
		this.controlUiContents = [];
	}

	private clearSubscription() {
		if (!this.subscription) {
			return;
		}

		this.subscription.unsubscribe();
		this.subscription = null;
	}
}

export class StructurePageManager {

	private pagesMap = new Map<number, { structurePage: StructurePage, subscription: Subscription }>();
	private uiStructuresSubject = new BehaviorSubject<UiStructure[]>([]);

	constructor(private uiStructure: UiStructure, private siPageCollection: SiPageCollection) {
	}

	get quickSearchStr(): string|null {
		return this.siPageCollection.quickSearchStr;
	}

	private ensureDeclared() {
		if (this.declarationRequired) {
			throw new IllegalStateError('Declaration required.');
		}
	}

	getSiProps(): SiProp[] {
		this.ensureDeclared();

		return this.siPageCollection.declaration.getBasicTypeDeclaration().getSiProps();
	}

	get declarationRequired(): boolean {
		return !this.siPageCollection.declared;
	}

	get loadingRequired(): boolean {
		return this.declarationRequired || (this.pagesMap.size === 0 && this.siPageCollection.size > 0);
	}

	get pages(): StructurePage[] {
		return Array.from(this.pagesMap.values()).map(v => v.structurePage).sort((aPage, bPage) => {
			return aPage.siPage.no - bPage.siPage.no;
		});
	}

	get lastPageNo(): number|null {
		if (this.pagesMap.size === 0) {
			return null;
		}
		return Math.max(...Array.from(this.pagesMap.keys()));
	}

	get lastPage(): StructurePage|null {
		const pageNo = this.lastPageNo;
		if (pageNo === null) {
			return null;
		}

		return this.pagesMap.get(pageNo).structurePage;
	}

	private obtainSiPage(pageNo: number): SiPage {
		if (this.siPageCollection.containsPageNo(pageNo)) {
			return this.siPageCollection.getPageByNo(pageNo);
		} else {
			return this.siPageCollection.loadPage(pageNo);
		}
	}

	containsPageNo(pageNo: number): boolean {
		return this.pagesMap.has(pageNo);
	}

	getPageByNo(pageNo: number): StructurePage {
		if (this.pagesMap.has(pageNo)) {
			return this.pagesMap.get(pageNo).structurePage;
		}

		throw new IllegalStateError('Unknown page no: ' + pageNo);
	}

	loadSingle(pageNo: number, offsetHeight: number): StructurePage {
		this.clear();

		return this.createPage(this.obtainSiPage(pageNo), offsetHeight);
	}

	loadNext(offsetHeight: number): StructurePage {
		const pageNo = (this.lastPageNo || 0) + 1;

		return this.createPage(this.obtainSiPage(pageNo), offsetHeight);
	}

	get possiablePagesNum(): number {
		return this.siPageCollection.pagesNum;
	}

	updateFilter(quickSearchStr: string|null) {
		this.siPageCollection.updateFilter(quickSearchStr);
	}

	// getLastVisiblePage(): StructurePage|null {
	// 	let lastPage: SiPage|null = null;
	// 	for (const page of this.pagesMap.values()) {
	// 		if (page.offsetHeight !== null && (lastPage === null || page.no > lastPage.no)) {
	// 			lastPage = page;
	// 		}
	// 	}
	// 	return lastPage;
	// }

	getBestPageByOffsetHeight(offsetHeight: number): StructurePage|null {
		let prevPage: StructurePage|null = null;

		for (const page of this.pages) {
			if (prevPage === null || (page.offsetHeight < offsetHeight
					&& prevPage.offsetHeight <= page.offsetHeight)) {
				prevPage = page;
				continue;
			}

			const bestPageDelta = offsetHeight - prevPage.offsetHeight;
			const pageDelta = page.offsetHeight - offsetHeight;

			if (bestPageDelta < pageDelta) {
				return prevPage;
			} else {
				return page;
			}
		}

		return prevPage;
	}

	// map(siPages: SiPage[]) {
	// 	const structurePages = new Array<StructurePage>();

	// 	for (const siPage of siPages) {
	// 		let structurePage = this.getPage(siPage);
	// 		if (!structurePage) {
	// 			structurePage = this.createPage(siPage);
	// 		}

	// 		this.val(structurePage);
	// 		structurePages.push(structurePage);
	// 	}

	// 	return structurePages;
	// }

	clear(): void {
		for (const [key, ] of this.pagesMap) {
			this.removePageByNo(key);
		}
	}

	private removePageByNo(no: number, verifyStructurePage?: StructurePage): void {
		const v = this.pagesMap.get(no);

		if (!v) {
			throw new IllegalStateError('Page no ' + no + ' does not exist.');
		}

		if (verifyStructurePage && v.structurePage !== verifyStructurePage) {
			throw new IllegalStateError('StructurePage missmatch.');
		}

		v.structurePage.clear();
		v.subscription.unsubscribe();
		this.pagesMap.delete(no);
	}

	// private getPage(siPage: SiPage): StructurePage|null {
	// 	const structurePage = this.pagesMap.get(siPage.no);
	// 	if (!structurePage || structurePage.siPage === siPage) {
	// 		return structurePage;
	// 	}

	// 	this.pagesMap.delete(siPage.no);
	// 	structurePage.clear();
	// 	return null;
	// }

	private createPage(siPage: SiPage, offsetHeight: number): StructurePage {
		if (this.pagesMap.has(siPage.no)) {
			throw new IllegalStateError('StructurePage for page no ' + siPage.no + ' already exists.');
		}

		const sp = new StructurePage(siPage, offsetHeight);
		const sub = siPage.entries$.subscribe((entries) => {
			sp.clear();

			if (!entries) {
				return;
			}

			for (const siEntry of entries) {
				this.applyNewStructureEntry(sp, siEntry, null, null);
			}

			this.combineUiStructures();
		});

		sub.add(siPage.disposed$.subscribe(() => {
			this.removePageByNo(siPage.no);
			this.combineUiStructures();
		}));

		this.pagesMap.set(siPage.no, { structurePage: sp, subscription: sub });

		if (!sp.isEmpty()) {
			this.combineUiStructures();
		}

		return sp;
	}

	private applyNewStructureEntry(structurePage: StructurePage, siEntry: SiEntry, oldStructureEntry: StructureEntry|null,
			insertIndex: number|null) {
		const fieldUiStructures = this.createFieldUiStructures(siEntry);
		const controlUiContents = siEntry.selectedEntryBuildup.controls
				.map(siControl => siControl.createUiContent(() => this.uiStructure.getZone()));

		const structureEntry = new StructureEntry(siEntry, fieldUiStructures, controlUiContents,
				(replacementEntry) => {
					this.applyNewStructureEntry(structurePage, replacementEntry, structureEntry, null);
					this.combineUiStructures();
				});

		if (oldStructureEntry) {
			if (!structurePage.replaceStructureEntry(oldStructureEntry, structureEntry)) {
				oldStructureEntry.clear();
			}

			return;
		}

		if (insertIndex !== null && insertIndex !== undefined) {
			structurePage.placeStructureEntryAt(insertIndex, structureEntry);
			return;
		}

		structurePage.appendStructureEntry(structureEntry);
	}

	private createFieldUiStructures(siEntry: SiEntry): UiStructure[] {
		const uiStructures = new Array<UiStructure>();

		for (const siProp of this.getSiProps()) {
			const uiStructure = new UiStructure(null);
			// uiStructure.compact = true;
			uiStructure.model = siEntry.selectedEntryBuildup.getFieldById(siProp.id).createUiStructureModel(true);
			uiStructures.push(uiStructure);
		}

		return uiStructures;
	}

	determineDecendantSiEntries(entry: SiEntry): SiEntry[] {
		const entryPosition = this.siPageCollection.getSiEntryPosition(entry);

		const entries: SiEntry[] = [];
		this.fillDecendantEntries(entryPosition, entries);
		return entries;
	}

	private fillDecendantEntries(position: SiEntryPosition, entries: SiEntry[]): void {
		entries.push(...position.childEntryPositions.map(p => p.entry));

		position.childEntryPositions.forEach(p => this.fillDecendantEntries(p, entries));
	}

	get sortable(): boolean {
		return this.siPageCollection.sortable;
	}

	moveByIndex(previousIndex: number, nextIndex: number) {
		this.siPageCollection.moveByIndex(previousIndex, nextIndex);
	}

	moveAfter(identifiers: SiEntryIdentifier[], afterEntryIdentifier: SiEntryIdentifier) {
		const siEntries = identifiers.map(i => this.siPageCollection.getEntryByIdentifier(i));
		const targetSiEntry = this.siPageCollection.getEntryByIdentifier(afterEntryIdentifier);
		if (siEntries.length > 0 && targetSiEntry) {
			this.siPageCollection.moveAfter(siEntries, targetSiEntry);
		}
	}

	moveBefore(identifiers: SiEntryIdentifier[], beforeEntryIdentifier: SiEntryIdentifier) {
		const siEntries = identifiers.map(i => this.siPageCollection.getEntryByIdentifier(i));
		const targetSiEntry = this.siPageCollection.getEntryByIdentifier(beforeEntryIdentifier);
		if (siEntries.length > 0 && targetSiEntry) {
			this.siPageCollection.moveBefore(siEntries, targetSiEntry);
		}
	}

	moveToParent(identifiers: SiEntryIdentifier[], parentEntryIdentifier: SiEntryIdentifier) {
		const siEntries = identifiers.map(i => this.siPageCollection.getEntryByIdentifier(i));
		const targetSiEntry = this.siPageCollection.getEntryByIdentifier(parentEntryIdentifier);
		if (siEntries.length > 0 && targetSiEntry) {
			this.siPageCollection.moveToParent(siEntries, targetSiEntry);
		}
	}

	isTree(): boolean {
		return this.siPageCollection.isTree();
	}

	private combineUiStructures() {
		const uiStructures = new Array<UiStructure>();
		for (const structurePage of this.pages) {
			for (const structureEntry of structurePage.structureEntries) {
				uiStructures.push(...structureEntry.fieldUiStructures);
			}
		}
		this.uiStructuresSubject.next(uiStructures);
	}

	getUiStructures$(): Observable<UiStructure[]> {
		return this.uiStructuresSubject.asObservable();
	}
}


