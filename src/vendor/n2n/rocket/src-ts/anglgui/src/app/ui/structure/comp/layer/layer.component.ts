import { Component, OnInit, Input, ElementRef } from '@angular/core';
import { UiLayer } from '../../model/ui-layer';
import { Subscription } from 'rxjs';

@Component({
	selector: 'rocket-ui-layer',
	templateUrl: './layer.component.html',
	styleUrls: ['./layer.component.css']
})
export class LayerComponent implements OnInit {
	@Input()
	uiLayer: UiLayer;

	constructor(private elemRef: ElementRef) {
	}

	ngOnInit() {
		// this.subscription.add(fromEvent<MouseEvent>(this.nativeElement, 'scroll').subscribe(() => {

		// }));
	}

	get nativeElement(): HTMLElement {
		return this.elemRef.nativeElement;
	}
}
