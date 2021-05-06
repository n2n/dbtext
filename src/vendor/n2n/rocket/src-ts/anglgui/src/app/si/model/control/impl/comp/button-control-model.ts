import { SiButton } from '../model/si-button';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';
import { UiContent } from 'src/app/ui/structure/model/ui-content';

export interface ButtonControlModel {

	getSubUiContents?: () => UiContent[];

	getSubSiButtonMap?: () => Map<string, SiButton>;

	getSubTooltip?: () => string|null;

	getSiButton(): SiButton;

	isLoading(): boolean;

	isDisabled(): boolean;

	getUiZone(): UiZone;

	exec(subKey: string|null): void;
}
