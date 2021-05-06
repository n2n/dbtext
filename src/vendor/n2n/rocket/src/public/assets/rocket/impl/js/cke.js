(function($) {
	var Cke = function(jqElem, type) {
		this.jqElem = jqElem;
		this.type = type || Cke.TYPE_NORMAL;
		
		this.linkConfigurations = jqElem.data("link-configurations") || null;
		this.defaultStylesSet = [{ name: 'Lead', element: 'p', attributes: { 'class' : 'lead'}}];
		
		this.jqElemParent = jqElem.parent(); 
		this.visible = this.jqElemParent.is(":visible");
		this.editor = null;
		this.initializeUI();
	}
	
	Cke.TYPE_INLINE = 'inline';
	Cke.TYPE_NORMAL = 'normal';

	Cke.bbCodePossibleToolbarItems =  {
			document: ['Source'],
			clipboard: [ "Cut", "Copy", "Paste", "Undo", "Redo" ],
			editing: [ "Find", "Replace", "SelectAll" ],
			basicstyles: [ "Bold", "Italic", "Underline", "RemoveFormat" ],
			paragraph: ["NumberedList", "BulletedList", "Blockquote"],
			links: ["Link", "Unlink"],
			insert: [ "Image", "SpecialChar"],
			styles: ["FontSize"],
			colors: [ "TextColor" ],
			tools: [ "Maximize", "ShowBlocks"]
	};
	
	Cke.prototype.initializeUI = function() {
		var that = this;
		if (this.visible) {
			switch (this.type) {
				case Cke.TYPE_INLINE:
					var jqElemDiv = $("<div/>").append(this.jqElem.text()).attr("contenteditable", "true")
						.addClass(this.jqElem.attr('class'));
					jqElemDiv.blur(function() {
						that.jqElem.html($(this).html());
					});
					this.jqElem.after(jqElemDiv);
					this.editor = CKEDITOR.inline(jqElemDiv.get(0), this.getOptions());
					that.jqElem.hide();
					break;
				case Cke.TYPE_NORMAL:
					this.editor = CKEDITOR.replace(this.jqElem.get(0), this.getOptions());
					break;
			}
		}
		

		if (!this.editor) {
			requestAnimationFrame(function() {
				that.hackCheck();
			});
		} else {
			this.editor.on("instanceReady", function () {
				requestAnimationFrame(function() {
					that.hackCheck();
				});
			});
		}
	};

	Cke.prototype.hackCheck = function() {
		var that = this;
		if (!document.contains(this.jqElem.get(0))) return;
		
		if (this.visible == this.jqElemParent.is(":visible")) {
			requestAnimationFrame(function() {
				that.hackCheck();
			});
			return;
		}
		
		this.visible = this.jqElemParent.is(":visible");
		if (this.editor) {
			this.editor.updateElement();
			this.editor.destroy();
			this.editor = null;
		}
		
		this.initializeUI();
	};
	
	Cke.prototype.getToolbar = function(mode, tableEditing, bbcode, 
			hasAdditionalStyles, hasFormatTags) {
		//if (mode == null) return normalToolbar;
		var modeNum = ["simple", "normal", "advanced"].indexOf(mode),
			basicStyleItems = ["Bold", "Italic", "Underline", "Strike", "RemoveFormat"],
			clipboardItems = [],
			editingItems = [],
			paragraphItems = ["NumberedList", "BulletedList"],
			linkItems = ["-", "Link", "Unlink"],
			insertItems = [],
			styleItems = [],
			toolItems = [],
			aboutItems = ["About"],
			toolbar = [];
		
		if (modeNum >= 1) {
			clipboardItems = clipboardItems.concat(["Cut", "Copy", "Paste", "-", "Undo", "Redo"]);
			editingItems = editingItems.concat(["Find", "Replace"]);
			basicStyleItems = basicStyleItems.concat(["Subscript", "Superscript"]);
			paragraphItems = paragraphItems.concat(["Outdent", "Indent", "blocks", "Blockquote", "-", "JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyBlock"]);
			linkItems = linkItems.concat(["Anchor", "-"]);
			basicStyleItems = basicStyleItems.concat(["-", "CopyFormatting", "-"]);
			toolItems = toolItems.concat(['Maximize', 'ShowBlocks']);

			if (tableEditing) {
				insertItems.push("Table");
			}

			insertItems = insertItems.concat(["HorizontalRule", "Smiley", "SpecialChar", "InsertSmiley"]);

			if (hasAdditionalStyles) {
				styleItems = styleItems.concat([ "Styles"]);
			}
			
			if (hasFormatTags) {
				styleItems = styleItems.concat([ "Format"]);
			}
		}

		if (modeNum >= 2) {
			insertItems = insertItems.concat(["Iframe"]);
			paragraphItems = paragraphItems.concat(["-", "CreateDiv", "PageBreak", "-"]);
			toolItems = toolItems.concat("-", "Source", "-");
		}
		

		toolbar.push({name: "clipboard", items: clipboardItems});
		toolbar.push({name: "editing", items: editingItems});
		toolbar.push({name: "basicstyles", items: basicStyleItems});
		toolbar.push({name: "paragraph", items: paragraphItems})
		toolbar.push({name: "links", items: linkItems});
		toolbar.push({name: "insert", items: insertItems});
		toolbar.push({name: "styles", items: styleItems});
		toolbar.push({name: "tools", items: toolItems});
		toolbar.push({name: "about", items: aboutItems});
		
		if (!bbcode) return toolbar;
		
		//bbcodify options
		var newToolbar = new Array();
		
		for (var i in toolbar) {
			var toolbarItem = toolbar[i],
				newToolbarItems = new Array();
			for (var j in toolbarItem.items) {
				if (Cke.bbCodePossibleToolbarItems.hasOwnProperty(toolbarItem.name) && 
						Cke.bbCodePossibleToolbarItems[toolbarItem.name].hasOwnProperty(j)) {
					newToolbarItems.push(toolbarItem.items[j]);
				}
			}
			if (newToolbarItems.length > 0) {
				newToolbar.push({
					name: toolbarItem.name,
					items: newToolbarItems
				});
			}
		}
		
		return newToolbar;
	}

	Cke.prototype.getOptions = function() {
		var configOptions = this.jqElem.data("rocket-impl-toolbar"),
			bbcode = configOptions["bbcode"] || false,
			contentsCss = configOptions["contentsCss"] || [],
			additionalStyles = configOptions["additionalStyles"] || [],
			bodyId = configOptions["bodyId"] || null,
			bodyClass = configOptions["bodyClass"] || null,
			formatTags = configOptions["formatTags"] || [],
			options = new Object();
		
		options.toolbar = this.getToolbar(configOptions['mode'], configOptions['tableEditing'] || false, bbcode, 
				additionalStyles.length > 0, formatTags.length > 0);
		options.extraPlugins = '';
		if (bbcode) {
			options.extraPlugins = 'bbcode';
		}
		
		if (contentsCss.length > 0) {
			options.contentsCss = contentsCss;
		}
		
		if (bodyClass) {
			options.bodyClass = bodyClass;
		}
		
		if (bodyId) {
			options.bodyId = bodyId;
		}
		
		if (formatTags.length > 0) {
			options.format_tags = formatTags;
		}
		
		if (this.type === Cke.TYPE_NORMAL) {
			if (options.extraPlugins.length > 0) {
				options.extraPlugins += ',';
			}
			options.extraPlugins += 'autogrow';
			options.removePlugins = 'resize';
			options.autoGrow_maxHeight = $(window).outerHeight() - 250;
			options.autoGrow_minHeight = 300;
			if (options.autoGrow_maxHeight > 700) {
				options.autoGrow_maxHeight = 700;
			}
		}
		
		var stylesSet = this.defaultStylesSet;
		if (additionalStyles.length > 0) {
			stylesSet = stylesSet.concat(additionalStyles);
		}
		
		options.stylesSet = stylesSet;
		return options;
	}
	
	var WysiwygIframe = function(jqElem) {
		this.jqElem = jqElem;
		
		this.contentsCss =  jqElem.data("contents-css") || null;
		if (this.contentsCss !== null) {
			this.contentsCss = JSON.parse(this.contentsCss.replace(/'/g, '"'))
		}
		this.bodyId = jqElem.data("body-id") || null;
		this.bodyClass = jqElem.data("body-class") || null;
		
		(function(that) {
//			this.document.open();
//			this.document.write(jqElem.data("content-html-json"));
//			this.document.close();
			this.configureIframe();
		}).call(this, this);
	};
	
	WysiwygIframe.prototype.configureIframe = function() {
		var that = this;
		var doIt = function() {
			var jqElemDocument = $(that.jqElem.get(0).contentWindow.document),
			jqElemBody = jqElemDocument.find("body:first"),
			jqElemHead = jqElemDocument.find("head:first");
			
			if (null !== that.contentsCss) {
				for (var i in that.contentsCss) {
					jqElemHead.append($("<link />", { href: that.contentsCss[i], rel: "stylesheet", media: "screen"}));
				}
			}
			
			if (null !== that.bodyId) {
				jqElemBody.attr("id", that.bodyId);
			}
			
			if (null !== that.bodyClass) {
				jqElemBody.addClass(that.bodyClass);
			}
			
			jqElemBody.append(that.jqElem.data("content-html-json"));
			
			var containerHeight = jqElemBody.outerHeight(true, true);
			containerHeight = (containerHeight > 400) ? 400 : containerHeight;
			that.jqElem.outerHeight(containerHeight);
			
			that.jqElem.on('load', function() {
				doIt();
			});
		}
		
		that.jqElem.ready(function() {
			doIt();
		});
	};
	
	if (typeof Jhtml !== 'undefined') {
		Jhtml.ready(function (elements) {
			$(elements).find(".rocket-impl-cke-classic").each(function () {
				new Cke($(this), Cke.TYPE_NORMAL);
				
				
//			var observer = new MutationObserver(function(mutations) {
//				  mutations.forEach(function(mutation) {
//				    console.log(mutation.type);
//				  });    
//			});
//			
//			var config = { attributes: true, childList: true, characterData: true };
//			observer.observe(elem.parentElement, config);
				
				
				
//			editor.checkDirty();
//			editor.resetDirty();
//			editor.resize();
				
//			createFakeElement
//			cke.js:10:5
//			createFakeParserElement
//			cke.js:10:5
//			restoreRealElement
				
//			CKEDITOR.remove(elem);
//			console.log("ee2");
//			for (let i in editor) {
//				console.log(i);
//			}
//			
				
				
//			let parentJq = $(elem.parentElement);
//			let visible = parentJq.is(":visible");
//			
//			let editor;
//			if (visible) {
//				editor = CKEDITOR.replace(elem);
//			}
//			
//			let hackCheck = function () {
//				if (!document.contains(elem)) return;
//				
//				if (visible == parentJq.is(":visible")) {
//					requestAnimationFrame(hackCheck);
//					return;
//				}
//				
//				visible = parentJq.is(":visible");
//				if (editor) {
//					editor.updateElement();
//					editor.destroy();
//					editor = null;
//				}
//				
//				if (visible) {
//					editor = CKEDITOR.replace(elem);
//				}
//				
//				requestAnimationFrame(hackCheck);
//			};
//			
//			
//			if (!editor) {
//				requestAnimationFrame(hackCheck);
//			} else {
//				editor.on("instanceReady", function () {
//					requestAnimationFrame(hackCheck);
//				});
//			}
				
				
//			let formJq = $(elem).closest("form");
//			formJq.submit(() => {
//				for (let i in CKEDITOR.instances) {
//					CKEDITOR.instances[i].updateElement();
//				}
//			});
//			formJq.find("input[type=submit], button[type=submit]").click(() => {
//				alert();
//				for (let i in CKEDITOR.instances) {
//					CKEDITOR.instances[i].updateElement();
//				}
//			});
			});
			
		});
		
		
		Jhtml.ready(function (elements) {
			$(elements).find(".rocket-cke-detail").each(function () {
				new WysiwygIframe($(this));
			});
		});
		
	} else {
		$(document).ready(function() {
			$(".rocket-impl-cke-classic").each(function () {
				new Cke($(this), Cke.TYPE_NORMAL);
			});
			
			$(".rocket-cke-detail").each(function () {
				new WysiwygIframe($(this));
			});
		});
	}
})(jQuery);
