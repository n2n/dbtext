import { SiGui } from '../../si-gui';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { IframeOutModel } from '../../../content/impl/iframe/comp/iframe-out-model';
import { SimpleUiStructureModel } from 'src/app/ui/structure/model/impl/simple-si-structure-model';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { IframeOutComponent } from '../../../content/impl/iframe/comp/iframe-out/iframe-out.component';
import { Message } from 'src/app/util/i18n/message';

export class IframeSiGui implements SiGui, IframeOutModel {

	constructor(public url: string|null, public srcDoc: string|null) {
	}

	getUrl(): string {
		return this.url;
	}

	getSrcDoc(): string {
		return this.srcDoc;
	}

	getMessages(): Message[] {
		return [];
	}

	createUiStructureModel(): UiStructureModel {
		return new SimpleUiStructureModel(new TypeUiContent(IframeOutComponent, (ref) => {
			ref.instance.model = this;
		}));
	}
}
