import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable} from 'rxjs';
import {map} from 'rxjs/operators';
import {MailItem} from '../bo/mail-item';
import {MailItemFactory} from '../build/mail-item-factory';
import {LogFileData} from '../bo/log-file-data';

@Injectable({
	providedIn: 'root'
})
export class ToolsService {
	constructor(private httpClient: HttpClient) {
	}

	getMailLogFileDatas(): Observable<LogFileData[]> {
		return this.httpClient.get<any>('tools/mail-center/mailslogfiledatas')
			.pipe(map((data) => {
				const logFileDatas = [];
				data.forEach((mailLogItemData) => {
					logFileDatas.push(new LogFileData(mailLogItemData.filename, mailLogItemData.numPages));
				});
				return logFileDatas;
			}));
	}

	getMails(logFileData: LogFileData, pageNum: number): Observable<MailItem[]> {

		let url = 'tools/mail-center/mails/';
		if (!!logFileData) {
			url = url + logFileData.filename + '/' + pageNum
		} else {
			url = url + '/' + pageNum;
		}

		return this.httpClient.get<any>(url)
			.pipe(map((data) => {
				return MailItemFactory.createMailItems(data);
			}));
	}

	clearCache(): Observable<any> {
		return this.httpClient.get<any>('tools/clear-cache').pipe(map((data) => data));
	}
}
