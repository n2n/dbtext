import { Component, OnInit, ElementRef, Input } from '@angular/core';
import { MessageFieldModel } from '../message-field-model';

@Component({
	selector: 'rocket-field-messages',
	templateUrl: './field-messages.component.html',
	styleUrls: ['./field-messages.component.css']
})
export class FieldMessagesComponent implements OnInit {
	@Input()
	model: MessageFieldModel;

	constructor(private elemRef: ElementRef) {
		elemRef.nativeElement.classList.add('rocket-message-error');
	}

	ngOnInit() {
	}

}
