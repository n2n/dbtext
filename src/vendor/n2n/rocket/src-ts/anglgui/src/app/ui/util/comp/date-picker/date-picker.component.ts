import { Component, OnInit, EventEmitter, Output, Input } from '@angular/core';
import { BsDatepickerConfig, BsLocaleService } from 'ngx-bootstrap/datepicker';


@Component({
	selector: 'rocket-ui-date-picker',
	templateUrl: './date-picker.component.html'
})
export class DatePickerComponent implements OnInit {


	private pDate: Date|null = null;
	bsConfig: Partial<BsDatepickerConfig> = {
		containerClass: 'theme-rocket',
		customTodayClass: 'bs-datepicker-today',
		adaptivePosition: true
	};

	constructor(private localeService: BsLocaleService) {
		this.localeService.use('de');
	}

	@Input()
	set date(date: Date|null) {
		this.pDate = date;
	}

	get date(): Date|null {
		return this.pDate;
	}

	@Output()
	private dateChange = new EventEmitter<Date|null>();
	// dateStruct: NgbDateStruct|null = null;

	// constructor(@Inject(LOCALE_ID) private localeId: string) { }

	ngOnInit(): void {
		// this.dateStruct =	{
		// 	year: this.date.getFullYear(),
		// 	month: this.date.getMonth(),
		// 	day: this.date.getDate()
		// };
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

		this.pDate.setDate(date.getDate());
		this.pDate.setMonth(date.getMonth());
		this.pDate.setFullYear(date.getFullYear());
		this.dateChange.emit(this.pDate);
	}
}


