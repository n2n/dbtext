namespace Rocket.Impl.Relation {
	
	export class EmbeddedEntryRetriever {
		private urlStr: string;
		private propertyPath: string;
		private draftMode: boolean;
		private startKey: number;
		private keyPrefix: string;
		private preloadNewsEnabled: boolean = false;
		private preloadedResponseObjects: Array<Jhtml.Snippet> = new Array<Jhtml.Snippet>();
		private pendingNewLookups: Array<PendingLookup> = new Array<PendingLookup>();
		public sortable: boolean = false;
		public grouped: boolean = true;
		
		constructor (lookupUrlStr: string, propertyPath: string, draftMode: boolean, startKey: number = null, 
				keyPrefix: string = null) {
			this.urlStr = lookupUrlStr;
			this.propertyPath = propertyPath;
			this.draftMode = draftMode;
			this.startKey = startKey;
			this.keyPrefix = keyPrefix;
		}
		
		public setPreloadNewsEnabled(preloadNewsEnabled: boolean) {
			if (!this.preloadNewsEnabled && preloadNewsEnabled && this.preloadedResponseObjects.length == 0) {
				this.loadNew();
			}
			
			this.preloadNewsEnabled = preloadNewsEnabled;
		}
		
		public lookupNew(doneCallback: (embeddedEntry: EmbeddedEntry, snippet: Jhtml.Snippet) => any, 
				failCallback: () => any = null) {
			this.pendingNewLookups.push({ "doneCallback": doneCallback, "failCallback": failCallback });
			
			this.checkNew()
			this.loadNew();
		}
		
//		public lookupCopy(pid: string, 
//				doneCallback: (embeddedEntry: EmbeddedEntry, snippet: Jhtml.Snippet) => any, 
//				failCallback: () => any = null) {
//			this.pendingLookups.push({ "doneCallback": })
//		}
		
		private checkNew() {
			if (this.pendingNewLookups.length == 0 || this.preloadedResponseObjects.length == 0) return;
			
			var pendingLookup: PendingLookup = this.pendingNewLookups.shift();
			let snippet: Jhtml.Snippet = this.preloadedResponseObjects.shift();
			var embeddedEntry = new EmbeddedEntry($(snippet.elements), false, this.sortable);
			
			pendingLookup.doneCallback(embeddedEntry, snippet);
		}
		
		private loadNew() {
			let url = Jhtml.Url.create(this.urlStr).extR("newmappingform", {
				"propertyPath": this.propertyPath + (this.startKey !== null ? "[" + this.keyPrefix + (this.startKey++) + "]" : ""),
				"draft": this.draftMode ? 1 : 0,
				"grouped": this.grouped ? 1 : 0
			});
			Jhtml.lookupModel(url)
					.then((result) => {
						this.doneNewResponse(result.model.snippet);
					})
					.catch(e => {
						this.failNewResponse();
						throw e;
					});
		}
		
		private failNewResponse() {
			if (this.pendingNewLookups.length == 0) return;
			
			var pendingLookup = this.pendingNewLookups.shift();
			if (pendingLookup.failCallback !== null) {
				pendingLookup.failCallback();
			}
		}
		
		private doneNewResponse(snippet: Jhtml.Snippet) {
			this.preloadedResponseObjects.push(snippet);
			this.checkNew();
		}
		
		lookupCopy(pid: string, doneCallback: (embeddedEntry: EmbeddedEntry, snippet: Jhtml.Snippet) => any, 
				failCallback: () => any = null) {
			let url = Jhtml.Url.create(this.urlStr).extR("copymappingform", {
				"pid": pid,
				"propertyPath": this.propertyPath + (this.startKey !== null ? "[" + this.keyPrefix + (this.startKey++) + "]" : ""),
				"grouped": this.grouped ? 1 : 0
			});
			Jhtml.lookupModel(url)
					.then((result) => {
						let snippet = result.model.snippet;
						doneCallback(new EmbeddedEntry($(snippet.elements), false, this.sortable), snippet);
					})
					.catch(e => {
						failCallback();
						throw e;
					});
		}
	}
	
	interface PendingLookup {
		doneCallback: (embeddedEntry: EmbeddedEntry, snippet: Jhtml.Snippet) => any;
		failCallback: () => any;
	}
}