import { SiEmbeddedEntry } from '../model/si-embedded-entry';
import { AddPasteObtainer } from '../comp/add-paste-obtainer';
import { EmbeddedEntryObtainer } from './embedded-entry-obtainer';

export class EmbeddedAddPasteObtainer implements AddPasteObtainer {
	constructor(private obtainer: EmbeddedEntryObtainer) {
	}

	// obtain(siEntryIdentifier: SiEntryIdentifier|null): Observable<SiEmbeddedEntry> {
	// 	return this.obtainer.obtain([siEntryIdentifier]).pipe(map(siEmbeddedEntries => siEmbeddedEntries[0]));
	// }

	preloadNew(): void {
		this.obtainer.preloadNew();
	}

	obtainNew(): Promise<SiEmbeddedEntry> {
		return this.obtainer.obtainNew();
	}


	// val(siEmbeddedEntry: SiEmbeddedEntry) {
	// 	const request = new SiValRequest();
	// 	const instruction = request.instructions[0] = new SiValInstruction(siEmbeddedEntry.entry.readInput());

	// 	if (siEmbeddedEntry.summaryComp) {
	// 		siEmbeddedEntry.summaryComp.entry = null;
	// 		instruction.getInstructions[0] = SiValGetInstruction.create(siEmbeddedEntry.summaryComp, false, true);
	// 	}

	// 	siEmbeddedEntry.entry.resetError();

	// 	this.siService.apiVal(this.apiUrl, request, this.uiZone).subscribe((response: SiValResponse) => {
	// 		const result = response.results[0];

	// 		if (result.entryError) {
	// 			siEmbeddedEntry.entry.handleError(result.entryError);
	// 		}

	// 		if (siEmbeddedEntry.summaryComp) {
	// 			siEmbeddedEntry.summaryComp.entry = result.getResults[0].entry;
	// 		}
	// 	});
	// }
}
