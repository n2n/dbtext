import { SplitOption } from '../model/split-option';

export interface SplitManagerModel {

	getSplitOptions(): SplitOption[];

	getIconClass(): string|null;

	getTooltip(): string|null;

	isKeyMandatory(key: string): boolean;

	isKeyActive(key: string): boolean;

	activateKey(key: string): void;

	deactivateKey(key: string): void;
}
