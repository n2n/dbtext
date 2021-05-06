import { Component, OnInit, OnDestroy } from '@angular/core';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { CdkDragDrop } from '@angular/cdk/drag-drop';
import { AddPasteObtainer } from '../add-paste-obtainer';
import { ClipboardService } from 'src/app/si/model/generic/clipboard.service';
import { EmbeddedEntriesInModel } from '../embedded-entries-in-model';
import { Embe } from '../../model/embe/embe';
import { CopyPool } from '../../model/embe/embe-copy-pool';
import { EmbeStructure } from '../../model/embe/embe-structure';


@Component({
	selector: 'rocket-embedded-entries-summary-in',
	templateUrl: './embedded-entries-summary-in.component.html'
})
export class EmbeddedEntriesSummaryInComponent implements OnInit, OnDestroy {
	model: EmbeddedEntriesInModel;

	copyPool: CopyPool;
	obtainer: AddPasteObtainer;

	// embeUiStructures = new Array<{embe: Embe, uiStructure: UiStructure}>();

	constructor(clipboard: ClipboardService) {
		this.copyPool = new CopyPool(clipboard);
	}

	ngOnInit() {
		this.obtainer = this.model.getAddPasteObtainer();
	}

	ngOnDestroy() {
	}

	get maxReached(): boolean {
		const max = this.model.getMax();

		return max && max <= this.embeStructures.length;
	}

	get toOne(): boolean {
		return this.model.getMax() === 1;
	}

	drop(event: CdkDragDrop<string[]>): void {
		this.model.switch(event.previousIndex, event.currentIndex);
	}

	// add(siEmbeddedEntry: SiEmbeddedEntry) {
	// 	this.embeCol.createEmbe(siEmbeddedEntry);
	// 	this.embeCol.writeEmbes();
	// }

	// addBefore(siEmbeddedEntry: SiEmbeddedEntry, embe: Embe) {
	// 	this.embeCol.createEmbe(siEmbeddedEntry);
	// 	this.embeCol.changeEmbePosition(this.embeCol.embes.length - 1, this.embeCol.embes.indexOf(embe));
	// 	this.embeCol.writeEmbes();
	// }

	// // place(siEmbeddedEntry: SiEmbeddedEntry, embe: Embe) {
	// // 	embe.siEmbeddedEntry = siEmbeddedEntry;
	// // 	this.embeCol.writeEmbes();
	// // }

	// remove(embe: Embe) {
	// 	if (this.embeCol.embes.length > this.model.getMin()) {
	// 		this.embeCol.removeEmbe(embe);
	// 		this.embeCol.writeEmbes();
	// 		return;
	// 	}

	// 	embe.siEmbeddedEntry = null;
	// 	this.obtainer.obtainNew().then(siEmbeddedEntry => {
	// 		embe.siEmbeddedEntry = siEmbeddedEntry;
	// 	});
	// }

	get embeStructures(): EmbeStructure[] {
		return this.model.getEmbeStructures();
	}

	open(embe: EmbeStructure) {
		this.model.open(embe);
	}

	// openAll() {
	// 	this.model.openAll();
	// }
}
