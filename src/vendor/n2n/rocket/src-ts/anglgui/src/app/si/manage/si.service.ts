import { Injectable, Injector } from '@angular/core';
import { Observable } from 'rxjs';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';
import { map } from 'rxjs/operators';
import { HttpParams, HttpClient } from '@angular/common/http';
import { SiInput } from '../model/input/si-input';
import { SiCallResponse, SiControlResult } from './si-control-result';
import { IllegalSiStateError } from '../util/illegal-si-state-error';
import { SiGetRequest } from '../model/api/si-get-request';
import { SiGetResponse } from '../model/api/si-get-response';
import { SiApiFactory } from '../build/si-api-factory';
import { SiValRequest } from '../model/api/si-val-request';
import { SiValResponse } from '../model/api/si-val-response';
import { Extractor } from 'src/app/util/mapping/extractor';
import { SiSortRequest } from '../model/api/si-sort-request';
import { SiModStateService } from '../model/mod/model/si-mod-state.service';
import { SiFrame, SiFrameApiSection } from '../model/meta/si-frame';
import { SiStyle } from '../model/meta/si-view-mode';
import { SiBuildTypes } from '../build/si-build-types';

@Injectable({
	providedIn: 'root'
})
export class SiService {

	constructor(private httpClient: HttpClient, private modState: SiModStateService, private injector: Injector) {
	}

	lookupZone(uiZone: UiZone): Promise<void> {
		return this.httpClient.get<any>(uiZone.url)
				.pipe(map((data: any) => {
					new SiBuildTypes.SiUiFactory(this.injector).fillZone(data, uiZone);
				}))
				.toPromise();
	}

// 	entryControlCall(apiUrl: string, callId: object, entryId: string, entryInputs: SiEntryInput[]): Observable<any> {
// 		const formData = new FormData();
// 		formData.append('callId', JSON.stringify(callId));
// 		formData.append('siEntryId', entryId);
// // 		formData.append('inputMap', JSON.stringify(entryInput));

// 		const params = new HttpParams();

// 		const options = {
// 			params,
// 			reportProgress: true
// 		};

// 		return this.httpClient.post<any>(apiUrl + '/execEntryControl', formData, options)
// 				.pipe(map(data => {
// 					if (data.errors) {
// 						throw data.errors;
// 					}

// 					return data.expert;
// 				}));
// 	}

	selectionControlCall(): Observable<any> {
		throw new Error('not yet implemented');
	}

	controlCall(apiUrl: string|SiFrame, style: SiStyle, apiCallId: object, input: SiInput|null): Observable<SiControlResult> {
		if (apiUrl instanceof SiFrame) {
			apiUrl = apiUrl.getApiUrl(SiFrameApiSection.CONTROL);
		}

		const formData = new FormData();
		formData.append('style', JSON.stringify(style));
		formData.append('apiCallId', JSON.stringify(apiCallId));

		if (input) {
			for (const [name, param] of input.toParamMap()) {
				formData.append(name, param);
			}
		}

		const params = new HttpParams();

		const options = {
			params,
			reportProgress: true
		};

		return this.httpClient.post<any>(apiUrl, formData, options)
				.pipe(map(data => {
					const resultFactory = new SiBuildTypes.SiResultFactory(this.injector);
					const result = resultFactory.createControlResult(data, input?.declaration);
					if (result.callResponse) {
						this.handleCallresponse(result.callResponse);
					}
					return result;
				}));
	}

	fieldCall(apiUrl: string|SiFrame, style: SiStyle, apiCallId: object, data: object, uploadMap: Map<string, Blob>): Observable<any> {
		if (apiUrl instanceof SiFrame) {
			apiUrl = apiUrl.getApiUrl(SiFrameApiSection.CONTROL);
		}

		const formData = new FormData();
		formData.append('style', JSON.stringify(style));
		formData.append('apiCallId', JSON.stringify(apiCallId));
		formData.append('data', JSON.stringify(data));

		for (const [name, param] of uploadMap) {
			if (formData.has(name)) {
				throw new IllegalSiStateError('Error illegal paramName ' + name);
			}

			formData.append(name, param);
		}

		const httpParams = new HttpParams();

		const options = {
			httpParams,
			reportProgress: true
		};

		return this.httpClient.post<any>(apiUrl, formData, options)
		 		.pipe(map(responseData => {
					return new Extractor(responseData).nullaObject('data');
				}));
	}

	apiGet(apiUrl: string|SiFrame, getRequest: SiGetRequest): Observable<SiGetResponse> {
		if (apiUrl instanceof SiFrame) {
			apiUrl = apiUrl.getApiUrl(SiFrameApiSection.GET);
		}

		return this.httpClient
				.post<any>(apiUrl, getRequest)
				.pipe(map(data => {
					return new SiApiFactory(this.injector).createGetResponse(data, getRequest);
				}));
	}

	apiVal(apiUrl: string|SiFrame, valRequest: SiValRequest): Observable<SiValResponse> {
		if (apiUrl instanceof SiFrame) {
			apiUrl = apiUrl.getApiUrl(SiFrameApiSection.VAL);
		}

		return this.httpClient
				.post<any>(apiUrl, valRequest)
				.pipe(map(data => {
					return new SiApiFactory(this.injector).createValResponse(data, valRequest);
				}));
	}

	apiSort(apiUrl: string|SiFrame, sortRequest: SiSortRequest): Observable<SiCallResponse> {
		if (apiUrl instanceof SiFrame) {
			apiUrl = apiUrl.getApiUrl(SiFrameApiSection.SORT);
		}

		const resultFactory = new SiBuildTypes.SiResultFactory(this.injector);
		return this.httpClient
				.post(apiUrl, sortRequest)
				.pipe(map(data => {
					const callResponse = resultFactory.createCallResponse(data);
					this.handleCallresponse(callResponse);
					return callResponse;
				}));
	}

	private handleCallresponse(result: SiCallResponse): void {
		this.modState.pushModEvent(result.modEvent);
		this.modState.pushMessages(result.messages);
	}
}
