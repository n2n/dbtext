import { Embe } from './embe';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { EmbeOutCollection } from './embe-collection';
import { Subscription, Observable, BehaviorSubject } from 'rxjs';
import { UiStructureError } from 'src/app/ui/structure/model/ui-structure-error';
import { BehaviorCollection } from 'src/app/util/collection/behavior-collection';
import { UiZoneError } from 'src/app/ui/structure/model/ui-zone-error';


export class EmbeStructure {
	public uiZoneErrors: UiStructureError[] = [];

	constructor(readonly embe: Embe, readonly uiStructure: UiStructure) {

	}

	dispose() {
	}
}


export class EmbeStructureCollection {
	private embeStructuresCol = new BehaviorCollection<EmbeStructure>();
	private subscription = new Subscription();
	private reducedZoneErrorsSubject = new BehaviorSubject<UiZoneError[]>([]);

	constructor(readonly reduced: boolean, readonly embeCol: EmbeOutCollection) {
	}

	clear() {
		this.silentClear();
		this.embeStructuresCol.set([]);
	}

	private silentClear() {
		if (this.subscription) {
			this.subscription.unsubscribe();
			this.subscription = null;
		}

		for (const embeStructure of this.embeStructuresCol.get()) {
			embeStructure.dispose();
		}
	}

	private splieEmbeStrucutre(embe: Embe): EmbeStructure|null {
		const i = this.embeStructuresCol.get().findIndex(es => es.embe === embe);
		if (i === -1) {
			return null;
		}

		return this.embeStructuresCol.splice(i, 1)[0];
	}

	refresh(): void {
		const embeStructures = new Array<EmbeStructure>();
		const subscription = new Subscription();

		for (const embe of this.embeCol.embes) {
			let embeStructure = this.splieEmbeStrucutre(embe);
			if (!embeStructure) {
				embeStructure = new EmbeStructure(embe, (this.reduced ? embe.summaryUiStructure : embe.uiStructure));
			}

			if (this.reduced) {
				subscription.add(embeStructure.embe.uiStructure.getZoneErrors$().subscribe(() => {
					this.combineZoneErrors();
				}));
			}

			embeStructures.push(embeStructure);
		}

		this.silentClear();
		this.embeStructuresCol.set(embeStructures);
		this.subscription = subscription;

		if (this.reduced) {
			this.combineZoneErrors();
		}
	}

	get embeStructures(): EmbeStructure[] {
		return this.embeStructuresCol.get();
	}

	get embeStructures$(): Observable<EmbeStructure[]> {
		return this.embeStructuresCol.get$();
	}

	private combineZoneErrors() {
		const uiZoneErrors = new Array<UiZoneError>();
		for (const embeStructure of this.embeStructuresCol.get()) {
			uiZoneErrors.push(...embeStructure.embe.uiStructure.getZoneErrors());
		}
		this.reducedZoneErrorsSubject.next(uiZoneErrors);
	}

	get reducedZoneErrors$(): Observable<UiZoneError[]> {
		return this.reducedZoneErrorsSubject.asObservable();
	}
}
