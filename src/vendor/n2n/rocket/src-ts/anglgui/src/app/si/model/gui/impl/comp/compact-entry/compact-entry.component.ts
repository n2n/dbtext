import { Component, OnInit } from '@angular/core';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { CompactEntryModel } from '../compact-entry-model';

@Component({
	selector: 'rocket-compact-entry',
	templateUrl: './compact-entry.component.html',
	styleUrls: ['./compact-entry.component.css'],
	host: {'class': 'rocket-compact-entry'}
})
export class CompactEntryComponent implements OnInit {
	uiStructure: UiStructure;
	model: CompactEntryModel;

	constructor() { }

	ngOnInit() {
	}

}
