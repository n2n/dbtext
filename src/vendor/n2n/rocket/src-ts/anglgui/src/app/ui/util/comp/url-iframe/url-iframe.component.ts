import {AfterViewInit, Component, ElementRef, Inject, Input, OnInit, SecurityContext, ViewChild} from '@angular/core';
import {DomSanitizer, SafeUrl} from '@angular/platform-browser';
import {IframeComponent} from "../iframe/iframe.component";

@Component({
	selector: 'rocket-ui-url-iframe',
	templateUrl: './url-iframe.component.html',
	styleUrls: ['./url-iframe.component.css']
})
export class UrlIframeComponent implements OnInit {
	@ViewChild('urlIframe') urlIframe: ElementRef;

	@Input()
	public srcUrl;

	public sanitizedUrl: SafeUrl;

	public loading = true;

	constructor(private sanitizer: DomSanitizer) {
	}

	ngOnInit(): void {
	this.sanitizedUrl = this.sanitizeUrl();
	}

	private appendScriptsToIframeContent() {
	const script = document.createElement('script');
	script.textContent = IframeComponent.createResizerJs();
	this.urlIframe.nativeElement.contentWindow.document.getElementsByTagName("body")[0]
		.insertAdjacentElement('beforeend', script);
	}

	sanitizeUrl() {
	return this.sanitizer.bypassSecurityTrustResourceUrl(this.srcUrl);
	}

	iframeLoaded() {
	if (!!this.urlIframe) {
		this.appendScriptsToIframeContent();
		this.loading = false;
		//alert(this.urlIframe.nativeElement.contentWindow.document.title);
	}
	}
}
