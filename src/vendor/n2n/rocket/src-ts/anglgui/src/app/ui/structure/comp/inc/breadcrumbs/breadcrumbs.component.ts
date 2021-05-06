import { Component, OnInit, Input } from '@angular/core';
import { UiBreadcrumb } from '../../../model/ui-zone';

@Component({
	selector: 'rocket-ui-breadcrumbs',
	templateUrl: './breadcrumbs.component.html',
	styleUrls: ['./breadcrumbs.component.css']
})
export class BreadcrumbsComponent implements OnInit {

	@Input()
	uiBreadcrumbs: UiBreadcrumb[] = [];

	constructor() { }

	ngOnInit() {
	}

	isLast(uiBreadcrumb: UiBreadcrumb): boolean {
		return this.uiBreadcrumbs.length > 0 && this.uiBreadcrumbs[this.uiBreadcrumbs.length - 1] === uiBreadcrumb;
	}
}
