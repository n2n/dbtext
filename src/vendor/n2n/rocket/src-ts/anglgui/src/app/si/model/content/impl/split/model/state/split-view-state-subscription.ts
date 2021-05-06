import { SplitViewStateContext } from './split-view-state-context';
import { SplitOption } from '../split-option';
import { Observable, Subject } from 'rxjs';
import { map } from 'rxjs/operators';

export class SplitViewStateSubscription {
	private visibleKeysChangedSubject = new Subject<string[]>();

	constructor(public splitViewStateContext: SplitViewStateContext, public splitOptions: SplitOption[]) {
		this.splitViewStateContext.getVisibleKeys$().subscribe(this.visibleKeysChangedSubject);
	}

	isKeyVisible(key: string): boolean {
		return this.splitViewStateContext.containsVisibleKey(key);
	}

	requestKeyVisibilityChange(key: string, visible: boolean) {
		if (visible) {
			this.splitViewStateContext.addVisibleKey(key);
		} else {
			this.splitViewStateContext.removeVisibleKey(key);
		}
	}

	cancel() {
		this.splitViewStateContext.removeSubscription(this);
		this.visibleKeysChangedSubject.complete();
	}

	get visibleKeysChanged$(): Observable<void> {
		return this.visibleKeysChangedSubject.pipe(map(() => { return; }));
	}
}
