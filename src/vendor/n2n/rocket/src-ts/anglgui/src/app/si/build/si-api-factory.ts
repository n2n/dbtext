
import { SiGetResponse } from 'src/app/si/model/api/si-get-response';
import { SiGetResult } from 'src/app/si/model/api/si-get-result';
import { SiGetRequest } from 'src/app/si/model/api/si-get-request';
import { SiValRequest } from '../model/api/si-val-request';
import { SiValResponse } from '../model/api/si-val-response';
import { SiValInstruction } from '../model/api/si-val-instruction';
import { SiValResult } from '../model/api/si-val-result';
import { SiValGetResult } from '../model/api/si-val-get-result';
import { SiMetaFactory } from './si-meta-factory';
import { SiDeclaration } from '../model/meta/si-declaration';
import { Extractor } from 'src/app/util/mapping/extractor';
import { Injector } from '@angular/core';
import { SiControlBoundry } from '../model/control/si-control-bountry';
import { SimpleSiControlBoundry } from '../model//control/impl/model/simple-si-control-boundry';
import { SiBuildTypes } from './si-build-types';

export class SiApiFactory {

	constructor(private injector: Injector) {
	}

	createGetResponse(data: any, request: SiGetRequest): SiGetResponse {
		const extr = new Extractor(data);

		const response = new SiGetResponse();

		const resultsData = extr.reqArray('results');
		for (const key of request.instructions.keys()) {
			if (!resultsData[key]) {
				throw new Error('No result for key: ' + key);
			}

			response.results[key] = this.createGetResult(resultsData[key], request.instructions[key].getDeclaration(),
					request.instructions[key].getGeneralControlsBoundry());
		}

		return response;
	}

	private createGetResult(data: any, declaration: SiDeclaration|null, controlBoundry: SiControlBoundry|null): SiGetResult {
		const extr = new Extractor(data);

		const result: SiGetResult = {
			declaration: null,
			generalControls: null,
			entry: null,
			partialContent: null
		};

		if (!declaration) {
			declaration = result.declaration = SiMetaFactory.createDeclaration(extr.reqObject('declaration'));
		}

		let controlsData: any = null;
		if (null !== (controlsData = extr.nullaArray('generalControls'))) {
			const compEssentialsFactory = new SiBuildTypes.SiControlFactory(controlBoundry || new SimpleSiControlBoundry([], declaration), this.injector);
			result.generalControls = compEssentialsFactory.createControls(controlsData);
		}

		let propData: any = null;
		if (null !== (propData = extr.nullaObject('entry'))) {
			result.entry = new SiBuildTypes.SiEntryFactory(declaration, this.injector).createEntry(propData);
		}

		if (null !== (propData = extr.nullaObject('partialContent'))) {
			result.partialContent = new SiBuildTypes.SiEntryFactory(declaration, this.injector)
					.createPartialContent(propData);
		}

		return result;
	}

	createValResponse(data: any, request: SiValRequest): SiValResponse {
		const extr = new Extractor(data);

		const response = new SiValResponse();

		const resultsData = extr.reqArray('results');
		request.instructions.forEach((_value, key) => {
			if (!resultsData[key]) {
				throw new Error('No result for key: ' + key);
			}

			response.results[key] = this.createValResult(resultsData[key], request.instructions[key]);
		});

		return response;
	}

	private createValResult(data: any, instruction: SiValInstruction): SiValResult {
		const extr = new Extractor(data);

		const result = new SiValResult(extr.reqBoolean('valid'));

		const resultsData = extr.reqArray('getResults');
		for (const key of instruction.getInstructions.keys()) {
			if (!resultsData[key]) {
				throw new Error('No result for key: ' + key);
			}

			const getInstruction = instruction.getInstructions[key];
			result.getResults[key] = this.createValGetResult(resultsData[key], getInstruction.getDeclaration());
		}

		return result;
	}

	private createValGetResult(data: any, declaration: SiDeclaration|null): SiValGetResult {
		const extr = new Extractor(data);

		const result: SiValGetResult = {
			declaration: null,
			entry: null
		};

		let propData: any = null;

		if (!declaration) {
			declaration = result.declaration = SiMetaFactory.createDeclaration(extr.reqObject('declaration'));
		}

		if (null !== (propData = extr.nullaObject('entry'))) {
			result.entry = new SiBuildTypes.SiEntryFactory(declaration, this.injector).createEntry(propData);
		}

		return result;
	}
}
