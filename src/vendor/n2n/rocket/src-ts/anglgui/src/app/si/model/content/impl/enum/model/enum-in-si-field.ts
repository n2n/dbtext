import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { InSiFieldAdapter } from '../../common/model/in-si-field-adapter';
import { SelectInFieldModel } from '../comp/select-in-field-model';
import { SiField } from '../../../si-field';
import { SelectInFieldComponent } from '../comp/select-in-field/select-in-field.component';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { Message } from 'src/app/util/i18n/message';
import { SiInputResetPoint } from '../../../si-input-reset-point';
import { CallbackInputResetPoint } from '../../common/model/callback-si-input-reset-point';

export class EnumInSiField extends InSiFieldAdapter implements SelectInFieldModel {
	public mandatory = false;
	private asscoiatedFieldsMap = new Map<string, SiField[]>();
	public emptyLabel: string|null = null;

	constructor(public label: string, public value: string|null, public options: Map<string, string>) {
		super();
	}

	getValue(): string {
		return this.value;
	}

	setValue(value: string): void {
		this.value = value;
		this.updateAssociates();
		this.validate();
	}

	private validate(): void {
		this.messagesCollection.clear();

		if (this.mandatory && this.value === null) {
			this.messagesCollection.push(Message.createCode('mandatory_err', new Map([['{field}', this.label]])));
		}
	}

	getOptions(): Map<string, string> {
		return this.options;
	}

	getEmptyLabel(): string|null {
		return this.emptyLabel;
	}

	isMandatory(): boolean {
		return this.mandatory;
	}

	readInput(): object {
		return {
			value: this.value
		};
	}

	// copy(): SiField {
	// 	const copy = new EnumInSiField(this.label, this.value, this.options);
	// 	copy.mandatory = this.mandatory;
	// 	return copy;
	// }

	protected createUiContent(): UiContent {
		return new TypeUiContent(SelectInFieldComponent, (ref) => {
			ref.instance.model = this;
		});
	}

	async copyValue(): Promise<SiGenericValue> {
		return new SiGenericValue(this.value);
	}

	async pasteValue(genericValue: SiGenericValue): Promise<boolean> {
		if (genericValue.isNull()) {
			this.value = null;
			return true;
		}

		if (genericValue.isString()) {
			const value = genericValue.readString();
			if (this.options.has(value)) {
				this.value = value;
				return true;
			}
		}

		return false;
	}

	async createInputResetPoint(): Promise<SiInputResetPoint> {
		return new CallbackInputResetPoint(this.value, (value) => {
			this.value = value;
		});
	}

	setAssociatedFields(value: string, fields: SiField[]): void {
		this.asscoiatedFieldsMap.set(value, fields);
		fields.forEach(field => field.setDisabled(this.value !== value));
	}

	private updateAssociates(): void {
		for (const [aKey, aFields] of this.asscoiatedFieldsMap) {
			const disabled = aKey !== this.value;
			aFields.forEach(field => field.setDisabled(disabled));
		}
	}
}
