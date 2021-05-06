//namespace Rocket.Cmd {
//	
//	export class Executor {
//		private container: Container;
//		
//		constructor(container: Container) {
//			this.container = container;
//		}
//		
//		private purifyExecConfig(config: ExecConfig): ExecConfig {
//			config.forceReload = config.forceReload === true;
//			config.showLoadingPage = config.showLoadingPage !== false
//			config.createNewLayer = config.createNewLayer === true
//			
//			if (!config.currentLayer) {
//				if (config.currentPage) {
//					config.currentLayer = config.currentPage.layer;
//				} else {
//					config.currentLayer = this.container.currentLayer;
//				}
//			}
//			
//			if (!config.currentPage) {
//				config.currentPage = null;
//			}
//			
//			return config;
//		}
//		
//		public exec(url: string|Url, config: ExecConfig = null) {
//			config = this.purifyExecConfig(config);
//			
//			var targetPage = null;
//			
//			if (!config.createNewLayer) {
//				targetPage = config.currentLayer.getPageByUrl(url);
//			}
//			
//			if (targetPage !== null) {
//				if (config.currentLayer.currentPage !== targetPage) {
//					config.currentLayer.pushHistoryEntry(targetPage.getUrl());
//				}
//				
//				if (!config.forceReload) {
//					if (config.done) {
//						setTimeout(function () { config.done(new ExecResult(null, targetPage)); }, 0);
//					}
//					
//					return;
//				}
//			}
//			
//			if (targetPage === null && config.showLoadingPage) {
//				targetPage = config.currentLayer.createPage(url);
//				config.currentLayer.pushHistoryEntry(url);
//			}
//			
//			if (targetPage !== null) {
//				targetPage.clear(true);
//			}
//		
//			var that = this;
//			$.ajax({
//				"url": url.toString(),
//				"dataType": "json"
//			}).fail(function (jqXHR, textStatus, data) {
//				if (jqXHR.status != 200) {
//                    config.currentLayer.container.handleError(url.toString(), jqXHR.responseText);
//					return;
//				}
//				
//				alert("Not yet implemented press F5 after ok.");
//			}).done(function (data, textStatus, jqXHR) {
//				that.analyzeResponse(config.currentLayer, data, url.toString(), targetPage);
//				
//				if (config.done) {
//					config.done(new ExecResult(null, targetPage));
//				}
//			});
//		}
//		
//		public analyzeResponse(currentLayer: Layer, response: Object, targetUrl: string, targetPage: Page = null): boolean {
//			if (typeof response["additional"] === "object") {
//				if (this.execDirectives(currentLayer, response["additional"])) {
//					if (targetPage !== null) targetPage.close();
//					return true;
//				}
//			}
//			
//			if (targetPage === null) {
//				targetPage = currentLayer.getPageByUrl(targetUrl);
//				if (targetPage !== null && !currentLayer.currentHistoryEntryUrl.equals(Url.create(targetUrl))) {
//					currentLayer.pushHistoryEntry(targetUrl);
//				}
//			}
//			
//			if (targetPage === null) {
//				targetPage = currentLayer.createPage(targetUrl);
//				currentLayer.pushHistoryEntry(targetUrl);
//			}
//			
//			targetPage.applyHtml(n2n.ajah.analyze(response));
//			n2n.ajah.update();
//		}
//		
//		private execDirectives(currentLayer: Layer, info: any) {
//			if (info.directive == "redirectBack") {
//				var index = currentLayer.currentHistoryIndex();
//				
//				if (index > 0) {
//					this.exec(currentLayer.getHistoryUrlByIndex(index - 1), { "currentLayer": currentLayer });
//					return true;
//				}
//		
//				if (info.fallbackUrl) {
//					this.exec(info.fallbackUrl, { "currentLayer": currentLayer });
//					return true;
//				}
//				
//				currentLayer.close();
//			}
//			
//			return false;
//		}
//	}
//	
//	export interface ExecConfig {
//		forceReload?: boolean; 
//		showLoadingPage?: boolean; 
//		createNewLayer?: boolean;
//		currentLayer?: Layer;
//		currentPage?: Page;
//		done?: (ExecResult) => any;
//	}
//	
//	export class ExecResult {
//		constructor(order, private _context: Page) {
//		}
//		
//		get context(): Page {
//			return this._context;
//		}
//	}
//}