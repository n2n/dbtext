
import { MessageFieldModel } from '../../common/comp/message-field-model';
import { SiCrumbGroup } from '../../meta/model/si-crumb';

export interface TextAreaInFieldModel extends MessageFieldModel {

	getValue(): string|null;

	setValue(value: string|null): void;

	getMaxlength(): number|null;

	onFocus(): void;

	onBlur(): void;
}
