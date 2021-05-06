
export class SiTypeContext {
	constructor(public typeId: string, public entryBuildupIds: string[], public treeMode = false) {
	}

	containsTypeId(typeId: string): boolean {
		return this.typeId === typeId || -1 !== this.entryBuildupIds.indexOf(typeId);
	}
}
