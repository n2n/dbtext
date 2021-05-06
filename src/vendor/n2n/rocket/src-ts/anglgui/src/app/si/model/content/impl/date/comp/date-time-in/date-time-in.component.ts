import { Component, OnInit, LOCALE_ID } from '@angular/core';
import { DateTimeInModel } from '../date-time-in-model';

@Component({
	selector: 'rocket-date-time-in',
	templateUrl: './date-time-in.component.html'
})
export class DateTimeInComponent implements OnInit {
	constructor() { }

	model: DateTimeInModel;

	private pEnabled = false;

	get enabled(): boolean {
		return this.model.mandatory || !!this.model.getValue() || this.pEnabled;
	}

	set enabled(enabled: boolean) {
		this.pEnabled = enabled;

		if (!enabled && !this.model.mandatory) {
			this.model.setValue(null);
		}
	}

	ngOnInit(): void {

	}



}
