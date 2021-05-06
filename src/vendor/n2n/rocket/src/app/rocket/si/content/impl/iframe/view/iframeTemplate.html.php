<?php
    use n2n\impl\web\ui\view\html\HtmlView;
    use n2n\core\N2N;
    use n2n\web\ui\UiComponent;

    $view = HtmlView::view($this);
    $html = htmlView::html($view);
    $request = HtmlView::request($view);

    $uiComponent = $view->getParam('uiComponent', false);
    $view->assert($uiComponent === null || $uiComponent instanceof UiComponent);

    $html->meta()->setTitle('helloWorld');
    $html->meta()->addCss('css/rocket-30.css');
?>
<!DOCTYPE html>
<html lang="<?php $html->out($request->getN2nLocale()->getLanguage()->getShort()) ?>" class="rocket-iframe">
	<?php $html->headStart() ?>
		<meta charset="<?php $html->out(N2N::CHARSET) ?>" />
	<?php $html->headEnd() ?>
	<?php $html->bodyStart() ?>
		<?php $view->out($uiComponent) ?>
		<?php $view->importContentView() ?>
	<?php $html->bodyEnd() ?>
</html>
