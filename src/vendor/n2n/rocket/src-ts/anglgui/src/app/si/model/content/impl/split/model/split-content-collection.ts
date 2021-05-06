import {SplitOption} from './split-option';
import {SiEntry} from '../../../si-entry';
import {SiStyle} from '../../../../meta/si-view-mode';
import {SiService} from '../../../../../manage/si.service';
import {SiControlBoundry} from '../../../../control/si-control-bountry';
import {SiGetInstruction} from '../../../../api/si-get-instruction';
import {SiGetRequest} from '../../../../api/si-get-request';
import {map} from 'rxjs/operators';
import {SiGetResponse} from '../../../../api/si-get-response';
import { SplitContextCopy } from './split-context-copy';
import { Subject, Observable, of } from 'rxjs';
import { SiInputResetPoint } from '../../../si-input-reset-point';
import { SplitContextInputResetPoint } from './split-context-reset-point';
import { ManagableSplitContext } from './split-context';

export class SplitContentCollection {
	protected splitContentMap = new Map<string, SplitContent>();

	putSplitContent(splitContent: SplitContent): void {
		this.splitContentMap.set(splitContent.key, splitContent);
	}

	getSplitContents(): SplitContent[] {
		return Array.from(this.splitContentMap.values());
	}

	containsKey(key: string): boolean {
		return this.splitContentMap.has(key);
	}

	getEntry$(key: string): Promise<SiEntry> {
		if (this.splitContentMap.has(key)) {
			return this.splitContentMap.get(key).getSiEntry$();
		}

		throw new Error('Unknown key.');
	}

	copy(): Promise<SplitContextCopy> {
		return SplitContextCopy.fromMap(this.splitContentMap);
	}

	paste(splitContextCopy: SplitContextCopy): Promise<boolean> {
		return splitContextCopy.applyToMap(this.splitContentMap);
	}

	createInputResetPoint(splitContext: ManagableSplitContext): Promise<SiInputResetPoint> {
		return SplitContextInputResetPoint.create(this.splitContentMap, splitContext);
	}
}

export class SplitContent implements SplitOption {
	private entry$: Promise<SiEntry>|null = null;
	private lazyDef: LazyDef|null = null;
	private loadedEntry: SiEntry|null = null;
	private loadedEntrySubject: Subject<SiEntry>|null = null;

	constructor(readonly key: string, public label: string, public shortLabel: string) {
	}

	static createUnavaialble(key: string, label: string, shortLabel: string): SplitContent {
		const splitContent = new SplitContent(key, label, shortLabel);
		splitContent.entry$ = Promise.resolve(null);
		return splitContent;
	}

	static createLazy(key: string, label: string, shortLabel: string, lazyDef: LazyDef): SplitContent {
		const splitContent = new SplitContent(key, label, shortLabel);
		splitContent.lazyDef = lazyDef;
		splitContent.loadedEntrySubject = new Subject();
		return splitContent;
	}

	static createEntry(key: string, label: string, shortLabel: string, entry: SiEntry): SplitContent {
		const splitContent = new SplitContent(key, label, shortLabel);
		splitContent.entry$ = Promise.resolve(entry);
		splitContent.loadedEntry = entry;
		return splitContent;
	}

	getLoadedSiEntry(): SiEntry|null {
		return this.loadedEntry;
	}

	getLoadedSiEntry$(): Observable<SiEntry|null> {
		if (this.loadedEntrySubject) {
			return this.loadedEntrySubject.asObservable();
		}

		return of(this.loadedEntry);
	}

	getSiEntry$(): Promise<SiEntry|null> {
		if (this.entry$) {
		return this.entry$;
		}

		let instruction: SiGetInstruction|null = null;
		if (this.lazyDef.entryId) {
			instruction = SiGetInstruction.entry(this.lazyDef.style, this.lazyDef.entryId);
		} else {
			instruction = SiGetInstruction.newEntry(this.lazyDef.style);
		}
		instruction.setPropIds(this.lazyDef.propIds);

		return this.entry$ = this.lazyDef.siService.apiGet(this.lazyDef.apiGetUrl, new SiGetRequest(instruction))
				.pipe(map((response: SiGetResponse) => {
					return this.loadedEntry = response.results[0].entry;
				}))
				.toPromise();
	}
}

export interface LazyDef {
	apiGetUrl: string;
	entryId: string|null;
	propIds: string[]|null;
	style: SiStyle;
	siService: SiService;
	siControlBoundy: SiControlBoundry;
}



