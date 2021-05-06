import { MessageFieldModel } from '../../common/comp/message-field-model';

export interface SelectInFieldModel extends MessageFieldModel {

	getValue(): string|null;

	setValue(value: string|null): void;

	getOptions(): Map<string, string>;

	getEmptyLabel(): string|null;

	isMandatory(): boolean;
}
