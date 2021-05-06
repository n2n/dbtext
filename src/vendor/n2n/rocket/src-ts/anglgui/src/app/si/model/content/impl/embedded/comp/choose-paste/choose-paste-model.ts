import { ClipboardService } from 'src/app/si/model/generic/clipboard.service';
import { SiMaskQualifier } from 'src/app/si/model/meta/si-mask-qualifier';
import { SiEntryQualifier } from '../../../../si-entry-qualifier';
import { SiEmbeddedEntry } from '../../model/si-embedded-entry';
import { Subject, Subscription } from 'rxjs';
import { SiGenericEmbeddedEntry } from '../../model/generic/generic-embedded';

export class ChoosePasteModel {
	addables: SiMaskQualifier[] = [];
	pastables: SiEntryQualifier[] = [];
	illegalPastables: SiEntryQualifier[] = [];

	readonly done$ = new Subject<SiEmbeddedEntry>();

	private siGenericEmbeddedEntries: SiGenericEmbeddedEntry[]|null = null;
	private sub: Subscription;

	constructor(public siEmbeddedEntry: SiEmbeddedEntry, private clipboardService: ClipboardService) {
		this.update();

		this.sub = this.clipboardService.changed$.subscribe(() => {
			this.update();
		});
	}

	destroy() {
		if (this.sub) {
			this.sub.unsubscribe();
			this.sub = null;
		}
	}

	update() {
		this.addables = this.siEmbeddedEntry.maskQualifiers;

		this.pastables = [];
		this.illegalPastables = [];

		this.siGenericEmbeddedEntries = this.clipboardService.filterValue(SiGenericEmbeddedEntry);
		for (const siGenericEmbeddedEntry of this.siGenericEmbeddedEntries) {
			if (!siGenericEmbeddedEntry.selectedTypeId) {
				continue;
			}

			if (this.siEmbeddedEntry.containsTypeId(siGenericEmbeddedEntry.selectedTypeId)) {
				this.pastables.push(siGenericEmbeddedEntry.entryQualifier);
			} else {
				this.illegalPastables.push(siGenericEmbeddedEntry.entryQualifier);
			}
		}
	}

	chooseAddable(siMaskQualifier: SiMaskQualifier) {
		this.siEmbeddedEntry.selectedTypeId = siMaskQualifier.identifier.entryBuildupId;
		this.done$.next(this.siEmbeddedEntry);
		this.done$.complete();
	}

	choosePastable(siEntryQualifier: SiEntryQualifier) {
		const siGenericEmbeddedEntry = this.clipboardService.filterValue(SiGenericEmbeddedEntry)
				.find((gene) => {
					return gene.entryQualifier.equals(siEntryQualifier);
				});

		if (!siGenericEmbeddedEntry) {
			return;
		}

		this.siEmbeddedEntry.paste(siGenericEmbeddedEntry);
		this.done$.next(this.siEmbeddedEntry);
	}
}
