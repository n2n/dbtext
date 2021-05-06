namespace Rocket.Cmd {

	export class Menu {
		private context: Zone;
		private _toolbar: Display.Toolbar = null;
		private _mainCommandList: Display.CommandList = null;
		private _partialCommandList: Display.CommandList = null;
		private _asideCommandList: Display.CommandList = null;
		
		constructor(context: Zone) {
			this.context = context;
		}
		
		clear() {
			this._toolbar = null;
		}
		
		get toolbar(): Display.Toolbar {
			if (this._toolbar) {
				return this._toolbar;
			}
			
			let jqToolbar = this.context.jQuery.find(".rocket-zone-toolbar:first");
			if (jqToolbar.length == 0) {
				jqToolbar = $("<div />", { "class": "rocket-zone-toolbar"}).prependTo(<JQuery<HTMLElement>> this.context.jQuery);
			}
			
			return this._toolbar = new Display.Toolbar(jqToolbar);
		}
		
		private getCommandsJq() {
			var commandsJq = this.context.jQuery.find(".rocket-zone-commands:first");
			if (commandsJq.length == 0) {
				commandsJq = $("<div />", {
					"class": "rocket-zone-commands"
				});
				this.context.jQuery.append(commandsJq);
			}
			
			return commandsJq;
		}
		
		get zoneCommandsJq(): JQuery<Element> {
			return this.getCommandsJq();
		}
		
		get partialCommandList(): Display.CommandList {
			if (this._partialCommandList !== null) {
				return this._partialCommandList;
			}
			
			let mainCommandJq = this.mainCommandList.jQuery;
			var partialCommandsJq = mainCommandJq.children(".rocket-partial-commands:first");
			if (partialCommandsJq.length == 0) {
				partialCommandsJq = $("<div />", {"class": "rocket-partial-commands" }).prependTo(<JQuery<HTMLElement>> mainCommandJq);
			}
			
			return this._partialCommandList = new Display.CommandList(partialCommandsJq);
		}
		
		get mainCommandList(): Display.CommandList {
			if (this._mainCommandList !== null) {
				return this._mainCommandList;
			}
			
			let commandsJq = this.getCommandsJq();
			let mainCommandsJq = commandsJq.children(".rocket-main-commands:first");
			
			if (mainCommandsJq.length == 0) {
				mainCommandsJq = commandsJq.children("div:first");
				mainCommandsJq.addClass("rocket-main-commands");
			}
			
			if (mainCommandsJq.length == 0) {
				let contentsJq = commandsJq.children(":not(.rocket-aside-commands)");
				mainCommandsJq = $("<div></div>", { class: "rocket-main-commands" }).appendTo(<JQuery<HTMLElement>> commandsJq);
				mainCommandsJq.append(contentsJq);
			}
			
			return this._mainCommandList = new Display.CommandList(mainCommandsJq);
		}
		
		get asideCommandList(): Display.CommandList {
			if (this._asideCommandList !== null) {
				return this._asideCommandList;
			}
			
			this.mainCommandList;
			let commandsJq = this.getCommandsJq();
			let asideCommandsJq = commandsJq.children(".rocket-aside-commands:first");
			if (asideCommandsJq.length == 0) {
				asideCommandsJq = $("<div />", {"class": "rocket-aside-commands" }).appendTo(<JQuery<HTMLElement>> commandsJq);
			}
			
			return this._asideCommandList = new Display.CommandList(asideCommandsJq);
		}
	}
}