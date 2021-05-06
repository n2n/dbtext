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

	use rocket\impl\ei\component\prop\file\command\model\ThumbModel;
	use n2n\impl\web\ui\view\html\HtmlView;
	use n2n\impl\web\ui\view\html\img\UiComponentFactory;
	use n2n\web\ui\Raw;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
	
	$view->useTemplate('~\core\view\template.html');
	
	$thumbModel = $view->getParam('thumbModel');
	$view->assert($thumbModel instanceof ThumbModel);
	
	$imageFile = $thumbModel->getImageFile();
	
	//$html->meta()->addJs('impl/js/image-resizer.js');
	//$html->meta()->addJs('impl/js/thumbs.js');
	$html->meta()->addCss('impl/css/image-resizer.css');
	
	$html->meta()->addCss('impl/js/thirdparty/magnific-popup/magnific-popup.min.css', 'screen');
	$html->meta()->addJs('impl/js/thirdparty/magnific-popup/jquery.magnific-popup.min.js');
	$html->meta()->addJs('impl/js/image-preview.js');
?>

<div class="rocket-group rocket-simple-group">
	<label><?php $html->out($imageFile->getFile()->getOriginalName()) ?></label>
	<div class="rocket-image-resizer-container rocket-structure-content">
		<?php $formHtml->open($thumbModel, null, null, array('class' => 'rocket-form')) ?>
			
			<?php $formHtml->input('x', array('id' => 'rocket-thumb-pos-x')) ?>
			<?php $formHtml->input('y', array('id' => 'rocket-thumb-pos-y')) ?>
			<?php $formHtml->input('width', array('id' => 'rocket-thumb-width')) ?>
			<?php $formHtml->input('height', array('id' => 'rocket-thumb-height')) ?>
					
			<div id="rocket-image-resizer"
					data-img-src="<?php $html->esc(UiComponentFactory::createImgSrc($imageFile)) ?>"
					data-text-fixed-ratio="<?php $html->l10nText('ei_impl_thumb_keep_aspect_ratio_label') ?>"
					data-text-low-resolution="<?php $html->l10nText('ei_impl_thumb_low_resolution_label') ?>" 
					data-text-zoom="Zoom"></div>
			
			<div>
				<h3>Bild-Versionen</h3>
				<ul class="rocket-image-dimensions list-unstyled">
					<?php foreach ($thumbModel->getRatioOptions() as $ratioStr => $label): ?>
						<?php $imageDimensionOptions = $thumbModel->getImageDimensionOptions($ratioStr) ?>
						<?php if (count($imageDimensionOptions) > 1 ): ?>
							<li class="rocket-image-version rocket-image-ratio" data-ratio-str="<?php $html->out($ratioStr) ?>">
								<?php $formHtml->inputRadio('selectedStr', $ratioStr, 
										array('class' => 'rocket-thumb-dimension-radio',
												'data-dimension-str' => (string) $thumbModel->getLargestDimension($ratioStr)), $label) ?>
								<span class="rocket-image-low-res">low res</span>
							</li>
						<?php endif ?>
						<?php foreach ($imageDimensionOptions as $imageDimensionStr => $label): ?>
							<li class="rocket-image-version" data-ratio-str="<?php $html->out($ratioStr) ?>">
								<?php if (null !== ($thumbFile = $imageFile->getThumbFile($thumbModel->getImageDimension($imageDimensionStr)))): ?>
									<?php $label = new Raw($html->getLink($thumbFile->getFileSource()->getUrl(), 
											$html->getImage($thumbFile, null, array('class' => '', 'style' => 'max-width: 30px;max-height:30px'), false, false),
											array('class' => 'rocket-image-previewable')) . $label) ?>
									<?php  ?>
								<?php endif ?>
								<?php $formHtml->inputRadio('selectedStr', $imageDimensionStr, 
										array('class' => 'rocket-thumb-dimension-radio',
												'data-dimension-str' => $imageDimensionStr), $label) ?>
								<span class="rocket-image-low-res">low res</span>
							</li>
						<?php endforeach ?>
					<?php endforeach ?>
				</ul>
			</div>
			
			<div class="rocket-zone-commands">
				<div>
					<?php $formHtml->buttonSubmit('save', new Raw('<i class="fa fa-save"></i><span>' 
									. $html->getL10nText('common_save_label') . '</span>'), 
							array('class' => 'btn btn-primary rocket-important')) ?>
					<?php $html->link($view->getParam('cancelUrl'), 
							new Raw('<i class="fa fa-times-circle"></i><span>' 
									. $html->getL10nText('common_cancel_label') . '</span>'),
							array('class' => 'btn btn-secondary rocket-jhtml', 'data-jhtml-use-page-scroll-pos' => 'true')) ?>
				</div>
			</div>
		<?php $formHtml->close() ?>
	</div>
</div>