
import { Extractor, ObjectMissmatchError } from 'src/app/util/mapping/extractor';
import { Message, MessageSeverity } from 'src/app/util/i18n/message';
import { SiCallResponse, SiDirective, SiControlResult, SiInputError } from '../manage/si-control-result';
import { SiEntryIdentifier } from '../model/content/si-entry-qualifier';
import { SiModEvent } from '../model/mod/model/si-mod-state.service';
import { Injector } from '@angular/core';
import { SiDeclaration } from '../model/meta/si-declaration';
import { SiBuildTypes } from './si-build-types';
import { SiEssentialsFactory } from './si-field-essentials-factory';

export class SiResultFactory {

	constructor(private injector: Injector) {

	}

	createControlResult(data: any, declaration?: SiDeclaration): SiControlResult {
		const extr = new Extractor(data);

		const inputErrorData = extr.nullaObject('inputError');
		if (inputErrorData) {
			return {
				inputError: this.createInputError(inputErrorData, declaration)
			};
		}

		return {
			callResponse: this.createCallResponse(extr.reqObject('callResponse'))
		};
	}

	createInputError(data: any, declaration: SiDeclaration): SiInputError {
		const inputError = new SiInputError();
		const entryFactory = new SiBuildTypes.SiEntryFactory(declaration, this.injector);
		for (const [eeKey, eeData] of new Extractor(data).reqMap('entries')) {
			inputError.errorEntries.set(eeKey, entryFactory.createEntry(eeData));
		}
		return inputError;
	}

	createCallResponse(data: any): SiCallResponse {
		const extr = new Extractor(data);

		const result = new SiCallResponse();

		result.directive = extr.nullaString('directive') as SiDirective;
		let navPointData: object|null;
		if (navPointData = extr.nullaObject('navPoint')) {
			result.navPoint = SiEssentialsFactory.createNavPoint(navPointData);
		}

		const eventMap = extr.reqMap('eventMap');
		const addedSeis: SiEntryIdentifier[] = [];
		const updatedSeis: SiEntryIdentifier[] = [];
		const removedSeis: SiEntryIdentifier[] = [];

		for (const [typeId, idsEvMapData] of eventMap) {
			const idEvMapExtr = new Extractor(idsEvMapData);

			for (const [id, eventType] of idEvMapExtr.reqStringMap('ids')) {
				switch (eventType) {
					case 'added':
						addedSeis.push(new SiEntryIdentifier(typeId, id));
						break;
					case 'changed':
						updatedSeis.push(new SiEntryIdentifier(typeId, id));
						break;
					case 'removed':
						removedSeis.push(new SiEntryIdentifier(typeId, id));
						break;
					default:
						throw new ObjectMissmatchError('Unknown event type: ' + eventType);
				}
			}
		}

		result.modEvent = new SiModEvent(addedSeis, updatedSeis, removedSeis);

		result.messages = extr.reqArray('messages').map((msgData) => {
			const msgExtr = new Extractor(msgData);
			return Message.createText(msgExtr.reqString('text'), msgExtr.reqString('severity') as MessageSeverity);
		});

		return result;
	}

	// static createEntryError(data: any): SiEntryError {
	// 	const extr = new Extractor(data);

	// 	const entryError = new SiEntryError(/*extr.reqStringArray('messages')*/);

	// 	for (const [key, fieldData] of extr.reqMap('fieldErrors')) {
	// 		entryError.fieldErrors.set(key, SiResultFactory.createFieldError(fieldData));
	// 	}

	// 	return entryError;
	// }

	// private static createFieldError(data: any): SiFieldError {
	// 	const extr = new Extractor(data);

	// 	const fieldError = new SiFieldError(Message.createTexts(extr.reqStringArray('messages')));

	// 	for (const [key, entryData] of extr.reqMap('subEntryErrors')) {
	// 		fieldError.subEntryErrors.set(key, SiResultFactory.createEntryError(entryData));
	// 	}

	// 	return fieldError;
	// }

}
