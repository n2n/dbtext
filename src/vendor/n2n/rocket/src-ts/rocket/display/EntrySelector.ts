namespace Rocket.Display {
	
	export class EntrySelector {
		private changedCallbacks: Array<EntrySelectorCallback> = [];
		
		private _selected: boolean = false;
		
		constructor(private jqElem: JQuery<Element>, private _entry: Entry) {
		}
		
		get jQuery(): JQuery<Element> {
			return this.jqElem;
		}
		
		get entry(): Entry {
			return this._entry;
		}
		
		get selected(): boolean {
			return this._selected;
		}
		
		set selected(selected: boolean) {
			if (this._selected == selected) return;
			
			this._selected = selected;
			this.triggerChanged();
		}
				
		onChanged(callback: EntrySelectorCallback, prepend: boolean = false) {
			if (prepend) {
				this.changedCallbacks.unshift(callback);
			} else {
				this.changedCallbacks.push(callback);
			}
		}
		
		offChanged(callback: EntrySelectorCallback) {
			this.changedCallbacks.splice(this.changedCallbacks.indexOf(callback));
		}
		
		protected triggerChanged() {
			this.changedCallbacks.forEach((callback) => {
				callback(this);
			});
		}
		
//		static findAll(jqElem: JQuery<Element>): Array<EntrySelector> {
//			var entrySelectors = new Array<EntrySelector>();
//			
//			jqElem.find(".rocket-entry-selector").each(function () {
//				entrySelectors.push(EntrySelector.from($(this)));
//			});
//			
//			return entrySelectors;
//		}
//		
//		static findFrom(jqElem: JQuery<Element>): EntrySelector {
//			var jqElem = jqElem.closest(".rocket-entry-selector");
//			
//			if (jqElem.length == 0) return null;
//			
//			return EntrySelector.findFrom(jqElem);
//		}
//		
//		private static from(jqElem: JQuery<Element>): EntrySelector {
//			var entrySelector = jqElem.data("rocketEntrySelector");
//			if (entrySelector instanceof EntrySelector) {
//				return entrySelector;
//			}
//			
//			entrySelector = new EntrySelector(jqElem); 
//			jqElem.data("rocketEntrySelector", entrySelector);
//			
//			return entrySelector;
//		}
	}
	
	interface EntrySelectorCallback {
		(entrySelector?: EntrySelector): any
	}
}