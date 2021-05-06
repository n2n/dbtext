export class GenericMissmatchError extends Error {

	static assertTrue(arg: any, errMessage: string|null = null) {
		if (arg !== true) {
			throw new GenericMissmatchError(errMessage);
		}
	}
}
