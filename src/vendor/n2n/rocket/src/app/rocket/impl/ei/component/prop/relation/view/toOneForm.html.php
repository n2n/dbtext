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
	use rocket\impl\ei\component\prop\relation\model\mag\ToOneForm;
	use rocket\impl\ei\component\prop\relation\model\mag\MappingForm;
use rocket\ei\util\gui\EiuHtmlBuilder;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
	
	$propertyPath = $view->getParam('propertyPath');
	$view->assert($propertyPath instanceof PropertyPath);
	
	$toOneForm = $formHtml->meta()->getMapValue($propertyPath)->getObject();
	$view->assert($toOneForm instanceof ToOneForm);
		
	$entryLabeler = $toOneForm->getEntryLabeler();
	
	$newMappingFormUrl = $view->getParam('newMappingFormUrl');
	$view->assert($newMappingFormUrl === null || $newMappingFormUrl instanceof Url);
	
	$newMappingFormPropertyPath = $propertyPath->ext('newMappingForm');
	
	$eiuHtml = new EiuHtmlBuilder($view);
	$grouped = $toOneForm->isReduced() || $toOneForm->isSelectionModeEnabled()/* || $eiuHtml->meta()->isFieldPanel()*/;
?>

<div class="rocket-impl-to-one" 
		data-mandatory="<?php $html->out($toOneForm->isMandatory()) ?>"
		data-remove-item-label="<?php $html->text('ei_impl_relation_remove_item_label', 
				array('item' => $entryLabeler->getGenericLabel())) ?>"
		data-replace-item-label="<?php $html->text('ei_impl_to_one_replace_item_label', 
				array('item' => $entryLabeler->getGenericLabel())) ?>"
		data-item-label="<?php $html->out($entryLabeler->getGenericLabel()) ?>"
		data-ei-spec-labels="<?php $html->out(json_encode($entryLabeler->getEiTypeLabels())) ?>"
		data-reduced="<?php $html->out($toOneForm->isReduced()) ?>"
		data-close-label="<?php $html->text('common_apply_label') ?>"
		data-grouped="<?php $html->out($grouped) ?>"
		data-display-item-label="<?php $html->out($formHtml->meta()->getLabel($propertyPath)) ?>">
		
	<?php if ($toOneForm->isSelectionModeEnabled()): ?>
		<div class="rocket-impl-selector" 
				data-original-ei-id="<?php $html->out($toOneForm->getOriginalEntryPid()) ?>"
				data-identity-strings="<?php $html->out(json_encode($entryLabeler->getSelectedIdentityStrings())) ?>"
				data-overview-tools-url="<?php $html->out($view->getParam('selectOverviewToolsUrl')) ?>"
				data-select-label="<?php $html->text('common_select_label') ?>"
				data-reset-label="<?php $html->text('common_reset_label') ?>"
				data-clear-label="<?php $html->text('common_clear_label') ?>"
				data-cancel-label="<?php $html->text('common_cancel_label') ?>">
			<?php $formHtml->input($propertyPath->ext('selectedEntryPid')) ?>
		</div>
	<?php endif ?>

	<?php if ($toOneForm->isMappingFormAvailable()): ?>
		<?php $currentPropertyPath = $propertyPath->ext('currentMappingForm') ?>
		<?php $currentMappingForm = $formHtml->meta()->getMapValue($currentPropertyPath)->getObject(); ?>
		<?php $view->assert($currentMappingForm instanceof MappingForm) ?>
		
		<?php if (null === $formHtml->meta()->getMapValue($currentPropertyPath)->getAttrs()): ?>
			<div class="rocket-impl-current"
					data-remove-item-label="<?php $html->text('ei_impl_relation_remove_item_label', 
							array('item' => $currentMappingForm->getEntryLabel())) ?>"
					data-item-label="<?php $html->out($currentMappingForm->getEntryLabel()) ?>">
				
				<?php $formHtml->meta()->pushBasePropertyPath($currentPropertyPath) ?>
				<?php $view->import('embeddedEntryForm.html', array('mappingForm' => $currentMappingForm,
						'grouped' => $grouped,
						'summaryRequired' => $toOneForm->isReduced())) ?>
				<?php $formHtml->meta()->popBasePropertyPath() ?>
			</div>
		<?php endif ?>
	<?php endif ?>

	<?php if ($toOneForm->isNewMappingFormAvailable()): ?>
		<div class="rocket-impl-new" 
				data-new-entry-form-url="<?php $html->out((string) $newMappingFormUrl) ?>"
				data-property-path="<?php $html->out((string) $formHtml->meta()->createRealPropertyPath($newMappingFormPropertyPath)) ?>"
				data-draft-mode="<?php $html->out($toOneForm->isDraftMode())?>"
				data-add-item-label="<?php $html->text('ei_impl_relation_add_item_label', 
						array('item' => $entryLabeler->getGenericLabel())) ?>"
				data-paste-item-label="<?php $html->text('ei_impl_relation_paste_item_label') ?>"
				data-replace-item-label="<?php $html->text('ei_impl_relation_replace_item_label', 
						array('item' => $entryLabeler->getGenericLabel())) ?>"
				data-ei-type-range="<?php $html->out(json_encode($toOneForm->getEiTypeIds())) ?>">
			<?php if (null === $formHtml->meta()->getMapValue($newMappingFormPropertyPath)->getAttrs()): ?>
				<?php $currentMappingForm = $formHtml->meta()->getMapValue($newMappingFormPropertyPath)->getObject() ?>
				<?php $view->assert($currentMappingForm instanceof MappingForm) ?>
				
				<?php $formHtml->meta()->pushBasePropertyPath($newMappingFormPropertyPath) ?>
				
				<?php $view->import('embeddedEntryForm.html', array('mappingForm' => $currentMappingForm,
						'grouped' => $grouped, 'summaryRequired' => $toOneForm->isReduced())) ?>
						
				<?php $formHtml->meta()->popBasePropertyPath() ?>
			<?php endif ?>
		</div>
	<?php endif ?>
</div>
