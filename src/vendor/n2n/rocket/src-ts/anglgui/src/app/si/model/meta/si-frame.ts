import { SiTypeContext } from './si-type-context';
import { IllegalSiStateError } from '../../util/illegal-si-state-error';

export class SiFrame {
	public sortable = false;

	constructor(public apiUrlMap: Map<string, string>, public typeContext: SiTypeContext) {
	}

	getApiUrl(apiSection: SiFrameApiSection) {
		if (this.apiUrlMap.has(apiSection)) {
			return this.apiUrlMap.get(apiSection);
		}

		throw new IllegalSiStateError('No api url given for section: ' + apiSection)
	}
}

export enum SiFrameApiSection {
	CONTROL = 'execcontrol',
	FIELD = 'callfield',
	GET = 'get',
	VAL = 'val',
	SORT = 'sort'
}
