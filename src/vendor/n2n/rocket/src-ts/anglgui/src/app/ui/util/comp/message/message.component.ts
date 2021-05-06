import { Component, OnInit, Input } from '@angular/core';
import { Message } from 'src/app/util/i18n/message';
import { TranslationService } from 'src/app/util/i18n/translation.service';

@Component({
	selector: 'rocket-ui-message',
	templateUrl: './message.component.html',
	styleUrls: ['./message.component.css']
})
export class MessageComponent implements OnInit {
	text: string;

	constructor(public translationService: TranslationService) { }

	ngOnInit() {
	}

	@Input()
	set message(message: Message) {
		if (message.translated) {
			this.text = message.content;
			return;
		}

		this.text = this.translationService.translate(message.content, message.args);
	}

	get message(): Message {
		return this.message;
	}

}
