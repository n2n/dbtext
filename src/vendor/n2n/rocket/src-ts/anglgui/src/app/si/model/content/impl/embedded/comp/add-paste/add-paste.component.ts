import { Component, OnInit, Input, EventEmitter, Output, HostListener, OnDestroy, ElementRef } from '@angular/core';
import { SiEmbeddedEntry } from '../../model/si-embedded-entry';
import { AddPasteObtainer } from '../add-paste-obtainer';
import { ClipboardService } from 'src/app/si/model/generic/clipboard.service';
import { ChoosePasteModel } from '../choose-paste/choose-paste-model';
import { Subscription, fromEvent, merge } from 'rxjs';
import { filter } from 'rxjs/operators';


export enum AddPasteType {
	REDUCED = 'reduced',
	BLOCK = 'block',
	TILES = 'tiles'
}

@Component({
	selector: 'rocket-si-add-past',
	templateUrl: './add-paste.component.html',
	styleUrls: ['./add-paste.component.css']
})
export class AddPasteComponent implements OnInit, OnDestroy {

	@Input()
	obtainer: AddPasteObtainer;

	private _disabled = false;

	@Output()
	newEntry = new EventEmitter<SiEmbeddedEntry>();

	loading = false;

	popupSubscription: Subscription|null = null;
	choosePasteModel: ChoosePasteModel;


	constructor(private elemRef: ElementRef<any>, private clipboardService: ClipboardService) {
	}

	ngOnInit(): void {
	}

	ngOnDestroy(): void {
		this.closePopup();
	}

	@Input()
	set disabled(disabled: boolean) {
		this._disabled = disabled;

		if (!this.disabled) {
			this.closePopup();
		}
	}

	get disabled(): boolean {
		return this._disabled;
	}

	@HostListener('mouseenter')
	prepareObtainer(): void {
		this.obtainer.preloadNew();
	}

	closePopup(): void {
		if (!this.popupOpen) {
			return;
		}

		this.popupSubscription.unsubscribe();
		this.popupSubscription = null;
	}

	get popupOpen(): boolean {
		return !!this.popupSubscription;
	}

	openPopup(): void {
		if (this.popupOpen) {
			return;
		}

		const up$ = fromEvent<MouseEvent>(document, 'click').pipe(filter(e => !this.elemRef.nativeElement.contains(e.target)));
		const esc$ = fromEvent<KeyboardEvent>(document, 'keyup')
				.pipe(filter((event: KeyboardEvent) => event.key === 'Escape'));
		this.popupSubscription = merge(up$, esc$).subscribe(() => {
			this.closePopup();
		});
	}

	togglePopup(): void {
		if (this.popupOpen) {
			this.closePopup();
		} else {
			this.openPopup();
		}

		if (!this.popupOpen || this.loading) {
			return;
		}

		if (this.choosePasteModel) {
			this.choosePasteModel.update();
			return;
		}

		this.loading = true;
		this.obtainer.obtainNew().then((siEmbeddedEntry) => {
			this.loading = false;
			this.handleAddResponse(siEmbeddedEntry);
		});
	}

	private handleAddResponse(siEmbeddedEntry: SiEmbeddedEntry): void {
		this.choosePasteModel = new ChoosePasteModel(siEmbeddedEntry, this.clipboardService);

		if (siEmbeddedEntry.selectedTypeId && this.choosePasteModel.pastables.length === 0
				&& this.choosePasteModel.illegalPastables.length === 0) {
			// this.siEmbeddedEntry.selectedTypeId = siMaskQualifier.identifier.typeId;
			this.choose(siEmbeddedEntry);
			return;
		}

		this.choosePasteModel.done$.subscribe(() => {
			this.choose(siEmbeddedEntry);
		});
	}

	private choose(siEmbeddedEntry: SiEmbeddedEntry): void {
		this.closePopup();
		this.newEntry.emit(siEmbeddedEntry);
		this.reset();
	}

	reset(): void {
		this.closePopup();
		this.choosePasteModel = null;
	}
}


