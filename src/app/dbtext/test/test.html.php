<?php
	$text = new \dbtext\TextHtmlBuilder($view);

	$ctm = $view->lookup(\dbtext\model\TextService::class)->tc($view->getModuleNamespace(), \n2n\l10n\N2nLocale::build('fr_FR'));
?>
<p>
	<?php $text->text('greeting') ?>
</p>

<p>
	<?php $text->textF('greeting_f', array('Nikolai', 'Schmid')) ?>
</p>

<p>
	<?php $text->text('search_phrase', array('item' => 'sachen')) ?>
</p>

<p>
	<?php $text->text('new_stuff', array('item' => 'sachen')) ?>
</p>