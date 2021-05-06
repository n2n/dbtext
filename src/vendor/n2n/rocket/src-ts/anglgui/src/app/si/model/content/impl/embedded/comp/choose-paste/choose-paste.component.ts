import { Component, OnInit, Input } from '@angular/core';
import { ChoosePasteModel } from './choose-paste-model';
import { SiEmbeddedEntry } from '../../model/si-embedded-entry';
import { ClipboardService } from 'src/app/si/model/generic/clipboard.service';

@Component({
	selector: 'rocket-choose-paste',
	templateUrl: './choose-paste.component.html',
	styleUrls: ['./choose-paste.component.css']
})
export class ChoosePasteComponent implements OnInit {

	@Input()
	model: ChoosePasteModel;
	@Input()
	siEmbeddedEntry: SiEmbeddedEntry;

	constructor(private clipboard: ClipboardService) {
	}

	ngOnInit(): void {
		if (!this.model) {
			this.model = new ChoosePasteModel(this.siEmbeddedEntry, this.clipboard);
		}
	}
}
