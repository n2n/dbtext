import { Injectable } from '@angular/core';
import { PlatformLocation } from '@angular/common';
import { IllegalStateError } from '../err/illegal-state-error';

@Injectable({
	providedIn: 'root'
})
export class PlatformService {

	constructor(private platformLocation: PlatformLocation) { }

	routerUrl(url: string): string {
		const baseHref = this.platformLocation.getBaseHrefFromDOM();

		if (url.startsWith(baseHref)) {
			return url.substring(baseHref.length);
		}

		url = new URL(url).pathname;
		if (url.startsWith(baseHref)) {
			return url.substring(baseHref.length);
		}

		throw new IllegalStateError('Ref url must start with base href: ' + url);
	}

	hrefUrl(routerUrl: string): string {
		return this.platformLocation.getBaseHrefFromDOM() + routerUrl;
	}
}
