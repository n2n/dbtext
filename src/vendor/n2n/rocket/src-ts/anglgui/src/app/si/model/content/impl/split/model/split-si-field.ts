import { SiField } from '../../../si-field';
import { SplitModel } from '../comp/split-model';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { SplitOption } from './split-option';
import { SiEntry } from '../../../si-entry';
import { SplitComponent } from '../comp/split/split.component';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { UiStructureModelMode, UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { SiFieldAdapter } from '../../common/model/si-field-adapter';
import { SimpleUiStructureModel } from 'src/app/ui/structure/model/impl/simple-si-structure-model';
import { UiStructureType } from 'src/app/si/model/meta/si-structure-declaration';
import { SplitViewStateService } from './state/split-view-state.service';
import { SplitViewStateSubscription } from './state/split-view-state-subscription';
import { SiCrumb } from '../../meta/model/si-crumb';
import { ButtonControlUiContent } from 'src/app/si/model/control/impl/comp/button-control-ui-content';
import { ButtonControlModel } from 'src/app/si/model/control/impl/comp/button-control-model';
import { CrumbGroupComponent } from '../../meta/comp/crumb-group/crumb-group.component';
import { SiButton } from 'src/app/si/model/control/impl/model/si-button';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { Subscription } from 'rxjs';
import { TranslationService } from 'src/app/util/i18n/translation.service';
import { UiStructureModelDecorator } from 'src/app/ui/structure/model/ui-structure-model-decorator';
import {SiInputResetPoint} from '../../../si-input-reset-point';
import { SplitContext, SplitStyle } from './split-context';
import { skip } from 'rxjs/operators';

export class SplitSiField extends SiFieldAdapter {

	splitContext: SplitContext|null;
	copyStyle: SplitStyle = { iconClass: null, tooltip: null };

	constructor(public refPropId: string, private viewStateService: SplitViewStateService, private translationService: TranslationService) {
		super();
	}

// 	handleError(error: SiFieldError): void {
// 		console.log(error);
// 	}

	hasInput(): boolean {
		return false;
	}

	readInput(): object {
		throw new IllegalSiStateError('no input');
	}

	createInputResetPoint(): Promise<SiInputResetPoint> {
		throw new IllegalSiStateError('no input');
	}

	// abstract copy(entryBuildUp: SiEntryBuildup): SiField;

	createUiStructureModel(compactMode: boolean): UiStructureModel {
		const uism = new SplitUiStructureModel(this.refPropId, this.splitContext, this.copyStyle, this.viewStateService,
				this.translationService, compactMode);
		uism.messagesCollection = this.messagesCollection;
		uism.setDisabled$(this.getDisabled$());
		return uism;
	}
}


class SplitUiStructureModel extends SimpleUiStructureModel implements SplitModel {
	private splitViewStateSubscription: SplitViewStateSubscription;
	readonly childUiStructureMap = new Map<string, UiStructure>();
	private loadedKeys = new Array<string>();
	private subscription: Subscription;

	private zoneSubscription: Subscription|null = null;

	constructor(private refPropId: string, private splitContext: SplitContext|null,
			private copyStyle: SplitStyle, private viewStateService: SplitViewStateService,
			private translationService: TranslationService, private compactMode: boolean) {
		super();
	}

	getSplitStyle(): SplitStyle {
		return this.splitContext ? this.splitContext.style : { iconClass: null, tooltip: null };
	}

	getCopyTooltip(): string|null {
		return this.copyStyle.tooltip;
	}

	getSplitOptions(): SplitOption[] {
		if (this.splitContext) {
			return this.splitContext.getSplitOptions();
		}

		return [];
	}

	getLabelByKey(key: string): string {
		return this.getSplitOptions().find(splitOption => splitOption.key === key).label;
	}

	getMode(): UiStructureModelMode {
		return UiStructureModelMode.ITEM_COLLECTION;
	}

	isKeyActive(key: string): boolean {
		if (!this.splitContext.isKeyActive) {
			return true;
		}

		return this.splitContext.isKeyActive(key);
	}

	activateKey(key: string): void {
		if (!this.splitContext.activateKey) {
			throw new IllegalSiStateError('Can not activate any keys.');
		}

		this.splitContext.activateKey(key);
	}

	getChildUiStructureMap(): Map<string, UiStructure> {
		return this.childUiStructureMap;
	}

	getSiField$(key: string): Promise<SiField|null> {
		if (!this.splitContext) {
			throw new Error('No SplitContext assigned.');
		}

		return this.splitContext.getEntry$(key).then((entry: SiEntry|null) => {
			if (entry === null) {
				return null;
			}

			return entry.selectedEntryBuildup.getFieldById(this.refPropId);
		});
	}

	bind(uiStructure: UiStructure) {
		super.bind(uiStructure);

		this.content = new TypeUiContent(SplitComponent, (ref) => {
			ref.instance.model = this;
			// ref.instance.uiStructure = uiStructure;
		});

		this.zoneSubscription = uiStructure.getZone$().subscribe((zone) => {
			if (!zone) {
				this.destroyStructures();
			} else {
				this.buildStructures(zone);
			}
		});
	}

	private destroyStructures() {
		if (!this.splitViewStateSubscription) {
			return;
		}

		this.splitViewStateSubscription.cancel();
		this.splitViewStateSubscription = null;

		if (this.subscription) {
			this.subscription.unsubscribe();
			this.subscription = null;
		}

		for (const childUiStructure of this.childUiStructureMap.values()) {
			childUiStructure.dispose();
		}
		this.childUiStructureMap.clear();
		this.loadedKeys = [];
	}

	private buildStructures(zone: UiZone): void {
		this.destroyStructures();

		this.splitViewStateSubscription = this.viewStateService.subscribe(zone, this.getSplitOptions(), this.getSplitStyle());

		for (const splitOption of this.getSplitOptions()) {
			const child = new UiStructure((this.compactMode ? UiStructureType.MINIMAL : UiStructureType.ITEM),
					splitOption.shortLabel);
			this.childUiStructureMap.set(splitOption.key, child);
			child.visible = false;
			child.visible$.pipe(skip(1)).subscribe(() => {
				this.splitViewStateSubscription.requestKeyVisibilityChange(splitOption.key, child.visible);
			});
		}

		this.checkChildUiStructureMap();
		this.splitViewStateSubscription.visibleKeysChanged$.subscribe(() => {
			this.checkChildUiStructureMap();
		});

		if (this.splitContext.activeKeys$) {
			this.subscription = this.splitContext.activeKeys$.subscribe(() => {
				this.checkChildUiStructureMap();
			});
		}
	}

	checkChildUiStructureMap() {
		for (const [key, childUiStructure] of this.childUiStructureMap) {
			childUiStructure.visible = this.splitViewStateSubscription.isKeyVisible(key);

			if (!childUiStructure.visible || -1 < this.loadedKeys.indexOf(key) || !this.isKeyActive(key)) {
				continue;
			}

			this.loadedKeys.push(key);
			this.getSiField$(key).then((siField) => {
				if (childUiStructure.disposed) {
					return;
				}

				if (!siField) {
					childUiStructure.model = this.createNotActiveUism();
					return;
				}

				let model = siField.createUiStructureModel(this.compactMode);

				if (siField.hasInput() && siField.pasteValue && siField.copyValue) {
					const decorator = model = new UiStructureModelDecorator(model);
					decorator.setAdditionalToolbarStructureModels([new SimpleUiStructureModel(new ButtonControlUiContent(
							new SplitButtonControlModel(key, siField, this, () => childUiStructure.getZone())))]);
				}

				childUiStructure.model = model;
			})/*.catch((e) => {
				childUiStructure.model = this.createNotActiveUism();
			})*/;
		}

		this.structuresCollection.set(Array.from(this.childUiStructureMap.values()));
	}

	private createNotActiveUism(): UiStructureModel {
		return new SimpleUiStructureModel(new TypeUiContent(CrumbGroupComponent, (ref) => {
			ref.instance.siCrumbGroup = {
				crumbs: [
					SiCrumb.createLabel(this.translationService.translate('ei_impl_locale_not_active_label'))
				]
			};
		}));
	}

	unbind(): void {
		super.unbind();

		if (this.zoneSubscription) {
			this.zoneSubscription.unsubscribe();
			this.zoneSubscription = null;
		}

		this.destroyStructures();
	}
}



class SplitButtonControlModel implements ButtonControlModel {
	private loading = false;

	private siButton: SiButton;
	private subSiButtons = new Map<string, SiButton>();

	constructor(private key: string, private siField: SiField, private model: SplitUiStructureModel,
			public getUiZone: () => UiZone) {
		this.siButton = new SiButton(null, 'btn btn-secondary', 'fas fa-reply-all');
		this.siButton.tooltip = this.model.getCopyTooltip();

		this.update();
	}

	update() {
		for (const splitOption of this.model.getSplitOptions()) {
			if (splitOption.key === this.key || this.subSiButtons.has(splitOption.key) || !this.model.isKeyActive(splitOption.key)) {
				continue;
			}

			this.subSiButtons.set(splitOption.key, new SiButton(splitOption.shortLabel, 'btn btn-secondary', 'fas fa-mail-forward'));
		}
	}

	isEmpty(): boolean {
		return this.subSiButtons.size === 0;
	}

	getSiButton(): SiButton {
		return this.siButton;
	}

	isLoading(): boolean {
		return this.loading;
	}

	isDisabled(): boolean {
		return this.loading;
	}

	exec(subKey: string|null): void {
		if (this.loading || !subKey) {
			return;
		}

		this.loading = true;

		this.model.getSiField$(subKey)
				.then(async (subSiField) => {
					if (this.siField.pasteValue && subSiField.copyValue) {
						this.siField.pasteValue(await subSiField.copyValue());
					}

					this.loading = false;
				});
	}

	getSubTooltip(): string|null {
		return this.model.getCopyTooltip();
	}

	getSubSiButtonMap(): Map<string, SiButton> {
		this.update();

		return this.subSiButtons;
	}
}
