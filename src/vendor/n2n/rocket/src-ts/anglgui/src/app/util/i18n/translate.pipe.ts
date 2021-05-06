import { Pipe, PipeTransform } from '@angular/core';
import { TranslationService } from 'src/app/util/i18n/translation.service';

@Pipe({
	name: 'translate'
})
export class TranslatePipe implements PipeTransform {

	constructor(private translate: TranslationService) {
	}

	transform(value: any, args?: any): any {
		return this.translate.translate(value);
	}
}
