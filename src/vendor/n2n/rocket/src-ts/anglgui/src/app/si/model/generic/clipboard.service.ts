import { Injectable } from '@angular/core';
import { SiGenericValue } from './si-generic-value';
import { Subject, Observable } from 'rxjs';

@Injectable({
	providedIn: 'root'
})
export class ClipboardService {

	private genericValues: SiGenericValue[] = [];
	private changedSubject = new Subject<void>();

	constructor() { }

	add(genericValue: SiGenericValue) {
		this.genericValues.push(genericValue);
		this.changedSubject.next();
	}

	remove(genericValue: SiGenericValue) {
		const i = this.genericValues.indexOf(genericValue);
		if (i !== -1) {
			this.genericValues.splice(i, 1);
			this.changedSubject.next();
		}
	}

	has(genericValue: SiGenericValue): boolean {
		return -1 !== this.genericValues.indexOf(genericValue);
	}

	filterValue<T>(type: new(...args: any[]) => T): T[] {
		return this.genericValues.filter(genericValue => genericValue.isInstanceOf(type))
				.map(genericValue => genericValue.readInstance(type));
	}

	clear() {
		if (this.genericValues.length === 0) {
			return;
		}

		this.genericValues = [];
		this.changedSubject.next();
	}

	get changed$(): Observable<void> {
		return this.changedSubject;
	}
}
