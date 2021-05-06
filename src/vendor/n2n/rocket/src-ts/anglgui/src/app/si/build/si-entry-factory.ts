import { SiDeclaration } from '../model/meta/si-declaration';
import { SiPartialContent } from '../model/content/si-partial-content';
import { SiEntry } from '../model/content/si-entry';
import { SiEntryIdentifier, SiEntryQualifier } from '../model/content/si-entry-qualifier';
import { SiEntryBuildup } from '../model/content/si-entry-buildup';
import { Extractor } from 'src/app/util/mapping/extractor';
import { SiControlFactory } from './si-control-factory';
import { Injector } from '@angular/core';
import { SiControlBoundry } from '../model/control/si-control-bountry';
import { SimpleSiControlBoundry } from '../model/control/impl/model/simple-si-control-boundry';
import { SiMetaFactory } from './si-meta-factory';
import { SiBuildTypes } from './si-build-types';

export class SiEntryFactory {
	constructor(private declaration: SiDeclaration, private injector: Injector) {
	}

	createPartialContent(data: any): SiPartialContent {
		const extr = new Extractor(data);
		return {
			entries: this.createEntries(extr.reqArray('entries')),
			count: extr.reqNumber('count'),
			offset: extr.reqNumber('offset')
		};
	}

	createEntries(data: Array<any>): SiEntry[] {
		const entries: Array<SiEntry> = [];
		for (const entryData of data) {
			entries.push(this.createEntry(entryData));
		}

		return entries;
	}

	createEntry(entryData: any): SiEntry {
		const extr = new Extractor(entryData);

		const siEntry = new SiEntry(SiMetaFactory.createEntryIdentifier(extr.reqObject('identifier')),
				SiMetaFactory.createStyle(extr.reqObject('style')));
		siEntry.treeLevel = extr.nullaNumber('treeLevel');

		const controlBoundry = new SimpleSiControlBoundry([siEntry], this.declaration);
		for (const [, buildupData] of extr.reqMap('buildups')) {
			siEntry.addEntryBuildup(this.createEntryBuildup(buildupData, siEntry.identifier, controlBoundry));
		}

		siEntry.selectedEntryBuildupId = extr.nullaString('selectedTypeId');

		return siEntry;
	}

	private createEntryBuildup(data: any, identifier: SiEntryIdentifier, controlBoundry: SiControlBoundry): SiEntryBuildup {
		const extr = new Extractor(data);

		const maskDeclaration = this.declaration.getTypeDeclarationByTypeId(extr.reqString('typeId'));
		const entryQualifier = new SiEntryQualifier(maskDeclaration.type.qualifier, identifier, extr.nullaString('idName'));

		const entryBuildup = new SiEntryBuildup(entryQualifier);
		entryBuildup.fieldMap = new SiBuildTypes.SiFieldFactory(controlBoundry, maskDeclaration.type, this.injector)
				.createFieldMap(extr.reqMap('fieldMap'));
		entryBuildup.controls = new SiControlFactory(controlBoundry, this.injector)
				.createControls(extr.reqArray('controls'));

		return entryBuildup;
	}
}
