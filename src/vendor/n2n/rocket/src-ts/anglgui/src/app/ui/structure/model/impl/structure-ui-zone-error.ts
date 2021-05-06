import { UiZoneError } from '../ui-zone-error';
import { Message } from 'src/app/util/i18n/message';
import { UiStructure } from '../ui-structure';

export class StructureUiZoneError implements UiZoneError {
	constructor(public message: Message, public uiStructure: UiStructure) {
	}

	marked(marked) {
		this.uiStructure.marked = marked;
	}

	focus() {
		this.uiStructure.visible = true;
	}
}
