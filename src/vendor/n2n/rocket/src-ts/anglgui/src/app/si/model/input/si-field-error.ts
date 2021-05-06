// import { SiEntryError } from 'src/app/si/model/input/si-entry-error';
// import { Message } from 'src/app/util/i18n/message';

// export class SiFieldError {
// 	constructor(public messages: Message[] = []) {
// 	}

// 	public subEntryErrors = new Map<string, SiEntryError>();

// 	getAllMessages(): Message[] {
// 		const messages: Message[] = [];

// 		messages.push(...this.messages);

// 		for (const [, entryError] of this.subEntryErrors) {
// 			messages.push(...entryError.getAllMessages());
// 		}

// 		return messages;
// 	}
// }
