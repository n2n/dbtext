import { Component, OnInit, Input, OnDestroy, HostBinding } from '@angular/core';
import { UiZone } from '../../model/ui-zone';
import { UiZoneError } from '../../model/ui-zone-error';
import { UiContent } from '../../model/ui-content';

@Component({
	selector: 'rocket-ui-zone',
	templateUrl: './zone.component.html',
	styleUrls: ['./zone.component.css']
})
export class ZoneComponent implements OnInit, OnDestroy {

	@Input() uiZone: UiZone;

	// uiZoneErrors: UiZoneError[] = [];

	// private subscription: Subscription;

	constructor(/*private elemRef: ElementRef*/) {
	}

	ngOnInit() {
		// this.subscription = this.uiZone.uiStructure.getZoneErrors$().subscribe((uiZoneErrors) => {
		// 	this.uiZoneErrors = uiZoneErrors;
		// });
	}

	ngOnDestroy() {
		// this.subscription.unsubscribe();
		// this.subscription = null;
	}

	get uiZoneErrors(): UiZoneError[] {
		if (!this.uiZone.structure) {
			return [];
		}

		return this.uiZone.structure.getZoneErrors();
	}

	get asideCommandUiContents(): UiContent[] {
		if (!this.uiZone.structure || !this.uiZone.structure.model) {
			return [];
		}
		return this.uiZone.structure.model.getAsideContents();
	}

	@HostBinding('class.rocket-contains-additional')
	hasUiZoneErrors() {
		return this.uiZoneErrors.length > 0;
	}

	get contextMenuUiContents(): UiContent[] {
		return this.uiZone.contextMenuContents;
	}

	get partialCommandUiContents(): UiContent[] {
		return this.uiZone.partialCommandContents;
	}

	get mainCommandUiContents(): UiContent[] {
		if (this.uiZone.structure && this.uiZone.structure.model && this.uiZone.structure.model.getMainControlContents().length > 0) {
			return [...this.uiZone.mainCommandContents, ...this.uiZone.structure.model.getMainControlContents()];
		}

		return this.uiZone.mainCommandContents;
	}


}
