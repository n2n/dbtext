import { Injectable } from '@angular/core';
import { SplitViewStateContext } from './split-view-state-context';
import { SplitViewStateSubscription } from './split-view-state-subscription';
import { SplitOption } from '../split-option';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';
import { SplitStyle } from '../split-context';
import { BehaviorSubject } from 'rxjs';

@Injectable({
	providedIn: 'root'
})
export class SplitViewStateService {
	private contexts = new Array<SplitViewStateContext>();
	private visibleKeysSubject = new BehaviorSubject<string[]>([]);

	constructor() {
	}

	subscribe(uiZone: UiZone, splitOptions: SplitOption[], splitStyle: SplitStyle): SplitViewStateSubscription {
		const context = this.getOrCreateContext(uiZone, splitStyle);

		return context.createSubscription(splitOptions);
	}

	private getOrCreateContext(uiZone: UiZone, splitStyle: SplitStyle): SplitViewStateContext {
		let context = this.contexts.find((iContext) => {
			return iContext.uiZone === uiZone;
		});

		if (context) {
			return context;
		}

		context = new SplitViewStateContext(uiZone, splitStyle, this.visibleKeysSubject);
		this.contexts.push(context);

		uiZone.onDispose(() => {
			this.removeContext(context);
		});

		return context;
	}

	private removeContext(context: SplitViewStateContext): void {
		const i = this.contexts.indexOf(context);
		if (i === -1) {
			throw new Error('Unknown SplitViewStateContext.');
		}

		this.contexts.splice(i, 1);
	}
}
