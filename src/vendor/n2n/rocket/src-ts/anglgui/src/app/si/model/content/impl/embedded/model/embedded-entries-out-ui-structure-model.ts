import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { SiControl } from 'src/app/si/model/control/si-control';
import { SimpleSiControl } from 'src/app/si/model/control/impl/model/simple-si-control';
import { TranslationService } from 'src/app/util/i18n/translation.service';

import { PopupUiLayer } from 'src/app/ui/structure/model/ui-layer';
import { IllegalStateError } from 'src/app/util/err/illegal-state-error';
import { SiButton } from 'src/app/si/model/control/impl/model/si-button';
import { SimpleUiStructureModel } from 'src/app/ui/structure/model/impl/simple-si-structure-model';
import { Observable, Subscription } from 'rxjs';
import { UiStructureModelAdapter } from 'src/app/ui/structure/model/impl/ui-structure-model-adapter';
import { SiFrame } from 'src/app/si/model/meta/si-frame';
import { EmbeddedEntryComponent } from '../comp/embedded-entry/embedded-entry.component';
import { UiStructureModel, UiStructureModelMode } from 'src/app/ui/structure/model/ui-structure-model';
import { EmbeOutCollection } from './embe/embe-collection';
import { EmbeddedEntriesOutModel } from '../comp/embedded-entries-out-model';
import { EmbeddedEntriesOutComponent } from '../comp/embedded-entries-out/embedded-entries-out.component';
import { EmbeddedEntriesSummaryOutComponent } from '../comp/embedded-entries-summary-out/embedded-entries-summary-out.component';
import { Embe } from './embe/embe';
import { EmbeddedEntriesOutConfig } from './embe/embedded-entries-config';
import { Message } from 'src/app/util/i18n/message';
import { UiStructureError } from 'src/app/ui/structure/model/ui-structure-error';
import { EmbeStructure, EmbeStructureCollection } from './embe/embe-structure';
import { BehaviorCollection } from 'src/app/util/collection/behavior-collection';
import { UiStructureType } from 'src/app/si/model/meta/si-structure-declaration';
import { map } from 'rxjs/operators';
import { ButtonControlUiContent } from 'src/app/si/model/control/impl/comp/button-control-ui-content';

export class EmbeddedEntriesOutUiStructureModel extends UiStructureModelAdapter implements EmbeddedEntriesOutModel {
	private embeOutUiStructureManager: EmbeOutUiStructureManager|null = null;
	private embeStructureCollection: EmbeStructureCollection|null = null;
	private structureErrorCollection = new BehaviorCollection<UiStructureError>();
	private subscription: Subscription|null = null;
	private errorState = {
		messages: new Array<Message>(),
		structureErrors: new Array<UiStructureError>()
	};

	constructor(public popupTitle: string, public frame: SiFrame, private embeOutCol: EmbeOutCollection,
			private config: EmbeddedEntriesOutConfig, private translationService: TranslationService,
			disabledSubject: Observable<boolean>|null = null) {
		super();
		this.disabled$ = disabledSubject;
	}

	getEmbeOutCollection(): EmbeOutCollection {
		return this.embeOutCol;
	}

	private getEmbeOutUiStructureManager(): EmbeOutUiStructureManager {
		IllegalStateError.assertTrue(!!this.embeOutUiStructureManager);
		return this.embeOutUiStructureManager;
	}

	open(embeStructure: EmbeStructure): void {
		IllegalStateError.assertTrue(this.config.reduced);
		this.getEmbeOutUiStructureManager().open(embeStructure.embe);
	}

	openAll() {
		this.getEmbeOutUiStructureManager().openAll();
	}

	bind(uiStructure: UiStructure): void {
		super.bind(uiStructure);

		this.embeStructureCollection = new EmbeStructureCollection(this.config.reduced, this.embeOutCol);
		this.embeStructureCollection.refresh();
		this.subscription = new Subscription();
		this.subscription.add(this.embeOutCol.source.getMessages$().subscribe((messages) => {
			this.errorState.messages = messages;
			this.updateReducedStructureErrors();
		}));
		this.subscription.add(this.embeStructureCollection.reducedZoneErrors$.subscribe((structrueErrors) => {
			this.errorState.structureErrors = structrueErrors;
			this.updateReducedStructureErrors();
		}));

		if (!this.config.reduced) {
			this.uiContent = new TypeUiContent(EmbeddedEntriesOutComponent, (ref) => {
				ref.instance.model = this;
			});
			return;
		}

		this.embeOutUiStructureManager = new EmbeOutUiStructureManager(uiStructure, this, this.translationService);
		this.uiContent = new TypeUiContent(EmbeddedEntriesSummaryOutComponent, (ref) => {
			ref.instance.model = this;
		});

		const button = new SiButton(this.translationService.translate('show_all_txt'), 'rocket-btn-light rocket-btn-light-warning', 'fa fa-file');

		const openAllUiContent = new ButtonControlUiContent({
			getUiZone: () => uiStructure.getZone(),
			getSiButton: () => button,
			isLoading: () => false,
			isDisabled: () => false,
			exec: () => this.openAll()
		});

		this.mode = UiStructureModelMode.MASSIVE_TOOLBAR;
		this.toolbarStructureModelsSubject.next([new SimpleUiStructureModel(openAllUiContent)]);
	}

	unbind() {
		this.errorState.messages = [];
		this.errorState.structureErrors = [];
		this.embeStructureCollection.clear();
		this.embeStructureCollection = null;
	}

	getAsideContents(): UiContent[] {
		return [];
	}

	getEmbeStructures(): EmbeStructure[] {
		return this.embeStructureCollection.embeStructures;
	}

	private updateReducedStructureErrors() {
		const structureErrors = new Array<UiStructureError>();

		structureErrors.push(...this.errorState.messages.map(message => ({ message })));
		structureErrors.push(...this.errorState.structureErrors);

		this.structureErrorCollection.set(structureErrors);
	}

	getMessages(): Message[] {
		return this.embeOutCol.source.getMessages();
	}

	getStructures$(): Observable<UiStructure[]> {
		return this.embeStructureCollection.embeStructures$.pipe(map(es => es.map(e => e.uiStructure)));
	}

	getStructureErrors(): UiStructureError[] {
		return this.structureErrorCollection.get();
	}

	getStructureErrors$(): Observable<UiStructureError[]> {
		return this.structureErrorCollection.get$();
	}

	// getMessages$(): Observable<Message[]> {
	// 	return this.embeOutSource.getMessages$();
	// }

	// getZoneErrors(): UiZoneError[] {
	// 	const errors = new Array<UiZoneError>();

	// 	for (const embe of this.embeOutCol.embes) {
	// 		if (!embe.uiStructureModel) {
	// 			continue;
	// 		}

	// 		if (!this.config.reduced) {
	// 			errors.push(...embe.uiStructureModel.getZoneErrors());
	// 			continue;
	// 		}

	// 		for (const zoneError of embe.uiStructureModel.getZoneErrors()) {
	// 			errors.push({
	// 				message: zoneError.message,
	// 				marked: (marked) => {
	// 					this.reqBoundUiStructure().marked = marked;
	// 				},
	// 				focus: () => {
	// 					IllegalStateError.assertTrue(!!this.embeOutUiStructureManager);

	// 					this.embeOutUiStructureManager.open(embe);

	// 					if (zoneError.focus) {
	// 						zoneError.focus();
	// 					}
	// 				}
	// 			});
	// 		}
	// 	}

	// 	return errors;
	// }
}

class EmbeOutUiStructureManager {

	private popupUiLayer: PopupUiLayer|null = null;

	constructor(private uiStructure: UiStructure, private model: EmbeddedEntriesOutUiStructureModel, private translationService: TranslationService) {

	}

	// private createEmbeUsm(embe: Embe): UiStructureModel {
	// 	const model = new SimpleUiStructureModel();
	// 	model.initCallback = (uiStructure) => {
	// 		const child = new UiStructure(null);
	// 		child.model = embe.uiStructureModel;

	// 		model.content = new TypeUiContent(EmbeddedEntryComponent, (ref) => {
	// 			ref.instance.embeStructure = new EmbeStructure(embe, uiStructure);
	// 		});
	// 	};
	// 	return model;
	// }

	open(embe: Embe) {
		if (this.popupUiLayer) {
			return;
		}

		const uiZone = this.uiStructure.getZone();

		this.popupUiLayer = uiZone.layer.container.createLayer();
		const zone = this.popupUiLayer.pushRoute(null, null).zone;

		zone.title = this.model.popupTitle;
		zone.breadcrumbs = [];
		zone.structure = embe.uiStructure;
		zone.mainCommandContents = this.createPopupControls()
					.map(siControl => siControl.createUiContent(() => zone));

		this.popupUiLayer.onDispose(() => {
			this.popupUiLayer = null;
		});
	}

	openAll() {
		if (this.popupUiLayer) {
			return;
		}

		const popupUiStructureModel = new SimpleUiStructureModel();

		popupUiStructureModel.initCallback = () => {
			popupUiStructureModel.content = new TypeUiContent(EmbeddedEntriesOutComponent, (ref) => {
				ref.instance.model = this.model;
			});
		};

		const structure = new UiStructure(UiStructureType.SIMPLE_GROUP, null, popupUiStructureModel);

		const uiZone = this.uiStructure.getZone();

		this.popupUiLayer = uiZone.layer.container.createLayer();
		this.popupUiLayer.onDispose(() => {
			this.popupUiLayer = null;
			structure.dispose();
		});

		const zone = this.popupUiLayer.pushRoute(null, null).zone;


		zone.title = this.model.popupTitle;
		zone.breadcrumbs = [];
		zone.structure = structure;
		zone.mainCommandContents = this.createPopupControls()
					.map(siControl => siControl.createUiContent(() => zone));
	}

	private createPopupControls(): SiControl[] {
		return [
			new SimpleSiControl(
					new SiButton(this.translationService.translate('common_close_label'), 'btn btn-secondary', 'fas fa-trash'),
					() => {
						this.popupUiLayer.dispose();
					})
		];
	}
}
