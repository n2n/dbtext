import { Injectable } from '@angular/core';
import GLightbox from 'glightbox';
import { GlightBoxElement } from './glight-box-element';

@Injectable({
  providedIn: 'root'
})
export class GlightBoxService {

	public glightBoxElements: Array <GlightBoxElement> = [];
	public glightbox: GLightbox;
	
 	constructor() { }

	registerElement(htmlElement: GlightBoxElement) {
		this.glightBoxElements.push(htmlElement);
		this.renewGLightBox();
	}
	
	unregisterElement(htmlElement: GlightBoxElement) {
		const i = this.glightBoxElements.indexOf(htmlElement);
		
		if (i > -1) {
			this.glightBoxElements.splice(i, 1);
		}
		
		this.renewGLightBox();
	}
	
	renewGLightBox() {
		if (this.glightbox) {
			this.glightbox.destroy();
		}
		
		this.glightbox = GLightbox({
	    	elements: this.glightBoxElements
		});
		
	}
	
	open(htmlElement: GlightBoxElement) {
		const i = this.glightBoxElements.indexOf(htmlElement);
		
		if (i === -1) {
			throw new Error('glightbox: unregistered Element');
		}
		
		this.glightbox.openAt(i);
	}
}
