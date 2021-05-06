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
	
	use n2n\core\N2N;
	use n2n\web\ui\Raw;
	use rocket\core\model\TemplateModel;
	use n2n\impl\web\ui\view\html\HtmlView;
	use n2n\web\http\nav\Murl;
	use rocket\user\model\LoginContext;
	use rocket\core\controller\RocketController;
	
	$view = HtmlView::view($this);
	$request = HtmlView::request($view);
	$html = HtmlView::html($view);
	$httpContext = HtmlView::httpContext($view);
	
	/**
	 * @var LoginContext $loginContext
	 */
	$loginContext = $view->lookup(LoginContext::class);
	$view->assert($loginContext instanceof LoginContext);
	
	// 	$rocket = $view->lookup('rocket\core\model\Rocket');
	// 	$view->assert($rocket instanceof Rocket);
	
	// 	$rocketState = $view->lookup('rocket\core\model\RocketState');
	// 	$view->assert($rocketState instanceof RocketState);
	
	// 	$manageState = $view->lookup('rocket\ei\manage\ManageState');
	// 	$view->assert($manageState instanceof ManageState);
	
	$htmlMeta = $html->meta();
	
	$htmlMeta->addMeta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0'));
	$htmlMeta->addMeta(array('content' => 'IE=edge', 'http-equiv' => 'x-ua-compatible'));
	$htmlMeta->addMeta(array('name' => 'robots', 'content' => 'noindex, nofollow'));
	
	// new design (not ready yet):
	$htmlMeta->addCss('css/rocket-30.css');
	// old design:
	//	$htmlMeta->addCss('css/rocket.css');
	$htmlMeta->addCss('css/icons/font-awesome/icons/all.min.css');
	// compatible with font awesome 4 classnames
	$htmlMeta->addCss('css/icons/font-awesome/icons/v4-shims.min.css');
// 	use this to insert custom icons
// 	$htmlMeta->addCss('css/icons/icomoon/icomoon.css');
	// 	$htmlMeta->addJs('js/respond.src.js', null);
	// 	$htmlMeta->addJs('js/jquery-responsive-table.js', null, true);
	
	
	// 	$spec = $rocket->getSpec();
	// 	$menuGroups = $spec->getMenuGroups();
	// 	$selectedLaunchPad = $manageState->getSelectedLaunchPad();
	// 	$breadcrumbs = $rocketState->getBreadcrumbs();
	// 	$activeBreadcrumb = array_pop($breadcrumbs);
	$htmlMeta->addLink(array('rel' => 'shortcut icon', 'href' => $httpContext->getAssetsUrl('rocket')->ext(array('img', 'favicon.ico'))));
	$htmlMeta->addLink(array('rel' => 'apple-touch-icon', 'href' => $httpContext->getAssetsUrl('rocket')->ext(array('img', 'apple-touch-icon.png'))));
?>
<!DOCTYPE html>
<html lang="<?php $html->out($request->getN2nLocale()->getLanguage()->getShort()) ?>">
	<?php $html->headStart() ?>
		<meta charset="<?php $html->out(N2n::CHARSET) ?>" />
		<base href="<?php $html->out($view->buildUrl(Murl::controller(RocketController::class))->getPath()->chEndingDelimiter(true)) ?>" />
	<?php $html->headEnd() ?>
	<?php $html->bodyStart(array('data-refresh-path' => $view->buildUrl(Murl::controller('rocket')),
			'class' => (isset($view->params['tmplMode']) ? $view->params['tmplMode'] : null))) ?>
		
		<?php $view->importContentView() ?>
		
	<?php $html->bodyEnd() ?>
</html>