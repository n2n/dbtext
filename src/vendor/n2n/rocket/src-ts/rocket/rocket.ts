namespace Rocket {
	import NavGroup = Rocket.Display.NavGroup;

	let container: Rocket.Cmd.Container;
	let blocker: Rocket.Cmd.Blocker;
	let initializer: Rocket.Display.Initializer;
	let $ = jQuery;

	jQuery(document).ready(function ($) {
		let jqContainer = $("#rocket-content-container");

		container = new Rocket.Cmd.Container(jqContainer);
		let userStore = Rocket.Impl.UserStore
				.read(jqContainer.find("#rocket-global-nav").find("h2").data("rocketUserId"));

		blocker = new Rocket.Cmd.Blocker(container);
		blocker.init($("body"));

		initializer = new Rocket.Display.Initializer(container, jqContainer.data("error-tab-title"),
				jqContainer.data("display-error-label"));
		
		let clipboard = new Impl.Relation.Clipboard();
		
		Jhtml.ready(() => {
			initializer.scan();
		});

		(function () {
			Jhtml.ready(() => {
				$(".rocket-impl-overview").each(function () {
					Rocket.Impl.Overview.OverviewPage.from($(this));
				});
			});

			Jhtml.ready(() => {
				$(".rocket-impl-overview").each(function () {
					Rocket.Impl.Overview.OverviewPage.from($(this));
				});
			});
		})();

		(function () {
			$("form.rocket-form").each(function () {
				Rocket.Impl.Form.from($(this));
			});

			Jhtml.ready(() => {
				$("form.rocket-form").each(function () {
					Rocket.Impl.Form.from($(this));
				});
			});
		}) ();

		(function () {
			$(".rocket-impl-to-many").each(function () {
				Rocket.Impl.Relation.ToMany.from($(this), clipboard);
			});

			Jhtml.ready(() => {
				$(".rocket-impl-to-many").each(function () {
					Rocket.Impl.Relation.ToMany.from($(this), clipboard);
				});
			});
		}) ();

		(function () {
			$(".rocket-impl-to-one").each(function () {
				let toOne = Rocket.Impl.Relation.ToOne.from($(this), clipboard);
			});

			Jhtml.ready(() => {
				$(".rocket-impl-to-one").each(function () {
					let toOne = Rocket.Impl.Relation.ToOne.from($(this), clipboard);
				});
			});
		})();

		(function () {
			let t = new Rocket.Impl.Translation.Translator(container, userStore);

			Jhtml.ready(() => {
				t.scan();
			});
		})();

		(function () {
			Jhtml.ready((elements: HTMLElement[]) => {
				$(elements).find("a.rocket-jhtml").each(function () {
					new Rocket.Display.Command(Jhtml.Ui.Link.from(<HTMLAnchorElement> this)).observe();
				});
			});
		})();
		
		(function() {
			Jhtml.ready((elements: HTMLElement[]) => {
				$(elements).find(".rocket-image-resizer-container").each(function () {
					new Rocket.Impl.File.RocketResizer($(this));
				});
			});
		})();

		(function () {
			let moveState = new Impl.Order.MoveState();

			Jhtml.ready((elements: HTMLElement[]) => {
				$(elements).find(".rocket-impl-insert-before").each(function () {
					let elemJq = $(this);
					new Impl.Order.Control(elemJq, Impl.Order.InsertMode.BEFORE, moveState,
							elemJq.siblings(".rocket-static"));
				});
				$(elements).find(".rocket-impl-insert-after").each(function () {
					let elemJq = $(this);
					new Impl.Order.Control(elemJq, Impl.Order.InsertMode.AFTER, moveState,
							elemJq.siblings(".rocket-static"));
				});
				$(elements).find(".rocket-impl-insert-as-child").each(function () {
					let elemJq = $(this);
					new Impl.Order.Control(elemJq, Impl.Order.InsertMode.CHILD, moveState,
							elemJq.siblings(".rocket-static"));
				});
			});
		})();

		(function() {
			var nav: Rocket.Display.Nav = new Rocket.Display.Nav();
			var navGroups: Rocket.Display.NavGroup[] = [];

			Jhtml.ready((elements) => {
				let elementsJq = $(elements);
				let rgn = elementsJq.find("#rocket-global-nav");
				if (rgn.length > 0) {
					nav.elemJq = rgn;
					let navGroupJq = rgn.find(".rocket-nav-group");

					navGroupJq.each((key: number, navGroupNode: HTMLElement) => {
						navGroups.push(Rocket.Display.NavGroup.build($(navGroupNode), userStore));
					})

					rgn.on("scroll",() => {
						userStore.navState.scrollPos = rgn.scrollTop();
						userStore.save();
					});

					var observer = new MutationObserver((mutations) => {
						nav.scrollToPos(userStore.navState.scrollPos)

						mutations.forEach((mutation) => {
							navGroups.forEach((navGroup: NavGroup) => {
								if ($(Array.from(mutation.removedNodes)[0]).get(0) === navGroup.elemJq.get(0)) {
									userStore.navState.offChanged(navGroup);
								}
							});

							navGroups.forEach((navGroup: NavGroup) => {
								if ($(Array.from(mutation.addedNodes)[0]).get(0) === navGroup.elemJq.get(0)) {
									if (userStore.navState.isGroupOpen(navGroup.id)) {
										navGroup.open(0);
									} else {
										navGroup.close(0);
									}

									nav.scrollToPos(userStore.navState.scrollPos);
								}
							});
						})
					})

					observer.observe(rgn.get(0), {childList: true});
				}

				elementsJq.each((key: number, node: HTMLElement) => {
					let nodeJq = $(node);
					if (nodeJq.hasClass("rocket-nav-group") && nodeJq.parent().get(0) === nav.elemJq.get(0)) {
						navGroups.push(Rocket.Display.NavGroup.build(nodeJq, userStore));
					}
				});

				nav.scrollToPos(userStore.navState.scrollPos);
			});
		})();

		(function() {
			Jhtml.ready((elements) => {
				var elementsJq = $(elements);
				elementsJq.find(".dropdown").each((i: number, elem: HTMLElement) => {
					var elemJq = $(elem);
					Display.Toggler.simple(elemJq.find(".dropdown-toggle"), elemJq.find(".dropdown-menu"));
				});
			});
		})();
		
		(function () {
			let url = $("[data-jhtml-container][data-rocket-url]").data("rocket-url");
			if (!url) return;
			
			setInterval(() => {
				$.get(url);
			}, 300000);
		})();
        
        (function() {
            Jhtml.ready((elements) => {
                var elementsJq = $(elements);
                elementsJq.find(".rocket-privilege-form").each(function () {
                    (new Core.PrivilegeForm($(this))).setup();
                });
                
                elementsJq.find(".rocket-command-privileges").each(function () {
                    (new Core.CommandPrivilegeList($(this))).listen();
                })
                
            });
        })();
        
        (function() {
            Jhtml.ready((elements) => {
                var elementsJq = $(elements);
                elementsJq.find(".rocket-mail").each(function () {
                    let mle = new Core.MailLogEntry($(this));
                    mle.listen();
                });
                
                elementsJq.find(".rocket-mail-paging").each(function () {
                    (new Core.MailPaging($(this))).listen()
                });
                
            });
        })();
	});

	export function scan(context: Rocket.Cmd.Zone = null) {
		initializer.scan();
	}

	export function getContainer(): Rocket.Cmd.Container {
		return container;
	}

	export function layerOf(elem: HTMLElement): Rocket.Cmd.Layer {
		return Rocket.Cmd.Layer.of($(elem));
	}

	export function contextOf(elem: HTMLElement): Rocket.Cmd.Zone {
		return Rocket.Cmd.Zone.of($(elem));
	}

//	export function exec(url: string, config: Rocket.Cmd.ExecConfig = null) {
//		executor.exec(url, config);
//	}
}
