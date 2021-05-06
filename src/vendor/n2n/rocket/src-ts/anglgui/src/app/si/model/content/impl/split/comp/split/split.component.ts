import { Component, OnInit } from '@angular/core';
import { SplitModel } from '../split-model';

@Component({
	selector: 'rocket-split',
	templateUrl: './split.component.html',
	styleUrls: ['./split.component.css'],
	host: { class: 'rocket-split' }
})
export class SplitComponent implements OnInit {

	model: SplitModel;

	constructor() {
	}

	ngOnInit() {
	}

	// isKeyActive(key: string): boolean {
	// 	return this.model.isKeyActive(key);
	// }

	// activateKey(key: string) {
	// 	this.model.activateKey(key);
	// }

	// getLabelByKey(key: string) {
	// 	return this.model.getSplitOptions().find(splitOption => splitOption.key === key).label;
	// }

	// isKeyVisible(key: string): boolean {
	// 	return this.subscription.isKeyVisible(key);
	// }

}
