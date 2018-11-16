<?php
/**
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011 Open Source Group GmbH www.osg-gmbh.de
* @website http://www.profinvent.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

defined('_JEXEC') or die('Restricted access');
$jversion = new JVersion();
$short_version = $jversion->getShortVersion();
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">

	<table class="adminform table">
		<tr>
			<td class="proc100 left">
			  	<?php echo JText::_('Filter'); ?>:
				<input type="text" name="search" id="search" value="<?php echo $this->lists['search']; ?>" class="text_area" onChange="document.adminForm.submit();" />
				<input type="button" onclick="this.form.submit();" value="<?php echo JText::_('COM_SEMINARMAN_GO');?>" />
				<input type="button" onclick="document.getElementById('search').value='';this.form.getElementById('filter_state').value='';this.form.submit();" value="<?php echo JText::_('COM_SEMINARMAN_RESET'); ?>" />
			</td>
			<td>
				<?php echo $this->lists['state']; ?>
			</td>
		</tr>
	</table>

	<table class="adminlist table table-striped" style="border-collapse: separate; border-spacing: 1px;">
	<thead>
		<tr>
			<th width="5"><?php

echo JText::_('COM_SEMINARMAN_NUM');

?></th>
         <?php if (version_compare($short_version, "3.0", 'ge')): ?>
			<th width="5"><?php echo JHtml::_('grid.checkall'); ?></th>
         <?php else: ?>
			<th width="5"><input type="checkbox" name="toggle" value="" onClick="checkAll(<?php echo count($this->rows); ?>);" /></th>
         <?php endif; ?>
			<th class="title"><?php

echo JHTML::_('grid.sort', 'COM_SEMINARMAN_CATEGORY', 'c.title', $this->lists['order_Dir'], $this->
    lists['order']);

?></th>
			<th width="20%"><?php

echo JHTML::_('grid.sort', 'COM_SEMINARMAN_ALIAS', 'c.alias', $this->lists['order_Dir'], $this->
    lists['order']);

?></th>
			<th width="6%"><?php

echo JHTML::_('grid.sort', 'COM_SEMINARMAN_ASSIGNED_TO', 'nrassigned', $this->lists['order_Dir'],
    $this->lists['order']);

?></th>
			<th width="1%"><?php

echo JText::_('JPUBLISHED');

?></th>
			<th width="8%"><?php

echo JHTML::_('grid.sort', 'COM_SEMINARMAN_ORDER', 'c.ordering', $this->lists['order_Dir'], $this->
    lists['order']);

?></th>
			<th width="1%"><?php

echo JHTML::_('grid.order', $this->rows, 'filesave.png', 'saveorder');

?></th>
			<th width="1%"><?php

echo JHTML::_('grid.sort', 'COM_SEMINARMAN_ID', 'c.id', $this->lists['order_Dir'], $this->
    lists['order']);

?></th>
		</tr>
	</thead>

	<tfoot>
		<tr>
			<td colspan="9">
				<?php

echo $this->pageNav->getListFooter();

?>
			</td>
		</tr>
	</tfoot>

	<tbody>
		<?php

$k = 0;
$i = 0;
$n = count($this->rows);
foreach ($this->rows as $row)
{

    $link = 'index.php?option=com_seminarman&amp;controller=categories&amp;task=edit&amp;cid[]=' .
        $row->id;
    if (version_compare($short_version, "3.0", 'ge')) {
    	$published = JHTML::_('jgrid.published', $row->published, $i);
	} else {
		$published = JHTML::_('grid.published', $row, $i);
	}
    $checked = JHTML::_('grid.checkedout', $row, $i);

?>
		<tr class="<?php

    echo "row$k";

?>">
			<td><?php

    echo $this->pageNav->getRowOffset($i);

?></td>
			<td width="7"><?php

    echo $checked;

?></td>
			<td align="<?php

    echo $this->direction ? 'left' : 'right';

?>">
				<?php

    if ($row->checked_out && ($row->checked_out != $this->user->get('id')))
    {
        echo '<span class="editlinktip hasTip" title="The record is checked out::by user ' . $row->checked_out . '">' . $row->treename . htmlspecialchars($row->title, ENT_QUOTES, 'UTF-8') . '</span>';
    } else
    {

?>
					<span class="editlinktip hasTip" title="<?php

        echo JText::_('COM_SEMINARMAN_EDIT');

?>::<?php

        echo $row->alias;

?>">
					<?php

        echo $row->treename . ' ';

?>
					<a href="<?php

        echo $link;

?>">
					<?php

        echo htmlspecialchars($row->title, ENT_QUOTES, 'UTF-8');

?>
					</a></span>
				<?php

    }

?>
			</td>
			<td>
				<?php

    if (JString::strlen($row->alias) > 25)
    {
        echo JString::substr(htmlspecialchars($row->alias, ENT_QUOTES, 'UTF-8'), 0, 25) .
            '...';
    } else
    {
        echo htmlspecialchars($row->alias, ENT_QUOTES, 'UTF-8');
    }

?>
			</td>
			<td align="center"><?php

    echo $row->nrassigned

?></td>
			<td align="center">
				<?php

    echo $published;

?>
			</td>
			<td class="order" colspan="2">
				<span><?php

    echo $this->pageNav->orderUpIcon($i, true, 'orderup', 'Move Up', $this->
        ordering);

?></span>

				<span><?php

    echo $this->pageNav->orderDownIcon($i, $n, true, 'orderdown', 'Move Down', $this->
        ordering);

?></span>

				<?php

    $disabled = $this->ordering ? '' : 'disabled="disabled"';

?>

				<input type="text" name="order[]" size="2" value="<?php echo $row->ordering; ?>" <?php echo $disabled; ?> class="text_area pull-right" style="text-align: center" />
			</td>
			<td align="center"><?php echo $row->id; ?></td>
		</tr>
<?php
    $k = 1 - $k;
    $i++;
}
?>
	</tbody>

	</table>

	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="option" value="com_seminarman" />
	<input type="hidden" name="controller" value="categories" />
	<input type="hidden" name="view" value="categories" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="filter_order" value="<?php

echo $this->lists['order'];

?>" />
	<input type="hidden" name="filter_order_Dir" value="" />
	<?php

echo JHTML::_('form.token');

?>
</form>