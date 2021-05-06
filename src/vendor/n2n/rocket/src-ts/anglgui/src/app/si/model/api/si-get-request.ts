import { SiGetInstruction } from 'src/app/si/model/api/si-get-instruction';

export class SiGetRequest {
	public instructions: SiGetInstruction[];

	constructor(...getInstructions: SiGetInstruction[]) {
		this.instructions = getInstructions;
	}
}
