import { AfterViewInit, Directive, ElementRef, HostListener, Input, OnDestroy } from '@angular/core';
import { GlightBoxElement } from './glight-box-element';
import { GlightBoxService } from './glight-box.service';

@Directive({
  selector: 'a[rocketUiGlightBox]'
})
export class GlightBoxDirective implements AfterViewInit, OnDestroy {

	private elemRef: ElementRef;
	private glightBoxService: GlightBoxService;
	
	@Input()
	glightboxEnabled = true;
	
	private glightBoxElement: GlightBoxElement|null = null;
	
	
	constructor(elemRef: ElementRef, glightBoxService: GlightBoxService) {
		this.elemRef = elemRef;
		this.glightBoxService = glightBoxService;
	}

	ngAfterViewInit() {
		this.glightBoxElement = {
			href: this.elemRef.nativeElement.href,
			type: 'image'
		};
		if (this.glightboxEnabled) {
			this.glightBoxService.registerElement(this.glightBoxElement);
		}
	}

	ngOnDestroy(): void {
		this.glightBoxService.unregisterElement(this.glightBoxElement);
	}
	
	@HostListener('click', ['$event'])
	onClick(e: MouseEvent) {
		if (this.glightboxEnabled) {
			e.preventDefault();
			this.glightBoxService.open(this.glightBoxElement);
		}
	}
}
