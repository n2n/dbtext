export class IllegalSiStateError extends Error {
	constructor(m: string) {
		super(m);

		// Set the prototype explicitly.
		Object.setPrototypeOf(this, IllegalSiStateError.prototype);
	}

	static assertTrue(cond: boolean, msg: string|null = null) {
		if (cond === true) {
			return;
		}

		throw new IllegalSiStateError(msg);
	}
}
