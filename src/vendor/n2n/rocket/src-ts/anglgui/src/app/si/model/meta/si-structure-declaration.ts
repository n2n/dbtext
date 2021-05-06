import { IllegalSiStateError } from '../../util/illegal-si-state-error';
import { SiProp } from './si-prop';


export class SiStructureDeclaration {

	constructor(readonly prop: SiProp|null, readonly label: string|null, public type: UiStructureType|null,
			public children: SiStructureDeclaration[] = []) {

		if (!!this.prop && !!this.prop === !!this.label) {
			throw new IllegalSiStateError('Label and label cannot be set a the same time.');
		}
	}
}

export enum UiStructureType {
	SIMPLE_GROUP = 'simple-group',
	MAIN_GROUP = 'main-group',
	// AUTONOMIC_GROUP = 'autonomic-group',
	LIGHT_GROUP = 'light-group',
	PANEL = 'panel',
	ITEM = 'item',
	MINIMAL = 'minimal'
}

export class UiStructureTypeUtils {
	static isGroup(uiStructureType: UiStructureType): boolean {
		return uiStructureType === UiStructureType.LIGHT_GROUP || uiStructureType === UiStructureType.MAIN_GROUP
				|| uiStructureType === UiStructureType.SIMPLE_GROUP;
	}
}
