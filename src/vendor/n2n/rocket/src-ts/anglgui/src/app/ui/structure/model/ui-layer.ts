import { Subject, Subscription } from 'rxjs';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { UiContainer } from './ui-container';
import { UiZone } from './ui-zone';
import { UiRoute } from './ui-route';
import { UnsupportedMethodError } from 'src/app/si/util/unsupported-method-error';
import { IllegalArgumentError } from 'src/app/si/util/illegal-argument-error';
import { IllegalStateError } from 'src/app/util/err/illegal-state-error';

export interface UiLayer {
	readonly container: UiContainer;
	readonly main: boolean;
	readonly currentRoute: UiRoute|null;
	readonly previousRoute: UiRoute|null;

	pushRoute(id: number|null, zoneUrl: string|null): UiRoute;

	switchRouteById(id: number): void;

	dispose(): void;
}

abstract class UiLayerAdapter implements UiLayer {
	private routes: Array<UiRoute> = [];
	private currentRouteIndex: number|null = null;
	private disposeSubject = new Subject<void>();

	constructor(readonly container: UiContainer) {
	}

	readonly abstract main: boolean;

	abstract pushRoute(id: number|null, zoneUrl: string|null): UiRoute;

	switchRouteById(id: number, verifyUrl: string = null): boolean {
		const index = this.getRouteIndexById(id);

		if (!this.routes[index]) {
			throw new IllegalSiStateError('Zone with id ' + id + ' does not exists. Verify url: ' + verifyUrl);
		}

		if (this.routes[index].zone.url !== verifyUrl) {
			// @todo temporary test to monitor angular routing behaviour
			throw new IllegalSiStateError('Zone pop url verify missmatch for id ' + id + ': '
					+ this.routes[index].zone.url + ' != ' + verifyUrl);
// 			return false;
		}

		this.currentRouteIndex = index;
		return true;
	}


	get currentRoute(): UiRoute|null {
		if (this.currentRouteIndex === null) {
			return null;
		}

		if (this.routes[this.currentRouteIndex]) {
			return this.routes[this.currentRouteIndex];
		}

		throw new IllegalSiStateError('Layer contains invalid current route');
	}

	get previousRoute(): UiRoute|null {
		if (this.currentRouteIndex === null || this.currentRouteIndex < 1) {
			return null;
		}

		if (this.routes[this.currentRouteIndex - 1]) {
			return this.routes[this.currentRouteIndex - 1];
		}

		throw new IllegalSiStateError('Layer contains invalid previous zone');
	}

	protected getRouteById(id: number): UiRoute|null {
		return this.routes.find(route => route.id === id) || null;
	}

	protected getRouteIndexById(id: number): number|null {
		const index = this.routes.findIndex(route => route.id === id);
		if (index === -1) {
			return null;
		}

		return index;
	}

	protected getRoutesByZone(zone: UiZone): UiRoute[] {
		return this.routes.filter((route: UiRoute) => {
			return route.zone === zone;
		});
	}

	protected createRoute(id: number, zoneUrl: string|null): UiRoute {
		if (!!this.getRouteById(id)) {
			throw new IllegalSiStateError('Route with id ' + id + ' already exists. Zone url: ' + zoneUrl);
		}

		if (this.currentRouteIndex !== null) {
			this.clearRoutesAfterIndex(this.currentRouteIndex);
		}

		const route = new UiRoute(id, this.getOrCreateZone(zoneUrl));

		this.currentRouteIndex = this.routes.push(route) - 1;
		route.onDispose(() => {
			this.removeRoute(route);
		});
		return route;
	}

	private findZoneByUrl(zoneUrl: string): UiZone|null {
		for (const route of this.routes.reverse()) {
			if (route.zone.url === zoneUrl) {
				return route.zone;
			}
		}

		return null;
	}

	private getOrCreateZone(zoneUrl: string|null) {
		const zone = this.findZoneByUrl(zoneUrl);
		if (zoneUrl && zone) {
			return zone;
		}

		return new UiZone(zoneUrl, this as any);
	}

	private clearRoutesAfterIndex(routeIndex: number) {
		for (const route of this.routes.slice(routeIndex + 1)) {
			route.dispose();
		}
	}

	private removeRoute(route: UiRoute) {
		const i = this.routes.indexOf(route);
		if (i === -1) {
			throw new IllegalStateError('Zone to remove doesn\'t exist on layer.');
		}

		this.routes.splice(i, 1);

		if (this.getRoutesByZone(route.zone).length === 0) {
			route.zone.dispose();
		}
	}

	dispose() {
		for (const zone of this.routes) {
			zone.dispose();
		}

		this.disposeSubject.next();
		this.disposeSubject.complete();
	}

	get routesNum(): number {
		return this.routes.length;
	}

	get disposed(): boolean {
		return this.disposeSubject.closed;
	}

	onDispose(callback: () => any): Subscription {
		return this.disposeSubject.subscribe(callback);
	}

	changeCurrentRouteId(newId: number) {
		if (this.getRouteById(newId)) {
			throw new IllegalSiStateError('Route with id ' + newId + ' already exists.');
		}

		this.currentRoute.id = newId;
	}
}


export class MainUiLayer extends UiLayerAdapter {
	readonly main = true;

	constructor(container: UiContainer) {
		super(container);
	}

	pushRoute(id: number|null, url: string|null): UiRoute {
		if (id === null || id === undefined || url === null || url === undefined) {
			throw new IllegalArgumentError('Id and url required for main layer routes.');
		}

		return this.createRoute(id, url);
	}

	dispose(): void {
		throw new UnsupportedMethodError('Main layer does not support such action.');
	}

	onDispose(callback: () => any): Subscription {
		throw new UnsupportedMethodError('Main layer does not support such action.');
	}
}

export class PopupUiLayer extends UiLayerAdapter {
	readonly main = false;

	constructor(container: UiContainer) {
		super(container);
	}

	pushRoute(id: number|null, url: string|null): UiRoute {
		return this.createRoute(this.routesNum, url);
	}
}
