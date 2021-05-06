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

	use rocket\user\model\RocketUserDao;
	use n2n\impl\web\ui\view\html\HtmlView;
	
	$view = HtmlView::view($this);
	$html = HtmlView::html($view);
	
	$userDao = $view->lookup('rocket\user\model\RocketUserDao'); $userDao instanceof RocketUserDao;
	$useSuccessfullLogins = $view->getParam('useSuccessfull', false, true);
	$logins = array();
	if ($useSuccessfullLogins) {
		$logins = $userDao->getSuccessfullLogins(0, 5);
	} else {
		$logins = $userDao->getFailedLogins();
	}
?>
<table class="table table-hover rocket-table">
	<thead>
		<tr>
			<th><?php $html->l10nText('user_nick_label') ?></th>
			<th><?php $html->l10nText('core_ip_label') ?></th>
			<?php if ($useSuccessfullLogins) : ?>
				<th><?php $html->l10nText('user_power_label') ?></th>
			<?php endif ?>
			<th><?php $html->l10nText('common_date_label') ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ((array) $logins as $login ) : ?>
			<tr>
				<td><?php $html->out($login->getNick()) ?></td>
				<td><?php $html->out($login->getIp()) ?></td>
				<?php if ($useSuccessfullLogins) : ?>
					<td><?php $html->out($login->getPower()) ?></td>
				<?php endif ?>
				<td><?php $html->out($html->getL10nDateTime($login->getDateTime())) ?></td>
			</tr>
		<?php endforeach ?>
	</tbody>
</table>
