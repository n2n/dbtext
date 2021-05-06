import { MessageFieldModel } from '../../common/comp/message-field-model';
import { UiNavPoint } from 'src/app/ui/util/model/ui-nav-point';

export interface LinkOutModel extends MessageFieldModel {

	getUiNavPoint(): UiNavPoint;

	getLabel(): string;
	
	isLytebox(): boolean;
	
	isBulky(): boolean;
}
