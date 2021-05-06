import { SiControl } from 'src/app/si/model/control/si-control';
import { SiButton } from 'src/app/si/model/control/impl/model/si-button';
import { ButtonControlUiContent } from '../comp/button-control-ui-content';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';
import { UiContent } from 'src/app/ui/structure/model/ui-content';

export class SimpleSiControl implements SiControl {
	public disabled = false;

	constructor(public siButton: SiButton, public callback: () => any) {
	}

	isDisabled(): boolean {
		return this.disabled;
	}

	createUiContent(getUiZone: () => UiZone): UiContent {
		return new ButtonControlUiContent({
			getUiZone,
			getSiButton: () => this.siButton,
			isLoading: () => false,
			isDisabled: () => this.disabled,
			exec: this.callback
		});
	}

	getSubTooltip(): string|null {
		return null;
	}
}
