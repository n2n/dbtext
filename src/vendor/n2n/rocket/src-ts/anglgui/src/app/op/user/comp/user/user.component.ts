import { Component, OnInit } from '@angular/core';
import { UserDaoService } from '../../model/user-dao.service';
import { ActivatedRoute, Router } from '@angular/router';
import { User, UserPower } from '../../bo/user';
import { TranslationService } from 'src/app/util/i18n/translation.service';
import { AppStateService } from 'src/app/app-state.service';
import { ErrorMap } from 'src/app/util/err/error-map';
import { Message } from 'src/app/util/i18n/message';

@Component({
	selector: 'rocket-user',
	templateUrl: './user.component.html',
	styleUrls: ['./user.component.css']
})
export class UserComponent implements OnInit {
	user: User|null = null;
	saving = false;
	errorMap: ErrorMap|null;

	password: string|null;
	passwordConfirmation: string|null;

	constructor(private userDao: UserDaoService, private route: ActivatedRoute, private translationService: TranslationService,
			private router: Router, private appState: AppStateService) {
	}

	ngOnInit() {
		this.route.params.subscribe((params) => {
			if (!params.userId) {
				this.user = new User(null, '', UserPower.NONE);
				return;
			}

			this.userDao.getUserById(params.userId)
					.subscribe((user: User) => {
						this.user = user;
					}, () => {
						this.cancel();
					});
		});
	}

	get title(): string {
		return this.user && this.user.username	? this.user.username :
				(this.user.id ? this.translationService.translate('edit_user_txt') : this.translationService.translate('add_user_txt'));
	}

	save() {
		if (this.saving) {
			return;
		}

		this.saving = true;
		this.errorMap = null;

		if (this.user.isNew()) {
			this.userDao
					.createUser({
						password: this.password,
						passwordConfirmation: this.passwordConfirmation,
						user: this.user
					})
					.subscribe(() => {
						this.router.navigate(['users']);
					}, (errorMap: ErrorMap) => {
						this.saving = false;
						this.errorMap = errorMap;
					});
			return;
		}

		this.userDao.saveUser(this.user)
				.subscribe(() => {
					this.router.navigate(['users']);
				}, (errorMap: ErrorMap) => {
					this.saving = false;
					this.errorMap = errorMap;
				});
	}

	cancel() {
		if (this.saving) {
			return;
		}

		this.router.navigate(['users']);
	}

	getErrorMessages(propertyName: string): Message[] {
		if (!this.errorMap || !this.errorMap.properties || !this.errorMap.properties.has(propertyName)) {
			return null;
		}

		return Message.createTexts(this.errorMap.properties.get(propertyName).messages);
	}

	get availablePowers(): UserPower[] {
		const userPowers: UserPower[] = [];
		switch (this.appState.user.power) {
			case UserPower.SUPER_ADMIN:
				userPowers.push(UserPower.SUPER_ADMIN);
			case UserPower.ADMIN:
				userPowers.push(UserPower.ADMIN);
			case UserPower.NONE:
				userPowers.push(UserPower.NONE);
		}
		return userPowers;
	}

}
