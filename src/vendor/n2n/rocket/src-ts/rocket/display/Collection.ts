namespace Rocket.Display {

	export class Collection {
		private entryMap: { [id: string]: Entry } = {};
		private sortedEntries: Entry[];
		private selectorObservers: Array<SelectorObserver> = [];
		private selectionChangedCbr = new Jhtml.Util.CallbackRegistry<() => any>();
		private insertCbr = new Jhtml.Util.CallbackRegistry<InsertCallback>();
		private insertedCbr = new Jhtml.Util.CallbackRegistry<InsertedCallback>();
		
		constructor(private elemJq: JQuery<Element>) {
		}
		
		scan() {
		    this.sortedEntries = null;
		    
			for (let entry of Entry.children(this.elemJq)) {
				if (this.entryMap[entry.id] && this.entryMap[entry.id] === entry) {
				    continue;
				}
				
				this.registerEntry(entry);
			}
		}
		
		public registerEntry(entry: Entry) {
		    this.entryMap[entry.id] = entry;
		    
		    if (entry.selector) {
		    	for (let selectorObserver of this.selectorObservers) {
		    		selectorObserver.observeEntrySelector(entry.selector);
		    	}
		    }
		    if (this.sortable && entry.selector) {
		    	this.applyHandle(entry.selector);
		    }
		    
			entry.selector.onChanged(() => {
				this.triggerChanged();
			});
		    
			var onFunc = () => {
				if (this.entryMap[entry.id] !== entry) return;
			
				delete this.entryMap[entry.id];
			};
			entry.on(Display.Entry.EventType.DISPOSED, onFunc);
			entry.on(Display.Entry.EventType.REMOVED, onFunc);
			
//			entry.jQuery.on("DOMNodeInserted", () => {
//				
//			});
		}
		
		private triggerChanged() {
			this.selectionChangedCbr.fire();
		}
				
		onSelectionChanged(callback: () => any) {
			this.selectionChangedCbr.on(callback);
		}
		
		offSelectionChanged(callback: () => any) {
			this.selectionChangedCbr.off(callback);
		}
		
		setupSelector(selectorObserver: SelectorObserver) {
			this.selectorObservers.push(selectorObserver);
			for (let entry of this.entries) {
				if (!entry.selector) continue;
				
				selectorObserver.observeEntrySelector(entry.selector);
			}
		}
		
		destroySelectors() {
			let selectorObserver;
			while (selectorObserver = this.selectorObservers.pop()) {
				selectorObserver.destroy();
			}
		}
		
		get selectedIds(): string[] {
			let ids: Array<string> = [];
			for (let entry of this.entries) {
				if (entry.selector && entry.selector.selected) {
					ids.push(entry.id);
				}
			}
			return ids;
		}
		
		get selectable(): boolean {
			return this.selectorObservers.length > 0;
		}
		
		get jQuery(): JQuery<Element> {
			return this.elemJq;
		}
		
		containsEntryId(id: string): boolean {
			return this.entryMap[id] !== undefined;
		}
		
		get entries(): Array<Entry> {
			if (this.sortedEntries) {
				return this.sortedEntries;
			}
			
			this.sortedEntries = new Array<Entry>();
			
			for (let entry of Entry.children(this.elemJq)) {
				if (!this.entryMap[entry.id] || this.entryMap[entry.id] !== entry) {
					continue;
				}

				this.sortedEntries.push(entry);
			}
			
			return this.sortedEntries.slice();
		}
		
		get selectedEntries(): Array<Entry> {
			var entries = new Array<Entry>();
			
			for (let entry of this.entries) {
				if (!entry.selector || !entry.selector.selected) continue;
				
				entries.push(entry);
			}
			
			return entries;
		}
		
		private _sortable = false;
		
		setupSortable() {
			if (this._sortable) return;
			
			this._sortable = true;
			this.elemJq.sortable({
				"handle": ".rocket-handle",
				"forcePlaceholderSize": true,
		      	"placeholder": "rocket-entry-placeholder",
				"start": (event: JQueryEventObject, ui: JQueryUI.SortableUIParams) => {
					let entry = Entry.find(ui.item, true);
					this.insertCbr.fire([entry]);
				},
				"update": (event: JQueryEventObject, ui: JQueryUI.SortableUIParams) => {
					this.sortedEntries = null;
					let entry = Entry.find(ui.item, true);
					this.insertedCbr.fire([entry], this.findPreviousEntry(entry), this.findNextEntry(entry));
				}
		    })/*.disableSelection()*/;
			
			for (let entry of this.entries) {
				if (!entry.selector) continue;
				
				this.applyHandle(entry.selector);
			}
		}
		
		get sortable(): boolean {
			return this._sortable;
		}
		
		private applyHandle(selector: EntrySelector) {
			selector.jQuery.append($("<div />", { "class": "rocket-handle" })
					.append($("<i></i>", { "class": "fa fa-bars" })));
		}
				
		private enabledSortable() {
			this._sortable = true;
			this.elemJq.sortable("enable");
			this.elemJq.disableSelection();
		}
		
		private disableSortable() {
			this._sortable = false;
			this.elemJq.sortable("disable");
			this.elemJq.enableSelection();
		}
		
		private valEntry(entry: Entry) {
			let id = entry.id;
			if (!this.entryMap[id]) {
				throw new Error("Unknown entry with id " + id);
			}
			
			if (this.entryMap[id] !== entry) {
				throw new Error("Collection contains other entry with same id: " + id);
			}
		}
		
		containsEntry(entry: Entry): boolean {
			let id = entry.id;
			return !!this.entryMap[id] && this.entryMap[id] === entry; 
		}
		
		findPreviousEntry(nextEntry: Entry): Entry|null {
			this.valEntry(nextEntry);
			
			let aboveEntry: Entry = null;
			for (let entry of this.entries) {
				if (entry === nextEntry) return aboveEntry;
				
				aboveEntry = entry;
			}
			
			return null;
		}
		
		findPreviousEntries(previousEntry: Entry): Entry[] {
			this.valEntry(previousEntry);
			
			let previousEntries: Entry[] = [];
			for (let entry of this.entries) {
				if (entry === previousEntry) {
					return previousEntries;
				}
				
				previousEntries.push(entry);
			}
			
			return previousEntries;
		}
		
		findNextEntry(previousEntry: Entry): Entry|null {
			this.valEntry(previousEntry);
			
			for (let entry of this.entries) {
				if (!previousEntry) {
					return entry;
				}
				
				if (entry === previousEntry) {
					previousEntry = null;
				}
			}
			
			return null;
		}
		
		findNextEntries(beforeEntry: Entry): Entry[] {
			this.valEntry(beforeEntry);
			
			let nextEntries: Entry[] = [];
			for (let entry of this.entries) {
				if (!beforeEntry) {
					nextEntries.push(entry);
				}
				
				if (entry === beforeEntry) {
					beforeEntry = null;
				}
			}
			
			return nextEntries;
		}
		
		findTreeParents(baseEntry: Entry) {
			this.valTreeEntry(baseEntry);
			
			let parentEntries: Entry[] = [];
			
			if (baseEntry.treeLevel === null) {
				return parentEntries;
			}
			
			
			let curTreeLevel = baseEntry.treeLevel;
			for (let entry of this.findPreviousEntries(baseEntry).reverse()) {
				let treeLevel = entry.treeLevel;
				if (treeLevel === null) {
					return parentEntries;
				}
				
				if (treeLevel < curTreeLevel) {
					parentEntries.push(entry);
					curTreeLevel = entry.treeLevel;
				}
				
				if (treeLevel == 0) {
					return parentEntries;
				}
			}
			
			return parentEntries;
		}
		
		private valTreeEntry(entry: Entry) {
			if (entry.treeLevel === null) {
				throw new Error("Passed entry is not part of a tree.");
			}
		}
		
		findTreeDescendants(baseEntry: Entry): Entry[] {
			this.valTreeEntry(baseEntry);
				
			let treeLevel = baseEntry.treeLevel;
			let treeDescendants: Entry[] = [];
			for (let entry of this.findNextEntries(baseEntry)) {
				if (entry.treeLevel > treeLevel) {
					treeDescendants.push(entry);
					continue;
				}
				
				return treeDescendants;
			}
			
			return treeDescendants;
		}
		
		insertAfter(aboveEntry: Entry|null, entries: Entry[]) {
			if (aboveEntry !== null) {
				this.valEntry(aboveEntry)
			}
			
			let belowEntry = this.findNextEntry(aboveEntry);
			
			this.insertCbr.fire(entries);
			
			for (let entry of entries.reverse()) {
				if (aboveEntry) {
					entry.jQuery.insertAfter(aboveEntry.jQuery);
				} else {
					this.elemJq.prepend(entry.jQuery);
				}
			}
			
			this.sortedEntries = null;
			this.insertedCbr.fire(entries, aboveEntry, belowEntry);
		}
		
		onInsert(callback: InsertCallback) {
			this.insertCbr.on(callback);
		}
		
		offInsert(callback: InsertCallback) {
			this.insertCbr.off(callback);
		}
		
		onInserted(callback: InsertedCallback) {
			this.insertedCbr.on(callback);
		}
		
		offInserted(callback: InsertedCallback) {
			this.insertedCbr.off(callback);
		}
		
		static readonly CSS_CLASS = "rocket-collection";
		static readonly SUPREME_EI_TYPE_ID_ATTR = "data-rocket-supreme-ei-type-id";
		
		static test(jqElem: JQuery<Element>) {
			if (jqElem.hasClass(Collection.CSS_CLASS)) {
				return Collection.from(jqElem);
			}
			
			return null;
		}
		
		static from(jqElem: JQuery<Element>): Collection {
			var collection = jqElem.data("rocketCollection");
			if (collection instanceof Collection) return collection;
		
			collection = new Collection(jqElem);
			jqElem.data("rocketCollection", collection);
			jqElem.addClass(Collection.CSS_CLASS);
			return collection;
		}
		
		static of(jqElem:JQuery) {
			jqElem = jqElem.closest("." + Collection.CSS_CLASS);
			if (jqElem.length == 0) return null;
			
			return Collection.from(jqElem);
		}

		
		private static fromArr(entriesJq: JQuery<Element>): Array<Collection> {
			let collections = new Array<Collection>();
			entriesJq.each(function () {
				collections.push(Collection.from($(this)));
			});
			return collections;
		}

		private static buildSupremeEiTypeISelector(supremeEiTypeId: string): string {
			return "." + Collection.CSS_CLASS + "[" + Collection.SUPREME_EI_TYPE_ID_ATTR + "=" + supremeEiTypeId + "]";
		}
		
		static findBySupremeEiTypeId(jqContainer: JQuery<Element>, supremeEiTypeId: string): Collection[] {
			return Collection.fromArr(jqContainer.find(Collection.buildSupremeEiTypeISelector(supremeEiTypeId)));
		}

		static hasSupremeEiTypeId(jqContainer: JQuery<Element>, supremeEiTypeId: string): boolean {
			return 0 < jqContainer.has(Collection.buildSupremeEiTypeISelector(supremeEiTypeId)).length;
		}
	}
	

	export interface InsertCallback {
		(entries: Entry[]): any
	}
	
	export interface InsertedCallback {
		(entries: Entry[], aboveEntry: Entry, belowEntry: Entry): any
	}
}