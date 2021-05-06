namespace Rocket.Cmd {
	
	export class ZoneClearer {
		constructor(private zones: Zone[]) {
			
		}
		
		clearBySupremeEiType(supremeEiTypeId: string, restrictToCollections: boolean) {
			for (let zone of this.zones) {
				if (!zone.page || zone.page.config.frozen || zone.page.disposed) {
					continue;
				}
				
				if (!restrictToCollections) {
					if (Display.Entry.hasSupremeEiTypeId(zone.jQuery, supremeEiTypeId)) {
						zone.page.dispose();
					}
					
					return;
				}
				
				if (Display.Collection.hasSupremeEiTypeId(zone.jQuery, supremeEiTypeId)) {
					zone.page.dispose();
				}
			}
		}
		
		clearByPid(supremeEiTypeId: string, pid: string, remove: boolean) {
			for (let zone of this.zones) {
				if (!zone.page || zone.page.disposed) continue;

				if (remove && this.removeByPid(zone, supremeEiTypeId, pid)) {
					continue;
				}
				
				if (zone.page.config.frozen) continue;
				
				let jqElem = zone.jQuery;
				
				if (Display.Entry.hasPid(zone.jQuery, supremeEiTypeId, pid)) {
					zone.page.dispose();
				}
			}
		}
		
		private removeByPid(zone: Zone, supremeEiTypeId: string, pid: string): boolean {
			let entries = Display.Entry.findByPid(zone.jQuery, supremeEiTypeId, pid);
			if (entries.length == 0) return true;
			
			let success = true;
			for (let entry of entries) {
				if (entry.collection) {
					entry.dispose();
				} else {
					success = false;
				}
			}
			return success;
		}
		
		clearByDraftId(supremeEiTypeId: string, draftId: number, remove: boolean) {
			for (let zone of this.zones) {
				if (!zone.page || zone.page.disposed) continue;
				
				if (remove && this.removeByDraftId(zone, supremeEiTypeId, draftId)) {
					continue;
				}
				
				if (zone.page.config.frozen) continue;
				
				if (Display.Entry.hasDraftId(zone.jQuery, supremeEiTypeId, draftId)) {
					zone.page.dispose();
				}
			}
		}
		
		private removeByDraftId(zone: Zone, supremeEiTypeId: string, draftId: number): boolean {
			let entries = Display.Entry.findByDraftId(zone.jQuery, supremeEiTypeId, draftId);
			if (entries.length == 0) return true;
			
			let success = true;
			for (let entry of entries) {
				if (entry.collection) {
					entry.dispose();
				} else {
					success = false;
				}
			}
			return success;
		}
	}
}