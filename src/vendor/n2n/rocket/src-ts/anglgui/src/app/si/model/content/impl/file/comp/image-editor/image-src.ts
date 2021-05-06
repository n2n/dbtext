import { Subject, Observable } from 'rxjs';
import { SiImageCut } from '../../model/file';
import { ElementRef } from '@angular/core';
import Cropper from 'cropperjs';

export interface RatioOpt {
	ratio: number;
	freeRatioAllowed: boolean;
}

export class ImageSrc {

	private cropper: Cropper|null = null;

	origWidth: number;
	origHeight: number;

	changed = false;
	cropping = false;
	private cropBoxData: any;
	private imageCuts: SiImageCut[]|null = null;

	private readySubject: Subject<void>|null = new Subject<void>();
	private ratioOpt: RatioOpt|null;
	private _fixedRatio = false;

	constructor(private elemRef: ElementRef, private mimeType: string) {
	}

	init() {
		this.destroy();

		this.cropper = new Cropper(this.elemRef.nativeElement, {
			viewMode: 1,
			preview: '.rocket-image-preview',
			zoomable: true,
			zoomOnTouch: false,
			zoomOnWheel: false,
			movable: false,
			center: true,
			crop: (event) => {
				if (!this.cropper.getCropBoxData().left) {
					return;
				}

				this.changed = true;
				this.cropping = true;

				const data = this.cropper.getData();

				if (!this.imageCuts) {
					this.origWidth = Math.round(data.width)
					this.origHeight = Math.round(data.height);
					return;
				}

				for (const imageCut of this.imageCuts) {
					imageCut.x = data.x;
					imageCut.y = data.y;
					imageCut.width = data.width;
					imageCut.height = data.height;
				}

				// console.log(event.type);
				// console.log(event.detail.x);
				// console.log(event.detail.y);
				// console.log(event.detail.width);
				// console.log(event.detail.height);
				// console.log(event.detail.rotate);
				// console.log(event.detail.scaleX);
				// console.log(event.detail.scaleY);
			},
			ready: () => {
				if (!this.readySubject) {
					throw new Error('No ready subject.');
				}

				const readySubject = this.readySubject;
				this.readySubject = null;
				readySubject.next();
				readySubject.complete();
				// console.log(this.cropper.getCanvasData());
				// console.log(this.cropper.getContainerData());

				// if (imageCut) {
				// 	this.cropper.setCropBoxData({ left: imageCut.x, top: imageCut.y, width: imageCut.width,
				// 			height: imageCut.height });
				// // 		rotate: 0, scaleX: 1, scaleY: 1 });
				// } else {
				// 	// this.cropper.setCropBoxData(this.cropper.getCanvasData());
				// 	this.cropper.clear();
				// }

				this.updateBoundries();
			}
		});
	}

	replace(url: string) {
		this.readySubject = new Subject<void>();
		this.cropper.replace(url);
	}

	reset() {
		this.cancelCropping();
		this.updateBoundries();
		this.cropper.reset();
		this.changed = false;
		this.updateBoundries();
	}

	private calcRatio(): number {
		const imageData = this.cropper.getImageData();

		return imageData.width / imageData.naturalWidth;
	}

	cut(imageCuts: SiImageCut[]|null, ratioOpt: RatioOpt|null) {
		this.imageCuts = null;

		if (!imageCuts) {
			this.cropper.clear();
			this.cropping = false;
			this.updateRatioOpt(ratioOpt, null);
			this.imageCuts = imageCuts;
			this.changed = false;
			return;
		}

		const imageCut = imageCuts[0];

		if (!imageCut) {
			throw new Error('Empty ImageCut Array.');
		}

		const cropData = {
			x: imageCut.x, y: imageCut.y, width: imageCut.width, height: imageCut.height
		};

		this.cropper.crop();
		this.cropping = true;
		this.updateRatioOpt(ratioOpt, imageCut);
		this.cropper.setData(cropData);
		this.imageCuts = imageCuts;
		this.changed = false;
	}

	private updateRatioOpt(ratioOpt: RatioOpt|null, imageCut: SiImageCut|null) {
		this.ratioOpt = ratioOpt;

		if (!this.ratioOpt) {
			this.fixedRatio = false;
			return;
		}

		if (!this.ratioOpt.freeRatioAllowed
				|| imageCut.width / imageCut.height === this.ratioOpt.ratio) {
			this.fixedRatio = true;
			return;
		}

		this.fixedRatio = false;
	}


	get freeRatioAllowed(): boolean {
		return !this.ratioOpt || this.ratioOpt.freeRatioAllowed;
	}

	get fixedRatio(): boolean {
		return this._fixedRatio;
	}

	set fixedRatio(fixedRatio: boolean) {
		if (!this.freeRatioAllowed) {
			fixedRatio = true;
		}

		const cropData = this.imageCuts ? {
			x: this.imageCuts[0].x, y: this.imageCuts[0].y, width: this.imageCuts[0].width, height: this.imageCuts[0].height
		} : null;

		this._fixedRatio = fixedRatio;
		if (fixedRatio) {
			this.cropper.setAspectRatio(this.ratioOpt.ratio);
		} else {
			this.cropper.setAspectRatio(null);
		}

		if (cropData) {
			this.cropper.setData(cropData);
		}
	}

	destroy() {
		if (!this.cropper) {
			return;
		}

		this.changed = false;

		this.cropper.destroy();
		this.cropper = null;

		if (this.readySubject) {
			this.readySubject.complete();
		}

		this.readySubject = new Subject<void>();
	}

	private cancelCropping() {
		if (this.cropping) {
			this.toggleCropping();
		}
	}

	rotateCw() {
		this.cancelCropping();
		this.changed = true;
		this.cropper.rotate(90);
		this.updateBoundries();
	}

	rotateCcw() {
		this.cancelCropping();
		this.changed = true;
		this.cropper.rotate(-90);
		this.updateBoundries();
	}

	private updateBoundries(){
		const containerData = this.cropper.getContainerData();
		const canvasData = this.cropper.getCanvasData();

		const widthZoomFactor = Math.min(containerData.width / canvasData.naturalHeight, 1);
		const heightZoomFactor = Math.min(containerData.height / canvasData.naturalHeight, 1);
		const zoomFactor = Math.min(widthZoomFactor, heightZoomFactor);

		this.cropper.zoomTo(zoomFactor);

		if (!this.imageCuts) {
			this.origWidth = this.cropper.getCanvasData().naturalWidth;
			this.origHeight = this.cropper.getCanvasData().naturalHeight;
		}

	}

	toggleCropping() {
		this.cropping = !this.cropping;

		if (this.cropping) {
			this.changed = true;
			this.cropper.crop();
		} else {
			this.cropper.clear();
			this.updateBoundries();
		}
	}

	createBlob(): Promise<Blob> {
		return new Promise((resolve) => {
			this.cropper.getCroppedCanvas().toBlob((blob) => {
				resolve(blob);
			}, this.mimeType);
		});
	}

	get ready(): boolean {
		return !this.readySubject;
	}

	get ready$(): Observable<void> {
		if (this.readySubject) {
			return this.readySubject.asObservable();
		}

		return new Observable<void>(subscriber => {
			subscriber.next();
			subscriber.complete();
		});
	}

	// createPreviewDataUrl(imageCut: SiImageCut): string {
	// 	if (!this.ready) {
	// 		throw new Error('Cropper not yet ready.');
	// 	}

	// 	if (this.cropping) {
	// 		this.cropBoxData = this.cropper.getCropBoxData();
	// 	}

	// 	this.cut(new SiImageCut(100, 100, 50, 50, false));
	// 	const dataUrl = this.cropper.getCroppedCanvas().toDataURL();

	// 	if (this.cropBoxData) {
	// 		this.cropper.setCropBoxData(this.cropBoxData);
	// 		this.cropBoxData = null;
	// 	} else {
	// 		this.cropper.clear();
	// 	}

	// 	return dataUrl;
	// }

}
