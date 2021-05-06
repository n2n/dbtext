import { Component, OnInit } from '@angular/core';
import { EmbeddedEntriesInModel } from '../embedded-entries-in-model';
import { AddPasteObtainer } from '../add-paste-obtainer';
import { EmbeStructure } from '../../model/embe/embe-structure';
import { CopyPool } from '../../model/embe/embe-copy-pool';
import { ClipboardService } from 'src/app/si/model/generic/clipboard.service';

@Component({
	selector: 'rocket-embedded-entries-in',
	templateUrl: './embedded-entries-in.component.html',
	styleUrls: ['./embedded-entries-in.component.css']
})
export class EmbeddedEntriesInComponent implements OnInit {
	model: EmbeddedEntriesInModel;
	copyPool: CopyPool;
	obtainer: AddPasteObtainer;

	constructor(clipboard: ClipboardService) {
		this.copyPool = new CopyPool(clipboard);
	}

	ngOnInit(): void {
		this.obtainer = this.model.getAddPasteObtainer();
	}

	get maxReached(): boolean {
		const max = this.model.getMax();

		return max && max <= this.model.getEmbeStructures().length;
	}

	get toOne(): boolean {
		return this.model.getMax() === 1;
	}

	get embeStructures(): EmbeStructure[] {
		return this.model.getEmbeStructures();
	}

	// add(siEmbeddedEntry: SiEmbeddedEntry) {
	// 	this.embeCol.createEmbe(siEmbeddedEntry);
	// 	this.embeCol.writeEmbes();
	// }

	// addBefore(embe: Embe, siEmbeddedEntry: SiEmbeddedEntry) {
	// 	this.embeCol.createEmbe(siEmbeddedEntry);
	// 	this.embeCol.changeEmbePosition(this.embeCol.embes.length - 1, this.embeCol.embes.indexOf(embe));
	// 	this.embeCol.writeEmbes();
	// }

	// up() {

	// }

	// down() {

	// }
}
