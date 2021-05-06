import { Component, OnInit } from '@angular/core';
import { DomSanitizer } from '@angular/platform-browser';
import { PanelLayout } from './panel-layout';
import { EmbeddedEntryPanelsModel, PanelDef } from '../embedded-entry-panels-model';

@Component({
	selector: 'rocket-embedded-entries-panels-in',
	templateUrl: './embedded-entry-panels.component.html',
	styleUrls: ['./embedded-entry-panels.component.css'],
	host: {class: 'rocket-embedded-entries-panels-in'}
})
export class EmbeddedEntryPanelsComponent implements OnInit {

	model: EmbeddedEntryPanelsModel;

	panelLayout: PanelLayout;
	panelDefs: Array<PanelDef>;

	constructor(san: DomSanitizer) {
		this.panelLayout = new PanelLayout(san);
	}

	ngOnInit(): void {
		this.panelDefs = this.model.getPanelDefs();
		for (const panelDef of this.panelDefs) {
			this.panelLayout.registerPanel(panelDef.siPanel);
		}
	}
}
