import { Component, OnInit, Input, EventEmitter, Output	} from '@angular/core';

@Component({
	selector: 'rocket-search-input-group',
	templateUrl: './search-input-group.component.html',
	styleUrls: ['./search-input-group.component.css']
})
export class SearchInputGroupComponent implements OnInit {
	private _value: string|null = null;
	@Input() placeholder: string|null = null;
	@Output() valueChange = new EventEmitter<string|null>();

	constructor() { }

	ngOnInit() {
	}

	@Input()
	set value(value: string|null) {
		if (value === '') {
			value = null;
		}

		if (this._value === value) {
			return;
		}

		this._value = value;
		this.valueChange.emit(value);
	}

	get value(): string|null {
		return this._value;
	}
}
