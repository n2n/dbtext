namespace Rocket.Display {
	export class EntryForm {
		private jqElem: JQuery<Element>;
		private jqEiTypeSelect: JQuery<Element> = null;
		private inited: boolean = false;
		
		constructor (jqElem: JQuery<Element>) {
			this.jqElem = jqElem;
		}
		
		public init() {
			if (this.inited) {
				throw new Error("EntryForm already initialized:");
			}
			this.inited = true;
			
			if (!this.jqElem.hasClass("rocket-multi-ei-type")) return;
			
			let jqSelector = this.jqElem.children(".rocket-ei-type-selector");
			let se = StructureElement.of(jqSelector);
			if (se && se.isGroup()) {
			    se.getToolbar(true).show().getJqControls().show().append(jqSelector);
			} else {
				jqSelector.addClass("rocket-toolbar");
			}

			this.jqEiTypeSelect = jqSelector.find("select");
			this.updateDisplay();
			
			this.jqEiTypeSelect.change(() => {
				this.updateDisplay();
			});
		}
		
		private updateDisplay() {
			if (!this.jqEiTypeSelect) return;
			
			this.jqElem.children(".rocket-ei-type-entry-form").hide();
			this.jqElem.children(".rocket-ei-type-" + this.jqEiTypeSelect.val()).show();
		}
		
		get jQuery(): JQuery<Element> {
			return this.jqElem;
		}
		
		get multiEiType(): boolean {
			return this.jqEiTypeSelect ? true : false;
		}
		
		get curEiTypeId(): string {
			if (!this.multiEiType) {
				return this.jqElem.data("rocket-ei-type-id");
			}
			
			return <string> this.jqEiTypeSelect.val();
		}
		
		set curEiTypeId(typeId: string) {
			this.jqEiTypeSelect.val(typeId);
			this.updateDisplay();
		}
		
		get curGenericLabel(): string {
			if (!this.multiEiType) {
				return this.jqElem.data("rocket-generic-label");
			}
			
			return this.jqEiTypeSelect.children(":selected").text();
		}
		
		get curGenericIconType(): string {
			if (!this.multiEiType) {
				return this.jqElem.data("rocket-generic-icon-type");
			}
			
			return this.jqEiTypeSelect.data("rocket-generic-icon-types")[this.curEiTypeId];
		}
		
		get typeMap(): { [typeId: string]: string } {
			let typeMap: { [typeId: string]: string } = {};
			
			if (!this.multiEiType) {
				typeMap[this.curEiTypeId] = this.curGenericLabel;
				return typeMap;  
			}
			
			this.jqEiTypeSelect.children().each(function () {
				let jqElem = $(this);
				typeMap[jqElem.attr("value")] = jqElem.text();
			});
			
			return typeMap;
		}
		
		public static from(jqElem: JQuery<Element>, create: boolean = true): EntryForm {
			var entryForm = jqElem.data("rocketEntryForm");
			if (entryForm instanceof EntryForm) return entryForm;
		
			if (!create) return null;
			
			entryForm = new EntryForm(jqElem);
			entryForm.init();
			jqElem.data("rocketEntryForm", entryForm);
			return entryForm;
		}
		
		public static firstOf(jqElem: JQuery<Element>): EntryForm {
			if (jqElem.hasClass("rocket-entry-form")) {
				return EntryForm.from(jqElem);
			}
			
			let jqEntryForm = jqElem.find(".rocket-entry-form:first");
			if (jqEntryForm.length == 0) return null;
			
			return EntryForm.from(jqEntryForm);
		}
		
		public static find(jqElem: JQuery<Element>, mulitTypeOnly: boolean = false): Array<EntryForm> {
			let entryForms: Array<EntryForm> = [];
			jqElem.find(".rocket-entry-form" + (mulitTypeOnly ? ".rocket-multi-ei-type": "")).each(function() {
				entryForms.push(EntryForm.from($(this)));	
			});
			return entryForms;
		}
	}	
}