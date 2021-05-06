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

	use rocket\si\control\SiIconType;
	use n2n\impl\web\ui\view\html\HtmlView;
	use rocket\ei\util\filter\EiuFilterForm;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
	
	$eiuFilterForm = $view->getParam('eiuFilterForm');
	$view->assert($eiuFilterForm instanceof EiuFilterForm);
	
	$propertyPath = $eiuFilterForm->getContextPropertyPath();
	$filterDefinition = $eiuFilterForm->getFilterDefinition();
	$filterJhtmlHook = $eiuFilterForm->getFilterJhtmlHook();

	
	$html->meta()->addJs('js/filters.js', 'rocket');
	
	$filterPropAttrs = array();
	foreach ($filterDefinition->getFilterProps() as $id => $filterItem) {
		$filterPropAttrs[$id] = $filterItem->getLabel($view->getN2nLocale());
	}
?>
<div class="rocket-filter" 
		data-icon-class-name-add="<?php $html->out(SiIconType::ICON_PLUS_CIRCLE) ?>"
		data-remove-icon-class-name="<?php $html->out(SiIconType::ICON_TIMES)?>"
		data-and-icon-class-name="fa fa-toggle-on" 
		data-or-icon-class-name="fa fa-toggle-off" 
		data-text-add-group="<?php $html->text('ei_filter_add_group_label') ?>" 
		data-text-add-field="<?php $html->text('ei_filter_add_field_label') ?>" 
		data-text-remove="<?php $html->text('common_delete_label') ?>"
		data-text-or="<?php $html->text('common_or_label') ?>"
		data-text-and="<?php $html->text('common_and_label') ?>"
		data-filter-field-item-form-url="<?php $html->out($filterJhtmlHook->getFieldItemFormUrl()) ?>"
		data-filter-group-form-url="<?php $html->out($filterJhtmlHook->getGroupFormUrl()) ?>"
		data-filter-fields="<?php $html->out(json_encode($filterPropAttrs)) ?>">
	
	<?php $view->import('\rocket\ei\util\filter\view\filterGroupForm.html', 
			array('propertyPath' => $propertyPath->ext('filterGroupForm'))) ?>
</div>
