import {Component, ElementRef, EventEmitter, Input, OnInit, Output, Renderer2} from '@angular/core';

@Component({
	selector: 'button[rocketUiButton]',
	templateUrl: './button.component.html'
})
export class ButtonComponent implements OnInit {
	@Input()
	label: string = "";
	@Input()
	important: boolean = false;
	@Input()
	loading: boolean = false;
	@Output()
	execFunc: EventEmitter<any> = new EventEmitter<any>();
	@Input()
	iconClass: string = "";

	constructor(private _elemRef: ElementRef, private _renderer: Renderer2) {
	}

	ngOnInit(): void {
	 this._renderer.setAttribute(this._elemRef.nativeElement, "important", this.important.toString());
	}

	exec() {
		this.execFunc.emit();
	}
}
