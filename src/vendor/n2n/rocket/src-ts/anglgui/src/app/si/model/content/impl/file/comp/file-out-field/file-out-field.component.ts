import { Component, OnInit } from '@angular/core';
import { SiFile } from '../../model/file';
import { FileFieldModel } from '../file-field-model';

@Component({
	selector: 'rocket-file-out-field',
	templateUrl: './file-out-field.component.html',
	styleUrls: ['./file-out-field.component.css'],
	host: {class: 'rocket-file-out-field'}
})
export class FileOutFieldComponent implements OnInit {

	model: FileFieldModel;

	constructor() { }

	get currentSiFile(): SiFile|null {
		return this.model.getSiFile();
	}

	ngOnInit(): void {
	}

}
