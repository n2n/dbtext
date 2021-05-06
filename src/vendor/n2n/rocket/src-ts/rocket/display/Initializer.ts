namespace Rocket.Display {
	import Container = Rocket.Cmd.Container;
	import Page = Rocket.Cmd.Zone;
	import AdditionalTab = Rocket.Cmd.AdditionalTab;
	
    export class Initializer {
        private container: Container;
		private errorTabTitle: string;
		private displayErrorLabel: string;
		private errorIndexes: Array<ErrorIndex>;
		
		constructor(container: Container, errorTabTitle: string, displayErrorLabel: string) {
			this.container = container;
			this.errorTabTitle = errorTabTitle;
			this.displayErrorLabel = displayErrorLabel;
			this.errorIndexes = new Array<ErrorIndex>();
		}
		
		public scan() {
			var errorIndex = null;
			while (undefined !== (errorIndex = this.errorIndexes.pop())) {
				errorIndex.getTab().dispose();
			}  
			
			var zones = this.container.getAllZones();
			for (var i in zones) {
				this.scanPage(zones[i]);
			}
		}
		
		private scanPage(context: Page) {
			var that = this;
			
			var i = 0;
			
			var jqPage = context.jQuery;
			
			EntryForm.find(jqPage, true);
			
			jqPage.find(".rocket-main-group").each(function () {
				let elemJq = $(this);
				Initializer.scanGroupNav(elemJq.parent());
			});
			
			
			var errorIndex: ErrorIndex = null;
			
			jqPage.find(".rocket-message-error").each(function () {
				var structureElement = StructureElement.of($(this));
				
				if (errorIndex === null) {
					errorIndex = new ErrorIndex(context.createAdditionalTab(that.errorTabTitle, false, Display.Severity.DANGER), that.displayErrorLabel);
					that.errorIndexes.push(errorIndex);
				}
				
				errorIndex.addError(structureElement, $(this).text());
			});
		}
		
		private static scanGroupNav(jqContainer: JQuery<Element>) {
			let curGroupNav: GroupNav = null;
			
			jqContainer.children().each(function () {
				var jqElem = $(this);
				if (!jqElem.hasClass("rocket-main-group")) {
					curGroupNav = null;
					return;
				}
				
				if (curGroupNav === null) {
					curGroupNav = GroupNav.fromMain(jqElem);
				}
				
				var group = StructureElement.from(jqElem);
				if (group === null) {	
					curGroupNav.registerGroup(StructureElement.from(jqElem, true));
				}
			});
			
			return curGroupNav;
		}
    }
    
    class GroupNav {
    	private jqGroupNav: JQuery<Element>;
		private groups: Array<StructureElement>;
    
    	public constructor(jqGroupNav: JQuery<Element>) {
    		this.jqGroupNav = jqGroupNav;
			this.groups = new Array<StructureElement>();
			
			jqGroupNav.hide();
    	}
    	
    	public registerGroup(group: StructureElement) {
			this.groups.push(group);
			if (this.groups.length == 2) {
				this.jqGroupNav.show();
			}
			
			let jqA = $("<a />", { 
				"text": group.title,
				"class": "nav-link"
			});
			let jqLi = $("<li />", {
				"class": "nav-item"
			}).append(jqA);
			
			this.jqGroupNav.append(jqLi);
			
			group.jQuery.children("label:first").hide();
			
			var that = this;
			
			jqLi.click(function () {
				group.show();
			});
			
			group.onShow(function () {
				jqLi.addClass("rocket-active");
				jqA.addClass("active");
				
				for (var i in that.groups) {
					if (that.groups[i] !== group) {
						that.groups[i].hide();
					}
				}
			});
			
			group.onHide(function () {
				jqLi.removeClass("rocket-active");
				jqA.removeClass("active");
			});
			
			if (this.groups.length == 1) {
				group.show();
			} else {
				group.hide();
			}
		}
		
		public static fromMain(jqElem: JQuery<Element>) {
			var jqPrev = jqElem.prev(".rocket-main-group-nav");
			if (jqPrev.length > 0) {
				let groupNav = jqPrev.data("rocketGroupNav");
				
				if (groupNav instanceof GroupNav) return groupNav;
			}
				
			var ulJq = $("<ul />", { "class" : "rocket-main-group-nav nav nav-tabs" }).insertBefore(jqElem);
			let groupNav =  new GroupNav(ulJq);
			ulJq.data("rocketGroupNav", groupNav);
			return groupNav;
		}
    }
    
	
	class ErrorIndex {
		private jqIndex: JQuery<Element>;
		private tab: AdditionalTab;
		private displayErrorLabel: string;
		
		constructor(tab: AdditionalTab, displayErrorLabel: string) {
			this.tab = tab;
			this.displayErrorLabel = displayErrorLabel;
		}
		
		public getTab(): AdditionalTab {
			return this.tab;
		}
		
		public addError(structureElement: StructureElement, errorMessage: string) {
			var jqElem = $("<div />", {
				"class": "rocket-error-index-entry"
			}).append($("<div />", { 
				"class": "rocket-error-index-message",
				"text": errorMessage 
			})).append($("<a />", {
				"href": "#",
				"text": this.displayErrorLabel
			}));
			
			this.tab.getJqContent().append(jqElem);
		
			var clicked = false;
			var visibleSe: StructureElement = null;
			
			if (!structureElement) return;
			
			jqElem.mouseenter(function () {
				structureElement.highlight(true);
			});
			
			jqElem.mouseleave(function () {
				structureElement.unhighlight(clicked);
				clicked = false;
			});
			
			jqElem.click(function (e: any) {
				e.preventDefault();
				clicked = true;
				structureElement.show(true);
				structureElement.scrollTo();
			});
		}
	}
}