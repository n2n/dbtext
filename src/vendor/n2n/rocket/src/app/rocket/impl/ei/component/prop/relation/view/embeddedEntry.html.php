<?php
	use rocket\ei\util\entry\EiuEntry;
	use rocket\ei\util\gui\EiuHtmlBuilder;
	use n2n\impl\web\ui\view\html\HtmlView;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	
	$eiuEntry = $view->getParam('eiuEntry');
	$view->assert($eiuEntry instanceof EiuEntry);
	
	$eiuHtml = new EiuHtmlBuilder($view);
	
	$summaryRequired = $view->getParam('summaryRequired');
?>

<div class="rocket-impl-entry">
	<?php if (!$eiuEntry->isAccessible()): ?>
		<?php if ($summaryRequired): ?>
			<div class="rocket-summary">
				<div class="rocket-handle"></div>
				<div class="rocket-impl-content-type">
					<i class="<?php $html->out($eiuEntry->getIconTyp()) ?>"></i>
					<?php $html->out($eiuEntry->getGenericLabel()) ?>
				</div>
				<div class="rocket-impl-content">
					<?php $html->text('ei_impl_not_accessible', array('entry' => $eiuEntry->createIdentityString())) ?>
				</div>
			</div>
		<?php endif ?>
		
		<div class="rocket-impl-body rocket-group">
			<label><?php $html->out($eiuEntry->createIdentityString()) ?></label>
			<div class="rocket-structure-content">
				<?php $html->text('ei_impl_not_accessible', array('entry' => $eiuEntry->createIdentityString())) ?>
			</div>
		</div>
	<?php else: ?>
		<?php if ($summaryRequired): ?>
			<?php $eiuEntryGui = $eiuEntry->newEntryGui(false) ?>
			<?php $eiuHtml->entryOpen('div', $eiuEntryGui, null, array('class' => 'rocket-summary')) ?>
				<div class="rocket-handle"></div>
				<div class="rocket-impl-content-type">
					<i class="<?php $html->out($eiuEntry->getGenericIconType()) ?>"></i>
					<?php $html->out($eiuEntry->getGenericLabel()) ?>
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
		<?php endif ?>
	
		<?php $eiuEntryGui = $eiuEntry->newEntryGui(true) ?>
		<?php $eiuHtml->entryOpen('div', $eiuEntryGui, null, array('class' => 'rocket-impl-body rocket-group rocket-light-group')) ?>
			<label><?php $html->out($eiuEntry->createIdentityString()) ?></label>
			<div class="rocket-structure-content">
				<?php $view->import($eiuEntryGui->createView($view)) ?>
			</div>
			
			<div class="rocket-zone-commands">
				<?php $eiuHtml->entryCommands(false) ?>
			</div>
		<?php $eiuHtml->entryClose() ?>
	<?php endif ?>
</div>