
import { SiControl } from 'src/app/si/model/control/si-control';
import { SiButton } from 'src/app/si/model/control/impl/model/si-button';
import { SiUiService as SiUiService } from 'src/app/si/manage/si-ui.service';
import { ButtonControlUiContent } from '../comp/button-control-ui-content';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';
import { SiControlBoundry } from '../../si-control-bountry';

export class ApiCallSiControl implements SiControl {

	inputSent = false;
	private loading = false;
	// private entryBoundFlag: boolean;

	constructor(public siUiService: SiUiService, public apiUrl: string, public apiCallId: object,
			public button: SiButton, public controlBoundry: SiControlBoundry) {
	}

	getSiButton(): SiButton {
		return this.button;
	}

	isLoading(): boolean {
		return this.loading;
	}

	isDisabled(): boolean {
		return !!this.controlBoundry.getBoundEntries().find(siEntry => siEntry.isClaimed());
	}

	// set entryBound(entryBound: boolean) {
	// 	if (this.entry && !entryBound) {
	// 		throw new IllegalSiStateError('Control must be bound to entry.');
	// 	}

	// 	this.entryBoundFlag = entryBound;
	// }

	// get entryBound(): boolean {
	// 	return !!this.entry || this.entryBoundFlag;
	// }

	exec(uiZone: UiZone): void {
		const locks = this.controlBoundry.getBoundEntries().map(entry => entry.createLock());

		const obs = this.siUiService.execControl(this.apiUrl, this.apiCallId, this.controlBoundry, this.inputSent,
				uiZone.layer);
		this.loading = true;
		obs.subscribe(() => {
			locks.forEach((lock) => { lock.release(); });

			this.loading = false;
		});
	}

	createUiContent(getUiZone: () => UiZone): UiContent {
		return new ButtonControlUiContent({
			exec: (/*subKey: string|null*/) => this.exec(getUiZone()),
			getUiZone,
			isDisabled: () => this.isDisabled(),
			isLoading: () => this.isLoading(),
			getSiButton: () => this.getSiButton()
		});
	}
}
