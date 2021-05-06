import { Injectable } from '@angular/core';
import { User, UserPower } from '../bo/user';
import { HttpClient } from '@angular/common/http';
import { map } from 'rxjs/operators';
import { Extractor } from 'src/app/util/mapping/extractor';
import { UserFactory } from './user-fatory';
import { Observable } from 'rxjs';
import { ErrorMapFactory } from 'src/app/util/err/error-map';

@Injectable({
	providedIn: 'root'
})
export class UserDaoService {

	constructor(private httpClient: HttpClient) {
	}

	getUsers(): Observable<User[]> {
		return this.httpClient.get<any>('users')
				.pipe(map((data) => {
					const extr = new Extractor(data);
					return UserFactory.createUsers(extr.reqArray('users'));
				}));
	}

	getUserById(userId: number): Observable<User>	{
		return this.httpClient.get<any>('users/user/' + userId)
				.pipe(map((data) => {
					return UserFactory.createUser(data);
				}));
	}

	deleteUser(user: User): Observable<boolean> {
		return this.httpClient.delete<any>('users/user/' + user.id)
				.pipe(map((data) => {
					return data.status === 'OK';
				}));
	}

	createUser(userAddRequest: UserAddRequest) {
		return this.httpClient.post<any>('users/add', userAddRequest)
				.pipe(map((data) => {
					if (data.status === 'ERR') {
						throw ErrorMapFactory.createErrorMap(data.errorMap);
					}

					return data.user;
				}));
	}

	changePassword(changePasswordRequest: ChangePasswordRequest): Observable<User> {
		return this.httpClient.post<any>('users/chpw', changePasswordRequest)
				.pipe(map((data) => {
					if (data.status === 'ERR') {
						throw ErrorMapFactory.createErrorMap(data.errorMap);
					}

					return data.user;
				}));
	}

	saveUser(user: User): Observable<User> {
		return this.httpClient.put<any>('users/user/' + user.id, user)
				.pipe(map((data) => {
					if (data.status === 'ERR') {
						throw ErrorMapFactory.createErrorMap(data.errorMap);
					}

					return data.user;
				}));
	}
}

export interface ChangePasswordRequest {
	password: string;
	passwordConfirmation: string;
	userId: number;
}

export interface UserAddRequest {
	password: string;
	passwordConfirmation: string;
	user: User;
}
