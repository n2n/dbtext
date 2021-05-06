import { UiNavPoint } from 'src/app/ui/util/model/ui-nav-point';
import { Injector } from '@angular/core';
import { PlatformService } from 'src/app/util/nav/platform.service';
import { UiLayer } from 'src/app/ui/structure/model/ui-layer';
import { SiUiService } from '../../manage/si-ui.service';

export class SiNavPoint {
	constructor(public url: string, public siref: boolean) {

	}

	toUiNavPoint(injector: Injector, uiLayer: UiLayer|null): UiNavPoint {
		if (!this.siref) {
			return {
				href: this.url
			};
		}

		const routerLink = injector.get(PlatformService).routerUrl(this.url);

		if (!uiLayer || uiLayer.main) {
			return {
				routerLink
			};
		}

		return {
			routerLink,
			callback: () => {
				injector.get(SiUiService).navigateByRouterUrl(routerLink, uiLayer);
				return false;
			}
		};
	}

	copy(): SiNavPoint {
		return new SiNavPoint(this.url, this.siref);
	}
}
