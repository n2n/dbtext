namespace Rocket.Impl.Relation {
	
	export class AddControlFactory {
		public clipboard: Clipboard;
		public pasteStrategy: PasteStrategy|null;
	
		constructor (public embeddedEntryRetriever: EmbeddedEntryRetriever, private newLabel: string,
				private pasteLabel: string) {
		}
		
		public createAdd(): AddControl {
			return AddControl.create(this.newLabel, this.pasteLabel, this.embeddedEntryRetriever, this.pasteStrategy);
		}
		
//		public createReplace(): AddControl {
//			return AddControl.create(this.replaceLabel, this.embeddedEntryRetriever);
//		}
	}
	
	export class AddControl {
		private embeddedEntryRetriever: EmbeddedEntryRetriever;
		private jqNew: JQuery<Element>;
		private jqNewButton: JQuery<Element>;
		private onNewEntryCallbacks: Array<(entry: EmbeddedEntry) => any> = [];
		private jqNewMultiTypeUl: JQuery|null = null;
		private newMultiTypeEmbeddedEntry: EmbeddedEntry; 
		
		private jqPaste: JQuery<Element>;
		private jqPasteButton: JQuery<Element>;
		private jqPasteUl: JQuery|null = null;
		private pasteOnChanged: () => any;
		
		private disposed: boolean = false;
		
		constructor(private jqElem: JQuery<Element>, embeddedEntryRetriever: EmbeddedEntryRetriever,
				private pasteStrategy: PasteStrategy = null) {
			this.embeddedEntryRetriever = embeddedEntryRetriever;
			
			this.initNew();
			this.initPaste();
		}
		
		private initNew() {
			this.jqNew = this.jqElem.children(".rocket-impl-new");
			this.jqNewButton = this.jqNew.children("button");
			
			this.jqNewButton.on("mouseenter", () => {
				this.embeddedEntryRetriever.setPreloadNewsEnabled(true);
			});
			this.jqNewButton.on("click", () => {
				if (this.isLoading()) return;
				
				if (this.jqNewMultiTypeUl) {
					this.jqNewMultiTypeUl.toggle();
					return;
				}
				
				this.block(true);
				this.embeddedEntryRetriever.lookupNew(
						(embeddedEntry: EmbeddedEntry, snippet: Jhtml.Snippet) => {
							this.examineNew(embeddedEntry, snippet);
						},
						() => {
							this.block(false);
						});
			});
		}
		
		private initPaste() {
			this.jqPaste = this.jqElem.children(".rocket-impl-paste");
			this.jqPasteButton = this.jqPaste.children("button");
			
			if (!this.pasteStrategy) return;
			
			this.pasteOnChanged = () => {
				this.syncPasteButton();
			}
			this.pasteStrategy.clipboard.onChanged(this.pasteOnChanged);
			
			this.jqPasteButton.on("click", () => {
				if (this.isLoading()) return;
				
				if (this.jqPasteUl) {
					this.jqPasteUl.toggle();
				}
			});
			
			this.syncPasteButton();
		}
		
		private syncPasteButton() {
			this.hidePaste();
			
			let found = false;
			
			for (let element of this.pasteStrategy.clipboard.toArray()) {
				if (-1 == this.pasteStrategy.pastableEiTypeIds.indexOf(element.eiTypeId)) {
					continue;
				}
				
				this.addPasteOption(element);
				found = true;
			}
		}
		
		private addPasteOption(element: ClipboardElement) {
			if (!this.jqPasteUl) {
				this.jqPasteUl = $("<ul />", { "class": "rocket-impl-multi-type-menu"}).appendTo(<JQuery<HTMLElement>> this.jqPaste).hide();
				this.jqPaste.show();
			}
			
			this.jqPasteUl.append($("<li />").append($("<button />", { 
				"type": "button", 
				"text": element.identityString,
				"click": () => {
					this.pasteElement(element);
				}
			})));
		}
		
		private pasteElement(element: ClipboardElement) {
			this.block(true);
			
			this.jqPasteUl.hide();
			
			this.embeddedEntryRetriever.lookupCopy(element.pid, 
					(embeddedEntry: EmbeddedEntry, snippet: Jhtml.Snippet) => {
						this.fireCallbacks(embeddedEntry);
						snippet.markAttached();
						this.block(false);
					}, 
					() => {
						this.pasteStrategy.clipboard.remove(element.eiTypeId, element.pid);
						this.block(false);
					});
		}
		
		private hidePaste() {
			this.jqPaste.hide();
			if (this.jqPasteUl) {
				this.jqPasteUl.remove();
				this.jqPasteUl = null;
			}
		}
		
		get jQuery(): JQuery<Element> {
			return this.jqElem;
		}
		
		private block(blocked: boolean) {
			if (blocked) {
				this.jqNewButton.prop("disabled", true);
				this.jqPasteButton.prop("disabled", true);
				this.jqElem.addClass("rocket-impl-loading");
			} else {
				this.jqNewButton.prop("disabled", false);
				this.jqPasteButton.prop("disabled", false);
				this.jqElem.removeClass("rocket-impl-loading");
			}
		}	
		
		private examineNew(embeddedEntry: EmbeddedEntry, snippet: Jhtml.Snippet) {
			this.block(false);
			
			if (!embeddedEntry.entryForm.multiEiType) {
				this.fireCallbacks(embeddedEntry);
				snippet.markAttached();
				return;
			}
			
			this.newMultiTypeEmbeddedEntry = embeddedEntry;
			
			this.jqNewMultiTypeUl = $("<ul />", { "class": "rocket-impl-multi-type-menu" });
			this.jqNew.append(this.jqNewMultiTypeUl);
			
			let typeMap = embeddedEntry.entryForm.typeMap;
			for (let typeId in typeMap) {
				this.jqNewMultiTypeUl.append($("<li />").append($("<button />", { 
					"type": "button", 
					"text": typeMap[typeId],
					"click": () => {
						embeddedEntry.entryForm.curEiTypeId = typeId;
						this.jqNewMultiTypeUl.remove();
						this.jqNewMultiTypeUl = null;
						this.newMultiTypeEmbeddedEntry = null;
						this.fireCallbacks(embeddedEntry);
						snippet.markAttached();
					}
				})));
			}
		}
		
		public dispose() {
			this.disposed = true;
			this.jqElem.remove();
			
			if (this.newMultiTypeEmbeddedEntry !== null) {
				this.fireCallbacks(this.newMultiTypeEmbeddedEntry);
				this.newMultiTypeEmbeddedEntry = null;
			}
			
			if (this.pasteOnChanged) {
				this.pasteStrategy.clipboard.offChanged(this.pasteOnChanged);
			}
		}
		
		public isLoading() {
			return this.jqElem.hasClass("rocket-impl-loading");
		}
		
		private fireCallbacks(embeddedEntry: EmbeddedEntry) {
			if (this.disposed) return;
			
			this.onNewEntryCallbacks.forEach(function (callback: (entry: EmbeddedEntry) => any) {
				callback(embeddedEntry);
			});
		}
		
		public onNewEmbeddedEntry(callback: (entry: EmbeddedEntry) => any) {
			this.onNewEntryCallbacks.push(callback);
		}
		
		public static create(newLabel: string, pasteLabel: string, embeddedEntryRetriever: EmbeddedEntryRetriever, 
				pasteStrategy: PasteStrategy = null): AddControl {
			let elemJq = $("<div />", { "class": "rocket-impl-add-entry"})
					.append($("<div />", { "class": "rocket-impl-new" })
							.append($("<button />", { "text": newLabel, "type": "button", "class": "btn btn-block btn-secondary" })));
			
			if (pasteStrategy) {
				elemJq.append($("<div />", { "class": "rocket-impl-paste" })
						.append($("<button />", { "text": pasteLabel, "type": "button", "class": "btn btn-block btn-secondary" })));
			}
			
			return new AddControl(elemJq, embeddedEntryRetriever, pasteStrategy);
		} 
	}
	
	export interface PasteStrategy {
		clipboard: Clipboard;
		pastableEiTypeIds: string[];
	}
}