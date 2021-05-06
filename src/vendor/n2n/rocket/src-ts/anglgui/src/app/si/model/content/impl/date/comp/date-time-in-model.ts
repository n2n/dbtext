import { MessageFieldModel } from '../../common/comp/message-field-model';


export interface DateTimeInModel extends MessageFieldModel {

	readonly mandatory: boolean;
	readonly dateChoosable: boolean;
	readonly timeChoosable: boolean;

	getValue(): Date|null;

	setValue(date: Date|null);
}
