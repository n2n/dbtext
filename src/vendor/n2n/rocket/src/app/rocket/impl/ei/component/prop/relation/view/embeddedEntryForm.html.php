<?php
	use rocket\impl\ei\component\prop\relation\model\mag\MappingForm;
	use n2n\impl\web\ui\view\html\HtmlView;
	use rocket\ei\util\gui\EiuHtmlBuilder;
use rocket\ei\mask\model\DisplayItem;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
	$eiuHtml = new EiuHtmlBuilder($view);
	
	$mappingForm = $view->getParam('mappingForm');
	$view->assert($mappingForm instanceof MappingForm);
	
	$eiuEntry = $mappingForm->getEiuEntryForm()->getChosenEiuEntryTypeForm()->getEiuEntryGui()->entry();
	
	$grouped = $view->getParam('grouped', false, true);
	$summaryRequired = $view->getParam('summaryRequired');
?>

<div class="rocket-impl-entry"
		data-item-label="<?php $html->out($mappingForm->getEntryLabel()) ?>"
		data-remove-item-label="<?php $html->text('ei_impl_relation_remove_item_label', 
				array('item' => $mappingForm->getEntryLabel())) ?>"
		data-rocket-impl-changed-text="<?php $html->text('ei_impl_entry_changed_txt') ?>">
	<?php $formHtml->optionalObjectEnabledHidden() ?>
	
	<?php if (!$mappingForm->isAccessible()): ?>
		<?php if ($summaryRequired): ?>
			<div class="rocket-summary">
				<div class="rocket-handle"><i class="fa fa-bars"></i></div>
				<div class="rocket-impl-content-type">
					<?php $html->out($mappingForm->getEntryLabel()) ?>
				</div>
				<div class="rocket-impl-content">
				</div>
			</div>
		<?php endif ?>
		
		<div class="rocket-impl-body rocket-group rocket-light-group">
			<label><?php $html->out($mappingForm->getEntryLabel()) ?></label>
			<div class="rocket-structure-content">
				<?php $html->text('ei_impl_not_accessible') ?>
			</div>
		</div>
	<?php else: ?>
		<?php if ($summaryRequired): ?>
			<?php if (!$eiuEntry->isNew()): ?>
				<?php $eiuEntryGui = $eiuEntry->newEntryGui(false) ?>
				<?php $eiuHtml->entryOpen('div', $eiuEntryGui, null, array('class' => 'rocket-summary')) ?>
					<div class="rocket-handle"><i class="fa fa-bars"></i></div>
					<div class="rocket-impl-content-type">
						<i class="<?php $html->out($eiuEntry->getGenericIconType()) ?>"></i>
						<span><?php $html->out($eiuEntry->getGenericLabel()) ?></span>
					</div>
					<div class="rocket-impl-content">
						<?php foreach ($eiuEntryGui->getDefPropPaths() as $eiPropPath): ?>
							<?php $eiuHtml->fieldOpen('div', $eiPropPath, false) ?>
								<?php $eiuHtml->fieldContent() ?>
							<?php $eiuHtml->fieldClose() ?>
						<?php endforeach ?>
					</div>
					<div class="rocket-simple-commands"></div>
				<?php $eiuHtml->entryClose() ?>
			<?php else: ?>
				<div class="rocket-summary">
					<div class="rocket-handle"><i class="fa fa-bars"></i></div>
					<div class="rocket-impl-content-type">
						<i class="<?php $html->out($eiuEntry->getGenericIconType()) ?>"></i>
						<span><?php $html->out($eiuEntry->getGenericLabel()) ?></span>
					</div>
					<div class="rocket-impl-content">
						<div class="rocket-impl-status">
							<?php $html->text('ei_impl_new_entry_txt') ?>
						</div>
					</div>
					<div class="rocket-simple-commands"></div>
				</div>
			<?php endif ?>
		<?php endif ?>
	
		<?php if (!$grouped): ?>
			<?php $view->out($mappingForm->getEiuEntryForm()
					->setContextPropertyPath($formHtml->meta()->propPath('eiuEntryForm'))
					->createView($view, false)) ?>
		<?php else: ?>
			<div class="rocket-impl-body">
				<?php $view->out($mappingForm->getEiuEntryForm()
						->setContextPropertyPath($formHtml->meta()->propPath('eiuEntryForm'))
						->createView($view, true, SiStructureType::LIGHT_GROUP, $mappingForm->getEntryLabel())) ?>
			</div>
		<?php endif ?>
	<?php endif ?>
	
	<?php $formHtml->input('orderIndex', array('class' => 'rocket-impl-order-index')) ?>
</div>