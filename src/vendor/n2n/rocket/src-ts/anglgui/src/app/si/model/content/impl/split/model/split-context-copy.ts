import {SiGenericEntry} from '../../../../generic/si-generic-entry';
import { SplitContent } from './split-content-collection';

export class SplitContextCopy {
	private genericMap = new Map<string, SiGenericEntry>();

	static async fromMap(map: Map<string, SplitContent>): Promise<SplitContextCopy> {
		const gsc = new SplitContextCopy();

		const promises = new Array<Promise<void>>();
		for (const [key, value] of map) {
			const entry = value.getLoadedSiEntry();
			if (entry) {
				continue;
			}

			promises.push(entry.copy().then((genericEntry) => {
				gsc.genericMap.set(key, genericEntry);
			}));
		}

		await Promise.all(promises);

		return gsc;
	}

	async applyToMap(splitContentMap: Map<string, SplitContent>): Promise<boolean> {
		const promises = new Array<Promise<boolean>>();

		for (const [key, genericEntry] of this.genericMap) {
			const siEntry = splitContentMap.get(key)?.getLoadedSiEntry();
			if (siEntry) {
				promises.push(siEntry.paste(genericEntry));
			}
		}

		return -1 !== (await Promise.all(promises)).indexOf(true);
	}
}
