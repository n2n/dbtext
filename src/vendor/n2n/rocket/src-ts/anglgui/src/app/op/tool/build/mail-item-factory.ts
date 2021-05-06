import {Extractor} from '../../../util/mapping/extractor';
import {MailItem} from '../bo/mail-item';
import {MailItemAttachmentFactory} from './mail-item-attachment-factory';

export class MailItemFactory {

	static createMailItems(datas: any[]): MailItem[] {
		const mailItems: MailItem[] = [];
		for (const data of datas) {
			mailItems.push(MailItemFactory.createMailItem(data));
		}
		return mailItems;
	}

	static createMailItem(data: any): MailItem {
		const mailItem = new MailItem();

		const extr = new Extractor(data);
		mailItem.dateTime = extr.nullaString('dateTime');
		mailItem.to = extr.nullaString('to');
		mailItem.from = extr.nullaString('from');
		mailItem.cc = extr.nullaString('cc');
		mailItem.bcc = extr.nullaString('bcc');
		mailItem.replyTo = extr.nullaString('replyTo');
		mailItem.attachments = MailItemAttachmentFactory.createMailAttachments(data.attachments);
		mailItem.message = extr.nullaString('message');
		mailItem.subject = extr.nullaString('subject');

		return mailItem;
	}
}
