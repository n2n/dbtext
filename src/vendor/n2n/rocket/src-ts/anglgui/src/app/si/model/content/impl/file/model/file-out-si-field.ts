import { SiFile } from './file';
import { FileOutFieldComponent } from '../comp/file-out-field/file-out-field.component';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { OutSiFieldAdapter } from '../../common/model/out-si-field-adapter';
import { FileFieldModel } from '../comp/file-field-model';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';

export class FileOutSiField extends OutSiFieldAdapter implements FileFieldModel {

	constructor(public value: SiFile|null) {
		super();
	}

	getSiFile(): SiFile | null {
		return this.value;
	}

	createUiContent(): UiContent|null {
		return new TypeUiContent(FileOutFieldComponent, (ref) => {
 			ref.instance.model = this;
		});
	}

	async copyValue(): Promise<SiGenericValue> {
		return new SiGenericValue(this.value ? this.value.copy() : null);
	}
}
