namespace Rocket.Display {
	
	export class Entry {
		private _selector: EntrySelector = null;
		private _state: Entry.State = Entry.State.PERSISTENT;
		private callbackRegistery: Util.CallbackRegistry<EntryCallback> = new Util.CallbackRegistry<EntryCallback>();
		
		constructor(private elemJq: JQuery<Element>) {
			elemJq.on("remove", () => {
				this.trigger(Entry.EventType.DISPOSED);
				this.callbackRegistery.clear();
			});
			
			let selectorJq = elemJq.find(".rocket-entry-selector:first");
			if (selectorJq.length > 0) {
				this.initSelector(selectorJq);
			}
		}
		
		get lastMod(): boolean {
			return this.elemJq.hasClass(Entry.LAST_MOD_CSS_CLASS);
		}
		
		set lastMod(lastMod: boolean) {
			if (lastMod) {
				this.elemJq.addClass(Entry.LAST_MOD_CSS_CLASS)
			} else {
				this.elemJq.removeClass(Entry.LAST_MOD_CSS_CLASS)
			}
		}
		
		get collection(): Collection|null {
			return Collection.test(this.elemJq.parent());
		}
		
		private initSelector(jqSelector: JQuery<Element>) {
			this._selector = new EntrySelector(jqSelector, this);
			
			var that = this;
			this.elemJq.click(function (e) {
				if (getSelection().toString() || Util.ElementUtils.isControl(e.target)) {
					return;
				}
				
				that._selector.selected = !that._selector.selected;
			});
		}
		
		private trigger(eventType: Entry.EventType) {
			var entry = this;
			this.callbackRegistery.filter(eventType.toString())
					.forEach(function (callback: EntryCallback) {
						callback(entry);
					});
		}
		
		public on(eventType: Entry.EventType, callback: EntryCallback) {
			this.callbackRegistery.register(eventType.toString(), callback);
		}
		
		public off(eventType: Entry.EventType, callback: EntryCallback) {
			this.callbackRegistery.unregister(eventType.toString(), callback);
		}
		
		get jQuery(): JQuery<Element> {
			return this.elemJq;
		}
		
		show() {
			this.elemJq.show();
		}
		
		hide() {
			this.elemJq.hide();
		}
		
		dispose() {
			this.elemJq.remove();
		}
		
		get state(): Entry.State {
			return this._state;
		}
		
		set state(state: Entry.State) {
			if (this._state == state) return;
			
			this._state = state;
			
			if (state == Entry.State.REMOVED) {
				this.trigger(Entry.EventType.REMOVED);
			}
		}
		
		get generalId(): string {
			return this.elemJq.data("rocket-general-id").toString();		
		}
		
		get id(): string {
			if (this.draftId !== null) {
				return this.draftId.toString();
			}
			
			return this.pid;
		}
		
		get supremeEiTypeId(): string {
			return this.elemJq.data("rocket-supreme-ei-type-id").toString();
		}
		
		get eiTypeId(): string {
			return this.elemJq.data("rocket-ei-type-id").toString();
		}
		
		get pid(): string {
			return this.elemJq.data("rocket-ei-id").toString();
		}
		
		get draftId(): number {
			var draftId = parseInt(this.elemJq.data("rocket-draft-id"));
			if (!isNaN(draftId)) {
				return draftId;
			}
			return null;
		}
		
		get identityString(): string {
			return this.elemJq.data("rocket-identity-string");
		}
		
		get selector(): EntrySelector {
			return this._selector;	
		}
		
		private findTreeLevelClass(): string|null {
			let cl = this.elemJq.get(0).classList;
			
			for (let i = 0; i < cl.length; i++) {
				let className = cl.item(i);
				if (className.startsWith(Entry.TREE_LEVEL_CSS_CLASS_PREFIX)) {
					return className;
				}
			}
			
			return null;
		}
		
		get treeLevel(): number|null {
			let className = this.findTreeLevelClass()
			if (className === null) return null;
			
			return parseInt(className.substr(Entry.TREE_LEVEL_CSS_CLASS_PREFIX.length));
		}
		
		set treeLevel(treeLevel: number|null) {
			let className = this.findTreeLevelClass();
			if (className) {
				this.elemJq.removeClass(className);
			} 
			
			if (treeLevel !== null) {
				this.elemJq.addClass(Entry.TREE_LEVEL_CSS_CLASS_PREFIX + treeLevel)
			}
		}
		
		static readonly CSS_CLASS = "rocket-entry";
		static readonly TREE_LEVEL_CSS_CLASS_PREFIX = "rocket-tree-level-";
		static readonly LAST_MOD_CSS_CLASS = "rocket-last-mod";
		static readonly SUPREME_EI_TYPE_ID_ATTR = "data-rocket-supreme-ei-type-id";
		static readonly EI_TYPE_ID_ATTR = "data-rocket-ei-type-id";
		static readonly ID_REP_ATTR = "data-rocket-ei-id";
		static readonly DRAFT_ID_ATTR = "data-rocket-draft-id";
		
		private static from(elemJq: JQuery<Element>): Entry {
			var entry = elemJq.data("rocketEntry");
			if (entry instanceof Entry) {
				return entry;
			}
			
			entry = new Entry(elemJq); 
			elemJq.data("rocketEntry", entry);
			elemJq.addClass(Entry.CSS_CLASS);
			
			return entry;
		}
		
		static of(jqElem: JQuery<Element>): Entry {
			var jqElem = jqElem.closest("." + Entry.CSS_CLASS);
			
			if (jqElem.length == 0) return null;
			
			return Entry.from(jqElem);
		}

		static find(jqElem: JQuery<Element>, includeSelf: boolean = false): Entry|null {
			let entries = Entry.findAll(jqElem, includeSelf);
			if (entries.length > 0) {
				return entries[0]
			}
			return null;
		}
		
		static findAll(jqElem: JQuery<Element>, includeSelf: boolean = false): Array<Entry> {
			let jqEntries = jqElem.find("." + Entry.CSS_CLASS);
			
			if (includeSelf) {
				jqEntries = jqEntries.add(<JQuery<HTMLElement>> jqElem.filter("." + Entry.CSS_CLASS));
			}
			
			return Entry.fromArr(jqEntries);
		}

		static findLastMod(jqElem: JQuery<Element>): Array<Entry> {
			let entriesJq = jqElem.find("." + Entry.CSS_CLASS + "." + Entry.LAST_MOD_CSS_CLASS);
			
			return Entry.fromArr(entriesJq);
		}
		
		private static fromArr(entriesJq: JQuery<Element>): Array<Entry> {
			let entries = new Array<Entry>();
			entriesJq.each(function () {
				entries.push(Entry.from($(this)));
			});
			return entries;
		}
		
		static children(jqElem: JQuery<Element>): Array<Entry> {
			return Entry.fromArr(jqElem.children("." + Entry.CSS_CLASS));
		}
		
		static filter(jqElem: JQuery<Element>): Array<Entry> {
			return Entry.fromArr(jqElem.filter("." + Entry.CSS_CLASS));
		}

		private static buildSupremeEiTypeISelector(supremeEiTypeId: string): string {
			return "." + Entry.CSS_CLASS + "[" + Entry.SUPREME_EI_TYPE_ID_ATTR + "=" + Rocket.Util.escSelector(supremeEiTypeId) + "]";
		}
		
		static findBySupremeEiTypeId(jqContainer: JQuery<Element>, supremeEiTypeId: string): Entry[] {
			return Entry.fromArr(jqContainer.find(Entry.buildSupremeEiTypeISelector(supremeEiTypeId)));
		}
		
		static hasSupremeEiTypeId(jqContainer: JQuery<Element>, supremeEiTypeId: string): boolean {
			return 0 < jqContainer.has(Entry.buildSupremeEiTypeISelector(supremeEiTypeId)).length;
		}
		
		private static buildPidSelector(supremeEiTypeId: string, pid: string): string {
			return "." + Entry.CSS_CLASS + "[" + Entry.SUPREME_EI_TYPE_ID_ATTR + "=" + Rocket.Util.escSelector(supremeEiTypeId) + "][" 
					+ Entry.ID_REP_ATTR + "=" + Rocket.Util.escSelector(pid) + "]";
		}
		
		static findByPid(jqElem: JQuery<Element>, supremeEiTypeId: string, pid: string): Entry[] {
			return Entry.fromArr(jqElem.find(Entry.buildPidSelector(supremeEiTypeId, pid)));
		}
		
		static hasPid(jqElem: JQuery<Element>, supremeEiTypeId: string, pid: string): boolean {
			return 0 < jqElem.has(Entry.buildPidSelector(supremeEiTypeId, pid)).length;
		}
		
		private static buildDraftIdSelector(supremeEiTypeId: string, draftId: number): string {
			return "." + Entry.CSS_CLASS + "[" + Entry.SUPREME_EI_TYPE_ID_ATTR + "=" + Rocket.Util.escSelector(supremeEiTypeId) + "][" 
					+ Entry.DRAFT_ID_ATTR + "=" + draftId + "]";
		}
		
		static findByDraftId(jqElem: JQuery<Element>, supremeEiTypeId: string, draftId: number): Entry[] {
			return Entry.fromArr(jqElem.find(Entry.buildDraftIdSelector(supremeEiTypeId, draftId)));
		}
		
		static hasDraftId(jqElem: JQuery<Element>, supremeEiTypeId: string, draftId: number): boolean {
			return 0 < jqElem.has(Entry.buildDraftIdSelector(supremeEiTypeId, draftId)).length;
		}
	}
	
	export interface EntryCallback {
		(entry: Entry): any;
	}
	
	export namespace Entry {
		export enum State {
			PERSISTENT /*= "persistent"*/,
			REMOVED /*= "removed"*/
		}
		
		export enum EventType {
			DISPOSED /*= "disposed"*/,
			REFRESHED /*= "refreshed"*/,
			REMOVED /*= "removed"*/
		}
	}
}