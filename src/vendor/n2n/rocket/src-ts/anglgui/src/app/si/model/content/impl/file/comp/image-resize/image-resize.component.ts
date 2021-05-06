import { Component, OnInit } from '@angular/core';
import { FileInFieldModel } from '../file-in-field-model';
import { SiImageDimension } from '../../model/file';

@Component({
	selector: 'rocket-image-resize',
	templateUrl: './image-resize.component.html',
	styleUrls: ['./image-resize.component.css']
})
export class ImageResizeComponent implements OnInit {

	model: FileInFieldModel;

	private ratioMap = new Map<number, ThumbRatio>();

	constructor() { }

	ngOnInit(): void {
		const siFile = this.model.getSiFile();

		if (!siFile) {
			throw new Error('No SiFile available.');
		}

		if (siFile.imageDimensions.length === 0) {
			throw new Error('No ImageDimensions available.');
		}

		for (const imageDimension of siFile.imageDimensions) {
			const thumbRatio = ThumbRatio.create(imageDimension);
			const ratio = thumbRatio.width / thumbRatio.height;

			if (!this.ratioMap.has(ratio)) {
				this.ratioMap.set(ratio, thumbRatio);
				return;
			}

			this.ratioMap.get(ratio).addImageDimension(imageDimension);
		}
	}

	get thumbRatios(): ThumbRatio[] {
		return this.thumbRatios;
	}
}




class ThumbRatio {
	public imageDimensions = new Array<SiImageDimension>();
	private largestImageDimension: SiImageDimension;

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

	get label(): string {
		return this.width + ' / ' + this.height;
	}

	addImageDimension(imageDimension: SiImageDimension) {
		this.imageDimensions.push(imageDimension);

		if (!this.largestImageDimension || this.largestImageDimension.height < imageDimension.height) {
			this.largestImageDimension = imageDimension;
		}
	}
}


// $(elements).find(".rocket-image-resizer-container").each(function () {
// 					new Rocket.Impl.File.RocketResizer($(this));
// 				});
