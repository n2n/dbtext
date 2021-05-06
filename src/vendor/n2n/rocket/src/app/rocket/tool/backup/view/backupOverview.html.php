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
	use n2n\impl\web\ui\view\html\HtmlView;
	use n2n\io\fs\FsPath;

	$view = HtmlView::view($view);
	$html = HtmlView::html($view);
	
	$files = $view->getParam('files', false, array()); $view->assert(is_array($files));
	$view->useTemplate('~\core\view\template.html', array('title' => $view->getL10nText('tool_backup_title')));
?>

<div class="rocket-content">
	<table class="table table-hover rocket-table">
		<thead>
			<tr>
				<th><?php $html->l10nText('tool_backup_file_name_label') ?></th>
				<th class="rocket-common-actions-label"><?php $html->l10nText('common_list_tools_label') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($files as $file): $file instanceof FsPath ?>
				<tr> 
					<td>
						<?php $html->out($file->getName()) ?>
					</td>
					<td>
						<div class="rocket-simple-commands">
							<?php $html->linkToController(array('download', $file->getName()), 
										new Raw('<i class="fa fa-download"></i><span>' . $html->getL10nText('common_save_as_label') . '</span>'), 
										array('class' => 'btn btn-secondary', 'title' => $html->getL10nText('common_save_as_label'))) ?>
							<?php $html->linkToController(array('delete', $file->getName()), 
									new Raw('<i class="fa fa-times-circle"></i><span>' . $html->getL10nText('common_delete_label') . '</span>'), 
									array('class' => 'btn btn-secondary', 'title' => $html->getL10nText('common_delete_label'),
											'data-rocket-confirm-cancel-label' => $view->getL10nText('common_no_label'),
											'data-rocket-confirm-ok-label' => $view->getL10nText('common_yes_label'),
											'data-rocket-confirm-msg' => $view->getL10nText('tool_backup_delete_confirm_msg', 
													array('file_name' => $file->getName())))) ?>
						</div>
					</td>
				</tr>
			<?php endforeach ?>
		</tbody>
	</table>
	<div class="rocket-zone-commands">
		<div>
				<?php $html->linkToController(array('create'), 
						new Raw('<i class="fa fa-plus-circle"></i> <span>' . $html->getL10nText('tool_backup_create_label') . '</span>'), 
						array('class' => 'btn btn-primary rocket-important', 
								'title' => $view->getL10nText('tool_backup_create_tooltip'))) ?>

				<?php $html->linkToController(array('delete', '*'), 
						new Raw('<i class="fa fa-times-circle"></i> <span>' . $html->getL10nText('tool_backup_delete_all_label') . '</span>'), 
						array('class' => 'btn btn-secondary', 'title' => $view->getL10nText('tool_backup_delete_all_tooltip'),
								'data-rocket-confirm-cancel-label' => $view->getL10nText('common_no_label'),
								'data-rocket-confirm-ok-label' => $view->getL10nText('common_yes_label'),
								'data-rocket-confirm-msg' => $view->getL10nText('tool_backup_delete_all_confirm_msg'))) ?>
		</div>
	</div>
</div>
