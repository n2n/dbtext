import { Component, OnInit } from '@angular/core';
import { StringArrayInModel } from '../string-array-in-model';
import { StringArrayInElement } from './string-array-in-element';

@Component({
	selector: 'rocket-string-array-in',
	templateUrl: './string-array-in.component.html'
})
export class StringArrayInComponent implements OnInit {

	model: StringArrayInModel;
	stringArrayInElements: Array<StringArrayInElement> = null;

	constructor() { }

	ngOnInit(): void {
	}

	getStringArrayInElements(): Array<StringArrayInElement> {
		if (null === this.stringArrayInElements) {
			this.stringArrayInElements = [];
			this.model.getValues().forEach((value: string) => {
				this.stringArrayInElements.push(new StringArrayInElement(value));
			});

			for (let i = this.stringArrayInElements.length; i < this.model.getMin(); i++) {
				this.stringArrayInElements.push(new StringArrayInElement(''));
			}
		}

		return this.stringArrayInElements;
	}

	removeStringArrayInElement(stringArrayInElement: StringArrayInElement): void {
		const i = this.stringArrayInElements.indexOf(stringArrayInElement);
		if (i === -1) {
			return;
		}

		this.stringArrayInElements.splice(i, 1);

		this.applyValues();
	}

	applyValues(): void {
		const values = [];
		this.stringArrayInElements.forEach((stringArrayInElement: StringArrayInElement) => {
			values.push(stringArrayInElement.value);
		});

		this.model.setValues(values);
	}

	get addDisabled(): boolean {
		return this.model.getMax() !== null && this.model.getMax() > 0 && this.stringArrayInElements.length >= this.model.getMax();
	}

	get removeDisabled(): boolean {
		return this.stringArrayInElements.length <= this.model.getMin();
	}

	addElement(): void {
		this.stringArrayInElements.push(new StringArrayInElement(''));
		this.applyValues();
	}
}
