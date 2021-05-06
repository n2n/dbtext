namespace Rocket.Impl.Translation {

	export class ViewMenu {
		private translatables: Array<Translatable> = [];
		private jqStatus: JQuery;
		private menuUlJq : JQuery;
		private _items: { [localeId: string]: ViewMenuItem } = {};
		private changing: boolean = false;
		
		constructor(private jqContainer: JQuery<Element>) {}

		get jQuery(): JQuery<Element> {
			return this.jqContainer;
		}

		get items(): { [localeId: string]: ViewMenuItem }  {
			return this._items;
		}
		
		get numItems(): number {
		    return Object.keys(this._items).length;
		}
		
		private draw(languagesLabel: string, visibleLabel: string, tooltip: string) {
			$("<div />", { "class": "rocket-impl-translation-status" })
					.append($("<label />", { "text": visibleLabel }).prepend($("<i></i>", { "class": "fa fa-language" })))
					.append(this.jqStatus = $("<span></span>"))
					.prependTo(<JQuery<HTMLElement>> this.jqContainer);
			
			let buttonJq = new Rocket.Display.CommandList(this.jqContainer)
				.createJqCommandButton({
					iconType: "fa fa-cog",
					label: languagesLabel,
					tooltip: tooltip
				});
			
			let menuJq = $("<div />", { "class": "rocket-impl-translation-status-menu" })
					.append(this.menuUlJq = $("<ul></ul>"))
					.append($("<div />", { "class": "rocket-impl-tooltip", "text": tooltip }))
					.hide();
			Display.Toggler.simple(buttonJq, menuJq);
			
			this.jqContainer.append(menuJq);
		}	
		
		private updateStatus() {
			let prettyLocaleIds: Array<string> = [];
			for (let localeId in this._items) {
				if (!this._items[localeId].on) continue;
				
				prettyLocaleIds.push(this._items[localeId].prettyLocaleId);
			}
			
			this.jqStatus.empty();
			this.jqStatus.text(prettyLocaleIds.join(", "));
			
			let onDisabled = prettyLocaleIds.length == 1;
			
			for (let localeId in this._items) {
				this._items[localeId].disabled = onDisabled && this._items[localeId].on;
			}
		}
		
		get visibleLocaleIds(): Array<string> {
			let localeIds: Array<string> = [];
			
			for (let localeId in this._items) {
				if (!this._items[localeId].on) continue;
				
				localeIds.push(localeId);
			}
			
			return localeIds;
		}
		
		registerTranslatable(translatable: Translatable) {
			if (-1 < this.translatables.indexOf(translatable)) return;
			
			if (!this.jqStatus) {
				this.draw(translatable.jQuery.data("rocket-impl-languages-label"), 
						translatable.jQuery.data("rocket-impl-visible-label"), 
						translatable.jQuery.data("rocket-impl-languages-view-tooltip"));
			}
			
			this.translatables.push(translatable);
			
			translatable.jQuery.on("remove", () => this.unregisterTranslatable(translatable));

			let labelVisible = this.numItems > 1;
			
			for (let content of translatable.contents) {
				if (!this._items[content.localeId]) {
					let item = this._items[content.localeId] = new ViewMenuItem(content.localeId, content.localeName, content.prettyLocaleId);
					item.draw($("<li />").appendTo(<JQuery<HTMLElement>> this.menuUlJq));
					
					item.on = this.numItems == 1;
					item.whenChanged(() => this.menuChanged());
					
					this.updateStatus();
				}
				
				content.visible = this._items[content.localeId].on;
				content.labelVisible = labelVisible;
				
				content.whenChanged(() => {
					if (this.changing || !content.active) return;
					
					this._items[content.localeId].on = true;
				});
			}
		}
		
		private getNumOn(): number {
		    let num = 0;
		    for (let localeId in this._items) {
		        if (this._items[localeId].on) {
		            num++
		        }
		    }
		    return num;
		}
		
		unregisterTranslatable(translatable: Translatable) {
			let i = this.translatables.indexOf(translatable);
			
			if (-1 < i) {
				this.translatables.splice(i, 1);
			}
		}
		
		checkLoadJobs() {
			LoadJobExecuter.create(this.translatables).exec();
		}
		
		private menuChanged() {
			if (this.changing) {
				throw new Error("already changing");
			}	
			
			this.changing = true;
			
			let visiableLocaleIds: Array<string> = [];
			
			for (let i in this._items) {
				if (this._items[i].on) {
					visiableLocaleIds.push(this._items[i].localeId);
				} 
			}
			let labelVisible = this.numItems > 1;
			
			for (let translatable of this.translatables) {
				translatable.visibleLocaleIds = visiableLocaleIds;
                translatable.labelVisible = labelVisible;
			}
			
			this.updateStatus();
			this.checkLoadJobs();
			this.changing = false;
		}

		static from(jqElem: JQuery<Element>): ViewMenu {
			let vm = jqElem.data("rocketImplViewMenu");
			if (vm instanceof ViewMenu) {
				return vm;
			}
			
			vm = new ViewMenu(jqElem);
			jqElem.data("rocketImplViewMenu", vm);
			
			return vm;
		}
	}
	
	export class ViewMenuItem {
		private _on: boolean = true;
		private changedCallbacks: Array<() => any> = [];
		private jqA: JQuery;
		private jqI: JQuery;
		
		constructor (public localeId: string, public label: string, public prettyLocaleId: string) {
		}
		
		draw(jqElem: JQuery) {
			this.jqI = $("<i></i>");
			
			this.jqA = $("<a />", { "href": "", "text": this.label + " ", "class": "btn" })
					.append(this.jqI)
					.appendTo(<JQuery<HTMLElement>> jqElem)
					.on ("click",(evt: any) => {
						if (this.disabled) return;
						
						this.on = !this.on;
						
						evt.preventDefault();
						return false;
					});
			
			this.checkI();
		}
		
		get disabled(): boolean {
			return this.jqA.hasClass("disabled");
		}
		
		set disabled(disabled: boolean) {
			if (disabled) {
				this.jqA.addClass("disabled");
			} else {
				this.jqA.removeClass("disabled");
			}
		}
		
		get on(): boolean {
			return this._on;
		}
		
		set on(on: boolean) {
			if (this._on == on) return;
			
			this._on = on;
			this.checkI();
			
			this.triggerChanged();
		}
		
		private triggerChanged() {
			for (let callback of this.changedCallbacks) {
				callback();
			}
		}

		whenChanged(callback: () => any) {
			this.changedCallbacks.push(callback);
		}
		
		private checkI() {
			if (this.on) {
				this.jqA.addClass("rocket-active");
				this.jqI.attr("class", "fa fa-toggle-on");
			} else {
				this.jqA.removeClass("rocket-active");
				this.jqI.attr("class", "fa fa-toggle-off");
			}
		}
	}
}