import { Component, OnInit, Inject, LOCALE_ID, ChangeDetectorRef } from '@angular/core';
import { InputInFieldModel } from '../input-in-field-model';

@Component({
	selector: 'rocket-number-in',
	templateUrl: './number-in.component.html'
})
export class NumberInComponent implements OnInit {
	model: InputInFieldModel;
	decimalPoint: string;

	constructor(@Inject(LOCALE_ID) localeId: string) {
		this.decimalPoint = new Intl.NumberFormat(localeId).format(1.1).replace(/1/g, '');
	}
	ngOnInit(): void {
	}
	get grouped(): boolean {
		return true;
	}

	set parsedValue(parsedValue: string|null) {
		this.value = this.parseValue(parsedValue);
	}

	get parsedValue(): string {
		if (this.decimalPoint !== '.') {
			return this.value.replace('.', this.decimalPoint);
		}
		
		return this.value;
	}

	private get value(): string {
		return this.model.getValue();
	}

	private set value(value: string|null) {
		if (!this.isValueValid(value)) {
			throw 'Invalid value set';
		}

		if (value === '') {
			value = null;
		}
		this.model.setValue(value);
	}

	private isValueValid(value: string|null): boolean {
		if (value === null || value.length === 0) {
			return true;
		}

		return value.match(/^-?[0-9]+(\.[0-9]*)?$/) !== null;
	}

	get step(): number {
		return this.model.getStep();
	}

	get factor(): number {
		return Math.pow(10, this.step.toString().length - (this.step.toString().lastIndexOf('.') + 1));
	}

	get flooredStep(): number {
		return Math.round(parseFloat(this.value) * this.factor / (this.step * this.factor)) * this.step;
	}

	nextStep(): void {
		if (null === this.step) {
			return;
		}

		this.value = (this.flooredStep + this.step).toString();
	}

	prevStep(): void {
		if (null === this.model.getStep()) {
			return;
		}
		this.value = (this.flooredStep - this.step).toString();
	}

	private parseValue(value: string|null) {
		if (value === null) {
			return null;
		}
		if (this.decimalPoint === '.') {
			return value;
		}

		return value.replace(this.decimalPoint, '.');
	}

	validate(event: any): void {
		console.log(typeof event);
		if (!this.isValueValid(this.parseValue(event.target.value))) {
			event.target.value = this.parsedValue;
		} else {
			this.parsedValue = event.target.value;
		}
	}

	onFocus() {

	}

	onBlur() {
		
	}
}
