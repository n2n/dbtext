<?php
	use n2n\impl\web\ui\view\html\HtmlView;
	use rocket\core\model\AnglTemplateModel;
use n2n\core\N2N;
use rocket\core\model\Rocket;

	$view = HtmlView::view($this);
	$html = HtmlView::html($view);
	
	if (isset($_SERVER['ROCKET_DEV'])) {
		$html->meta()->bodyEnd()->addJs('angl-dev/runtime.js');
		$html->meta()->bodyEnd()->addJs('angl-dev/polyfills.js');
		$html->meta()->bodyEnd()->addJs('angl-dev/vendor.js');
		$html->meta()->bodyEnd()->addJs('angl-dev/main.js');
	} else {
		$html->meta()->bodyEnd()->addJs('angl/runtime.js?v=' . Rocket::VERSION, null, false, false, ['defer']);
		$html->meta()->bodyEnd()->addJs('angl/polyfills.js?v=' . Rocket::VERSION, null, false, false, ['defer']);
		$html->meta()->bodyEnd()->addJs('angl/main.js?v=' . Rocket::VERSION, null, false, false, ['defer']);
	}
	
// 	$html->meta()->bodyEnd()->addCssUrl('https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.11/cropper.min.css');
	
	$view->useTemplate('boilerplate.html', $view->getParams());
	
	$html->meta()->addCssCode('
			rocket-ui-structure-branch {
				display: block;
			}

			.rocket-highlighed {
			    animation: last-mod-transition 30s;
			    background: inherit;
			}

			.rocket-locked .rocket-group,
			.rocket-highlighed .rocket-group {
 				background: transparent !important;
			}

			.rocket-marked {
				outline: 3px solid #dc3545;
				position: relative;
				background: rgba(220, 53, 69, 0.1);
			}
			
			.rocket-marked-remember {
				outline: 3px solid transparent;
				-webkit-transition: outline 1s;
				transition: outline 1s;
			}
	');
	
	$anglTemplateModel = $view->lookup(AnglTemplateModel::class);
	$view->assert($anglTemplateModel instanceof AnglTemplateModel);
?>

<rocket-root data-rocket-angl-data="<?php $html->out(json_encode($anglTemplateModel->createData($view->getControllerContext()))) ?>"
		data-rocket-assets-url="<?php $html->out($html->meta()->getAssetUrl(null, 'rocket')) ?>"
		data-locale-id="<?php $html->out($request->getN2nLocale()->toWebId(true)) ?>"></rocket-root>