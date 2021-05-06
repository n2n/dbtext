import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ButtonControlComponent } from './model/control/impl/comp/button-control/button-control.component';
import { CompactExplorerComponent } from './model/gui/impl/comp/compact-explorer/compact-explorer.component';
import { BulkyEntryComponent } from './model/gui/impl/comp/bulky-entry/bulky-entry.component';
import { InputInFieldComponent } from './model/content/impl/alphanum/comp/input-in-field/input-in-field.component';
import { TextareaInFieldComponent } from './model/content/impl/alphanum/comp/textarea-in-field/textarea-in-field.component';
import { FileInFieldComponent } from './model/content/impl/file/comp/file-in-field/file-in-field.component';
import { FileOutFieldComponent } from './model/content/impl/file/comp/file-out-field/file-out-field.component';
import {
	QualifierSelectInFieldComponent
} from './model/content/impl/qualifier/comp/qualifier-select-in-field/qualifier-select-in-field.component';
import { LinkOutFieldComponent } from './model/content/impl/alphanum/comp/link-out-field/link-out-field.component';
import { StringOutFieldComponent } from './model/content/impl/alphanum/comp/string-out-field/string-out-field.component';
import { EmbeddedEntriesInComponent } from './model/content/impl/embedded/comp/embedded-entries-in/embedded-entries-in.component';
import { CompactEntryComponent } from './model/gui/impl/comp/compact-entry/compact-entry.component';
import {
	EmbeddedEntriesSummaryInComponent
} from './model/content/impl/embedded/comp/embedded-entries-summary-in/embedded-entries-summary-in.component';
import { EmbeddedEntriesOutComponent } from './model/content/impl/embedded/comp/embedded-entries-out/embedded-entries-out.component';
import {
	EmbeddedEntriesSummaryOutComponent
} from './model/content/impl/embedded/comp/embedded-entries-summary-out/embedded-entries-summary-out.component';
import { ImageResizeComponent } from './model/content/impl/file/comp/image-resize/image-resize.component';
import { EntryDirective } from './model/mod/directive/entry.directive';
import { FormsModule } from '@angular/forms';
import { DragDropModule } from '@angular/cdk/drag-drop';
import { UiModule } from '../ui/ui.module';
import { UtilModule } from '../util/util.module';
import { PaginationComponent } from './model/gui/impl/comp/pagination/pagination.component';
import { CrumbGroupComponent } from './model/content/impl/meta/comp/crumb-group/crumb-group.component';
import { RouterModule } from '@angular/router';
import { AddPasteComponent } from './model/content/impl/embedded/comp/add-paste/add-paste.component';
import { QualifierComponent } from './model/content/impl/qualifier/comp/qualifier/qualifier.component';
import { TogglerInFieldComponent } from './model/content/impl/boolean/comp/toggler-in-field/toggler-in-field.component';
import { SplitComponent } from './model/content/impl/split/comp/split/split.component';
import { SplitViewMenuComponent } from './model/content/impl/split/comp/split-view-menu/split-view-menu.component';
import { SplitManagerComponent } from './model/content/impl/split/comp/split-manager/split-manager.component';
import { QualifierTilingComponent } from './model/content/impl/qualifier/comp/qualifier-tiling/qualifier-tiling.component';
import { ChoosePasteComponent } from './model/content/impl/embedded/comp/choose-paste/choose-paste.component';
import { EmbeddedEntryComponent } from './model/content/impl/embedded/comp/embedded-entry/embedded-entry.component';
import { ImageEditorComponent } from './model/content/impl/file/comp/image-editor/image-editor.component';
import { UploadResultMessageComponent } from './model/content/impl/file/comp/inc/upload-result-message/upload-result-message.component';
import { ImagePreviewComponent } from './model/content/impl/file/comp/image-preview/image-preview.component';
import { FieldMessagesComponent } from './model/content/impl/common/comp/field-messages/field-messages.component';
import { CrumbOutFieldComponent } from './model/content/impl/meta/comp/crumb-out-field/crumb-out-field.component';
import { EmbeddedEntryPanelsComponent } from './model/content/impl/embedded/comp/embedded-entry-panels/embedded-entry-panels.component';
import { SelectInFieldComponent } from './model/content/impl/enum/comp/select-in-field/select-in-field.component';
import { IframeOutComponent } from './model/content/impl/iframe/comp/iframe-out/iframe-out.component';
import { IframeInComponent } from './model/content/impl/iframe/comp/iframe-in/iframe-in.component';
import { DateTimeInComponent } from './model/content/impl/date/comp/date-time-in/date-time-in.component';
import { NumberInComponent } from './model/content/impl/alphanum/comp/number-in/number-in.component';
import { StringArrayInComponent } from './model/content/impl/array/comp/string-array-in/string-array-in.component';
import { PasswordInComponent } from './model/content/impl/alphanum/comp/password-in/password-in.component';
import { SiBuildTypes } from './build/si-build-types';
import { SiGuiFactory } from './build/si-gui-factory';
import { SiEntryFactory } from './build/si-entry-factory';
import { SiFieldFactory } from './build/si-field-factory';
import { SiResultFactory } from './build/si-result-factory';
import { SiControlFactory } from './build/si-control-factory';
import { SiUiFactory } from './build/si-ui-factory';

@NgModule({
	declarations: [
		ButtonControlComponent, CompactExplorerComponent, BulkyEntryComponent, StringOutFieldComponent,
		InputInFieldComponent, TextareaInFieldComponent, FileInFieldComponent, FileOutFieldComponent,
		QualifierSelectInFieldComponent, LinkOutFieldComponent, CompactEntryComponent, EmbeddedEntriesInComponent,
		EmbeddedEntriesSummaryInComponent, EmbeddedEntriesOutComponent, EmbeddedEntriesSummaryOutComponent,
		ImageResizeComponent, EmbeddedEntryPanelsComponent, EntryDirective, PaginationComponent, CrumbGroupComponent,
		AddPasteComponent, QualifierComponent, TogglerInFieldComponent, SplitComponent, SplitViewMenuComponent,
		SplitManagerComponent, QualifierTilingComponent, ChoosePasteComponent, EmbeddedEntryComponent,
		ImageEditorComponent, UploadResultMessageComponent, ImagePreviewComponent, FieldMessagesComponent,
		CrumbOutFieldComponent, SelectInFieldComponent, IframeOutComponent, IframeInComponent, DateTimeInComponent,
		NumberInComponent, StringArrayInComponent, PasswordInComponent
	],
	imports: [
		CommonModule,
		FormsModule,
		DragDropModule,
		UiModule,
		UtilModule,
		RouterModule
	],
	exports: [
		CompactExplorerComponent, BulkyEntryComponent, StringOutFieldComponent, InputInFieldComponent,
		TextareaInFieldComponent, FileInFieldComponent, FileOutFieldComponent, QualifierSelectInFieldComponent,
		LinkOutFieldComponent, CompactEntryComponent, EmbeddedEntriesInComponent, EmbeddedEntriesSummaryInComponent,
		EmbeddedEntriesOutComponent, EmbeddedEntriesSummaryOutComponent, ImageResizeComponent,
		EmbeddedEntryPanelsComponent, ButtonControlComponent, PaginationComponent, SelectInFieldComponent
	],
	entryComponents: [
		CompactExplorerComponent, BulkyEntryComponent, StringOutFieldComponent,
		InputInFieldComponent, TextareaInFieldComponent, FileInFieldComponent, FileOutFieldComponent, QualifierSelectInFieldComponent,
		LinkOutFieldComponent, CompactEntryComponent, EmbeddedEntriesInComponent, EmbeddedEntriesSummaryInComponent,
		EmbeddedEntriesOutComponent, EmbeddedEntriesSummaryOutComponent, ImageResizeComponent,
		EmbeddedEntryPanelsComponent, ButtonControlComponent, PaginationComponent, TogglerInFieldComponent, SplitComponent,
		SplitViewMenuComponent, CrumbGroupComponent, SplitManagerComponent, EmbeddedEntryComponent,
		ImageEditorComponent, CrumbOutFieldComponent, SelectInFieldComponent, NumberInComponent, StringArrayInComponent, PasswordInComponent
	]
})
export class SiModule { }

// SiBuildTypes.SiUiService = SiUiService;
// SiBuildTypes.SiService = SiService;
// SiBuildTypes.FileInSiField = FileInSiField;
// SiBuildTypes.LinkOutSiField = LinkOutSiField;
// SiBuildTypes.SiNavPoint = SiNavPoint;
// SiBuildTypes.QualifierSelectInSiField = QualifierSelectInSiField;
SiBuildTypes.SiGuiFactory = SiGuiFactory;
SiBuildTypes.SiEntryFactory = SiEntryFactory;
SiBuildTypes.SiFieldFactory = SiFieldFactory;
SiBuildTypes.SiControlFactory = SiControlFactory;
SiBuildTypes.SiResultFactory = SiResultFactory;
SiBuildTypes.SiUiFactory = SiUiFactory;
