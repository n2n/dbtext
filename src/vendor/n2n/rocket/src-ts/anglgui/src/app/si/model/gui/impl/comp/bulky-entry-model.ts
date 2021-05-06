import { SiEntry } from '../../../content/si-entry';
import { StructureBranchModel } from 'src/app/ui/structure/comp/structure-branch-model';

export interface BulkyEntryModel {

	getSiEntry(): SiEntry;

	getContentStructureBranchModel(): StructureBranchModel;
}
