namespace Rocket.Display {
	
	export interface SelectorObserver {
		
		observeEntrySelector(entrySelector: EntrySelector): void;
		
		getSelectedIds(): Array<string>;
		
		destroy(): void;
	}
	
	export class MultiEntrySelectorObserver implements SelectorObserver {
		private selectedIds: Array<string>;
		private identityStrings: { [key: string]: string } = {};
		private selectors: { [key: string]: EntrySelector } = {};
		private checkJqs: { [key: string]: JQuery } = {};
		
		constructor(private originalPids: Array<string> = new Array<string>()) {
			this.selectedIds = originalPids;
		}
		
		destroy(): void {
			for (let key in this.selectors) {
				this.checkJqs[key].remove();
				
				let selector = this.selectors[key];
				selector.offChanged(this.onChanged);
				
				let entry = selector.entry;
				entry.off(Entry.EventType.DISPOSED, this.onDisposed);
				entry.off(Entry.EventType.REMOVED, this.onRemoved);
			}
			
			this.identityStrings = {};
			this.selectors = {};
			this.checkJqs = {};
		}
		
		observeEntrySelector(selector: EntrySelector) {
			let entry = selector.entry;
			let id = entry.id;
			
			if (this.selectors[id]) return;
			
			let jqCheck = $("<input />", { "type": "checkbox" });
			selector.jQuery.empty();
			selector.jQuery.append(jqCheck);
			
			jqCheck.change(() => {
				selector.selected = jqCheck.is(":checked");
			});
			selector.onChanged(this.onChanged, true);
			
			selector.selected = this.containsSelectedId(id);
			jqCheck.prop("checked", selector.selected);
			
			this.checkJqs[id] = jqCheck;
			this.selectors[id] = selector;
			this.identityStrings[id] = entry.identityString;
			
			entry.on(Entry.EventType.DISPOSED, this.onDisposed);
			entry.on(Entry.EventType.REMOVED, this.onRemoved);
		}
		
		private onChanged = (selector: EntrySelector) => {
			let id = selector.entry.id
			this.checkJqs[id].prop("checked", selector.selected);
			this.chSelect(selector.selected, id);
		}
		
		private onDisposed = (entry: Entry) => {
			delete this.selectors[entry.id];
		}
		
		private onRemoved = (entry: Entry) => {
			this.chSelect(false, entry.id);
		}
		
		public containsSelectedId(id: string): boolean {
			return -1 < this.selectedIds.indexOf(id);
		}
		
		private chSelect(selected: boolean, id: string) {
			if (selected) {
				if (-1 < this.selectedIds.indexOf(id)) return;
				
				this.selectedIds.push(id);
				return;
			}
			
			var i;
			if (-1 < (i = this.selectedIds.indexOf(id))) {
				this.selectedIds.splice(i, 1);
			}
		}
		
		getSelectedIds(): Array<string> {
			return this.selectedIds;
		}
		
		getIdentityStringById(id: string): string {
			if (this.identityStrings[id] !== undefined) {
				return this.identityStrings[id];
			}
			
			return null;
		}
		
		getSelectorById(id: string): EntrySelector {
			if (this.selectors[id] !== undefined) {
				return this.selectors[id];
			}
			
			return null;
		}
		
		setSelectedIds(selectedIds: Array<string>) {
			this.selectedIds = selectedIds;
			
			var that = this;
			for (var id in this.selectors) {
				this.selectors[id].selected = that.containsSelectedId(id);
			}
		}
	}
	
	
	
	export class SingleEntrySelectorObserver implements SelectorObserver {
		private selectedId: string = null;
		private identityStrings: { [id: string]: string } = {};
		private selectors: { [id: string]: EntrySelector } = {};
		private checkJqs: { [id: string]: JQuery } = {};
		
		constructor(private originalId: string = null) {
			this.selectedId = originalId;
		}
		
		destroy(): void {
			for (let id in this.selectors) {
				this.checkJqs[id].remove();
				
				let entry = this.selectors[id].entry;
				entry.off(Entry.EventType.DISPOSED, this.onDisposed);
				entry.off(Entry.EventType.REMOVED, this.onRemoved);
			}
			
			this.identityStrings = {};
			this.selectors = {};
		}
		
		observeEntrySelector(selector: EntrySelector) {
			let entry = selector.entry;
			let id = entry.id;
			if (this.selectors[id]) return;
			
			let checkJq = $("<input />", { "type": "radio" });
			selector.jQuery.empty();
			selector.jQuery.append(checkJq);
			
			checkJq.change(() => {
				selector.selected = checkJq.is(":checked");
			});
			selector.onChanged(this.onChanged);
			
			
			selector.selected = this.selectedId === id;
			
			this.checkJqs[id] = checkJq;
			this.selectors[id] = selector;
			this.identityStrings[id] = entry.identityString;
			
			entry.on(Entry.EventType.DISPOSED, this.onDisposed);
			entry.on(Entry.EventType.REMOVED, this.onRemoved);
		}
		
		private onChanged = (selector: EntrySelector) => {
			let id = selector.entry.id
			this.checkJqs[id].prop("checked", selector.selected);
			this.chSelect(selector.selected, id);
		}
		
		private onDisposed = (entry: Entry) => {
			delete this.selectors[entry.id];
		}
		
		private onRemoved = (entry: Entry) => {
			this.chSelect(false, entry.id);
		}
		
		getSelectedIds(): Array<string> {
			return [this.selectedId];
		}
		
		private chSelect(selected: boolean, id: string) {
			if (!selected) {
				if (this.selectedId === id) {
					this.selectedId = null;
				} 
				return;
			}
			
			if (this.selectedId === id) return;
			
			this.selectedId = id;
			
			for (let id in this.selectors) {
				if (id === this.selectedId) continue;
				
				this.selectors[id].selected = false;
			}
		}
		
		getIdentityStringById(id: string): string {
			if (this.identityStrings[id] !== undefined) {
				return this.identityStrings[id];
			}
			
			return null;
		}
		
		getSelectorById(id: string): EntrySelector {
			if (this.selectors[id] !== undefined) {
				return this.selectors[id];
			}
			
			return null;
		}
		
		setSelectedId(selectedId: string) {
			if (this.selectors[selectedId]) {
				this.selectors[selectedId].selected = true;
				return;
			}
			
			this.selectedId = selectedId;
			
			for (let id in this.selectors) {
				this.selectors[id].selected = false;
			}
		}
	}
}