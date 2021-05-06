import { SiPanel } from '../model/si-panel';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';

export interface PanelDef {
	siPanel: SiPanel;
	uiStructure: UiStructure;
}

export interface EmbeddedEntryPanelsModel {

	getPanelDefs(): PanelDef[];
}
