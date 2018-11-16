<?php
/**
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011 Open Source Group GmbH www.osg-gmbh.de
* @website http://www.profinvent.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

defined('_JEXEC') or die('Restricted access');

?>

<script type="text/javascript">
Joomla.submitbutton = function(task){
	var form = document.adminForm;
	if (task == 'cancel') {
		Joomla.submitform( task );
		return;
	}

	// do field validation
	if (form.title.value == ""){
		alert( "<?php

echo JText::_('COM_SEMINARMAN_MISSING_TITLE');

?>" );
	} else {
		<?php

echo $this->editor->save('text');

?>
		Joomla.submitform( task );
	}
}
</script>

<form action="index.php" method="post" name="adminForm" id="adminForm">

	<table class="no_space_table">
		<tr>
			<td class="vtop">
				<table  class="adminform">
					<tr>
						<td>
							<label for="title">
								<?php

echo JText::_('COM_SEMINARMAN_CATEGORY') . ':';

?>
							</label>
						</td>
						<td>
							<input name="title" class="inputbox" type="text" value="<?php

echo $this->row->title;

?>" size="50" maxlength="100" />
						</td>
						<td>
							<label for="published">
								<?php

echo JText::_('JPUBLISHED') . ':';

?>
							</label>
						</td>
						<td>
							<?php

$html = JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $this->
    row->published);
echo $html;

?>
						</td>
					</tr>
					<tr>
						<td>
							<label for="alias">
								<?php

echo JText::_('COM_SEMINARMAN_ALIAS') . ':';

?>
							</label>
						</td>
						<td>
							<input class="inputbox" type="text" name="alias" id="alias" size="50" maxlength="100" value="<?php

echo $this->row->alias;

?>" />
						</td>
						<td>
							<label for="parent">
								<?php

echo JText::_('COM_SEMINARMAN_PARENT') . ':';

?>
							</label>
						</td>
						<td>
							<?php

echo $this->Lists['parent_id'];

?>
						</td>
					</tr>
<?php 
    echo $this->Lists['select_vm'];
?>					
				</table>

				<table class="adminform">
					<tr>
						<td>
							<?php


echo $this->editor->display('text', $this->row->text, '100%;', '350', '75', '20',
    array('pagebreak', 'readmore'));

?>
						</td>
					</tr>
				</table>

			</td>
			<td class="category_td vtop">
<?php
	//$title = JText::_('ACCESS');
	echo $this->pane->startPane('det-pane');
?>
<?php
	$title = JText::_('COM_SEMINARMAN_IMAGE');
	echo $this->pane->startPanel($title, 'IMAGE');
?>
				<table>
					<tr>
						<td>
							<label for="image">
								<?php

echo JText::_('COM_SEMINARMAN_SELECT_IMAGE') . ':';

?>
							</label>
						</td>
						<td>
							<?php

echo $this->Lists['imagelist'];

?>
						</td>
					</tr>
					<tr>
						<td></td>
						<td>
							<script type="text/javascript">
								if (document.forms[0].image.options.value!=''){
									jsimg='../images/' + getSelectedValue( 'adminForm', 'image' );
								} else {
									jsimg='';
								}
								document.write('<img src="' + jsimg + '" name="imagelib" class="category_image" alt="<?php echo JText::_('COM_SEMINARMAN_PREVIEW'); ?>" />');
							</script>
							<br /><br />
						</td>
					</tr>
					<tr>
						<td>
							<label for="icon">
								<?php

								echo JText::_('COM_SEMINARMAN_SELECT_ICON') . ':';

								?>
							</label>
						</td>
						<td>
							<?php

							echo $this->Lists['iconlist'];

							?>
						</td>
					</tr>
					<tr>
						<td></td>
						<td>
							<script type="text/javascript">
								if (document.forms[0].icon.options.value!=''){
									jsimg='../images/' + getSelectedValue( 'adminForm', 'icon' );
								} else {
									jsimg='';
								}
								document.write('<img src="' + jsimg + '" name="iconlib" class="category_icon" alt="<?php echo JText::_('COM_SEMINARMAN_PREVIEW'); ?>" />');
							</script>
							<br /><br />
						</td>
					</tr>
				</table>
				<?php

$title = JText::_('COM_SEMINARMAN_METADATA_INFORMATION');
echo $this->pane->endPanel();
echo $this->pane->startPanel($title, 'COM_SEMINARMAN_METADATA_INFORMATION');
?>
				<table>
					<tr>
						<td>
							<label for="metadesc">
								<?php

echo JText::_('COM_SEMINARMAN_DESCRIPTION');

?>:
							</label>
							<br />
							<textarea class="inputbox category_textarea" cols="40" rows="5" name="meta_description" id="metadesc"><?php

echo str_replace('&', '&amp;', $this->row->meta_description);

?></textarea>
						</td>
					</tr>
					<tr>
						<td>
							<label for="metakey">
								<?php

echo JText::_('COM_SEMINARMAN_KEYWORDS');

?>:
							</label>
							<br />
							<textarea class="inputbox category_textarea" cols="40" rows="5" name="meta_keywords" id="metakey"><?php

echo str_replace('&', '&amp;', $this->row->meta_keywords);

?></textarea>
						</td>
					</tr>
					<tr>

					</tr>
				</table>
				<?php

echo $this->pane->endPanel();
$title = JText::_('COM_SEMINARMAN_PARAMETERS_ADVANCED');
echo $this->pane->startPanel($title, 'COM_SEMINARMAN_PARAMETERS_ADVANCED');
?>
                <h3><?php echo JText::_('COM_SEMINARMAN_SORT_COURSES'); ?></h3>
                <table>
                	<tr>
						<td>
							<label for="paramssorting_option">
								<?php echo JText::_('COM_SEMINARMAN_ORDERING') . ':'; ?>
							</label>
						</td>
						<td>
                                <?php echo $this->Lists['sorting_option']; ?>
						</td>
					</tr>
                	<tr>
						<td>
							<label for="paramssorting_direction">
								<?php echo JText::_('COM_SEMINARMAN_ORDERING_DIRECTION') . ':'; ?>
							</label>
						</td>
						<td>
                                <?php echo $this->Lists['sorting_direction']; ?>
						</td>
					</tr>
                </table>
<?php
echo $this->pane->endPanel();
echo $this->pane->endPane();
?>
			</td>
		</tr>
	</table>

<?php

echo JHTML::_('form.token');

?>
<input type="hidden" name="option" value="com_seminarman" />
<input type="hidden" name="id" value="<?php

echo $this->row->id;

?>" />

<input type="hidden" name="controller" value="categories" />
<input type="hidden" name="view" value="category" />
<input type="hidden" name="task" value="" />
</form>

<?php


JHTML::_('behavior.keepalive');

?>