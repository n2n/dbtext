namespace Rocket.Impl {
	export class LangState {
		private _activeLocaleIds: string[];
		private listeners: StateListener[] = [];

		constructor(activeLanguageIds: string[]) {
			this._activeLocaleIds = activeLanguageIds;
		}

		public languageActive(localeId: string): boolean {
			return !!this.activeLocaleIds.find((id) => id === localeId);
		}

		public toggleActiveLocaleId(localeId: string, state: boolean): void {
			if (!!state && !this.languageActive(localeId)) {
				this.activeLocaleIds.push(localeId);
			}

			if (!state && !!this.languageActive(localeId)) {
				this.activeLocaleIds.splice(this.activeLocaleIds.findIndex((id) => id === localeId), 1);
			}

			this.change(state);
		}

		public onChanged(listener: StateListener) {
			this.listeners.push(listener);
		}

		public offChanged(listener: StateListener) {
			this.listeners.splice(this.listeners.indexOf(listener), 1);
		}

		public change(state: boolean) {
			this.listeners.forEach((listener: StateListener) => {
				listener.changed(state);
			})
		}

		get activeLocaleIds(): string[] {
			return this._activeLocaleIds;
		}
	}
}