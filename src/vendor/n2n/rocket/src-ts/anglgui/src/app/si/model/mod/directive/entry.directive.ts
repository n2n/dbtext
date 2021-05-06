import { Directive, Input, ElementRef, DoCheck, OnInit, OnDestroy, HostBinding } from '@angular/core';
import { SiEntry, SiEntryState } from '../../content/si-entry';
import { SiModStateService } from '../model/si-mod-state.service';

@Directive({
	selector: '[rocketSiEntry]'
})
export class EntryDirective implements DoCheck, OnInit, OnDestroy {

	private _siEntry: SiEntry;

	private currentlyHighlighted = false;
	private currentState: SiEntryState;

	constructor(private elementRef: ElementRef, private modState: SiModStateService) { }

	ngOnInit() {
	}

	ngOnDestroy() {
		this.modState.unregisterShownEntry(this.siEntry, this);
	}

	@Input()
	set siEntry(siEntry: SiEntry) {
		if (this._siEntry) {
			this.modState.unregisterShownEntry(this._siEntry, this);
		}

		this._siEntry = siEntry;
		this.modState.registerShownEntry(siEntry, this);
	}

	get siEntry(): SiEntry {
		return this._siEntry;
	}

	ngDoCheck() {
		this.chHighlightedClass(this.modState.lastModEvent
				&& this.modState.lastModEvent.containsModEntryIdentifier(this.siEntry.identifier));
		this.chStateClass(this.siEntry.state);
	}



	private chHighlightedClass(highlighted: boolean) {
		if (highlighted === this.currentlyHighlighted) {
			return;
		}

		this.currentlyHighlighted = highlighted;

		const classList = this.elementRef.nativeElement.classList;
		if (highlighted) {
			classList.add('rocket-highlighed');
		} else {
			classList.remove('rocket-highlighed');
		}
	}

	private chStateClass(state: SiEntryState) {
		if (this.currentState === state) {
			return;
		}

		this.currentState = state;

		const classList = this.elementRef.nativeElement.classList;
		classList.remove('rocket-reloading');
		classList.remove('rocket-locked');
		classList.remove('rocket-outdated');
		classList.remove('rocket-removed');

		switch (state) {
			case SiEntryState.RELOADING:
				classList.add('rocket-reloading');
				break;
			case SiEntryState.LOCKED:
				classList.add('rocket-locked');
				break;
			case SiEntryState.OUTDATED:
			case SiEntryState.REPLACED:
				classList.add('rocket-outdated');
				break;
			case SiEntryState.REMOVED:
				classList.add('rocket-removed');
				break;
		}
	}

}
