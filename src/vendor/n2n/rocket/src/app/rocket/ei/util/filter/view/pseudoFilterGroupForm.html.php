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
	use rocket\ei\util\filter\form\FilterGroupForm;
	
	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
	
	$propertyPath = $view->getParam('propertyPath');
	$view->assert($propertyPath instanceof PropertyPath);
	
	$filterGroupForm = $view->getParam('filterGroupForm');
	$view->assert($filterGroupForm instanceof FilterGroupForm);
?>

<?php $formHtml->openPseudo($filterGroupForm, $propertyPath) ?>
	<li class="rocket-filter-group">
		<?php $formHtml->optionalObjectEnabledHidden(null) ?>
		<div class="rocket-filter-items">
			<?php $formHtml->inputCheckbox('useAnd', true, 
					array('class' => 'rocket-filter-and-indicator'))?>
			<ul	class="rocket-filter-field-items"
					data-new-form-array-property-path="<?php $html->out('filterPropItemForms') ?>">
			</ul>
			<ul class="rocket-filter-groups" 
					data-new-form-array-property-path="<?php $html->out('filterGroupForms') ?>">
			</ul>
		</div>
	</li>
<?php $formHtml->closePseudo() ?>
