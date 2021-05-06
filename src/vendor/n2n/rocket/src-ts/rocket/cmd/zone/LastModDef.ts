namespace Rocket.Cmd {
	export class LastModDef {
		supremeEiTypeId: string;
		pid?: string;
		draftId?: number;
		
		static createLive(supremeEiTypeId: string, pid: string): LastModDef {
			let lmd = new LastModDef();
			lmd.supremeEiTypeId = supremeEiTypeId;
			lmd.pid = pid;
			return lmd;
		}
		
		static createDraft(supremeEiTypeId: string, draftId: number): LastModDef {
			let lmd = new LastModDef();
			lmd.supremeEiTypeId = supremeEiTypeId;
			lmd.draftId = draftId;
			return lmd;
		}
		
		static fromEntry(entry: Display.Entry): LastModDef {
			let lastModDef = new LastModDef();
			lastModDef.supremeEiTypeId = entry.supremeEiTypeId;
			lastModDef.pid = entry.pid;
			lastModDef.draftId = entry.draftId;
			return lastModDef;
		}
	}
}