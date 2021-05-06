import { Component, OnInit, Input, HostBinding } from '@angular/core';
import { ButtonControlModel } from '../button-control-model';
import { SiButton } from '../../model/si-button';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';
import { filter } from 'rxjs/operators';
import { UiContent } from 'src/app/ui/structure/model/ui-content';

@Component({
	selector: 'rocket-button-control',
	templateUrl: './button-control.component.html',
	styleUrls: ['./button-control.component.css'],
	host: {class: 'rocket-button-control'}
})
export class ButtonControlComponent implements OnInit {

	@Input()
	model: ButtonControlModel;

	private _subVisible = false;

	constructor() {
	}

	ngOnInit() {
	}

	@HostBinding('class.rocket-fixed-width')
	get fixedWidth(): boolean {
		return !!this.model.getSubTooltip && !!this.model.getSubTooltip();
	}

	get siButton(): SiButton {
		return this.model.getSiButton();
	}

	get loading(): boolean {
		return this.model.isLoading();
	}

	get disabled() {
		return this.model.isDisabled() || this.loading;
	}

	hasSubUiContents(): boolean {
		return !!this.model.getSubUiContents && this.model.getSubUiContents().length > 0;
	}

	hasSubSiButtons() {
		return !!this.model.getSubSiButtonMap && this.model.getSubSiButtonMap().size > 0;
	}

	get subUiContents(): UiContent[] {
		return this.model.getSubUiContents ? this.model.getSubUiContents() : [];
	}

	get subSiButtonMap() {
		return this.model.getSubSiButtonMap ? this.model.getSubSiButtonMap() : [];
	}

	get subVisible(): boolean {
		return this._subVisible && !this.disabled && (this.hasSubSiButtons() || this.hasSubUiContents());
	}

	exec() {
		if (this.hasSubSiButtons() || this.hasSubUiContents()) {
			this._subVisible = !this._subVisible;
			return;
		}

		const siConfirm = this.model.getSiButton().confirm;

		if (!siConfirm) {
			this.model.exec(null);
			return;
		}

		const cd = this.model.getUiZone().createConfirmDialog(siConfirm.message, siConfirm.okLabel, siConfirm.cancelLabel);
		cd.danger = siConfirm.danger;
		cd.confirmed$.pipe(filter(confirmed => confirmed)).subscribe(() => {
					this.model.exec(null);
				});
	}

	subExec(key: string) {
		this._subVisible = false;

		const siConfirm = this.model.getSubSiButtonMap().get(key).confirm;

		if (!siConfirm) {
			this.model.exec(key);
			return;
		}

		const cd = this.model.getUiZone().createConfirmDialog(siConfirm.message, siConfirm.okLabel, siConfirm.cancelLabel);
		cd.danger = siConfirm.danger;
		cd.confirmed$.pipe(filter(confirmed => confirmed)).subscribe(() => {
					this.model.exec(key);
				});
	}


}
