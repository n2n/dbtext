
import { ComponentFactoryResolver, ViewContainerRef, ComponentRef } from '@angular/core';

export interface UiContent {

	initComponent(viewContainerRef: ViewContainerRef, componentFactoryResolver: ComponentFactoryResolver/*,
			uiStructure: UiStructure*/): ComponentRef<any>;

// 	getZoneErrors(): UiZoneError[];

}
