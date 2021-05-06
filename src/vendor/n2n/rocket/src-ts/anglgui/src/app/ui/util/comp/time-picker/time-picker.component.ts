import { Component, OnInit, Input, EventEmitter, Output } from '@angular/core';

@Component({
	selector: 'rocket-ui-time-picker',
	templateUrl: './time-picker.component.html'
})
export class TimePickerComponent implements OnInit {
	private pDate: Date|null = null;

	@Output()
	private dateChange = new EventEmitter<Date>();

	ngOnInit(): void {
		this.date = this.pDate;
	}

	@Input()
	set date(date: Date|null) {
		this.pDate = date;
	}

	get date(): Date|null {
		return this.pDate;
	}

	selectDate(date: Date|null): void {
		if (date && isNaN(date.getTime())) {
			return;
		}

		if (!this.pDate || !date) {
			this.pDate = date;
			this.dateChange.emit(this.pDate);
			return;
		}

		this.pDate.setHours(date.getHours());
		this.pDate.setMinutes(date.getMinutes());
		this.pDate.setSeconds(date.getSeconds());
		this.dateChange.emit(this.pDate);
	}
}
