import { UiZoneError } from '../ui-zone-error';
import { ComponentFactoryResolver, ComponentRef, ViewContainerRef } from '@angular/core';
import { UiContent } from '../ui-content';

export class TypeUiContent<T> implements UiContent {
	// public zoneErrors: UiZoneError[] = [];

	constructor(public type: new(...args: any[]) => T, public callback: (cr: ComponentRef<T>) => any) {
	}

	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver): ComponentRef<T> {
		const componentFactory = componentFactoryResolver.resolveComponentFactory<T>(this.type);

		const componentRef = viewContainerRef.createComponent(componentFactory);

		this.callback(componentRef);

		return componentRef;
	}

	// getZoneErrors(): UiZoneError[] {
	// 	return this.zoneErrors;
	// }
}
