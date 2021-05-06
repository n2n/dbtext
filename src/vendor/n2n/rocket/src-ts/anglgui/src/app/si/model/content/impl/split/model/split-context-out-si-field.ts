import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { OutSiFieldAdapter} from '../../common/model/out-si-field-adapter';
import { SplitContentCollection } from './split-content-collection';
import { SplitContext, SplitStyle } from './split-context';
import { SplitOption } from './split-option';
import { SiEntry } from '../../../si-entry';

export class SplitContextOutSiField extends OutSiFieldAdapter implements SplitContext {
	public style: SplitStyle = { iconClass: null, tooltip: null };
	readonly collection = new SplitContentCollection();

	isDisplayable(): boolean {
		return false;
	}

	protected createUiContent(): UiContent {
		throw new IllegalSiStateError('SiField not displayable');
	}

	async copyValue(): Promise<SiGenericValue> {
		return new SiGenericValue(await this.collection.copy());
	}

	getSplitOptions(): SplitOption[] {
		return this.collection.getSplitContents();
	}

	getEntry$(key: string): Promise<SiEntry|null> {
		return this.collection.getEntry$(key);
	}
}
