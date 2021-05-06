export interface SiCrumbGroup {
	crumbs: SiCrumb[];
}

export class SiCrumb {
	public severity = SiCrumb.Severity.NORMAL;
	public title: string|null = null;

	constructor(readonly type: SiCrumb.Type, readonly label: string|null, readonly iconClass: string|null) {
	}

	static createIcon(iconClass: string): SiCrumb {
		return new SiCrumb(SiCrumb.Type.ICON, null, iconClass);
	}

	static createLabel(label: string): SiCrumb {
		return new SiCrumb(SiCrumb.Type.LABEL, label, null);
	}
}

export namespace SiCrumb {
	export enum Type {
		LABEL = 'label',
		ICON = 'icon',
		NUMBER = 'number'
	}

	export enum Severity {
		NORMAL = 'normal',
		INACTIVE = 'inactive',
		IMPORTANT = 'important',
		UNIMPORTANT = 'unimportant'
	}
}
