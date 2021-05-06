<?php
	/*
	 * Copyright (c) 2012-2016, Hofmänner New Media.
	 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
	 *
	 * This file is part of the n2n module ROCKET.
	 *
	 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
	 * GNU Lesser General Public License as published by the Free Software Foundation, either
	 * version 2.1 of the License, or (at your option) any later version.
	 *
	 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
	 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
	 *
	 * The following people participated in this project:
	 *
	 * Andreas von Burg...........:	Architect, Lead Developer, Concept
	 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
	 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
	 */

	use n2n\impl\web\ui\view\html\HtmlView;
	
	$view = HtmlView::view($this);
	$html = HtmlView::html($view);

	$eiuEntries = $view->getParam('eiuEntries');
?>
<div class="rocket-impl-to-many"
		data-reduced="<?php $html->out($view->getParam('reduced')) ?>"
		data-close-label="<?php $html->text('common_apply_label') ?>"
		data-show-all-label="<?php $html->text('common_show_all_label') ?>">
	<div class="rocket-impl-entries">
		<?php foreach ($eiuEntries as $eiuEntry): ?>
			<?php $view->import('embeddedEntry.html', array('eiuEntry' => $eiuEntry, 'summaryRequired' => $view->getParam('reduced'))) ?>
		<?php endforeach ?>
	</div>
</div>
