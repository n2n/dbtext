import { Component, OnInit } from '@angular/core';
import { TogglerInModel } from '../toggler-in-model';

@Component({
	selector: 'rocket-toggler-in-field',
	templateUrl: './toggler-in-field.component.html',
	styleUrls: ['./toggler-in-field.component.css'],
	host: {class: 'rocket-toggler-in-field'}
})
export class TogglerInFieldComponent implements OnInit {
	model: TogglerInModel;

	constructor() { }

	ngOnInit() {
	}

}
