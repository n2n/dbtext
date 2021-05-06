
export class SiProp {

	constructor(readonly id: string, public label: string, public helpText: string|null = null,
			public dependantPropIds = new Array<string>()) {

	}
}
