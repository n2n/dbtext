import { SiGui } from '../../si-gui';
import { SiDeclaration } from '../../../meta/si-declaration';
import { SiEntry, SiEntryState } from '../../../content/si-entry';
import { SiControl } from '../../../control/si-control';
import { UiStructureModel, UiStructureModelMode } from 'src/app/ui/structure/model/ui-structure-model';
import { SimpleUiStructureModel } from 'src/app/ui/structure/model/impl/simple-si-structure-model';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiProp } from '../../../meta/si-prop';
import { SiField } from '../../../content/si-field';
import { Subscription, BehaviorSubject, Observable } from 'rxjs';
import { SiStructureDeclaration, UiStructureType, UiStructureTypeUtils } from '../../../meta/si-structure-declaration';
import { PlainContentComponent } from 'src/app/ui/structure/comp/plain-content/plain-content.component';
import { SiControlBoundry } from '../../../control/si-control-bountry';
import { SiFrame, SiFrameApiSection } from '../../../meta/si-frame';
import { SiEntryMonitor } from '../../../mod/model/si-entry-monitor';
import { SiService } from 'src/app/si/manage/si.service';
import { SiModStateService } from '../../../mod/model/si-mod-state.service';
import { BranchUiStructureModel } from 'src/app/ui/structure/model/impl/branch-ui-structure-model';
import { UiStructureModelDecorator } from 'src/app/ui/structure/model/ui-structure-model-decorator';
import { BulkyEntryModel } from '../comp/bulky-entry-model';
import { UiStructureModelAdapter } from 'src/app/ui/structure/model/impl/ui-structure-model-adapter';
import { StructureBranchModel } from 'src/app/ui/structure/comp/structure-branch-model';
import { BulkyEntryComponent } from '../comp/bulky-entry/bulky-entry.component';
import { SelectInFieldComponent } from '../../../content/impl/enum/comp/select-in-field/select-in-field.component';
import { SelectInFieldModel } from '../../../content/impl/enum/comp/select-in-field-model';
import { Message } from 'src/app/util/i18n/message';

export class BulkyEntrySiGui implements SiGui, SiControlBoundry {
	private _entry: SiEntry|null = null;
	public entryControlsIncluded = true;
	public controls: Array<SiControl> = [];

	constructor(public siFrame: SiFrame, public declaration: SiDeclaration, public siService: SiService,
			public siModStateService: SiModStateService) {
	}

	getBoundEntries(): SiEntry[] {
		return [this.entry];
	}

	getBoundDeclaration(): SiDeclaration {
		return this.declaration;
	}
	// reload() {
	// }

	// getContent() {
	// 	return this;
	// }

	get entry(): SiEntry|null {
		while (this._entry.replacementEntry) {
			this._entry = this._entry.replacementEntry;
		}
		return this._entry;
	}

	set entry(entry: SiEntry|null) {
		this._entry = entry;
	}

	createUiStructureModel(): UiStructureModel {
		return new BulkyUiStructureModel(this.entry, this.declaration, this.getControls(),
				new SiEntryMonitor(this.siFrame.getApiUrl(SiFrameApiSection.GET), this.siService,
						this.siModStateService, this.entryControlsIncluded));
	}

	private getControls(): SiControl[] {
		const controls: SiControl[] = [];
		controls.push(...this.controls);
		return controls;
	}
}

class BulkyUiStructureModel extends UiStructureModelAdapter implements BulkyEntryModel, StructureBranchModel {
	private subscription: Subscription|null = null;
	private uiStructureModelCache = new UiStructureModelCache();
	private uiStructureSubject = new BehaviorSubject<UiStructure[]>([]);

	constructor(private siEntry: SiEntry, private siDeclaration: SiDeclaration, private controls: SiControl[],
			private siEntryMonitor: SiEntryMonitor) {
		super();
	}

	getSiEntry(): SiEntry {
		return this.siEntry;
	}

	// getSiDeclaration(): SiDeclaration {
	// 	return this.siDeclaration;
	// }

	getContentStructureBranchModel(): StructureBranchModel {
		return this;
	}

	getStructures$(): Observable<UiStructure[]> {
		return this.uiStructureSubject.asObservable();
	}

	bind(uiStructure: UiStructure): void {
		super.bind(uiStructure);

		while (this.siEntry.replacementEntry) {
			this.siEntry = this.siEntry.replacementEntry;
		}

		this.subscription = new Subscription();

		if (!this.siEntry.isMultiType()) {
			this.rebuildStructures();
		} else {
			this.subscription.add(this.siEntry.selectedTypeId$.subscribe(() => {
				this.rebuildStructures();
			}));
		}

		this.siEntryMonitor.start();
		this.monitorEntry();

		this.mainControlUiContents = this.controls.map((control) => {
			return control.createUiContent(() => uiStructure.getZone());
		});

		this.uiContent = new TypeUiContent(BulkyEntryComponent, (ref) => {
			ref.instance.model = this;
		});
	}

	private monitorEntry() {
		if (!this.siEntry.isNew()) {
			this.siEntryMonitor.registerEntry(this.siEntry);
		}

		const sub = this.siEntry.state$.subscribe((state) => {
			switch (state) {
				case SiEntryState.REPLACED:
					if (!this.siEntry.isNew()) {
						this.siEntryMonitor.unregisterEntry(this.siEntry);
					}
					this.siEntry = this.siEntry.replacementEntry;
					this.subscription.remove(sub);
					this.monitorEntry();
					this.rebuildStructures();
					break;
			}
		});

		this.subscription.add(sub);
	}

	private createTypeSwitchUiStructureModel(): UiStructureModel {
		return new SimpleUiStructureModel(new TypeUiContent(SelectInFieldComponent, (ref) => {
			ref.instance.model = new TypeSelectInModel(this.siEntry);
		}));
	}

	unbind(): void {
		super.unbind();

		if (!this.siEntry.isNew()) {
			this.siEntryMonitor.unregisterEntry(this.siEntry);
		}
		this.siEntryMonitor.stop();
		this.uiContent = null;

		this.clear();

		if (this.subscription) {
			this.subscription.unsubscribe();
			this.subscription = null;
		}
	}

	private clear() {
		this.uiStructureSubject.next([]);
		this.asideUiContents = [];
		this.toolbarStructureModelsSubject.next([]);

		this.uiStructureModelCache.clear();
	}

	private rebuildStructures() {
		this.clear();

		if (!this.siEntry.entryBuildupSelected) {
			if (!this.isBoundStructureInsideGroup()){
				// todo: group
			}
			return;
		}

		this.toolbarStructureModelsSubject.next([]);
		this.mode = UiStructureModelMode.MASSIVE_TOOLBAR;

		this.asideUiContents = this.siEntry.selectedEntryBuildup.controls
				.map(control => control.createUiContent(() => this.boundUiStructure.getZone()));

		const siMaskDeclaration = this.siDeclaration.getTypeDeclarationByTypeId(this.siEntry.selectedEntryBuildupId);
		const toolbarResolver = new ToolbarResolver();

		this.uiStructureSubject.next(this.createStructures(siMaskDeclaration.structureDeclarations, toolbarResolver,
				!this.isBoundStructureInsideGroup()));

		for (const prop of siMaskDeclaration.type.getProps()) {
			if (prop.dependantPropIds.length > 0 && this.siEntry.selectedEntryBuildup.containsPropId(prop.id)) {
				toolbarResolver.fillContext(prop, this.siEntry.selectedEntryBuildup.getFieldById(prop.id));
			}
		}

		const toolbarStrucutreModels = [...toolbarResolver.toolbarUiStructureModels];
		if (this.siEntry.isMultiType()) {
			toolbarStrucutreModels.push(this.createTypeSwitchUiStructureModel());
		}
		this.toolbarStructureModelsSubject.next(toolbarStrucutreModels);
	}

	private isBoundStructureInsideGroup(): boolean {
		let uiStructure = this.boundUiStructure;
		do {
			if (UiStructureTypeUtils.isGroup(uiStructure.type)) {
				return true;
			}
		} while (uiStructure.isBound() && (uiStructure = uiStructure.getParent()));

		return false;
	}

	private createStructures(uiStructureDeclarations: SiStructureDeclaration[],
			toolbarResolver: ToolbarResolver, groupsRequired: boolean): UiStructure[] {
		const structures: UiStructure[] = [];
		let curUnbUiStructureModel: BranchUiStructureModel|null = null;

		for (const usd of uiStructureDeclarations) {
			if (!groupsRequired || UiStructureTypeUtils.isGroup(usd.type)
					|| (usd.type === UiStructureType.PANEL && !this.containsNonGrouped(usd))) {
				structures.push(this.createStructure(usd, toolbarResolver));
				curUnbUiStructureModel = null;
				continue;
			}

			if (!curUnbUiStructureModel) {
				curUnbUiStructureModel = new BranchUiStructureModel();
				structures.push(new UiStructure(UiStructureType.SIMPLE_GROUP, null, curUnbUiStructureModel));
			}

			curUnbUiStructureModel.pushUiStructure(this.createStructure(usd, toolbarResolver));
		}

		return structures;
	}

	private containsNonGrouped(siStructureDeclaration: SiStructureDeclaration): boolean {
		if (siStructureDeclaration.children.length === 0) {
			return false;
		}

		for (const child of siStructureDeclaration.children) {
			if (UiStructureTypeUtils.isGroup(child.type)) {
				continue;
			}

			if (child.type === UiStructureType.PANEL && !this.containsNonGrouped(child)) {
				continue;
			}

			return true;
		}

		return false;
	}

	private createStructure(ssd: SiStructureDeclaration, toolbarResolver: ToolbarResolver): UiStructure {
		const uiStructure = new UiStructure(ssd.type, (ssd.prop ? ssd.prop.label : ssd.label));

		if (ssd.prop) {
			uiStructure.label = ssd.prop.label;
			if (this.siEntry.selectedEntryBuildup.containsPropId(ssd.prop.id)) {
				const model = new UiStructureModelDecorator(this.createUiStructureModel(ssd.prop));
				uiStructure.model = model;
				toolbarResolver.registerDecorator(uiStructure, model);
				toolbarResolver.register(ssd.prop.id, uiStructure);
			}
			return uiStructure;
		}

		uiStructure.label = ssd.label;
		const branchModel = new UiStructureModelDecorator(new BranchUiStructureModel(
				this.createStructures(ssd.children, toolbarResolver, false)));
		uiStructure.model = branchModel;
		toolbarResolver.registerDecorator(uiStructure, branchModel);

		return uiStructure;
	}

	private createUiStructureModel(siProp: SiProp): UiStructureModel {
		if (this.siEntry.selectedEntryBuildup.containsPropId(siProp.id)) {
			const siField = this.siEntry.selectedEntryBuildup.getFieldById(siProp.id);
			return this.uiStructureModelCache.obtain(this.siEntry.selectedEntryBuildupId, siProp.id, siField);
		}

		return new SimpleUiStructureModel(new TypeUiContent(PlainContentComponent, () => {}));
	}
}

class UiStructureModelCache {
	private map = new Map<string, Map<string, UiStructureModel>>();

	obtain(siTypeId: string, siFieldId: string, siField: SiField): UiStructureModel {
		if (!this.map.has(siTypeId)) {
			this.map.set(siTypeId, new Map());
		}

		const map = this.map.get(siTypeId);
		if (!map.has(siFieldId)) {
			map.set(siFieldId, siField.createUiStructureModel(false));
		}

		return map.get(siFieldId);
	}

	clear(): void {
		this.map.clear();
	}
}

class TypeSelectInModel implements SelectInFieldModel {
	private options = new Map<string, string>();

	constructor(private siEntry: SiEntry) {
		for (const mq of siEntry.maskQualifiers) {
			this.options.set(mq.identifier.entryBuildupId, mq.name);
		}
	}

	isMandatory(): boolean {
		return true;
	}

	getMessages(): Message[] {
		return [];
	}

	getValue(): string {
		return this.siEntry.selectedEntryBuildupId;
	}

	setValue(value: string): void {
		this.siEntry.selectedEntryBuildupId = value;
	}

	getOptions(): Map<string, string> {
		return this.options;
	}

	getEmptyLabel(): string|null {
		return null;
	}
}

class ToolbarResolver {
	private uiStructuresMap = new Map<string, UiStructure>();
	private decoratorItems = new Array<{ uiStructure: UiStructure, decorator: UiStructureModelDecorator }>();
	public toolbarUiStructureModels = new Array<UiStructureModel>();

	register(propId: string, uiStructure: UiStructure) {
		this.uiStructuresMap.set(propId, uiStructure);
	}

	registerDecorator(uiStructure: UiStructure, decorator: UiStructureModelDecorator) {
		this.decoratorItems.push({ uiStructure, decorator });
	}

	fillContext(conextSiProp: SiProp, contextSiField: SiField) {
		if (!contextSiField.isDisplayable()) {
			return;
		}

		let contextUiStructure: UiStructure|null = null;

		for (const dependantPropId of conextSiProp.dependantPropIds) {
			const uiStructure = this.uiStructuresMap.get(dependantPropId);

			if (!uiStructure) {
				continue;
			}

			if (!contextUiStructure) {
				contextUiStructure = uiStructure;
			}

			contextUiStructure = this.deterOuter(contextUiStructure, uiStructure);
		}

		const uiStructureModel = contextSiField.createUiStructureModel(false);

		let decorator: UiStructureModelDecorator;
		if (contextUiStructure && (decorator = this.findDecorator(contextUiStructure))) {
			decorator.setAdditionalToolbarStructureModels([uiStructureModel]);
		} else {
			this.toolbarUiStructureModels.push(uiStructureModel);
		}
	}

	private findDecorator(uiStructure: UiStructure): UiStructureModelDecorator {
		const di = this.decoratorItems.find(d => d.uiStructure === uiStructure);

		if (di) {
			return di.decorator;
		}

		return null;
	}

	private deterOuter(uiStructure1: UiStructure, uiStructure2: UiStructure): UiStructure {
		if (uiStructure1 === uiStructure2) {
			return uiStructure1;
		}

		if (uiStructure1.containsDescendant(uiStructure2)) {
			return uiStructure1;
		}

		if (uiStructure2.containsDescendant(uiStructure1)) {
			return uiStructure2;
		}

		return this.deterOuter(uiStructure1.getParent(), uiStructure2.getParent());
	}

}

