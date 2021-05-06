import { UiStructureModel, UiStructureModelMode } from '../ui-structure-model';
import { UiContent } from '../ui-content';
import { Observable, of, from, BehaviorSubject } from 'rxjs';
import { UiStructure } from '../ui-structure';
import { IllegalStateError } from 'src/app/util/err/illegal-state-error';
import { UiStructureError } from '../ui-structure-error';

export abstract class UiStructureModelAdapter implements UiStructureModel {
	protected boundUiStructure: UiStructure|null = null;
	protected uiContent: UiContent|null = null;
	protected mainControlUiContents: UiContent[] = [];
	protected asideUiContents: UiContent[] = [];
	protected toolbarStructureModelsSubject = new BehaviorSubject<UiStructureModel[]>([]);
	protected disabled$: Observable<boolean>;
	protected mode = UiStructureModelMode.NONE;

	bind(uiStructure: UiStructure): void {
		IllegalStateError.assertTrue(!this.boundUiStructure, 'UiStructureModel already bound. ');
		this.boundUiStructure = uiStructure;
	}

	unbind(): void {
		IllegalStateError.assertTrue(!!this.boundUiStructure, 'UiStructureModel not bound.');
		this.boundUiStructure = null;
	}

	protected reqBoundUiStructure(): UiStructure {
		IllegalStateError.assertTrue(!!this.boundUiStructure, 'UiStructureModel not bound.');
		return this.boundUiStructure;
	}

	getContent(): UiContent|null {
		return this.uiContent;
	}

	getMainControlContents(): UiContent[] {
		return this.mainControlUiContents;
	}

	getAsideContents(): UiContent[] {
		return this.asideUiContents;
	}

	getToolbarStructureModels$(): Observable<UiStructureModel[]> {
		return this.toolbarStructureModelsSubject.asObservable();
	}

	getDisabled$(): Observable<boolean> {
		if (!this.disabled$) {
			this.disabled$ = of(false);
		}

		return this.disabled$;
	}

	abstract getStructures$(): Observable<UiStructure[]>;

	getStructureErrors$(): Observable<UiStructureError[]> {
		return from([]);
	}

	getMode(): UiStructureModelMode {
		return this.mode;
	}
}
