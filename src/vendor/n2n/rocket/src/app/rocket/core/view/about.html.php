<?php
	use n2n\impl\web\ui\view\html\HtmlView;

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
	$view = HtmlView::view($this);
	$html = HtmlView::html($view);

	$view->useTemplate('~\core\view\template.html',
			array('title' => $view->getL10nText('about_title')));
?>
<div class="rocket-group rocket-simple-group">

	<label><?php $html->l10nText('about_credits') ?></label>
	<div class="rocket-structure-content">
		<p>Rocket basiert auf dem PHP Framework n2n. n2n ist ein Produkt von Hofmänner New Media, Winterthur.</p>
		<h3><?php $html->l10nText('about_credits_title')?></h3>
		<dl class="rocket-about-creators">
			<dt>Bert Hofmänner</dt>
			<dd>Idee, Frontend UX, Konzept</dd>
			<dt>Andreas von Burg</dt>
			<dd>Architektur, Lead Developer, Konzept</dd>
			<dt>Thomas Günther</dt>
			<dd>Developer, Frontend UI</dd>
			<dt>Timo Schwertle</dt>
			<dd>UI/UX Design</dd>
		</dl>
	</div>
</div>
<div class="rocket-group rocket-simple-group">
	<label><?php $html->l10nText('about_license') ?></label>
	<div class="rocket-structure-content">
		<h3><?php $html->l10nText('about_license_title')?></h3>
	</div>
</div>
