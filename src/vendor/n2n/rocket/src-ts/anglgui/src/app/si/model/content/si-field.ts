import { Message } from 'src/app/util/i18n/message';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { Observable } from 'rxjs';
import { SiGenericValue } from '../generic/si-generic-value';
import { SiInputResetPoint } from './si-input-reset-point';

export interface SiField {

	copyValue?: () => Promise<SiGenericValue>;

	pasteValue?: (genericValue: SiGenericValue) => Promise<boolean>;

	isDisplayable(): boolean;

	createUiStructureModel(compactMode: boolean): UiStructureModel;

	hasInput(): boolean;

	readInput(): object;

	createInputResetPoint(): Promise<SiInputResetPoint>;

	// handleError(error: SiFieldError): void;

	// resetError(): void;

	getMessages(): Message[];

	isDisabled(): boolean;

	setDisabled(disabled: boolean): void;

	getDisabled$(): Observable<boolean>;

	// copy(entryBuildUp: SiEntryBuildup): SiField;



	// consume(consumableSiField: SiField): SiField;
}
