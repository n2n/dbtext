import { FileFieldModel } from './file-field-model';
import { SiFile } from '../model/file';
import { SiStyle } from 'src/app/si/model/meta/si-view-mode';

export interface FileInFieldModel extends FileFieldModel {

	getApiFieldUrl(): string;

	getSiStyle(): SiStyle;

	getApiCallId(): object;

	getAcceptedExtensions(): string[];

	getAcceptedMimeTypes(): string[];

	getMaxSize(): number;

	setSiFile(file: SiFile|null): void;
}
