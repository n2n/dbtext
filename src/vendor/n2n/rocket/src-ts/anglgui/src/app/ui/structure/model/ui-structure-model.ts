
import { UiContent } from './ui-content';
import { Observable } from 'rxjs';
import { UiStructure } from './ui-structure';
import { UiStructureError } from './ui-structure-error';

export interface UiStructureModel {

	bind(uiStructure: UiStructure): void;

	unbind(): void;

	getContent(): UiContent|null;

	getMainControlContents(): UiContent[];

	getAsideContents(): UiContent[];

	getToolbarStructureModels$(): Observable<UiStructureModel[]>;

	// getStructureErrors(): UiStructureError[];

	getStructureErrors$(): Observable<UiStructureError[]>;

	getStructures$(): Observable<UiStructure[]>;

	getDisabled$(): Observable<boolean>;

	getMode(): UiStructureModelMode;
}

export enum UiStructureModelMode {
	NONE = 0,
	ITEM_COLLECTION = 1,
	MASSIVE_TOOLBAR = 2
}
