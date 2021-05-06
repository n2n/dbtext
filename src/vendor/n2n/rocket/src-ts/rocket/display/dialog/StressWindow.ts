namespace Rocket.Display { 
	export class StressWindow {
		private elemBackgroundJq: JQuery<Element>;
		private elemDialogJq: JQuery<Element>;
		private elemControlsJq: JQuery<Element>;
		private elemMessageJq: JQuery<Element>;
		private elemConfirmJq: JQuery<Element>;
		
		public constructor() {
			this.elemBackgroundJq = $("<div />", {
				"class": "rocket-dialog-background"
			}).css({
				"position": "fixed",
				"height": "100%",
				"width": "100%",
				"top": 0,
				"left": 0,
				"z-index": 998,
				"opacity": 0
			});
			
			this.elemDialogJq = $("<div />").css({
				"position": "fixed",
				"z-index": 999
			});
			
			this.elemMessageJq = $("<p />", {
				"class": "rocket-dialog-message"	
			}).appendTo(<JQuery<HTMLElement>> this.elemDialogJq);
			
			this.elemControlsJq = $("<div/>", {
				"class": "rocket-dialog-controls"
			}).appendTo(<JQuery<HTMLElement>> this.elemDialogJq);
			
		}
		
		public open(dialog: Dialog) {
			var that = this,
				elemBody = $("body"),
				elemWindow = $(window);
			
			this.elemDialogJq.removeClass()
					.addClass("rocket-dialog-" + dialog.serverity + " rocket-dialog");
			
			this.elemMessageJq.empty().text(dialog.msg);
			this.initButtons(dialog);
			elemBody.append(this.elemBackgroundJq).append(this.elemDialogJq);
			
			//Position the dialog 
			this.elemDialogJq.css({
				"left": (elemWindow.width() - this.elemDialogJq.outerWidth(true)) / 2,
				"top": (elemWindow.height() - this.elemDialogJq.outerHeight(true)) / 3
			}).hide();
			
			this.elemBackgroundJq.show().animate({
				opacity: 0.7
			}, 151, function() {
				that.elemDialogJq.show();
			});
			
			elemWindow.on('keydown.dialog', function(event) {
				var keyCode = (window.event) ? event.keyCode : event.which;
				if (keyCode == 13) {
					//Enter
					that.elemConfirmJq.click(); 
					$(window).off('keydown.dialog');
				} else if (keyCode == 27) {
					//Esc
					that.close();
				}   
			});
		}
		
		private initButtons(dialog: Dialog) {
			var that = this;
			this.elemConfirmJq = null;
			this.elemControlsJq.empty();
			
			dialog.buttons.forEach((button: Button) => {
				var elemA = $("<a>", {
					"href": "#"
				}).addClass("btn btn-" + button.type).click((e: any) => {
					e.preventDefault();
					button.callback(e);
					that.close();
				}).text(button.label);
				
				if (that.elemConfirmJq == null) {
					that.elemConfirmJq = elemA;
				} 
				that.elemControlsJq.append(elemA);
				that.elemControlsJq.append(" ");
			});
			
		}
		
		private removeCurrentFocus() {
			//remove focus from all other to ensure that the submit button isn't fired twice
			$("<input/>", {
				"type": "text", 
				"name": "remove-focus"	
			}).appendTo($("body")).focus().remove();
		}
			
		public close() {
			this.elemBackgroundJq.detach();
			this.elemDialogJq.detach();
			$(window).off('keydown.dialog');
		};
	}
}