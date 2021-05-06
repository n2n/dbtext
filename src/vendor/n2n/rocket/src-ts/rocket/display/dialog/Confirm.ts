namespace Rocket.Display {

	export class Confirm {
		dialog: Dialog;
		successCallback: () => any;
		cancelCallback: () => any;
		private stressWindow: StressWindow = null;
		
		constructor(msg: string, okLabel: string, cancelLabel: string, severity: Dialog.Severity) {
			this.dialog = new Dialog(msg, severity);
			this.dialog.addButton({ label: okLabel, type: "primary", callback: () => { 
				this.close();
				if (this.successCallback) {
					this.successCallback();
				}
			}});
			this.dialog.addButton({ label: cancelLabel, type: "secondary", callback: () => { 
				this.close();
				if (this.cancelCallback) {
					this.cancelCallback();
				}
			}});
		}
		
		open() {
			this.stressWindow = new StressWindow();
			this.stressWindow.open(this.dialog);
		}
		
		close() {
			if (!this.stressWindow) return;
			
			this.stressWindow.close();
			this.stressWindow = null;
		}
		
		static test(elemJq: JQuery<Element>, successCallback?: () => any): Confirm|null {
			if (!elemJq.data("rocket-confirm-msg")) return null;
			
			return Confirm.fromElem(elemJq, successCallback);
		}
		
		static fromElem(elemJq: JQuery<Element>, successCallback?: () => any): Confirm {
			let confirm = new Confirm(
					elemJq.data("rocket-confirm-msg") || "Are you sure?",
					elemJq.data("rocket-confirm-ok-label") || "Yes",
					elemJq.data("rocket-confirm-cancel-label") || "No",
					"danger");
			confirm.successCallback = successCallback;
			
			return confirm;
		}
	}
}