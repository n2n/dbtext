namespace Rocket.Impl.Translation {

	export class Translator {
		constructor(private container: Rocket.Cmd.Container, private userStore: UserStore) {}

		scan() {
			for (let context of this.container.getAllZones()) {
				let elems = context.jQuery.find(".rocket-impl-translation-manager").toArray();
				let elem;

				while (elem = elems.pop()) {
					this.initTm($(elem), context);
				}

				let jqViewControl = context.menu.toolbar.getJqControls().find(".rocket-impl-translation-view-control");

				let jqTranslatables = context.jQuery.find(".rocket-impl-translatable");
				if (jqTranslatables.length == 0) {
					jqViewControl.hide();
					continue;
				}

				jqViewControl.show();

				let isInitViewMenu = false;
				if (jqViewControl.length == 0) {
					jqViewControl = $("<div />", { "class": "rocket-impl-translation-view-control" });
					context.menu.toolbar.getJqControls().show().append(jqViewControl);
					isInitViewMenu = true;
				}

				let viewMenu = ViewMenu.from(jqViewControl);

				jqTranslatables.each((i, elem) => {
					viewMenu.registerTranslatable(Translatable.from($(elem)));
				});

				if (isInitViewMenu) {
					this.initViewMenu(viewMenu);
				}

				viewMenu.checkLoadJobs();
			}
		}

		private initTm(jqElem: JQuery<Element>, context: Rocket.Cmd.Zone) {
			let tm = TranslationManager.from(jqElem);
			tm.val(this.userStore.langState.activeLocaleIds);
			let se = Rocket.Display.StructureElement.of(jqElem);
			
			let jqBase = null;
			if (!se) {
				jqBase = context.jQuery;
			} else {
				jqBase = se.jQuery;
			}
			
			jqBase.find(".rocket-impl-translatable-" + jqElem.data("rocket-impl-mark-class-key")).each((i, elem) => {
				let elemJq = $(elem);
				if (Translatable.test(elemJq)) {
					return;
				}
				tm.registerTranslatable(Translatable.from(elemJq));
			});
		}
		
		private ensureSomethingOn(viewMenuItems: { [localeId: string]: ViewMenuItem }) {
		    for (let localeId in viewMenuItems) {
		        if (viewMenuItems[localeId].on) {
		            return;
		        }
		    }
		    
		    for (let localeId in viewMenuItems) {
                viewMenuItems[localeId].on = true
		    }
		}

		private initViewMenu(viewMenu: ViewMenu) {
			let langState = this.userStore.langState;
			let viewMenuItems = viewMenu.items;
			let listeners: StateListener[] = [];

			if (this.userStore.langState.activeLocaleIds.length > 0) {
				for (let localeId in viewMenuItems) {
					viewMenuItems[localeId].on = this.userStore.langState.languageActive(localeId);
				}
			}
			
			this.ensureSomethingOn(viewMenuItems);
			
			for (let localeId in viewMenuItems) {
				let viewMenuItem = viewMenuItems[localeId];

				this.userStore.langState.toggleActiveLocaleId(localeId, viewMenuItem.on);

				viewMenuItem.whenChanged(() => {
					this.userStore.langState.toggleActiveLocaleId(localeId, viewMenuItem.on);
					this.userStore.save();
				});

				listeners.push({
					changed(state: boolean) {
						if (langState.languageActive(localeId) === viewMenuItem.on) return;

						viewMenuItem.on = state;
					}
				});

				this.userStore.langState.onChanged(listeners[listeners.length - 1]);
			}

			let observer: MutationObserver = new MutationObserver((mutations) => {
				if (!viewMenu.jQuery.is(":visible")) {
					listeners.forEach((listener: StateListener) => {
						this.userStore.langState.offChanged(listener);
					});
					observer.disconnect();
					return;
				}
			});

			observer.observe($(".rocket-main-layer").get(0), {childList: true, attributes: true, characterData: true, subtree: true});
		}
	}
}