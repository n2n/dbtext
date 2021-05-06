import { Component, OnInit, ElementRef, DoCheck, Input, ViewChild } from '@angular/core';
import { SiImageCut } from '../../model/file';

@Component({
	selector: 'rocket-image-preview',
	templateUrl: './image-preview.component.html',
	styleUrls: ['./image-preview.component.css'],
})
export class ImagePreviewComponent implements OnInit, DoCheck {

	@Input()
	imageCut: SiImageCut|null = null;

	@Input()
	src: string;

	@Input()
	size = 50;

	@ViewChild('img', { static: true } )
	imgElemRef: ElementRef;

	private style: CSSStyleDeclaration;

	constructor(private elemRef: ElementRef) {
		this.style = elemRef.nativeElement.style;
		this.style.overflow = 'hidden';
		this.style.display = 'block';
	}

	ngOnInit() {
	}

	ngDoCheck() {
		if (!this.imageCut) {
			this.style.width = this.size + 'px';
			this.style.height = this.size + 'px';
			this.imgElemRef.nativeElement.style.maxWidth = '100%';
			this.imgElemRef.nativeElement.style.maxHeight = '100%';
			return;
		}

		const widthRatio = this.size / this.imageCut.width;
		const heightRatio = this.size / this.imageCut.height;
		const ratio = Math.min(widthRatio, heightRatio);

		this.style.width = (this.imageCut.width * ratio) + 'px';
		this.style.height = (this.imageCut.height * ratio) + 'px';

		const imgElement = this.imgElemRef.nativeElement;

		imgElement.style.display = 'block';
		imgElement.style.width = (imgElement.naturalWidth * ratio) + 'px';
		imgElement.style.height = (imgElement.naturalHeight * ratio) + 'px';
		imgElement.style.marginLeft = (this.imageCut.x * -ratio) + 'px';
		imgElement.style.marginTop = (this.imageCut.y * -ratio) + 'px';
	}

}
