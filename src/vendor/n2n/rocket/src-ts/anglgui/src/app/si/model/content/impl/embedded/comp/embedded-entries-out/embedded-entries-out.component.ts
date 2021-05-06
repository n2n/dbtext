import { Component, OnInit } from '@angular/core';
import { EmbeddedEntriesOutModel } from '../embedded-entries-out-model';
import { EmbeStructure } from '../../model/embe/embe-structure';

@Component({
	selector: 'rocket-embedded-entries-out',
	templateUrl: './embedded-entries-out.component.html',
	styleUrls: ['./embedded-entries-out.component.css']
})
export class EmbeddedEntriesOutComponent implements OnInit {
	model: EmbeddedEntriesOutModel;

	constructor() { }

	ngOnInit() {
	}

	get embeStructures(): EmbeStructure[] {
		return this.model.getEmbeStructures();
	}

}
