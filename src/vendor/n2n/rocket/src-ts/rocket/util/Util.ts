namespace Rocket.Util {
	
	export class CallbackRegistry<C extends Function> {
		private callbackMap: { [nature: string]: Array<C>} = {};  
		
		public register(nature: string, callback: C) {
			if (this.callbackMap[nature] === undefined) {
				this.callbackMap[nature] = new Array<C>();
			}
			
			this.callbackMap[nature].push(callback);
		}
		
		public unregister(nature: string, callback: C) {
			if (this.callbackMap[nature] === undefined) {
				return;
			}
			
			for (let i in this.callbackMap[nature]) {
				if (this.callbackMap[nature][i] === callback) {
					this.callbackMap[nature].splice(parseInt(i), 1);
					return;
				}
			}
		}
		
		public filter(nature: string): Array<C> {
			if (this.callbackMap[nature] === undefined) {
				return new Array<C>();
			}
			
			return this.callbackMap[nature];
		}
		
		clear(nature?: string) {
			if (nature) {
				this.callbackMap[nature] = [];
				return;
			}
			
			this.callbackMap = {};
		}
	}
	
	export class ArgUtils {
		static valIsset(arg: any) {
			if (arg !== null && arg !== undefined) return;
			
			throw new InvalidArgumentError("Invalid arg: " + arg);
		}
	}
	
	export class ElementUtils {
		static isControl(elem: Element) {
			return !!Jhtml.Util.closest(elem, "a, button, input, textarea, select", true);
//			
//			switch (elem.tagName) {
//				case 'A':
//				case 'BUTTON':
//				case 'INPUT':
//				case 'TEXTAREA':
//				case 'SELECT':
//					return true;
//				default:
//					return false;
//			}
		}
	}
		
	export class InvalidArgumentError extends Error {
		
//		constructor (message: string) {
//			super(message);
//			Object.setPrototypeOf(this, InvalidArgumentError.prototype);
//		}
	}
	
	export class IllegalStateError extends Error /*implements ExceptionInformation*/ {
//		constructor (public message: string) {
//			super(message);
//			
//			this.name = 'MyError';
//			this.message = message || 'Default Message';
//			this.stack = (new Error()).stack;
//		}
		
		static assertTrue(arg: any, errMsg: string = null) {
			if (arg === true) return;
			
			if (errMsg === null) {
				errMsg = "Illegal state";
			}
			
			throw new Error(errMsg);
		}
	}
	
	export function escSelector(str: string): string {
		return str.replace(/([ #;?%&,.+*~\':"!^$[\]()=>|\/@])/g,"\\$1"); 
	}
}