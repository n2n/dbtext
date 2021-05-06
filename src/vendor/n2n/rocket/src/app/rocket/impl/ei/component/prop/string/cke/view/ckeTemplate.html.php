<?php
    use n2n\impl\web\dispatch\ui\FormHtmlBuilder;
    use n2n\impl\web\ui\view\html\HtmlView;
	use n2n\core\N2N;
    use n2nutil\jquery\JQueryLibrary;
    use page\ui\PageHtmlBuilder;
    use rocket\impl\ei\component\prop\string\cke\conf\CkeEditorConfig;
    use rocket\impl\ei\component\prop\string\cke\ui\CkeComposer;
    use rocket\impl\ei\component\prop\string\cke\ui\CkeHtmlBuilder;
    use rocket\impl\ei\component\prop\string\cke\ui\CkeLibrary;

    $view = HtmlView::view($this);
	$html = htmlView::html($view);
	$request = HtmlView::request($view);

	$formHtml = new FormHtmlBuilder($view);
    $ckeHtml = new CkeHtmlBuilder($view);
    $pageHtml = new PageHtmlBuilder($view);

    /**
     * @var CkeComposer $ckeInField
     */
    $ckeComposer = $view->getParam('composer');
    $view->assert($ckeComposer instanceof CkeComposer);

    /**
     * @var CkeEditorConfig $config
     */
    $config = $view->getParam('config');
    $view->assert($config instanceof CkeEditorConfig);

    $html->meta()->addLibrary(new JQueryLibrary(3));
    $html->meta()->addLibrary(new CkeLibrary());

    $attrs = array('name' => 'content');
?>
<!DOCTYPE html>
<html lang="<?php $html->out($request->getN2nLocale()->getLanguage()->getShort()) ?>">
	<?php $html->headStart() ?>
		<meta charset="<?php $html->out(N2N::CHARSET) ?>" />
	<?php $html->headEnd() ?>
	<?php $html->bodyStart(array('style' => 'margin: 0')) ?>

        <style>
            /* fix cke collapse on focus */
            .cke_contents {
                height: 100% !important;
            }
        </style>

        <script>
            CKEDITOR.config.removeButtons = 'Maximize'; // maximize causes cke height = 0. Needs to be fixed

            CKEDITOR.on('instanceReady', function(e) {
                var iframeJq = $(window.parent.document.getElementsByTagName("iframe")[0]);

                var cke = CKEDITOR.instances['content'];
                cke.on('change', function() {
                    cke.updateElement();
                    var element = cke.element.$;
                    if ('createEvent' in document) {
                        var event = document.createEvent('HTMLEvents');
                        event.initEvent('change', false, true);
                        element.dispatchEvent(event);
                    } else {
                        element.fireEvent('onchange');
                    }
                });

                CKEDITOR.on('dialogDefinition', function (e) {
                    var initialHeight = iframeJq.css('height');

                    var dialog = e.data.definition.dialog;
                    dialog.on('show', function () {
                        iframeJq.css('height', this.getSize().height + 50);
                    });
                    dialog.on('hide', function () {
                        iframeJq.css('height', initialHeight);
                    });
                });

            });
        </script>

        <?php $html->out($ckeHtml->getTextarea('', $ckeComposer, $config->getCkeCssConfig(),
            $config->getCkeLinkProviders()->getArrayCopy(), $attrs)) ?>
    <?php $html->bodyEnd() ?>
</html>
