namespace Rocket.Cmd {

	export class Lock {
		constructor(private releaseCallback: (lock: Lock) => any) {
		}
		
		release() {
			this.releaseCallback(this);
		}
	}
}