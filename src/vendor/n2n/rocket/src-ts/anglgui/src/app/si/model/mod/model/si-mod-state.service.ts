import { Injectable } from '@angular/core';
import { SiEntryIdentifier } from '../../content/si-entry-qualifier';
import { SiEntry } from '../../content/si-entry';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { Subject, Observable, BehaviorSubject } from 'rxjs';
import { skip } from 'rxjs/operators';
import { Message } from 'src/app/util/i18n/message';

@Injectable({
	providedIn: 'root'
})
export class SiModStateService {

	private lastModEventSubject = new BehaviorSubject<SiModEvent>(null);
	private lastMessagesSubject = new BehaviorSubject<Message[]>([]);

	private shownEntriesMap = new Map<SiEntry, object[]>();
	private shownEntrySubject = new Subject<SiEntry>();

	constructor() {
	}

	pushModEvent(event: SiModEvent): void {
		this.lastModEventSubject.next(event);
	}

	pushMessages(messages: Message[]): void {
		this.lastMessagesSubject.next(messages);
	}

	get modEvent$(): Observable<SiModEvent> {
		return this.lastModEventSubject.pipe(skip(1));
	}

	get lastModEvent(): SiModEvent|null {
		return this.lastModEventSubject.getValue();
	}

	// containsModEntryIdentifier(ei: SiEntryIdentifier): boolean {
	// 	return this.lastModEvent && this.lastModEvent.containsModEntryIdentifier(ei);
	// }

	isEntryShown(entry: SiEntry): boolean {
		return this.shownEntriesMap.has(entry);
	}

	get shownEntry$(): Observable<SiEntry> {
		return this.shownEntrySubject.asObservable();
	}

	get lastMessages(): Message[] {
		return this.lastMessagesSubject.getValue();
	}

	get messages$(): Observable<Message[]> {
		return this.lastMessagesSubject.asObservable();
	}

	registerShownEntry(entry: SiEntry, refObj: object): void {
		if (!this.shownEntriesMap.has(entry)) {
			this.shownEntriesMap.set(entry, []);
		}

		const objects = this.shownEntriesMap.get(entry);
		if (-1 < objects.indexOf(refObj)) {
			throw new IllegalSiStateError('Entry already shown.');
		}

		objects.push(refObj);
		this.shownEntrySubject.next(entry);
	}

	unregisterShownEntry(entry: SiEntry, refObj: object): void {
		if (!this.shownEntriesMap.has(entry)) {
			throw new IllegalSiStateError('Entry not shown.');
		}

		const objects = this.shownEntriesMap.get(entry);
		const i = objects.indexOf(refObj);
		if (-1 === i) {
			throw new IllegalSiStateError('Entry not shown.');
		}

		objects.splice(i, 1);
	}
}

export class SiModEvent {

	private addedEventMap = new Map<string, Map<string, SiEntryIdentifier>>();
	private updatedEventMap = new Map<string, Map<string, SiEntryIdentifier>>();
	private removedEventMap = new Map<string, Map<string, SiEntryIdentifier>>();

	constructor(readonly added: SiEntryIdentifier[], readonly updated: SiEntryIdentifier[], readonly removed: SiEntryIdentifier[]) {
		this.update();
	}

	private update(): void {
		this.addedEventMap.clear();
		for (const ei of this.added) {
			this.reqEiMap(this.addedEventMap, ei.typeId).set(ei.id, ei);
		}

		this.updatedEventMap.clear();
		for (const ei of this.updated) {
			this.reqEiMap(this.updatedEventMap, ei.typeId).set(ei.id, ei);
		}

		this.removedEventMap.clear();
		for (const ei of this.removed) {
			this.reqEiMap(this.removedEventMap, ei.typeId).set(ei.id, ei);
		}
	}

	private reqEiMap(map: Map<string, Map<string, SiEntryIdentifier>>, typeId: string): Map<string, SiEntryIdentifier> {
		if (!map.has(typeId)) {
			map.set(typeId, new Map());
		}

		return map.get(typeId);
	}

	containsModEntryIdentifier(ei: SiEntryIdentifier): boolean {
		return (this.addedEventMap.has(ei.typeId) && this.addedEventMap.get(ei.typeId).has(ei.id))
				|| (this.updatedEventMap.has(ei.typeId) && this.updatedEventMap.get(ei.typeId).has(ei.id))
				|| (this.removedEventMap.has(ei.typeId) && this.removedEventMap.get(ei.typeId).has(ei.id));
	}

	containsAddedTypeId(typeId: string): boolean {
		return this.addedEventMap.has(typeId);
	}

	containsUpdatedTypeId(typeId: string): boolean {
		return this.updatedEventMap.has(typeId);
	}

	containsRemovedTypeId(typeId: string): boolean {
		return this.removedEventMap.has(typeId);
	}

}
