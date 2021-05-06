import { SiEmbeddedEntry } from '../model/si-embedded-entry';

export interface AddPasteObtainer {

	preloadNew(): void;

	obtainNew(): Promise<SiEmbeddedEntry>;
}
