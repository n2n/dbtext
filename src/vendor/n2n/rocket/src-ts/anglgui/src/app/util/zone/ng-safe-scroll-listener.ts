import { Subscription, Observable, fromEvent } from 'rxjs';
import { NgZone } from '@angular/core';
import { throttleTime } from 'rxjs/operators';

export class NgSafeScrollListener {

	constructor(private containerElem: Element, private ngZone: NgZone) {
	}

	trottled$(ms: number): Observable<void> {
		return new Observable((subscriber) => {
			let scrollSubscription: Subscription;

			this.ngZone.runOutsideAngular(() => {
				scrollSubscription = fromEvent<MouseEvent>(this.containerElem, 'scroll')
						.pipe(throttleTime(ms, undefined, { leading: true, trailing: true }))
						.subscribe(() => {
							this.ngZone.run(() => {
								subscriber.next();
							});
						});
			});

			return {
				unsubscribe: () => {
					scrollSubscription.unsubscribe();
				}
			};
		});
	}

}
