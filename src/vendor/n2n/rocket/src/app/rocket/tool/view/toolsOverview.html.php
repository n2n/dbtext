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
	use rocket\tool\controller\ToolController;
	use rocket\si\control\SiIconType;
	use n2n\impl\web\ui\view\html\HtmlView;
	
	$view = HtmlView::view($this);
	$html = HtmlView::html($view);
	
	$view->useTemplate('~\core\view\template.html', array('title' => $view->getL10nText('tool_title')));
?>
<div class="rocket-content">
	<table class="table table-hover rocket-table">
		<thead>
			<tr>
				<th><?php $html->l10nText('tool_title') ?></th>
				<th><?php $html->l10nText('common_list_tools_label') ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<?php $html->l10nText('tool_backup_title') ?>
				</td>
				<td>
					<div class="rocket-simple-commands">
						<?php $html->linkToController(ToolController::ACTION_BACKUP_OVERVIEW, 
								new Raw('<i class="fa fa-hdd-o"></i><span>' 
										. $html->getL10nText('tool_backup_title') . '</span>'), 
								array('class' => 'btn btn-secondary')) ?>
					</div>
				</td>
			</tr>
			<tr>
				<td>
					<?php $html->l10nText('tool_mail_center_title') ?>
				</td>
				<td>
					<div class="rocket-simple-commands">
						<?php $html->linkToController(ToolController::ACTION_MAIL_CENTER, 
								new Raw('<i class="' . SiIconType::ICON_ENVELOPE . '"></i><span>' . $html->getL10nText('tool_mail_center_tooltip') . '</span>'), 
								array('class' => 'btn btn-secondary')) ?>
					</div>
				</td>
			</tr>
			<tr>
				<td>
					<?php $html->l10nText('tool_clear_cache_title') ?>
				</td>
				<td>
					<div class="rocket-simple-commands">
						<?php $html->linkToController(ToolController::ACTION_CLEAR_CACHE, 
								new Raw('<i class="' . SiIconType::ICON_ERASER . '"></i><span>' 
												. $html->getL10nText('tool_clear_cache_title') . '</span>'), 
										array('class' => 'btn btn-secondary', 
												'title' => $html->getL10nText('tool_clear_cache_title'))) ?>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
</div>
