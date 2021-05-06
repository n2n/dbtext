namespace Rocket.Impl.Relation {
	
	export class Clipboard {
		private elements: { [key: string]: ClipboardElement } = {};
		private cbr: Jhtml.Util.CallbackRegistry<() => any> = new Jhtml.Util.CallbackRegistry();
		
		clear() {
			if (this.isEmpty()) return;
			
			this.elements = {};
			this.cbr.fire();
		}
		
		add(eiTypeId: string, pid: string, identityString: string) {
			this.elements[this.createKey(eiTypeId, pid)] = new ClipboardElement(eiTypeId, pid, identityString);
			this.cbr.fire();
		}
		
		remove(eiTypeId: string, pid: string) {
			let key = this.createKey(eiTypeId, pid);
			
			if (!this.elements[key]) return;
			
			delete this.elements[key];
			this.cbr.fire();
		}
		
		contains(eiTypeId: string, pid: string): boolean {
			return !!this.elements[this.createKey(eiTypeId, pid)];
		}
		
		private createKey(eiTypeId: string, pid: string): string {
			return eiTypeId + ":" + pid;
		}
		
		onChanged(callback: () => any) {
			this.cbr.on(callback);
		}
		
		offChanged(callback: () => any) {
			this.cbr.off(callback);
		}
		
		toArray(): ClipboardElement[] {
			let elements: ClipboardElement[] = [];
			for (let key in this.elements) {
				elements.push(this.elements[key]);
			}
			return elements;
		}
		
		isEmpty() {
			for (let key in this.elements) return false;
			
			return true;
		}
	}
	
	export class ClipboardElement {
		constructor(public eiTypeId: string, public pid: string, public identityString: string) {
			
		}
	}
}