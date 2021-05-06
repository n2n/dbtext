import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { UtilModule } from 'src/app/util/util.module';
import { RouterModule } from '@angular/router';
import { PlainContentComponent } from './structure/comp/plain-content/plain-content.component';
import { StructureContentDirective } from './structure/comp/structure/structure-content.directive';
import { StructureBranchComponent } from './structure/comp/structure-branch/structure-branch.component';
import { ContainerComponent } from './structure/comp/container/container.component';
import { ZoneComponent } from './structure/comp/zone/zone.component';
import { LayerComponent } from './structure/comp/layer/layer.component';
import { StructureComponent } from './structure/comp/structure/structure.component';
import { MessageComponent } from './util/comp/message/message.component';
import { BreadcrumbsComponent } from './structure/comp/inc/breadcrumbs/breadcrumbs.component';
import { NavPointDirective } from './util/directive/nav-point.directive';
import { TogglerComponent } from './util/comp/toggler/toggler.component';
import { SearchInputGroupComponent } from './util/comp/search-input-group/search-input-group.component';
import { FormsModule } from '@angular/forms';
import { SimpleZoneContainerComponent } from './structure/comp/simple-zone-container/simple-zone-container.component';
import { ButtonComponent } from './util/comp/button/button.component';
import { MessagesComponent } from './util/comp/message/messages.component';
import { SelectComponent } from './util/comp/select-input/select.component';
import { IframeComponent } from './util/comp/iframe/iframe.component';
import { IFrameResizerDirective } from './util/directive/iframe-resizer-directive.directive';
import { UrlIframeComponent } from './util/comp/url-iframe/url-iframe.component';
import { PaginationComponent } from './util/comp/pagination/pagination.component';
import { DatePickerComponent } from './util/comp/date-picker/date-picker.component';
import { TimePickerComponent } from './util/comp/time-picker/time-picker.component';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { BsDatepickerModule } from 'ngx-bootstrap/datepicker';
import { TimepickerModule } from 'ngx-bootstrap/timepicker';
import { BsDropdownModule } from 'ngx-bootstrap/dropdown';

import { defineLocale } from 'ngx-bootstrap/chronos';
import { deLocale, frLocale, itLocale, enGbLocale } from 'ngx-bootstrap/locale';
import { GlightBoxDirective } from './glightbox/glight-box.directive';
import { StructureToolbarDirective } from './structure/comp/structure/structure-toolbar.directive';

defineLocale('de', deLocale);
defineLocale('fr', frLocale);
defineLocale('it', itLocale);
defineLocale('en', enGbLocale);

@NgModule({
	declarations: [
		LayerComponent, ContainerComponent, ZoneComponent, StructureComponent, StructureContentDirective,
		StructureBranchComponent, PlainContentComponent, MessageComponent, BreadcrumbsComponent, NavPointDirective,
		TogglerComponent, SearchInputGroupComponent, SimpleZoneContainerComponent, ButtonComponent, MessagesComponent,
		IframeComponent, IFrameResizerDirective, IFrameResizerDirective, SelectComponent, UrlIframeComponent,
		PaginationComponent, DatePickerComponent, TimePickerComponent, GlightBoxDirective, StructureToolbarDirective
	],
	imports: [
		CommonModule,
		UtilModule,
		RouterModule,
		FormsModule,
		BrowserAnimationsModule,
		BsDatepickerModule.forRoot(),
		TimepickerModule.forRoot(),
		BsDropdownModule.forRoot()
	],
	exports: [
		ContainerComponent,
		StructureComponent,
		StructureToolbarDirective,
		StructureContentDirective,
		StructureBranchComponent,
		PlainContentComponent,
		MessageComponent,
		NavPointDirective,
		TogglerComponent,
		SearchInputGroupComponent,
		SimpleZoneContainerComponent,
		ButtonComponent,
		MessagesComponent,
		IframeComponent,
		IFrameResizerDirective,
		SelectComponent,
		UrlIframeComponent,
		PaginationComponent,
		DatePickerComponent,
		TimePickerComponent,
		GlightBoxDirective
	],
	entryComponents: [ PlainContentComponent ]
})
export class UiModule { }
