import { SiMask } from '../model/meta/si-type';
import { SiStructureDeclaration } from '../model/meta/si-structure-declaration';
import { Extractor } from 'src/app/util/mapping/extractor';


export class SiTypeEssentialsFactory {

	constructor(private type: SiMask) {
	}

	createStructureDeclarations(data: Array<any>): SiStructureDeclaration[] {
		const declarations: Array<SiStructureDeclaration> = [];
		for (const declarationData of data) {
			declarations.push(this.createStructureDeclaration(declarationData));
		}
		return declarations;
	}

	createStructureDeclaration(data: any): SiStructureDeclaration {
		const extr = new Extractor(data);

		const propId = extr.nullaString('propId');
		const type = extr.nullaString('structureType') as any;
		const children = this.createStructureDeclarations(extr.reqArray('children'));

		if (propId !== null) {
			return new SiStructureDeclaration(this.type.getPropById(propId), null, type, children);
		}

		return new SiStructureDeclaration(null, extr.nullaString('label'), type, children);
	}
}
