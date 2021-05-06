import { Embe } from './embe';
import { SiGenericEntry } from 'src/app/si/model/generic/si-generic-entry';
import { EmbeddedEntriesInConfig } from './embedded-entries-config';
import { SiEmbeddedEntry } from '../si-embedded-entry';
import {IllegalSiStateError} from '../../../../../../util/illegal-si-state-error';
import { Message } from 'src/app/util/i18n/message';
import { Observable } from 'rxjs';
import { SiInputResetPoint } from '../../../../si-input-reset-point';


export interface EmbeOutSource {
	getValues(): SiEmbeddedEntry[];

	getMessages(): Message[];

	getMessages$(): Observable<Message[]>;
}

export interface EmbeInSource extends EmbeOutSource {
	setValues(values: SiEmbeddedEntry[]): void;
}

export class EmbeOutCollection {
	public embes: Embe[] = [];

	constructor(readonly source: EmbeOutSource) {
	}

	// protected unregisterEmbe(embe: Embe) {
	// }

	// initEmbe(embe: Embe, siEmbeddedEntry: SiEmbeddedEntry): Embe {
	// 	embe.siEmbeddedEntry = siEmbeddedEntry;

	// 	return embe;
	// }

	removeEmbes() {
		let embe: Embe;
		// tslint:disable-next-line: no-conditional-assignment
		while (undefined !== (embe = this.embes.pop())) {
			embe.clear();
		}
	}

	createEmbe(siEmbeddedEntry: SiEmbeddedEntry|null = null): Embe {
		const embe = new Embe(siEmbeddedEntry);
		this.embes.push(embe);
		return embe;
	}

	readEmbes() {
		this.removeEmbes();

		for (const siEmbeddedEntry of this.source.getValues()) {
			this.createEmbe(siEmbeddedEntry);
		}
	}
}

export class EmbeInCollection extends EmbeOutCollection {
	constructor(private inSource: EmbeInSource, private config: EmbeddedEntriesInConfig) {
		super(inSource);
	}

	async createEntriesResetPoints(): Promise<SiInputResetPoint[]> {
		const entries: Array<Promise<SiInputResetPoint>> = [];
		for (const embe of this.embes) {
			entries.push(embe.siEntry.createInputResetPoint());
		}
		return await Promise.all(entries);
	}

	writeEmbes(): void {
		const values = new Array<SiEmbeddedEntry>();

		for (const embe of this.embes) {
			if (embe.isPlaceholder()) {
				continue;
			}

			values.push(embe.siEmbeddedEntry);
		}

		this.inSource.setValues(values);
	}

	// fillWithPlaceholderEmbes() {
	// 	if (!this.config.allowedSiMaskQualifiers) {
	// 		return;
	// 	}

	// 	const min = this.config.min;
	// 	while (this.embes.length < min) {
	// 		this.createEmbe();
	// 	}
	// }

	removeEmbe(embe: Embe) {
		const i = this.embes.indexOf(embe);
		if (i < 0) {
			throw new Error('Unknown Embe');
		}

		this.embes.splice(i, 1);
		embe.clear();
		// this.unregisterEmbe(embe);
	}

	changeEmbePosition(oldIndex: number, newIndex: number) {
		const moveEmbe = this.embes[oldIndex];

		if (oldIndex < newIndex) {
			for (let i = oldIndex + 1; i <= newIndex; i++) {
				this.embes[i - 1] = this.embes[i];
			}
		}

		if (oldIndex > newIndex) {
			for (let i = oldIndex - 1; i >= newIndex; i--) {
				this.embes[i + 1] = this.embes[i];
			}
		}

		this.embes[newIndex] = moveEmbe;

		if ((new Set(this.embes)).size !== this.embes.length) {
			throw new IllegalSiStateError('embes array not unique: ' + (new Set(this.embes)).size + ' !== ' + this.embes.length);
		}
	}
}
