import { Component, OnInit } from '@angular/core';
import { UserDaoService } from '../../model/user-dao.service';
import { User } from '../../bo/user';
import { AppStateService } from 'src/app/app-state.service';

@Component({
	selector: 'rocket-users',
	templateUrl: './users.component.html',
	styleUrls: ['./users.component.css']
})
export class UsersComponent implements OnInit {

	users: User[]|null = null;

	constructor(private userDao: UserDaoService, private appState: AppStateService) { }

	ngOnInit() {
		this.users = null;
		this.userDao.getUsers().subscribe(users => {
			this.users = users;
		});
	}

	get currentUser(): User {
		return this.appState.user;
	}

	isEditable(user: User) {
		return user.isEditableBy(this.currentUser);
	}

	isDeletable(user: User) {
		return user.isDeletableBy(this.currentUser);
	}

	delete(user: User) {
		this.userDao.deleteUser(user).subscribe(() => {

		});
	}
}
