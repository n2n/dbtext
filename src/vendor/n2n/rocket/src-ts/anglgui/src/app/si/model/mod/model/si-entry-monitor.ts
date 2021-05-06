import { SiModStateService, SiModEvent } from './si-mod-state.service';
import { SiEntry, SiEntryState } from '../../content/si-entry';
import { SiService } from 'src/app/si/manage/si.service';
import { SiGetRequest } from '../../api/si-get-request';
import { SiGetInstruction } from '../../api/si-get-instruction';
import { IllegalArgumentError } from 'src/app/si/util/illegal-argument-error';
import { SiGetResponse } from '../../api/si-get-response';
import { SiGetResult } from '../../api/si-get-result';
import { Subscription } from 'rxjs';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { IllegalStateError } from 'src/app/util/err/illegal-state-error';

export class SiEntryMonitor {
	private entriesMap = new Map<string, SiEntry[]>();
	private nextReloadJob: ReloadJob;

	constructor(private apiGetUrl: string, private siService: SiService, private modStateService: SiModStateService,
			private controlsIncluded: boolean) {
		this.nextReloadJob = new ReloadJob(apiGetUrl, siService, controlsIncluded);
	}

	private subscription: Subscription|null = null;

	registerEntry(entry: SiEntry): void {
		const id = entry.identifier.id;
		if (!id) {
			throw new IllegalArgumentError('New entry can not be monitored.');
		}

		if (!this.entriesMap.has(id)) {
			this.entriesMap.set(id, []);
		}

		const entries = this.entriesMap.get(id);

		if (-1 < entries.indexOf(entry)) {
			throw new IllegalArgumentError('Entry already registered.');
		}

		entries.push(entry);
	}

	unregisterEntry(entry: SiEntry): void {
		const id = entry.identifier.id;
		if (!this.entriesMap.has(id)) {
			throw new IllegalStateError('Entry not registed.');
		}

		const entries = this.entriesMap.get(id);
		const i = entries.indexOf(entry);
		entries.splice(i, 1);

		this.nextReloadJob.removeSiEntry(entry);
	}

	get size(): number {
		let size = 0;
		this.entriesMap.forEach((value) => {
			size += value.length;
		});
		return size;
	}

	unregisterAllEntries(): void {
		this.entriesMap.clear();
		this.nextReloadJob.clear();
	}

	start(): void {
		if (this.subscription) {
			throw new IllegalSiStateError('Monitor already started.');
		}

		this.subscription = this.modStateService.modEvent$.subscribe((modEvent) => {
			if (this.handleEvent(modEvent).shownEntryUpdated) {
				this.executeNextReloadJob();
			}
		});

		this.subscription.add(this.modStateService.shownEntry$.subscribe((siEntry) => {
			if (this.nextReloadJob.containsSiEntry(siEntry)) {
				this.executeNextReloadJob();
			}
		}));
	}

	stop(): void {
		if (this.subscription) {
			this.subscription.unsubscribe();
		}

		this.subscription = null;
	}

	private handleEvent(modEvent: SiModEvent): { shownEntryUpdated: boolean } {
		for (const siEntryIdentifier of modEvent.removed || []) {
			const id = siEntryIdentifier.id;
			if (!this.entriesMap.has(id)) {
				continue;
			}

			for (const siEntry of this.entriesMap.get(id).filter(e => e.identifier.equals(siEntryIdentifier))) {
				siEntry.markAsRemoved();
			}
		}

		let shownEntryUpdated = false;

		for (const siEntryIdentifier of modEvent.updated || []) {
			const id = siEntryIdentifier.id;
			if (!this.entriesMap.has(id)) {
				continue;
			}

			for (const siEntry of this.entriesMap.get(id).filter(e => e.identifier.equals(siEntryIdentifier))) {
				siEntry.markAsOutdated();
				this.nextReloadJob.addSiEntry(siEntry);
				if (!shownEntryUpdated && this.modStateService.isEntryShown(siEntry)) {
					shownEntryUpdated = true;
				}
			}
		}

		return { shownEntryUpdated };
	}

	private executeNextReloadJob(): void {
		const rj = this.nextReloadJob;

		this.nextReloadJob = new ReloadJob(this.apiGetUrl, this.siService, this.controlsIncluded);

		rj.execute();
	}
}

class ReloadJob {
	private siEntriesMap = new Map<SiEntry, SiEntry>();

	constructor(private apiGetUrl: string, private siService: SiService, private controlsIncluded: boolean) {
	}

	containsSiEntry(entry: SiEntry): boolean {
		return this.siEntriesMap.has(entry);
	}

	addSiEntry(siEntry: SiEntry): void {
		this.siEntriesMap.set(siEntry, siEntry);
	}

	removeSiEntry(siEntry: SiEntry): void {
		this.siEntriesMap.delete(siEntry);
	}

	clear(): void {
		this.siEntriesMap.clear();
	}

	execute(): void {
		const getInstructions: SiGetInstruction[] = [];
		const entries: SiEntry[] = [];
		for (const [, entry] of this.siEntriesMap) {
			if (entry.state !== SiEntryState.OUTDATED) {
				continue;
			}

			entry.markAsReloading();
			getInstructions.push(SiGetInstruction.entry(entry.style, entry.identifier.id)
					.setEntryControlsIncluded(this.controlsIncluded));
			entries.push(entry);
		}

		this.siService.apiGet(this.apiGetUrl, new SiGetRequest(...getInstructions))
				.subscribe((response: SiGetResponse) => {
					this.handleResults(response.results, entries);
				});
	}

	private handleResults(results: SiGetResult[], entries: SiEntry[]): void {
		for (const i of results.keys()) {
			entries[i].replace(results[i].entry);
		}
	}
}
