
export class Fresult<E extends Error = Error, T = void> {
	protected value: T|null = null;
	protected error: E|null = null;

	static success<T>(value?: T): Fresult<any, T>	{
		const result = new Fresult<any, T>();
		result.value = value;
		return result;
	}

	static error<E extends Error>(error: E): Fresult<E, any> {
		const result = new Fresult<E, any>();
		result.error = error;
		return result;
	}

	isValid(): boolean {
		return !this.error;
	}

	getValue(): T {
		if (!this.error) {
			return this.value;
		}

		throw new Error('Error result contains no value.');
	}

	getError(): E {
		if (this.error) {
			return this.error;
		}

		throw new Error('Valid result contains no error.');
	}

	throwIfError(): void {
		if (this.error) {
			throw this.error;
		}
	}
}
