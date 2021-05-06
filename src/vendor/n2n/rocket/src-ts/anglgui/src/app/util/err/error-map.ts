import { Extractor } from '../mapping/extractor';

export interface ErrorMap {
	properties: Map<string, ErrorMap>;
	messages: Array<string>;
}

export class ErrorMapFactory {

	static createErrorMap(data: any): ErrorMap {
		const extr = new Extractor(data);

		return {
			properties: extr.contains('properties') ? ErrorMapFactory.createErrorMapsMap(extr.reqMap('properties')) : undefined,
			messages: extr.contains('messages') ? extr.reqStringArray('messages') : undefined
		};
	}

	static createErrorMapsMap(dataMap: Map<string, any>): Map<string, ErrorMap> {
		const errorMapsMap = new Map<string, ErrorMap>();
		for (const [key, data] of dataMap) {
			errorMapsMap.set(key, ErrorMapFactory.createErrorMap(data));
		}
		return errorMapsMap;
	}
}
