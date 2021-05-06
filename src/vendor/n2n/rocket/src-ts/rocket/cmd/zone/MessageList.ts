namespace Rocket.Cmd {
	
	export class MessageList {
		private zone: Zone;
		
		constructor(zone: Zone) {
			this.zone = zone;
		}
		
		clear() {
			this.zone.jQuery.find("rocket-messages").remove();
		}
		
		private severityToClassName(severity: Severity) {
			switch (severity) {
				case Severity.ERROR:
					return "alert-danger";
				case Severity.INFO:
					return "alert-info";
				case Severity.WARN:
					return "alert-warn";
				case Severity.SUCCESS:
					return "alert-success";
				default:
					throw new Error("Unkown severity: " + severity);
			}
		}
		
		private getMessagesUlJqBySeverity(severity: Severity): JQuery<Element> {
			let zoneJq = this.zone.jQuery;
			let className = this.severityToClassName(severity);
			
			let messagesJq = zoneJq.find("ul.rocket-messages." + className);
			if (messagesJq.length > 0) {
				return messagesJq;
			}
			
			messagesJq = $("<ul />", { "class": "rocket-messages alert " + className + " list-unstyled" });
			
			let contentJq = this.zone.jQuery.find(".rocket-content");
			if (contentJq.length > 0) {
				messagesJq.insertBefore(contentJq);
			} else {
				zoneJq.prepend(messagesJq);
			}
			
			return messagesJq;
		}
		
		add(message: Message) {
			let liJq = $("<li></li>", { "text": message.text });
			liJq.hide();
			this.getMessagesUlJqBySeverity(message.severity).append(liJq);
			liJq.fadeIn();
		}
		
		addAll(messages: Message[]) {
			for (let message of messages) {
				this.add(message);
			}
		}
		
	}
	

	export class Message {
		constructor(public text: string, public severity: Severity) {
		}
	}
	
	export enum Severity {
		SUCCESS = 1,
		INFO = 2,
		WARN = 4,
		ERROR = 8,
	}
}