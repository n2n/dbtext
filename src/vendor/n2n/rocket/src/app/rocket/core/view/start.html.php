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

	use rocket\user\model\LoginContext;
	use rocket\core\model\ServerInfoExtractor;
	use rocket\core\model\DeleteLoginModel;
	use n2n\web\ui\Raw;
	use n2n\impl\web\ui\view\html\HtmlView;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
	
	$deleteLoginModel = $view->getParam('deleteLoginModel', true);
	$view->assert($deleteLoginModel instanceof DeleteLoginModel);
	
	$serverInfoExtractor = $view->lookup('rocket\core\model\ServerInfoExtractor'); 
	$view->assert($serverInfoExtractor instanceof ServerInfoExtractor);
	
	$loginContext = $view->lookup('rocket\user\model\LoginContext');
	$view->assert($loginContext instanceof LoginContext);

	$view->useTemplate('template.html',
			array('title' => $view->getL10nText('core_start_title', array('user' => $loginContext->getCurrentUser()))));
?>
<?php if ($loginContext->getCurrentUser()->isAdmin()): ?>
	<div id="rocket-core-latest-logins" class="rocket-main-group">
		<label><?php $html->l10nText('core_latest_logins_title') ?></label>
		<div class="rocket-structure-content row">
			<div class="col-sm-6 mt-3">
				<h3><?php $html->l10nText('core_latest_logins_title') ?></h3>
				<?php $view->import('inc\loginTable.html', array('useSuccessfull' => true)) ?>
			</div>
			<div class="col-sm-6 mt-3">
				<h3><?php $html->l10nText('core_failed_logins_title') ?></h3>
				<?php $view->import('inc\loginTable.html', array('useSuccessfull' => false)) ?>
			</div>
		</div>
	</div>
	<div id="rocket-core-server-info" class="rocket-main-group">
		<label><?php $html->l10nText('core_server_info_title') ?></label>
		<div class="rocket-structure-content">
    		<table class="table table-hover rocket-table mt-3">
    			<thead>
    				<tr>
    					<th><?php $html->text('core_property_label') ?></th>
    					<th><?php $html->text('core_value_label') ?></th>
    				</tr>
    			</thead>
    			<tbody>
    				<?php foreach ($serverInfoExtractor->getServerProperties() as $props): ?>
    					<tr>
    						<td><?php $html->out($props['name']) ?></td>
    						<td class="<?php echo ($props['status'] == 1 ? 'rocket-server-info-value-ok' : 'rocket-server-info-value-nok') ?>">
    							<?php $html->esc($props['value']) ?>
    						</td>
    					</tr> 
    				<?php endforeach ?>
    			</tbody>
    		</table>
		</div>
	</div>

    <div class="rocket-zone-commands">
    	<?php $formHtml->open($deleteLoginModel) ?>
    		<?php $formHtml->buttonSubmit('delete', new Raw('<i class="fa fa-times-circle"></i> <span>' 
    						. $html->getL10nText('core_delete_failed_logins_label') . '</span>'), 
    				array('class' => 'btn btn-secondary')); ?>
    	<?php $formHtml->close() ?>
    </div>
<?php endif ?>
