import { SiEntryQualifierSelection } from '../model/si-entry-qualifier-selection';
import { StructurePageManager } from './compact-explorer/structure-page-manager';
import { Observable } from 'rxjs';

export interface CompactExplorerModel {

	getCurrentPageNo$(): Observable<number>;

	// getApiUrl(): string;

	getStructurePageManager(): StructurePageManager;

	// getSiControlBoundry(): SiControlBoundry;

	getSiEntryQualifierSelection(): SiEntryQualifierSelection;

	// areGeneralControlsInitialized(): boolean;

	// applyGeneralControls(controls: SiControl[]): void;
}
