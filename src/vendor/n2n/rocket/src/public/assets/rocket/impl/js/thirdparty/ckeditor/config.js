/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

$( document ).ready(function() {

//	var configOptions = $(".rocket-impl-cke-classic").data("rocket-impl-toolbar")
//
//	CKEDITOR.editorConfig = function( config ) {
//		
//		var modes = ["simple", "normal", "advanced"];
//		var modeNum = modes.indexOf(configOptions["mode"]);
//
//		config.plugins = 'dialogui,dialog,about,a11yhelp,dialogadvtab,basicstyles,bidi,blockquote,notification,button,toolbar,clipboard,panelbutton,panel,floatpanel,colorbutton,colordialog,templates,menu,contextmenu,copyformatting,div,resize,elementspath,enterkey,entities,popup,filebrowser,find,fakeobjects,flash,floatingspace,listblock,richcombo,font,forms,format,horizontalrule,htmlwriter,iframe,wysiwygarea,image,indent,indentblock,indentlist,smiley,justify,menubutton,language,link,list,liststyle,magicline,maximize,newpage,pagebreak,pastetext,pastefromword,preview,print,removeformat,save,selectall,showblocks,showborders,sourcearea,specialchar,scayt,stylescombo,tab,table,tabletools,tableselection,undo,wsc';
//		config.skin = 'moono-lisa';
//
//		config.bodyId = configOptions["bodyId"];
//		config.bodyClass = configOptions["bodyClass"];
//		config.tablesEnabled = configOptions["tablesEnabled"];
//
//		if (configOptions["contentsCss"] !== null) {
//			configOptions["contentsCss"].push(config.contentsCss);
//			config.contentsCss = configOptions["contentsCss"];
//			config.extraPlugins = "stylesheetparser";
//		}
//
//		config.toolbar = [];
//
//		var basicStyleItems = ["Bold", "Italic", "Underline", "Strike", "RemoveFormat"];
//		var clipboardItems = [];
//		var editingItems = [];
//		var paragraphItems = ["NumberedList", "BulletedList"];
//		var linkItems = ["-", "Link", "Unlink"];
//		var insertItems = [];
//		var styleItems = [];
//		var toolItems = [];
//		var aboutItems = ["About"];
//
//		if (modeNum >= 1) {
//			clipboardItems = clipboardItems.concat(["Cut", "Copy", "Paste", "PateText", "PasteFromWord", "-", "Undo", "Redo"]);
//			editingItems = editingItems.concat(["Find", "Replace"]);
//			basicStyleItems = basicStyleItems.concat(["Subscript", "Superscript"]);
//			paragraphItems = paragraphItems.concat(["Outdent", "Indent", "blocks", "Blockquote", "-", "JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyBlock"]);
//			linkItems = linkItems.concat(["Anchor", "-"]);
//			basicStyleItems = basicStyleItems.concat(["-", "CopyFormatting", "RemoveFormat", "-"]);
//			toolItems = toolItems.concat([ 'Maximize', 'ShowBlocks' ]);
//
//			if (configOptions["tableEditing"]) {
//				insertItems.push("Table");
//			}
//
//			insertItems = insertItems.concat(["HorizontalRule", "Smiley", "SpecialChar", "InsertSmiley"]);
//
//			if (!!configOptions["additionalStyles"]) {
//				styleItems = styleItems.concat([ "Styles", "Format"]);
//			}
//		}
//
//		if (modeNum >= 2) {
//			insertItems = insertItems.concat(["Iframe"]);
//			paragraphItems = paragraphItems.concat(["-", "CreateDiv", "PageBreak", "-"]);
//			toolItems = toolItems.concat("-", "Source", "-");
//		}
//
//		config.toolbar.push({name: "clipboard", items: clipboardItems});
//		config.toolbar.push({name: "editing", items: editingItems});
//		config.toolbar.push({name: "basicstyles", items: basicStyleItems});
//		config.toolbar.push({name: "paragraph", items: paragraphItems})
//		config.toolbar.push({name: "links", items: linkItems});
//		config.toolbar.push({name: "insert", items: insertItems});
//		config.toolbar.push({name: "styles", items: styleItems});
//		config.toolbar.push({name: "tools", items: toolItems});
//		config.toolbar.push({name: "about", items: aboutItems});
//
//		if (configOptions["bbcode"]) {
//			config.extraPlugins = "bbcode";
//		}
//		
//	};
});