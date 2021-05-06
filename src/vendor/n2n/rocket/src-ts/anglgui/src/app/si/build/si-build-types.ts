import { SiEntryFactory } from './si-entry-factory';
import { SiGuiFactory } from './si-gui-factory';
import { SiFieldFactory } from './si-field-factory';
import { SiResultFactory } from './si-result-factory';
import { SiControlFactory } from './si-control-factory';
import { SiUiFactory } from './si-ui-factory';

export class SiBuildTypes {
	// static SiUiService: new (...args: any[]) => SiUiService;
	// static SiService: new (...args: any[]) => SiService;
	// static FileInSiField: new (...args: any[]) => FileInSiField;
	// static LinkOutSiField: new (...args: any[]) => LinkOutSiField;
	// static SiNavPoint: new (...args: any[]) => SiNavPoint;
	// static QualifierSelectInSiField: new (...args: any[]) => QualifierSelectInSiField;
	static SiGuiFactory: new (...args: any[]) => SiGuiFactory;
	static SiEntryFactory: new (...args: any[]) => SiEntryFactory;
	static SiFieldFactory: new (...args: any[]) => SiFieldFactory;
	static SiControlFactory: new (...args: any[]) => SiControlFactory;
	static SiResultFactory: new (...args: any[]) => SiResultFactory;
	static SiUiFactory: new (...args: any[]) => SiUiFactory;
}
