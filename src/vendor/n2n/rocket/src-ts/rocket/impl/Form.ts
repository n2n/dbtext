/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 * 
 */
namespace Rocket.Impl {
	var $ = jQuery;
	
	export class Form {
		private jqForm: JQuery<Element>;
		private _jForm: Jhtml.Ui.Form;
		private _config: Form.Config = new Form.Config();
		
		constructor(jqForm: JQuery<Element>) {
			this.jqForm = jqForm;
			this._jForm = Jhtml.Ui.Form.from(<HTMLFormElement> jqForm.get(0));
			
			this._jForm.on("submit", () => {
				this.block();
			});
			
			this._jForm.on("submitted", () => {
				this.unblock();
			});
		}
		
		get jQuery(): JQuery<Element> {
			return this.jqForm;
		}
		
		get jForm(): Jhtml.Ui.Form {
			return this._jForm;
		}
		
		get config(): Form.Config {
			return this._config;
		}
		
		
		private lock: Cmd.Lock;
		
		private block() {
			let zone: Cmd.Zone;
			if (!this.lock && this.config.blockPage && (zone = Cmd.Zone.of(this.jqForm))) {
				this.lock = zone.createLock();
			}
		}
		
		private unblock() {
			if (this.lock) {
				this.lock.release();
				this.lock = null;
			}
		}
		
		public static from(jqForm: JQuery<Element>): Form {
			var form = jqForm.data("rocketImplForm");
			if (form instanceof Form) return form;
			
			if (jqForm.length == 0) {
				throw new Error("Invalid argument");
			}
			
			form = new Form(jqForm);
			jqForm.data("rocketImplForm", form);
			return form;
		}
	}
	
	
	export namespace Form {
		export class Config {
			public blockPage = true; 
		}
		
		export enum EventType {
			SUBMIT/* = "submit"*/,
			SUBMITTED/* = "submitted"*/
		}
	}
	
	export interface FormCallback {
		(form: Form): any
	}
}