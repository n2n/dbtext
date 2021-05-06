import { Component, OnInit, ElementRef, ViewChild } from '@angular/core';
import { PasswordInModel } from '../password-in-model';

@Component({
	selector: 'rocket-password-in',
	templateUrl: './password-in.component.html'
})
export class PasswordInComponent implements OnInit {
	model: PasswordInModel;
	private pType = 'password';
	private unblocked = false;
	@ViewChild('pwInput', { static: true })
	private inputElemRef: ElementRef;

	public pRawPassword: string|null = null;

	constructor() { }

	ngOnInit(): void {

	}

	get type(): string {
		if (this.blocked) {
			return 'password';
		}

		return this.pType;
	}

	get rawPassword(): string|null {
		if (this.blocked) {
			return 'holeradio';
		}

		return this.pRawPassword;
	}

	set rawPassword(rawPassword: string|null) {
		if (this.blocked) {
			return;
		}
		if (rawPassword === '') {
			rawPassword = null;
		}

		this.pRawPassword = rawPassword;
		this.model.setRawPassword(rawPassword);
	}

	get blocked(): boolean {
		return this.model.isPasswordSet() && !this.unblocked;
	}

	get passwordVisible(): boolean {
		return this.type === 'text';
	}

	changeType(): void {
		if (this.pType === 'password') {
			this.pType = 'text';
			return;
		}

		this.pType = 'password';
	}

	setUnblocked(unblocked: boolean): void {
		this.unblocked = unblocked;
		if (!this.unblocked) {
			this.model.setRawPassword(null);
			return;
		}

		this.inputElemRef.nativeElement.focus();

		this.model.setRawPassword(this.pRawPassword);
	}

	applyGeneratedPassword(): void {
		let passwordLength = 12;
		if (passwordLength < this.model.getMinlength()) {
			passwordLength = this.model.getMinlength();
		}

		if (passwordLength > this.model.getMaxlength()) {
			passwordLength = this.model.getMaxlength();
		}

		this.rawPassword = this.generatePassword(passwordLength);
		this.pType = 'text';
		this.inputElemRef.nativeElement.focus();
	}

	private generatePassword(passwordLength: number): string {
		const passwordChars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz#@!%&()/';
		return Array(passwordLength).fill(passwordChars).map((x) => {
			return x[Math.floor(Math.random() * x.length)];
		}).join('');
	}
}
