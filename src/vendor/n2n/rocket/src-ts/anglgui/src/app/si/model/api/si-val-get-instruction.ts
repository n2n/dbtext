
import { SiDeclaration } from '../meta/si-declaration';
import { SiStyle } from '../meta/si-view-mode';

export class SiValGetInstruction {

	protected declaration: SiDeclaration|null = null;
	protected controlsIncluded = false;

	constructor(public style: SiStyle) {
	}

	// static create(bulky: boolean, readOnly: boolean): SiValGetInstruction {
	// 	return new SiValGetInstruction({ bulky, readOnly });
	// }

	static create(style: SiStyle): SiValGetInstruction {
		return new SiValGetInstruction(style);
	}

	static createFromDeclaration(declaration: SiDeclaration): SiValGetInstruction {
		const instruction = new SiValGetInstruction(declaration.style);
		instruction.declaration = declaration;
		return instruction;
	}

	getDeclaration(): SiDeclaration|null {
		return this.declaration;
	}

	// setDeclaration(declaration: SiDeclaration): SiValGetInstruction {
	// 	this.declaration = declaration;
	// 	return this;
	// }

	setControlsIncluded(controlsIncluded: boolean): SiValGetInstruction {
		this.controlsIncluded = controlsIncluded;
		return this;
	}

	toJSON(): object {
		return {
			style: this.style,
			declarationRequested: !this.declaration,
			controlsIncluded: this.controlsIncluded
		};
	}
}

// export interface SiPartialContentInstruction {
// 	offset: number;
// 	num: number;
// }
