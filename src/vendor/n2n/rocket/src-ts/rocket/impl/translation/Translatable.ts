namespace Rocket.Impl.Translation {

	export class Translatable {
		private srcGuiFieldPath: string|null = null;
		private loadUrlDefs: { [localeId: string]: UrlDef } = {};
		private copyUrlDefs: { [localeId: string]: UrlDef } = {};
		private _contents: { [localeId: string]: TranslatedContent } = {}

		constructor(private jqElem: JQuery<Element>) {
			let srcLoadConfig = jqElem.data("rocket-impl-src-load-config");
			
			if (!srcLoadConfig) return;
			
			this.srcGuiFieldPath = srcLoadConfig.guiFieldPath;
			for (let localeId in srcLoadConfig.loadUrlDefs) {
				this.loadUrlDefs[localeId] = {
					label: srcLoadConfig.loadUrlDefs[localeId].label,
					url: Jhtml.Url.create(srcLoadConfig.loadUrlDefs[localeId].url)
				};
			}
			for (let localeId in srcLoadConfig.copyUrlDefs) {
				this.copyUrlDefs[localeId] = {
					label: srcLoadConfig.copyUrlDefs[localeId].label,
					url: Jhtml.Url.create(srcLoadConfig.copyUrlDefs[localeId].url)
				};
			}
		}

		get jQuery(): JQuery<Element> {
			return this.jqElem;
		}

		get localeIds(): Array<string> {
			return Object.keys(this._contents);
		}

		get contents(): Array<TranslatedContent> {
			let O: any = Object;
			return O.values(this._contents);
		}

		set visibleLocaleIds(localeIds: Array<string>) {
			for (let content of this.contents) {
				content.visible = -1 < localeIds.indexOf(content.localeId);
			}
		}

		get visibleLocaleIds() {
			let localeIds = new Array<string>();

			for (let content of this.contents) {
				if (!content.visible) continue;

				localeIds.push(content.localeId);
			}

			return localeIds;
		}
		
		set labelVisible(labelVisible: boolean) {
		    for (let content of this.contents) {
		        content.labelVisible = labelVisible;
            }
		}

		set activeLocaleIds(localeIds: Array<string>) {
			for (let content of this.contents) {
				content.active = -1 < localeIds.indexOf(content.localeId);
			}
		}

		get activeLocaleIds() {
			let localeIds = new Array<string>();

			for (let content of this.contents) {
				if (!content.active) continue;

				localeIds.push(content.localeId);
			}

			return localeIds;
		}
		
		get loadJobs(): LoadJob[] {
			if (!this.srcGuiFieldPath) return [];
			
			let loadJobs: LoadJob[] = [];
			for (let content of this.contents) {
				if (content.loaded || content.loading || !content.visible || !content.active
						|| !this.loadUrlDefs[content.localeId]) {
					continue;
				}

				loadJobs.push({
					url: this.loadUrlDefs[content.localeId].url.extR(null, { "propertyPath": content.propertyPath }),
					guiFieldPath: this.srcGuiFieldPath,
					content: content
				});
			}
			return loadJobs;
		}

		public scan() {
			this.jqElem.children().each((i, elem) => {
				let jqElem: JQuery<Element> = $(elem);
				let localeId = jqElem.data("rocket-impl-locale-id");
				if (!localeId || this._contents[localeId]) return;

				let tc = this._contents[localeId] = new TranslatedContent(localeId, jqElem);
				tc.drawCopyControl(this.copyUrlDefs, this.srcGuiFieldPath);
			});
		}

		static test(elemJq: JQuery<Element>): Translatable {
			let translatable = elemJq.data("rocketImplTranslatable");
			if (translatable instanceof Translatable) {
				return translatable;
			}

			return null;
		}

		static from(jqElem: JQuery<Element>): Translatable {
			let translatable = Translatable.test(jqElem);
			if (translatable instanceof Translatable) {
				return translatable;
			}

			translatable = new Translatable(jqElem);
			jqElem.data("rocketImplTranslatable", translatable);
			translatable.scan();
			return translatable;
		}
	}

	export interface LoadJob {
		url: Jhtml.Url;
		guiFieldPath: string;
		content: TranslatedContent
	}
	
	interface UrlDef {
		label: string,
		url: Jhtml.Url
	}

	export class TranslatedContent {
//		private jqTranslation: JQuery<Element>;
		private _propertyPath: string;
		private _pid: string;
		private _fieldJq: JQuery<Element>;
		private jqEnabler: JQuery<Element> = null;
		private copyControlJq: JQuery<Element> = null;
		private changedCallbacks: Array<() => any> = [];
		private _visible: boolean = true;

        private _labelVisible: boolean = true;
		
		constructor(private _localeId: string, private elemJq: JQuery<Element>) {
			Display.StructureElement.from(elemJq, true);
			
			
//			this.jqTranslation = jqElem.children(".rocket-impl-translation");
			this._propertyPath = elemJq.data("rocket-impl-property-path");
			this._pid = elemJq.data("rocket-impl-ei-id") || null;
			this._fieldJq = elemJq.children();
			
			this.elemJq.hide();
			this._visible = false;
		}
		
		get loaded() {
			return this.elemJq.children("div").children("div")
					.children("input[type=hidden].rocket-impl-unloaded").length == 0;
		}

		get jQuery(): JQuery<Element> {
			return this.elemJq;
		}

		get fieldJq(): JQuery<Element> {
			return this._fieldJq;
		}

		replaceField(newFieldJq: JQuery<Element>) {
			this._fieldJq.replaceWith(<JQuery<HTMLElement>> newFieldJq);
			this._fieldJq = newFieldJq;
			this.updateLabelVisiblity();
		}

		get localeId(): string {
			return this._localeId;
		}

		get propertyPath(): string {
			return this._propertyPath;
		}

		get pid(): string|null {
			return this._pid;
		}

		private findLabelJq(): JQuery<Element> {
		    return this.elemJq.find("label:first")
		}
		
		get prettyLocaleId(): string {
//			return this.elemJq.data("rocket-impl-pretty-locale");
			return this.findLabelJq().text();
		}

		get localeName(): string {
//			return this.elemJq.data("rocket-impl-locale-name");
			return this.findLabelJq().attr("title");
		}

		get visible(): boolean {
			return this._visible;
		}

		set visible(visible: boolean) {
			if (visible) {
				if (this._visible) return;
				this._visible = true;

				this.elemJq.show();
				this.triggerChanged();
				return;
			}

			if (!this._visible) return;

			this._visible = false;
			this.elemJq.hide();
			this.triggerChanged();
		}
		
		set labelVisible(labelVisible: boolean) {
		    if (this._labelVisible == labelVisible) return;
		    
		    this._labelVisible = labelVisible;
		    this.updateLabelVisiblity();
		}
		
		private updateLabelVisiblity() {
		    if (this._labelVisible) {
                this.findLabelJq().show();
            } else {
                this.findLabelJq().hide();
            }
		}

		get active(): boolean {
			return this.jqEnabler ? false : true;
		}

		set active(active: boolean) {
			if (active) {
				if (this.jqEnabler) {
					this.jqEnabler.remove();
					this.jqEnabler = null;
					this.triggerChanged();
				}

				if (this.copyControlJq) {
					this.copyControlJq.show();
				}

				this.elemJq.removeClass("rocket-inactive");
				return;
			}

			if (!this.jqEnabler) {
				this.jqEnabler = $("<button />", {
					"class": "rocket-impl-enabler",
					"type": "button",
					"text": " " + this.elemJq.data("rocket-impl-activate-label"),
					"click": () => { this.active = true}
				}).prepend($("<i />", { "class": "fa fa-language", "text": "" })).appendTo(<JQuery<HTMLElement>> this.elemJq);

				this.triggerChanged();
			}

			if (this.copyControlJq) {
				this.copyControlJq.show();
			}

			this.elemJq.addClass("rocket-inactive");
		}

		private copyControl: CopyControl;

		drawCopyControl(copyUrlDefs: { [localeId: string]: UrlDef }, guiFieldPath: string) {
			for (let localeId in copyUrlDefs) {
				if (localeId == this.localeId) continue;

				if (!this.copyControl) {
					this.copyControl = new CopyControl(this, guiFieldPath);
					this.copyControl.draw(this.elemJq.data("rocket-impl-copy-tooltip"));
				}

				this.copyControl.addUrlDef(copyUrlDefs[localeId]);
			}
		}
		
		private loaderJq: JQuery<Element>;
		
		get loading(): boolean {
			return !!this.loaderJq;
		}
		
		set loading(loading: boolean) {
			if (!loading) {
				if (!this.loaderJq) return;
				
				this.loaderJq.remove();
				this.loaderJq = null;
				return;
			}
			
			if (this.loaderJq) return;
			
			this.loaderJq = $("<div />", {
				class: "rocket-load-blocker"
			}).append($("<div></div>", { class: "rocket-loader" })).appendTo(<JQuery<HTMLElement>> this.elemJq);
		}

		private triggerChanged() {
			for (let callback of this.changedCallbacks) {
				callback();
			}
		}

		public whenChanged(callback: () => any) {
			this.changedCallbacks.push(callback);
		}
	}

	class CopyControl {

		private elemJq: JQuery<Element>;
		private menuUlJq: JQuery<Element>;
		private toggler: Display.Toggler;

		constructor(private translatedContent: TranslatedContent, private guiFieldPath: string) {
		}

		draw(tooltip: string) {
			this.elemJq = $("<div></div>", { class: "rocket-impl-translation-copy-control rocket-simple-commands" });
			this.translatedContent.jQuery.append(this.elemJq);

			let buttonJq = $("<button />", { "type": "button", "class": "btn btn-secondary" })
					.append($("<i></i>", { class: "fa fa-copy", title: tooltip }));
			let menuJq = $("<div />", { class: "rocket-impl-translation-copy-menu" })
					.append(this.menuUlJq = $("<ul></ul>"))
					.append($("<div />", { class: "rocket-impl-tooltip", text: tooltip }));

			this.toggler = Display.Toggler.simple(buttonJq, menuJq);

			this.elemJq.append(buttonJq);
			this.elemJq.append(menuJq);

//			if (!this.translatedContent.active) {
//				this.hide();
//			}
		}

		addUrlDef(urlDef: UrlDef) {
			let url = this.completeCopyUrl(urlDef.url);
			this.menuUlJq.append($("<li/>").append($("<a />", {
				"text": urlDef.label
			}).append($("<i></i>", { class: "fa fa-mail-forward"})).click((e) => {
				e.stopPropagation();
				this.copy(url);
				this.toggler.close();
			})));
		}


		private completeCopyUrl(url: Jhtml.Url) {
			return url.extR(null, {
				propertyPath: this.translatedContent.propertyPath,
				toN2nLocale: this.translatedContent.localeId,
				toPid: this.translatedContent.pid
			});
		}


		private copy(url: Jhtml.Url) {
			if (this.translatedContent.loading) return;
			
			let lje = new LoadJobExecuter();

			lje.add({
				content: this.translatedContent,
				guiFieldPath: this.guiFieldPath,
				url: url
			});
			
			lje.exec();
		}

		private replace(snippet: Jhtml.Snippet) {
//			let newFieldJq = $(snippet.elements).children();
//			this.translatedContent.replaceField(newFieldJq);
//			snippet.elements = newFieldJq.toArray();
//			snippet.markAttached();
//
//			this.loaderJq.remove();
//			this.loaderJq = null;
		}
	}
}