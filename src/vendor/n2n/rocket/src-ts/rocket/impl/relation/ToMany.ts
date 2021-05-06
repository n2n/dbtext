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
/// <reference path="../../display/StructureElement.ts" />
namespace Rocket.Impl.Relation {
	import cmd = Rocket.Cmd;
	import display = Rocket.Display;
	
	var $ = jQuery;

	export class ToMany {
		constructor(private selector: ToManySelector = null, private embedded: ToManyEmbedded = null) {
		}
				
		public static from(jqToMany: JQuery, clipboard: Clipboard = null): ToMany {
			var toMany: ToMany = jqToMany.data("rocketImplToMany");
			if (toMany instanceof ToMany) {
				return toMany;
			}
			
			let toManySelector: ToManySelector = null;
			let jqSelector = jqToMany.children(".rocket-impl-selector");
			if (jqSelector.length > 0) {
				toManySelector = new ToManySelector(jqSelector, jqSelector.find("li.rocket-new-entry").detach());
				jqSelector.find("ul li").each(function () {
					var entry = new SelectedEntry($(this));
					entry.label = toManySelector.determineIdentityString(entry.pid);
					toManySelector.addSelectedEntry(entry);
				});
			}
			
			var jqCurrents = jqToMany.children(".rocket-impl-currents");
			var jqNews = jqToMany.children(".rocket-impl-news");
			let jqEntries = jqToMany.children(".rocket-impl-entries");
			var addControlFactory = null;
			
			let toManyEmbedded: ToManyEmbedded = null;
			let entryFormRetriever: EmbeddedEntryRetriever = null;
			if (jqCurrents.length > 0 || jqNews.length > 0 || jqEntries.length > 0) {
				
				if (jqNews.length > 0) {
					var propertyPath = jqNews.data("property-path");
					
					var startKey: number = 0;
					var testPropertyPath = propertyPath + "[n";
					jqNews.find("input, textarea").each(function () {
						var name: string = <string> $(this).attr("name");
						if (0 == name.indexOf(testPropertyPath)) {
							name = name.substring(testPropertyPath.length);
							
							name.match(/^[0-9]+/).forEach(function (key) {
								var curKey: number = parseInt(key);
								if (curKey >= startKey) {
									startKey = curKey + 1;
								}
							});
						}
					});
				
					entryFormRetriever = new EmbeddedEntryRetriever(jqNews.data("new-entry-form-url"), propertyPath, 
							jqNews.data("draftMode"), startKey, "n");
					addControlFactory = new AddControlFactory(entryFormRetriever, jqNews.data("add-item-label"),
							jqNews.data("paste-item-label"));
					
					let eiTypeIds: string[] = jqNews.data("ei-type-range");
					if (clipboard && eiTypeIds) {
						addControlFactory.pasteStrategy = {
							clipboard: clipboard,
							pastableEiTypeIds: eiTypeIds
						};
					}
				}
				
				toManyEmbedded = new ToManyEmbedded(jqToMany, addControlFactory, clipboard);
				if (entryFormRetriever) {
					entryFormRetriever.sortable = toManyEmbedded.sortable;
				}
				
				jqCurrents.children(".rocket-impl-entry").each(function () {
					toManyEmbedded.addEntry(new EmbeddedEntry($(this), toManyEmbedded.isReadOnly(), toManyEmbedded.sortable, !!clipboard));
				});
				jqNews.children(".rocket-impl-entry").each(function () {
					toManyEmbedded.addEntry(new EmbeddedEntry($(this), toManyEmbedded.isReadOnly(), toManyEmbedded.sortable, false));
				});
				jqEntries.children(".rocket-impl-entry").each(function () {
					toManyEmbedded.addEntry(new EmbeddedEntry($(this), true, false, !!clipboard));
				});
			}
			
			var toMany = new ToMany(toManySelector, toManyEmbedded);
			jqToMany.data("rocketImplToMany", toMany);		
			
			return toMany;
		}
	}
	
	class ToManySelector {
		private jqUl: JQuery<Element>;
		private entries: Array<SelectedEntry> = new Array<SelectedEntry>();
		private originalPids: Array<string>;
		private identityStrings: { [key: string]: string};
		private browserLayer: cmd.Layer = null;
		private browserSelectorObserver: Display.MultiEntrySelectorObserver = null;
		private resetButtonJq: JQuery<Element> = null;
		
		constructor(private jqElem: JQuery, private jqNewEntrySkeleton: JQuery) {
			this.jqElem = jqElem;
			this.jqUl = jqElem.children("ul");
			
			this.originalPids = jqElem.data("original-ei-ids");
			this.identityStrings = jqElem.data("identity-strings");
			
			this.init();
		}
		
		public determineIdentityString(pid: string): string {
			return this.identityStrings[pid];
		}
		
		private init() {
			var jqCommandList = $("<div />");
			this.jqElem.append(jqCommandList);
			
			var that = this;
			var commandList = new display.CommandList(jqCommandList);
			
			commandList.createJqCommandButton({ label: this.jqElem.data("select-label") })
					.mouseenter(function () {
						that.loadBrowser();
					})
					.click(function () {
						that.openBrowser();
					});

			this.resetButtonJq = commandList.createJqCommandButton({ label: this.jqElem.data("reset-label") })
					.click(function () {
						that.reset();
					})
					.hide();
			
			commandList.createJqCommandButton({ label: this.jqElem.data("clear-label") }).click(function () {
				that.clear();
			});
		}
		
		public createSelectedEntry(pid: string, identityString: string = null): SelectedEntry {
			var entry = new SelectedEntry(this.jqNewEntrySkeleton.clone().appendTo(<JQuery<HTMLElement>> this.jqUl));
			entry.pid = pid;
			if (identityString !== null) {
				entry.label = identityString;
			} else {
				entry.label = this.determineIdentityString(pid);
			} 
			this.addSelectedEntry(entry);
			return entry;
		}
		
		public addSelectedEntry(entry: SelectedEntry) {
			this.entries.push(entry);	
			
			var that = this;
			entry.commandList.createJqCommandButton({ iconType: "fa fa-trash-o", label: this.jqElem.data("remove-entry-label") }).click(function () {
				that.removeSelectedEntry(entry);				
			});
		}
		
		public removeSelectedEntry(entry: SelectedEntry) {
			for (var i in this.entries) {
				if (this.entries[i] !== entry) continue;
			
				entry.jQuery.remove();
				this.entries.splice(parseInt(i), 1);
			}
		}
		
		public reset() {
			this.clear();
			
			for (let pid of this.originalPids) {
				this.createSelectedEntry(pid);
			}
			
			this.manageReset();
		}
		
		public clear() {
			for (var i in this.entries) {
				this.entries[i].jQuery.remove();
			}
			
			this.entries.splice(0, this.entries.length);
			this.manageReset();
		}
		
		private manageReset() {
			this.resetButtonJq.hide();

			if (this.originalPids.length != this.entries.length) {
				this.resetButtonJq.show();
				return;
			}
			
			for (let entry of this.entries) {
				if (-1 < this.originalPids.indexOf(entry.pid)) continue;
					
				this.resetButtonJq.show();
				return;
			}
		}
		
		public loadBrowser() {
			if (this.browserLayer !== null) return;
			
			var that = this;
			
			this.browserLayer = Rocket.getContainer().createLayer(cmd.Zone.of(this.jqElem));
			this.browserLayer.hide();
			this.browserLayer.on(cmd.Layer.EventType.CLOSE, function () {
				that.browserLayer = null;
				that.browserSelectorObserver = null;				
			});
			
			let url = this.jqElem.data("overview-tools-url");
			this.browserLayer.monitor.exec(url).then(() => {
				let zone = this.browserLayer.getZoneByUrl(url);
				this.iniBrowserZone(zone);
				zone.on(Cmd.Zone.EventType.CONTENT_CHANGED, () => {
					this.iniBrowserZone(zone);
				});
			});
		}
		
		private iniBrowserZone(zone: cmd.Zone) {
			if (this.browserLayer === null) return;
			
			var ocs = Impl.Overview.OverviewPage.findAll(zone.jQuery);
			if (ocs.length == 0) return;
			
			ocs[0].initSelector(this.browserSelectorObserver = new Display.MultiEntrySelectorObserver());
			
			zone.menu.zoneCommandsJq.find(".rocket-important").removeClass("rocket-important");
			
			var that = this;
			zone.menu.partialCommandList.createJqCommandButton({ label: this.jqElem.data("select-label"), severity: Display.Severity.PRIMARY, important: true }).click(function () {
				that.updateSelection();
				zone.layer.hide();
			});
			zone.menu.partialCommandList.createJqCommandButton({ label: this.jqElem.data("cancel-label") }).click(function () {
				zone.layer.hide();
			});
			
			this.updateBrowser();
		}
		
		public openBrowser() {
			this.loadBrowser();
			
			this.updateBrowser();
			
			this.browserLayer.show();
		}
		
		private updateBrowser() {
			if (this.browserSelectorObserver === null) return;
			
			var selectedIds: Array<string> = new Array();
			this.entries.forEach(function (entry: SelectedEntry) {
				selectedIds.push(entry.pid);
			});
			
			this.browserSelectorObserver.setSelectedIds(selectedIds);
		}
		
		private updateSelection() {
			if (this.browserSelectorObserver === null) return;
			
			this.clear();
			
			var that = this;
			this.browserSelectorObserver.getSelectedIds().forEach(function (id) {
				var identityString = that.browserSelectorObserver.getIdentityStringById(id);
				if (identityString !== null) {
					that.createSelectedEntry(id, identityString);
					return;
				}
				
				that.createSelectedEntry(id);
			});

			this.manageReset();
		}
	}
	
	class SelectedEntry {
		private cmdList: display.CommandList;
		private jqLabel: JQuery;
		private jqInput: JQuery;
		
		constructor(private jqElem: JQuery) {
			jqElem.prepend(this.jqLabel = $("<span />"));
			
			this.cmdList = new display.CommandList($("<div />").appendTo(jqElem), true);			
			this.jqInput = jqElem.children("input").hide();
		}
		
		get jQuery(): JQuery {
			return this.jqElem;
		}
		
		get commandList(): display.CommandList {
			return this.cmdList;
		}
		
		get label(): string {
			return this.jqLabel.text();
		}
		
		set label(label: string) {
			this.jqLabel.text(label);
		}
		
		get pid(): string {
			return this.jqInput.val().toString();
		}
		
		set pid(pid: string) {
			this.jqInput.val(pid);
		}
	}
	
	class ToManyEmbedded {
		private jqToMany: JQuery;
		private addControlFactory: AddControlFactory;
		private reduceEnabled: boolean = true;
		sortable: boolean = true;
		min: number = null;
		max: number = null;
		private entries: Array<EmbeddedEntry> = new Array<EmbeddedEntry>();
		private jqEmbedded: JQuery;
		private jqEntries: JQuery;
		private expandZone: cmd.Zone = null;
		private dominantEntry: EmbeddedEntry = null;
		private closeLabel: string;
		private firstAddControl: AddControl = null;
		private lastAddControl: AddControl = null
		private entryAddControls: Array<AddControl> = new Array<AddControl>();
		private embeddedContainerJq: JQuery;
		
		constructor(jqToMany: JQuery, addControlFactory: AddControlFactory = null, private clipboard: Clipboard = null) {
			this.jqToMany = jqToMany;
			this.addControlFactory = addControlFactory;
			this.reduceEnabled = (true == jqToMany.data("reduced"));
			this.sortable = (true == jqToMany.data("sortable"));
			this.closeLabel = jqToMany.data("close-label");
			this.min = jqToMany.data("min") || null;
			this.max = jqToMany.data("max") || null;
			
			this.jqEmbedded = $("<div />", {
				"class": "rocket-impl-embedded"
			});
			
			let jqGroup = this.jqToMany.children(".rocket-group").children(".rocket-control");
			if (jqGroup.length > 0) {
				this.embeddedContainerJq = jqGroup;
			} else {
				this.embeddedContainerJq = this.jqToMany;
			}
			
			this.embeddedContainerJq.append(this.jqEmbedded);
			
			this.jqEntries = $("<div />");
			this.jqEmbedded.append(this.jqEntries);
			
			if (this.reduceEnabled) {
				var structureElement = Display.StructureElement.of(this.jqEmbedded);
				structureElement.type = Display.StructureElement.Type.LIGHT_GROUP;
				var toolbar = structureElement.getToolbar(true).show();
				
				var jqButton: JQuery<Element> = null;
				if (this.isReadOnly()) { 
					jqButton = toolbar.getCommandList().createJqCommandButton({ 
						iconType: "fa fa-file", 
						label: jqToMany.data("show-all-label"),
						important: true,
						labelImportant: true});
				} else {
					jqButton = toolbar.getCommandList().createJqCommandButton({ 
						iconType: "fa fa-pencil", 
						label:  jqToMany.data("edit-all-label"),
						severity: display.Severity.WARNING,
						important: true,
						labelImportant: true
					});
				}
				let that = this;
				jqButton.click(function () {
					that.expand();
				});
			}

			if (this.sortable) {
				this.initSortable();
			}
			
			this.initClipboard();
			
			this.changed();
		}
		
		public isReadOnly(): boolean {
			return this.addControlFactory === null;
		}
		
		private changed() {
			for (let i in this.entries) {
				let index = parseInt(i); 
				this.entries[index].setOrderIndex(index);
				
				if (this.isPartialExpaned()) continue;
				this.entries[index].setMoveUpEnabled(index > 0);
				this.entries[index].setMoveDownEnabled(index < this.entries.length - 1);
			}
			
			Rocket.scan();
			
			if (this.addControlFactory === null) return;
			
			let entryAddControl = null;
			while (entryAddControl = this.entryAddControls.pop()) {
				entryAddControl.dispose();
			}
			
			if (this.max && this.max <= this.entries.length) {
				if (this.firstAddControl !== null) {
					this.firstAddControl.dispose();
					this.firstAddControl = null;
				}
				if (this.lastAddControl !== null) {
					this.lastAddControl.dispose();
					this.lastAddControl = null;
				}
				return;
			}
			
			if (this.entries.length === 0 && this.firstAddControl !== null) {
				this.firstAddControl.dispose();
				this.firstAddControl = null;
			}
			
			if (this.entries.length > 0 && this.firstAddControl === null) {
				this.firstAddControl = this.createFirstAddControl();
			}
			
			if (this.isExpanded() && !this.isPartialExpaned()) {
				for (var i in this.entries) {
					if (parseInt(i) == 0) continue;
					
					this.entryAddControls.push(this.createEntryAddControl(this.entries[i]));
				}
			}
			
			if (this.lastAddControl === null) {
				this.lastAddControl = this.createLastAddControl();
			}
			
			if (this.isPartialExpaned()) {
				if (this.firstAddControl !== null) {
					this.firstAddControl.jQuery.hide();
				}
				this.lastAddControl.jQuery.hide();
			} else if (!this.isExpanded()) {
				if (this.firstAddControl !== null) {
					this.firstAddControl.jQuery.hide();
				}
				this.lastAddControl.jQuery.show();
			} else {
				if (this.firstAddControl !== null) {
					this.firstAddControl.jQuery.show();
				}
				this.lastAddControl.jQuery.show();
			}
		}
		
		private createFirstAddControl(): AddControl {
			var addControl = this.addControlFactory.createAdd();
			var that = this;
				
			this.jqEmbedded.prepend(addControl.jQuery);
			
			addControl.onNewEmbeddedEntry(function(newEntry: EmbeddedEntry) {
				that.insertEntry(newEntry);
			});
			return addControl;
		}
		
		private createEntryAddControl(entry: EmbeddedEntry): AddControl {
			var addControl = this.addControlFactory.createAdd();
			var that = this;
			
			this.entryAddControls.push(addControl);
			addControl.jQuery.insertBefore(entry.jQuery);
			addControl.onNewEmbeddedEntry(function(newEntry: EmbeddedEntry) {
				that.insertEntry(newEntry, entry);
			});
			return addControl;
		}
		
		private createLastAddControl(): AddControl {
			var addControl = this.addControlFactory.createAdd();
			var that = this;
			
			this.jqEmbedded.append(addControl.jQuery);
			addControl.onNewEmbeddedEntry(function(newEntry: EmbeddedEntry) {
				that.addEntry(newEntry);
				if (!that.isExpanded()) {
					that.expand(newEntry);
				}
			});
			return addControl;
		}
		
		
		public insertEntry(entry: EmbeddedEntry, beforeEntry: EmbeddedEntry = null) {
			entry.jQuery.detach();
			
			if (beforeEntry === null) {
				this.entries.unshift(entry);
				this.jqEntries.prepend(entry.jQuery);
			} else {
				entry.jQuery.insertBefore(beforeEntry.jQuery);
				this.entries.splice(beforeEntry.getOrderIndex(), 0, entry);
			}
			
			this.initEntry(entry);
			this.changed();
		}
		
		public addEntry(entry: EmbeddedEntry) {
			entry.setOrderIndex(this.entries.length);
			this.entries.push(entry);
			this.jqEntries.append(entry.jQuery);
		
			this.initEntry(entry);
			
			if (this.isReadOnly()) return;
			this.changed();
		}
		
		private switchIndex(oldIndex: number, newIndex: number) {
			let entry = this.entries[oldIndex];
			
			this.entries.splice(oldIndex, 1);
			this.entries.splice(newIndex, 0, entry);
			
			this.changed();
		}
			
		private initEntry(entry: EmbeddedEntry) {
			if (this.isExpanded()) {
				entry.expand();
			} else {
				entry.reduce();
			}
			
			var that = this;
			
			entry.onMove(function (up: boolean) {
				var oldIndex: number = entry.getOrderIndex();
				var newIndex: number = up ? oldIndex - 1 : oldIndex + 1;
			
				if (newIndex < 0 || newIndex >= that.entries.length) {
					return;
				}

				if (up) {
					that.entries[oldIndex].jQuery.insertBefore(that.entries[newIndex].jQuery);
				} else {
					that.entries[oldIndex].jQuery.insertAfter(that.entries[newIndex].jQuery);
				}
				
				that.switchIndex(oldIndex, newIndex);
			});
			
			entry.onRemove(function () {
				that.entries.splice(entry.getOrderIndex(), 1);
				entry.jQuery.remove();
				
				that.changed();
			});
			
			entry.onFocus(function () {
				that.expand(entry);
			});
			
			this.initCopy(entry);
		}
		
		private initSortable() {
			var that = this;
			var oldIndex: number = 0;
			this.jqEntries.sortable({
				"handle": ".rocket-impl-handle",
				"forcePlaceholderSize": true,
		      	"placeholder": "rocket-impl-entry rocket-impl-entry-placeholder",
				"start": function (event: JQueryEventObject, ui: JQueryUI.SortableUIParams) {
					oldIndex = ui.item.index();
				},
				"update": function (event: JQueryEventObject, ui: JQueryUI.SortableUIParams) {
					var newIndex = ui.item.index();
					
					that.switchIndex(oldIndex, newIndex);
				}
		    }).disableSelection();
		}
		
		
		private enabledSortable() {
			this.jqEntries.sortable("enable");
			this.jqEntries.disableSelection();
		}
		
		private disableSortable() {
			this.jqEntries.sortable("disable");
			this.jqEntries.enableSelection();
		}
		
		public isExpanded(): boolean {
			return this.expandZone !== null || !this.reduceEnabled;
		}
		
		public isPartialExpaned() {
			return this.dominantEntry !== null;
		}	 
		
		public expand(dominantEntry: EmbeddedEntry = null) {
			if (this.isExpanded()) return;
			
			if (this.sortable) {
				this.disableSortable();
			}
			
			this.dominantEntry = dominantEntry;
			this.expandZone = Rocket.getContainer().createLayer(cmd.Zone.of(this.jqToMany))
					.createZone(window.location.href);
			this.jqEmbedded.detach();
			
			let contentJq = $("<div />", { "class": "rocket-content" }).append(this.jqEmbedded);
			this.expandZone.applyContent(contentJq);
			$("<header></header>").insertBefore(contentJq);
			
			this.expandZone.layer.pushHistoryEntry(window.location.href);
			
			for (let i in this.entries) {
				if (dominantEntry === null) {
					this.entries[i].expand(true);
				} else if (dominantEntry === this.entries[i]) {
					this.entries[i].expand(false);
				} else {
					this.entries[i].hide();
				}
			}
			
			var that = this;
			
			var jqCommandButton = this.expandZone.menu.mainCommandList
					.createJqCommandButton({ iconType: "fa fa-trash-o", label: this.closeLabel, severity: display.Severity.WARNING} , true);
			jqCommandButton.click(function () {
				that.expandZone.layer.close();
			});
			
			this.expandZone.on(cmd.Zone.EventType.CLOSE, function () {
				that.reduce();
			});
			
			this.changed();
		}
		
		public reduce() {
			if (!this.isExpanded()) return;
			
			this.dominantEntry = null;
			this.expandZone = null;
			
			this.jqEmbedded.detach();
			this.embeddedContainerJq.append(this.jqEmbedded);
			
			for (let i in this.entries) {
				this.entries[i].reduce();
			}
			
			if (this.sortable) {
				this.enabledSortable();
			}
			
			this.changed();
		}
		
		private clearClipboard: boolean = true;
		private syncing: boolean = false;
		
		private initCopy(entry: EmbeddedEntry) {
			if (!this.clipboard || !entry.copyable) return;
			
			let diEntry = entry.entry;
			if (!diEntry) {
				throw new Error("No display entry available.");
			}
			
			entry.copied = this.clipboard.contains(diEntry.eiTypeId, diEntry.pid);
			
			entry.onRemove(() => {
				this.clipboard.remove(diEntry.eiTypeId, diEntry.pid);
			});
			
			entry.onCopy(() => {
				if (this.syncing) return;
				
				this.syncing = true;
				
				if (!entry.copied) {
					this.clipboard.remove(diEntry.eiTypeId, diEntry.pid);
					this.syncing = false;
					return;
				}
					
				if (this.clearClipboard) {
					this.clipboard.clear();
					this.clearClipboard = false;
				}
				
				this.clipboard.add(diEntry.eiTypeId, diEntry.pid, diEntry.identityString);
				this.syncing = false;
			});
		}
		
		private initClipboard() {
			if (!this.clipboard) return;
			
			let onChanged = () => {
				this.syncCopy();
			};
			
			this.clipboard.onChanged(onChanged);
			Cmd.Zone.of(this.jqToMany).page.on("disposed", () => {
				this.clipboard.offChanged(onChanged);
				
				if (this.firstAddControl) {
					this.firstAddControl.dispose();
					this.firstAddControl = null;
				}
				
				if (this.lastAddControl) {
					this.lastAddControl.dispose();
					this.lastAddControl = null;
				}
				
				let entryAddControl;
				while (entryAddControl = this.entryAddControls.pop()) {
					entryAddControl.dispose();
				}
			});
		}
		
		private syncCopy() {
			if (this.syncing || this.clipboard.isEmpty()) return;
			
			this.syncing = true;
			let found = false;
			
			for (let entry of this.entries) {
				if (!entry.copyable) continue;
			
				let diEntry = entry.entry;
				if (!diEntry) {
					throw new Error("No display entry available.");
				}
				
				if (this.clipboard.contains(diEntry.eiTypeId, diEntry.pid)) {
					entry.copied = true;
					found = true;
				} else {
					entry.copied = false;
				}
			}
			
			this.clearClipboard = !found;
			
			this.syncing = false;
		}
	}
}