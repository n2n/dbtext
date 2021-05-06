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
	use rocket\user\model\LoginContext;
	use n2n\impl\web\ui\view\html\HtmlView;
	use n2n\core\N2N;
use rocket\user\model\RocketUserDao;
	
	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
	
	$loginContext = $view->getParam('loginContext');
	$view->assert($loginContext instanceof LoginContext);
	
	$userDao = $view->lookup(RocketUserDao::class);
	$view->assert($userDao instanceof RocketUserDao);
	$htmlMeta = $html->meta();
	
	$htmlMeta->addMeta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0'));
	$htmlMeta->addMeta(array('name' => 'robots', 'content' => 'noindex'));
	$htmlMeta->addCss('css/rocket-30.css');
	$htmlMeta->addCss('css/icons/font-awesome/icons/all.min.css');
	// 	use this to insert custom icons
	// 	$htmlMeta->addCss('css/icons/icomoon/icomoon.css');
?>
<!DOCTYPE html>
<html> 
	<?php $html->headStart() ?>
		<meta charset="<?php $html->out(N2N::CHARSET) ?>" />
	<?php $html->headEnd() ?>
	<?php $html->bodyStart(array('id' => 'rocket-login')) ?>
		<div id="rocket-login-container">
			<div id="rocket-login-form-container">
				<div id="rocket-logo-container">
					<?php $html->imageAsset('img/rocket-logo.svg', '', array('id' => 'rocket-login-logo')) ?>
					<?php $html->linkToContext('', new Raw('<i class="fa fa-home"></i> ' . $html->getL10nText('user_back_to_website_label')), array('class' => 'rocket-user-back-link' , 'target' => '_blank'))?>
				</div>
				<?php $html->messageList(null, null, array('class' => 'alert alert-danger list-unstyled')) ?>
				<?php $formHtml->open($loginContext, null, null, array('class' => 'rocket-login-form')) ?>
					<div class="input-group input-group-lg mb-3">
    					<label for="nick" class="input-group-prepend" title="<?php $html->l10nText('user_nick_label') ?>">
    						<i class="fa fa-user input-group-text"></i>
    						<span class="sr-only"><?php $html->l10nText('user_nick_label') ?></span>
    					</label>
						<?php $formHtml->input('nick', array('placeholder' => $view->getL10nText('user_nick_label'), 
    						    'class' => 'form-control', 'id' => 'nick')) ?>
					</div>
					<div class="input-group input-group-lg mb-3">
    					<label for="rawPassword" class="input-group-prepend" title="<?php $html->l10nText('user_password_label') ?>">
    						<i class="fa fa-lock input-group-text"></i>
    						<span class="sr-only"><?php $html->l10nText('user_password_label') ?></span>
    					</label>
						<?php $formHtml->input('rawPassword', array('placeholder' => $view->getL10nText('user_password_label'), 
						      'class' => 'form-control form-control-lg', 'id' => 'rawPassword'), 'password', true) ?>
					</div>
					<div class="rocket-form-actions">
						<?php $formHtml->inputSubmit('login', $view->getL10nText('user_login_label'), 
						      array('class' => 'btn btn-secondary btn-lg btn-block')) ?>
					</div>
				<?php $formHtml->close() ?>
				<?php if (N2N::isDevelopmentModeOn()): ?>
					<div id="rocket-dev-login-container">
						<h2>Development Login:</h2>
							<ul class="list-unstyled mb-0">
								<?php foreach ($userDao->getUsers() as $user): ?>
									<li><?php $html->linkToController(['devlogin', $user->getId()], $user->getNick())?></li>
								<?php endforeach ?>
							</ul>
					</div>
				<?php endif ?>
			</div>
			
		</div>
	<?php $html->bodyEnd()?>
</html>
