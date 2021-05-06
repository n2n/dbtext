import { UiStructure } from 'src/app/ui/structure/model/ui-structure';

export interface CompactEntryModel {

	isLoading(): boolean;

	getFieldUiStructures(): UiStructure[];
}
