import {Component, OnInit} from '@angular/core';
import {TranslationService} from '../../../../util/i18n/translation.service';
import {UiBreadcrumb} from '../../../../ui/structure/model/ui-zone';
import {ToolsService} from "../../model/tools.service";
import {Message, MessageSeverity} from "../../../../util/i18n/message";

@Component({
selector: 'rocket-tools',
templateUrl: './tools.component.html',
styleUrls: ['./tools.component.css']
})
export class ToolsComponent implements OnInit {
uiBreadcrumbs: UiBreadcrumb[];

clearCacheInProgress: boolean = false;
messages: Message[] = [];
formData: Map<string, string> = new Map([["fname", "asdf Schmid"],["fmail", "nikolai@schmid.guru"]]);

constructor(translationService: TranslationService, private toolsService: ToolsService) {
this.uiBreadcrumbs = [
{
name: translationService.translate('tool_title'),
navPoint: {
routerLink: '/tools'
}
}
];
}

public clearCache(): void {
this.clearCacheInProgress = true;
this.toolsService.clearCache().toPromise().then(() => {
let clearCacheMessage = new Message("tools_cache_cleared_info", false, MessageSeverity.SUCCESS);
clearCacheMessage.durationMs = 2000;
this.messages.push(clearCacheMessage);
this.clearCacheInProgress = false;
});
}

ngOnInit(): void {
}

formDataChanged(value: Map<string, string>) {
this.formData = value;
this.formData.forEach((value, key) => {
if (value == "Andreas") {
this.formData.set(key, "Atusch");
}
});
}
}
