import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { SiMaskQualifier } from 'src/app/si/model/meta/si-mask-qualifier';
import { SiEntryQualifier } from '../../../../si-entry-qualifier';

@Component({
	selector: 'rocket-qualifier-tiling',
	templateUrl: './qualifier-tiling.component.html',
	styleUrls: ['./qualifier-tiling.component.css'],
	host: {class: "rocket-qualifier-tiling"}
})
export class QualifierTilingComponent implements OnInit {

	@Input()
	siMaskQualifiers: Array<SiMaskQualifier> = [];
	@Input()
	illegalSiMaskQualifiers: Array<SiMaskQualifier> = [];
	@Input()
	siEntryQualifiers: Array<SiEntryQualifier> = [];
	@Input()
	illegalSiEntryQualifiers: Array<SiEntryQualifier> = [];
	@Input()
	disabled = false;
	@Output()
	siTypeSelected = new EventEmitter<SiMaskQualifier>();
	@Output()
	siEntrySelected = new EventEmitter<SiEntryQualifier>();
	@Input()
	simple = false;

	constructor() { }

	ngOnInit() {
	}

	get searchable() {
		return this.siMaskQualifiers.length + this.siEntryQualifiers.length > 10;
	}

	chooseSiType(siMaskQualifier: SiMaskQualifier) {
		this.siTypeSelected.emit(siMaskQualifier);
	}

	chooseSiEntry(siEntryQualifier: SiEntryQualifier) {
		this.siEntrySelected.emit(siEntryQualifier);
	}
}
