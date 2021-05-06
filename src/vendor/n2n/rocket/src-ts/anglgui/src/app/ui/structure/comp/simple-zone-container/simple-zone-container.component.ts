import {Component, ElementRef, Input, OnInit} from '@angular/core';
import {UiBreadcrumb} from '../../model/ui-zone';
import {Message} from 'src/app/util/i18n/message';

@Component({
	selector: 'rocket-ui-simple-zone-container',
	templateUrl: './simple-zone-container.component.html',
	styleUrls: ['./simple-zone-container.component.css']
})
export class SimpleZoneContainerComponent implements OnInit {

	@Input()
	messages: Message[] = [];
	@Input()
	title: string;
	@Input()
	loading = false;
	@Input()
	breadcrumbs: UiBreadcrumb[] = [];

	constructor(elemRef: ElementRef) {
		elemRef.nativeElement.classList.add('rocket-container');
	}

	ngOnInit() {
	}

}
