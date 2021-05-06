import { BehaviorSubject, Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { IllegalStateError } from '../err/illegal-state-error';

export class BehaviorCollection<T> {

	private subject: BehaviorSubject<T[]>;

	constructor(values: T[] = []) {
		this.subject = new BehaviorSubject<T[]>(values);
	}

	clear(): BehaviorCollection<T> {
		this.ensureNotDisposed();
		this.subject.next([]);
		return this;
	}

	push(...ts: T[]): BehaviorCollection<T> {
		this.ensureNotDisposed();
		const arr = this.subject.getValue();
		arr.push(...ts);
		this.subject.next(arr);
		return this;
	}

	get$(): Observable<T[]> {
		this.ensureNotDisposed();
		return this.subject.pipe(map(ts => [...ts]));
	}

	get(): T[] {
		this.ensureNotDisposed();
		return [...this.subject.getValue()];
	}

	set(ts: T[]) {
		this.ensureNotDisposed();
		this.subject.next([...ts]);
	}

	private ensureNotDisposed() {
		if (this.subject) {
			return;
		}

		throw new IllegalStateError('BehaviorCollection already disposed.');
	}

	get disposed(): boolean {
		return !this.subject;
	}

	dispose(): void {
		this.ensureNotDisposed();
		this.subject.complete();
		this.subject = null;
	}

	splice(start: number, deleteCount?: number, ...items: T[]): T[] {
		const value = this.subject.getValue();
		return value.splice(start, deleteCount, ...items);
	}

}
