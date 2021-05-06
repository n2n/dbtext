import { InSiFieldAdapter } from '../../common/model/in-si-field-adapter';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { Message } from 'src/app/util/i18n/message';
import { GenericMissmatchError } from 'src/app/si/model/generic/generic-missmatch-error';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { StringArrayInModel } from '../comp/string-array-in-model';
import { StringArrayInComponent } from '../comp/string-array-in/string-array-in.component';
import { SiInputResetPoint } from '../../../si-input-reset-point';
import { CallbackInputResetPoint } from '../../common/model/callback-si-input-reset-point';


export class StringArrayInSiField extends InSiFieldAdapter implements StringArrayInModel {
	public min = 0;
	public max: number|null = null;

	constructor(public label: string, public values = []) {
		super();
		this.validate();
	}


	getMin(): number {
		return this.min;
	}

	getMax(): number|null {
		return this.max;
	}

	readInput(): object {
		return { values: this.values };
	}

	getValues(): string[] {
		return this.values;
	}

	setValues(values: string[]): void {
		this.values = values;
		this.validate();
	}

	private validate(): void {
		this.messagesCollection.clear();

		if (this.min && this.values.length < this.min) {
			this.messagesCollection.push(Message.createCode('min_err', new Map([['{field}', this.label], ['{min}', this.min.toString()]])));
		}

		if (this.max && this.values.length > this.max) {
			this.messagesCollection.push(Message.createCode('max_err', new Map([['{field}', this.label], ['{max}', this.max.toString()]])));
		}
	}

	async copyValue(): Promise<SiGenericValue> {
		return new SiGenericValue(new Array(this.values));
	}

	async pasteValue(genericValue: SiGenericValue): Promise<boolean> {
		if (genericValue.isInstanceOf(StringArrayCopy)) {
			this.values = new Array(genericValue.readInstance(StringArrayCopy).values);
			return true;
		}

		return false;
	}

	async createInputResetPoint(): Promise<SiInputResetPoint> {
		return new CallbackInputResetPoint(new Array(this.values), (values) => { this.values = values; });
	}

	createUiContent(): UiContent {
		return new TypeUiContent(StringArrayInComponent, (ref) => {
			ref.instance.model = this;
		});
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


class StringArrayCopy {
	constructor(public values: string[]) {
	}
}