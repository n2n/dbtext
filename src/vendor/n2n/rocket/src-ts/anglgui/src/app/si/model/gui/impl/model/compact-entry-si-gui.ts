import { SiControl } from 'src/app/si/model/control/si-control';
import { Message } from 'src/app/util/i18n/message';
import { SiGui } from '../../si-gui';
import { SiEntry, SiEntryState } from '../../../content/si-entry';
import { SiDeclaration } from '../../../meta/si-declaration';
import { CompactEntryComponent } from '../comp/compact-entry/compact-entry.component';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { CompactEntryModel } from '../comp/compact-entry-model';
import { SiControlBoundry } from '../../../control/si-control-bountry';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { Subscription, BehaviorSubject, Observable } from 'rxjs';
import { SiEntryMonitor } from '../../../mod/model/si-entry-monitor';
import { UiStructureModelAdapter } from 'src/app/ui/structure/model/impl/ui-structure-model-adapter';
import { SiFrame, SiFrameApiSection } from '../../../meta/si-frame';
import { SiModStateService } from '../../../mod/model/si-mod-state.service';
import { SiService } from 'src/app/si/manage/si.service';
import { IllegalStateError } from 'src/app/util/err/illegal-state-error';

export class CompactEntrySiGui implements SiGui, SiControlBoundry {
	private entrySubject = new BehaviorSubject<SiEntry|null>(null);
	public entryControlsIncluded = true;
	public controls: SiControl[] = [];

	constructor(public siFrame: SiFrame, public declaration: SiDeclaration, public siService: SiService,
			public siModStateService: SiModStateService) {
	}

	get entry(): SiEntry|null {
		return this.entrySubject.getValue();
	}

	set entry(entry: SiEntry|null) {
		this.entrySubject.next(entry);
	}

	// get entry$(): Observable<SiEntry|null> {
	// 	return this.entrySubject.asObservable();
	// }

	getBoundEntries(): SiEntry[] {
		return [this.entry];
	}

	getBoundDeclaration(): SiDeclaration {
		return this.declaration;
	}

	getMessages(): Message[] {
		if (!this.entry) {
			return [];
		}

		return this.entry.getMessages();
	}

	getSiEntry(): SiEntry|null {
		return this.entry;
	}

	getSiDeclaration(): SiDeclaration {
		return this.declaration;
	}

	createUiStructureModel(): UiStructureModel {
		return new CompactUiStructureModel(this.entrySubject.asObservable(), this.declaration, this.controls,
				new SiEntryMonitor(this.siFrame.getApiUrl(SiFrameApiSection.GET), this.siService,
						this.siModStateService, this.entryControlsIncluded));
	}

	// getFieldDeclarations(): SiFieldDeclaration[] {
	// 	return this.declaration.getFieldDeclarationsByTypeId(this.entry.selectedTypeId);
	// }
}


class CompactUiStructureModel extends UiStructureModelAdapter implements CompactEntryModel {

	private fieldUiStructuresSubject = new BehaviorSubject<UiStructure[]>([]);
	private subscription: Subscription|null = null;
	private currentSiEntry: SiEntry|null = null;

	constructor(private siEntry$: Observable<SiEntry>, private siDeclaration: SiDeclaration, private controls: SiControl[],
			private siEntryMonitor: SiEntryMonitor) {
		super();
	}

	isLoading() {
		return !this.currentSiEntry;
	}

	getSiDeclaration(): SiDeclaration {
		return this.siDeclaration;
	}

	getFieldUiStructures(): UiStructure[] {
		return this.fieldUiStructuresSubject.getValue();
	}

	getStructures$(): Observable<UiStructure[]> {
		return this.fieldUiStructuresSubject.asObservable();
	}

	getMessages(): Message[] {
		return [];
	}



	// getStructureErrors(): UiStructureError[] {
	// 	return [];
	// }

	// getStructureErrors$(): Observable<UiStructureError[]> {
	// 	return from([]);
	// }

	// getZoneErrors(): UiZoneError[] {
	// 	if (!this.currentSiEntry) {
	// 		return [];
	// 	}

	// 	const zoneErrors = new Array<UiZoneError>();
	// 	const typeId = this.currentSiEntry.selectedTypeId;

	// 	if (!typeId) {
	// 		return zoneErrors;
	// 	}

	// 	for (const fieldUiStructure of this.fieldUiStructures) {
	// 		zoneErrors.push(...fieldUiStructure.getZoneErrors());
	// 	}
	// 	return zoneErrors;
	// }

	bind(uiStructure: UiStructure): void {
		super.bind(uiStructure);

		this.siEntryMonitor.start();

		this.subscription = new Subscription();

		this.subscription.add(this.siEntry$.subscribe((siEntry) => {
			this.rebuild(siEntry ? siEntry.getFinalReplacementEntry() : null);
		}));

		this.uiContent = new TypeUiContent(CompactEntryComponent, (ref) => {
			ref.instance.model = this;
		});

		this.mainControlUiContents = this.controls.map((control) => {
			return control.createUiContent(() => uiStructure.getZone());
		});
	}


	private rebuild(siEntry: SiEntry|null) {
		this.clear();

		if (!siEntry || !siEntry.entryBuildupSelected) {
			return;
		}

		this.currentSiEntry = siEntry;

		this.buildStructures(siEntry);

// 		if (!siEntry.isMultiType()) {
// 			this.rebuild(siEntry);
// 		} else {
// 			this.subscription.add(siEntry.selectedTypeId$.subscribe(() => {
// 				this.rebuild(siEntry);
// 			}));
// 		}

		this.monitorEntry(siEntry);
	}

	private buildStructures(siEntry: SiEntry) {
		const siEntryBuildup = siEntry.selectedEntryBuildup;
		const siMaskDeclaration = this.siDeclaration.getTypeDeclarationByTypeId(siEntry.selectedEntryBuildupId);

		this.asideUiContents = siEntryBuildup.controls
					.map(control => control.createUiContent(() => this.boundUiStructure.getZone()));

		const fieldUiStructures = new Array<UiStructure>();
		for (const siProp of siMaskDeclaration.getSiProps()) {
			const structure = new UiStructure(null);
			structure.model = siEntryBuildup.getFieldById(siProp.id).createUiStructureModel(true);
			// structure.compact = true;
			fieldUiStructures.push(structure);
		}
		this.fieldUiStructuresSubject.next(fieldUiStructures);
	}

	private monitorEntry(siEntry: SiEntry) {
		if (!siEntry.isNew()) {
			this.siEntryMonitor.registerEntry(siEntry);
		}

		const sub = siEntry.state$.subscribe((state) => {
			switch (state) {
				case SiEntryState.REPLACED:
					if (!siEntry.isNew()) {
						this.siEntryMonitor.unregisterEntry(siEntry);
					}
					this.subscription.remove(sub);
					this.rebuild(siEntry.replacementEntry);
					break;
			}
		});

		this.subscription.add(sub);
	}

	unbind(): void {
		super.unbind();

		this.clear();

		this.siEntryMonitor.stop();
		IllegalStateError.assertTrue(this.siEntryMonitor.size === 0,
				'Remaining monitor entries: ' + this.siEntryMonitor.size);

		this.uiContent = null;


		this.mainControlUiContents = [];

		if (this.subscription) {
			this.subscription.unsubscribe();
			this.subscription = null;
		}
	}

	private clear() {
		if (this.currentSiEntry) {
			if (!this.currentSiEntry.isNew()) {
				this.siEntryMonitor.unregisterEntry(this.currentSiEntry);
			}
			this.currentSiEntry = null;
		}

		for (const fieldUiStructure of this.fieldUiStructuresSubject.getValue()) {
			fieldUiStructure.dispose();
		}
		this.fieldUiStructuresSubject.next([]);

		this.asideUiContents = [];
	}



}
