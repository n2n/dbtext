import { Observable, BehaviorSubject, Subscription, Subject } from 'rxjs';
import { UiZone } from './ui-zone';
import { UiStructureModel, UiStructureModelMode } from './ui-structure-model';
import { UiStructureType } from 'src/app/si/model/meta/si-structure-declaration';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { UiZoneError } from './ui-zone-error';
import { BehaviorCollection } from 'src/app/util/collection/behavior-collection';
import { UiStructureError } from './ui-structure-error';
import { IllegalStateError } from 'src/app/util/err/illegal-state-error';
import { filter } from 'rxjs/operators';
import { IllegalArgumentError } from 'src/app/si/util/illegal-argument-error';

export class UiStructure {
	private zoneSubject = new BehaviorSubject<UiZone|null>(null);
	private parent: UiStructure|null;
	private parentSubscription: Subscription|null;

	private pModel: UiStructureModel|null;
	private modelSubscription: Subscription|null = null;
	private modelStructureErrors: UiStructureError[]|null = null;
	private children: Array<{ structure: UiStructure, subscription: Subscription }> = [];
	private toolbarItems: Array<{ structure: UiStructure, subscription: Subscription }> = [];
	// private extraToolbarItems: Array<{ structure: UiStructure, subscription: Subscription }> = [];
	private zoneErrorsCollection = new BehaviorCollection<UiZoneError>();

	private visibleSubject = new BehaviorSubject<boolean>(true);
	private markedSubject = new BehaviorSubject<boolean>(false);
	// private toolbarChildrenSubject = new BehaviorSubject<UiStructure[]>([]);
	// private contentChildrenSubject = new BehaviorSubject<UiStructure[]>([]);
	private disabledSubject = new BehaviorSubject<boolean>(false);
	private focusedSubject = new Subject<void>();

	private disposedSubject = new BehaviorSubject<boolean>(false);
	private pLevel: number|null = null;

	// compact = false;

	constructor(public type: UiStructureType|null = null,
			public label: string|null = null, model: UiStructureModel|null = null) {

		this.model = model;
	}

	setZone(zone: UiZone|null): void {
		if (zone && zone.structure !== this) {
			throw new IllegalArgumentError('ZoneModel structure does not match.');
		}

		if (!zone && this.zoneSubject.getValue() && this.zoneSubject.getValue().structure) {
			throw new IllegalArgumentError('ZoneModel structure not unset.');
		}

		if (zone && (this.zoneSubject.getValue() || this.parent)) {
			throw new IllegalStateError('UiStructure has already been assigned to a zone or parent.');
		}

		this.zoneSubject.next(zone);
	}

	protected setParent(uiStructure: UiStructure|null): void {
		if (this.isRoot()) {
			throw new IllegalStateError('UiStructure is at root.');
		}

		if (this.parent && uiStructure) {
			throw new IllegalStateError('UiStructure has already been assigned to a parent.');
		}

		if (this.parent) {
			this.parent = null;
			this.parentSubscription.unsubscribe();
			this.parentSubscription = null;
			this.zoneSubject.next(null);
		}

		if (!uiStructure) {
			return;
		}

		this.parent = uiStructure;
		this.parentSubscription = uiStructure.getZone$().subscribe((zone) => {
			if (this.zoneSubject.getValue() !== zone) {
				this.zoneSubject.next(zone);
			}
		});
	}

	private ensureAssigned(): void {
		if (!this.parent && !this.zoneSubject.getValue()) {
			throw new IllegalStateError('UiStructure has not yet been assigned to parent or zone.');
		}
	}

	isBound(): boolean {
		return !!this.parent || !!this.zoneSubject.getValue();
	}

	getParent(): UiStructure|null {
		this.ensureAssigned();
		return this.parent;
	}

	hasZone(): boolean {
		return !!this.zoneSubject.getValue();
	}

	getZone(): UiZone|null {
		this.ensureAssigned();
		return this.zoneSubject.getValue();
	}

	getZone$(): Observable<UiZone|null> {
		return this.zoneSubject.asObservable();
	}

	isRoot(): boolean {
		return !this.parent && !!this.zoneSubject.getValue();
	}

	getRoot(): UiStructure {
		let root: UiStructure = this;

		while (root.parent) {
			root = root.parent;
		}

		return root;
	}

	get level(): number {
		this.ensureAssigned();

		if (this.pLevel !== null) {
			return this.pLevel;
		}

		this.pLevel = 0;

		let cur: UiStructure = this;
		while (cur.parent) {
			cur = cur.parent;
			this.pLevel++;
		}

		return this.pLevel;
	}

	containsDescendant(uiStructure: UiStructure): boolean {
		let curUiStructure = uiStructure;
		while (curUiStructure.parent) {
			if (curUiStructure.parent === this) {
				return true;
			}

			curUiStructure = curUiStructure.parent;
		}

		return false;
	}

	isItemCollection(): boolean {
		return this.type === UiStructureType.ITEM && !!this.model
				// tslint:disable-next-line: no-bitwise
				&& 0 < (this.model.getMode() & UiStructureModelMode.ITEM_COLLECTION);

	}

	isDoubleItem(): boolean {
		return this.type === UiStructureType.ITEM && this.parent.isItemCollection();
	}

	isToolbarMassive(): boolean {
		// tslint:disable-next-line: no-bitwise
		return this.model && 0 < (this.model.getMode() & UiStructureModelMode.MASSIVE_TOOLBAR);
	}

	// createToolbarChild(model: UiStructureModel): UiStructure {
	// 	const toolbarChild = new UiStructure(this, null, null, null, model);

	// 	const toolbarChildrean = this.toolbarChildrenSubject.getValue();
	// 	toolbarChildrean.push(toolbarChild);
	// 	this.toolbarChildrenSubject.next(toolbarChildrean);

	// 	return toolbarChild;
	// }

	// createContentChild(type: UiStructureType|null = null, label: string|null = null,
	// 		model: UiStructureModel|null = null): UiStructure {
	// 	const contentChild = new UiStructure(this, null, type, label, model);

	// 	const contentChildrean = this.contentChildrenSubject.getValue();
	// 	contentChildrean.push(contentChild);
	// 	this.contentChildrenSubject.next(contentChildrean);

	// 	return contentChild;
	// }

	// createChild(type: UiStructureType|null = null, label: string|null = null,
	// 		model: UiStructureModel|null = null): UiStructure {
	// 	return new UiStructure(this, null, type, label, model);
	// }

	// hasToolbarChildren(): boolean {
	// 	return this.getToolbarChildren().length > 0;
	// }

	// getToolbarChildren(): UiStructure[] {
	// 	return Array.from(this.toolbarChildrenSubject.getValue());
	// }

	// getToolbarChildren$(): Observable<UiStructure[]> {
	// 	return this.toolbarChildrenSubject;
	// }

	// hasContentChildren(): boolean {
	// 	return this.getContentChildren().length > 0;
	// }

	// getContentChildren(): UiStructure[] {
	// 	return Array.from(this.contentChildrenSubject.getValue());
	// }

	// getContentChildren$(): Observable<UiStructure[]> {
	// 	return this.contentChildrenSubject.asObservable();
	// }

	getChildren(): UiStructure[] {
		return this.children.map(c => c.structure);
	}

	getToolbarStructures(): UiStructure[] {
		return this.toolbarItems.map(ti => ti.structure)/*.concat(this.extraToolbarItems.map(eti => eti.structure))*/;
	}

	get disposed(): boolean {
		return this.disposedSubject.getValue();
	}

	private ensureNotDisposed() {
		if (!this.disposed) {
			return;
		}
		throw new IllegalSiStateError('UiStructure already disposed.');
	}

	get model(): UiStructureModel|null {
// 		this.ensureNotDisposed();
		return this.pModel;
	}

	set model(model: UiStructureModel|null) {
		this.ensureNotDisposed();

		if (this.pModel === model) {
			return;
		}

		this.clear();

		if (!model) {
			return;
		}

		this.pModel = model;
		this.modelStructureErrors = [];
		this.modelSubscription = new Subscription();

		model.bind(this);

		this.modelSubscription.add(model.getStructures$().subscribe((structures) => this.updateChildren(structures)));
		this.modelSubscription.add(model.getToolbarStructureModels$().subscribe((structureModels) => this.updateToolbar(structureModels)));
		this.modelSubscription.add(model.getDisabled$().subscribe(d => this.disabledSubject.next(d)));
		this.modelSubscription.add(model.getStructureErrors$().subscribe((structureErrors) => {
			this.modelStructureErrors = structureErrors;
			this.compileZoneErrors();
		}));
	}

	private clear() {
		this.clearChildren();
		this.clearToolbarItems();
		// this.clearExtraToolbarItems();

		if (this.pModel) {
			this.modelSubscription.unsubscribe();
			this.modelSubscription = null;
			this.pModel.unbind();
			this.pModel = null;
		}

		this.zoneErrorsCollection.set([]);
	}

	private clearToolbarItems() {
		let toolbarItem: { structure: UiStructure, subscription: Subscription };
		while (toolbarItem = this.toolbarItems.pop()) {
			toolbarItem.subscription.unsubscribe();
			toolbarItem.structure.dispose();
		}
	}

	private clearChildren() {
		let child: { structure: UiStructure, subscription: Subscription };
		while (child = this.children.pop()) {
			child.subscription.unsubscribe();
			child.structure.setParent(null);
		}
	}

	private unregisterChild(structure: UiStructure) {
		const i = this.children.findIndex(c => c.structure === structure);
		if (i === -1) {
			throw new IllegalStateError('Unknown child');
		}

		const child = this.children.splice(i, 1)[0];
		child.subscription.unsubscribe();
		child.structure.setParent(null);
	}

	private updateToolbar(structureModels: UiStructureModel[]) {
		const updatedToolbarItems = new Array<{ structure: UiStructure, subscription: Subscription }>();

		for (const structureModel of structureModels) {
			const i = this.toolbarItems.findIndex(e => e.structure.model === structureModel);
			if (i > -1) {
				updatedToolbarItems.push(this.toolbarItems.splice(i, 1)[0]);
				continue;
			}

			const structure = new UiStructure(null, null, structureModel);

			updatedToolbarItems.push({
				structure,
				subscription: structure.getZoneErrors$().subscribe(() => {
					this.compileZoneErrors();
				})
			});

			structure.setParent(this);
		}

		this.clearToolbarItems();

		this.toolbarItems = updatedToolbarItems;
	}

	private updateChildren(structures: UiStructure[]) {
		const updatedChildren = new Array<{ structure: UiStructure, subscription: Subscription }>();

		for (const structure of structures) {
			if (structure.disposed) {
				continue;
			}

			const i = this.children.findIndex(e => e.structure === structure);
			if (i > -1) {
				updatedChildren.push(this.children.splice(i, 1)[0]);
				continue;
			}

			const subscription = new Subscription();
			subscription.add(structure.getZoneErrors$().subscribe(() => {
				this.compileZoneErrors();
			}));
			subscription.add(structure.disposed$.pipe(filter(d => d)).subscribe(() => {
				this.unregisterChild(structure);
				this.compileZoneErrors();
			}));

			updatedChildren.push({ structure, subscription });

			structure.setParent(this);
		}

		this.clearChildren();

		this.children = updatedChildren;
		this.compileZoneErrors();
	}

	// addExtraToolbarStructureModel(...uiStructureModels: UiStructureModel[]) {
	// 	const models = this.getExtraToolbarStructureModels();
	// 	models.push(...uiStructureModels);
	// 	models.filter((value, index, self) => {
		// 		return self.indexOf(value) === index;
	// 	});
	// 	this.setExtraToolbarStructureModels(models);
	// }

	// removeExtraToolbarStructureModel(...uiStructureModels: UiStructureModel[]) {
	// 	const models = this.getExtraToolbarStructureModels();
	// 	for (const uiStructureModel of uiStructureModels) {
	// 		const i = models.indexOf(uiStructureModel);
	// 		if (i > -1) {
	// 			models.splice(i, 1);
	// 		}
	// 	}
	// 	this.setExtraToolbarStructureModels(models);
	// }

	// setExtraToolbarStructureModels(structureModels: UiStructureModel[]) {
	// 	const updatedExtraToolbarItems = new Array<{ structure: UiStructure, subscription: Subscription }>();

	// 	for (const structureModel of structureModels) {
	// 		const i = this.extraToolbarItems.findIndex(e => e.structure.model === structureModel);
	// 		if (i > -1) {
	// 			updatedExtraToolbarItems.push(this.toolbarItems.splice(i, 1)[0]);
	// 			continue;
	// 		}

	// 		const structure = new UiStructure(null, null, structureModel);
	// 		updatedExtraToolbarItems.push({
	// 			structure,
	// 			subscription: structure.getZoneErrors$().subscribe(() => {
	// 				this.compileZoneErrors();
	// 			})
	// 		});

	// 		structure.setParent(this);
	// 	}

	// 	this.clearExtraToolbarItems();

	// 	this.extraToolbarItems = updatedExtraToolbarItems;
	// }

	// getExtraToolbarStructureModels(): UiStructureModel[] {
	// 	return this.extraToolbarItems.map(eti => eti.structure.model);
	// }

	// private clearExtraToolbarItems() {
	// 	let extraToolbarItem: { structure: UiStructure, subscription: Subscription };
	// 	while (extraToolbarItem = this.extraToolbarItems.pop()) {
	// 		extraToolbarItem.subscription.unsubscribe();
	// 		extraToolbarItem.structure.dispose();
	// 	}
	// }

	get disposed$(): Observable<boolean> {
		return this.disposedSubject;
	}

	dispose() {
		if (this.disposed) {
			return;
		}

		this.disposedSubject.next(true);
		this.disposedSubject.complete();

		this.clear();

		this.visibleSubject.complete();
		this.markedSubject.complete();
		this.focusedSubject.complete();
		this.zoneSubject.complete();

		this.zoneErrorsCollection.dispose();
	}

	// protected registerChild(child: UiStructure) {
	// 	this.ensureNotDisposed();

	// 	const i = this.children.findIndex(c => c.structure === child);
	// 	if (i !== -1 || this === child) {
	// 		throw new IllegalSiStateError('Child already exists or is same as parent.');
	// 	}

	// 	this.children.push({
	// 		structure: child,
	// 		subscription: child.getZoneErrors$().subscribe(() => {
	// 			this.compileZoneErrors();
	// 		})
	// 	});
	// }

	// protected unregisterChild(child: UiStructure) {
	// 	let i = this.children.findIndex(c => c.structure === child);
	// 	if (i === -1) {
	// 		throw new IllegalSiStateError('Unknown child.');
	// 	}

	// 	this.children.splice(i, 1);

	// 	const toolbarChildren = this.toolbarChildrenSubject.getValue();
	// 	i = toolbarChildren.indexOf(child);
	// 	if (i > -1) {
	// 		toolbarChildren.splice(i, 1);
	// 	}
	// 	this.toolbarChildrenSubject.next(toolbarChildren);

	// 	const contentChildren = this.contentChildrenSubject.getValue();
	// 	i = contentChildren.indexOf(child);
	// 	if (i > -1) {
	// 		contentChildren.splice(i, 1);
	// 	}
	// 	this.contentChildrenSubject.next(contentChildren);
	// }

	get marked(): boolean {
		return this.markedSubject.getValue();
	}

	set marked(marked: boolean) {
		this.markedSubject.next(marked);
	}

	get visible(): boolean {
		return this.visibleSubject.getValue();
	}

	set visible(visible: boolean) {
		this.visibleSubject.next(visible);
	}

	get visible$(): Observable<boolean> {
		return this.visibleSubject.asObservable();
	}

	get disabled(): boolean {
		return this.disabledSubject.getValue();
	}

	get disabled$(): Observable<boolean> {
		return this.disabledSubject.asObservable();
	}

	get focused$(): Observable<void> {
		return this.focusedSubject.asObservable();
	}

	focus(): void {
		this.focusedSubject.next();
	}

	getZoneErrors$(): Observable<UiZoneError[]> {
		this.ensureNotDisposed();
		return this.zoneErrorsCollection.get$();
	}

	getZoneErrors(): UiZoneError[] {
		this.ensureNotDisposed();
		return this.zoneErrorsCollection.get();
	}

	private compileZoneErrors(): void {
		const errors: UiZoneError[] = [];

		for (const toolbarItem of this.toolbarItems) {
			errors.push(...toolbarItem.structure.getZoneErrors());
		}

		// for (const extraToolbarItem of this.extraToolbarItems) {
		// 	errors.push(...extraToolbarItem.structure.getZoneErrors());
		// }

		if (this.model) {
			errors.push(...this.modelStructureErrors.map(se => this.createZoneError(se)));
		}

		for (const child of this.children) {
			errors.push(...child.structure.getZoneErrors());
		}

		this.zoneErrorsCollection.set(errors);
	}

	private createZoneError(structureError: UiStructureError): UiZoneError {
		return {
			message: structureError.message,
			marked: structureError.marked	|| ((marked) => {
				this.marked = marked;
			}),
			focus: (() => {
				this.visible = true;
				if (structureError.focus) {
					structureError.focus();
				}
			})
		};
	}
}
