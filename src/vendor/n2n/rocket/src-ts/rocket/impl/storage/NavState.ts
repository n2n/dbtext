namespace Rocket.Impl {
	export class NavState {
		private _scrollPos: number;
		private _navGroupOpenedIds: string[];
		private navStateListeners: StateListener[] = [];

		constructor(scrollPos: number, navGroupOpenedIds: string[] = []) {
			this._scrollPos = scrollPos;
			this._navGroupOpenedIds = navGroupOpenedIds;
		}

		public onChanged(elemJq: JQuery<Element>, listener: StateListener) {
			this.navStateListeners.push(listener);
			elemJq.on("remove", () => { this.offChanged(listener) });
		}

		public offChanged(navStateListener: StateListener) {
			this.navStateListeners.splice(this.navStateListeners.indexOf(navStateListener), 1);
		}

		public change(id: string, opened: boolean) {
			if (opened) {
				this.addOpenNavGroupId(id);
			} else {
				this.removeOpenNavGroupId(id);
			}

			this.navStateListeners.forEach((navStateListener: StateListener) => {
				navStateListener.changed(opened);
			})
		}

		public addOpenNavGroupId(id: string) {
			if (this._navGroupOpenedIds.indexOf(id) > -1) return;
			this._navGroupOpenedIds.push(id);
		}

		public removeOpenNavGroupId(id: string) {
			if (this._navGroupOpenedIds.indexOf(id) === -1) return;
			this._navGroupOpenedIds.splice(this._navGroupOpenedIds.indexOf(id), 1);
		}

		public isGroupOpen(navId: string): boolean {
			return !!this._navGroupOpenedIds.find((id: string) => { return id == navId });
		}

		get navGroupOpenedIds(): string[] {
			return this._navGroupOpenedIds;
		}

		get scrollPos(): number {
			return this._scrollPos;
		}

		set scrollPos(value: number) {
			this._scrollPos = value;
		}
	}

	export interface StateListener {
		changed(state: boolean): void;
	}
}