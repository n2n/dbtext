import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TranslatePipe } from './i18n/translate.pipe';

@NgModule({
	declarations: [TranslatePipe],
	imports: [
		CommonModule
	],
	exports: [
		TranslatePipe
	]
})
export class UtilModule { }
