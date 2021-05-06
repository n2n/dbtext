import { MessageFieldModel } from '../../common/comp/message-field-model';

export interface IframeOutModel extends MessageFieldModel {

	getUrl(): string|null;

	getSrcDoc(): string|null;
}
