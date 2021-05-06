namespace Rocket.Cmd {
	import display = Rocket.Display;
	
	export class Layer implements Jhtml.CompHandler {
		private _zones: Array<Zone> = new Array<Zone>();
		private onNewZoneCallbacks: Array<ZoneCallback>;
		private onNewHistoryEntryCallbacks: Array<HistoryCallback>;
		private callbackRegistery: Rocket.Util.CallbackRegistry<LayerCallback> = new Rocket.Util.CallbackRegistry<LayerCallback>();
		private _visible: boolean = true;
		
		constructor(private jqLayer: JQuery<Element>, private _level: number, private _container: Container, 
				private _monitor: Jhtml.Monitor) {
			this.onNewZoneCallbacks = new Array<ZoneCallback>();
			this.onNewHistoryEntryCallbacks = new Array<HistoryCallback>();

			var zoneJq = jqLayer.children(".rocket-zone:first");
			if (zoneJq.length > 0) {
				let url = Jhtml.Url.create(window.location.href);
				var zone = new Zone(zoneJq, url, this);
				let page = this.monitor.history.currentPage;
				page.promise = this.createPromise(zone);
				zone.page = page;
				this.addZone(zone);
			}

			this.monitor.history.onChanged(() => this.historyChanged() );
			this.monitor.registerCompHandler("rocket-page", this);
			this.historyChanged();
		}
		
		get jQuery(): JQuery<Element> {
			return this.jqLayer;
		}
		
		get monitor(): Jhtml.Monitor {
			return this._monitor;
		}
		
		containsUrl(url: Jhtml.Url): boolean {
			for (var i in this._zones) {
				if (this._zones[i].containsUrl(url)) return true;
			}
			
			return false;
		}
		
		public getZoneByUrl(urlExpr: string|Jhtml.Url): Zone {
			let url = Jhtml.Url.create(urlExpr);
			
			for (let i in this._zones) {
				if (this._zones[i].containsUrl(url)) {
					return this._zones[i];
				}
			}
	
			return null;
		}
		
		private historyChanged() {
			let currentEntry: Jhtml.History.Entry = this.monitor.history.currentEntry;
			if (!currentEntry) return;
			
			let page = currentEntry.page;
			let zone: Zone = this.getZoneByUrl(page.url);
			if (!zone) {
				zone = this.createZone(page.url)
				zone.clear(true);
			}
			
			if (!zone.page) {
				zone.page = page;
			}
			this.switchToZone(zone);
		}

		public createZone(urlExpr: string|Jhtml.Url): Zone {
			let url = Jhtml.Url.create(urlExpr);
			
			if (this.containsUrl(url)) {
				throw new Error("Page with url already available: " + url);
			}
			
			var jqZone = $("<div />");
			this.jqLayer.append(jqZone);
			var zone = new Zone(jqZone, url, this);
			this.addZone(zone);
			
			return zone;
		}

		get currentZone(): Zone {
			if (this.empty || !this._monitor.history.currentEntry) {
				return null;
			}

			var url = this._monitor.history.currentPage.url;
			
			for (var i in this._zones) {
				if (this._zones[i].containsUrl(url)) {
					return this._zones[i];
				} 
			}
				
			return null;
		}
		
		get container(): Container {
			return this._container;
		}
		
		get visible(): boolean {
			return this._visible;
		}
		
		private trigger(eventType: Layer.EventType) {
			var layer = this;
			this.callbackRegistery.filter(eventType.toString())
					.forEach(function (callback: LayerCallback) {
						callback(layer);
					});
		}
		
		on(eventType: Layer.EventType, callback: LayerCallback) {
			this.callbackRegistery.register(eventType.toString(), callback);
		}
		
		off(eventType: Layer.EventType, callback: LayerCallback) {
			this.callbackRegistery.unregister(eventType.toString(), callback);
		}		
		
		show() {
			this._visible = true;
			this.jqLayer.show();
			
			this.trigger(Layer.EventType.SHOWED);
		}
		
		hide() {
			this._visible = false;
			this.jqLayer.hide();
			
			this.trigger(Layer.EventType.HIDDEN);
		}
		
		get level(): number {
			return this._level;
		}
		
		get empty(): boolean {
			return this._zones.length == 0;
		}
		
		get zones(): Array<Zone> {
			return this._zones.slice();
		}
				
		private addZone(zone: Zone) {
			this._zones.push(zone);
			var that = this;
			
			zone.on(Zone.EventType.CLOSE, function (zone: Zone) {
				for (var i in that._zones) {
					if (that._zones[i] !== zone) continue;
					
					that._zones.splice(parseInt(i), 1);
					break;
				}
			});
			
			for (var i in this.onNewZoneCallbacks) {
				this.onNewZoneCallbacks[i](zone);
			}
		}
		
		private scrollPos: number = 0;
		
		set active(active: boolean) {
			if (active == this.active) return;
			
			if (this.monitor) {
				this.monitor.active = active;
			}
			
			if (active) {
				this.jqLayer.addClass("rocket-active");
				$(window).scrollTop(this.scrollPos);
				return;
			}
			
			this.scrollPos = $(window).scrollTop();
			this.jqLayer.removeClass("rocket-active");
		}
		
		get active() {
			return this.jqLayer.hasClass("rocket-active");
		}
		
		public onNewZone(onNewPageCallback: ZoneCallback) {
			this.onNewZoneCallbacks.push(onNewPageCallback);
		}
		
		public clear() {
			for (var i in this._zones) {
				this._zones[i].close();
			}
		}
		
		public close() {
			this.trigger(Layer.EventType.CLOSE);
			
			let zone = null;
			while (zone = this._zones.pop()) {
				zone.close();
			}
			this.jqLayer.remove();
		}
		
		private switchToZone(zone: Zone) {
			for (var i in this._zones) {
				if (this._zones[i] === zone) {
					zone.show();
				} else {
					this._zones[i].hide();
				}
			}
		}
		
		attachComp(comp: Jhtml.Comp): boolean {
			if (comp.isAttached) return true;
			
//			if (!comp.model.response) {
//				throw new Error("model response undefined");
//			}
			
			let url = this.monitor.history.currentPage.url;
			let zone: Zone = this.getZoneByUrl(url);
			if (!zone) {
				throw new Error("Zone for url " + url + " does not extist");
			}
				
			zone.applyComp(comp);
			return true;
		}
		
		detachComp(comp: Jhtml.Comp): boolean {
			return true;
		}
		
		pushHistoryEntry(urlExpr: Jhtml.Url|string) {
			let url: Jhtml.Url = Jhtml.Url.create(urlExpr);
			let history = this.monitor.history;
			
			let page = history.getPageByUrl(url);
			if (page) {
				history.push(page);
				return;
			}

			let zone: Zone = this.getZoneByUrl(url);
			if (zone) {
				page = new Jhtml.Page(url, this.createPromise(zone));
				history.push(page);
				return;
			}
			
			history.push(new Jhtml.Page(url, null));
		}
		
		private createPromise(zone: Zone): Promise<Jhtml.Directive> {
			return new Promise((resolve: any) => {
				resolve({
					getAdditionalData(): any {
						return null;
					},
					exec() {
						zone.layer.switchToZone(zone);
					}
				});
			});
		}
		
		
		
//		public currentHistoryIndex(): number {
//			return this._currentHistoryIndex;
//		}
		
//		public pushHistoryEntry(urlExpr: string|Url) {
//			var url: Url = Url.create(urlExpr);
//			var context: Page = this.getPageByUrl(url);
//			if (context === null) {
//				throw new Error("Not context with this url found: " + url);
//			}
//			
//			this._currentHistoryIndex = this.historyUrls.length;
//			this.historyUrls.push(url);
//			context.activeUrl = url;
//			
//			for (var i in this.onNewHistoryEntryCallbacks) {
//				this.onNewHistoryEntryCallbacks[i](this._currentHistoryIndex, url, context);
//			}
//			
//			this.switchToPage(context);
//		}
		
//		get currentHistoryEntryUrl(): Url {
//			return this.historyUrls[this._currentHistoryIndex];
//		}
//		
//		public go(historyIndex: number, urlExpr: string|Url) {
//			var url = Url.create(urlExpr);
//			
//			if (this.historyUrls.length < (historyIndex + 1)) {
//				throw new Error("Invalid history index: " + historyIndex);
//			}
//			
//			if (this.historyUrls[historyIndex].equals(url)) {
//				throw new Error("Url missmatch for history index " + historyIndex + ". Url: " + url + " History url: " 
//						+ this.historyUrls[historyIndex]);
//			}
//			
//			this._currentHistoryIndex = historyIndex;
//			var context = this.getPageByUrl(this.historyUrls[historyIndex]);
//			if (context === null) return false;
//			
//			this.switchToPage(context);
//			return true;
//		}
//		
//		public getHistoryUrlByIndex(historyIndex: number): Url {
//			if (this.historyUrls.length <= historyIndex) return null;
//			
//			return this.historyUrls[historyIndex];
//		}
//		
//		
//		
//		
//		public onNewHistoryEntry(onNewHistoryEntryCallback: HistoryCallback) {
//			this.onNewHistoryEntryCallbacks.push(onNewHistoryEntryCallback);
//		}

		public static create(jqLayer: JQuery<Element>, _level: number, _container: Container, history: Jhtml.History) {
			if (Layer.test(jqLayer)) {
				throw new Error("Layer already bound to this element.");
			}
			
			jqLayer.addClass("rocket-layer");
			jqLayer.data("rocketLayer", this);
		}
		
		private static test(jqLayer: JQuery<Element>): Layer {
			var layer = jqLayer.data("rocketLayer");
			if (layer instanceof Layer) {
				return layer;
			}
			
			return null;
		}
		
		public static of(jqElem: JQuery<Element>): Layer {
			if (!jqElem.hasClass(".rocket-layer")) {
				jqElem = jqElem.closest(".rocket-layer");
			}
			
			var layer = Layer.test(jqElem);
			if (layer === undefined) {
				return null;
			}
			
			return layer;
		}
	}
	
	interface HistoryCallback {
		(index: number, url: Jhtml.Url, context: Zone): any
	}
	
	
	export interface LayerCallback {
		(layer: Layer): any
	}
	
	export namespace Layer {
		export enum EventType {
			SHOWED /*= "show"*/,
			HIDDEN /*= "hide"*/,
			CLOSE /*= "close"*/
		}
	}
}