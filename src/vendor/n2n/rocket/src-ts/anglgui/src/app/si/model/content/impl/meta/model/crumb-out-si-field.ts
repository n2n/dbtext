import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { OutSiFieldAdapter } from '../../common/model/out-si-field-adapter';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { SiCrumbGroup } from './si-crumb';
import { CrumbOutFieldComponent } from '../comp/crumb-out-field/crumb-out-field.component';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { UiStructureType } from 'src/app/si/model/meta/si-structure-declaration';

export class CrumbOutSiField extends OutSiFieldAdapter {

	constructor(public crumbGroups: SiCrumbGroup[]) {
		super();
	}

	createUiContent(uiStructure: UiStructure): UiContent|null {
		return new TypeUiContent(CrumbOutFieldComponent, (ref) => {
			ref.instance.model = {
				isBulky: () => !!uiStructure.type && uiStructure.type !== UiStructureType.MINIMAL,
				getSiCrumbGroups: () => this.crumbGroups,
				getMessages: () => this.messagesCollection.get(),
			}
		});
	}

	getSiCrumbGroups(): SiCrumbGroup[] {
		return this.crumbGroups;
	}
}
