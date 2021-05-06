import { User, UserPower } from '../bo/user';
import { Extractor } from 'src/app/util/mapping/extractor';

export class UserFactory {

	static createUsers(datas: any[]): User[] {
		const users: User[] = [];
		for (const data of datas) {
			users.push(UserFactory.createUser(data));
		}
		return users;
	}

	static createUser(data: any): User {
		const extr = new Extractor(data);

		const user = new User(extr.reqNumber('id'), extr.reqString('username'), extr.reqString('power') as UserPower);
		user.firstname = extr.nullaString('firstname');
		user.lastname = extr.nullaString('lastname');
		user.email = extr.nullaString('email');

		return user;
	}
}
