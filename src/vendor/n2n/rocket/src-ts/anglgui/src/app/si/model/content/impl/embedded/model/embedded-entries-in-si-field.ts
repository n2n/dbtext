
import { SiEmbeddedEntry } from './si-embedded-entry';
import { SiService } from 'src/app/si/manage/si.service';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { EmbeddedEntryObtainer } from './embedded-entry-obtainer';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { EmbeddedEntriesInUiStructureModel } from './embedded-entries-in-ui-structure-model';
import { TranslationService } from 'src/app/util/i18n/translation.service';
import { SiFieldAdapter } from '../../common/model/si-field-adapter';
import { Message } from 'src/app/util/i18n/message';
import { SiFrame } from 'src/app/si/model/meta/si-frame';
import { SiModStateService } from 'src/app/si/model/mod/model/si-mod-state.service';
import { EmbeddedEntriesInConfig } from './embe/embedded-entries-config';
import { EmbeInSource, EmbeInCollection } from './embe/embe-collection';
import { GenericEmbeddedEntryManager } from './generic/generic-embedded-entry-manager';
import { SiInputResetPoint } from '../../../si-input-reset-point';

export class EmbeddedEntriesInSiField extends SiFieldAdapter implements EmbeInSource {

	config: EmbeddedEntriesInConfig = {
		min: 0,
		max: null,
		reduced: false,
		nonNewRemovable: true,
		sortable: false,
		allowedTypeIds: null
	};

	constructor(private label: string, private siService: SiService, private siModState: SiModStateService,
			private frame: SiFrame, private translationService: TranslationService, private values: SiEmbeddedEntry[] = []) {
		super();
	}

	setValues(values: SiEmbeddedEntry[]): void {
		this.values = values;
		this.validate();
	}

	getValues(): SiEmbeddedEntry[] {
		return this.values;
	}

	private validate(): void {
		this.messagesCollection.clear();

//		const values = this.getTypeSelectedValues();

//		if (this.values.length < this.config.min) {
//			this.messagesCollection.push(Message.createCode('min_elements_err',
//					new Map([['{field}', this.label], ['{min}', this.config.min.toString()]])));
//		}

		if (this.config.max !== null && this.values.length > this.config.max) {
			this.messagesCollection.push(Message.createCode('max_elements_err',
					new Map([['{field}', this.label], ['{max}', this.config.max.toString()]])));
		}
	}

	hasInput(): boolean {
		return true;
	}

	private getTypeSelectedValues(): SiEmbeddedEntry[] {
		return this.values.filter(ee => ee.entry.selectedEntryBuildupId);
	}

	readInput(): object {
		return { entryInputs: this.getTypeSelectedValues().map(embeddedEntry => embeddedEntry.entry.readInput() ) };
	}

	createUiStructureModel(): UiStructureModel {
		const embeInCol = new EmbeInCollection(this, this.config);
		embeInCol.readEmbes();

		return new EmbeddedEntriesInUiStructureModel(this.label,
				new EmbeddedEntryObtainer(this.siService, this.siModState, this.frame, this.config.reduced, this.config.allowedTypeIds),
				this.frame, embeInCol, this.config, this.translationService,
				this.getDisabled$());
	}

	// copy(): SiField {
	// 	throw new Error('not yet implemented');
	// }

	private createGenericManager(): GenericEmbeddedEntryManager {
		return new GenericEmbeddedEntryManager(this.values, this.siService, this.siModState, this.frame, this,
				this.config.reduced, this.config.allowedTypeIds);
	}

	copyValue(): Promise<SiGenericValue> {
		return this.createGenericManager().copyValue();
	}

	pasteValue(genericValue: SiGenericValue): Promise<boolean> {
		return this.createGenericManager().pasteValue(genericValue);
	}

	createInputResetPoint(): Promise<SiInputResetPoint> {
		return this.createGenericManager().createResetPoint();
	}
}


