import { SafeStyle, DomSanitizer } from '@angular/platform-browser';
import { SiPanel } from '../../model/si-panel';

export class PanelLayout {

	private numGridRows = 0;
	private numGridCols = 0;

	constructor(private san: DomSanitizer) {
	}

	registerPanel(panel: SiPanel) {
		const gridPos = panel.gridPos;
		if (gridPos === null) {
			return;
		}

		const colEnd = gridPos.colEnd;
		if (this.numGridCols < colEnd) {
			this.numGridCols = colEnd;
		}

		const rowEnd = gridPos.rowEnd;
		if (this.numGridRows < rowEnd) {
			this.numGridRows = rowEnd;
		}
	}

	hasGrid(): boolean {
		return this.numGridRows > 0 || this.numGridCols > 0;
	}

	style(): SafeStyle {
		if (!this.hasGrid()) {
			return null;
		}

		return this.san.bypassSecurityTrustStyle('display: grid; grid-template-columns: repeat('
				+ (this.numGridCols - 1) + ', 1fr');
	}

	styleOf(panel: SiPanel): SafeStyle {
		if (!panel.gridPos) {
			return null;
		}

		return this.san.bypassSecurityTrustStyle('grid-column-start: ' + panel.gridPos.colStart
				+ '; grid-column-end: ' + panel.gridPos.colEnd
				+ '; grid-row-start: ' + panel.gridPos.rowStart
				+ '; grid-row-end: ' + panel.gridPos.rowEnd);
	}
}
