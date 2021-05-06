export class SwapResult<T> {
	constructor(readonly newValues: T[], readonly oldValues: T[]) {
	}
}

export class ArrayState<T> {

	constructor(private values: Array<T>) {
	}

	swap(values: Array<T>): SwapResult<T> {
		const newValues = values.filter(value => -1 === this.values.indexOf(value));
		const oldValues = this.values.filter(value => -1 === values.indexOf(value));
		this.values = values;
		return new SwapResult<T>(newValues, oldValues);
	}
}
