namespace Rocket.Impl.Order {

	export class Control {
		private entry: Display.Entry;
		private collection: Display.Collection|null;
		
		constructor(private elemJq: JQuery<Element>, private insertMode: InsertMode, private moveState: MoveState,
				private otherElemJq: JQuery<Element>) {
			this.entry = Display.Entry.of(elemJq);
			this.collection = this.entry.collection;
			if (!this.collection || !this.entry.selector) {
				this.elemJq.hide();
				return;
			}
			
			if (!this.collection.selectable) {
				this.collection.setupSelector(new Display.MultiEntrySelectorObserver());
			}
			
			let onSelectionChanged = () => {
				this.update();
			};
			this.collection.onSelectionChanged(onSelectionChanged)
			this.entry.on(Display.Entry.EventType.DISPOSED, () => {
				this.collection.offSelectionChanged(onSelectionChanged);
			});
			
			this.update();
			
			this.elemJq.click((evt) => {
				evt.preventDefault();
				this.exec();
				return false;
			});
			
			this.setupSortable();
		}
		
		private setupSortable() {
			if (this.insertMode != InsertMode.AFTER && this.insertMode != InsertMode.BEFORE) {
				return;
			}
			
			this.collection.setupSortable();
			
			this.collection.onInsert((entries: Display.Entry[]) => {
				if (this.moveState.executing) return;
				
				this.prepare(entries);
			});
			
			this.collection.onInserted((entries: Display.Entry[], aboveEntry: Display.Entry, belowEntry: Display.Entry) => {
				if (this.moveState.executing) return;
				
				if (this.insertMode == InsertMode.BEFORE) {
					if (aboveEntry === null && this.entry === this.collection.entries[1]) {
						this.dingsel(entries);
						return;
					}
					
					if (belowEntry === this.entry && this.entry.treeLevel !== null 
							&& aboveEntry.treeLevel < this.entry.treeLevel) {
						this.dingsel(entries);
						return;
					}
				}
				
				if (this.insertMode == InsertMode.AFTER && this.entry === aboveEntry
						&& (belowEntry === null || this.entry.treeLevel === null || belowEntry.treeLevel <= this.entry.treeLevel)) {
					this.dingsel(entries);
					return;
				}
			});
		}
		
		get jQuery(): JQuery<Element> {
			return this.elemJq;
		}
		
		private prepare(entries: Display.Entry[]) {
			this.moveState.memorizeTreeDecendants(entries);
		}
		
		private update() {
			if ((this.entry.selector && this.entry.selector.selected)
					|| this.collection.selectedIds.length == 0
					|| this.checkIfParentSelected()) {
				this.elemJq.hide();
				this.otherElemJq.show();
			} else {
				this.elemJq.show();
				this.otherElemJq.hide();
			}
		}
		
		private checkIfParentSelected() {
			if (this.entry.treeLevel === null) return false;
			
			return !!this.entry.collection.findTreeParents(this.entry)
					.find((parentEntry: Display.Entry) => {
						return parentEntry.selector && parentEntry.selector.selected;
					});
		}
		
		private exec() {
			this.moveState.executing = true;
			let entries = this.collection.selectedEntries;
			this.prepare(entries);
			
			if (this.insertMode == InsertMode.BEFORE) {
				this.collection.insertAfter(this.collection.findPreviousEntry(this.entry), entries);
			} else if (this.entry.treeLevel === null) {
				this.collection.insertAfter(this.entry, entries);
			} else {
				let aboveEntry = this.collection.findTreeDescendants(this.entry).pop();
				if (!aboveEntry) {
					aboveEntry = this.entry;
				}
				this.collection.insertAfter(aboveEntry, entries);
			}
			
			this.moveState.executing = false;
			this.dingsel(entries);
		}
		
		private dingsel(entries: Display.Entry[]) {
			Display.Entry.findLastMod(Cmd.Zone.of(this.elemJq).jQuery).forEach((entry: Display.Entry) => {
				entry.lastMod = false;
			})
			
			let pids = [];
			for (let entry of entries) {
				pids.push(entry.id);
				entry.selector.selected = false;
				this.dingselAndExecTree(entry);
				entry.lastMod = true;
			}
			
			let url = new Jhtml.Url(this.elemJq.attr("href")).extR(null, { "pids": pids });
			Jhtml.Monitor.of(this.elemJq.get(0)).lookupModel(url);
		}
		
		private dingselAndExecTree(entry: Display.Entry) {
			if (entry.treeLevel === null) return;
			
			let newTreeLevel: number;
			if (this.insertMode == InsertMode.CHILD) {
				newTreeLevel = (this.entry.treeLevel || 0) + 1;
			} else {
				newTreeLevel = this.entry.treeLevel;
			}
			
			let treeLevelDelta = newTreeLevel - entry.treeLevel;
			entry.treeLevel = newTreeLevel;
			
			if (newTreeLevel === null) return;
			
			this.moveState.executing = true;
			
			let decendants = this.moveState.retrieveTreeDecendants(entry);

			this.collection.insertAfter(entry, decendants);
			
			this.moveState.executing = false;
			
			for (let decendant of decendants) {
				decendant.lastMod = true;
				decendant.treeLevel += treeLevelDelta;
			}
		}
	}
	
	export enum InsertMode {
		BEFORE, AFTER, CHILD
	}
	
	interface MaveStateItem {
		entry: Display.Entry; 
		treeDecendantsEntries: Display.Entry[];
	}
	
	export class MoveState {
		private treeMoveStates: Array<MaveStateItem> = [];
		private _executing = false;
		
		set executing(executing: boolean) {
			if (this._executing == executing) {
				throw new Error("Illegal move state");
			}
			
			this._executing = executing;
		}
		
		get executing(): boolean {
			return this._executing;
		}
		
		memorizeTreeDecendants(entries: Display.Entry[]) {
			this.treeMoveStates.splice(0);
			
			for (let entry of entries) {
				if (entry.treeLevel === null) continue;
				
				let decendants: Display.Entry[] = [];
				
				if (entry.collection) {
					decendants = entry.collection.findTreeDescendants(entry);
				}
				
				this.treeMoveStates.push({
					entry: entry,
					treeDecendantsEntries: decendants 
				});
			}
		}
		
		retrieveTreeDecendants(entry: Display.Entry): Display.Entry[] {
			let moveState = this.treeMoveStates.find((moveState: MaveStateItem) => {
				return moveState.entry === entry;
			});
			
			if (moveState) {
				this.treeMoveStates.splice(this.treeMoveStates.indexOf(moveState), 1);
				return moveState.treeDecendantsEntries;
			}
			
			throw new Error("illegal move state");
		}
	}
}