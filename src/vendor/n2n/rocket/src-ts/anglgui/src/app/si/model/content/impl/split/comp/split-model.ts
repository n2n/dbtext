import { UiStructure } from 'src/app/ui/structure/model/ui-structure';

export interface SplitModel {

	// getSplitOptions(): SplitOption[];

	// getSplitStyle(): SplitStyle;

	isKeyActive(key: string): boolean;

	activateKey(key: string): void;

	getChildUiStructureMap(): Map<string, UiStructure>;

	getLabelByKey(key: string): string;

	// getSiField$(key: string): Promise<SiField>;

	// getCopyTooltip(): string|null;
}
