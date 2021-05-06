import { Component, OnInit } from '@angular/core';
import { SplitManagerModel } from '../split-manager-model';

@Component({
	selector: 'rocket-split-manager',
	templateUrl: './split-manager.component.html',
	styleUrls: ['./split-manager.component.css']
})
export class SplitManagerComponent implements OnInit {

	model: SplitManagerModel;
	menuVisible = false;

	constructor() { }

	ngOnInit() {
	}

	toggleMenuVisibility() {
		this.menuVisible = !this.menuVisible;
	}

	toggleKeyActivity(key: string) {
		if (!this.model.isKeyActive(key)) {
			this.model.activateKey(key);
		} else if (!this.model.isKeyMandatory(key)) {
			this.model.deactivateKey(key);
		}
	}
}
