
import { SiButton } from 'src/app/si/model/control/impl/model/si-button';
import { SiModEvent } from '../model/mod/model/si-mod-state.service';
import { Message } from 'src/app/util/i18n/message';
import { SiNavPoint } from '../model/control/si-nav-point';
import { SiEntry } from '../model/content/si-entry';

export interface SiControlResult {
	inputError?: SiInputError;
	callResponse?: SiCallResponse;
}

export class SiInputError {
	public errorEntries = new Map<string, SiEntry>();
}

export class SiCallResponse {
	public directive: SiDirective|null = null;
	public navPoint: SiNavPoint|null = null;
	public messages: Message[] = [];
	public newButton: SiButton|null = null;
	public modEvent: SiModEvent|null = null;
}

export enum SiDirective {
	REDIRECT_BACK = 'redirectBack',
	REDIRECT = 'redirect'
}
