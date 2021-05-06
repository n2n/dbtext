import { InSiFieldAdapter } from '../../common/model/in-si-field-adapter';
import { InputInFieldModel } from '../comp/input-in-field-model';
import { SiCrumbGroup } from '../../meta/model/si-crumb';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { InputInFieldComponent } from '../comp/input-in-field/input-in-field.component';
import { Message } from 'src/app/util/i18n/message';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { TextareaInFieldComponent } from '../comp/textarea-in-field/textarea-in-field.component';
import { TextAreaInFieldModel } from '../comp/textarea-in-field-model';
import { SiInputResetPoint } from '../../../si-input-reset-point';
import { CallbackInputResetPoint as CallbackSiInputResetPoint } from '../../common/model/callback-si-input-reset-point';


export class StringInSiField extends InSiFieldAdapter implements InputInFieldModel, TextAreaInFieldModel {
	public mandatory = false;
	public minlength: number|null = null;
	public maxlength: number|null = null;
	public prefixAddons: SiCrumbGroup[] = [];
	public suffixAddons: SiCrumbGroup[] = [];
	private tmpValue: string|null = null;

	constructor(public label: string, public value: string|null, public multiline: boolean = false) {
		super();
		this.validate();
	}

	getType(): string {
		return 'text';
	}

	getMin(): number|null {
		return null;
	}

	getMax(): number|null {
		return null;
	}

	getStep(): number|null {
		return null;
	}

	readInput(): object {
		return { value: this.value };
	}

	createInputResetPoint(): Promise<SiInputResetPoint> {
		return Promise.resolve(new CallbackSiInputResetPoint(this.value, (value) => { this.value = value; }));
	}

	getValue(): string|null {
		if (null !== this.tmpValue) {
			return this.tmpValue;
		}

		return this.value;
	}

	getMaxlength(): number|null {
		return this.maxlength;
	}

	setValue(value: string|null): void {
		if (null === value) {
			this.tmpValue = '';
			return;
		}

		this.tmpValue = value;
	}

	getPrefixAddons(): SiCrumbGroup[] {
		return this.prefixAddons;
	}

	getSuffixAddons(): SiCrumbGroup[] {
		return this.suffixAddons;
	}

	private validate(): void {
		this.messagesCollection.clear();

		if (this.mandatory && this.value === null) {
			this.messagesCollection.push(Message.createCode('mandatory_err', new Map([['{field}', this.label]])));
		}

		if (this.minlength && this.value && this.value.length < this.minlength) {
			this.messagesCollection.push(Message.createCode('minlength_err',
					new Map([['{field}', this.label], ['{minlength}', this.minlength.toString()]])));
		}

		if (this.maxlength && this.value && this.value.length > this.maxlength) {
			this.messagesCollection.push(Message.createCode('maxlength_err',
					new Map([['{field}', this.label], ['{maxlength}', this.maxlength.toString()]])));
		}
	}

	copyValue(): Promise<SiGenericValue> {
		return Promise.resolve(new SiGenericValue(this.value));
	}

	pasteValue(genericValue: SiGenericValue): Promise<boolean> {
		if (!genericValue.isNull() && !genericValue.isStringRepresentable()) {
			return Promise.resolve(false);
		}

		this.value = genericValue.readStringOrNull();
		return Promise.resolve(true);
	}

	createUiContent(): UiContent {
		return new TypeUiContent(this.multiline ? TextareaInFieldComponent : InputInFieldComponent, (ref) => {
			ref.instance.model = this;
		});
	}

	onBlur(): void {
		if (null !== this.tmpValue) {
			if (this.tmpValue.length === 0) {
				this.value = null;
			} else {
				this.value = this.tmpValue;
			}
		}
		this.tmpValue = null;
		this.validate();
	}

	onFocus(): void {
		this.messagesCollection.clear();
	}

// 	initComponent(viewContainerRef: ViewContainerRef,
// 			componentFactoryResolver: ComponentFactoryResolver,
// 			commanderService: SiUiService): ComponentRef<any> {
// 		const componentFactory = componentFactoryResolver.resolveComponentFactory(InputInFieldComponent);
//
// 		const componentRef = viewContainerRef.createComponent(componentFactory);
//
// 		const component = componentRef.instance;
// 		component.model = this;
//
// 		return componentRef;
// 	}
}
