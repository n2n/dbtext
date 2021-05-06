namespace Rocket.Cmd {
	
	export class Blocker {
		private jqContainer: JQuery<Element>;
		private jqBlocker: JQuery<Element> = null;
		
		constructor(private container: Container) {
			for (let layer of container.layers) {
				this.observeLayer(layer);
			}
			
			var that = this;
			container.layerOn(Container.LayerEventType.ADDED, function (layer: Layer) {
				that.observeLayer(layer);
				that.check();
			});
			
		}
		
		private observeLayer(layer: Layer) {
			for (let context of layer.zones) {
				this.observePage(context)
			}
			
			layer.onNewZone((context: Zone) => {
				this.observePage(context);
				this.check();
			});
		}
		
		private observePage(context: Zone) {
			var checkCallback = () => {
				this.check();
			}
			
			context.on(Zone.EventType.SHOW, checkCallback);
			context.on(Zone.EventType.HIDE, checkCallback);
			context.on(Zone.EventType.CLOSE, checkCallback);
			context.on(Zone.EventType.CONTENT_CHANGED, checkCallback);
			context.on(Zone.EventType.BLOCKED_CHANGED, checkCallback);
		}
		
		
		init(jqContainer: JQuery<Element>) {
			if (this.jqContainer) {
				throw new Error("Blocker already initialized.");
			}
			
			this.jqContainer = jqContainer;
			this.check();
		}
		
		
		private check() {
			if (!this.jqContainer || !this.container.currentLayer.currentZone) return;
			
			if (!this.container.currentLayer.currentZone.locked) {
				if (!this.jqBlocker) return;
				
				this.jqBlocker.remove();
				this.jqBlocker = null;
				return;	
			}
			
			if (this.jqBlocker) return;

			this.jqBlocker = 
					$("<div />", { 
						"class": "rocket-zone-block",
						"css": {
							"position": "fixed",
							"top": 0,
							"left": 0,
							"right": 0,
							"bottom": 0
						} 
					})
					.append($("<div />", { "class": "rocket-loader" }))
					.appendTo(<JQuery<HTMLElement>> this.jqContainer);
		}
	}
	
}