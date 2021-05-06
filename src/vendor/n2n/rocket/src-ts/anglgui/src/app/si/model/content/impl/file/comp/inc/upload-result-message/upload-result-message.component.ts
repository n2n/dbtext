import { Component, OnInit, Input } from '@angular/core';
import { MessageFieldModel } from '../../../../common/comp/message-field-model';
import { UploadResult } from '../../image-editor-model';

@Component({
	selector: 'rocket-upload-result-message',
	templateUrl: './upload-result-message.component.html',
	styleUrls: ['./upload-result-message.component.css']
})
export class UploadResultMessageComponent implements OnInit {

	@Input()
	uploadResult: UploadResult|null = null;
	@Input()
	messageFieldModel: MessageFieldModel|null = null;

	constructor() { }

	ngOnInit() {
	}

	get uploadTooLarge(): boolean {
		return this.uploadResult && !!this.uploadResult.uploadTooLarge;
	}

	get uploadErrorMessage(): string|null {
		return (this.uploadResult ? (this.uploadResult.uploadErrorMessage || null) : null);
	}
}
