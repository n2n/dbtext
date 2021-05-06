import { SiValGetResult } from './si-val-get-result';

export class SiValResult {
	readonly getResults: SiValGetResult[] = [];

	constructor(public valid: boolean) {
	}
}
