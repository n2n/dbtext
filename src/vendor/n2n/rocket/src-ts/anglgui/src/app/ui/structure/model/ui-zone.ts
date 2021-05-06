import { Subject, Subscription } from 'rxjs';
import { UiLayer } from './ui-layer';
import { UiContent } from './ui-content';
import { UiNavPoint } from '../../util/model/ui-nav-point';
import { UiStructure } from './ui-structure';
import { IllegalStateError } from 'src/app/util/err/illegal-state-error';
import { UiConfirmDialog } from './ui-confirm-dialog';

export class UiZone {
	title: string|null = null;
	breadcrumbs = new Array<UiBreadcrumb>();
	partialCommandContents = new Array<UiContent>();
	mainCommandContents = new Array<UiContent>();
	contextMenuContents = new Array<UiContent>();
	confirmDialog: UiConfirmDialog|null = null;
	private disposeSubject = new Subject<void>();
	private _structure: UiStructure|null = null;

	constructor(readonly url: string|null, readonly layer: UiLayer) {
	}

	get active(): boolean {
		return this.layer.currentRoute.zone === this;
	}

	set structure(structure: UiStructure|null) {
		if (this._structure === structure) {
			return;
		}

		this.resetStructure();

		if (structure) {
			this._structure = structure;
			structure.setZone(this);
		}
	}

	get structure(): UiStructure|null {
		return this._structure;
	}

	// public content: SiGui|null;


	private resetStructure() {
		const structure = this._structure;
		this._structure = null;
		if (structure) {
			structure.setZone(null);
		}
	}

	reset() {
		this.resetStructure();
		this.title = null;
		this.breadcrumbs = [];
		this.partialCommandContents = [];
		this.mainCommandContents = [];
		this.confirmDialog = null;
	}

	dispose() {
		this.reset();

		this.disposeSubject.next();
		this.disposeSubject.complete();
	}

	onDispose(callback: () => any): Subscription {
		return this.disposeSubject.subscribe(callback);
	}

	createConfirmDialog(message: string|null, okLabel: string|null, cancelLabel: string|null): UiConfirmDialog {
		if (this.confirmDialog) {
			throw new IllegalStateError('Zone already blocked by dialog.');
		}

		this.confirmDialog = new UiConfirmDialog(message, okLabel, cancelLabel);
		this.confirmDialog.confirmed$.subscribe(() => {
			this.confirmDialog = null;
		});
		return this.confirmDialog;
	}
}

export interface UiBreadcrumb {
	navPoint: UiNavPoint;
	name: string;
}

