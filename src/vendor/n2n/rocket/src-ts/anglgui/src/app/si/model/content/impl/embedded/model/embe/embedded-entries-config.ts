export interface EmbeddedEntriesOutConfig {
	reduced: boolean;
}


export interface EmbeddedEntriesInConfig extends EmbeddedEntriesOutConfig {
	min: number;
	max: number|null;
	nonNewRemovable: boolean;
	sortable: boolean;
	allowedTypeIds: string[]|null;
}
