namespace Rocket.Display {
	export class NavItem {
		private _htmlElement: HTMLElement

		public constructor(htmlElement: HTMLElement) {
			this._htmlElement = htmlElement
		}

		get htmlElement(): HTMLElement {
			return this._htmlElement;
		}

		set htmlElement(value: HTMLElement) {
			this._htmlElement = value;
		}
	}
}