import { Injectable } from '@angular/core';
import { User } from './op/user/bo/user';

@Injectable({
	providedIn: 'root'
})
export class AppStateService {

	localeId = 'de-CH';
	user: User;
	pageName: string;
	assetsUrl: string;

	constructor() { }
}
