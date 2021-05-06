import { Component, OnInit } from '@angular/core';
import { TextAreaInFieldModel } from '../textarea-in-field-model';

@Component({
	selector: 'rocket-textarea-in-field',
	templateUrl: './textarea-in-field.component.html'
})
export class TextareaInFieldComponent implements OnInit {

	model: TextAreaInFieldModel;

	constructor() { }

	ngOnInit(): void {
	}

	get value(): string|null {
		return this.model.getValue();
	}

	set value(value: string|null) {
		if (value === '') {
			value = null;
		}

		this.model.setValue(value);
	}
}
