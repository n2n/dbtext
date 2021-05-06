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
	
	export class ToOne {
		
		constructor(private toOneSelector: ToOneSelector = null, private embedded: ToOneEmbedded = null) {
			if (toOneSelector && embedded) {
				embedded.whenChanged(function () {
					if (embedded.currentEntry || embedded.newEntry) {
						toOneSelector.jQuery.hide();
					} else {
						toOneSelector.jQuery.show();
					}
				});
			}
		}
		
		public static from(jqToOne: JQuery, clipboard: Clipboard = null): ToOne {
			let toOne: ToOne = jqToOne.data("rocketImplToOne");
			if (toOne instanceof ToOne) {
				return toOne;
			}
			
			let toOneSelector: ToOneSelector = null;
			let jqSelector = jqToOne.children(".rocket-impl-selector");
			if (jqSelector.length > 0) {
				toOneSelector = new ToOneSelector(jqSelector);
			}
			
			let jqCurrent = jqToOne.children(".rocket-impl-current");
			let jqNew = jqToOne.children(".rocket-impl-new");
			let jqDetail = jqToOne.children(".rocket-impl-detail");
			let addControlFactory = null;
			
			let toOneEmbedded: ToOneEmbedded = null;
			if (jqCurrent.length > 0 || jqNew.length > 0 || jqDetail.length > 0) {
				let newEntryFormUrl = jqNew.data("new-entry-form-url");
				if (jqNew.length > 0 && newEntryFormUrl) {
					let propertyPath = jqNew.data("property-path");
					let entryFormRetriever = new EmbeddedEntryRetriever(jqNew.data("new-entry-form-url"), propertyPath, 
							jqNew.data("draftMode"));
					entryFormRetriever.grouped = !!jqToOne.data("grouped");
					entryFormRetriever.sortable = false;
					
					addControlFactory = new AddControlFactory(entryFormRetriever, jqNew.data("add-item-label"),
							jqNew.data("paste-item-label"));
					
					let eiTypeIds: string[] = jqNew.data("ei-type-range");
					if (clipboard && eiTypeIds) {
						addControlFactory.pasteStrategy = {
							clipboard: clipboard,
							pastableEiTypeIds: eiTypeIds
						};
					}
				}
				
				toOneEmbedded = new ToOneEmbedded(jqToOne, addControlFactory, clipboard/*, !toOneSelector*/);
				
				jqCurrent.children(".rocket-impl-entry").each(function () {
					toOneEmbedded.currentEntry = new EmbeddedEntry($(this), toOneEmbedded.isReadOnly(), false, !!clipboard);
				});
				jqNew.children(".rocket-impl-entry").each(function () {
					toOneEmbedded.newEntry = new EmbeddedEntry($(this), toOneEmbedded.isReadOnly(), false);
				});
				jqDetail.children(".rocket-impl-entry").each(function () {
					toOneEmbedded.currentEntry = new EmbeddedEntry($(this), true, false, !!clipboard);
				});
			}
		
			toOne = new ToOne(toOneSelector, toOneEmbedded);
			jqToOne.data("rocketImplToOne", toOne);		
			
			return toOne;
		}
	}
	
	class ToOneEmbedded {
		private jqToOne: JQuery<Element>;
		public addControlFactory: AddControlFactory|null;
		private reduceEnabled: boolean = true;
		private _currentEntry: EmbeddedEntry;
		private _newEntry: EmbeddedEntry;
		private jqEmbedded: JQuery<Element>;
		private jqEntries: JQuery<Element>;
		private expandZone: cmd.Zone = null;
		private closeLabel: string;
		private changedCallbacks: Array<() => any> = new Array<() => any>();
		
		constructor(jqToOne: JQuery, addControlFactory: AddControlFactory = null, private clipboard: Clipboard = null/*, 
				private panelMode: boolean = false*/) {
			this.jqToOne = jqToOne;
			this.addControlFactory = addControlFactory;
			this.reduceEnabled = (true == jqToOne.data("reduced"));
			this.closeLabel = jqToOne.data("close-label");
			
			this.jqEmbedded = $("<div />", {
				"class": "rocket-impl-embedded"
			});
			this.jqToOne.append(this.jqEmbedded);
			
			this.jqEntries = $("<div />");
			this.jqEmbedded.append(this.jqEntries);
			
			this.initClipboard();

			this.changed();
		}
		
		public isReadOnly(): boolean {
			return this.addControlFactory === null;
		}
		
		private addControl: AddControl;
		private addGroup: Display.StructureElement;
		private firstReplaceControl: AddControl;
		private secondReplaceControl: AddControl;
		
		private changed() {
			if (this.addControlFactory === null) return;
			
			if (!this.addControl) {
				this.addControl = this.createAddControl();
			}
			
//			if (!this.firstReplaceControl) {
//				this.firstReplaceControl = this.createReplaceControl(true);
//			}
//			
//			if (!this.secondReplaceControl) {
//				this.secondReplaceControl = this.createReplaceControl(false);
//			}
			
			if (this.currentEntry || this.newEntry) {
				this.addControl.jQuery.hide();
				if (this.addGroup) {
					this.addGroup.hide();
				}
//				if (this.isExpanded()) {
//					this.firstReplaceControl.jQuery.show();
//				} else {
//					this.firstReplaceControl.jQuery.hide();
//				}
//				this.secondReplaceControl.jQuery.show();
			} else {
				this.addControl.jQuery.show();
				if (this.addGroup) {
					this.addGroup.show();
				}
//				this.firstReplaceControl.jQuery.hide();
//				this.secondReplaceControl.jQuery.hide();
			}
			
			this.triggerChanged();

			Rocket.scan();
		}
		
//		private createReplaceControl(prepend: boolean): AddControl {
//			var addControl = this.addControlFactory.createReplace();
//				
//			if (prepend) {
//				this.jqEmbedded.prepend(addControl.jQuery);
//			} else {
//				this.jqEmbedded.append(addControl.jQuery);
//			}
//			
//			addControl.onNewEmbeddedEntry((newEntry: EmbeddedEntry) => {
//				this.newEntry = newEntry;
//			});
//			return addControl;
//		}
		
		private createAddControl(): AddControl {
			var addControl = this.addControlFactory.createAdd();

			let jqAdd = addControl.jQuery;

//			if (this.panelMode) {
//				this.addGroup = Display.StructureElement.from($("<div />"), true);
//				this.addGroup.title = this.jqToOne.data("display-item-label");
//				this.addGroup.type = Display.StructureElement.Type.LIGHT_GROUP;
//				this.addGroup.contentJq.append(jqAdd);
//				
//				jqAdd = this.addGroup.jQuery;
//			}
			
			this.jqEmbedded.append(jqAdd);
			addControl.onNewEmbeddedEntry((newEntry: EmbeddedEntry) => {
				this.newEntry = newEntry;
				if (!this.isExpanded()) {
					this.expand();
				}
			});
			
			return addControl;
		}
		
		get currentEntry(): EmbeddedEntry {
			return this._currentEntry;
		}
		
		set currentEntry(entry: EmbeddedEntry) {
			if (this._currentEntry === entry) return;
			
			if (this._currentEntry) {
				this._currentEntry.dispose();
			}
				
			this._currentEntry = entry;
			
			if (!entry) return;
			
			if (this.newEntry) {
				this._currentEntry.jQuery.detach();
			}
		
			entry.onRemove(() => {
				this._currentEntry.dispose();
				this._currentEntry = null;
				this.changed();
			});
			
			
			this.initCopy(entry);
			
			this.initEntry(entry);
			this.changed();
		}
		
		get newEntry(): EmbeddedEntry {
			return this._newEntry;
		}
		
		set newEntry(entry: EmbeddedEntry) {
			if (this._newEntry === entry) return;
			
			if (this._newEntry) {
				this._newEntry.dispose();
			}
				
			this._newEntry = entry;
			
			if (!entry) return;
			
			if (this.currentEntry) {
				this.currentEntry.jQuery.detach();
			}
	
			entry.onRemove(() => {
				this._newEntry.dispose();
				this._newEntry = null;
				
				if (this.currentEntry) {
					this.currentEntry.jQuery.appendTo(<JQuery<HTMLElement>> this.jqEntries);
				}
				
				this.changed();
			});
			
			this.initEntry(entry);
			this.changed();
		}
	
		private initEntry(entry: EmbeddedEntry) {
			this.jqEntries.append(entry.jQuery);
			
			if (this.isExpanded()) {
				entry.expand(true);
			} else {
				entry.reduce();
			}
			
			entry.onFocus(() => {
				this.expand();
			});
		}
		
		public isExpanded(): boolean {
			return this.expandZone !== null || !this.reduceEnabled;
		}
		
		public expand() {
			if (this.isExpanded()) return;
			
			this.expandZone = Rocket.getContainer().createLayer(cmd.Zone.of(this.jqToOne))
					.createZone(window.location.href);
			this.jqEmbedded.detach();

			let contentJq = $("<div />", { "class": "rocket-content" }).append(this.jqEmbedded);
			this.expandZone.applyContent(contentJq);
			$("<header></header>").insertBefore(contentJq);
			
			this.expandZone.layer.pushHistoryEntry(window.location.href);

			if (this.newEntry) {
				this.newEntry.expand(true);
			}
			
			if (this.currentEntry) {
				this.currentEntry.expand(true);
			}
			
			var jqCommandButton = this.expandZone.menu.mainCommandList
					.createJqCommandButton({ iconType: "fa fa-trash-o", label: this.closeLabel, severity: display.Severity.WARNING } , true);
			jqCommandButton.click(() => {
				this.expandZone.layer.close();
			});
			
			this.expandZone.on(cmd.Zone.EventType.CLOSE, () => {
				this.reduce();
			});
			
			this.changed();
		}
		
		public reduce() {
			if (!this.isExpanded()) return;
			
			this.expandZone = null;
			
			this.jqEmbedded.detach();
			this.jqToOne.append(this.jqEmbedded);
			
			if (this.newEntry) {
				this.newEntry.reduce();
			}
			
			if (this.currentEntry) {
				this.currentEntry.reduce();
			}
			
			this.changed();
		}
		
		private triggerChanged() {
			for (let callback of this.changedCallbacks) {
				callback();
			}
		}
		
		public whenChanged(callback: () => any) {
			this.changedCallbacks.push(callback);
		}
		
		private syncing: boolean = false;
	
		private initCopy(entry: EmbeddedEntry) {
			if (!this.clipboard || !entry.copyable) return;
			
			let diEntry = entry.entry;
			if (!diEntry) {
				throw new Error("No display entry available.");
			}
			
			entry.copied = this.clipboard.contains(diEntry.eiTypeId, diEntry.pid)
			
			entry.onCopy(() => {
				if (this.syncing) return;
				
				this.syncing = true;
				
				if (!entry.copied) {
					this.clipboard.remove(diEntry.eiTypeId, diEntry.pid);
				} else {
					this.clipboard.clear();
					this.clipboard.add(diEntry.eiTypeId, diEntry.pid, diEntry.identityString);
				}
				
				this.syncing = false;
			});
		}
		
		private initClipboard() {
			if (!this.clipboard) return;
			
			let onChanged = () => {
				this.syncCopy();
			};
			
			this.clipboard.onChanged(onChanged);
			Cmd.Zone.of(this.jqToOne).page.on("disposed", () => {
				if (this.addControl) {
					this.addControl.dispose();
				}
				if (this.addGroup) {
					this.addGroup.jQuery.remove();
				}
				
				this.clipboard.offChanged(onChanged);
			});
		}
		
		private syncCopy() {
			if (!this.currentEntry || !this.currentEntry.copyable) return;
			
			if (this.syncing) return;
			
			let diEntry = this.currentEntry.entry;
			if (!diEntry) {
				throw new Error("No display entry available.");
			}
			
			this.syncing = true;
			
			if (this.clipboard.contains(diEntry.eiTypeId, diEntry.pid)) {
				this.currentEntry.copied = true;
			} else {
				this.currentEntry.copied = false;
			}
			
			this.syncing = false;
		}
	}
	
	
	
	class ToOneSelector {
		private jqInput: JQuery<Element>;
		private originalPid: string;
		private identityStrings: { [key: string]: string};
		private jqSelectedEntry: JQuery<Element>;
		private jqEntryLabel: JQuery<Element>;
		private browserLayer: cmd.Layer = null;
		private browserSelectorObserver: Display.SingleEntrySelectorObserver = null;
		private resetButtonJq: JQuery<Element>;
		
		constructor(private jqElem: JQuery) {
			this.jqElem = jqElem;
			this.jqInput = jqElem.children("input").hide();
			
			this.originalPid = jqElem.data("original-ei-id");
			this.identityStrings = jqElem.data("identity-strings");
			
			this.init();
			this.selectEntry(this.selectedPid);
		}
		
		get jQuery(): JQuery {
			return this.jqElem;
		}
		
		get selectedPid(): string {
			let pid: string = this.jqInput.val().toString();
			if (pid.length == 0) return null;
			
			return pid;
		}	
		
		private init() {
			this.jqSelectedEntry = $("<div />")
			this.jqSelectedEntry.append(this.jqEntryLabel = $("<span />", { "text": this.identityStrings[this.originalPid] }));
			new display.CommandList($("<div />").appendTo(<JQuery<HTMLElement>> this.jqSelectedEntry), true)
					.createJqCommandButton({ iconType: "fa fa-trash-o", label: this.jqElem.data("remove-entry-label") })
					.click(() => {
						this.clear();				
					});
			this.jqElem.append(this.jqSelectedEntry);
			
			var jqCommandList = $("<div />");
			this.jqElem.append(jqCommandList);
			
			var commandList = new display.CommandList(jqCommandList);
			
			commandList.createJqCommandButton({ label: this.jqElem.data("select-label") })
					.mouseenter(() => {
						this.loadBrowser();
					})
					.click(() => {
						this.openBrowser();
					});
			
			this.resetButtonJq = commandList.createJqCommandButton({ label: this.jqElem.data("reset-label") })
					.click(() => {
						this.reset();
					}).hide();
		}
		
		private selectEntry(pid: string, identityString: string = null) {
			this.jqInput.val(pid);
			
			if (pid === null) {
				this.jqSelectedEntry.hide();
				return;
			}
			
			this.jqSelectedEntry.show();
			
			if (identityString === null) {
				identityString = this.identityStrings[pid];
			}
			this.jqEntryLabel.text(identityString);
			
			if (this.originalPid != this.selectedPid) {
				this.resetButtonJq.show();
			} else {
				this.resetButtonJq.hide();
			}
		}
		
		public reset() {
			this.selectEntry(this.originalPid);
		}
		
		public clear() {
			this.selectEntry(null);
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
				
				that.iniBrowserPage(zone);
				zone.on(Cmd.Zone.EventType.CONTENT_CHANGED, () => {
					this.iniBrowserPage(zone);
				});
			});
		}
		
		private iniBrowserPage(zone: cmd.Zone) {
			if (this.browserLayer === null) return;
			
			var ocs = Impl.Overview.OverviewPage.findAll(zone.jQuery);
			if (ocs.length == 0) return;
			
			ocs[0].initSelector(this.browserSelectorObserver = new Display.SingleEntrySelectorObserver());
			
			zone.menu.zoneCommandsJq.find(".rocket-important").removeClass("rocket-important");
			
			zone.menu.partialCommandList.createJqCommandButton({ 
				label: this.jqElem.data("select-label"), 
				severity: Display.Severity.PRIMARY, 
				important: true
			}).click(() => {
				this.updateSelection();
				zone.layer.hide();
			});
			zone.menu.partialCommandList.createJqCommandButton({ label: this.jqElem.data("cancel-label") }).click(() => {
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
			
			this.browserSelectorObserver.setSelectedId(this.selectedPid);
		}
		
		private updateSelection() {
			if (this.browserSelectorObserver === null) return;
			
			this.clear();
			
			this.browserSelectorObserver.getSelectedIds().forEach((id) => {
				var identityString = this.browserSelectorObserver.getIdentityStringById(id);
				if (identityString !== null) {
					this.selectEntry(id, identityString);
					return;
				}
				
				this.selectEntry(id);
			});
		}
	}
}