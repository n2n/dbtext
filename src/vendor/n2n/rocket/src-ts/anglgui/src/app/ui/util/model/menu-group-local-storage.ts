import {UiMenuGroup} from '../../structure/model/ui-menu';

export class MenuGroupLocalStorage {
	public static UI_MENU_GROUP_OPEN_STATE_KEY = 'ui-menu-group-open-state';

	private static async readUiMenuGroupOpenStates(): Promise<OpenState[]> {
		const openStatesJsonString = localStorage.getItem(this.UI_MENU_GROUP_OPEN_STATE_KEY);
		if (!openStatesJsonString) {
			return [];
		}
		return JSON.parse(openStatesJsonString);
	}

	static async saveOpenState(menuGroup: UiMenuGroup, state: boolean): Promise<void> {
		const openStates = await this.readUiMenuGroupOpenStates();

		const item = openStates.find(mg => mg.id === menuGroup.id);
		if (!!item) {
			item.isOpen = state;
		} else {
			openStates.push({id : menuGroup.id, isOpen : state});
		}

		localStorage.setItem(this.UI_MENU_GROUP_OPEN_STATE_KEY, JSON.stringify(openStates));
	}

	static async toggleOpenStates(menuGroups: UiMenuGroup[]): Promise<void> {
		const openStates = await this.readUiMenuGroupOpenStates();

		openStates.forEach((openState) => {
			const menuGroup = menuGroups.find(mg => mg.id === openState.id);
			if (!!menuGroup) {
				menuGroup.isOpen = openState.isOpen;
			} else {
				this.removeOpenState(openState);
			}
		});
	}

	private static async removeOpenState(openState: OpenState): Promise<void> {
		const openStates = await this.readUiMenuGroupOpenStates();
		openStates.splice(openStates.indexOf(openStates.find(os => os.id === openState.id), 1));
		localStorage.setItem(this.UI_MENU_GROUP_OPEN_STATE_KEY, JSON.stringify(openStates));
	}
}

export interface OpenState {
	id; isOpen;
}
