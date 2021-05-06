import { InSiFieldAdapter } from '../../common/model/in-si-field-adapter';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { Message } from 'src/app/util/i18n/message';
import { PasswordInModel } from '../comp/password-in-model';
import { PasswordInComponent } from '../comp/password-in/password-in.component';
import { SiInputResetPoint } from '../../../si-input-reset-point';
import { CallbackInputResetPoint } from '../../common/model/callback-si-input-reset-point';

export class PasswordInSiField extends InSiFieldAdapter implements PasswordInModel {
	public mandatory = false;
	public minlength: number|null = null;
	public maxlength: number|null = null;
	public passwordSet = false;
	public rawPassword: string|null = null;
	public tmpRawPassword: string|null = null;

	constructor(public label: string) {
		super();
		this.validate();
	}

	private validate(): void {
		this.messagesCollection.clear();

		if (this.mandatory && !this.passwordSet && this.rawPassword === null) {
			this.messagesCollection.push(Message.createCode('mandatory_err', new Map([['{field}', this.label]])));
		}

		if (this.minlength && this.rawPassword && this.rawPassword.length < this.minlength) {
			this.messagesCollection.push(Message.createCode('minlength_err',
					new Map([['{field}', this.label], ['{minlength}', this.minlength.toString()]])));
		}

		if (this.maxlength && this.rawPassword && this.rawPassword.length > this.maxlength) {
			this.messagesCollection.push(Message.createCode('maxlength_err',
					new Map([['{field}', this.label], ['{maxlength}', this.maxlength.toString()]])));
		}
	}

	getMaxlength(): number|null {
		return this.maxlength;
	}

	getMinlength(): number|null {
		return this.minlength;
	}

	isPasswordSet(): boolean {
		return this.passwordSet;
	}

	setRawPassword(rawPassword: string): void {
		this.tmpRawPassword = rawPassword;
	}

	onBlur(): void {
		this.rawPassword = this.tmpRawPassword;
		this.validate();
	}

	onFocus(): void {
		this.messagesCollection.clear();
	}

	createUiContent(): UiContent {
		return new TypeUiContent(PasswordInComponent, (ref) => {
			ref.instance.model = this;
		});
	}

	readInput(): object {
		return { rawPassword: this.rawPassword };
	}

	createInputResetPoint(): Promise<SiInputResetPoint> {
		return Promise.resolve(new CallbackInputResetPoint(this.rawPassword, (rawPassword) => {
			this.rawPassword = rawPassword;
		}));
	}
}
