import { SiEntry } from 'src/app/si/model/content/si-entry';
import { SiDeclaration } from '../meta/si-declaration';

export interface SiValGetResult {

	declaration: SiDeclaration|null;

	entry: SiEntry|null;
}
