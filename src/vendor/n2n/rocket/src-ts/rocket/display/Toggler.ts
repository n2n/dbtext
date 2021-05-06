namespace Rocket.Display {

	export class Toggler {
		private closeCallback: (e?: any) => any;

		constructor(private buttonJq: JQuery<Element>, private menuJq: JQuery<Element>, private mouseLeaveMs: number) {
			menuJq.hide();
		}

		toggle(e?: any) {
			if (this.closeCallback) {
				this.closeCallback(e);
				return;
			}

			this.open();
		}

//		set disabled(disabled: boolean) {
//			if (disabled) {
//				this.buttonJq.addClass("disabled");
//			} else {
//				this.buttonJq.removeClass("disabled");
//			}
//		}
//
//		get disabled(): boolean {
//			return this.buttonJq.hasClass("disabled");
//		}

		close() {
			if (!this.closeCallback) return;

			this.closeCallback();
		}

		open() {
			if (this.closeCallback) return;

			this.menuJq.show();
			this.buttonJq.addClass("active");
			let bodyJq = $("body");

			let events: TogglerEvent[] = [];

			this.closeCallback = (e?: any) => {
				if (e && e.type == "click" && this.menuJq.has(e.target).length > 0) {
					return;
				}

				for (let event of events) {
					event.off();
				}

				this.closeCallback = null;

				this.menuJq.hide();
				this.buttonJq.removeClass("active");
			};

			events.push(new TogglerEvent(bodyJq, "click", this.closeCallback));
			if (this.mouseLeaveMs !== null) {
				let delayTimer = new Timer(this.closeCallback, this.mouseLeaveMs);
				delayTimer.start();

				events.push(new TogglerEvent(bodyJq, "click", () => {
					delayTimer.reset();
				}));

				events.push(new TogglerEvent(this.menuJq, "mouseleave", () => {
					delayTimer.start()
				}));
				events.push(new TogglerEvent(this.menuJq, "mouseenter", () => {
					delayTimer.reset()
				}));
			}

			for (let event of events) {
				event.on();
			}
		}

		/**
		 * @param {JQuery} buttonJq
		 * @param {JQuery} menuJq
		 * @param {number} mouseLeaveMs null for no close on mouseleave
		 * @returns {Rocket.Display.Toggler}
		 */
		static simple(buttonJq: JQuery<Element>, menuJq: JQuery<Element>, mouseLeaveMs: number = 3000): Toggler {
			let toggler = new Toggler(buttonJq, menuJq, mouseLeaveMs);

			buttonJq.on("click", (e: any) => {
				e.stopImmediatePropagation();
				toggler.toggle(e);
			});

			return toggler;
		}
	}

	export class Timer {
		private timerId: number = null;

		public constructor(private callback: (e?: any) => any, private delay: number) {
		}

		public start() {
			if (this.started) {
				this.reset();
			}

			this.timerId = window.setTimeout(this.callback, this.delay);
		}

		get started(): boolean {
			return this.timerId != null;
		}


		public reset() {
			if (!this.started) return;

			window.clearTimeout(this.timerId);
			this.timerId = null;
		}
	}

	export class TogglerEvent {

		constructor(private elemJq: JQuery<Element>, private eventName: string, private callback: (e?: any) => any) {
		}

		public on() {
			this.elemJq.on(this.eventName, this.callback);
		}

		public off() {
			this.elemJq.off(this.eventName, this.callback);
		}
	}
}