import { EmbeStructure } from '../model/embe/embe-structure';

export interface EmbeddedEntriesOutModel {

	getEmbeStructures(): EmbeStructure[];

	open(EmbeStructure: EmbeStructure): void;

	openAll(): void;
}
