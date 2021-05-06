import { MessageFieldModel } from '../../common/comp/message-field-model';

export interface StringFieldModel extends MessageFieldModel {

	getValue(): string|null;
	
	isBulky(): boolean;
}
