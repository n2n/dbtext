import { SiImageCut, SiImageDimension } from '../../model/file';
import { ElementRef } from '@angular/core';

export class ThumbRatio {
	public open = false;
	public imageDimensions = new Array<SiImageDimension>();
	// private _largestImageDimension: SiImageDimension;

	private groupedImageCuts: SiImageCut[];
	private imgCutDimMap = new Map<string, SiImageDimension[]>();

	private groupedPreviewElementRef: ElementRef|null = null;
	private imgDimPreviewElements = new Map<string, ElementRef>();

	constructor(readonly width: number, readonly height: number, readonly ratioFixed = false) {
	}

	static create(imageDimension: SiImageDimension): ThumbRatio {
		const width = imageDimension.width;
		const height = imageDimension.height;
		const ggt = ThumbRatio.gcd(width, height);

		const thumbRatio = new ThumbRatio(width / ggt, height / ggt, imageDimension.ratioFixed);
		thumbRatio.addImageDimension(imageDimension);
		return thumbRatio;
	}

	private static gcd(num1: number, num2: number): number {
		if (num2 === 0) {
			return num1;
		}

		return ThumbRatio.gcd(num2, num1 % num2);
	}

	registerPreviewImg(elementRef: ElementRef, imageDimension: SiImageDimension|null) {
		if (!imageDimension) {
			this.groupedPreviewElementRef = elementRef;
		} else {
			this.imgDimPreviewElements.set(imageDimension.id, elementRef);
		}
	}

	unregisterPreviewImg(elementRef: ElementRef) {
		if (this.groupedPreviewElementRef === elementRef) {
			this.groupedPreviewElementRef = null;
			return;
		}

		for (const [key, elemRef] of this.imgDimPreviewElements) {
			if (elemRef === elementRef) {
				this.imgDimPreviewElements.delete(key);
				return;
			}
		}

		throw new Error('Unkown preview ' + elementRef);
	}

	get label(): string {
		return this.width + ' / ' + this.height;
	}

	// get largestImageDimension(): SiImageDimension {
	// 	return this._largestImageDimension;
	// }

	// get customRatio(): number {
	// 	return this.width / this.height;
	// }

	addImageDimension(imageDimension: SiImageDimension) {
		this.imageDimensions.push(imageDimension);

		// if (!this._largestImageDimension || this._largestImageDimension.height < imageDimension.height) {
		// 	this._largestImageDimension = imageDimension;
		// }

		if (!imageDimension.imageCut.equals) {
			throw new Error();
		}

		this.classifyImageDimension(imageDimension);

		this.determineGroupedImageCut();
	}

	updateGroups() {
		this.imgCutDimMap.clear();

		for (const imageDimension of this.imageDimensions) {
			this.classifyImageDimension(imageDimension);
		}

		this.determineGroupedImageCut();
	}

	private classifyImageDimension(imageDimension: SiImageDimension) {
		const key = this.imgCutKey(imageDimension.imageCut);
		if (!this.imgCutDimMap.has(key)) {
			this.imgCutDimMap.set(key, []);
		}

		this.imgCutDimMap.get(key).push(imageDimension);
	}

	private determineGroupedImageCut() {
		let preferedImageCut: SiImageCut|null = null;
		if (this.groupedImageCuts) {
			preferedImageCut = this.groupedImageCuts[0];
		}

		this.groupedImageCuts = null;

		let lastSize = 0;
		for (const [key, imgDims] of this.imgCutDimMap) {
			if (lastSize > imgDims.length
					|| (lastSize === imgDims.length && (!preferedImageCut || key !== this.imgCutKey(preferedImageCut)))) {
				continue;
			}

			this.groupedImageCuts = imgDims.map(imgDim => imgDim.imageCut);
			lastSize = imgDims.length;
		}
	}

	private imgCutKey(imgCut: SiImageCut): string {
		return imgCut.width + ',' + imgCut.height + ',' + imgCut.x + ',' + imgCut.y;
	}

	hasGroupedImageCuts(): boolean {
		return !!this.groupedImageCuts;
	}

	hasIndividualImageCut(imageDimension: SiImageDimension): boolean {
		return !this.groupedImageCuts || !this.groupedImageCuts[0].equals(imageDimension.imageCut);
	}

	resetIndividutalImageCut(imageDimension: SiImageDimension) {
		let baseImageCut: SiImageCut;
		if (this.groupedImageCuts) {
			baseImageCut = this.groupedImageCuts[0];
		}
		if (!baseImageCut) {
			baseImageCut = this.imageDimensions[0] !== imageDimension ? this.imageDimensions[0].imageCut : this.imageDimensions[1].imageCut;
		}

		imageDimension.imageCut.x = baseImageCut.x;
		imageDimension.imageCut.y = baseImageCut.y;
		imageDimension.imageCut.width = baseImageCut.width;
		imageDimension.imageCut.height = baseImageCut.height;

		this.updateGroups();
	}

	getGroupedImageCuts(): SiImageCut[] {
		if (this.groupedImageCuts) {
			return this.groupedImageCuts;
		}

		throw new Error('No base image cut available.');
	}

	getGroupedPreviewImageCut(currentImageDimension: SiImageDimension|null): SiImageCut {
		for (const imgCut of this.getGroupedImageCuts()) {
			if (!currentImageDimension || imgCut !== currentImageDimension.imageCut) {
				return imgCut;
			}
		}

		throw new Error();
	}

	containImageDimension(imageDimension: SiImageDimension) {
		return !!this.imageDimensions.find(id => id.id === imageDimension.id);
	}
}
