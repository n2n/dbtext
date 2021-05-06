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

	use n2n\web\ui\Raw;
	use rocket\user\bo\RocketUser;
	use n2n\impl\web\ui\view\html\HtmlView;
	
	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
	
	$view->useTemplate('~\core\view\template.html', array('title' => $view->getL10nText('user_title')));
	
	$users = $view->getParam('users');
	$loggedInUser = $view->getParam('loggedInUser');
	$view->assert($loggedInUser instanceof RocketUser);
?>

<table class="table table-hover rocket-table">
	<thead>
		<tr>
			<th><?php $html->l10nText('common_id_label') ?></th>
			<th><?php $html->l10nText('user_nick_label') ?></th>
			<th><?php $html->l10nText('common_name_label') ?></th>
			<th><?php $html->l10nText('user_email_label') ?></th>
			<th><?php $html->l10nText('user_power_label') ?></th>
			<th><?php $html->l10nText('common_list_tools_label') ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($users as $user): $view->assert($user instanceof RocketUser)?>
			<tr>
				<td><?php $html->esc($user->getId()) ?></td>
				<td><?php $html->esc($user->getNick()) ?></td>
				<td><?php $html->esc($user->getFirstname()) ?> <?php $html->esc($user->getLastname()) ?></td>
				<td><?php $html->esc($user->getEmail()) ?></td>
				<td><?php $html->esc($user->getPower()) ?></td>
				<td>
					<div class="rocket-simple-commands">
						<?php if ($loggedInUser->isSuperAdmin() || $user->equals($loggedInUser)): ?>
							<?php $html->linkToController(array('edit', $user->getId()), 
									new n2n\web\ui\Raw('<i class="fa fa-pencil"></i><span>' . $view->getL10nText('user_edit_label') . '</span>'),
									array('title' => $view->getL10nText('user_edit_tooltip'),
											'class' => 'btn btn-warning')) ?>
						<?php endif ?>
						<?php if ($loggedInUser->isSuperAdmin() && !$user->equals($loggedInUser)): ?>
							<?php $html->linkToController(array('delete', $user->getId()), 
									new n2n\web\ui\Raw('<i class="fa fa-times"></i><span>' . $view->getL10nText('user_delete_label') . '</span>'),
									array('title' => $view->getL10nText('user_delete_tooltip'), 
											'data-rocket-confirm-msg' => $view->getL10nText('user_delete_confirm', array('user' => $user->getNick())),
											'data-rocket-confirm-ok-label' => $view->getL10nText('common_yes_label'),
											'data-rocket-confirm-cancel-label' => $view->getL10nText('common_no_label'),
											'class' => 'btn btn-danger')) ?>
						<?php endif ?>
					</div>
				</td>
			</tr>
		<?php endforeach ?>	
	</tbody>
</table>

<?php if ($loggedInUser->isSuperAdmin()): ?>
	<div class="rocket-zone-commands">
		<div>
			<?php $html->linkToController('add', new Raw('<i class="fa fa-plus-circle"></i> <span>' 
							. $view->getL10nText('user_add_label') . '</span>'), 
					array('class' => 'btn btn-primary')) ?>
		</div>
	</div>
<?php endif ?>
