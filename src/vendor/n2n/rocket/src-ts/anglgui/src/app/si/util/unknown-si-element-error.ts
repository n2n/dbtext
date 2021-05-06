export class UnknownSiElementError extends Error {
	constructor(m: string) {
		super(m);

		// Set the prototype explicitly.
		Object.setPrototypeOf(this, UnknownSiElementError.prototype);
	}

	static assertTrue(cond: boolean, msg: string|null = null) {
		if (cond === true) {
			return;
		}

		throw new UnknownSiElementError(msg);
	}
}
