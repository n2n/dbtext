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
Jhtml.ready(function(elements) {
	(function() {
		if (typeof $.fn.magnificPopup != 'function') return;
		$(elements).find(".rocket-image-previewable").magnificPopup({
			type: 'image', 
			gallery: {
				enabled:true
			}
		});
	})();
	
	(function() {
		if (typeof $.fn.magnificPopup != 'function') return;
		$(elements).find(".rocket-video-previewable").magnificPopup({
            type: 'iframe',
        	iframe: {
        		  markup: '<div class="mfp-iframe-scaler">'+
        		            '<div class="mfp-close"></div>'+
        		            '<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>'+
        		          '</div>', // HTML markup of popup, `mfp-close` will be replaced by the close button

        		  patterns: {
        		    youtube: {
        		      index: 'youtube.com/', // String that detects type of video (in this case YouTube). Simply via url.indexOf(index).

        		      id: 'v=', // String that splits URL in a two parts, second part should be %id%
        		      // Or null - full URL will be returned
        		      // Or a function that should return %id%, for example:
        		      // id: function(url) { return 'parsed id'; }

        		      src: '//www.youtube.com/embed/%id%?autoplay=1' // URL that will be set as a source for iframe.
        		    },
        		    vimeo: {
        		      index: 'vimeo.com/',
        		      id: '/',
        		      src: '//player.vimeo.com/video/%id%?autoplay=1'
        		    }
        		    // you may add here more sources

        		  },

        		  srcAction: 'iframe_src', // Templating object key. First part defines CSS selector, second attribute. "iframe_src" means: find "iframe" and set attribute "src".
        		}
		});
	})();
	
	(function(){
		var jqImagePreviewItem = $(elements).find('.rocket-image-previewable');
		
		jqImagePreviewItem.each(function(){
			$(this).parent('td').addClass('rocket-image-preview-item');
		})
	})();
});
