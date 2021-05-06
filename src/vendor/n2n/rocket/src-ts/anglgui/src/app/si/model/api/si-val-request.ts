import { SiValInstruction } from './si-val-instruction';

export class SiValRequest {
	public instructions: SiValInstruction[];

	constructor(...instructions: SiValInstruction[]) {
		this.instructions = instructions;
	}
}
