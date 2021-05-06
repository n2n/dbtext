import { SplitViewStateSubscription } from './split-view-state-subscription';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { SplitViewMenuComponent } from '../../comp/split-view-menu/split-view-menu.component';
import { SplitViewMenuModel } from '../../comp/split-view-menu-model';
import { SplitOption } from '../split-option';
import { BehaviorSubject, Observable } from 'rxjs';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { SplitStyle } from '../split-context';

export class SplitViewStateContext implements SplitViewMenuModel {

	private subscriptions: Array<SplitViewStateSubscription> = [];
	private optionMap = new Map<string, SplitOption>();
	private visibleKeys: string[];

	private viewMenuUc: UiContent|null = null;

	constructor(readonly uiZone: UiZone, public splitStyle: SplitStyle, private visibleKeysSubject: BehaviorSubject<string[]>) {
		this.visibleKeys = visibleKeysSubject.getValue();
	}

	createSubscription(options: SplitOption[]): SplitViewStateSubscription {
		const subscription = new SplitViewStateSubscription(this, options);
		this.subscriptions.push(subscription);
		this.updateStructure();
		return subscription;
	}

	removeSubscription(subscription: SplitViewStateSubscription): void {
		const i = this.subscriptions.indexOf(subscription);
		if (i === -1) {
			throw new Error('Subscription does not exist.');
		}

		this.subscriptions.splice(i, 1);
		this.updateStructure();
	}

	getSplitOptions(): SplitOption[] {
		return Array.from(this.optionMap.values());
	}

	getIconClass(): string|null {
		return this.splitStyle.iconClass;
	}

	getTooltip(): string|null {
		return this.splitStyle.tooltip;
	}

	// getVisibleKeys(): string[] {
	// 	return this.visibleKeys;
	// }

	getVisibleKeys$(): Observable<string[]> {
		return this.visibleKeysSubject.asObservable();
	}

	getVisibleKeysNum(): number {
		return this.visibleKeys.length;
	}

	containsVisibleKey(key: string) {
		return -1 < this.visibleKeys.indexOf(key);
	}

	addVisibleKey(key: string): void {
		const i = this.visibleKeys.indexOf(key);
		if (i > -1) {
			return;
		}

		this.visibleKeys.push(key);
		this.validateVisibleKeys(true);
	}

	removeVisibleKey(key: string): void {
		const i = this.visibleKeys.indexOf(key);
		if (i === -1) {
			return;
		}

		this.visibleKeys.splice(i, 1);
		this.validateVisibleKeys(true);
	}

	private validateVisibleKeys(triggerObsAnyway: boolean) {
		if (this.visibleKeys.length === 0 && this.optionMap.size > 0) {
			this.visibleKeys.push(this.optionMap.keys().next().value);
		} else if (!triggerObsAnyway) {
			return;
		}

		this.triggerVisibleKeysObs();
	}

	private triggerVisibleKeysObs() {
		this.visibleKeysSubject.next([...this.visibleKeys]);
	}

	private updateStructure() {
		const assigned = this.optionMap.size > 0;

		this.optionMap.clear();
		for (const subscription of this.subscriptions) {
			for (const splitOption of subscription.splitOptions) {
				this.optionMap.set(splitOption.key, splitOption);
			}
		}

		this.validateVisibleKeys(false);

		if (this.optionMap.size > 0) {
			if (!assigned) {
				this.viewMenuUc = new TypeUiContent(SplitViewMenuComponent, (ref) => {
					ref.instance.model = this;
				});
				this.uiZone.contextMenuContents.push(this.viewMenuUc);
			}

			return;
		}

		if (!assigned) {
			return;
		}

		const i = this.uiZone.contextMenuContents.indexOf(this.viewMenuUc);
		if (i > -1) {
			this.uiZone.contextMenuContents.splice(i, 1);
		}
		this.viewMenuUc = null;
	}
}
