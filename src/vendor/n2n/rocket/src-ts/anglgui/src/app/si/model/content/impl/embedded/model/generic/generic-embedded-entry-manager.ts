import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { SiGenericEmbeddedEntryCollection, SiGenericEmbeddedEntry } from './generic-embedded';
import { SiFrame } from 'src/app/si/model/meta/si-frame';
import { SiService } from 'src/app/si/manage/si.service';
import { GenericMissmatchError } from 'src/app/si/model/generic/generic-missmatch-error';
import { SiModStateService } from 'src/app/si/model/mod/model/si-mod-state.service';
import { SiEmbeddedEntry } from '../si-embedded-entry';
import { SiEntryIdentifier } from '../../../../si-entry-qualifier';
import { SiField } from '../../../../si-field';
import { EmbeddedEntryObtainer } from '../embedded-entry-obtainer';
import { CallbackInputResetPoint } from '../../../common/model/callback-si-input-reset-point';
import { SiInputResetPoint } from '../../../../si-input-reset-point';

export class GenericEmbeddedEntryManager {

	constructor(private values: SiEmbeddedEntry[], private siService: SiService, private siModState: SiModStateService,
			private siFrame: SiFrame, private origSiField: SiField, private reduced: boolean, private allowedTypeIds: string[]|null) {
	}

	private findCurrentValue(entryIdentifier: SiEntryIdentifier): SiEmbeddedEntry|null {
		return this.values.find(embeddedEntry => embeddedEntry.entry.identifier.equals(entryIdentifier)) || null;
	}

	async copyValue(): Promise<SiGenericValue> {
		return new SiGenericValue(new SiGenericEmbeddedEntryCollection(
				await Promise.all(this.values.map(embeddedEntry => embeddedEntry.copy()))));
	}

	async pasteValue(genericValue: SiGenericValue): Promise<boolean> {
		const newEmbeInds = new Array<EmbeInd>();

		const collection = genericValue.readInstance(SiGenericEmbeddedEntryCollection);
		for (const genericEmbedddedEntry of collection.siGenericEmbeddedEntries) {
			const entryIdentifier = genericEmbedddedEntry.genericEntry.identifier;
			this.valEntryIdentifier(entryIdentifier);

			newEmbeInds.push({
				embeddedEntry: this.findCurrentValue(entryIdentifier),
				genericEmbeddedEntry: genericEmbedddedEntry
			});
		}

		const newEntryIdentifiers = newEmbeInds.filter(embeInd => embeInd.embeddedEntry === null).map(() => null);
		if (newEntryIdentifiers.length === 0) {
			return this.handlePaste(newEmbeInds, []);
		}

		const obtainer = new EmbeddedEntryObtainer(this.siService, this.siModState, this.siFrame, this.reduced,
				this.allowedTypeIds);
		return obtainer.obtain(newEntryIdentifiers).toPromise()
				.then((embeddedEntries) => {
					return this.handlePaste(newEmbeInds, embeddedEntries);
				});
	}

	private async handlePaste(embeInds: Array<EmbeInd>, newEmbeddedEntries: SiEmbeddedEntry[]): Promise<boolean> {
		const pastePromises: Array<Promise<boolean>> = [];

		const values = new Array<SiEmbeddedEntry>();
		for (const inf of embeInds) {
			let embeddedEntry = inf.embeddedEntry;
			if (!embeddedEntry) {
				embeddedEntry = newEmbeddedEntries.shift();
			}

			pastePromises.push(embeddedEntry.paste(inf.genericEmbeddedEntry));
			values.push(embeddedEntry);
		}
		this.values = values;

		return await Promise.all(pastePromises).then(vs => -1 !== vs.indexOf(true));
	}

	async createResetPoint(): Promise<SiInputResetPoint> {
		const valueDefs = await Promise.all(this.values
				.map(async embeddedEntry => ({ embeddedEntry, resetPoint: await embeddedEntry.createResetPoint() })));

		return new CallbackInputResetPoint(valueDefs, async (vds) => {
			const promises = new Array<Promise<void>>();

			this.values = [];
			for (const vd of vds) {
				this.values.push(vd.embeddedEntry);
				promises.push(vd.resetPoint.rollbackTo());
			}

			await Promise.all(promises);
		});
	}

	// resetToPoint(genericValue: SiGenericValue): void {
	// 	const collection = genericValue.readInstance(SiEmbeddedEntryResetPointCollection);
	// 	if (collection.origSiField !== this.origSiField) {
	// 		throw new GenericMissmatchError('Reset point belongs to diffrent field.');
	// 	}

	// 	const values = new Array<SiEmbeddedEntry>();
	// 	for (const resetPoint of collection.genercEntryResetPoints) {
	// 		this.valEntryIdentifier(resetPoint.origSiEmbeddedEntry.entry.identifier);

	// 		resetPoint.origSiEmbeddedEntry.resetToPoint(resetPoint.genericEmbeddedEntry);
	// 		values.push(resetPoint.origSiEmbeddedEntry);
	// 	}
	// 	this.values = values;
	// }

	private valEntryIdentifier(entryIdentifier: SiEntryIdentifier) {
		if (!this.siFrame.typeContext.containsTypeId(entryIdentifier.typeId)) {
			throw new GenericMissmatchError('Types dont match: '
					+ entryIdentifier.typeId + ' != ' + this.siFrame);
		}
	}
}

interface EmbeInd {
	embeddedEntry: SiEmbeddedEntry|null;
	genericEmbeddedEntry: SiGenericEmbeddedEntry;
}
