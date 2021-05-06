import { SiEntryQualifier } from 'src/app/si/model/content/si-entry-qualifier';
import { MessageFieldModel } from '../../common/comp/message-field-model';
import { SiFrame } from 'src/app/si/model/meta/si-frame';

export interface QualifierSelectInModel extends MessageFieldModel {

	getSiFrame(): SiFrame;

	getMin(): number;

	getMax(): number|null;

	getPickables(): SiEntryQualifier[]|null;

	getValues(): SiEntryQualifier[];

	setValues(values: SiEntryQualifier[]): void;
}
