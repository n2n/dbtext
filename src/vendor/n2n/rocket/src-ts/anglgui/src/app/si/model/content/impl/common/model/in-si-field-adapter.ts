import { SimpleSiFieldAdapter } from './simple-si-field-adapter';
import { SiInputResetPoint } from '../../../si-input-reset-point';

export abstract class InSiFieldAdapter extends SimpleSiFieldAdapter {

	hasInput(): boolean {
		return true;
	}

	abstract readInput(): object;

	abstract createInputResetPoint(): Promise<SiInputResetPoint>;

	// abstract copy(): SiField;

	// protected abstract createUiContent(uiStructure: UiStructure): UiContent;
}
