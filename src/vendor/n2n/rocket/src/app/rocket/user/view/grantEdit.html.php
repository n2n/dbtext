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

	use n2n\web\ui\Raw;
	use rocket\user\model\EiGrantForm;
	use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\http\nav\Murl;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
	
	$eiGrantForm = $view->getParam('eiGrantForm'); 
	$view->assert($eiGrantForm instanceof EiGrantForm);
	
	$userGroup = $eiGrantForm->getEiGrant()->getRocketUserGroup();
	
	$view->useTemplate('~\core\view\template.html', array('title' => $view->getParam('label')));
?>

<?php $formHtml->open($eiGrantForm, null, null, ['class' => 'rocket-privilege-form rocket-form', 
		'data-rocket-add-privilege-label' => $html->getText('user_add_privilege_label'),
		'data-rocket-save-first-info' => $html->getText('user_save_first_info')]) ?>
	<?php $formHtml->messageList() ?>
	
	<?php $formHtml->meta()->arrayProps('eiGrantPrivilegeForms', function () 
			use ($view, $html, $formHtml, $eiGrantForm) { ?>
		<div class="rocket-group rocket-simple-group rocket-privilege">
			<label><?php $html->text('user_privilege_title') ?></label>
			<div class="rocket-toolbar">
				<?php $formHtml->optionalObjectCheckbox(null, ['class' => 'rocket-privilege-enabler'])  ?>
			</div>
			<div class="rocket-structure-content">
				<?php if (null !== ($mappingResult = $formHtml->meta()->getMapValue('eiuPrivilegeForm'))): ?>
					<?php $html->out($mappingResult->getObject()
							->setContextPropertyPath($formHtml->meta()->propPath('eiuPrivilegeForm'))) ?>
				<?php endif ?>
				
				<?php if ($eiGrantForm->areRestrictionsAvailable()): ?>
					<div class="rocket-panel">
						<div class="rocket-structure-content">
							<?php $formHtml->optionalObjectCheckbox('restrictionEiuFilterForm', 
									['class' => 'rocket-restrictions-enabler'], 
									$html->getL10nText('user_access_restricted_label')) ?>
						</div>
					</div>
				
					<div class="rocket-group rocket-simple-group rocket-restrictions">	
						<label><?php $html->l10nText('user_group_access_restrictions_label')?></label>
						<div class="rocket-structure-content">
							<?php $html->out($formHtml->meta()->getMapValue('restrictionEiuFilterForm')->getObject()
									->setContextPropertyPath($formHtml->meta()->propPath('restrictionEiuFilterForm')))?>
						</div>
					</div>
				<?php endif ?>
			</div>
		</div>
	<?php }, count($formHtml->meta()->getMapValue('eiGrantPrivilegeForms')) + 5) ?>
	
	<div class="rocket-zone-commands">	
		<div>
			<?php $formHtml->buttonSubmit('save', new Raw('<i class="fa fa-save"></i><span>' 
							. $html->getL10nText('common_save_label') . '</span>'),
					array('class' => 'btn btn-primary rocket-important')) ?>
					
			<?php $html->link(Murl::controller()->pathExt('grants', $userGroup->getId()), 
					new Raw('<i class=" icon-remove-circle"></i><span>'
							. $html->getL10nText('common_cancel_label') . '</span>'),
					array('class' => 'btn btn-secondary rocket-jhtml', 'data-jhtml-use-page-scroll-pos' => 'true')) ?>	
		</div>
	</div>
<?php $formHtml->close() ?>
