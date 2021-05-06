namespace Rocket.Impl.Overview {
	import cmd = Rocket.Cmd;
	import impl = Rocket.Impl.Overview;
	
	var $ = jQuery;

	export class Header {
		private jqElem: JQuery<Element>;
		private state: State;
		private quicksearchForm: QuicksearchForm;
		private critmodSelect: CritmodSelect;
		private critmodForm: CritmodForm;
		
		constructor(private overviewContent: OverviewContent) {
		}
		
		init(jqElem: JQuery<Element>) {
			this.jqElem = jqElem;
			
			this.state = new State(this.overviewContent);
			this.state.draw(this.jqElem.find(".rocket-impl-state:first"));
			
			this.quicksearchForm = new QuicksearchForm(this.overviewContent);
			this.quicksearchForm.init(this.jqElem.find("form.rocket-impl-quicksearch:first"));
			
			this.critmodForm = new CritmodForm(this.overviewContent);
			this.critmodForm.init(this.jqElem.find("form.rocket-impl-critmod:first"));
			
			this.critmodSelect = new CritmodSelect(this.overviewContent);
			this.critmodSelect.init(this.jqElem.find("form.rocket-impl-critmod-select:first"), this.critmodForm);
			
			this.critmodForm.drawControl(this.critmodSelect.jQuery.parent());
		}
	}
	
	class State {
		private jqElem: JQuery<Element>;
		private jqAllButton: JQuery<Element>;
		private jqSelectedButton: JQuery<Element>;
		
		constructor(private overviewContent: OverviewContent) {
		}
		
		public draw(jqElem: JQuery<Element>) {
			this.jqElem = jqElem;
			var that = this;
			
			this.jqAllButton = $("<button />", { "type": "button", "class": "btn btn-secondary" }).appendTo(<JQuery<HTMLElement>> jqElem);
			this.jqAllButton.click(function () {
				that.overviewContent.showAll();
				that.reDraw();
			});
			
			this.jqSelectedButton = $("<button />", { "type": "button", "class": "btn btn-secondary" }).appendTo(<JQuery<HTMLElement>> jqElem);
			this.jqSelectedButton.click(function () {
				that.overviewContent.showSelected();
				that.reDraw();
			});
			
			this.reDraw();
			
			this.overviewContent.whenContentChanged(function () { that.reDraw(); });
			this.overviewContent.whenSelectionChanged(function () { that.reDraw(); }); 
		}
		
		public reDraw() {
			var numEntries = this.overviewContent.numEntries;
			if (numEntries == 1) {
				this.jqAllButton.text(numEntries + " " + this.jqElem.data("entries-label"));
			} else {
				this.jqAllButton.text(numEntries + " " + this.jqElem.data("entries-plural-label"));
			}
			
			if (this.overviewContent.selectedOnly) {
				this.jqAllButton.removeClass("active");
				this.jqSelectedButton.addClass("active");
			} else {
				this.jqAllButton.addClass("active");
				this.jqSelectedButton.removeClass("active");
			}
			
			if (!this.overviewContent.selectable) {
				this.jqSelectedButton.hide();
				return;
			}
			
			this.jqSelectedButton.show();
			
			var numSelected = this.overviewContent.numSelectedEntries;
			if (numSelected == 1) {
				this.jqSelectedButton.text(numSelected + " " + this.jqElem.data("selected-label"));
			} else {
				this.jqSelectedButton.text(numSelected + " " + this.jqElem.data("selected-plural-label"));
			}
			
			if (0 == numSelected) {
				this.jqSelectedButton.prop("disabled", true);
				return;
			}
			
			this.jqSelectedButton.prop("disabled", false);			
		}
	}
	
	class QuicksearchForm {
		private jqSearchButton: JQuery<Element>;
		private jqSearchInput: JQuery<Element>;
		private form: Jhtml.Ui.Form;
		private jqForm: JQuery<Element>;
		
		constructor(private overviewContent: OverviewContent) {
		}
		
		public init(jqForm: JQuery<Element>) {
			if (this.form) {
				throw new Error("Quicksearch already initialized.");
			}
			
			this.jqForm = jqForm;
			this.form = Jhtml.Ui.Form.from(<HTMLFormElement> jqForm.get(0));
			
			this.form.on("submit", () => {
				this.onSubmit();
			});
			
			this.form.config.disableControls = false;
			this.form.config.actionUrl = jqForm.data("rocket-impl-post-url");
			this.form.config.successResponseHandler = (response: Jhtml.Response) => {
				if (!response.model || !response.model.snippet) return false;
				
				this.whenSubmitted(response.model.snippet, response.additionalData);
				return true;
			}
			
			this.initListeners();
		}
		
		private initListeners() {
			this.form.reset();
			var jqButtons = this.jqForm.find("button[type=submit]");
			this.jqSearchButton = $(jqButtons.get(0));
			var jqClearButton = $(jqButtons.get(1));
			this.jqSearchInput = this.jqForm.find("input[type=search]:first");
			var that = this;
			
			this.jqSearchInput.on("paste keyup", function () {
				that.send(false);
			});
			
			this.jqSearchInput.on("change", function () {
				that.send(true);
			});
			
			jqClearButton.on("click", function () {
				that.jqSearchInput.val("");	
				that.updateState();
			});
		}
		
		private sc = 0;
		private serachVal: string = null;
		
		private updateState() {
			if (this.jqSearchInput.val().toString().length > 0) {
				this.jqForm.addClass("rocket-active");
			} else {
				this.jqForm.removeClass("rocket-active");
			}
		}
		
		private send(force: boolean) {
			var searchVal = this.jqSearchInput.val().toString();
			
			if (this.serachVal == searchVal) return;
			
			this.updateState();
			this.overviewContent.clear(true);
			
			this.serachVal = searchVal;
			
			var si = ++this.sc;
			var that = this;
			
			if (force) {
				that.jqSearchButton.click();
				return;
			}
			
			setTimeout(function () {
				if (si !== that.sc) return;
				
				that.jqSearchButton.click();
			}, 300);

		}
		
		private onSubmit() {
			this.sc++;
			this.overviewContent.clear(true);
		}

		private whenSubmitted(snippet: Jhtml.Snippet, info: any) {
			this.overviewContent.initFromResponse(snippet, info);
		}
	}
	
	class CritmodSelect {
		private form: Jhtml.Ui.Form;
		private critmodForm: CritmodForm;
		
		private jqForm: JQuery<Element>;
		private jqSelect: JQuery<Element>;
		private jqButton: JQuery<Element>;
		
		constructor(private overviewContent: OverviewContent) {
		}
		
		get jQuery(): JQuery<Element> {
			return this.jqForm;
		}
		
		public init(jqForm: JQuery<Element>, critmodForm: CritmodForm) {
			if (this.form) {
				throw new Error("CritmodSelect already initialized.");
			}
			
			this.jqForm = jqForm;
			this.form = Jhtml.Ui.Form.from(<HTMLFormElement> jqForm.get(0));
			this.form.reset();
			
			this.critmodForm = critmodForm;
			
			this.jqButton = jqForm.find("button[type=submit]").hide();
			
			this.form.config.disableControls = false;
			this.form.config.actionUrl = jqForm.data("rocket-impl-post-url");
			this.form.config.autoSubmitAllowed = false;
			
			this.form.config.successResponseHandler = (response: Jhtml.Response) => {
				if (response.model && response.model.snippet) {
					this.whenSubmitted(response.model.snippet, response.additionalData);
					return true;
				}
				
				return false;
			}
			
			this.jqSelect = jqForm.find("select:first").change(() => {
				this.send();
			});
			
			critmodForm.onChange(() => {
				this.form.abortSubmit();
				this.updateId();
			});
			
			critmodForm.whenChanged((idOptions) => {
				this.updateIdOptions(idOptions);
			});
		}
		
//		private sc = 0;
//		private serachVal = null;
//		
		private updateState() {
			if (this.jqSelect.val()) {
				this.jqForm.addClass("rocket-active");
			} else {
				this.jqForm.removeClass("rocket-active");
			}
		}
		
		private send() {
			this.form.submit({ button: this.jqButton.get(0) });
		
			this.updateState();
			this.overviewContent.clear(true);
			
			var id = this.jqSelect.val();
			this.critmodForm.activated = id ? true : false;
			this.critmodForm.critmodSaveId = id.toString();
			this.critmodForm.freeze(); 
		}
		
		private whenSubmitted(snippet: Jhtml.Snippet, info: any) {
			this.overviewContent.initFromResponse(snippet, info);
			this.critmodForm.reload();
		}
		
		private updateId() {
			var id = this.critmodForm.critmodSaveId;
			if (id && isNaN(parseInt(id))) {
				this.jqSelect.append($("<option />", { "value": id, "text": this.critmodForm.critmodSaveName }));
			}
			
			this.jqSelect.val(id);
			this.updateState();
			
		}
		
		private updateIdOptions(idOptions: {[key: string]: string}) {
			this.jqSelect.empty();
			
			for (let id in idOptions) {
				this.jqSelect.append($("<option />", { value: id.trim(), text: idOptions[id] }));	
			}	
			
			this.jqSelect.val(this.critmodForm.critmodSaveId);
		}
	}
	
	class CritmodForm {
		private jqForm: JQuery<Element>;
		private form: Jhtml.Ui.Form;
		
		private jqApplyButton: JQuery<Element>;
		private jqClearButton: JQuery<Element>;
		private jqNameInput: JQuery<Element>;
		private jqSaveButton: JQuery<Element>;
		private jqSaveAsButton: JQuery<Element>;
		private jqDeleteButton: JQuery<Element>;
		
		private jqControlContainer: JQuery<Element>;
		private jqOpenButton: JQuery<Element>;
		private jqEditButton: JQuery<Element>;
		private jqCloseButton: JQuery<Element>;
		
		private changeCallbacks: Array<() => any> = [];
		private changedCallbacks: Array<(idOptions: {[key: string]: string}) => any> = [];
		
		private _open: boolean = true;
		
		constructor(private overviewContent: OverviewContent) {
		}
		
		public drawControl(jqControlContainer: JQuery<Element>) {
			this.jqControlContainer = jqControlContainer;
			
			this.jqOpenButton = $("<button />", { 
						"class": "btn btn-secondary", 
						"text": jqControlContainer.data("rocket-impl-open-filter-label") + " "
					})
					.append($("<i />", { "class": "fa fa-filter"}))
					.click(() => { this.open = true })
					.appendTo(<JQuery<HTMLElement>> jqControlContainer);
			
			this.jqEditButton = $("<button />", { 
						"class": "btn btn-secondary", 
						"text": jqControlContainer.data("rocket-impl-edit-filter-label") + " "
					})
					.append($("<i />", { "class": "fa fa-filter"}))
					.click(() => { this.open = true })
					.appendTo(<JQuery<HTMLElement>> jqControlContainer);
			
			this.jqCloseButton = $("<button />", { 
						"class": "btn btn-secondary", 
						"text": jqControlContainer.data("rocket-impl-close-filter-label") + " "
					})
					.append($("<i />", { "class": "fa fa-times"}))
					.click(() => { this.open = false })
					.appendTo(<JQuery<HTMLElement>> jqControlContainer);
			
			this.open = false;
		}
		
		private updateControl() {
			if (!this.jqOpenButton) return;
			
			if (this.open) {
				this.jqControlContainer.addClass("rocket-open");
				this.jqOpenButton.hide();
				this.jqEditButton.hide();
				this.jqCloseButton.show();
				return;
			} 
			
			this.jqControlContainer.removeClass("rocket-open");
			
			if (this.critmodSaveId) {
				this.jqOpenButton.hide();
				this.jqEditButton.show();
			} else {
				this.jqOpenButton.show();
				this.jqEditButton.hide();
			}
		
			this.jqCloseButton.hide();
		}
		
		get open() {
			return this._open;
		}
		
		set open(open: boolean) {
			this._open = open;
			
			if (open) {
				this.jqForm.show();
			} else {
				this.jqForm.hide();
			}
			
			this.updateControl();
		}
		
		public init(jqForm: JQuery<Element>) {
			if (this.form) {
				throw new Error("CritmodForm already initialized.");
			}
			
			this.jqForm = jqForm;
			this.form = Jhtml.Ui.Form.from(<HTMLFormElement> jqForm.get(0));
			this.form.reset();
			
			this.form.config.actionUrl = jqForm.data("rocket-impl-post-url");

			this.form.config.successResponseHandler = (response: Jhtml.Response) => {
				if (response.model && response.model.snippet) {
					this.whenSubmitted(response.model.snippet, response.additionalData);
					return true;
				}
				
				return false;
			};
			
			var activateFunc = (ensureCritmodSaveId: boolean) => { 
				this.activated = true;
				
				if (ensureCritmodSaveId && !this.critmodSaveId) {
					this.critmodSaveId = "new";
				}
				this.onSubmit();
			}
			var deactivateFunc = () => { 
				this.activated = false; 
				this.critmodSaveId = null;
				
				this.block();
				this.onSubmit();
			}
			
			this.jqApplyButton = jqForm.find(".rocket-impl-critmod-apply").click(function () { activateFunc(false); });
			this.jqClearButton = jqForm.find(".rocket-impl-critmod-clear").click(function () { deactivateFunc(); });
			this.jqNameInput = jqForm.find(".rocket-impl-critmod-name");
			this.jqSaveButton = jqForm.find(".rocket-impl-critmod-save").click(function () { activateFunc(true); });
			this.jqSaveAsButton = jqForm.find(".rocket-impl-critmod-save-as").click(() => {
				this.critmodSaveId = null;
				activateFunc(true); 
			});
			this.jqDeleteButton = jqForm.find(".rocket-impl-critmod-delete").click(function () { deactivateFunc(); });
			
			this.updateState();
		}
		
		get activated(): boolean {
			return this.jqForm.hasClass("rocket-active");
		}
		
		set activated(activated: boolean) {
			if (activated) {
				this.jqForm.addClass("rocket-active");
			} else {
				this.jqForm.removeClass("rocket-active");
			}
		}
		
		get critmodSaveId(): string {
			return this.jqForm.data("rocket-impl-critmod-save-id");
		}
		
		set critmodSaveId(critmodSaveId: string) {
			this.jqForm.data("rocket-impl-critmod-save-id", critmodSaveId);
			
			this.updateControl();
		}
		
		get critmodSaveName(): string {
			return this.jqNameInput.val().toString();
		}
						
		private updateState() {
			if (this.critmodSaveId) {
				this.jqSaveAsButton.show();
				this.jqDeleteButton.show();
			} else {
				this.jqSaveAsButton.hide();
				this.jqDeleteButton.hide();
			}
		}
		
		private jqBlocker: JQuery<Element>;
		
		public freeze() {
			this.form.abortSubmit();
			this.form.disableControls();
			
			this.block();
		}
		
		private block() {
			if (this.jqBlocker) return;
			
			this.jqBlocker = $("<div />", { "class": "rocket-impl-critmod-blocker" })
					.appendTo(<JQuery<HTMLElement>> this.jqForm);
		}
		
		public reload() {
			var url = this.form.config.actionUrl;

			Jhtml.Monitor.of(this.jqForm.get(0)).lookupModel(Jhtml.Url.create(url)).then((result) => {
				this.replaceForm(result.model.snippet, result.response.additionalData);
			});
			
//			var that = this;
//			$.ajax({
//				"url": url,
//				"dataType": "json"
//			}).fail(function (jqXHR: any, textStatus: any, data: any) {
//				if (jqXHR.status != 200) {
//                    Rocket.getContainer().handleError(url, jqXHR.responseText);
//					return;
//				}
//				
//				throw new Error("invalid response");
//			}).done(function (data: any, textStatus: any, jqXHR: any) {
//				that.replaceForm(data);
//			});
		}
		
		private onSubmit() {
			this.changeCallbacks.forEach(function (callback) {
				callback();
			});
			
			this.overviewContent.clear(true);
		}
		
		private whenSubmitted(snippet: Jhtml.Snippet, info: any) {
			this.overviewContent.init(1);
			
			this.replaceForm(snippet, info);
		}
		
		private replaceForm(snippet: Jhtml.Snippet, info: any) {
			if (this.jqBlocker) {
				this.jqBlocker.remove();
				this.jqBlocker = null;
			}
			
			var jqForm = $(snippet.elements);
			this.jqForm.replaceWith(<JQuery<HTMLElement>> jqForm);
			this.form = null;
			snippet.markAttached();
			this.init(jqForm);
			
			this.open = this.open;
			this.updateControl();
			
			var idOptions = info.critmodSaveIdOptions;
			this.changedCallbacks.forEach(function (callback) {
				callback(idOptions);
			});
		}
		
		public onChange(callback: () => any) {
			this.changeCallbacks.push(callback);
		}
		
		public whenChanged(callback: (idOptions: {[key: string]: string}) => any) {
			this.changedCallbacks.push(callback);
		}
	};
}