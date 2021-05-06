import { AfterViewInit, Component, HostBinding, OnInit } from '@angular/core';
import { LinkOutModel } from '../link-field-model';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';
import { SiUiService } from 'src/app/si/manage/si-ui.service';
import GLightbox from 'glightbox';

@Component({
	selector: 'rocket-link-out-field',
	templateUrl: './link-out-field.component.html',
	styleUrls: ['./link-out-field.component.css'],
	host: {class: 'rocket-link-out-field'}
})
export class LinkOutFieldComponent implements OnInit, AfterViewInit {

	uiZone: UiZone;
	model: LinkOutModel;

	constructor(private siUiService: SiUiService) {
	}

	ngAfterViewInit(): void {
		if (this.model.isLytebox) {
			GLightbox({
				selector:	'.glightbox',
			});
		}
	}

	ngOnInit() {
	}
	
	@HostBinding('class.form-control-plaintext')
	get bulky(): boolean {
		return this.model.isBulky();
	}
}
