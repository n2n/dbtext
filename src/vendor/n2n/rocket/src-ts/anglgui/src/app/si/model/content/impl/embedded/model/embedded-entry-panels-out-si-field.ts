import { SiPanel } from './si-panel';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiService } from 'src/app/si/manage/si.service';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { SiFieldAdapter } from '../../common/model/si-field-adapter';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { UiStructureModelAdapter } from 'src/app/ui/structure/model/impl/ui-structure-model-adapter';
import { TranslationService } from 'src/app/util/i18n/translation.service';
import { UiZoneError } from 'src/app/ui/structure/model/ui-zone-error';
import { UiStructureType } from 'src/app/si/model/meta/si-structure-declaration';
import { SiFrame } from 'src/app/si/model/meta/si-frame';
import { GenericMissmatchError } from 'src/app/si/model/generic/generic-missmatch-error';
import { SiModStateService } from 'src/app/si/model/mod/model/si-mod-state.service';
import { GenericEmbeddedEntryManager } from './generic/generic-embedded-entry-manager';
import { EmbeddedEntriesOutUiStructureModel } from './embedded-entries-out-ui-structure-model';
import { PanelDef } from '../comp/embedded-entry-panels-model';
import { EmbeddedEntryPanelsComponent } from '../comp/embedded-entry-panels/embedded-entry-panels.component';
import { Message } from 'src/app/util/i18n/message';
import { BehaviorCollection } from 'src/app/util/collection/behavior-collection';
import { Observable, from } from 'rxjs';
import { map } from 'rxjs/operators';
import { UiStructureError } from 'src/app/ui/structure/model/ui-structure-error';
import { EmbeOutCollection } from './embe/embe-collection';
import { IllegalStateError } from 'src/app/util/err/illegal-state-error';
import { CallbackInputResetPoint } from '../../common/model/callback-si-input-reset-point';
import { SiInputResetPoint } from '../../../si-input-reset-point';

class GenericSiPanelValueCollection {
	public map = new Map<string, SiGenericValue>();
}

export class EmbeddedEntryPanelsOutSiField extends SiFieldAdapter	{

	constructor(public siService: SiService, public siModState: SiModStateService, public frame: SiFrame,
			public translationService: TranslationService, public panels: SiPanel[]) {
		super();
	}

	getPanels(): SiPanel[] {
		return this.panels;
	}

	hasInput(): boolean {
		return false;
	}

	readInput(): object {
		throw new Error('not input');
	}

	createUiStructureModel(): UiStructureModel {
		const panelAssemblies = this.panels.map((panel) => {
			const embeOutCol = new EmbeOutCollection(panel);
			embeOutCol.readEmbes();

			return {
				panel,
				structureModel: new EmbeddedEntriesOutUiStructureModel(panel.label, this.frame, embeOutCol, panel, this.translationService)
			};
		});

		return new EmbeddedEntryPanelsOutUiStructureModel(this.messagesCollection, panelAssemblies);
	}

	// createUiContent(uiStructure: UiStructure): UiContent {
	// 	return new TypeUiContent(EmbeddedEntryPanelsComponent, (ref) => {
	// 		ref.instance.model = this;
	// 		ref.instance.uiStructure = uiStructure;
	// 	});
	// }


	private createGenericManager(panel: SiPanel): GenericEmbeddedEntryManager {
		return new GenericEmbeddedEntryManager(panel.values, this.siService, this.siModState, this.frame, this, panel.reduced,
				panel.allowedTypeIds);
	}

	async copyValue(): Promise<SiGenericValue> {
		const col = new GenericSiPanelValueCollection();

		const promises = new Array<Promise<void>>();
		for (const panel of this.panels) {
			promises.push(this.createGenericManager(panel).copyValue().then(genericValue => {
				col.map.set(panel.name, genericValue);
			}));
		}
		await Promise.all(promises);

		return new SiGenericValue(col);
	}

	pasteValue(genericValue: SiGenericValue): Promise<boolean> {
		const col = genericValue.readInstance(GenericSiPanelValueCollection);
		const promises = new Array<Promise<boolean>>();

		for (const panel of this.panels) {
			if (!col.map.has(panel.name)) {
				continue;
			}

			promises.push(this.createGenericManager(panel).pasteValue(col.map.get(panel.name)));
		}

		return Promise.all(promises).then(results => -1 !== results.indexOf(true));
	}
	
	createInputResetPoint(): Promise<SiInputResetPoint> {
		throw new Error('no input');
	}

}

class EmbeddedEntryPanelsOutUiStructureModel extends UiStructureModelAdapter {
	private panelDefs: PanelDef[]|null = null;

	constructor(private messagesCollection: BehaviorCollection<Message>,
			private panelAssemblies: Array<{panel: SiPanel, structureModel: EmbeddedEntriesOutUiStructureModel}>) {
		super();
	}

	bind(uiStructure: UiStructure) {
		super.bind(uiStructure);

		this.panelDefs = new Array<PanelDef>();
		for (const panelAssembly of this.panelAssemblies) {
			this.panelDefs.push({
				siPanel: panelAssembly.panel,
				uiStructure: new UiStructure(UiStructureType.SIMPLE_GROUP,
						panelAssembly.panel.label, panelAssembly.structureModel)
			});
		}

		this.uiContent = new TypeUiContent(EmbeddedEntryPanelsComponent, (ref) => {
			ref.instance.model = {
				getPanelDefs: () => this.panelDefs
			};
		});
	}

	getMessages(): Message[] {
		return this.messagesCollection.get();
	}

	getStructures$(): Observable<UiStructure[]> {
		IllegalStateError.assertTrue(!!this.panelDefs, 'EmbeddedEntryPanelsInUiStructureModel not bound.');
		return from([this.panelDefs.map(pa => pa.uiStructure)]);
	}

	// getStructureErrors(): UiStructureError[] {
	// 	return this.messagesCollection.get().map((message) => ({message}));
	// }

	getStructureErrors$(): Observable<UiStructureError[]> {
		return this.messagesCollection.get$().pipe(map((messages) => messages.map((message) => ({ message }))));
	}

	// getZoneErrors(): UiZoneError[] {
	// 	const uiZoneErrors = new Array<UiZoneError>();
	// 	for (const panelAssembly of this.panelAssemblies) {
	// 		uiZoneErrors.push(...panelAssembly.structureModel.getZoneErrors());
	// 	}
	// 	return uiZoneErrors;
	// }
}



