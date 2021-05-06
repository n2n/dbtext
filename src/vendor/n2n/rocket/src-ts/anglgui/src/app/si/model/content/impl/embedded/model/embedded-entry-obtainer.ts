import { SiEntryIdentifier } from 'src/app/si/model/content/si-entry-qualifier';
import { SiGetInstruction } from 'src/app/si/model/api/si-get-instruction';
import { Observable } from 'rxjs';
import { SiGetRequest } from 'src/app/si/model/api/si-get-request';
import { map } from 'rxjs/operators';
import { SiGetResponse } from 'src/app/si/model/api/si-get-response';
import { SiValRequest } from 'src/app/si/model/api/si-val-request';
import { SiValInstruction } from 'src/app/si/model/api/si-val-instruction';
import { SiValGetInstruction } from 'src/app/si/model/api/si-val-get-instruction';
import { SiValResponse } from 'src/app/si/model/api/si-val-response';
import { SiValResult } from 'src/app/si/model/api/si-val-result';
import { SiService } from 'src/app/si/manage/si.service';
import { BulkyEntrySiGui } from 'src/app/si/model/gui/impl/model/bulky-entry-si-gui';
import { CompactEntrySiGui } from 'src/app/si/model/gui/impl/model/compact-entry-si-gui';
import { SiEmbeddedEntry } from '../model/si-embedded-entry';
import { SiGetResult } from 'src/app/si/model/api/si-get-result';
import { SiFrame, SiFrameApiSection } from 'src/app/si/model/meta/si-frame';
import { SiModStateService } from 'src/app/si/model/mod/model/si-mod-state.service';
import { SiApiFactory } from 'src/app/si/build/si-api-factory';

export class EmbeddedEntryObtainer	{

	constructor(public siService: SiService, public siModStateService: SiModStateService, public siFrame: SiFrame,
			public obtainSummary: boolean, public typeIds: Array<string>|null) {
	}

	private preloadedNew$: Promise<SiEmbeddedEntry>|null = null;

	private createBulkyInstruction(siEntryIdentifier: SiEntryIdentifier|null): SiGetInstruction {
		if (siEntryIdentifier) {
			return SiGetInstruction.entry({ bulky: true, readOnly: false }, siEntryIdentifier.id);
		}

		return SiGetInstruction.newEntry({ bulky: true, readOnly: false }).setTypeIds(this.typeIds);
	}

	private createSummaryInstruction(siEntryIdentifier: SiEntryIdentifier|null): SiGetInstruction {
		if (siEntryIdentifier) {
			return SiGetInstruction.entry({ bulky: false, readOnly: true }, siEntryIdentifier.id);
		}

		return SiGetInstruction.newEntry({ bulky: false, readOnly: true }).setTypeIds(this.typeIds);
	}

	preloadNew(): void {
		if (this.preloadedNew$) {
			return;
		}

		this.preloadedNew$ = this.obtain([null]).pipe(map(siEmbeddedEntries => siEmbeddedEntries[0])).toPromise();
	}

	obtainNew(): Promise<SiEmbeddedEntry> {
		this.preloadNew();
		const siEmbeddedEntry$ = this.preloadedNew$;
		this.preloadedNew$ = null;
		this.preloadNew();
		return siEmbeddedEntry$;
	}

	obtain(siEntryIdentifiers: Array<SiEntryIdentifier|null>): Observable<SiEmbeddedEntry[]> {
		const request = new SiGetRequest();

		for (const siEntryIdentifier of siEntryIdentifiers) {
			request.instructions.push(this.createBulkyInstruction(siEntryIdentifier));

			if (this.obtainSummary) {
				request.instructions[1] = this.createSummaryInstruction(siEntryIdentifier);
			}
		}

		return this.siService.apiGet(this.siFrame.getApiUrl(SiFrameApiSection.GET), request).pipe(map((siGetResponse) => {
			return this.handleResponse(siGetResponse);
		}));
	}

	private handleResponse(response: SiGetResponse): SiEmbeddedEntry[] {
		const siEmbeddedEntries = new Array<SiEmbeddedEntry>();

		let result: SiGetResult;
		while (result = response.results.shift()) {
			const siComp = new BulkyEntrySiGui(this.siFrame, result.declaration, this.siService, this.siModStateService);
			siComp.entry = result.entry;

			let summarySiGui: CompactEntrySiGui|null = null;
			if (this.obtainSummary) {
				result = response.results.shift();
				summarySiGui = new CompactEntrySiGui(this.siFrame, result.declaration, this.siService, this.siModStateService);
				summarySiGui.entry = result.entry;
			}

			siEmbeddedEntries.push(new SiEmbeddedEntry(siComp, summarySiGui));
		}

		return siEmbeddedEntries;
	}

	val(siEmbeddedEntries: SiEmbeddedEntry[]): void {
		const request = new SiValRequest();

		siEmbeddedEntries.forEach((siEmbeddedEntry, i) => {
			request.instructions[i] = this.createValInstruction(siEmbeddedEntry);

			// siEmbeddedEntry.entry.resetError();
		});

		this.siService.apiVal(this.siFrame, request).subscribe((response: SiValResponse) => {
			siEmbeddedEntries.forEach((siEmbeddedEntry, i) => {
				this.handleValResult(siEmbeddedEntry, response.results[i]);
			});
		});
	}

	private handleValResult(siEmbeddedEntry: SiEmbeddedEntry, siValResult: SiValResult): void {
		siEmbeddedEntry.entry.replace(siValResult.getResults[0].entry);

		if (siEmbeddedEntry.summaryComp) {
			siEmbeddedEntry.summaryComp.entry = siValResult.getResults[1].entry;
		}
	}

	private createValInstruction(siEmbeddedEntry: SiEmbeddedEntry): SiValInstruction {
		const instruction = new SiValInstruction(siEmbeddedEntry.entry.readInput());

		instruction.getInstructions[0] = SiValGetInstruction.create({ bulky: true, readOnly: false });

		if (siEmbeddedEntry.summaryComp) {
			siEmbeddedEntry.summaryComp.entry = null;
			instruction.getInstructions[1] = SiValGetInstruction.create({ bulky: false, readOnly: true });
		}

		return instruction;
	}
}
