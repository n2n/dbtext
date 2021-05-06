
import { MessageFieldModel } from '../../common/comp/message-field-model';

export interface PasswordInModel extends MessageFieldModel {

	setRawPassword(rawPassword: string|null): void;

	getMaxlength(): number|null;

	getMinlength(): number|null;

	isPasswordSet(): boolean;

	onBlur(): void;

	onFocus(): void;
}
