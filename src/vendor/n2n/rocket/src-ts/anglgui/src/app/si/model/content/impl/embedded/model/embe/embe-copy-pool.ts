import { ClipboardService } from 'src/app/si/model/generic/clipboard.service';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { Embe } from './embe';
import { SiEmbeddedEntry } from '../si-embedded-entry';

export class CopyPool {
	private copyEvents = new Array<CopyEvent>();

	constructor(private clipboard: ClipboardService) {
	}

	toggle(embe: Embe) {
		const i = this.findIndex(embe);
		if (i !== -1) {
			this.removeByIndex(i);
			return;
		}

		this.add(embe);
	}

	add(embe: Embe) {
		this.update();

		if (this.copyEvents.length === 0) {
			this.clipboard.clear();
		}
		const genericValue = new SiGenericValue(embe.siEmbeddedEntry.copy());

		this.copyEvents.push({ siEmbeddedEntry: embe.siEmbeddedEntry, genericValue });
		this.clipboard.add(genericValue);
	}

	private update() {
		for (const copyEvent of this.copyEvents.filter(ce => !this.clipboard.has(ce.genericValue))) {
			this.copyEvents.splice(this.copyEvents.indexOf(copyEvent), 1);
		}
	}

	clear() {
		let copyEvent: CopyEvent;
		while (copyEvent = this.copyEvents.pop()) {
			this.clipboard.remove(copyEvent.genericValue);
		}
	}

	private findIndex(embe: Embe): number {
		return this.copyEvents.findIndex(copyEvent => copyEvent.siEmbeddedEntry === embe.siEmbeddedEntry);
	}

	remove(embe: Embe) {
		const i = this.findIndex(embe);
		if (i !== -1) {
			this.removeByIndex(i);
		}
	}

	private removeByIndex(i: number) {
		this.clipboard.remove(this.copyEvents[i].genericValue);
		this.copyEvents.splice(i, 1);
	}

	isCopied(embe: Embe) {
		const i = this.copyEvents.findIndex(copyEvent => copyEvent.siEmbeddedEntry === embe.siEmbeddedEntry);

		return i !== -1 && this.clipboard.has(this.copyEvents[i].genericValue);
	}
}

interface CopyEvent {
	siEmbeddedEntry: SiEmbeddedEntry;
	genericValue: SiGenericValue;
}
