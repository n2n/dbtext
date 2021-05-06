import { Observable, BehaviorSubject } from 'rxjs';
import { UiStructure } from '../ui-structure';
import { UiStructureModelMode } from '../ui-structure-model';
import { SimpleUiStructureModel } from './simple-si-structure-model';
import { TypeUiContent } from './type-si-content';
import { StructureBranchComponent } from '../../comp/structure-branch/structure-branch.component';

export class BranchUiStructureModel extends SimpleUiStructureModel implements BranchUiStructureModel {
	public mode = UiStructureModelMode.NONE;
	private uiStructuresSubject = new BehaviorSubject<UiStructure[]>([]);

	constructor(uiStructures: UiStructure[] = []) {
		super(new TypeUiContent(StructureBranchComponent, (ref) => {
			ref.instance.model = this;
		}));

		this.uiStructuresSubject.next(uiStructures);
	}

	set uiStructures(uiStructures: UiStructure[]) {
		this.uiStructuresSubject.next(uiStructures);
	}

	get uiStructures() {
		return this.uiStructuresSubject.getValue();
	}

	pushUiStructure(uiStructure: UiStructure) {
		this.uiStructuresSubject.next([...this.uiStructures, uiStructure]);
	}

	getStructures$(): Observable<UiStructure[]> {
		return this.uiStructuresSubject.asObservable();
	}
}
