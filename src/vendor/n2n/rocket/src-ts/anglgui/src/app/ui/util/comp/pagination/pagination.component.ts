import {Component, ElementRef, EventEmitter, Input, OnInit, Output} from '@angular/core';

@Component({
	selector: 'rocket-ui-pagination',
	templateUrl: './pagination.component.html',
	styleUrls: ['./pagination.component.css']
})
export class PaginationComponent implements OnInit {

	@Input() totalPagesNo: number;
	@Input() currentPageNo = 1;
	@Output() currentPageNoChange = new EventEmitter<number>();

	constructor(elemRef: ElementRef) {
	elemRef.nativeElement.classList.add('rocket-pagination');
	}


	set internalPageNo(pageNo) {
		if (!this.validatePageNo(pageNo)) {
			return;
		}

		this.currentPageNo = pageNo;
		this.currentPageNoChange.emit(pageNo);
	}

	get internalPageNo(): number {
		return this.currentPageNo;
	}

	get lastPageNo(): number {
		return this.totalPagesNo;
	}

	private validatePageNo(pageNo: number) {
		if (pageNo > this.totalPagesNo) {
			this.currentPageNo = this.totalPagesNo;
			return false;
		}

		if (pageNo < 1) {
			this.currentPageNo = 1;
			return false;
		}

		return !isNaN(pageNo);
	}

	ngOnInit(): void {
	}
}
