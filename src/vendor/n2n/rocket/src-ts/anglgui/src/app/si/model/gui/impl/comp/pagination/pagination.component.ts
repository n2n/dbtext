import { Component, OnInit } from '@angular/core';
import { PaginationModel } from '../pagination-model';

@Component({
	selector: 'rocket-si-pagination.rocket-pagination',
	templateUrl: './pagination.component.html',
	styleUrls: ['./pagination.component.css']
})
export class PaginationComponent implements OnInit {
	public model: PaginationModel;

	constructor() { }

	ngOnInit(): void {
	}

	get visible(): boolean {
		return this.model && this.model.pagesNum && this.model.pagesNum > 1;
	}

	get pagesNum(): number {
		return this.model.pagesNum;
	}

	get currentPageNo(): number {
		return this.model.currentPageNo;
	}

	set currentPageNo(pageNo: number) {
		this.model.currentPageNo = pageNo;
	}
}
