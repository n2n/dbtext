import { SiEntry, SiEntryState } from 'src/app/si/model/content/si-entry';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { SiEntryMonitor } from '../../../mod/model/si-entry-monitor';
import { Subscription, Subject, Observable, BehaviorSubject } from 'rxjs';

export class SiPage {
	private _entrySubPairs: Array<{entry: SiEntry, subscription: Subscription}>|null = null;
	private _size: number|null = null;
	private _ghostSize: number|null = null;
	private entriesSubject = new BehaviorSubject<SiEntry[]|null>(null);
	private disposedSubject = new Subject<void>();
	private entryRemovedSubject = new Subject<SiEntry>();

	constructor(private entryMonitor: SiEntryMonitor, readonly no: number,
			public offset: number, entries: SiEntry[]|null) {
		if (no < 1) {
			throw new IllegalSiStateError('Illegal page no: ' + no);
		}

		if (entries) {
			this.applyEntries(entries);
		}

		this.recalcSize();
		this.triggerEntriesSubject();
	}

	get loaded(): boolean {
		this.ensureNotDisposed();

		return !!this._entrySubPairs;
	}

	get entries(): SiEntry[]|null {
		this.ensureNotDisposed();

		if (this._entrySubPairs) {
			return this._entrySubPairs.map(v => v.entry);
		}

		return null;
	}

	set entries(entries: SiEntry[]|null) {
		this.ensureNotDisposed();

		this.applyEntries(entries);
		this.triggerEntriesSubject();
	}

	private ensureLoaded() {
		if (this.loaded) {
			return;
		}

		throw new IllegalSiStateError('Page not loaded.');
	}

	get size(): number {
		this.ensureLoaded();

		if (this._size === null) {
			throw new IllegalSiStateError('Size not set.');
		}

		return this._size;
	}

	get ghostSize(): number {
		this.ensureLoaded();

		if (this._ghostSize === null) {
			throw new IllegalSiStateError('Ghost size not set.');
		}

		return this._ghostSize;
	}

	private recalcSize(): number {
		if (!this._entrySubPairs) {
			this._size = null;
			this._ghostSize = null;
			return;
		}

		this._size = 0;
		this._ghostSize = 0;
		for (const v of this._entrySubPairs) {
			if (v.entry.isAlive()) {
				this._size++;
			} else {
				this._ghostSize++;
			}
		}
	}

	get entries$(): Observable<SiEntry[]> {
		this.ensureNotDisposed();

		return this.entriesSubject.asObservable();
	}

	private removeEntries() {
		if (!this._entrySubPairs) {
			return;
		}

		for (const i of Array.from(this._entrySubPairs.keys()).reverse()) {
			this.removeEntryByIndex(i);
		}

		this._entrySubPairs = null;

		this.entryMonitor.unregisterAllEntries();
		this.entryMonitor.stop();

		this.recalcSize();
	}

	private applyEntries(newEntries: SiEntry[]|null) {
		this.removeEntries();

		if (!newEntries) {
			return;
		}

		this._entrySubPairs = [];
		for (const newEntry of newEntries) {
			this.placeEntry(this._entrySubPairs.length, newEntry);
		}

		this.entryMonitor.start();

		this.recalcSize();
	}

	removeEntry(siEntry: SiEntry) {
		this.ensureLoaded();

		const i = this.entries.indexOf(siEntry);

		if (i < 0) {
			throw new IllegalSiStateError('SiEntry does not exist: ' + siEntry.identifier.toString());
		}

		this.removeEntryByIndex(i);
	}

	removeEntryByIndex(i: number) {
		this.ensureLoaded();

		if (!this._entrySubPairs[i]) {
			throw new IllegalSiStateError('SiEntry index does not exist: ' + i);
		}

		this.releaseEntrySubPair(this._entrySubPairs[i]);

		this._entrySubPairs.splice(i, 1);
		this.triggerEntriesSubject();
	}

	private releaseEntrySubPair(v: { entry: SiEntry, subscription: Subscription }) {
		this.entryMonitor.unregisterEntry(v.entry);
		v.subscription.unsubscribe();
	}

	insertEntry(i: number, newEntry: SiEntry) {
		this._entrySubPairs.splice(i, 0, null);

		this.placeEntry(i, newEntry);
		this.triggerEntriesSubject();
	}

	private placeEntry(i: number, newEntry: SiEntry) {
		if (this._entrySubPairs[i]) {
			this.releaseEntrySubPair(this._entrySubPairs[i]);
		}

		const subscription = newEntry.state$.subscribe((state) => {
			const curI = this.entries.indexOf(newEntry);

			switch (state) {
				case SiEntryState.REPLACED:
					this.placeEntry(curI, newEntry.replacementEntry);
					break;
				case SiEntryState.REMOVED:
					this.recalcSize();
					this.entryRemovedSubject.next(newEntry);
					break;
			}
		});

		this._entrySubPairs[i] = {
			entry: newEntry,
			subscription
		};

		this.entryMonitor.registerEntry(newEntry);
	}

	get entryRemoved$(): Observable<SiEntry> {
		return this.entryRemovedSubject.asObservable();
	}

	private ensureNotDisposed() {
		if (this.disposed) {
			throw new IllegalSiStateError('SiPage disposed.');
		}
	}

	get disposed(): boolean {
		return this.disposedSubject.isStopped;
	}

	get disposed$(): Observable<void> {
		return this.disposedSubject.asObservable();
	}

	dipose() {
		if (this.disposed) {
			return;
		}

		this.removeEntries();
		this.triggerEntriesSubject();

		this.entryRemovedSubject.complete();
		this.disposedSubject.next();
		this.disposedSubject.complete();
		this.entriesSubject.complete();
	}

	private triggerEntriesSubject() {
		this.entriesSubject.next(this.entries);
	}
}
