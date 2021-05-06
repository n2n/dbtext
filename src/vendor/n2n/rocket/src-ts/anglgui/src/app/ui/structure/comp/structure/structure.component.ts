import { Component, OnInit, Input, ViewChild, ElementRef, ChangeDetectorRef, OnDestroy, DoCheck, HostBinding, ContentChildren, QueryList, AfterContentInit } from '@angular/core';
import { StructureContentDirective } from 'src/app/ui/structure/comp/structure/structure-content.directive';
import { UiStructure } from '../../model/ui-structure';
import { UiContent } from '../../model/ui-content';
import { UiStructureType } from 'src/app/si/model/meta/si-structure-declaration';
import { Subscription } from 'rxjs';
import { StructureToolbarDirective } from './structure-toolbar.directive';

@Component({
	// tslint:disable-next-line:component-selector
	selector: '[rocketUiStructure]',
	templateUrl: './structure.component.html',
	styleUrls: ['./structure.component.css']
})
export class StructureComponent implements OnInit, OnDestroy, AfterContentInit {
	@Input()
	labelVisible = true;
	@Input()
	toolbarVisible = true;
	@Input()
	asideVisible = true;
	@Input()
	contentVisible = true;
	@HostBinding('class.rocket-compact')
	@Input()
	compact = false;

	private _uiStructure: UiStructure;
	private focusedSubscription: Subscription|null = null;

	// @ViewChild(StructureContentDirective, { static: true })
	// structureContentDirective: StructureContentDirective;

	@ContentChildren(StructureToolbarDirective)
	structureToolbarDirectives: QueryList<StructureToolbarDirective>;

	constructor(private elRef: ElementRef, private cdRef: ChangeDetectorRef) {
	}

	ngOnInit(): void {
// 		const componentFactory = this.componentFactoryResolver.resolveComponentFactory(CompactExplorerComponent);

// 		const componentRef = this.zoneContentDirective.viewContainerRef.createComponent(componentFactory);

// 		(<ZoneComponent> componentRef.instance).data = {};
		this.focusedSubscription = this.uiStructure.focused$.subscribe(() => {
			this.elRef.nativeElement.scrollIntoView({ behavior: 'smooth' });
		});
	}

	ngAfterContentInit(): void {
	}

	ngOnDestroy(): void {
		this.focusedSubscription.unsubscribe();
		this.focusedSubscription = null;

		this.clear();
	}

	// ngDoCheck() {
	// 	if (!this.uiStructure) {
	// 		return;
	// 	}

	// 	// const classList = this.elRef.nativeElement.classList;

	// 	// if (this.uiStructure.isItemCollection()) {
	// 	// 	if (!classList.contains('rocket-item-collection')) {
	// 	// 		classList.add('rocket-item-collection');
	// 	// 	}
	// 	// } else {
	// 	// 	if (classList.contains('rocket-item-collection')) {
	// 	// 		classList.remove('rocket-item-collection');
	// 	// 	}
	// 	// }

	// 	// if (this.uiStructure.isDoubleItem()) {
	// 	// 	if (!classList.contains('rocket-double-item')) {
	// 	// 		classList.add('rocket-double-item');
	// 	// 	}
	// 	// } else {
	// 	// 	if (classList.contains('rocket-double-item')) {
	// 	// 		classList.remove('rocket-double-item');
	// 	// 	}
	// 	// }
	// }

	@HostBinding('class.rocket-item-collection')
	get itemCollection(): boolean {
		return this.uiStructure.isItemCollection();
	}

	@HostBinding('class.rocket-double-item')
	get doubleItem(): boolean {
		return this.uiStructure.isDoubleItem();
	}

	@HostBinding('class.rocket-marked')
	get marked(): boolean {
		return this.uiStructure.marked;
	}

	@HostBinding('class.rocket-massive-toolbared')
	get massiveToolbarExists(): boolean {
		return this.uiStructure.isToolbarMassive() && this.uiStructure.getToolbarStructures().length > 0;
	}

	isToolbarMassive(): boolean {
		return this.uiStructure.isToolbarMassive();
	}

	hasCustomToolbar(): boolean {
		return this.structureToolbarDirectives.length > 0;
	}

	private clear(): void {
		this._uiStructure = null;
	}

	// @HostBinding('class.rocket-bulky')
	// get bulky(): boolean {
	// 	return !this.uiStructure.compact;
	// }

	@Input()
	set uiStructure(uiStructure: UiStructure) {
		// ensure UiStructure is root or assigned to parent
		uiStructure.getParent();

		this.clear();

		this._uiStructure = uiStructure;

		// this.toolbarSubscription = uiStructure.getToolbarChildren$().subscribe((toolbarUiStructures) => {
		// 	this.toolbarUiStructures = toolbarUiStructures;
		// 	// if (!uiStructure.disposed) {
		// 	// 	this.cdRef.detectChanges();
		// 	// }
		// });

		const classList = this.elRef.nativeElement.classList;
		classList.add('rocket-level-' + uiStructure.level);
	}

	get uiStructure(): UiStructure {
		return this._uiStructure;
	}

	// get contentStructuresAvailable(): boolean {
	// 	return this.uiStructure.getContentChildren().length > 0;
	// }

	get uiContent(): UiContent|null {
		if (this._uiStructure.model) {
			return this._uiStructure.model.getContent();
		}

		return null;
	}

	get toolbarUiStructures(): UiStructure[] {
		if (this._uiStructure.model) {
			return this._uiStructure.getToolbarStructures();
		}

		return [];
	}

	get asideUiContents(): UiContent[] {
		if (this._uiStructure.model) {
			return this._uiStructure.model.getAsideContents();
		}

		return [];
	}

	// get contentUiStructures(): UiStructure[] {
	// 	return this._uiStructure.getContentChildren();
	// }

	getType(): UiStructureType|null {
		return this.uiStructure.type;
	}

	getLabel(): string|null {
		return this.uiStructure.label;
	}

	isItemContext(): boolean {
		if (this.uiStructure.type !== UiStructureType.ITEM) {
			return false;
		}

		return !!this.uiStructure.getChildren().find(child => child.type === UiStructureType.ITEM);
	}

// 	ngDoCheck() {
// 		if (this.currentUiStructure &&
// 				(this.currentUiStructure !== this.uiStructure)) {
// 			this.structureContentDirective.viewContainerRef.clear();
// 			this.currentUiStructure = null;
// 		}
//
// 		if (this.currentUiStructure || !this.uiStructure) {
// 			return;
// 		}
//
// 		this.currentUiStructure = this.uiStructure;
// 		this.currentUiStructure.initComponent(this.structureContentDirective.viewContainerRef,
// 				this.componentFactoryResolver);
//// 		this.structureContentDirective.viewContainerRef.element.nativeElement.childNodes[0].classList.add('rocket-control');
//
// 		this.applyCssClass();
// 	}

	@HostBinding('class')
	get typeCssClass(): string {
		switch (this.getType()) {
			case UiStructureType.ITEM:
				return 'rocket-item';
			case UiStructureType.SIMPLE_GROUP:
				return 'rocket-group rocket-simple-group';
			case UiStructureType.MAIN_GROUP:
				return 'rocket-group rocket-main-group';
			case UiStructureType.LIGHT_GROUP:
				return 'rocket-group rocket-light-group';
			case UiStructureType.PANEL:
				return 'rocket-panel';
			case UiStructureType.MINIMAL:
				return 'rocket-minimal';
			default:
				return '';
		}
	}

	get loaded(): boolean {
		return !!this.uiStructure.model;
	}

}
