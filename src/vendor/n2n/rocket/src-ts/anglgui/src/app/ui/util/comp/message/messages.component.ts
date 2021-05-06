import {Component, Input, IterableDiffer, IterableDiffers, OnInit} from '@angular/core';
import {Message} from "../../../../util/i18n/message";

@Component({
	selector: 'rocket-ui-messages',
	templateUrl: './messages.component.html',
	styleUrls: ['./messages.component.css']
})
export class MessagesComponent implements OnInit {

	@Input()
	public messages: Message[] = [];
	private iterableDiffer: IterableDiffer<Message>;

	constructor(private iterableDiffers: IterableDiffers) {
	this.iterableDiffer = iterableDiffers.find([]).create(null);
	}

	ngOnInit(): void {}

	private addMessageRemovalTimeout(message: Message) {
	if (message.durationMs !== null) {
		setTimeout(() => this.messages.splice(this.messages.indexOf(message), 1), message.durationMs);
	}
	}

	ngDoCheck() {
	let changes = this.iterableDiffer.diff(this.messages);
	if (changes) {
		changes.forEachAddedItem((change) => this.addMessageRemovalTimeout(change.item))
	}
	}
}
