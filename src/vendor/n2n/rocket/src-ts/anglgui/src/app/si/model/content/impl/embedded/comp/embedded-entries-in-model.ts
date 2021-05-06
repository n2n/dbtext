import { AddPasteObtainer } from './add-paste-obtainer';
import { MessageFieldModel } from '../../common/comp/message-field-model';
import { EmbeStructure } from '../model/embe/embe-structure';
import { SiEmbeddedEntry } from '../model/si-embedded-entry';

export interface EmbeddedEntriesInModel extends MessageFieldModel {

	// getMin(): number;

	getMax(): number|null;

	// isNonNewRemovable(): boolean;

	isSortable(): boolean;

	// isSummaryRequired(): boolean;

	// getTypeCategory(): string;

	// getAllowedSiMaskQualifiers(): SiMaskQualifier[]|null;

	getAddPasteObtainer(): AddPasteObtainer;

	getEmbeStructures(): EmbeStructure[];

	switch(previousIndex: number, currentIndex: number): void;

	add(siEmbeddedEntry: SiEmbeddedEntry): void;

	addBefore(siEmbeddedEntry: SiEmbeddedEntry, embeStructure: EmbeStructure): void;

	// place(siEmbeddedEntry: SiEmbeddedEntry, embe: Embe) {
	// 	embe.siEmbeddedEntry = siEmbeddedEntry;
	// 	this.embeCol.writeEmbes();
	// }

	remove(embeStructure: EmbeStructure): void;

	open(embeStructure: EmbeStructure): void;

	// openAll(): void;
}
