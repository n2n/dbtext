/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 * 
 */
namespace Rocket.Impl.Overview {
	import cmd = Rocket.Cmd;
	
	var $ = jQuery;
	
	export class OverviewPage {
		private jqPageControls: JQuery<Element>;
		
		constructor(private jqContainer: JQuery<Element>, private overviewContent: OverviewContent) {
		}
		
		public initSelector(selectorObserver: Display.SelectorObserver) {
			this.overviewContent.initSelector(selectorObserver);
		}
		
		public static findAll(jqElem: JQuery<Element>): Array<OverviewPage> {
			var oc: Array<OverviewPage> = new Array();
			
			jqElem.find(".rocket-impl-overview").each(function () {
				oc.push(OverviewPage.from($(this)));
			});
			
			return oc;
		}
		
		public static from(jqElem: JQuery<Element>): OverviewPage {
			var overviewPage: OverviewPage = jqElem.data("rocketImplOverviewPage");
			if (overviewPage instanceof OverviewPage) {
				return overviewPage;
			}
			
			var jqForm = jqElem.children("form");
			
			let overviewToolsJq = jqElem.children(".rocket-impl-overview-tools");
			var overviewContent = new OverviewContent(jqElem.find("tbody.rocket-collection:first"), 
					Jhtml.Url.create(overviewToolsJq.data("content-url")), overviewToolsJq.data("state-key"));
			
//			new PageUpdater(Rocket.Cmd.Page.of(jqElem), new Jhtml.Url(jqElem.data("overview-path")))
//					.init(overviewContent);
			
			overviewContent.initFromDom(jqElem.data("current-page"), jqElem.data("num-pages"), jqElem.data("num-entries"), 
					jqElem.data("page-size"));
			
			
			var pagination = new Pagination(overviewContent);
			pagination.draw(Rocket.Cmd.Zone.of(jqForm).menu.asideCommandList.jQuery);
			
			
			var header = new Header(overviewContent);
			header.init(jqElem.children(".rocket-impl-overview-tools"));
			
			overviewPage = new OverviewPage(jqElem, overviewContent);
			jqElem.data("rocketImplOverviewPage", overviewPage);
			
//			overviewContent.initSelector(new MultiEntrySelectorObserver(["51","53"]));
			
			return overviewPage;
		}
	}
	
	
	
	
//	
	
//	class Entry {
//		
//		constructor (private _pid: string, public identityString: string) {
//		}
//		
//		get pid(): string {
//			return this._pid;
//		}
//	}
	
//	class PageUpdater {
//		private overviewContent: OverviewContent;
//		private lastCurrentPageNo: number = null;
//		private pageUrls: Array<Jhtml.Url> = new Array<Jhtml.Url>();
//		
//		constructor(private context: cmd.Page, private overviewBaseUrl: Jhtml.Url) {
//			var that = this;
//			this.context.on(cmd.Page.EventType.ACTIVE_URL_CHANGED, function () {
//				that.contextUpdated();
//			});
//		}
//		
//		public init(overviewContent: OverviewContent) {
//			this.overviewContent = overviewContent;
//			var that = this;
//			overviewContent.whenContentChanged(function () {
//				that.contentUpdated();
//			});
//		}
//		
//		private contextUpdated() {
//			var newActiveUrl = this.context.activeUrl;
//			for (var i in this.pageUrls) {
//				if (!this.pageUrls[i].equals(newActiveUrl)) continue;
//				
//				this.overviewContent.currentPageNo = (parseInt(i) + 1);
//				return;
//			}
//		}
//		
//		private contentUpdated() {
//			if (!this.overviewContent.isInit()) return;
//			
//			var newCurPageNo = this.overviewContent.currentPageNo;
//			var newNumPages = this.overviewContent.numPages;
//			
//			if (this.pageUrls.length < newNumPages) {
//				for (let pageNo = this.pageUrls.length + 1; pageNo <= newNumPages; pageNo++) {
//					var pageUrl = this.overviewBaseUrl.extR(pageNo > 1 ? pageNo.toString() : null);
//					this.pageUrls[pageNo - 1] = pageUrl;
//					this.context.registerUrl(pageUrl);
//				}
//			} 
//			
//			var newActiveUrl = this.pageUrls[newCurPageNo - 1];
//			if (!this.context.activeUrl.equals(newActiveUrl)) {
//				this.context.layer.pushHistoryEntry(newActiveUrl);
//			}
//			
//			if (this.pageUrls.length > newNumPages) {
//				for (let pageNo = this.pageUrls.length; pageNo > newNumPages; pageNo--) {
//					this.context.unregisterUrl(this.pageUrls.pop());
//				}
//			}
//		}
//		
//	}
	
	class Pagination {
		private jqPagination: JQuery<Element>;
		private jqInput: JQuery<Element>;
		
		constructor(private overviewContent: OverviewContent) {
		}
		
		public getCurrentPageNo(): number {
			return this.overviewContent.currentPageNo;
		}
		
		public getNumPages(): number {
			return this.overviewContent.numPages;
		}
		
		public goTo(pageNo: number) {
			this.overviewContent.goTo(pageNo);
			return;
		}
		
		public draw(jqContainer: JQuery<Element>) {
			var that = this;
			
			this.jqPagination = $("<div />", { "class": "rocket-impl-overview-pagination btn-group" });
			jqContainer.append(this.jqPagination);
			
			this.jqPagination.append(
					 $("<button />", {
						"type": "button",
						"class": "rocket-impl-pagination-first btn btn-secondary",
						"click": function () { that.goTo(1) }
					}).append($("<span />", { text: 1 })).append(" ").append($("<i />", {
						"class": "fa fa-step-backward"	
					})));
			
			this.jqPagination.append(
					 $("<button />", {
						"type": "button",
						"class": "rocket-impl-pagination-prev btn btn-secondary",
						"click": function () { 
							if (that.getCurrentPageNo() > 1) {
								that.goTo(that.getCurrentPageNo() - 1);
							} 
						}
					}).append($("<i />", {
						"class": "fa fa-chevron-left"	
					})));
			
			this.jqInput = $("<input />", {
				"class": "rocket-impl-pagination-no form-control",
				"type": "text",
				"value": this.getCurrentPageNo()
			}).on("change", function () {
				var pageNo: number = parseInt(that.jqInput.val().toString());
				if (pageNo === NaN || !that.overviewContent.isPageNoValid(pageNo)) {
					that.jqInput.val(that.overviewContent.currentPageNo);
					return;
				}
				
				that.jqInput.val(pageNo);
				that.overviewContent.goTo(pageNo);
			});
			this.jqPagination.append(this.jqInput);
			
			this.jqPagination.append(
					$("<button />", {
						"type": "button",
						"class": "rocket-impl-pagination-next btn btn-secondary",
						"click": function () { 
							if (that.getCurrentPageNo() < that.getNumPages()) {
								that.goTo(that.getCurrentPageNo() + 1);
							} 
						}
					}).append($("<i />", {
						"class": "fa fa-chevron-right"	
					})));
		
			this.jqPagination.append(
					 $("<button />", {
						"type": "button",
						"class": "rocket-impl-pagination-last btn btn-secondary",
						"click": function () { that.goTo(that.getNumPages()); }
					}).append($("<i />", {
						"class": "fa fa-step-forward"
					})).append(" ").append($("<span />", { text: that.getNumPages() })));
			
			
			let contentChangedCallback = function () {
				if (!that.overviewContent.isInit() || that.overviewContent.selectedOnly || that.overviewContent.numPages <= 1) {
					that.jqPagination.hide();
				} else {
					that.jqPagination.show();
				}
				that.jqInput.val(that.overviewContent.currentPageNo);
			};
			this.overviewContent.whenContentChanged(contentChangedCallback);		
			contentChangedCallback();
		}
	}
	
	class FixedHeader {
		private numEntries: number;
		
		private jqHeader: JQuery<Element>;
		private jqTable: JQuery<Element>;
		private jqTableClone: JQuery<Element>;
		
		public constructor(numEntries: number) {
			this.numEntries = numEntries;	
		}
		
		public getNumEntries(): number {
			return this.numEntries;	
		}
		
		public draw(jqHeader: JQuery<Element>, jqTable: JQuery<Element>) {
			this.jqHeader = jqHeader;
			this.jqTable = jqTable;
			
//			this.cloneTableHeader();
			
//			var that = this;
//			$(window).scroll(function () {
//				that.scrolled();
//			});
			
//			var headerOffset = this.jqHeader.offset().top;
//			var headerHeight = this.jqHeader.height();
//			var headerWidth = this.jqHeader.width();
//			this.jqHeader.css({"position": "fixed", "top": headerOffset});
//			this.jqHeader.parent().css("padding-top", headerHeight);
			
//			this.calcDimensions();
//			$(window).resize(function () {
//				that.calcDimensions();
//			});
		}
		
		private fixedCssAttrs: any;
		
		private calcDimensions() {
			this.jqHeader.parent().css("padding-top", null);
			this.jqHeader.css("position", "relative");
			
			var headerOffset = this.jqHeader.offset();
			this.fixedCssAttrs = {
				"position": "fixed",
				"top": $("#rocket-content-container").offset().top, 
				"left": headerOffset.left, 
				"right": $(window).width() - (headerOffset.left + this.jqHeader.outerWidth()) 
			};
			
			this.scrolled();
		}
		
		private fixed: boolean = false;
		
		private scrolled() {
			var headerHeight = this.jqHeader.children().outerHeight();
			if (this.jqTable.offset().top - $(window).scrollTop() <= this.fixedCssAttrs.top + headerHeight) {
				if (this.fixed) return;
				this.fixed = true;
				this.jqHeader.css(this.fixedCssAttrs);
				this.jqHeader.parent().css("padding-top", headerHeight);
				this.jqTableClone.show();
			} else {
				if (!this.fixed) return;
				this.fixed = false;
				this.jqHeader.css({
					"position": "relative",
					"top": "", 
					"left": "", 
					"right": "" 
				});
				this.jqHeader.parent().css("padding-top", "");
				this.jqTableClone.hide();
			}
		}
		
		private cloneTableHeader() {
			this.jqTableClone = this.jqTable.clone();
			this.jqTableClone.css("margin-bottom", 0);
			this.jqTableClone.children("tbody").remove();
			this.jqHeader.append(this.jqTableClone);
			this.jqTableClone.hide();
						
			var jqClonedChildren = this.jqTableClone.children("thead").children("tr").children();
			this.jqTable.children("thead").children("tr").children().each(function(index) {
				jqClonedChildren.eq(index).innerWidth($(this).innerWidth());
//				jqClonedChildren.css({
//					"boxSizing": "border-box"	
//				});
			});
			
//			this.jqTable.children("thead").hide();
		}
	}
}
