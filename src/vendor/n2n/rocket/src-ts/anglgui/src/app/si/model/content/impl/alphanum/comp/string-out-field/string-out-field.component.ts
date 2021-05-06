import { Component, OnInit, ElementRef, HostBinding } from '@angular/core';
import { StringFieldModel } from '../string-field-model';

@Component({
	selector: 'rocket-ui-string-out-field',
	templateUrl: './string-out-field.component.html',
	styleUrls: ['./string-out-field.component.css'],
})
export class StringOutFieldComponent implements OnInit {

	model: StringFieldModel;

	constructor(elRef: ElementRef) {
	}

	ngOnInit() {
	}
	
	@HostBinding('class')
	get class(): string {
		return 'rocket-ui-string-out-field';
	}
	
	@HostBinding('class.form-control-plaintext')
	get bulky(): boolean {
		return this.model.isBulky();
	}
}
