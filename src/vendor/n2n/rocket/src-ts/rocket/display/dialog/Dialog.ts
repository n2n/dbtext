namespace Rocket.Display { 
	
	export class Dialog {
		private _buttons: Array<Button> = [];
		private severity: Dialog.Severity;
		
		public constructor(public msg: string, severity: Dialog.Severity = "warning") {
			this.msg = msg;
			this.severity = severity;
		}
		
		public addButton(button: Button) {
			this.buttons.push(button);
		}
		
		get serverity(): Dialog.Severity {
			return this.severity;	
		}
		
		get buttons(): Button[] {
			return this._buttons;	
		}
	}
	
	export namespace Dialog {
		export type Severity = "warning"|"danger"|"info"|"success";
	}
	
	export interface Button {
		label: string;
		callback: (e: JQueryEventObject) => void;
		type: "primary"|"secondary";
	}
}