import { UiStructureModel, UiStructureModelMode } from './ui-structure-model';
import { UiStructure } from './ui-structure';
import { UiContent } from './ui-content';
import { Observable, BehaviorSubject, combineLatest } from 'rxjs';
import { UiStructureError } from './ui-structure-error';
import { map } from 'rxjs/operators';

export class UiStructureModelDecorator implements UiStructureModel {
	private additionalToolbarStructureModelsSubject = new BehaviorSubject<UiStructureModel[]>([]);

	constructor(readonly decorated: UiStructureModel) {
	}

	bind(uiStructure: UiStructure): void {
		this.decorated.bind(uiStructure);
	}

	unbind(): void {
		this.decorated.unbind();
	}

	getContent(): UiContent|null {
		return this.decorated.getContent();
	}

	getMainControlContents(): UiContent[] {
		return this.decorated.getMainControlContents();
	}

	getAsideContents(): UiContent[] {
		return this.decorated.getAsideContents();
	}

	setAdditionalToolbarStructureModels(models: UiStructureModel[]) {
		this.additionalToolbarStructureModelsSubject.next(models);
	}

	getAdditionalToolbarStructureModels(): UiStructureModel[] {
		return this.additionalToolbarStructureModelsSubject.getValue();
	}

	getToolbarStructureModels$(): Observable<UiStructureModel[]> {
		return combineLatest([this.additionalToolbarStructureModelsSubject, this.decorated.getToolbarStructureModels$()])
				.pipe(map(([mo1, mo2]) => [...mo1, ...mo2]));
	}

	getStructureErrors$(): Observable<UiStructureError[]> {
		return this.decorated.getStructureErrors$();
	}

	getStructures$(): Observable<UiStructure[]> {
		return this.decorated.getStructures$();
	}

	getDisabled$(): Observable<boolean> {
		return this.decorated.getDisabled$();
	}

	getMode(): UiStructureModelMode {
		return this.decorated.getMode();
	}
}
