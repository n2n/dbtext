import { Component, OnInit, Input } from '@angular/core';
import { UiStructure } from '../../model/ui-structure';
import { UiContent } from '../../model/ui-content';

@Component({
	selector: 'rocket-plain-content',
	templateUrl: './plain-content.component.html',
	styleUrls: ['./plain-content.component.css']
})
export class PlainContentComponent implements OnInit {

	@Input()
	uiStructure: UiStructure;
	@Input()
	uiContent: UiContent|null = null;

	constructor() { }

	ngOnInit() {
	}
}
