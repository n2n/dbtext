import { SiEntry } from '../content/si-entry';
import { SiDeclaration } from '../meta/si-declaration';

export interface SiControlBoundry {

	getBoundEntries(): SiEntry[];

	getBoundDeclaration(): SiDeclaration;
}
