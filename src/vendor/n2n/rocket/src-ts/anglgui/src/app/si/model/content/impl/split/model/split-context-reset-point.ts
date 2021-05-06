import { SiInputResetPoint } from '../../../si-input-reset-point';
import { SiEntry } from '../../../si-entry';
import { SplitContent } from './split-content-collection';
import { ManagableSplitContext } from './split-context';

export class SplitContextInputResetPoint implements SiInputResetPoint {
	private activeKeys = new Array<string>();
	private inputResetPointsMap = new Map<string, SiInputResetPoint>();

	constructor(private splitContentMap: Map<string, SplitContent>, private splitContext: ManagableSplitContext) {
	}

	static async create(splitContentMap: Map<string, SplitContent>, splitContext: ManagableSplitContext): Promise<SplitContextInputResetPoint> {
		const scrp = new SplitContextInputResetPoint(splitContentMap, splitContext);

		for (const [key, splitContent] of splitContentMap) {
			if (!splitContext.isKeyActive(key)) {
				continue;
			}

			scrp.activeKeys.push(key);

			splitContent.getLoadedSiEntry$().subscribe(async (entry: SiEntry) => {
				if (entry) {
					scrp.inputResetPointsMap.set(key, await entry.createInputResetPoint());
				}
			});
		}

		return Promise.resolve(scrp);
	}

	private containsActiveKey(key: string): boolean {
		return -1 !== this.activeKeys.indexOf(key);
	}

	async rollbackTo(): Promise<void> {
		for (const [key, ] of this.splitContentMap) {
			if (this.containsActiveKey(key)) {
				this.splitContext.activateKey(key);
			} else {
				this.splitContext.deactivateKey(key);
			}
		}

		const promises = [];
		for (const [, inputResetPoint] of this.inputResetPointsMap) {
			promises.push(inputResetPoint.rollbackTo());
		}

		await Promise.all(promises);
	}
}

export interface ResetableSplitContext {
	// 

	isKeyActive(key: string): boolean;

	activateKey(key: string): void;

	deactivateKey(key: string): void;
}



