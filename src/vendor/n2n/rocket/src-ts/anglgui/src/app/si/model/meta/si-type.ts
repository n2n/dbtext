
import { SiMaskQualifier } from 'src/app/si/model/meta/si-mask-qualifier';
import { SiProp } from './si-prop';
import { IllegalSiStateError } from '../../util/illegal-si-state-error';

export class SiMask {
	private propMap = new Map<string, SiProp>();

	constructor(readonly qualifier: SiMaskQualifier) {
	}

	addProp(prop: SiProp) {
		this.propMap.set(prop.id, prop);
	}

	containsPropId(propId: string): boolean {
		return this.propMap.has(propId);
	}

	getPropById(propId: string): SiProp {
		if (this.containsPropId(propId)) {
			return this.propMap.get(propId);
		}

		throw new IllegalSiStateError('Unknown prop id: ' + propId);
	}

	getProps(): SiProp[] {
		return Array.from(this.propMap.values());
	}
}
