import { Injectable } from '@angular/core';

@Injectable({
	providedIn: 'root'
})
export class TranslationService {

	map = new Map<string, string>();

	translate(code: string, args: Map<string, string>|null = null): string {
		let str = this.map.get(code) || code;

		if (args === null) {
			return str;
		}

		for (const [key, value] of args) {
			str = str.replace(key, value);
		}

		return str;
	}
}
