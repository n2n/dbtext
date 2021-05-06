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

	use n2n\web\dispatch\map\PropertyPath;
	use n2n\impl\web\ui\view\html\HtmlView;
	use n2n\util\uri\Url;
	use rocket\impl\ei\component\prop\relation\model\mag\MappingForm;
	use rocket\impl\ei\component\prop\relation\model\mag\ToManyForm;

	/**
	 * @var \n2n\web\ui\view\View $view
	 */
	$view = HtmlView::view($view);
	$html = HtmlView::html($view);
	$formHtml = HtmlView::formHtml($view);
	
	$propertyPath = $view->getParam('propertyPath');
	$view->assert($propertyPath instanceof PropertyPath);
	
	$toManyForm = $formHtml->meta()->getMapValue($propertyPath)->getObject();
	$view->assert($toManyForm instanceof ToManyForm);
	
	$entryLabeler = $toManyForm->getEntryLabeler();
	
	$newMappingFormUrl = $view->getParam('newMappingFormUrl');
	$view->assert($newMappingFormUrl === null || $newMappingFormUrl instanceof Url);
	
// 	$eiuHtml = new EiuHtmlBuilder($view);
	
// 	$combined = $toManyForm->isSelectionModeEnabled() && count($toManyForm->getCurrentMappingForms()) > 0
?>
<div class="rocket-impl-to-many" 
		data-min="<?php $html->out($toManyForm->getMin()) ?>"
		data-max="<?php $html->out($toManyForm->getMax()) ?>"
		data-remove-item-label="<?php $html->text('ei_impl_relation_remove_item_label', 
				array('item' => $entryLabeler->getGenericLabel())) ?>"
		data-move-up-label="<?php $html->text('common_move_up_label') ?>"
		data-move-down-label="<?php $html->text('common_move_down_label') ?>"
		data-item-label="<?php $html->out($entryLabeler->getGenericLabel()) ?>"
		data-ei-spec-labels="<?php $html->out(json_encode($entryLabeler->getEiTypeLabels())) ?>"
		data-reduced="<?php $html->out($toManyForm->isReduced()) ?>"
		data-sortable="<?php $html->out($toManyForm->isSortable()) ?>"
		data-close-label="<?php $html->text('common_apply_label') ?>"
		data-edit-all-label="<?php $html->text('common_edit_all_label') ?>">
		
	<?php if ($toManyForm->isSelectionModeEnabled()): ?>
		<div class="rocket-impl-selector"
				data-original-ei-ids="<?php $html->out(json_encode($toManyForm->getOriginalEntryPids())) ?>"
				data-identity-strings="<?php $html->out(json_encode($entryLabeler->getSelectedIdentityStrings())) ?>"
				data-overview-tools-url="<?php $html->out($view->getParam('selectOverviewToolsUrl')) ?>"
				data-select-label="<?php $html->text('common_select_label') ?>"
				data-reset-label="<?php $html->text('common_reset_label') ?>"
				data-clear-label="<?php $html->text('common_clear_label') ?>"
				data-cancel-label="<?php $html->text('common_cancel_label') ?>"
				data-edit-all-label="<?php $html->text('ei_impl_relation_edit_all') ?>"
				data-generic-entry-label="<?php $html->out($entryLabeler->getGenericLabel()) ?>"
				data-base-property-name="<?php $html->out($formHtml->meta()->getForm()->getDispatchTargetEncoder()
						->buildValueParamName($propertyPath->ext('selectedEntryPids'), false)) ?>">
			<ul>
				<?php $formHtml->meta()->arrayProps($propertyPath->ext('selectedEntryPids'), function () use ($formHtml, $propertyPath) { ?> 
					<li><?php $formHtml->input($propertyPath->ext('selectedEntryPids[]')) ?></li>
				<?php }, null, null, true) ?>
				<li class="rocket-new-entry"><?php $formHtml->input($propertyPath->ext('selectedEntryPids[]')) ?></li>
			</ul>
		</div>
	<?php endif ?>
	
	<?php if (count($toManyForm->getCurrentMappingForms()) > 0): ?>
		<div class="rocket-impl-currents">
			<?php $formHtml->meta()->arrayProps($propertyPath->ext('currentMappingForms'), function () use ($view, $html, $formHtml, $toManyForm) { ?>
				<?php $currentMappingForm = $formHtml->meta()->getMapValue()->getObject(); ?>
				<?php $view->assert($currentMappingForm instanceof MappingForm) ?>
			
				<?php $view->import('embeddedEntryForm.html', array('mappingForm' => $currentMappingForm, 
						'summaryRequired' => $toManyForm->isReduced()))?>
			<?php }) ?>
		</div>
	<?php endif ?>
	
	<?php if ($toManyForm->isNewMappingFormAvailable()): ?>
		<div class="rocket-impl-news"
				data-new-entry-form-url="<?php $html->out((string) $newMappingFormUrl) ?>"
				data-property-path="<?php $html->out($formHtml->meta()
						->createRealPropertyPath($propertyPath->ext('newMappingForms'))) ?>"
				data-draft-mode="<?php $html->out($toManyForm->isDraftMode())?>"
				data-add-item-label="<?php $html->text('ei_impl_relation_add_item_label', 
						array('item' => $entryLabeler->getGenericLabel())) ?>"
				data-paste-item-label="<?php $html->text('ei_impl_relation_paste_item_label') ?>"
				data-ei-type-range="<?php $html->out(json_encode($toManyForm->getEiTypeIds())) ?>">
			<?php $formHtml->meta()->arrayProps($propertyPath->ext('newMappingForms'), function () use ($html, $formHtml, $view, $toManyForm) { ?>
				<?php $currentMappingForm = $formHtml->meta()->getMapValue()->getObject(); ?>
				<?php $view->assert($currentMappingForm instanceof MappingForm) ?>
				
				<?php $view->import('embeddedEntryForm.html', array('mappingForm' => $currentMappingForm,
						'summaryRequired' => $toManyForm->isReduced())) ?>
			<?php }) ?>
		</div>
	<?php endif ?>
	<?php if ($toManyForm->isSelectionModeEnabled() && $toManyForm->isNewMappingFormAvailable()): ?>
		<div class="rocket-group rocket-simple-group">
			<label><?php $html->text('ei_impl_embedded_add_title') ?></label>
			<div class="rocket-structure-content">
			
			</div>
		</div>
	<?php endif ?>
</div>
