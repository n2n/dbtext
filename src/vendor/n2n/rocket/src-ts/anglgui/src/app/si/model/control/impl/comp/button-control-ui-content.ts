import { ButtonControlModel } from './button-control-model';
import { ButtonControlComponent } from './button-control/button-control.component';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';

export class ButtonControlUiContent extends TypeUiContent<ButtonControlComponent> {
	constructor(model: ButtonControlModel) {
		super(ButtonControlComponent, (ref) => {
			ref.instance.model = model;
		});
	}
}
