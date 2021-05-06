namespace Rocket.Core {
    export class CommandPrivilegeList {
        
        constructor(private containerJq: JQuery<Element>) {
        }
        
        listen() {
           this.containerJq.find('li').each((i, elem: HTMLElement) => {
              let cpl = new CpListener($(elem));
              cpl.check();
              cpl.listen();
           });
        }
    }
    
    class CpListener {
        private checkJq: JQuery<Element>;
        private decendentChecksJq: JQuery<Element>;
        
        constructor(private elemJq: JQuery<Element>) {
            this.checkJq = this.elemJq.children("input[type=checkbox]")
            this.decendentChecksJq = this.elemJq.find("li input[type=checkbox]");
        }
        
        listen() {
            this.checkJq.change(() =>  {
                this.check();
            });
        }
        
        check() {
            if (this.elemJq.is(":disabled")) return;
            
            if (this.checkJq.is(":checked")) {
                this.decendentChecksJq.prop("disabled", true);
                this.decendentChecksJq.prop("checked", true);
            } else {
                this.decendentChecksJq.prop("disabled", false);
                this.decendentChecksJq.prop("checked", false);
            }
        }
    }
}
