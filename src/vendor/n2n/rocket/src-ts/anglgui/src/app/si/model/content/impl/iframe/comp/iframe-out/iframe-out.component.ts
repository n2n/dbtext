import { Component, OnInit } from '@angular/core';
import { IframeOutModel } from '../iframe-out-model';

@Component({
	selector: 'rocket-iframe-out',
	templateUrl: './iframe-out.component.html'
})
export class IframeOutComponent implements OnInit {

	model: IframeOutModel;

	constructor() { }

	ngOnInit(): void {
	}

}
