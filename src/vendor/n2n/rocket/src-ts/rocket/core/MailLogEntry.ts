namespace Rocket.Core {
 
    
    export class MailLogEntry {
        private contentJq: JQuery<Element>;
        private expanded = false;
    
        constructor(private containerJq: JQuery<Element>) {
            this.contentJq = containerJq.children("dl:first");
            this.contentJq.hide();
        }
        
        listen() {
            this.containerJq.children("header:first").click(() => {
                this.toggle();
            });
        }
        
        toggle() {
            if (this.expanded) {
                this.minimize();
            } else {
                this.expand();
            }
            
        }
        
        expand() {
            if (this.expanded) return;
            
            this.contentJq.slideDown(100);
            this.expanded = true;
            this.containerJq.addClass("rocket-expaned");
        }
        
        minimize() {
            if (!this.expanded) return;
            
            this.contentJq.slideUp(100);
            this.expanded = false;
            this.containerJq.removeClass("rocket-expaned");
        }
        
    }
    
    export class MailPaging {
        
        constructor(private selectJq: JQuery<Element>) {
        }
        
        listen() {
            this.selectJq.change(() => {
               window.location.href = <string> this.selectJq.val(); 
            });
        }
    }
}


