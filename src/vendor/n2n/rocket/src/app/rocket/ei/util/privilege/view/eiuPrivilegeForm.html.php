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
	use n2n\web\dispatch\map\PropertyPath;
	use rocket\ei\util\privilege\EiuPrivilegeForm;
	use rocket\ei\util\privilege\view\EiuPrivilegeHtmlBuilder;
use rocket\ei\manage\RocketUiOutfitter;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);

	$eiuPrivilegeForm = $view->getParam('eiuPrivilegeForm');
	$view->assert($eiuPrivilegeForm instanceof EiuPrivilegeForm);
	
	$eiuPrivilegeHtml = new EiuPrivilegeHtmlBuilder($view);
	
// 	$eiuEntryFormViewModel->initFromView($view);
	
	$basePropertyPath = $eiuPrivilegeForm->getContextPropertyPath();
	if ($basePropertyPath === null) {
		$basePropertyPath = new PropertyPath(array());
	}
	
	$ruio = new RocketUiOutfitter();
?>
<div class="rocket-group rocket-simple-group">
	<label><?php $html->l10nText('user_command_privileges_label')?></label>
	
		
	<div class="rocket-structure-content rocket-command-privileges">
		<?php $eiuPrivilegeHtml->privilegeCheckboxes($basePropertyPath->ext('eiCommandPathStrs[]'), 
				$eiuPrivilegeForm->getPrivilegeDefinition()) ?>
	</div>
</div>

<div class="rocket-group rocket-simple-group">
	<label><?php $html->l10nText('user_prop_privileges_label')?></label>
	<div class="rocket-structure-content">
		<?php $formHtml->meta()->objectProps($basePropertyPath->ext('eiPropMagForm'), function() use ($formHtml, $ruio) { ?>
			<?php $formHtml->magOpen('div', null, array('class' => 'rocket-item'), $ruio) ?>
				<?php $formHtml->magLabel() ?>
				<div class="rocket-structure-content">
					<?php $formHtml->magField() ?>
				</div>
			<?php $formHtml->magClose() ?>
		<?php }) ?>
	</div>
</div>