namespace Rocket.Impl.Overview {
	import cmd = Rocket.Cmd;
	
	var $ = jQuery;
	
	export class OverviewContent {
		private collection: Display.Collection;
		private pages: { [pageNo: number]: Page } = {};
		private fakePage: Page = null;
		private selectorState: SelectorState;
		private _currentPageNo: number = null; 
		private _numPages: number;
		private _numEntries: number;
		private _pageSize: number;
		private allInfo: AllInfo = null;
		private contentChangedCallbacks: Array<(overviewContent: OverviewContent) => any> = [];
		
		constructor(jqElem: JQuery<Element>, private loadUrl: Jhtml.Url, private stateKey: string) {
			this.collection = Display.Collection.from(jqElem);
			this.selectorState = new SelectorState(this.collection);
		}	
		
		isInit(): boolean {
			return this._currentPageNo != null && this._numPages != null && this._numEntries != null;
		}
		
		initFromDom(currentPageNo: number, numPages: number, numEntries: number, pageSize: number) {
			this.reset(false);
			this._currentPageNo = currentPageNo;
			this._numPages = numPages;
			this._numEntries = numEntries;
			this._pageSize = pageSize;
			
			this.refitPages(currentPageNo);
			
			if (this.allInfo) {
				let O: any = Object;
				this.allInfo = new AllInfo(O.values(this.pages), 0);
			}
			
			this.buildFakePage();
			this.triggerContentChange();
		}
		
		private refitPages(startPageNo: number) {
			this.pages = {};
			
			this.collection.scan();

			let page: Page = null;
			let i = 0;
			for (let entry of this.collection.entries) {
				if (this.fakePage && this.fakePage.containsEntry(entry)) {
					continue;
				}
				
				if (0 == i % this.pageSize) {
					page = this.createPage((i / this._pageSize) + 1);
					page.entries = [];
				}
				page.entries.push(entry);
				
				i++;
			}
			this.pageVisibilityChanged();
		}
		
		init(currentPageNo: number) {
			this.reset(false);
			this.goTo(currentPageNo);
			
			if (this.allInfo) {
				this.allInfo = new AllInfo([this.pages[currentPageNo]], 0);
			}
			
			this.buildFakePage();
			this.triggerContentChange();
		}
		
		initFromResponse(snippet: Jhtml.Snippet, info: any) {
			this.reset(false);
			
			var page: Page = this.createPage(parseInt(info.pageNo));
			this._currentPageNo = page.pageNo;
			this.initPageFromResponse([page], snippet, info);
			
			if (this.allInfo) {
				this.allInfo = new AllInfo([page], 0);
			}
			
			this.buildFakePage();
			this.triggerContentChange();
		}
		
		clear(showLoader: boolean) {
			this.reset(showLoader);
			
			this.triggerContentChange();
		}
		
		private reset(showLoader: boolean) { 
			let page: Page = null;
			for (let pageNo in this.pages) {
				page = this.pages[pageNo];
				page.dispose();
				delete this.pages[pageNo];
				this.unmarkPageAsLoading(page.pageNo);
			}
			
			this._currentPageNo = null;
			
			if (this.fakePage) {
				this.fakePage.dispose();
				this.unmarkPageAsLoading(this.fakePage.pageNo);
				this.fakePage = null;
			}
			
			if (this.allInfo) {
				this.allInfo = new AllInfo([], 0);
			}
			
			if (showLoader) {
				this.addLoader();
			} else {
				this.removeLoader();
			}
		}
		
		private selectorObserver: Display.SelectorObserver = null;
		
		initSelector(selectorObserver: Display.SelectorObserver) {
			if (this.selectorObserver) {
				throw new Error("Selector state already activated");
			}
			
			this.selectorObserver = selectorObserver;
			this.selectorState.activate(selectorObserver);
			this.triggerContentChange();
			
			this.buildFakePage();
		}
		
		private buildFakePage() {
			if (!this.selectorObserver) return;
			
			if (this.fakePage) {
				throw new Error("Fake page already existing.");
			}
			
			this.fakePage = new Page(0);
			this.fakePage.hide();
			
			var pids = this.selectorObserver.getSelectedIds();
			var unloadedIds = pids.slice();
			var that = this;
		
			this.collection.entries.forEach(function (entry: Display.Entry) {
				let id = entry.id;
				
				let i;
				if (-1 < (i = unloadedIds.indexOf(id))) {
					unloadedIds.splice(i, 1);
				}
			});
			
			this.loadFakePage(unloadedIds);
			return this.fakePage;
		}
		
		private loadFakePage(unloadedPids: Array<string>) {
			if (unloadedPids.length == 0) {
				this.fakePage.entries = [];
				this.selectorState.observeFakePage(this.fakePage);
				return;
			}
			
			this.markPageAsLoading(0);
			
			let fakePage = this.fakePage;
			
			Jhtml.Monitor.of(this.collection.jQuery.get(0))
					.lookupModel(this.loadUrl.extR(null, { "pids": unloadedPids }))
					.then((modelResult) => {
				if (fakePage !== this.fakePage) return; 
				
				this.unmarkPageAsLoading(0);
				
				let model = modelResult.model;
				let collectionJq = $(model.snippet.elements).find(".rocket-collection:first");
				model.snippet.elements = collectionJq.children().toArray();
				fakePage.entries = Display.Entry.children(collectionJq);
				for (let entry of fakePage.entries) {
					this.collection.jQuery.append(entry.jQuery);
				}
				
				this.collection.scan();
				model.snippet.markAttached();
				
				this.selectorState.observeFakePage(fakePage);
				this.triggerContentChange();
			});
		}
		
		get selectedOnly(): boolean {
			return this.allInfo != null;
		}
		
		public showSelected() {
			var scrollTop =  $("html, body").scrollTop();
			var visiblePages = new Array<Page>();
			for (let pageNo in this.pages) {
				let page = this.pages[pageNo];
				if (page.visible) {
					visiblePages.push(page);
				}
				page.hide();
			}
			
			this.selectorState.showSelectedEntriesOnly();
			this.selectorState.autoShowSelected = true;	
			
			if (this.allInfo === null) {
				this.allInfo = new AllInfo(visiblePages, scrollTop);
			}
			
			this.updateLoader();
			this.triggerContentChange();
		}
		
//		get selectorState(): SelectorState {
//			return this._selectorState;
//		}
		
		public showAll() {
			if (this.allInfo === null) return;
			
			this.selectorState.hideEntries();
			this.selectorState.autoShowSelected = false;
			
			this.allInfo.pages.forEach(function (page: Page) {
				page.show();
			});
			this.pageVisibilityChanged();
			
			$("html, body").scrollTop(this.allInfo.scrollTop);
			this.allInfo = null;
			
			this.updateLoader();
			this.triggerContentChange();
		}
		
//		containsPid(pid: string): boolean {
//			for (let i in this.pages) {
//				if (this.pages[i].containsPid(pid)) return true;
//			}
//			
//			return false;
//		}
		
		get currentPageNo(): number {
			return this._currentPageNo;
		}
		
		get numPages(): number {
			return this._numPages;
		}
		
		get numEntries(): number {
			return this._numEntries;
		}
		
		get pageSize(): number {
			return this._pageSize;
		}
		
		get numSelectedEntries(): number {
			if (!this.collection.selectable) return null;
			
			if (!this.selectorObserver || (this.fakePage !== null && this.fakePage.isContentLoaded())) {
				return this.collection.selectedEntries.length;
			}
			
			return this.selectorObserver.getSelectedIds().length;
		}
		
		get selectable(): boolean {
			return this.collection.selectable;
		}
		
		private setCurrentPageNo(currentPageNo: number) {
			if (this._currentPageNo == currentPageNo) {
				return;
			}
			
			this._currentPageNo = currentPageNo;
			
			this.triggerContentChange();	
		}
		
		private triggerContentChange() {
			this.contentChangedCallbacks.forEach((callback) => {
				callback(this);
			});
		}
		
		private changeBoundaries(numPages: number, numEntries: number, entriesPerPage: number) {
			if (this._numPages == numPages && this._numEntries == numEntries 
					&& this._pageSize == entriesPerPage) {
				return;
			}
			
			this._numPages = numPages;
			this._numEntries = numEntries;
			
			if (this.currentPageNo > this.numPages) {
				this.goTo(this.numPages);
				return;
			}
			
			this.triggerContentChange();
		}
		
		whenContentChanged(callback: (overviewContent: OverviewContent) => any) {
			this.contentChangedCallbacks.push(callback);
		}
		
		whenSelectionChanged(callback: () => any) {
			this.selectorState.whenChanged(callback);
		}
		
		isPageNoValid(pageNo: number): boolean {
			return (pageNo > 0 && pageNo <= this.numPages);
		}
		
		containsPageNo(pageNo: number): boolean {
			return this.pages[pageNo] !== undefined;
		}
		
		private applyContents(page: Page, entries: Display.Entry[]) {
			if (page.entries !== null) {
				throw new Error("Contents already applied.");
			}
			
			page.entries = entries;
			for (var pni = page.pageNo - 1; pni > 0; pni--) {
				if (this.pages[pni] === undefined || !this.pages[pni].isContentLoaded()) continue;
				
				let aboveJq = this.pages[pni].lastEntry.jQuery;
				for (let entry of entries) {
					entry.jQuery.insertAfter(aboveJq);
					aboveJq = entry.jQuery;
					this.selectorState.observeEntry(entry);
				}
				this.collection.scan()
				return;
			}
			
			let aboveJq: JQuery<Element>;
			for (let entry of entries) {
				if (!aboveJq) {
					this.collection.jQuery.prepend(entry.jQuery);
				} else {
					entry.jQuery.insertAfter(aboveJq);
				}
				
				aboveJq = entry.jQuery;
				this.selectorState.observeEntry(entry);
			}
			this.collection.scan()
		}
		
		goTo(pageNo: number) {
			if (!this.isPageNoValid(pageNo)) {
				throw new Error("Invalid pageNo: " + pageNo);
			}
			
			if (this.selectedOnly) {
				throw new Error("No paging support for selected entries.");
			}
			
			if (pageNo === this.currentPageNo) {
				return;
			}
			
			if (this.pages[pageNo] === undefined) {
				this.load(pageNo);
				this.showSingle(pageNo);
				this.setCurrentPageNo(pageNo);
				return;
			}
			
			if (this.scrollToPage(this.currentPageNo, pageNo)) {
				this.setCurrentPageNo(pageNo);
				return;
			}
			
			this.showSingle(pageNo);
			this.setCurrentPageNo(pageNo);
			this.pageVisibilityChanged();
		}
		
		private showSingle(pageNo: number) {
			for (var i in this.pages) {
				if (this.pages[i].pageNo == pageNo) {
					this.pages[i].show();
				} else {
					this.pages[i].hide();
				}
			}
			this.pageVisibilityChanged();
		}
		
		private pageVisibilityChanged() {
			let startPageNo: number = null;

			let numPages = 0;
			for (let pageNo in this.pages) {
				if (!this.pages[pageNo].visible) continue;

				if (!startPageNo) {
					startPageNo = this.pages[pageNo].pageNo; 
				}
				numPages++;
			}
			
			if (startPageNo === null) return;
			
			let jhtmlPage = Cmd.Zone.of(this.collection.jQuery).page;
			jhtmlPage.loadUrl = jhtmlPage.url.extR((startPageNo != 1 ? startPageNo.toString() : null), 
					{ numPages: numPages, stateKey: this.stateKey});
		}
		
		private scrollToPage(pageNo: number, targetPageNo: number): boolean {
			var page: Page = null;
			if (pageNo < targetPageNo) {
				for (var i = pageNo; i <= targetPageNo; i++) {
					if (!this.containsPageNo(i) || !this.pages[i].isContentLoaded()) {
						return false;
					}
					
					page = this.pages[i];
					page.show();
				}
				this.pageVisibilityChanged();
			} else {
				for (var i = pageNo; i >= targetPageNo; i--) {
					if (!this.containsPageNo(i) || !this.pages[i].isContentLoaded() || !this.pages[i].visible) {
						return false;
					}
					
					page = this.pages[i];
				}
			}
			
			$("html, body").stop().animate({
				scrollTop: page.firstEntry.jQuery.offset().top 
			}, 500);
			
			return true;
		}	
		
		private loadingPageNos: Array<number> = new Array<number>();
		private jqLoader: JQuery<Element> = null;
		
		private markPageAsLoading(pageNo: number) {
			if (-1 < this.loadingPageNos.indexOf(pageNo)) {
				throw new Error("page already loading");
			}

			this.loadingPageNos.push(pageNo);
			this.updateLoader();
		}
		
		private unmarkPageAsLoading(pageNo: number) {
			var i = this.loadingPageNos.indexOf(pageNo);
			
			if (-1 == i) return;
			
			this.loadingPageNos.splice(i, 1);
			this.updateLoader();
		}
		
		private updateLoader() {
			for (var i in this.loadingPageNos) {
				if (this.loadingPageNos[i] == 0 && this.selectedOnly) {
					this.addLoader();
					return;
				}
				
				if (this.loadingPageNos[i] > 0 && !this.selectedOnly) {
					this.addLoader();
					return;
				}
			}
			
			this.removeLoader();
		}
		
		private addLoader() {
			if (this.jqLoader) return;
			
			this.jqLoader = $("<div />", { "class": "rocket-impl-overview-loading" })
						.insertAfter(this.collection.jQuery.parent("table"));
		}
		
		private removeLoader() {
			if (!this.jqLoader) return;
			
			this.jqLoader.remove();
			this.jqLoader = null;
		}
		
		
		private createPage(pageNo: number): Page {
			if (this.containsPageNo(pageNo)) {
				throw new Error("Page already exists: " + pageNo);
			}
			
			var page = this.pages[pageNo] = new Page(pageNo);
			if (this.selectedOnly) {
				page.hide();
			}
			return page;
		}
		
		private load(pageNo: number) {
			var page: Page = this.createPage(pageNo);
			
			this.markPageAsLoading(pageNo);
			
			Jhtml.Monitor.of(this.collection.jQuery.get(0))
					.lookupModel(this.loadUrl.extR(null, { "pageNo": pageNo }))
					.then((modelResult) => {
						if (page !== this.pages[pageNo]) return;
						
						this.unmarkPageAsLoading(pageNo);
						
						this.initPageFromResponse([page], modelResult.model.snippet, modelResult.response.additionalData);
						this.triggerContentChange();
					})
					.catch(e => {
						if (page !== this.pages[pageNo]) return;
						
						this.unmarkPageAsLoading(pageNo);
						throw e;
					});
		}
		
		private initPageFromResponse(pages: Page[], snippet: Jhtml.Snippet, data: any) {
			this.changeBoundaries(data.numPages, data.numEntries, data.pageSize);
			
			
			let collectionJq = $(snippet.elements).find(".rocket-collection:first");
			var jqContents = collectionJq.children();
			snippet.elements = jqContents.toArray();
			let entries = Display.Entry.children(collectionJq);
			
			for (let page of pages) {
				this.applyContents(page, entries.splice(0, this._pageSize));
			}
			
			snippet.markAttached();
		}
	}	
	
	
	class SelectorState {
		private fakeEntryMap: { [id: string]: Display.Entry } = {};
		private _autoShowSelected: boolean = false;
		
		constructor(private collection: Display.Collection) {
		}
		
		activate(selectorObserver: Display.SelectorObserver) {
			if (!selectorObserver) return;

			this.collection.destroySelectors();
			this.collection.setupSelector(selectorObserver);
		}
		
		observeFakePage(fakePage: Page) {
			fakePage.entries.forEach((entry: Display.Entry) => {
				if (this.collection.containsEntryId(entry.id)) {
					entry.dispose();
				} else {
					this.registerEntry(entry);
				}
			});
		}
		
		observeEntry(entry: Display.Entry) {
			if (this.fakeEntryMap[entry.id]) {
				this.fakeEntryMap[entry.id].dispose();
			}
			
			this.registerEntry(entry);
		}
		
		private registerEntry(entry: Display.Entry, fake: boolean = false) {
			this.collection.registerEntry(entry);
			if (fake) {
				this.fakeEntryMap[entry.id] = entry;
			}
			
			if (entry.selector === null) return;
			
			if (this.autoShowSelected && entry.selector.selected) {
				entry.show();
			}
			
			entry.selector.onChanged(() => {
				if (this.autoShowSelected && entry.selector.selected) {
					entry.show();
				}
			});
			var onFunc = () => {
				delete this.fakeEntryMap[entry.id];
			};
			entry.on(Display.Entry.EventType.DISPOSED, onFunc);
			entry.on(Display.Entry.EventType.REMOVED, onFunc);
		}
		
		get autoShowSelected(): boolean {
			return this._autoShowSelected;
		}
		
		set autoShowSelected(showSelected: boolean) {
			this._autoShowSelected = showSelected; 
		}
		
		showSelectedEntriesOnly() {
			this.collection.entries.forEach(function (entry: Display.Entry) {
				if (entry.selector.selected) {
					entry.show();
				} else {
					entry.hide();
				}
			});
		}
		
		hideEntries() {
			this.collection.entries.forEach(function (entry: Display.Entry) {
				entry.hide();
			});
		}
		
		whenChanged(callback: () => any) {
			this.collection.onSelectionChanged(callback);
		}
	}
	
	class AllInfo {
		constructor(public pages: Array<Page>, public scrollTop: number) {
		}
	}
	
	class Page {
		private _visible: boolean = true;
		
		constructor(public pageNo: number, public entries: Display.Entry[] = null) {
		}
		
		get visible(): boolean {
			return this._visible;
		}
		
		containsEntry(entry: Display.Entry): boolean {
			return 0 < this.entries.indexOf(entry);
		}
		
		show() {
			this._visible = true;
			this.disp();
		}
		
		hide() {
			this._visible = false;
			this.disp();
		}
		
		get firstEntry(): Display.Entry {
			if (!this.entries || !this.entries[0]) {
				throw new Error("no first entry");
			}
			
			return this.entries[0]
		}
		
		get lastEntry(): Display.Entry {
			if (!this.entries || this.entries.length == 0) {
				throw new Error("no last entry");
			}
			
			return this.entries[this.entries.length - 1];
		}
		
		dispose() {
			if (!this.isContentLoaded()) return;
				
			for (let entry of this.entries) {
				entry.dispose();
			}
			
			this.entries = null;
		}
		
		isContentLoaded(): boolean {
			return !!this.entries;
		}
		
//		set jqContents(jqContents: JQuery<Element>) {
//			this._jqContents = jqContents;
//			
//			this.entries = Display.Entry.filter(this.jqContents);
//			
//			this.disp();
//			
//			var that = this;
//			for (var i in this.entries) {
//				let entry = this.entries[i];
//				entry.on(Display.Entry.EventType.DISPOSED, function () {
//					let j = that.entries.indexOf(entry);
//					if (-1 == j) return;
//					
//					that.entries.splice(j, 1);
//				});
//			}
//		}
		
		private disp() {
			if (this.entries === null) return;
			
			this.entries.forEach((entry: Display.Entry) => {
				if (this.visible) {
					entry.show();
				} else {
					entry.hide();
				}
			});
		}
		
		removeEntryById(id: string) {
			for (var i in this.entries) {
				if (this.entries[i].id != id) continue;
				
				this.entries[i].jQuery.remove();
				this.entries.splice(parseInt(i), 1);
				return; 
			}
		}
	}
}