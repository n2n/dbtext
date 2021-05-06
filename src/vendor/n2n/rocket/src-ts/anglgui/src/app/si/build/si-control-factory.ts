import { Extractor, ObjectMissmatchError } from 'src/app/util/mapping/extractor';
import { SiControl } from '../model/control/si-control';
import { ApiCallSiControl } from '../model/control/impl/model/api-call-si-control';
import { RefSiControl } from '../model/control/impl/model/ref-si-control';
import { SiButton, SiConfirm } from '../model/control/impl/model/si-button';
import { Injector } from '@angular/core';
import { SiControlBoundry } from '../model/control/si-control-bountry';
import { GroupSiControl } from '../model/control/impl/model/group-si-control';
import { SimpleSiControl } from '../model/control/impl/model/simple-si-control';
import { SiNavPoint } from '../model/control/si-nav-point';
import { SiUiService } from '../manage/si-ui.service';

enum SiControlType {
	REF = 'ref',
	API_CALL = 'api-call',
	GROUP = 'group',
	DEACTIVATED = 'deactivated'
}

export class SiControlFactory {

	constructor(private controlBoundry: SiControlBoundry, private injector: Injector) {
	}

	createControls(dataArr: any[]): SiControl[] {
		const controls = new Array<SiControl>();

		for (const controlData of dataArr) {
			controls.push(this.createControl(controlData));
		}
		return controls;
	}

	createControl(data: any): SiControl {
		const extr = new Extractor(data);
		const dataExtr = extr.reqExtractor('data');

		switch (extr.reqString('type')) {
			case SiControlType.REF:
				return new RefSiControl(
						this.injector.get(SiUiService),
						dataExtr.reqString('url'),
						dataExtr.reqBoolean('newWindow'),
						this.createButton(dataExtr.reqObject('button')),
						this.controlBoundry);
			case SiControlType.API_CALL:
				const apiControl = new ApiCallSiControl(
						this.injector.get(SiUiService),
						dataExtr.reqString('apiUrl'),
						dataExtr.reqObject('apiCallId'),
						this.createButton(dataExtr.reqObject('button')),
						this.controlBoundry);
				apiControl.inputSent = dataExtr.reqBoolean('inputHandled');
				return apiControl;
			case SiControlType.GROUP:
				const groupControl = new GroupSiControl(
						this.createButton(dataExtr.reqObject('button')),
						dataExtr.reqArray('controls').map(controlData => this.createControl(controlData)));
				return groupControl;
			case SiControlType.DEACTIVATED:
				const deactivatedControl = new SimpleSiControl(this.createButton(dataExtr.reqObject('button')), () => {});
				deactivatedControl.disabled = true;
				return deactivatedControl;
			default:
				throw new ObjectMissmatchError('Invalid si control type: ' + data.type);
		}
	}

	private createButton(data: any): SiButton {
		const extr = new Extractor(data);
		const btn = new SiButton(extr.reqString('name'), extr.reqString('btnClass'), extr.reqString('iconClass'));

		btn.tooltip = extr.nullaString('tooltip');
		btn.important = extr.reqBoolean('important');
		btn.iconImportant = extr.reqBoolean('iconImportant');
		btn.iconAlways = extr.reqBoolean('iconAlways');
		btn.labelAlways = extr.reqBoolean('labelAlways');
		btn.href = extr.nullaString('href');

		const confirmData = extr.nullaObject('confirm');
		if (confirmData) {
			btn.confirm = this.createConfirm(confirmData);
		}
		return btn;
	}

	private createConfirm(data: any): SiConfirm {
		const extr = new Extractor(data);

		return {
			message: extr.nullaString('message'),
			okLabel: extr.nullaString('okLabel'),
			cancelLabel: extr.nullaString('cancelLabel'),
			danger: extr.reqBoolean('danger')
		};
	}
}
