/// <reference path="../../util/Util.ts" />
/// <reference path="../../display/StructureElement.ts" />

namespace Rocket.Cmd {
	import display = Rocket.Display;
	import util = Rocket.Util;
	
	export class Zone {
		private jqZone: JQuery<Element>;
		private _activeUrl: Jhtml.Url;
		private urls: Array<Jhtml.Url> = [];
		private _layer: Layer;
		private callbackRegistery: util.CallbackRegistry<ZoneCallback> = new util.CallbackRegistry<ZoneCallback>();
		private additionalTabManager: AdditionalTabManager;
		private _menu: Menu;
		private _messageList: MessageList;
		private _blocked: boolean = false;
		
		private _page: Jhtml.Page = null;
	
		private _lastModDefs: LastModDef[] = [];
	
		constructor(jqZone: JQuery<Element>, url: Jhtml.Url, layer: Layer) {
			this.jqZone = jqZone;
			this.urls.push(this._activeUrl = url);
			this._layer = layer;
			
			jqZone.addClass("rocket-zone");
			jqZone.data("rocketZone", this);

			this.reset();
			this.hide();
		}
		
		get layer(): Layer {
			return this._layer;
		}
		
		get jQuery(): JQuery<Element> {
			return this.jqZone;
		}
		
		get page(): Jhtml.Page|null {
			return this._page;
		}
		
		set page(page: Jhtml.Page) {
			if (this._page) {
				throw new Error("page already assigned");
			}
			
			this._page = page;
//			page.config.keep = true;
			
			if (page) {
				this.registerPageListeners();
			}
		}
		
		private onDisposed: () => any;
		private onPromiseAssigned: () => any;
		
		private registerPageListeners() {
			this.page.on("disposed", this.onDisposed = () => {
//				if (this.layer.currentZone === this) return;
				
				this.clear(true);
			});
			this.page.on("promiseAssigned", this.onPromiseAssigned = () => {
				this.clear(true);
			});	
		}
		
		private unregisterPageListeners() {
			if (this.onDisposed) {
				this.page.off("disposed", this.onDisposed);
			}
			
			if (this.onPromiseAssigned) {
				this.page.off("promiseAssigned", this.onPromiseAssigned);
			}
		}
		
		containsUrl(url: Jhtml.Url): boolean {
			for (var i in this.urls) {
				if (this.urls[i].equals(url)) return true;
			}
			
			return false;
		}
		
//		registerUrl(url: Url) {
//			if (this.containsUrl(url)) return;
//			
//			if (this._layer.containsUrl(url)) {
//				throw new Error("Url already registered for another Page of the current Layer."); 
//			}
//			
//			this.urls.push(url);
//		}
//		
//		unregisterUrl(url: Url) {
//			if (this.activeUrl.equals(url)) {
//				throw new Error("Cannot remove active url");
//			}
//			
//			for (var i in this.urls) {
//				if (this.urls[i].equals(url)) {
//					this.urls.splice(parseInt(i), 1);
//				}
//			}
//		}
		
		get activeUrl(): Jhtml.Url {
			return this._activeUrl;
		}
		
//		set activeUrl(activeUrl: Url) {
//			Rocket.util.ArgUtils.valIsset(activeUrl !== null)
//			
//			if (this._activeUrl.equals(activeUrl)) {
//				return;
//			}
//			
//			if (this.containsUrl(activeUrl)) {
//				this._activeUrl = activeUrl;
//				this.fireEvent(Page.EventType.ACTIVE_URL_CHANGED);
//				return;
//			}
//			
//			throw new Error("Active url not available for this context.");
//		}
		
		private fireEvent(eventType: Zone.EventType) {
			var that = this;
			this.callbackRegistery.filter(eventType.toString()).forEach(function (callback: ZoneCallback) {
				callback(that);
			});
		}
		
		private ensureNotClosed() {
			if (this.jqZone !== null) return;
			
			throw new Error("Page already closed.");
		}
		
		get closed(): boolean {
			return !this.jqZone;
		}
		
		public close() {
			this.trigger(Zone.EventType.CLOSE)
			this.jqZone.remove();
			this.jqZone = null;
			
			
			if (this.page) {
				this.unregisterPageListeners();
				this.page.dispose();
				this._page = null;
			}
		}
		
		public show() {
			this.trigger(Zone.EventType.SHOW);
			
			this.jqZone.show();
		}
		
		public hide() {
			this.trigger(Zone.EventType.HIDE);
			
			this.jqZone.hide();
		}
		
		private reset() {
			this.additionalTabManager = new AdditionalTabManager(this);
			this._menu = new Menu(this);
			this._messageList = new MessageList(this);
		}
		
		
		get empty(): boolean {
			return this.jqZone.is(":empty");
		}
		
		public clear(showLoader: boolean = false) {
			if (showLoader) {
				this.jqZone.addClass("rocket-loader");
			} else {
				this.endLoading();
			}
			
			
			if (this.empty) return;
			
			this.reset();
				
			this.jqZone.empty();
			
			this.trigger(Zone.EventType.CONTENT_CHANGED);
		}
			
		public applyHtml(html: string) {
			this.clear(false);
			this.jqZone.html(html);
			
			this.reset();
			
			this.applyLastModDefs();
			this.trigger(Zone.EventType.CONTENT_CHANGED);
		}
		
		public applyComp(comp: Jhtml.Comp) {
			this.clear(false);
			comp.attachTo(this.jqZone.get(0));
			
			this.reset();
			
			this.applyLastModDefs(); 
			this.trigger(Zone.EventType.CONTENT_CHANGED);
		}
		
		public isLoading(): boolean {
			return this.jqZone.hasClass("rocket-loader");
		}
		
		public endLoading() {
			this.jqZone.removeClass("rocket-loader");
		}
		
		public applyContent(jqContent: JQuery<Element>) {
			this.endLoading();
			this.jqZone.append(jqContent);
			
			this.reset();
			this.trigger(Zone.EventType.CONTENT_CHANGED);
		}
		
		set lastModDefs(lastModDefs: LastModDef[]) {
			this._lastModDefs = lastModDefs;
			this.applyLastModDefs();
		}
		
		get lastModDefs(): LastModDef[] {
			return this._lastModDefs;
		}
		
		private applyLastModDefs() {
			if (!this.jQuery) return;
			
			this.chLastMod(Display.Entry.findLastMod(this.jQuery), false);
			
			for (let lastModDef of this._lastModDefs) {
				if (lastModDef.pid) {
					this.chLastMod(Display.Entry
							.findByPid(this.jQuery, lastModDef.supremeEiTypeId, lastModDef.pid), true);
					continue;
				}
				
				if (lastModDef.draftId) {
					this.chLastMod(Display.Entry
							.findByDraftId(this.jQuery, lastModDef.supremeEiTypeId, lastModDef.draftId), true);
					continue;
				}
				
				this.chLastMod(Display.Entry.findBySupremeEiTypeId(this.jQuery, lastModDef.supremeEiTypeId), true);
			}
		}
		
		private chLastMod(entries: Display.Entry[], lastMod: boolean) {
			for (let entry of entries) {
				entry.lastMod = lastMod;
			}
		}
		
		private trigger(eventType: Zone.EventType) {
			var context = this;
			this.callbackRegistery.filter(eventType.toString())
					.forEach(function (callback: ZoneCallback) {
						callback(context);
					});
		}
		
		public on(eventType: Zone.EventType, callback: ZoneCallback) {
			this.callbackRegistery.register(eventType.toString(), callback);
		}
		
		public off(eventType: Zone.EventType, callback: ZoneCallback) {
			this.callbackRegistery.unregister(eventType.toString(), callback);
		}
		
		public createAdditionalTab(title: string, prepend: boolean = false, severity: Display.Severity = null) {
			return this.additionalTabManager.createTab(title, prepend, severity);
		} 
		
		get menu(): Menu {
			return this._menu;
		}
		
		get messageList(): MessageList {
			return this._messageList;
		}
		
		get locked(): boolean {
			return this.locks.length > 0;
		}
		
		private locks: Array<Lock> = new Array();
		
		private releaseLock(lock: Lock) {
			let i = this.locks.indexOf(lock);
			if (i == -1) return; 
			
			this.locks.splice(i, 1);
			this.trigger(Zone.EventType.BLOCKED_CHANGED);
		}
		
		createLock(): Lock {
			var that = this;
			var lock = new Lock(function (lock: Lock) {
				that.releaseLock(lock);
			});
			this.locks.push(lock);
			this.trigger(Zone.EventType.BLOCKED_CHANGED);
			return lock;
		}
		
		public static of(jqElem: JQuery<Element>): Zone {
			if (!jqElem.hasClass(".rocket-zone")) {
				jqElem = jqElem.parents(".rocket-zone");
			}
			
			let zone = jqElem.data("rocketZone");
			if (zone instanceof Zone) return zone;
			
			return null;
		}
	}
	
	export interface ZoneCallback {
		(context: Zone): any
	}
	
	export namespace Zone {
		export enum EventType {
			SHOW /*= "show"*/,
			HIDE /*= "hide"*/,
			CLOSE /*= "close"*/,
			CONTENT_CHANGED /*= "contentChanged"*/,
			ACTIVE_URL_CHANGED /*= "activeUrlChanged"*/,
			BLOCKED_CHANGED /*= "stateChanged"*/ 
		}
	}
}