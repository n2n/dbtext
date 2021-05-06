import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { SiEntry } from '../../../si-entry';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { SplitManagerComponent } from '../comp/split-manager/split-manager.component';
import { SplitManagerModel } from '../comp/split-manager-model';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { BehaviorSubject, Observable } from 'rxjs';
import {InSiFieldAdapter} from '../../common/model/in-si-field-adapter';
import {SplitContentCollection} from './split-content-collection';
import {SplitContextCopy} from './split-context-copy';
import { SiInputResetPoint } from '../../../si-input-reset-point';
import { ManagableSplitContext, SplitStyle } from './split-context';
import { SplitOption } from './split-option';

export class SplitContextInSiField extends InSiFieldAdapter implements SplitManagerModel, ManagableSplitContext {
	readonly collection = new SplitContentCollection();
	style: SplitStyle = { iconClass: null, tooltip: null };
	managerStyle: SplitStyle = { iconClass: null, tooltip: null };
	private activeKeysSubject = new BehaviorSubject<string[]>([]);
	mandatoryKeys = new Array<string>();
	min: number;

	hasInput(): boolean {
		return true;
	}

	readInput(): object {
		const entryInputObj = {};
		for (const splitContent of this.collection.getSplitContents()) {
			let entry: SiEntry;
			if (entry = splitContent.getLoadedSiEntry()) {
				entryInputObj[splitContent.key] = entry.readInput();
			}
		}
		return {
			activeKeys: this.activeKeys,
			entryInputs: entryInputObj
		};
	}

	createInputResetPoint(): Promise<SiInputResetPoint> {
		return this.collection.createInputResetPoint(this);
	}

	async copyValue(): Promise<SiGenericValue> {
		return new SiGenericValue(await this.collection.copy());
	}

	async pasteValue(genericValue: SiGenericValue): Promise<boolean> {
		if (!genericValue.isInstanceOf(SplitContextCopy)) {
			return false;
		}

		return this.collection.paste(genericValue.readInstance(SplitContextCopy));
	}

	protected createUiContent(): UiContent {
		return new TypeUiContent(SplitManagerComponent, (ref) => {
			ref.instance.model = this;
		});
	}

	get activeKeys(): string[] {
		return this.activeKeysSubject.getValue();
	}

	set activeKeys(activesKeys: string[]) {
		this.activeKeysSubject.next(activesKeys);
	}

	get activeKeys$(): Observable<string[]> {
		return this.activeKeysSubject.asObservable();
	}

	isKeyMandatory(key: string): boolean {
		return -1 < this.mandatoryKeys.indexOf(key)
				|| (this.activeKeys.length <= this.min && this.isKeyActive(key));
	}

	isKeyActive(key: string): boolean {
		if (-1 < this.activeKeys.indexOf(key)) {
			return true;
		}

		if (this.activeKeys.length < this.min && this.collection.containsKey(key)) {
			this.activeKeys.push(key);
			this.triggetActiveKeysSubject();
			return true;
		}

		return false;
	}

	activateKey(key: string): void {
		if (!this.collection.containsKey(key)) {
			throw new Error('Unknown key: ' + key);
		}

		if (!this.isKeyActive(key)) {
			this.activeKeys.push(key);
			this.triggetActiveKeysSubject();
		}
	}

	deactivateKey(key: string): void {
		const i = this.activeKeys.indexOf(key);

		if (i > -1) {
			this.activeKeys.splice(i, 1);
			this.triggetActiveKeysSubject();
		}
	}

	private triggetActiveKeysSubject(): void {
		this.activeKeysSubject.next([...this.activeKeys]);
	}

	getSplitOptions(): SplitOption[] {
		return this.collection.getSplitContents();
	}
	
	getEntry$(key: string): Promise<SiEntry|null> {
		return this.collection.getEntry$(key);
	}

	getIconClass(): string {
		return this.managerStyle.iconClass || 'fas fa-columns';
	}

	getTooltip(): string|null {
		return this.managerStyle.tooltip;
	}
}
