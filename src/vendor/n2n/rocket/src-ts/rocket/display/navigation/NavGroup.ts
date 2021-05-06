namespace Rocket.Display {
	import UserState = Rocket.Impl.NavState;
	import UserStore = Rocket.Impl.UserStore;
	import StateListener = Rocket.Impl.StateListener;

	export class NavGroup implements StateListener {
		private _id: string;
		private _elemJq: JQuery<Element>;
		private _userState: UserState;
		private _opened: boolean;

		public constructor(id: string, elemJq: JQuery<Element>, userState: UserState) {
			this.id = id;
			this.elemJq = elemJq;
			this.userState = userState;

			this.opened = userState.isGroupOpen(id);

			if (this.opened) {
				this.open(0);
			} else {
				this.close(0);
			}
		}

		public static build(elemJq: JQuery<Element>, userStore: UserStore) {
			let id = elemJq.data("navGroupId");
			let navGroup = new NavGroup(id, elemJq, userStore.navState);
			userStore.navState.onChanged(elemJq, navGroup);
			elemJq.find("h3").on("click", () => {
				navGroup.toggle();
				userStore.save();
			});

			return navGroup;
		}

		public toggle() {
			if (this.opened) {
				this.close(150);
			} else {
				this.open(150);
			}
		}

		public changed() {
			if (this.userState.isGroupOpen(this.id) === this.opened) return;
			this.opened = this.userState.isGroupOpen(this.id);

			if (this.opened === true) {
				this.open();
			}

			if (this.opened === false) {
				this.close();
			}
		}

		public open(ms: number = 150) {
			this.opened = true;
			let icon = this.elemJq.find("h3").find("i");

			icon.addClass("fa-minus");
			icon.removeClass("fa-plus");
			this.elemJq.find('.nav').stop(true, true).slideDown({duration: ms});
			this.userState.change(this.id, this.opened);
		}

		public close(ms: number = 150) {
			this.opened = false;
			let icon = this.elemJq.find("h3").find("i");

			icon.addClass("fa-plus");
			icon.removeClass("fa-minus");
			this.elemJq.find('.nav').stop(true, true).slideUp({duration: ms});

			this.userState.change(this.id, this.opened);
		}

		get userState(): Rocket.Impl.NavState {
			return this._userState;
		}

		set userState(value: Rocket.Impl.NavState) {
			this._userState = value;
		}

		get elemJq(): JQuery<Element> {
			return this._elemJq;
		}

		set elemJq(value: JQuery<Element>) {
			this._elemJq = value;
		}

		get id(): string {
			return this._id;
		}

		set id(value: string) {
			this._id = value;
		}

		get opened(): boolean {
			return this._opened;
		}

		set opened(value: boolean) {
			this._opened = value;
		}
	}
}