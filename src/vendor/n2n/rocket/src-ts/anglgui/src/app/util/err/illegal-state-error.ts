export class IllegalStateError extends Error {
	constructor(m: string) {
		super(m);

		// Set the prototype explicitly.
		Object.setPrototypeOf(this, IllegalStateError.prototype);
	}

	static assertTrue(cond: boolean, msg: string|null = null): void {
		if (cond === true) {
			return;
		}

		throw new IllegalStateError(msg);
	}
}
