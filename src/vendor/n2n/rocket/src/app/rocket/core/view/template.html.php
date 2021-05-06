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
	
	use n2n\l10n\Message;
	use n2n\impl\web\ui\view\html\HtmlView;
	use rocket\core\model\TemplateModel;
	use n2nutil\jquery\JQueryUiLibrary;
use n2n\web\http\nav\Murl;
use n2n\core\N2N;
use n2n\web\ui\Raw;
use rocket\user\model\LoginContext;
	
	$view = HtmlView::view($this);
	$request = HtmlView::request($view);
	$html = HtmlView::html($view);
	$httpContext = HtmlView::httpContext($view);
	
	$view->useTemplate('boilerplate.html', $view->getParams());
	
	$html->meta()->addLibrary(new JQueryUiLibrary(3));
	
	$templateModel = $view->lookup(TemplateModel::class);
	$view->assert($templateModel instanceof TemplateModel);
	
	$loginContext = $view->lookup(LoginContext::class);
	$view->assert($loginContext instanceof LoginContext);
	
	$html->meta()->addCssCode('
			body {
				scroll-behavior: smooth;
			}

			.rocket-layer {
				animation: layertransform 0.2s;
			}
			
			.rocket-layer,
			.rocket-main-layer {
				visibility: hidden;
			}
			
			.rocket-layer.rocket-active,
			.rocket-main-layer.rocket-active {
				visibility: visible;
			}
			
			@keyframes layertransform {
			    from { transform: translateX(100vw); }
			    to { transform: translateX(0); }
			}');
?>
<header id="rocket-header">
	<?php $html->link(Murl::controller('rocket'), $html->getImageAsset('img/rocket-logo.svg', 'logo'),
			array('id' => 'rocket-branding')) ?>
	<h2 id="rocket-customer-name"><?php $html->out(N2N::getAppConfig()->general()->getPageName()) ?></h2>
	<nav id="rocket-conf-nav" class="navbar-expand-lg" data-jhtml-comp="rocket-conf-nav">
		<button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse"
				data-target="#rocket-conf-nav" aria-controls="navbarText" aria-expanded="false"
				aria-label="Toggle navigation">
			<i class="fas fa-bars"></i>
		</button>
		<h2 class="sr-only"><?php $html->l10nText('conf_nav_title') ?></h2>
		<ul class="nav rocket-meta-nav justify-content-end">
			<?php if ($templateModel->getCurrentUser()->isAdmin()): ?>
				<li class="nav-item">
					<?php $html->linkStart(Murl::controller('rocket')->pathExt('tools'), array('class' => 'nav-link')) ?>
					<i class="fas fa-wrench mr-2"></i><span><?php $html->text('tool_title') ?></span>
					<?php $html->linkEnd() ?>
				</li>
				<li class="nav-item">
					<?php $html->linkStart(Murl::controller('rocket')->pathExt('users'), array('class' => 'nav-link')) ?>
					<i class="fas fa-user mr-2"></i><span><?php $html->text('user_title') ?></span>
					<?php $html->linkEnd() ?>
				</li>
				<li class="nav-item">
					<?php $html->linkStart(Murl::controller('rocket')->pathExt('usergroups'), array('class' => 'nav-link')) ?>
					<i class="fas fa-users mr-2"></i><span><?php $html->text('user_groups_title') ?></span>
					<?php $html->linkEnd() ?>
				</li>
			<?php endif ?>
			<li class="nav-item">
				<?php $html->linkStart(Murl::controller('rocket')->pathExt('users', 'profile'), array('class' => 'nav-link rocket-conf-user')) ?>
				<i class="fas fa-user mr-2"></i><span><?php $html->out((string) $templateModel->getCurrentUser()) ?></span>
				<?php $html->linkEnd() ?>
			</li>
			<li class="nav-item">
				<?php $html->linkStart(Murl::controller('rocket')->pathExt('logout'), array('class' => 'nav-link rocket-conf-logout')) ?>
				<i class="fas fa-sign-out"></i>
				<?php $html->linkEnd() ?>
			</li>
			<li class="nav-item">
				<?php $html->linkStart(Murl::controller('rocket')->pathExt('about'), array('class' => 'nav-link')) ?>
				<i class="fas fa-info"></i>
				<?php $html->linkEnd() ?>
			</li>
		</ul>
	</nav>
</header>
<nav id="rocket-global-nav" data-jhtml-comp="rocket-global-nav">
	<h2 class="sr-only" data-rocket-user-id="<?php $html->out($loginContext->getCurrentUser()->getId()) ?>"><?php $html->l10nText('manage_nav_title') ?></h2>
	<?php foreach ($templateModel->getNavArray() as $navArray): ?>
		<div class="rocket-nav-group<?php $html->esc($navArray['open'] ? ' rocket-nav-group-open': '') ?>"
				data-nav-group-id="<?php $html->out(str_replace(' ', '-', strtolower($navArray['menuGroup']->getLabel()))) ?>">
			<h3 class="rocket-global-nav-group-title">
				<a><?php $html->esc($navArray['menuGroup']->getLabel()) ?></a>
				<i class="fa <?php $html->esc($navArray['open'] ? 'fa-minus': 'fa-plus') ?>"></i>
			</h3>
			<ul class="nav flex-column">
				<?php foreach ($navArray['launchPads'] as $launchPad): ?>
					<li class="nav-item">
						<?php $html->link(
								$view->buildUrl(Murl::controller('rocket')->pathExt('manage', $launchPad->getId()))
										->ext($launchPad->determinePathExt($view->getN2nContext())),
								new Raw($html->getEsc($navArray['menuGroup']->determineLabel($launchPad))
									. '<span></span>'),
								array('data-jhtml' => 'true', 'class' => 'nav-link'
									. ($templateModel->isLaunchPadActive($launchPad) ? ' active' : null))) ?></li>
				<?php endforeach ?>
			</ul>
		</div>
	<?php endforeach ?>
</nav>

<div id="rocket-content-container" data-error-tab-title="<?php $html->text('ei_error_list_title') ?>"
		data-display-error-label="<?php $html->text('core_display_error_label') ?>">
	<div class="rocket-main-layer">
		<div class="rocket-zone" data-jhtml-comp="rocket-page">
			<header>
				<?php if (null !== ($activeBreadcrumb = $templateModel->getActiveBreadcrumb())): ?>
					<ol class="breadcrumb">
						<?php foreach ($templateModel->getBreadcrumbs() as $breadcrumb): ?>
							<li class="breadcrumb-item"><?php $html->link($breadcrumb->getUrl(), (string) $breadcrumb->getLabel(),
									($breadcrumb->isJhtml() ? array('data-jhtml' => 'true', 'data-jhtml-use-page-scroll-pos' => 'true') : null)) ?></li>
						<?php endforeach ?>
						<li class="breadcrumb-item active">
							<?php $html->link($activeBreadcrumb->getUrl(), (string) $activeBreadcrumb->getLabel(),
								($activeBreadcrumb->isJhtml() ? array('data-jhtml' => 'true', 'data-jhtml-use-page-scroll-pos' => 'true') : null)) ?>
						</li>
					</ol>
				<?php endif ?>

				<!-- WICHTIG -->

				<?php if (isset($view->params['title'])): ?>
					<h1><?php $html->out($view->params['title']) ?></h1>
				<?php else: ?>
					<h1>Rocket</h1>
				<?php endif ?>
			</header>

			<?php $html->messageList(null, Message::SEVERITY_ERROR, array('class' => 'rocket-messages alert alert-danger list-unstyled')) ?>
			<?php $html->messageList(null, Message::SEVERITY_INFO, array('class' => 'rocket-messages alert alert-info list-unstyled')) ?>
			<?php $html->messageList(null, Message::SEVERITY_WARN, array('class' => 'rocket-messages alert alert-warn list-unstyled')) ?>
			<?php $html->messageList(null, Message::SEVERITY_SUCCESS, array('class' => 'rocket-messages alert alert-success list-unstyled')) ?>

			<div class="rocket-content <?php $html->esc($view->hasPanel('additional') ? ' rocket-contains-additional' : '') ?>"
				 data-error-list-label="<?php $html->text('ei_error_list_title') ?>">
				<?php $view->importContentView() ?>
			</div>

			<?php if ($view->hasPanel('additional')): ?>
				<div id="rocket-additional">
					<?php $view->importPanel('additional') ?>
				</div>
			<?php endif ?>

			<!-- NICHT MEHR WICHTIG -->
		</div>
	</div>
</div>
