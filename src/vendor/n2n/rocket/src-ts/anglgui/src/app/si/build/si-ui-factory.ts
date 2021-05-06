import { UiBreadcrumb, UiZone } from 'src/app/ui/structure/model/ui-zone';
import { UiLayer } from 'src/app/ui/structure/model/ui-layer';
import { Injector } from '@angular/core';
import { Extractor } from 'src/app/util/mapping/extractor';
import { UiMenuItem, UiMenuGroup } from 'src/app/ui/structure/model/ui-menu';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiEssentialsFactory } from './si-field-essentials-factory';
import { SiBuildTypes } from './si-build-types';

export class SiUiFactory {

	constructor(private injector: Injector) {
	}

	fillZone(data: any, uiZone: UiZone): void {
		const extr = new Extractor(data);

		const comp = new SiBuildTypes.SiGuiFactory(this.injector).buildGui(extr.reqObject('comp'));

		uiZone.title = extr.reqString('title');
		uiZone.breadcrumbs = this.createBreadcrumbs(extr.reqArray('breadcrumbs'), uiZone.layer);
		uiZone.structure = new UiStructure(null, null, comp.createUiStructureModel());
		/*new SiControlFactory(comp, this.injector).createControls(extr.reqArray('controls'))
			.map(siControl => siControl.createUiContent(zone))*/
	}

	createBreadcrumbs(dataArr: Array<any>, uiLayer: UiLayer|null): UiBreadcrumb[] {
		const breadcrumbs: UiBreadcrumb[] = [];

		for (const data of dataArr) {
			breadcrumbs.push(this.createBreadcrumb(data, uiLayer));
		}

		return breadcrumbs;
	}

	createBreadcrumb(data: any, uiLayer: UiLayer|null): UiBreadcrumb {
		const extr = new Extractor(data);

		const navPoint = SiEssentialsFactory
				.createNavPoint(extr.reqObject('navPoint'))
				.toUiNavPoint(this.injector, uiLayer);

		return {
			name: extr.reqString('name'),
			navPoint
		};
	}

	createMenuGroups(dataArr: Array<any>): UiMenuGroup[] {
		const menuGroups = new Array<UiMenuGroup>();
		for (const data of dataArr) {
			menuGroups.push(this.createMenuGroup(data));
		}

		return menuGroups;
	}

	createMenuGroup(data: any): UiMenuGroup {
		const extr = new Extractor(data);

		return new UiMenuGroup(extr.reqString('label'), this.createMenuItems(extr.reqArray('menuItems')));
	}

	createMenuItems(dataArr: Array<any>): UiMenuItem[] {
		return dataArr.map(data => this.createMenuItem(data));
	}

	createMenuItem(data: any): UiMenuItem {
		const extr = new Extractor(data);

		return new UiMenuItem(extr.reqString('id'), extr.reqString('label'), SiEssentialsFactory
				.createNavPoint(extr.reqObject('navPoint'))
				.toUiNavPoint(this.injector, null));
	}
}


