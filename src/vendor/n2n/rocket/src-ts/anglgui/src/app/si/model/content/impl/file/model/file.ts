
export class SiFile {
	thumbUrl: string|null;
	mimeType: string|null;
	imageDimensions: SiImageDimension[] = [];

	constructor(public id: object, public name: string, public url: string|null) {
	}

	copy(): SiFile {
		const siFile = new SiFile(this.id, this.name, this.url);
		siFile.thumbUrl = this.thumbUrl;
		siFile.mimeType = this.mimeType;
		siFile.imageDimensions = this.imageDimensions.map(id => {
			return {
				id: id.id,
				name: id.name,
				width: id.width,
				height: id.height,
				imageCut: id.imageCut.copy(),
				ratioFixed: id.ratioFixed
			}
		});
		return siFile;
	}
}

export interface SiImageDimension {
	id: string;
	name: string;
	width: number;
	height: number;
	imageCut: SiImageCut;
	ratioFixed: boolean;
}

export class SiImageCut {
	constructor(public x: number, public y: number, public width: number, public height: number, public exists: boolean) {
	}

	copy(): SiImageCut {
		return new SiImageCut(this.x, this.y, this.width, this.height, this.exists);
	}

	equals(obj: any): boolean {
		return obj instanceof SiImageCut && obj.x === this.x && obj.y === this.y && obj.height === this.height
				&& obj.exists === this.exists;
	}
}
