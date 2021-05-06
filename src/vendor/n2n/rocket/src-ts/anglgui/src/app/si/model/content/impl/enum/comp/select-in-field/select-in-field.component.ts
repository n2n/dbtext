import { Component, OnInit } from '@angular/core';
import { SelectInFieldModel } from '../select-in-field-model';
import { Option } from 'src/app/ui/util/comp/select-input/select.component';

@Component({
	selector: 'rocket-select-in-field',
	templateUrl: './select-in-field.component.html'
})
export class SelectInFieldComponent implements OnInit {

	model: SelectInFieldModel;

	constructor() { }

	ngOnInit(): void {
	}

	get optional(): boolean {
		return !this.model.isMandatory();
	}

	get value(): string|null {
		return this.model.getValue();
	}

	set value(value: string|null) {
		// if (value === '') {
		// 	value = null;
		// }
		this.model.setValue(value);
	}

	get options(): Option[] {
		const options: Option[] = [];

		for (const [value, label] of this.model.getOptions()) {
			options.push({ value, label });
		}

		return options;
	}
}
