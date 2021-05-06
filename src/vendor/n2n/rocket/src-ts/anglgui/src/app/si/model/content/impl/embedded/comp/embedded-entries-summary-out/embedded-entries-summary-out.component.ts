import { Component, OnInit } from '@angular/core';
import { EmbeddedEntriesOutModel } from '../embedded-entries-out-model';
import { EmbeStructure } from '../../model/embe/embe-structure';

@Component({
	selector: 'rocket-embedded-entries-summary-out',
	templateUrl: './embedded-entries-summary-out.component.html'
})
export class EmbeddedEntriesSummaryOutComponent {
	model: EmbeddedEntriesOutModel;

	constructor() { }

	get embeStructures(): EmbeStructure[] {
		return this.model.getEmbeStructures();
	}
}
