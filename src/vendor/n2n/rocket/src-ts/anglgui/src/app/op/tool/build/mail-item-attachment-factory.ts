import {Extractor} from '../../../util/mapping/extractor';
import {MailItemAttachment} from '../bo/mail-item-attachment';

export class MailItemAttachmentFactory {
	static createMailAttachments(datas: any[]): MailItemAttachment[] {
		const attachments: MailItemAttachment[] = [];
		for (const data of datas) {
			attachments.push(MailItemAttachmentFactory.createMailItemAttachment(data));
		}
		return attachments;
	}

	static createMailItemAttachment(data: any): MailItemAttachment {
		const mailItemAttachment = new MailItemAttachment();

		const extr = new Extractor(data);
		mailItemAttachment.name = extr.nullaString('name');
		mailItemAttachment.path = extr.nullaString('path');

		return mailItemAttachment;
	}
}
