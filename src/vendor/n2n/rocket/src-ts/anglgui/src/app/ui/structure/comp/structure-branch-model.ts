import { Observable } from 'rxjs';
import { UiStructure } from '../model/ui-structure';

export interface StructureBranchModel {
	getStructures$(): Observable<UiStructure[]>;
}
