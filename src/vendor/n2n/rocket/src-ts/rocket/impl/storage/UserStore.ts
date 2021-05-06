namespace Rocket.Impl {
	export class UserStore {
		private static readonly STORAGE_ITEM_NAME = "rocket_user_state";

		private _userId: number;
		private _userStoreItems: UserStoreItem[] = [];

		private _navState: NavState;
		private _langState: LangState;

		constructor(userId: number, navState: NavState, langState: LangState, userStoreItems: UserStoreItem[]) {
			this._userId = userId;
			this._userStoreItems = userStoreItems;
			this._langState = langState;
			this._navState = navState;
		}

		public static read(userId: number): UserStore {
			let userStoreUserItems: UserStoreItem[];
			try {
				userStoreUserItems = JSON.parse(window.localStorage.getItem(UserStore.STORAGE_ITEM_NAME)) || [];
			} catch (e) {
				userStoreUserItems = [];
			}

			if (!(userStoreUserItems instanceof Array)) {
				userStoreUserItems = [];
			}

			let userStoreItem = userStoreUserItems.find((userStoreUserItem: UserStoreItem) => {
				return (userStoreUserItem.userId === userId);
			});

			if (!userStoreItem) {
				return new UserStore(userId, new NavState(0, []), new LangState([]), userStoreUserItems);
			}

			return new UserStore(userId, new NavState(userStoreItem.scrollPos, userStoreItem.navGroupOpenedIds),
					new LangState(userStoreItem.activeLanguageLocaleIds), userStoreUserItems);
		}

		public save(): void {
			var userItem = this._userStoreItems.find((userItem: UserStoreItem) => {
				if (userItem.userId === this.userId) {
					return true;
				}
			});

			if (!userItem) {
				userItem = { userId: this.userId,
					scrollPos: this.navState.scrollPos,
					navGroupOpenedIds: this.navState.navGroupOpenedIds,
					activeLanguageLocaleIds: this.langState.activeLocaleIds };

				this._userStoreItems.push(userItem);
			}

			userItem.scrollPos = this.navState.scrollPos;
			userItem.navGroupOpenedIds = this.navState.navGroupOpenedIds;
			userItem.activeLanguageLocaleIds = this.langState.activeLocaleIds;

			window.localStorage.setItem(UserStore.STORAGE_ITEM_NAME, JSON.stringify(this._userStoreItems));
		}

		get userId() {
			return this._userId;
		}

		get langState(): Rocket.Impl.LangState {
			return this._langState;
		}

		get navState(): Rocket.Impl.NavState {
			return this._navState;
		}
	}

	export interface UserStoreItem {
		userId: number,
		scrollPos: number,
		navGroupOpenedIds: string[],
		activeLanguageLocaleIds: string[]
	}
}