import { Component, Input, ContentChildren, QueryList, AfterContentInit } from '@angular/core';
import { Embe } from '../../model/embe/embe';
import { EmbeStructure } from '../../model/embe/embe-structure';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { StructureToolbarDirective } from 'src/app/ui/structure/comp/structure/structure-toolbar.directive';

@Component({
	selector: 'rocket-embedded-entry',
	templateUrl: './embedded-entry.component.html',
	styleUrls: ['./embedded-entry.component.css']
})
export class EmbeddedEntryComponent {

	@Input()
	embeStructure: EmbeStructure;

	@ContentChildren(StructureToolbarDirective)
	private toolbarChildren: QueryList<any>;

	get embe(): Embe {
		return this.embeStructure.embe;
	}

	get uiStructure(): UiStructure {
		return this.embeStructure.uiStructure;
	}

	hasToolbar(): boolean {
		return this.toolbarChildren && this.toolbarChildren.length > 0;
	}
}
