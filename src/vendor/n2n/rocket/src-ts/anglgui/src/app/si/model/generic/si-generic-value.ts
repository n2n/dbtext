import { GenericMissmatchError } from './generic-missmatch-error';

export class SiGenericValue {

	constructor(public value: object|string|number|boolean|null) {
	}

	isNull(): boolean {
		return this.value === null;
	}

	isInstanceOf(type: new(...args: any[]) => any): boolean {
		return this.value instanceof type;
	}

	isString(): boolean {
		return typeof this.value === 'string';
	}

	isNumber(): boolean {
		return typeof this.value === 'number';
	}

	isBoolean(): boolean {
		return typeof this.value === 'boolean';
	}

	isStringRepresentable(): boolean {
		switch (typeof this.value) {
			case 'string':
			case 'number':
				return true;
			default:
				return false;
		}

	}

	readString(): string {
		if (this.isStringRepresentable()) {
			return this.value.toString();
		}

		throw new GenericMissmatchError('Value is not stringlike');
	}

	readStringOrNull(): string|null {
		if (this.isNull()) {
			return null;
		}

		return this.readString();
	}

	readNumber(): number {
		if (this.isNumber()) {
			return this.value as number;
		}

		throw new GenericMissmatchError('Value is not a number');
	}

	readNumberOrNull(): number|null {
		if (this.isNull()) {
			return null;
		}

		return this.readNumber();
	}

	readBoolean(): boolean {
		if (this.isBoolean()) {
			return this.value as boolean;
		}

		throw new GenericMissmatchError('Value is not a boolean');
	}

	readBooleanOrNull(): boolean|null {
		if (this.isNull()) {
			return null;
		}

		return this.readBoolean();
	}

	readInstance<T>(type: new(...args: any[]) => T): T {
		if (this.isInstanceOf(type)) {
			return this.value as unknown as T;
		}

		throw new GenericMissmatchError('Value is not instanceof ' + type.name);
	}
}
