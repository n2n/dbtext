import { SiInputResetPoint } from '../../../si-input-reset-point';

export class CallbackInputResetPoint<T> implements SiInputResetPoint {

	constructor(private value: T, private rollbackToCallback: (value: T) => void|Promise<void>) {
	}

	rollbackTo(): Promise<void> {
		return this.rollbackToCallback(this.value) ||  Promise.resolve();
	}
}
