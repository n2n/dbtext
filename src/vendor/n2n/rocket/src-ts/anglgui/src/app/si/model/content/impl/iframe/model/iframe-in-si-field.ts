import { InSiFieldAdapter } from '../../common/model/in-si-field-adapter';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import {IframeInComponent} from '../comp/iframe-in/iframe-in.component';
import {IframeInModel} from '../comp/iframe-in-model';
import { SiInputResetPoint } from '../../../si-input-reset-point';
import { CallbackInputResetPoint } from '../../common/model/callback-si-input-reset-point';


export class IframeInSiField extends InSiFieldAdapter implements IframeInModel {

	constructor(public url: string|null, public srcDoc: string|null, private formData: Map<string, string>) {
		super();
	}

	createUiContent(): UiContent|null {
		return new TypeUiContent(IframeInComponent, (ref) => {
			ref.instance.model = this;
		});
	}

	private formDataToObject(): object {
		const params = {};
		for (const [key, value] of this.formData) {
			params[key] = value;
		}
		return { params };
	}

	readInput(): object {
		return this.formDataToObject();
	}

	async copyValue(): Promise<SiGenericValue> {
		return new SiGenericValue(new Map(this.formData));
	}

	async pasteValue(genericValue: SiGenericValue): Promise<boolean> {
		if (!genericValue.isInstanceOf(FormDataCopy)) {
			return false;
		}

		this.formData = new Map<string, string>(genericValue.readInstance(FormDataCopy).formData);
		return true;
	}

	getUrl(): string|null {
		return this.url;
	}

	getSrcDoc(): string|null {
		return this.srcDoc;
	}

	getFormData(): Map<string, string> {
		return this.formData;
	}

	setFormData(formData: Map<string, string>): void {
		this.formData = formData;
	}

	async createInputResetPoint(): Promise<SiInputResetPoint> {
		return new CallbackInputResetPoint(new Map(this.formData), (formData) => {
			this.formData = new Map(formData);
		});
	}
}

class FormDataCopy {
	constructor(public formData: Map<string, string>) {
	}
}
