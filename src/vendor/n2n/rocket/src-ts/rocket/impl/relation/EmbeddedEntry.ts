namespace Rocket.Impl.Relation {
	
	export class EmbeddedEntry {
		private entryGroup: Rocket.Display.StructureElement;
		private jqOrderIndex: JQuery<Element>;
		private jqSummary: JQuery<Element>;
		
		private jqPageCommands: JQuery<Element>;
		private bodyGroup: Rocket.Display.StructureElement;
		private toolbar: Rocket.Display.Toolbar;
		private _entryForm: Rocket.Display.EntryForm;
		
		private jqExpMoveUpButton: JQuery<Element>;
		private jqExpMoveDownButton: JQuery<Element>;
		private jqExpRemoveButton: JQuery<Element>;
		private jqRedFocusButton: JQuery<Element>;
		private jqRedRemoveButton: JQuery<Element>;
	
		private jqRedCopyButton: JQuery<Element>;
		private jqExpCopyButton: JQuery<Element>;
		private copyCbr: Jhtml.Util.CallbackRegistry<() => any> = new Jhtml.Util.CallbackRegistry();
		
		constructor(jqEntry: JQuery<Element>, private readOnly: boolean, sortable: boolean, copyable: boolean = false) {
			this.entryGroup = Rocket.Display.StructureElement.from(jqEntry, true);
			
			let groupJq = jqEntry.children(".rocket-impl-body");
			if (groupJq.length == 0) {
				groupJq = jqEntry;
			}
			this.bodyGroup = Rocket.Display.StructureElement.from(groupJq, true);
			 
			this.jqOrderIndex = jqEntry.children(".rocket-impl-order-index").hide();
			this.jqSummary = jqEntry.children(".rocket-impl-summary");
			
			this.jqPageCommands = this.bodyGroup.jQuery.children(".rocket-zone-commands");

			let rcl = new Rocket.Display.CommandList(this.jqSummary.children(".rocket-simple-commands"), true);
			let tbse = null;
			if (!this.bodyGroup.isGroup() && null !== (tbse = Display.StructureElement.findFirst(groupJq))) {
				this.toolbar = tbse.getToolbar(true);
			} else {
				this.toolbar = this.bodyGroup.getToolbar(true);
			}
			
			let ecl = this.toolbar.getCommandList();
			
			if (copyable) {
				let config = { 
					iconType: "fa fa-copy", label: "Copy", 
					severity: Rocket.Display.Severity.WARNING 
				};
				let onClick = () => {
					this.copied = !this.copied;
				};

				this.jqExpCopyButton = ecl.createJqCommandButton(config).click(onClick);
				this.jqRedCopyButton = rcl.createJqCommandButton(config).click(onClick);
			}
			
			if (readOnly) {
				
				this.jqRedFocusButton = rcl.createJqCommandButton({iconType: "fa fa-file", label: "Detail", 
						severity: Rocket.Display.Severity.SECONDARY});
			} else {
				this._entryForm = Rocket.Display.EntryForm.firstOf(jqEntry);
				
				if (sortable) {
					this.jqExpMoveUpButton = ecl.createJqCommandButton({ iconType: "fa fa-arrow-up", label: "Move up" });
					this.jqExpMoveDownButton = ecl.createJqCommandButton({ iconType: "fa fa-arrow-down", label: "Move down"});
				} 
				
				this.jqExpRemoveButton = ecl.createJqCommandButton({ iconType: "fa fa-trash-o", label: "Remove", 
						severity: Rocket.Display.Severity.DANGER }); 
				
				this.jqRedFocusButton = rcl.createJqCommandButton({ iconType: "fa fa-pencil", label: "Edit", 
						severity: Rocket.Display.Severity.WARNING });
				this.jqRedRemoveButton = rcl.createJqCommandButton({ iconType: "fa fa-trash-o", label: "Remove", 
						severity: Rocket.Display.Severity.DANGER });
				
				let formElemsJq = this.bodyGroup.jQuery.find("input, textarea, select, button");
				let changedCallback = () => { 
					this.changed();
					formElemsJq.off("change", changedCallback);
				};
				formElemsJq.on("change", changedCallback);
			}
			
			if (!sortable) {
				jqEntry.find(".rocket-impl-handle:first").addClass("rocket-not-sortable");
			}
			
			this.reduce();
			
			jqEntry.data("rocketImplEmbeddedEntry", this);
			
			if (this.toolbar.isEmpty()) {
			    this.toolbar.hide();
			}
		}
		
		get entryForm(): Rocket.Display.EntryForm|null {
			return this._entryForm;
		}
		
		public onMove(callback: (up: boolean) => any) {
			if (this.readOnly || !this.jqExpMoveUpButton) return;
			
			this.jqExpMoveUpButton.click(function () {
				callback(true);
			});
			this.jqExpMoveDownButton.click(function () {
				callback(false);
			});
		}
				
		public onRemove(callback: () => any) {
			if (this.readOnly) return;
			
			this.jqExpRemoveButton.click(function () {
				callback();
			});
			this.jqRedRemoveButton.click(function () {
				callback();
			});
		}
		
		public onFocus(callback: () => any) {
			this.jqRedFocusButton.click(function () {
				callback();
			});
			
			this.bodyGroup.onShow(function () {
				callback();
			});
		}
		
		get copyable(): boolean {
			return !!this.jqExpCopyButton;
		}
		
		get copied() : boolean {
			return this.jqExpCopyButton && this.jqExpCopyButton.hasClass("active");
		}
		
		set copied(copied) {
			if (!this.jqExpCopyButton) {
				throw new Error("Not copyable.");
			}
			
			if (this.copied == copied) return;
			
			if (copied) {
				this.jqExpCopyButton.addClass("active");
				this.jqRedCopyButton.addClass("active");
			} else {
				this.jqExpCopyButton.removeClass("active");
				this.jqRedCopyButton.removeClass("active");
			}
			
			this.copyCbr.fire();
		}
		
		onCopy(callback: () => any) {
			if (!this.jqRedCopyButton) {
				throw new Error("EmbeddedEntry not copyable.");
			}
			
			this.copyCbr.on(callback);
		}
        
        get jQuery(): JQuery<Element> {
            return this.entryGroup.jQuery;
        }
		
		public getExpandedCommandList(): Rocket.Display.CommandList {
			return this.toolbar.getCommandList();
		}
		
		public expand(asPartOfList: boolean = true) {
			this.entryGroup.show();
			this.jqSummary.hide();
			this.bodyGroup.show();
			
//			this.entryGroup.setGroup(true);
			
			if (asPartOfList) {
				this.jqPageCommands.hide();
			} else {
				this.jqPageCommands.show();
			}

			if (this.readOnly) return;
			
			if (asPartOfList) {
				if (this.jqExpMoveUpButton) this.jqExpMoveUpButton.show();
				if (this.jqExpMoveDownButton) this.jqExpMoveDownButton.show();
				this.jqExpRemoveButton.show();
				this.jqPageCommands.hide();
			} else {
				if (this.jqExpMoveUpButton) this.jqExpMoveUpButton.hide();
				if (this.jqExpMoveDownButton) this.jqExpMoveDownButton.hide();
				this.jqExpRemoveButton.hide();
				this.jqPageCommands.show();
			}
		}
		
		public reduce() {
			this.entryGroup.show();
			this.jqSummary.show();
			this.bodyGroup.hide();
			
			let jqContentType = this.jqSummary.find(".rocket-impl-content-type:first");
			if (this.entryForm) {
				jqContentType.children("span").text(this.entryForm.curGenericLabel);
				jqContentType.children("i").attr("class", this.entryForm.curGenericIconType);
			}
			
			this.entryGroup.type = Display.StructureElement.Type.NONE;
		}
		
		public hide() {
			this.entryGroup.hide();
		}
		
		public setOrderIndex(orderIndex: number) {
			this.jqOrderIndex.val(orderIndex);
		}
	
		public getOrderIndex(): number {
			return parseInt(<string> this.jqOrderIndex.val());
		}
		
		public setMoveUpEnabled(enabled: boolean) {
			if (this.readOnly || !this.jqExpMoveUpButton) return;
			
			if (enabled) {
				this.jqExpMoveUpButton.show();
			} else {
				this.jqExpMoveUpButton.hide();
			}
		}
		
		public setMoveDownEnabled(enabled: boolean) {
			if (this.readOnly || !this.jqExpMoveDownButton) return;
			
			if (enabled) {
				this.jqExpMoveDownButton.show();
			} else {
				this.jqExpMoveDownButton.hide();
			}
		}
		
		public dispose() {
			this.jQuery.remove();
		}
		
		private changed() {
			let divJq = this.jqSummary.children(".rocket-impl-content");
			divJq.empty();
			divJq.append($("<div />", { "class": "rocket-impl-status", "text": this.jQuery.data("rocket-impl-changed-text") }));
		}
				
		private _entry: Rocket.Display.Entry|null = null;
		
		get entry(): Rocket.Display.Entry|null {
			if (this._entry) return this._entry;
			
			return this._entry = Display.Entry.find(this.jQuery, true)
		}
		
//		public static from(jqElem: JQuery, create: boolean = false): EmbeddedEntry {
//			var entry = jqElem.data("rocketImplEmbeddedEntry");
//			if (entry instanceof EmbeddedEntry) {
//				return entry;
//			}
//			
//			if (create) {
//				return new EmbeddedEntry(jqElem); 				
//			}
//			
//			return null;
//		}
	}
}