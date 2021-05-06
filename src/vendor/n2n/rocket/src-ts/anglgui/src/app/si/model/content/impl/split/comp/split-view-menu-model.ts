import { SplitOption } from '../model/split-option';

export interface SplitViewMenuModel {

	getSplitOptions(): SplitOption[];

	getIconClass(): string;

	getTooltip(): string|null;

	containsVisibleKey(key: string): boolean;

	addVisibleKey(key: string): void;

	removeVisibleKey(key: string): void;

	getVisibleKeysNum(): number;
}
