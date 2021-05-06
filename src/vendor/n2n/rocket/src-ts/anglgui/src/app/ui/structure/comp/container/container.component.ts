import { Component, OnInit, Input } from '@angular/core';
import { UiContainer } from '../../model/ui-container';

@Component({
	selector: 'rocket-ui-container',
	templateUrl: './container.component.html',
	styleUrls: ['./container.component.css']
})
export class ContainerComponent implements OnInit {
	@Input()
	uiContainer: UiContainer;

	constructor() { }

	ngOnInit() {
	}
}
