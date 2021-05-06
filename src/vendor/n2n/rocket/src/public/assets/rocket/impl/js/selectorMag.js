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
 */
jQuery(document).ready(function($) {
	// a part of this js is included in the toMany.ts
	// !!! regarding that options should be used rocket-independent (i.e. in hangar) !!! 
	// !!! this should be changed !!!
	//		
	// rocketTs.registerUiInitFunction(".rocket-selector-mag", function(elem: JQuery) {
	//	 new ToManySelector(elem, "Text: remove Item");
	// });
	//
	
	var SelectorMag = function(jqElem) {
		this.jqElem = jqElem;
		this.jqElemContainer = jqElem.parent("div").parent("div");
		
		this.jqElemSelectOperator = this.jqElemContainer.prev("div").find("select:first");
		if (this.jqElemSelectOperator.length === 0) return;
		
		this.jqElemFilterContainer = this.jqElemContainer.next();
		if (this.jqElemFilterContainer.length === 0) return;
		
		(function(that) {
			this.jqElemSelectOperator.change(function() {
				that.jqElemContainer.hide();
				that.jqElemFilterContainer.hide();
				switch ($(this).val()) {
					case 'IN':
					case 'NOT IN':
						that.jqElemContainer.show();
						break;
					case 'EXISTS':
					case 'NOT EXISTS':
						that.jqElemFilterContainer.show();
						break;
				}
			}).change();
		}).call(this, this);
	};
	
	var initialize = function() {
		$(".rocket-selector-mag").each(function() {
			var jqElem = $(this);
			if (jqElem.data("initialized-rocket-selector-mag")) return;
			jqElem.data("initialized-rocket-selector-mag", true);
			new SelectorMag(jqElem);
		});
	};
	
	initialize();
	n2n.dispatch.registerCallback(initialize);
});
