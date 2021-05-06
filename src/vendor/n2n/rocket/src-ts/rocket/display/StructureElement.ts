namespace Rocket.Display {

	export class StructureElement {
		private jqElem: JQuery<Element>;
		private onShowCallbacks: Array<(se: StructureElement) => any> = [];
		private onHideCallbacks: Array<(se: StructureElement) => any> = [];
		private toolbar: Toolbar = null;
		
		constructor(jqElem: JQuery<Element>) {
			this.jqElem = jqElem;
			
//			jqElem.addClass("rocket-structure-element");
			jqElem.data("rocketStructureElement", this);
			
			this.valClasses();
		}
		
		private valClasses() {
			if (this.isItem() || this.isGroup()) {
				this.jqElem.removeClass("rocket-structure-element");
			} else {
				this.jqElem.addClass("rocket-structure-element");
			}
		}
		
		get jQuery(): JQuery<Element> {
			return this.jqElem;
		}
		
		get contentJq() {
			let contentJq = this.jqElem.children(".rocket-control");
			
			if (contentJq.length > 0) {
				return contentJq;
			}
			
			return $("<div />", { "class": "rocket-control" }).appendTo(<JQuery<HTMLElement>> this.jqElem);
		}
		
		set type(type: StructureElement.Type) {
			this.jqElem.removeClass("rocket-item");
			this.jqElem.removeClass("rocket-group");
			this.jqElem.removeClass("rocket-simple-group");
			this.jqElem.removeClass("rocket-light-group");
			this.jqElem.removeClass("rocket-main-group");
			this.jqElem.removeClass("rocket-panel");
			
			switch(type) {
			case StructureElement.Type.ITEM:
				this.jqElem.addClass("rocket-item");
				break;
			case StructureElement.Type.SIMPLE_GROUP:
				this.jqElem.addClass("rocket-group");
				this.jqElem.addClass("rocket-simple-group");
				break;
			case StructureElement.Type.LIGHT_GROUP:
				this.jqElem.addClass("rocket-group");
				this.jqElem.addClass("rocket-light-group");
				break;
			case StructureElement.Type.MAIN_GROUP:
				this.jqElem.addClass("rocket-group");
				this.jqElem.addClass("rocket-main-group");
				break;
			case StructureElement.Type.PANEL:
				this.jqElem.addClass("rocket-panel");
				break;
			}
			
			this.valClasses();
		}
		
		public isGroup(): boolean {
			return this.jqElem.hasClass("rocket-group");
		}
		
		public isPanel(): boolean {
			return this.jqElem.hasClass("rocket-panel");
		}
		
		public isItem(): boolean {
			return this.jqElem.hasClass("rocket-item");
		}
		
		public getToolbar(createIfNotExists: boolean): Toolbar|null {
			if (!createIfNotExists || this.toolbar !== null) {
				return this.toolbar;
			}
			
//			if (!this.isGroup()) {
//				return null;
//			}
			
			let toolbarJq = this.jqElem.find(".rocket-toolbar:first")
					.filter((index, elem) => {
						return this === StructureElement.of($(elem));
					});
			if (toolbarJq.length == 0) {
				toolbarJq = $("<div />", { "class": "rocket-toolbar" });
				this.jqElem.prepend(toolbarJq);
			}
			
			return this.toolbar = new Toolbar(toolbarJq);
		}
		
		get title() {
			return this.jqElem.children("label:first").text();
		}
		
		set title(title: string|null) {
			let labelJq = this.jqElem.children("label:first");
			
			if (title === null) {
				labelJq.remove();
			}
			
			if (labelJq.length > 0) {
				labelJq.text(title);
				return;
			}
			
			this.jqElem.prepend($("<label />", { text: title }));
		}
		
		public getParent(): StructureElement {
			return StructureElement.of(this.jqElem.parent());
		}
		
		public isVisible() {
			return this.jqElem.is(":visible");
		}
		
		public show(includeParents: boolean = false) {
			for (var i in this.onShowCallbacks) {
				this.onShowCallbacks[i](this);
			}
			
			this.jqElem.show();
			
			var parent;
			if (includeParents && null !== (parent = this.getParent())) {
				parent.show(true)
			}
		}
		
		public hide() {
			for (var i in this.onHideCallbacks) {
				this.onHideCallbacks[i](this);
			}
			
			this.jqElem.hide();
		}
		
//		public addChild(structureElement: StructureElement) {
//			var that = this;
//			structureElement.onShow(function () {
//				that.show();
//			});
//		}
		
		public onShow(callback: (group: StructureElement) => any) {
			this.onShowCallbacks.push(callback);
		}
		
		public onHide(callback: (group: StructureElement) => any) {
			this.onHideCallbacks.push(callback);
		}
		
		public scrollTo() {
			var top = this.jqElem.offset().top;
			var maxOffset = top - 50;
			
			var height = this.jqElem.outerHeight();
			var margin = $(window).height() - height;
			
			var offset = top - (margin / 2);
			
			if (maxOffset < offset) {
				offset = maxOffset;
			}
			
			$("html, body").animate({
		    	"scrollTop": offset
		    }, 250);
		}
		
		private highlightedParent: StructureElement = null;
		
		public highlight(findVisibleParent: boolean = false) {
			this.jqElem.addClass("rocket-error");
			this.jqElem.removeClass("rocket-highlight-remember");
			
			if (!findVisibleParent || this.isVisible()) return;
				
			this.highlightedParent = this;
			while (null !== (this.highlightedParent = this.highlightedParent.getParent())) {
				if (!this.highlightedParent.isVisible()) continue;
				
				this.highlightedParent.highlight();
				return;
			}
		}
		
		public unhighlight(slow: boolean = false) {
			this.jqElem.removeClass("rocket-error");
			
			if (slow) {
				this.jqElem.addClass("rocket-highlight-remember");	
			} else {
				this.jqElem.removeClass("rocket-highlight-remember");
			}
			
			if (this.highlightedParent !== null) {
				this.highlightedParent.unhighlight();
				this.highlightedParent = null;
			}
		}

		public static from(jqElem: JQuery<Element>, create: boolean = false): StructureElement {
			var structureElement = jqElem.data("rocketStructureElement");
			if (structureElement instanceof StructureElement) return structureElement;
		
			if (!create) return null;
			
			structureElement = new StructureElement(jqElem);
			jqElem.data("rocketStructureElement", structureElement);
			return structureElement;
		}
		
		public static of(jqElem: JQuery<Element>): StructureElement {
			jqElem = jqElem.closest(".rocket-structure-element, .rocket-group, .rocket-item, .rocket-panel");
			
			if (jqElem.length == 0) return null;
			
			var structureElement = jqElem.data("rocketStructureElement");
			if (structureElement instanceof StructureElement) {
				return structureElement;
			}
			
			structureElement = StructureElement.from(jqElem, true);
			jqElem.data("rocketStructureElement", structureElement);
			return structureElement;
		}
		
		public static findFirst(containerJq: JQuery<Element>): StructureElement {
			let elemsJq = containerJq.find(".rocket-structure-element, .rocket-group, .rocket-item, .rocket-panel").first();
			
			if (elemsJq.length == 0) return null;
			
			var structureElement = elemsJq.data("rocketStructureElement");
			if (structureElement instanceof StructureElement) {
				return structureElement;
			}
			
			structureElement = StructureElement.from(elemsJq, true);
			elemsJq.data("rocketStructureElement", structureElement);
			return structureElement;
		}
	}
	
	export namespace StructureElement {
		
		export enum Type {
			ITEM,
			SIMPLE_GROUP,
			MAIN_GROUP,
			LIGHT_GROUP,
			PANEL,
			NONE
		}
	}
	
	export class Toolbar {
		private jqToolbar: JQuery<Element>;
		private jqControls: JQuery<Element>;
		private commandList: CommandList;
		
		public constructor(jqToolbar: JQuery<Element>) {
			this.jqToolbar = jqToolbar;
			
			this.jqControls = jqToolbar.children(".rocket-group-controls").first();
			if (this.jqControls.length == 0) {
				this.jqControls = $("<div />", { "class": "rocket-group-controls"});
				this.jqToolbar.append(this.jqControls);
				this.jqControls.hide();
			} else if (this.jqControls.is(':empty')) {
				this.jqControls.hide();
			}
			
			var jqCommands = jqToolbar.children(".rocket-simple-commands");
			if (jqCommands.length == 0) {
				jqCommands = $("<div />", { "class": "rocket-simple-commands"});
				jqToolbar.append(jqCommands);
			}
			this.commandList = new CommandList(jqCommands, true);
		}
		
		get jQuery(): JQuery<Element> {
			return this.jqToolbar;
		}
		
		public getJqControls(): JQuery<Element> {
		    return this.jqControls;	
		}
		
		public getCommandList(): CommandList {
		    return this.commandList;
		}
		
		isEmpty(): boolean {
            return this.jqControls.is(":empty") && this.commandList.isEmpty();
        }
		
		show(): Toolbar {
		    this.jQuery.show();
		    return this;
		}
		
		hide(): Toolbar {
		    this.jQuery.hide();
		    return this;
		}
	}
	
	export class CommandList {
		private jqCommandList: JQuery<Element>;
		
		public constructor(jqCommandList: JQuery<Element>, private simple: boolean = false) {
			this.jqCommandList = jqCommandList;
			
			if (simple) {
				jqCommandList.addClass("rocket-simple-commands");
			}
		}
		
		get jQuery(): JQuery<Element> {
			return this.jqCommandList;
		}
		
		isEmpty(): boolean {
		    return this.jqCommandList.is(":empty");
		}
		
		public createJqCommandButton(buttonConfig: ButtonConfig/*, iconType: string, label: string, severity: Severity = Severity.SECONDARY, tooltip: string = null*/, prepend: boolean = false): JQuery<Element> {
			this.jqCommandList.show();
			
			if (buttonConfig.iconType === undefined) {
				buttonConfig.iconType = "fa fa-circle-o";
			}
			
			if (buttonConfig.severity === undefined) {
				buttonConfig.severity = Severity.SECONDARY;
			}
			
			var jqButton = $("<button />", { 
				"class": "btn btn-" + buttonConfig.severity 
						+ (buttonConfig.important ? " rocket-important" : "")
						+ (buttonConfig.iconImportant ? " rocket-icon-important" : "")
						+ (buttonConfig.labelImportant ? " rocket-label-important" : ""),
				"title": buttonConfig.tooltip,
				"type": "button"
			});
		
			if (this.simple) {
				jqButton.append($("<span />", {
					"text": buttonConfig.label
				})).append($("<i />", {
					"class": buttonConfig.iconType
				}));
			} else {
				jqButton.append($("<i />", {
					"class": buttonConfig.iconType
				})).append("&nbsp;").append($("<span />", {
					"text": buttonConfig.label
				}));
			}
			
			if (prepend) {
				this.jqCommandList.prepend(jqButton);
			} else {
				this.jqCommandList.append(jqButton);
			}
			
			return jqButton;
		}
		
		static create(simple: boolean = false) {
			return new CommandList($("<div />"), simple);
		}
	}
	
	export interface ButtonConfig {
		iconType?: string;
		label: string;
		severity?: Severity;
		tooltip?: string;
		important?: boolean;
		iconImportant?: boolean;
		labelImportant?: boolean;
	}
}