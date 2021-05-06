import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { UserPower, User } from '../../bo/user';
import { UserDaoService } from '../../model/user-dao.service';
import { ErrorMap } from 'src/app/util/err/error-map';
import { Message } from 'src/app/util/i18n/message';

@Component({
	selector: 'rocket-ch-pw',
	templateUrl: './ch-pw.component.html',
	styleUrls: ['./ch-pw.component.css']
})
export class ChPwComponent implements OnInit {
	user: User|null = null;
	saving = false;
	errorMap: ErrorMap|null;

	password: string|null;
	passwordConfirmation: string|null;

	constructor(private route: ActivatedRoute, private router: Router, private userDao: UserDaoService) { }

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

	save() {
		if (this.saving) {
			return;
		}

		this.saving = true;
		this.errorMap = null;

		this.userDao
				.changePassword({
					password: this.password,
					passwordConfirmation: this.passwordConfirmation,
					userId: this.user.id
				})
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
}
