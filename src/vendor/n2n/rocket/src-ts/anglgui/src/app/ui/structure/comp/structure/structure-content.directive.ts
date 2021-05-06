import { Directive } from '@angular/core';
import { ViewContainerRef, Input, ComponentFactoryResolver } from '@angular/core';
import { UiContent } from '../../model/ui-content';

@Directive({
	selector: '[rocketUiContent]'
})
export class StructureContentDirective {
// 	@Input()
// 	public uiStructure: UiStructure;
	private _uiContent: UiContent|null = null;

	constructor(public viewContainerRef: ViewContainerRef,
			private componentFactoryResolver: ComponentFactoryResolver) {
// 		viewContainerRef.element.nativeElement.classList.add('rocket-control');
	}

	@Input() set uiContent(uiContent: UiContent|null) {
		if (this._uiContent === uiContent) {
			return;
		}

		this._uiContent = uiContent;
		this.viewContainerRef.clear();

// 		if (!this.uiStructure) {
// 			throw new IllegalSiStateError('Unknown UiStructure for content directive.');
// 		}

		if (uiContent) {
// 			const cfr = this.uiStructure.getZone().layer.container.componentFactoryResolver;
			uiContent.initComponent(this.viewContainerRef, this.componentFactoryResolver/*, this.uiStructure*/);
		}
	}

	get uiContent(): UiContent|null {
		return this._uiContent;
	}
}
