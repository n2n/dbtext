import { Component, OnInit, OnDestroy, ComponentFactoryResolver } from '@angular/core';
import { ActivatedRoute, Router, NavigationStart } from '@angular/router';
import { SiUiService } from 'src/app/si/manage/si-ui.service';
import { Subscription } from 'rxjs';
import { filter } from 'rxjs/operators';
import { UiContainer } from 'src/app/ui/structure/model/ui-container';
import { MainUiLayer } from 'src/app/ui/structure/model/ui-layer';
import { SiModStateService } from 'src/app/si/model/mod/model/si-mod-state.service';
import { Message } from 'src/app/util/i18n/message';

@Component({
	selector: 'rocket-ei',
	templateUrl: './ei.component.html',
	styleUrls: ['./ei.component.css']
})
export class EiComponent implements OnInit, OnDestroy {

	uiContainer: UiContainer;
	private subscription: Subscription;

	constructor(private route: ActivatedRoute, private siUiService: SiUiService,
			private router: Router/*, platformLocation: PlatformLocation*/,
			componentFactoryResolver: ComponentFactoryResolver,
			private siModState: SiModStateService) {
		this.uiContainer = new UiContainer(componentFactoryResolver);
// 		alert(platformLocation.getBaseHrefFromDOM() + ' ' + route.snapshot.url.join('/'));
	}

	ngOnInit(): void {
		this.subscription = this.router.events
				.pipe(filter((event) => {
					// console.log(event);
					return (event instanceof NavigationStart);
				}))
				.subscribe((event: NavigationStart) => {
					this.handleNav(event);
				});

		// @todo find out if this works

		let id = 1;
		const curNav = this.router.getCurrentNavigation();
		if (curNav) {
			id = curNav.id;
		}

		const zone = this.mainUiLayer.pushRoute(1, this.route.snapshot.url.join('/')).zone;
		this.siUiService.loadZone(zone, false);
	}

	ngOnDestroy(): void {
		this.subscription.unsubscribe();
	}

	get mainUiLayer(): MainUiLayer {
		return this.uiContainer.getMainLayer();
	}

	private handleNav(event: NavigationStart): void {
		const url = event.url.substr(1);

		switch (event.navigationTrigger) {
		case 'popstate':
			if (event.restoredState &&
					this.mainUiLayer.switchRouteById(event.restoredState.navigationId, url)) {
				this.mainUiLayer.changeCurrentRouteId(event.id);
				break;
			}
		case 'imperative':
			this.mainUiLayer.pushRoute(event.id, url);
			this.siUiService.loadZone(this.mainUiLayer.currentRoute.zone, true);
			break;
		default:
			// console.log('state ' + event.navigationTrigger);
		}

	}

	get messages(): Message[] {
		return this.siModState.lastMessages;
	}
}
