import { Component, OnInit, ChangeDetectorRef, DoCheck, HostBinding } from '@angular/core';
import { SplitViewMenuModel } from '../split-view-menu-model';

@Component({
	selector: 'rocket-split-view-menu',
	templateUrl: './split-view-menu.component.html'
})
export class SplitViewMenuComponent implements OnInit {
	model: SplitViewMenuModel;
	menuVisible = false;

	constructor(private cdRef: ChangeDetectorRef) {
	}

	@HostBinding('class')
	class = 'rocket-split-view-menu';

	ngOnInit(): void {
	}

	toggleMenuVisibility() {
		this.menuVisible = !this.menuVisible;
	}

	isKeyVisible(key: string): boolean {
		return this.model.containsVisibleKey(key);
	}

	isKeyMandatory(key: string): boolean {
		return this.isKeyVisible(key) && this.model.getVisibleKeysNum() === 1;
	}

	toggleKeyVisibility(key: string) {
		if (this.isKeyVisible(key)) {
			this.model.removeVisibleKey(key);
		} else {
			this.model.addVisibleKey(key);
		}
	}

	get activeShortLabel() {
		const shortLabels = [];
		for (const splitOption of this.model.getSplitOptions()) {
			if (this.isKeyVisible(splitOption.key)) {
				shortLabels.push(splitOption.shortLabel);
			}
		}
		return shortLabels.join(', ');
	}
}
