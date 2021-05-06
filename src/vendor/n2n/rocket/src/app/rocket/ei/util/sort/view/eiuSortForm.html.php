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
	use n2n\persistence\orm\criteria\Criteria;
	use rocket\si\control\SiIconType;
	use rocket\ei\util\sort\EiuSortForm;
use n2n\web\dispatch\map\PropertyPath;
	
	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
	$request = HtmlView::request($this);
	
	$eiuSortForm = $view->getParam('eiuSortForm');
	$view->assert($eiuSortForm instanceof EiuSortForm);
	
	$propertyPath = $eiuSortForm->getContextPropertyPath()->ext('sortForm') ?? new PropertyPath(['sortForm']);
	
	$sortPropIdOptions = array();
	foreach ($eiuSortForm->getSortDefinition()->getSortProps() as $id => $sortProp) {
		$sortPropIdOptions[$id] = $sortProp->getLabel($view->getN2nLocale());
	}
	
	$directionsOptions = array(
			Criteria::ORDER_DIRECTION_ASC => $view->getL10nText('ei_sort_asc_label'),
			Criteria::ORDER_DIRECTION_DESC => $view->getL10nText('ei_sort_desc_label'));
	
	$html->meta()->addJs('js/filters.js', 'rocket');
?>
<div class="rocket-sort" 
		data-text-add-sort="<?php $html->l10nText('ei_impl_add_sort_label') ?>" 
		data-icon-class-name-add="<?php $html->out(SiIconType::ICON_PLUS_CIRCLE) ?>"
		data-text-remove-sort="<?php $html->l10nText('ei_impl_remove_sort_label') ?>" 
		data-icon-class-name-remove="<?php $html->out(SiIconType::ICON_TIMES) ?>">
	<div class="rocket-sort-contraints">
		<?php foreach ($formHtml->meta()->getMapValue($propertyPath->ext('directions')) as $key => $direction): ?>
			<div class="nav-item rocket-sort-constraint">
				<?php $formHtml->select($propertyPath->ext('sortPropIds')->fieldExt($key), $sortPropIdOptions, array('class' => 'form-control rocket-sort-prop')) ?>
				<?php $formHtml->select($propertyPath->ext('directions')->fieldExt($key), $directionsOptions, array('class' => 'form-control')) ?>
			</div>
		<?php endforeach ?>
		<div class="nav-item rocket-sort-constraint rocket-empty-sort-constraint">
			<?php $formHtml->select($propertyPath->ext('sortPropIds[]'), $sortPropIdOptions, array('class' => 'form-control rocket-sort-prop')) ?>
			<?php $formHtml->select($propertyPath->ext('directions[]'), $directionsOptions, array('class' => 'form-control')) ?>
		</div>
	</div>
</div>
