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
	use rocket\impl\ei\component\prop\relation\model\filter\RelationSelectorForm;
	
	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
		
	$propertyPath = $view->getParam('propertyPath');
	$view->assert($propertyPath instanceof PropertyPath);
	
	$relationSelectorForm = $formHtml->meta()->getMapValue($propertyPath)->getObject();
	$view->assert($relationSelectorForm instanceof RelationSelectorForm);
	
	$entryLabeler = $relationSelectorForm->getEntryLabeler();
	$html->meta()->addJs('impl/js/selectorMag.js', 'rocket');
?>
<div class="rocket-selector-mag"
		data-original-ei-ids="<?php $html->out(json_encode($relationSelectorForm->getEntryPids())) ?>"
		data-identity-strings="<?php $html->out(json_encode($entryLabeler->getSelectedIdentityStrings())) ?>"
		data-overview-tools-url="<?php $html->out($view->getParam('selectOverviewToolsUrl')) ?>"
		data-add-label="<?php $html->text('common_add_label') ?>"
		data-reset-label="<?php $html->text('common_reset_label') ?>"
		data-clear-label="<?php $html->text('common_clear_label') ?>"
		data-generic-entry-label="<?php $html->out($entryLabeler->getGenericLabel()) ?>"
		data-base-property-name="<?php $html->out($formHtml->meta()->getForm()->getDispatchTargetEncoder()
				->buildValueParamName($propertyPath->ext('entryPids'), false))?>">
	<ul>
		<?php $formHtml->meta()->arrayProps($propertyPath->ext('entryPids'), function () use ($formHtml, $propertyPath) { ?> 
			<li><?php $formHtml->input() ?></li>
		<?php }, null, null, true) ?>
		<li class="rocket-new-entry"><?php $formHtml->input($propertyPath->ext('entryPids[]')) ?></li>
	</ul>
</div>
