
import { SiEntryInput } from '../input/si-entry-input';
import { SiValGetInstruction } from './si-val-get-instruction';

export class SiValInstruction {

	public getInstructions: SiValGetInstruction[];

	constructor(public entryInput: SiEntryInput, ...getInstructions: SiValGetInstruction[]) {
		this.getInstructions = getInstructions;
	}
}

// export interface SiPartialContentInstruction {
// 	offset: number;
// 	num: number;
// }
