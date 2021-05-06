namespace Rocket.Impl.Translation {

	export class LoadJobExecuter {
		private groups: LoadJobGroup[] = [];
		
		constructor() {
		}
		
		add(loadJob: LoadJob) {
			for (let group of this.groups) {
				if (group.add(loadJob)) return;
			}
			
			this.groups.push(LoadJobGroup.create(loadJob));
		}
		
		exec() {
			for (let group of this.groups) {
				group.exec();
			}
			this.groups = [];
		}
		
		static create(translatables: Translatable[]): LoadJobExecuter {
			let lje = new LoadJobExecuter();
			for (let translatable of translatables) {
				for (let lj of translatable.loadJobs) {
					lje.add(lj);
				}
			}
			return lje;
		}
	}
	
	class LoadJobGroup {
		loadJobs: LoadJob[] = [];
		
		constructor(private url: Jhtml.Url) {
		}
		
		add(loadJob: LoadJob): boolean {
			if (!this.url.equals(loadJob.url)) {
				return false;
			}
			
			this.loadJobs.push(loadJob);
			return true;
		}
		
		exec() {
			let guiFieldPaths: string[] = [];
			
			for (let loadJob of this.loadJobs) {
				guiFieldPaths.push(loadJob.guiFieldPath);
				loadJob.content.loading = true;
			}
			
			let url = this.url.extR(null, { guiFieldPaths: guiFieldPaths });
			
			Jhtml.lookupModel(url).then((result) => {
				this.splitResult(result.model.snippet);
			});
		}
		
		private splitResult(snippet: Jhtml.Snippet) {
			let usedElements: Element[] = [];
		
			$(snippet.elements).children().each((i, elem) => {
				let elemJq = $(elem);
				let guiFieldPath = elemJq.data("rocket-impl-gui-field-path");
				
				let loadJob = this.loadJobs.find(loadJob => loadJob.guiFieldPath == guiFieldPath);
				let newContentJq = elemJq.children().first();
				
				loadJob.content.replaceField(newContentJq);
				loadJob.content.loading = false;
				usedElements.push(newContentJq.get(0));
			});
			
			snippet.elements = usedElements;
			snippet.markAttached();
		}
		
		static create(loadJob: LoadJob) {
			let lj = new LoadJobGroup(loadJob.url);
			lj.add(loadJob);
			return lj;
		}
	}
}