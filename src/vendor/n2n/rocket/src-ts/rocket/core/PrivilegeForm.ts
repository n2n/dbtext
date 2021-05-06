namespace Rocket.Core {
	export class PrivilegeForm {
	    private addButtonJq: JQuery<Element>;
	    private unusedPrivileges: Privilege[] = [];
	    
		constructor(private formJq: JQuery<Element>) {
		}
		
		setup() {
		    this.formJq.find(".rocket-privilege").each((i, elem) => {
		        let p = new Privilege($(elem));
		        p.setup();
		        if (!p.used) {
		            this.unusedPrivileges.push(p);
		        }
		    });
		    
		    this.addButtonJq = Cmd.Zone.of(this.formJq).menu.mainCommandList
                .createJqCommandButton({
                    label: this.formJq.data("rocket-add-privilege-label")
                })
                .click(() => {
                    this.incrPrivileges();
                    this.updateButton();
                });
		}
		
		private incrPrivileges() {
		    if (this.unusedPrivileges.length == 0) {
		        return;
		    }
		    
		    let privilege = this.unusedPrivileges.shift();
		    privilege.used = true;
		}
		
		private updateButton() {
		    if (this.unusedPrivileges.length == 0) {
		        this.addButtonJq.find("span").text(this.formJq.data("rocket-save-first-info"))
		        this.addButtonJq.prop("disabled", true);
		    }
		}
	}
	
	export class Privilege {
	    private structureElement: Display.StructureElement;
        private enablerJq: JQuery<Element>;
        private restrictionsJq: JQuery<Element>;
        private restrictionsEnablerJq: JQuery<Element>;
	    
	    constructor(containerJq: JQuery<Element>) {
	        this.structureElement = Display.StructureElement.from(containerJq, true);
	        this.enablerJq = containerJq.find("input.rocket-privilege-enabler");
	        this.restrictionsJq = containerJq.find(".rocket-restrictions:first");
	        this.restrictionsEnablerJq = containerJq.find(".rocket-restrictions-enabler:first")
	        this.restrictionsEnablerJq.on("change", () => {
	           this.checkVisibility(); 
	        });
	    }
	    
	    get used(): boolean {
	        return this.enablerJq.is(":checked");
	    }
	    
	    set used(used: boolean) {
	        this.enablerJq.prop("checked", used);
	        this.checkVisibility();
	    }
	    
	    private checkVisibility() {
	        if (this.used) {
                this.structureElement.show(false)
            } else {
                this.structureElement.hide();
            }
	        
	        if (this.restrictionsEnablerJq.is(":checked")) {
	            this.restrictionsJq.show();
	        } else {
	            this.restrictionsJq.hide();
	        }
	    }
	    
	    setup() {
	        this.enablerJq.hide();
	        
	        this.checkVisibility();
	        
	        this.structureElement.getToolbar(true).show().getCommandList()
	                .createJqCommandButton({
	                    iconType: "fa fa-trash",
	                    label: "Remove"
	                       
	                })
	                .click(() => {
	                    this.used = false;
	                    this.structureElement.contentJq.remove();
	                });
	    }
	}
}
