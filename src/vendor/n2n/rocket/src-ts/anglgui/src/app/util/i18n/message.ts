
export class Message {
	public args: Map<string, string>|null = null;
	public durationMs: number|null = null;

	constructor(readonly content: string, readonly translated: boolean, readonly severity: MessageSeverity) {
	}

	static createText(text: string, severity = MessageSeverity.ERROR): Message {
		return new Message(text, true, severity);
	}

	static createTexts(texts: string[], severity = MessageSeverity.ERROR): Message[] {
		return texts.map(text => new Message(text, true, severity));
	}

	static createCode(code: string, args: Map<string, string>|null = null, severity = MessageSeverity.ERROR): Message {
		const msg = new Message(code, false, severity);
		msg.args = args;
		return msg;
	}

	static createCodes(codes: string[], severity = MessageSeverity.ERROR): Message[] {
		return codes.map(code => new Message(code, false, severity));
	}
}

export enum MessageSeverity {
	INFO = 'info',
	ERROR = 'error',
	SUCCESS = 'success',
	WARN = 'warn'
}
