import { MessageFieldModel } from '../../common/comp/message-field-model';

export interface StringArrayInModel extends MessageFieldModel {

	getValues(): string[];

	setValues(value: string[]): void;

	getMin(): number;

	getMax(): number|null;
}
