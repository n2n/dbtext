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

	use rocket\si\control\SiIconType;
	use rocket\ei\util\frame\EiuFrame;
	use n2n\impl\web\ui\view\html\HtmlView;
	
	$view = HtmlView::view($this);
	$html = HtmlView::html($view);
	$formHtml = HtmlView::formHtml($view);
	$httpContext = HtmlView::httpContext($view);
	
	$eiuFrame = $view->getParam('eiuFrame');
	$view->assert($eiuFrame instanceof EiuFrame);

	$view->useTemplate('\rocket\core\view\template.html',
			array('title' => $view->getL10nText('ei_impl_multi_upload_title', 
					array('plural_label' => $eiuFrame->getGenericPluralLabel())))); 
	
	$html->meta()->addJs('impl/js/multiupload/jquery.knob.js');
	$html->meta()->addJs('impl/js/multiupload/jquery.ui.widget.js');
	$html->meta()->addJs('impl/js/multiupload/jquery.iframe-transport.js');
	$html->meta()->addJs('impl/js/multiupload/jquery.fileupload.js');
	$html->meta()->addJs('impl/js/multiupload/multiupload.js');
	$html->meta()->addCss('impl/css/multiupload/multiupload.css');
?>
<div class="rocket-content">
	<h3><?php $html->text('ei_impl_multi_upload_label', array('plural_label' => 
					$eiuFrame->getGenericPluralLabel())) ?></h3>
	<form id="rocket-multi-upload-form" method="post" 
			action="<?php $html->out($html->meta()->getControllerUrl(array('upload'))) ?>"
			data-order="<?php $html->out($view->getParam('order', false)) ?>" 
			enctype="multipart/form-data">
		<div id="rocket-multi-upload-drop">
			Drop Here
			<a>Browse</a>
			<input type="file" name="prop-upl" multiple />
		</div>
		<ul>
			<!-- The file uploads will be shown here -->
		</ul>
	</form>
</div>
<div class="rocket-zone-commands">
	<div>
		<a id="rocket-multi-upload-submit" href="#" class="btn btn-primary">
			<i class="<?php $view->out(SiIconType::ICON_UPLOAD)?>"></i>
			<span><?php $html->text('ei_impl_multi_upload_start_label')?></span>
		</a>
		<?php $html->link($eiuFrame->getEiFrame()->getOverviewUrl($httpContext),
				new n2n\web\ui\Raw('<i class="fa fa-times-circle"></i><span>' . $html->getText('common_cancel_label') . '</span>'),
						array('class' => 'btn btn-secondary')) ?>
	</div>
</div>
