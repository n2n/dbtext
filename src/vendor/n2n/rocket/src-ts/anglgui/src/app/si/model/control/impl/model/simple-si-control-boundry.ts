import { SiControlBoundry } from '../../si-control-bountry';
import { SiEntry } from '../../../content/si-entry';
import { SiDeclaration } from '../../../meta/si-declaration';

export class SimpleSiControlBoundry implements SiControlBoundry {

	constructor(public entries: SiEntry[], public declaration: SiDeclaration) {
	}

	getBoundEntries(): SiEntry[] {
		return this.entries;
	}

	getBoundDeclaration(): SiDeclaration {
		return this.declaration;
	}
}
