
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { SiMaskDeclaration } from './si-mask-declaration';
import { SiStyle } from './si-view-mode';

export class SiDeclaration {
	private maskDeclarationMap = new Map<string, SiMaskDeclaration>();

	constructor(public style: SiStyle) {
	}

	constainsTypeId(typeId: string): boolean {
		return this.maskDeclarationMap.has(typeId);
	}

	addTypeDeclaration(maskDeclaration: SiMaskDeclaration): void {
		this.maskDeclarationMap.set(maskDeclaration.type.qualifier.identifier.id, maskDeclaration);
	}

	getBasicTypeDeclaration(): SiMaskDeclaration {
		// if (this.basicSiMaskDeclaration) {
		// 	return this.basicSiMaskDeclaration;
		// }

		const value = this.maskDeclarationMap.values().next();
		if (value) {
			return value.value;
		}

		throw new IllegalSiStateError('SiDeclaration contains no SiMaskDeclaration.');
	}

	containsTypeId(typeId: string): boolean {
		return this.maskDeclarationMap.has(typeId);
	}

	getTypeDeclarationByTypeId(typeId: string): SiMaskDeclaration {
		if (this.maskDeclarationMap.has(typeId)) {
			return this.maskDeclarationMap.get(typeId);
		}

		throw new IllegalSiStateError('Unkown typeId: ' + typeId);
	}
}
