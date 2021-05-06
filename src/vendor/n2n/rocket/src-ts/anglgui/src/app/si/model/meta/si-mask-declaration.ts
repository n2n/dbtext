
import { SiMask } from './si-type';
import { SiStructureDeclaration } from './si-structure-declaration';
import { SiProp } from './si-prop';

export class SiMaskDeclaration {

	constructor(public type: SiMask, public structureDeclarations: Array<SiStructureDeclaration>|null) {
	}

	getSiProps(): SiProp[] {
		// return this.type.getProps();
		return this.structureDeclarations.filter(sd => !!sd.prop).map(sd => sd.prop);
	}
}
