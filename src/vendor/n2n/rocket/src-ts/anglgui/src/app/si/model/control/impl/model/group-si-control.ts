import { SiControl } from 'src/app/si/model/control/si-control';
import { SiButton } from 'src/app/si/model/control/impl/model/si-button';
import { ButtonControlUiContent } from '../comp/button-control-ui-content';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';
import { UiContent } from 'src/app/ui/structure/model/ui-content';

export class GroupSiControl implements SiControl {

	constructor(public siButton: SiButton, public subControls: SiControl[]) {
	}

	getSiButton(): SiButton {
		return this.siButton;
	}

	isLoading(): boolean {
		return false;
	}

	isDisabled(): boolean {
		return !!this.subControls.find(sc => sc.isDisabled());
	}

	createUiContent(getUiZone: () => UiZone): UiContent {
		const subUiContents = this.subControls.map(c => c.createUiContent(getUiZone));

		return new ButtonControlUiContent({
			getSiButton: () => this.siButton,
			isLoading: () => false,
			isDisabled: () => this.isDisabled(),
			exec: () => {},
			getSubUiContents: () => subUiContents,
			getUiZone
		});
	}

	getSubTooltip(): string|null {
		return null;
	}
}
