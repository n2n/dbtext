import { SiEntryQualifier } from '../../../content/si-entry-qualifier';

export interface SiEntryQualifierSelection {
	min: number;
	max: number|null;
	selectedQualfiers: SiEntryQualifier[];
}
