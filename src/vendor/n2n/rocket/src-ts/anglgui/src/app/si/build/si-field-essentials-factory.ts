import { SiCrumbGroup, SiCrumb } from '../model/content/impl/meta/model/si-crumb';
import { Extractor } from 'src/app/util/mapping/extractor';
import { SiGridPos } from '../model/content/impl/embedded/model/si-panel';
import { SiImageDimension, SiFile, SiImageCut } from '../model/content/impl/file/model/file';
import { SiNavPoint } from '../model/control/si-nav-point';

export class SiEssentialsFactory {
	static createCrumbGroups(dataArr: Array<any>): SiCrumbGroup[] {
		const crumbGroups: SiCrumbGroup[] = [];
		for (const data of dataArr) {
			crumbGroups.push(this.createCrumbGroup(data));
		}
		return crumbGroups;
	}

	static createCrumbGroup(data: any): SiCrumbGroup {
		const extr = new Extractor(data);
		return {
			crumbs: this.createCrumbs(extr.reqArray('crumbs'))
		};
	}

	static createCrumbs(dataArr: Array<any>): SiCrumb[] {
		const crumbs: SiCrumb[] = [];
		for (const data of dataArr) {
			crumbs.push(this.createCrumb(data));
		}
		return crumbs;
	}

	static createCrumb(data: any): SiCrumb {
		const extr = new Extractor(data);

		let crumb: SiCrumb;
		switch (extr.reqString('type')) {
			case SiCrumb.Type.LABEL:
				crumb = SiCrumb.createLabel(extr.reqString('label'));
				break;
			case SiCrumb.Type.ICON:
				crumb = SiCrumb.createIcon(extr.reqString('iconClass'));
				break;
		}

		crumb.severity = extr.reqString('severity') as SiCrumb.Severity;
		crumb.title = extr.nullaString('title');

		return crumb;
	}

	static buildGridPos(data: any): SiGridPos|null {
		if (data === null) {
			return null;
		}

		const extr = new Extractor(data);

		return {
			colStart: extr.reqNumber('colStart'),
			colEnd: extr.reqNumber('colEnd'),
			rowStart: extr.reqNumber('rowStart'),
			rowEnd: extr.reqNumber('rowEnd')
		};
	}

	static buildSiFile(data: any): SiFile|null {
		if (data === null) {
			return null;
		}

		const extr = new Extractor(data);

		const imageDimensions: SiImageDimension[] = [];
		for (const idData of extr.reqArray('imageDimensions')) {
			imageDimensions.push(SiEssentialsFactory.createSiImageDimension(idData));
		}

		const siFile = new SiFile(extr.reqObject('id'), extr.reqString('name'), extr.nullaString('url'));
		siFile.thumbUrl = extr.nullaString('thumbUrl');
		siFile.mimeType = extr.nullaString('mimeType');
		siFile.imageDimensions = imageDimensions;
		return siFile;
	}

	static createSiImageDimension(data: any): SiImageDimension {
		const extr = new Extractor(data);

		return {
			id: extr.reqString('id'),
			name: extr.nullaString('name'),
			width: extr.reqNumber('width'),
			height: extr.reqNumber('height'),
			imageCut: this.createSiImageCut(extr.reqObject('imageCut')),
			ratioFixed: extr.reqBoolean('ratioFixed')
		};
	}

	static createSiImageCut(data: any): SiImageCut {
		const extr = new Extractor(data);

		return new SiImageCut(extr.reqNumber('x'), extr.reqNumber('y'), extr.reqNumber('width'),
				extr.reqNumber('height'), extr.reqBoolean('exists'));
	}

	static createNavPoint(data: any): SiNavPoint {
		const extr = new Extractor(data);

		return new SiNavPoint(extr.reqString('url'), extr.reqBoolean('siref'));
	}
}
