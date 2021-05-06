export class IllegalArgumentError extends Error {
	constructor(m: string) {
		super(m);

		// Set the prototype explicitly.
		Object.setPrototypeOf(this, IllegalArgumentError.prototype);
	}

	static assertTrue(cond: boolean, msg: string|null = null) {
		if (cond === true) {
			return;
		}

		throw new IllegalArgumentError(msg);
	}
}
