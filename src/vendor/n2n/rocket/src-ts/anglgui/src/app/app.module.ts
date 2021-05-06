import { BrowserModule } from '@angular/platform-browser';
import { NgModule, LOCALE_ID } from '@angular/core';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { OpModule } from 'src/app/op/op.module';
import {BrowserAnimationsModule, NoopAnimationsModule} from '@angular/platform-browser/animations';
import { UtilModule } from './util/util.module';
import { AppStateService } from './app-state.service';

import localeDeCh from '@angular/common/locales/de-CH';
import { registerLocaleData } from '@angular/common';
registerLocaleData(localeDeCh);

@NgModule({
	declarations: [
	AppComponent
	],
	imports: [
		BrowserAnimationsModule,
		NoopAnimationsModule,
		BrowserModule,
		AppRoutingModule,
		OpModule,
		UtilModule
	],
	providers: [
		{
			provide: LOCALE_ID,
			deps: [ AppStateService ],		// some service handling global settings
			useFactory: (appState: AppStateService) => appState.localeId // returns locale string
		}
	],
	bootstrap: [AppComponent]
})
export class AppModule { }
