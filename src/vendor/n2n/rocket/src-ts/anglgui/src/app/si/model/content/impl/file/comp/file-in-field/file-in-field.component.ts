import { Component, OnInit, ViewChild, ElementRef } from '@angular/core';
import { SiService } from 'src/app/si/manage/si.service';
import { SiFile } from '../../model/file';
import { FileInFieldModel } from '../file-in-field-model';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { PopupUiLayer } from 'src/app/ui/structure/model/ui-layer';
import { SimpleUiStructureModel } from 'src/app/ui/structure/model/impl/simple-si-structure-model';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { ImageEditorComponent } from '../image-editor/image-editor.component';
import { TranslationService } from 'src/app/util/i18n/translation.service';
import { UploadResult, ImageEditorModel } from '../image-editor-model';
import { Message } from 'src/app/util/i18n/message';
import { SiControl } from 'src/app/si/model/control/si-control';
import { SimpleSiControl } from 'src/app/si/model/control/impl/model/simple-si-control';
import { SiButton } from 'src/app/si/model/control/impl/model/si-button';
import { UiStructureType } from 'src/app/si/model/meta/si-structure-declaration';
import { SiEssentialsFactory } from 'src/app/si/build/si-field-essentials-factory';


@Component({
	selector: 'rocket-file-in-field',
	templateUrl: './file-in-field.component.html',
	styleUrls: ['./file-in-field.component.css'],
	host: {class: 'rocket-file-in-field'}
})
export class FileInFieldComponent implements OnInit {
	private uploader: CommonImageEditorModel;

	constructor(private siService: SiService, private translationService: TranslationService) {
	}

	get loading() {
		return !!this.uploader.uploadingFile || (this.currentSiFile && this.currentSiFile.thumbUrl && !this.imgLoaded);
	}

	get inputAvailable(): boolean {
		return !this.currentSiFile || (this.uploader.uploadInitiated && this.loading);
	}

	get currentSiFile(): SiFile|null {
		return this.model.getSiFile();
	}

	get removable(): boolean {
		if (this.loading) {
			return false;
		}

		if (this.currentSiFile) {
			return true;
		}

		if (this.fileInputRef && (this.fileInputRef.nativeElement as HTMLInputElement).value !== '') {
			return true;
		}

		return false;
	}

	get resizable(): boolean {
		return !this.loading && this.currentSiFile && this.currentSiFile.mimeType
				&& this.currentSiFile.imageDimensions.length > 0;
	}

	model: FileInFieldModel;
	uiStructure: UiStructure;
	imgLoaded = false;

	uploadResult: UploadResult|null = null;

	@ViewChild('fileInput')
	fileInputRef: ElementRef;

	private popupUiLayer: PopupUiLayer|null = null;

	ngOnInit() {
		this.uploader = new CommonImageEditorModel(this.siService, this.model);
	}

	getPrettySize(): string {
		let maxSize = this.model.getMaxSize();

		if (maxSize < 1024) {
			return maxSize.toLocaleString() + ' Bytes';
		}

		maxSize /= 1024;

		if (maxSize < 1024) {
			return maxSize.toLocaleString() + ' KB';
		}

		maxSize /= 1024;

		return maxSize.toLocaleString() + ' MB';
	}

	change(event: any) {
		this.reset();

		const fileList: FileList = event.target.files;

		if (fileList.length === 0) {
			return;
		}

		this.uploader.upload(fileList[0], null).then((uploadErrorResult) => {
			if (uploadErrorResult.siFile) {
				this.imgLoaded = false;
			}

			this.uploadResult = uploadErrorResult;
		});
	}

	editImage() {
		if (this.popupUiLayer) {
			return;
		}

		let bakSiFile = this.model.getSiFile().copy();
		const uiZone = this.uiStructure.getZone();

		this.popupUiLayer = uiZone.layer.container.createLayer();
		this.popupUiLayer.onDispose(() => {
			this.popupUiLayer = null;
		});

		const zone = this.popupUiLayer.pushRoute(null, null).zone;
		zone.title = 'Some Title';
		zone.breadcrumbs = [];
		zone.structure = new UiStructure(UiStructureType.SIMPLE_GROUP, null, new SimpleUiStructureModel(
					new TypeUiContent(ImageEditorComponent, (cr) => {
						cr.instance.model = this.uploader;
					})));
		zone.mainCommandContents = this.createPopupControls(() => { bakSiFile = null; })
					.map(siControl => siControl.createUiContent(() => zone));

		this.popupUiLayer.onDispose(() => {
			this.popupUiLayer = null;
			if (bakSiFile) {
				this.model.setSiFile(bakSiFile);
			}
		});
	}

	private createPopupControls(applyCallback: () => any): SiControl[] {
		return [
			new SimpleSiControl(
					new SiButton(this.translationService.translate('common_apply_label'), 'btn btn-success', 'fas fa-save'),
					() => {
						applyCallback();
						this.popupUiLayer.dispose();
					}),
			new SimpleSiControl(
					new SiButton(this.translationService.translate('common_discard_label'), 'btn btn-secondary', 'fas fa-trash'),
					() => {
						this.popupUiLayer.dispose();
					})
		];
	}


	// resize() {
	// 	if (this.popupUiLayer) {
	// 		return;
	// 	}

	// 	const uiZone = this.uiStructure.getZone();

	// 	this.popupUiLayer = uiZone.layer.container.createLayer();
	// 	this.popupUiLayer.onDispose(() => {
	// 		this.popupUiLayer = null;
	// 	});

	// 	this.popupUiLayer.pushZone(null).model = {
	// 		title: 'Some Title',
	// 		breadcrumbs: [],
	// 		structureModel: new SimpleUiStructureModel(
	// 				new TypeUiContent(ImageResizeComponent, (cr) => cr.instance.model = this.model))
	// 	};
	// }

	getAcceptStr(): string {
		const acceptParts = this.model.getAcceptedExtensions().map(ext => '.' + ext.split(',').join(''));
		acceptParts.push(...this.model.getAcceptedMimeTypes().map(ext => ext.split(',').join('')));

		return acceptParts.join(',');
	}

	private reset() {
		this.model.setSiFile(null);
		this.uploadResult = null;
	}

	removeCurrent() {
		this.reset();
		if (this.fileInputRef) {
			(this.fileInputRef.nativeElement as HTMLInputElement).value = '';
		}
	}
}

class CommonImageEditorModel implements ImageEditorModel {

	uploadInitiated = false;
	uploadingFile: Blob|null = null;

	constructor(private siService: SiService, private model: FileInFieldModel) {
	}

	getSiFile(): SiFile {
		return this.model.getSiFile();
	}

	setSiFile(siFile: SiFile) {
		this.model.setSiFile(siFile);
	}

	async upload(file: Blob, fileName: string|null): Promise<UploadResult> {
		if (file.size > this.model.getMaxSize()) {
			return Promise.resolve({ uploadTooLarge: true });
		}

		this.uploadingFile = file;
		this.uploadInitiated = true;

		const data = await this.siService.fieldCall(this.model.getApiFieldUrl(), this.model.getSiStyle(), this.model.getApiCallId(),
				{ fileName }, new Map().set('upload', file)).toPromise();

		this.uploadingFile = null;
		if (data.error) {
			return { uploadErrorMessage: data.error };
		}
		const siFile = SiEssentialsFactory.buildSiFile(data.file);
		this.model.setSiFile(siFile);
		return { siFile };
	}

	reset() {
		this.uploadingFile = null;
		this.uploadInitiated = false;
		this.uploadingFile = null;
	}

	getMessages(): Message[] {
		return this.model.getMessages();
	}
}
