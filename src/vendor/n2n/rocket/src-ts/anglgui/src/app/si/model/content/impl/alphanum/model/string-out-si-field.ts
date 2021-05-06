import { OutSiFieldAdapter } from '../../common/model/out-si-field-adapter';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { StringOutFieldComponent } from '../comp/string-out-field/string-out-field.component';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { UiStructureType } from 'src/app/si/model/meta/si-structure-declaration';


export class StringOutSiField extends OutSiFieldAdapter {

	constructor(private value: string|null) {
		super();
	}

	createUiContent(uiStructure: UiStructure): UiContent|null {
		return new TypeUiContent(StringOutFieldComponent, (ref) => {
			ref.instance.model = {
				getMessages: () => this.messagesCollection.get(),
				getValue: () => this.value,
				isBulky: () => !!uiStructure.type && uiStructure.type !== UiStructureType.MINIMAL
			};
		});
	}

	getValue(): string | null {
		return this.value;
	}

	copyValue(): Promise<SiGenericValue> {
		return Promise.resolve(new SiGenericValue(this.value));
	}
}
