import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { OutSiFieldAdapter } from '../../common/model/out-si-field-adapter';
import { LinkOutModel } from '../comp/link-field-model';
import { LinkOutFieldComponent } from '../comp/link-out-field/link-out-field.component';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { SiNavPoint } from 'src/app/si/model/control/si-nav-point';
import { Injector } from '@angular/core';
import { UiStructureType } from 'src/app/si/model/meta/si-structure-declaration';

export class LinkOutSiField extends OutSiFieldAdapter {
	public lytebox = false;

	constructor(public navPoint: SiNavPoint, public label: string, private injector: Injector) {
		super();
	}

	createUiContent(uiStructure: UiStructure): UiContent {
		return new TypeUiContent(LinkOutFieldComponent, (ref) => {
			ref.instance.model = this.createLinkOutModel(uiStructure);
		});
	}

	private createLinkOutModel(uiStructure: UiStructure): LinkOutModel {
		return {
			getLabel: () => this.label,
			getMessages: () => this.getMessages(),
			isBulky: () => !!uiStructure.type && uiStructure.type !== UiStructureType.MINIMAL,
			getUiNavPoint: () => {
				return this.navPoint.toUiNavPoint(this.injector, uiStructure.getZone().layer);
			},
			isLytebox: () => this.lytebox
		};
	}


// 	initComponent(viewContainerRef: ViewContainerRef,
// 			componentFactoryResolver: ComponentFactoryResolver,
// 			commanderService: SiUiService): ComponentRef<any> {
// 		const componentFactory = componentFactoryResolver.resolveComponentFactory(LinkOutFieldComponent);
//
// 			const componentRef = viewContainerRef.createComponent(componentFactory);
//
// 			componentRef.instance.model = this;
//
// 			return componentRef;
// 	}

	getLabel(): string {
		return this.label;
	}

	copyValue(): Promise<SiGenericValue> {
		return Promise.resolve(new SiGenericValue(this.navPoint?.copy() || null));
	}

	pasteValue(genericValue: SiGenericValue): Promise<boolean> {
		if (genericValue.isNull()) {
			this.navPoint = null;
			return Promise.resolve(true);
		}

		if (genericValue.isInstanceOf(SiNavPoint)) {
			this.navPoint = genericValue.readInstance(SiNavPoint).copy();
			return Promise.resolve(true);
		}

		return Promise.resolve(false);
	}
}
