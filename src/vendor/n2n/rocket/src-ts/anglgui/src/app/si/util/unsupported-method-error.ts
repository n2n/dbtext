export class UnsupportedMethodError extends Error {
	constructor(m: string) {
		super(m);

		// Set the prototype explicitly.
		Object.setPrototypeOf(this, UnsupportedMethodError.prototype);
	}

	static assertTrue(cond: boolean, msg: string|null = null) {
		if (cond === true) {
			return;
		}

		throw new UnsupportedMethodError(msg);
	}
}
