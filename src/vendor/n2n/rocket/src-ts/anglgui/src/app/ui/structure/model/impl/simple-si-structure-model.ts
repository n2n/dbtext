import { Message } from 'src/app/util/i18n/message';
import { UiContent } from '../ui-content';
import { Observable } from 'rxjs';
import { UiStructure } from '../ui-structure';
import { UiStructureModelAdapter } from './ui-structure-model-adapter';
import { UiStructureModelMode, UiStructureModel } from '../ui-structure-model';
import { BehaviorCollection } from 'src/app/util/collection/behavior-collection';
import { UiStructureError } from '../ui-structure-error';
import { map } from 'rxjs/operators';

export class SimpleUiStructureModel extends UiStructureModelAdapter {

	public mode = UiStructureModelMode.NONE;
	public messagesCollection = new BehaviorCollection<Message>();
	public structuresCollection = new BehaviorCollection<UiStructure>();
	public initCallback: (uiStructure: UiStructure) => void = () => {};
	public destroyCallback: () => void = () => {};

	constructor(public content: UiContent|null = null) {
		super();
	}

	set mainControlContents(uiContents: UiContent[]) {
		this.mainControlUiContents = uiContents;
	}

	get mainControlContents(): UiContent[] {
		return this.mainControlUiContents;
	}

	set asideContents(uiContents: UiContent[]) {
		this.asideUiContents = uiContents;
	}

	get asideContents(): UiContent[] {
		return this.asideUiContents;
	}

	set toolbarStructureModels(toolbarStructureModels: UiStructureModel[]) {
		this.toolbarStructureModelsSubject.next(toolbarStructureModels);
	}

	get toolbarStructureModels(): UiStructureModel[] {
		return this.toolbarStructureModelsSubject.getValue();
	}

	bind(uiStructure: UiStructure) {
		super.bind(uiStructure);
		this.initCallback(uiStructure);
	}

	unbind() {
		super.unbind();
		this.destroyCallback();
	}

	setDisabled$(disabled$: Observable<boolean>) {
		this.disabled$ = disabled$;
	}

	getContent(): UiContent|null {
		return this.content;
	}

	// getStructureErrors(): UiStructureError[] {
	// 	return this.messagesCollection.get().map((m) => ({ message: m }));
	// }

	getStructures$(): Observable<UiStructure[]> {
		return this.structuresCollection.get$();
	}

	getStructureErrors$(): Observable<UiStructureError[]> {
		return this.messagesCollection.get$().pipe(map((ms) => ms.map((m) => ({ message: m }))));
	}

	getMode(): UiStructureModelMode {
		return this.mode;
	}
}
