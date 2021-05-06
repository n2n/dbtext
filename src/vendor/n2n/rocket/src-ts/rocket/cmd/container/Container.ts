namespace Rocket.Cmd {
	import display = Rocket.Display;
	
	export class Container {
		private jqContainer: JQuery<Element>;
		private _layers: Array<Layer>;
		private layerCallbackRegistery: Rocket.Util.CallbackRegistry<LayerCallback> = new Rocket.Util.CallbackRegistry<LayerCallback>();
		
		constructor(jqContainer: JQuery<Element>) {
			this.jqContainer = jqContainer;
			this._layers = new Array<Layer>();
			
			var layer = new Layer(this.jqContainer.find(".rocket-main-layer"), this._layers.length, this, 
					Jhtml.getOrCreateMonitor());
			this.registerLayer(layer);
			
			
			jQuery(document).keyup((e) => {
				if (e.keyCode == 27 && !$(e.target).is("input, textarea, button")) { 
					this.closePopup();
			    }
			});
		}
		
		closePopup() {
			if (this.currentLayer.level == 0) return;
			
			this.currentLayer.close();
		}

		get layers(): Array<Layer> {
			return this._layers.slice();
		}
		
//		public handleError(url: string, html: string) {
//			var stateObj = { 
//				"type": "rocketErrorPage",
//				"url": url
//			};
//			
//			if (this.jqErrorLayer) {
//                this.jqErrorLayer.remove();
//				history.replaceState(stateObj, "n2n Rocket", url);
//			} else {
//				history.pushState(stateObj, "n2n Rocket", url);
//			}
//			
//			this.jqErrorLayer = $("<div />", { "class": "rocket-error-layer" });
//			this.jqErrorLayer.css({ "position": "fixed", "top": 0, "left": 0, "right": 0, "bottom": 0 });
//			this.jqContainer.append(this.jqErrorLayer);
//			
//			var iframe = document.createElement("iframe");
//			this.jqErrorLayer.append(iframe);
//			
//			iframe.contentWindow.document.open();
//			iframe.contentWindow.document.write(html);
//			iframe.contentWindow.document.close();
//			
//			$(iframe).css({ "width": "100%", "height": "100%", "background": "white" });
//		}
	
		get mainLayer(): Layer {
			if (this._layers.length > 0) {
				return this._layers[0];
			}
			
			throw new Error("Container empty.");
		}
		
		private markCurrent() {
			for (let layer of this._layers) {
				layer.active = false;
			}
			
			this.currentLayer.active = true;
		}
		
		get currentLayer(): Layer {
			if (this._layers.length == 0) {
				throw new Error("Container empty.");
			}
			
			var layer = null;
			for (let i in this._layers) {
				if (this._layers[i].visible) {
					layer = this._layers[i];
				}
			}
			
			if (layer !== null) return layer;
			
			return this._layers[this._layers.length - 1];
		}
		
		private unregisterLayer(layer: Layer) {
			var i = this._layers.indexOf(layer);
			if (i < 0) return;
			
			this._layers.splice(i, 1);
			
			this.layerTrigger(Container.LayerEventType.REMOVED, layer);
		}
		
		private registerLayer(layer: Layer) {
			let lastModDefs: LastModDef[] = [];
			let messages: Message[] = [];
			layer.monitor.onDirective((evt) => { 
				 lastModDefs = this.deterLastModDefs(evt.directive);
				 messages = this.deterMessages(evt.directive);
			});
			layer.monitor.onDirectiveExecuted((evt) => { 
				if (!layer.currentZone) return;
				
				if (lastModDefs.length > 0) {
					layer.currentZone.lastModDefs = lastModDefs; 
				}
				
				// quick fix because of spinning message
				if (messages.length > 0 && !layer.currentZone.isLoading()) {
					layer.currentZone.messageList.clear()
					layer.currentZone.messageList.addAll(messages);
				}
			});
			
			this._layers.push(layer);
						
			this.markCurrent();
		}
		
		private deterLastModDefs(directive: Jhtml.Directive): Cmd.LastModDef[] {
			let data = directive.getAdditionalData();
			
			if (!data || !data.rocketEvent || !data.rocketEvent.eiMods) return [];
			
			let lastModDefs: Array<Cmd.LastModDef> = [];
			let zoneClearer = new ZoneClearer(this.getAllZones());
			
			let eiMods = data.rocketEvent.eiMods;
			for (let supremeEiTypeId in eiMods) {
				if (!eiMods[supremeEiTypeId].pids && eiMods[supremeEiTypeId].draftIds) {
					zoneClearer.clearBySupremeEiType(supremeEiTypeId, false);
					continue;
				}
				
				if (eiMods[supremeEiTypeId].pids) {
					for (let pid in eiMods[supremeEiTypeId].pids) {
						let modType = eiMods[supremeEiTypeId].pids[pid];
						switch (modType) {
							case "changed":
								zoneClearer.clearByPid(supremeEiTypeId, pid, false);
								lastModDefs.push(Cmd.LastModDef.createLive(supremeEiTypeId, pid));
								break;
							case "removed":
								zoneClearer.clearByPid(supremeEiTypeId, pid, true);
								break;
							case "added":
								zoneClearer.clearBySupremeEiType(supremeEiTypeId, true);
								lastModDefs.push(Cmd.LastModDef.createLive(supremeEiTypeId, pid));
								break;
							default: 
								throw new Error("Invalid mod type " + modType);
						}
						
					}
				}
				
				if (eiMods[supremeEiTypeId].draftIds) {
					for (let draftIdStr in eiMods[supremeEiTypeId].draftIds) {
						let draftId = parseInt(draftIdStr);
						let modType = eiMods[supremeEiTypeId].draftIds[draftIdStr];
						switch (modType) {
							case "changed":
								zoneClearer.clearByDraftId(supremeEiTypeId, draftId, false);
								lastModDefs.push(Cmd.LastModDef.createDraft(supremeEiTypeId, draftId));
								break;
							case "removed":
								zoneClearer.clearByDraftId(supremeEiTypeId, draftId, true);
								break;
							case "added":
								zoneClearer.clearBySupremeEiType(supremeEiTypeId, true);
								lastModDefs.push(Cmd.LastModDef.createDraft(supremeEiTypeId, draftId));
								break;
							default: 
								throw new Error("Invalid mod type " + modType);
						}
					}
				}
			}
			
			return lastModDefs;
		}
		
		private deterMessages(directive: Jhtml.Directive): Cmd.Message[] {
			let data = directive.getAdditionalData();

			if (!data || !data.rocketEvent || !data.rocketEvent.messages) return [];
			
			
			
			let messages = [];
			for (let message of data.rocketEvent.messages) {
				messages.push(new Message(message.text, message.severity));
			}
			return messages;
		}
		
		public createLayer(dependentZone: Zone = null): Layer {
			var jqLayer = $("<div />", {
				"class": "rocket-layer"
			});
			
			this.jqContainer.append(jqLayer);

			var layer = new Layer(jqLayer, this._layers.length, this, 
					Jhtml.Monitor.create(jqLayer.get(0), new Jhtml.History(), true));
			this.registerLayer(layer);
			
			var jqToolbar = $("<div />", {
				"class": "rocket-layer-toolbar"
			});
			jqLayer.append(jqToolbar);
			
			var jqButton = $("<button />", { 
				"class": "btn btn-warning"
			}).append($("<i />", {
				"class": "fa fa-times"
			})).click(function () {
				layer.close();
			});
			jqToolbar.append(jqButton);
			
			var that = this;
			layer.on(Layer.EventType.CLOSE, () => {
				that.unregisterLayer(layer);
				this.markCurrent();
			});
			layer.on(Layer.EventType.SHOWED, () => {
				this.markCurrent();
			});
			layer.on(Layer.EventType.HIDDEN, () => {
				this.markCurrent();
			});
			
			if (dependentZone === null) {
				this.layerTrigger(Container.LayerEventType.ADDED, layer);
				return layer;
			}
			
			let reopenable = false;
			dependentZone.on(Zone.EventType.CLOSE, () => {
				layer.close();
			});
			dependentZone.on(Zone.EventType.CONTENT_CHANGED, () => {
				layer.close();
			});
			dependentZone.on(Zone.EventType.HIDE, () => {
				reopenable = layer.visible;
				layer.hide();
			});
			dependentZone.on(Zone.EventType.SHOW, () => {
				if (!reopenable) return;
				
				layer.show();
			});
			
			this.layerTrigger(Container.LayerEventType.ADDED, layer);
			return layer;
		}
			
		public getAllZones(): Array<Zone> {
			var zones = new Array<Zone>();
			
			for (var i in this._layers) {
				var layerZones = this._layers[i].zones; 
				for (var j in layerZones) {
					zones.push(layerZones[j]);
				}
			}
			
			return zones;
		}
		
		private layerTrigger(eventType: Container.LayerEventType, layer: Layer) {
			var container = this;
			this.layerCallbackRegistery.filter(eventType.toString())
					.forEach(function (callback: LayerCallback) {
						callback(layer);
					});
		}
		
		public layerOn(eventType: Container.LayerEventType, callback: LayerCallback) {
			this.layerCallbackRegistery.register(eventType.toString(), callback);
		}
		
		public layerOff(eventType: Zone.EventType, callback: LayerCallback) {
			this.layerCallbackRegistery.unregister(eventType.toString(), callback);
		}
	}
	
	export namespace Container {
		export enum LayerEventType {
			REMOVED /*= "removed"*/,
			ADDED /*= "added"*/
		}
	}
}