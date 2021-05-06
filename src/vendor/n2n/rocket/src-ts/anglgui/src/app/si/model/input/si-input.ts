
import { SiEntryInput } from 'src/app/si/model/input/si-entry-input';
import { SiDeclaration } from '../meta/si-declaration';

export class SiInput {

	constructor(public declaration: SiDeclaration, public entryInputs: SiEntryInput[] = []) {

	}

	toParamMap(): Map<string, string|Blob> {
		const map = new Map<string, string|Blob>();

		if (this.entryInputs.length === 0) {
			return map;
		}

		const entryInputMaps: Array<any> = [];

		for (const entryInput of this.entryInputs) {
			const fieldInputObj = {};

			for (const [propId, inputObj] of entryInput.fieldInputMap) {
				fieldInputObj[propId] = inputObj;
			}

			entryInputMaps.push(entryInput.toJSON());
		}

		map.set('entryInputMaps', JSON.stringify(entryInputMaps));

		return map;
	}
}
