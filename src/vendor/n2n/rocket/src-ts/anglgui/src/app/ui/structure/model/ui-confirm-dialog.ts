import { Observable, Subject } from 'rxjs';

export class UiConfirmDialog {
	private subject = new Subject<boolean>();

	constructor(public message: string|null, public okLabel: string|null, public cancelLabel: string|null,
			public danger: boolean = false) {
	}

	get confirmed$(): Observable<boolean> {
		return this.subject.asObservable();
	}

	ok() {
		this.subject.next(true);
		this.subject.complete();
	}

	cancel() {
		this.subject.next(false);
		this.subject.complete();
	}
}


export enum UiConfirmSeverity {

}