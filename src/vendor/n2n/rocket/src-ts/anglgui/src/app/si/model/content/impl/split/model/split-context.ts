import { SplitOption } from './split-option';
import { SiEntry } from '../../../si-entry';
import { Observable } from 'rxjs';

export interface SplitContext {
	readonly style: SplitStyle;

	isKeyActive?: (key: string) => boolean;

	activateKey?: (key: string) => void;

	readonly activeKeys$?: Observable<string[]>;

	getSplitOptions(): SplitOption[];

	getEntry$(key: string): Promise<SiEntry|null>;

}

export interface ManagableSplitContext {

	isKeyActive(key: string): boolean;

	activateKey(key: string): void;

	deactivateKey(key: string): void;
}

export interface SplitStyle {
	iconClass: string|null;
	tooltip: string|null;
}
