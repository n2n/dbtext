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

	use rocket\user\model\GroupGrantsViewModel;
	use n2n\impl\web\ui\view\html\HtmlView;
	use n2n\web\http\nav\Murl;
	use n2n\impl\web\ui\view\html\HtmlElement;
	
	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
	
	
	$groupGrantsViewModel = $view->getParam('groupGrantsViewModel');
	$view->assert($groupGrantsViewModel instanceof GroupGrantsViewModel);
	
	$view->useTemplate('~\core\view\template.html', array('title' => $view->getL10nText('user_group_grants_of_txt', 
			['user_group' => $groupGrantsViewModel->getRocketUserGroup()->getName()])));
	
	$groupId = $groupGrantsViewModel->getRocketUserGroup()->getId()
?>


<table class="table table-hover rocket-table">
	<thead>
		<tr>
			<th><?php $html->l10nText('common_name_label') ?></th>
			<th><?php $html->l10nText('user_power_label') ?></th>
			<th><?php $html->l10nText('common_list_tools_label') ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($groupGrantsViewModel->getEiTypeItems() as $eiTypePath => $eiTypeItem): ?>
			<tr class="rocket-tree-level-<?php $html->out($eiTypeItem->getLevel()) ?>">
				<td>
					[<?php $html->out($eiTypeItem->isExtension() ? 'Extension' : 'Type') ?>]
					<?php $html->esc($eiTypeItem->getLabel()) ?>
				</td>
				<?php if ($eiTypeItem->isFullyAccessible()): ?>
					<td class="rocket-access-type-full"><?php $html->text('user_full_access_label') ?></td>
				<?php elseif ($eiTypeItem->isAccessible()): ?>
					<td class="rocket-access-type-restricted"><?php $html->text('user_restricted_access_label') ?></td>
				<?php else: ?>
					<td class="rocket-access-type-denied"><?php $html->text('user_denied_access_label') ?></td>
				<?php endif ?>
				<td class="rocket-table-commands">
					<div class="rocket-simple-commands">
						<?php if ($eiTypeItem->isFullyAccessible()): ?>
							<?php $html->linkStart(Murl::controller()->pathExt(['removeeigrant', $groupId, $eiTypePath]),
									['class' => 'btn btn-success rocket-important rocket-jhtml']) ?>
								<i class="fa fa-thumbs-up"></i>
							<?php $html->linkEnd() ?>
						<?php elseif ($eiTypeItem->isAccessible()): ?>
							<?php $html->linkToController(array('fullyeigrant', $groupId, $eiTypePath), 
									new HtmlElement('i', ['class' => 'fa fa-thumbs-o-up fa-rotate-270'], ''),
									array('class' => 'btn btn-secondary rocket-jhtml')) ?>
						<?php else: ?>
							<?php $html->linkToController(array('fullyeigrant', $groupId, $eiTypePath),
									new HtmlElement('i', ['class' => 'fa fa-thumbs-down'], ''),
									array('class' => 'btn btn-danger rocket-important rocket-jhtml')) ?>
						<?php endif ?>
						
						<?php $html->linkStart(Murl::controller()->pathExt(['restricteigrant', $groupId, $eiTypePath]),
								['class' => 'btn btn-secondary rocket-impotant rocket-jhtml', 
										'title' => $view->getL10nText('user_type_access_label', ['type' => $eiTypeItem->getLabel()])]) ?>
							<i class="fa fa-gear"></i>
						<?php $html->linkEnd() ?>
					</div>
				</td>
			</tr>
		<?php endforeach ?>	
		
		<?php foreach ($groupGrantsViewModel->getCustomItems() as $customSpecId => $customItem): ?>
			<tr>
				<td><?php $html->esc($customItem->getLabel()) ?></td>
				<?php if ($customItem->isFullyAccessible()): ?>
					<td class="rocket-access-type-full"><?php $html->text('user_full_access_label') ?></td>
				<?php elseif ($customItem->isAccessible()): ?>
					<td class="rocket-access-type-restricted"><?php $html->text('user_restricted_access_label') ?></td>
				<?php else: ?>
					<td class="rocket-access-type-denied"><?php $html->text('user_denied_access_label') ?></td>
				<?php endif ?>
				<td>
					<div class="rocket-simple-commands">
						<?php $html->linkToController(array('fullycustomgrant', $groupId, $customSpecId), 
								new n2n\web\ui\Raw('<i class="fa fa-thumbs-up"></i><span>' 
										. $html->getL10nText('user_edit_group_label') . '</span>'),
								array('title' => $html->getL10nText('user_edit_tooltip'),
										'class' => 'btn btn-success')) ?>
						<?php $html->linkToController(array('restrictcustomgrant', $groupId, $customSpecId), 
								new n2n\web\ui\Raw('<i class="fa fa-wrench"></i><span>' 
										. $html->getL10nText('user_edit_group_label') . '</span>'),
								array('title' => $html->getL10nText('user_edit_tooltip'),
										'class' => 'btn btn-warning')) ?>
						<?php $html->linkToController(array('removecustomgrant', $groupId, $customSpecId),
								new n2n\web\ui\Raw('<i class="fa fa-thumbs-down"></i><span>' 
										. $html->getL10nText('user_edit_group_label') . '</span>'),
								array('title' => $html->getL10nText('user_edit_tooltip'),
										'class' => 'btn btn-danger')) ?>
					</div>
				</td>
			</tr>
		<?php endforeach ?>
	</tbody>
</table>