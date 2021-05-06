import { Message } from 'src/app/util/i18n/message';

export interface UiStructureError {
	message: Message;
	focus?: () => void;
	marked?: (marked: boolean) => void;
}
