
import { MessageFieldModel } from '../../common/comp/message-field-model';
import { SiCrumbGroup } from '../../meta/model/si-crumb';

export interface InputInFieldModel extends MessageFieldModel {

	getValue(): string|null;

	setValue(value: string|null): void;

	getType(): string;

	getMaxlength(): number|null;

	getMin(): number|null;

	getMax(): number|null;

	getStep(): number|null;

	getPrefixAddons(): SiCrumbGroup[];

	getSuffixAddons(): SiCrumbGroup[];

	onFocus(): void;

	onBlur(): void;
}
