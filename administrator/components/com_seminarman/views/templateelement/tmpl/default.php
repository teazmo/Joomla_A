<?php
/**
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011 Open Source Group GmbH www.osg-gmbh.de
* @website http://www.profinvent.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

defined('_JEXEC') or die('Restricted access');

?>

<form action="index.php?option=com_seminarman&amp;view=templateelement&amp;tmpl=component" method="post" name="adminForm" id="adminForm">

<table class="adminform">
	<tr>
		<td class="proc100"><?php echo JText::_('COM_SEMINARMAN_SEARCH'); ?>
			<input type="text" name="search" id="search" value="<?php echo $this->lists['search']; ?>" class="text_area" onChange="document.adminForm.submit();" />
			<button onclick="this.form.submit();"><?php echo JText::_('COM_SEMINARMAN_GO'); ?></button>
			<button onclick="this.form.getElementById('search').value='';this.form.submit();"><?php echo JText::_('COM_SEMINARMAN_RESET'); ?></button>
		</td>
		<td><?php echo $this->lists['state']; ?></td>
	</tr>
</table>

<table class="adminlist cellspace1">
	<thead>
		<tr>
			<th class="pix5"><?php echo JText::_('COM_SEMINARMAN_NUM'); ?></th>
			<th class="title"><?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_TITLE', 'i.title', $this->lists['order_Dir'], $this->lists['order']); ?></th>
			<th class="proc1"><?php echo JText::_('JPUBLISHED'); ?></th>
			<th class="proc1"><?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_ID', 'i.id', $this->lists['order_Dir'], $this->lists['order']);?></th>
		</tr>
	</thead>

	<tfoot>
		<tr>
			<td colspan="10"><?php echo $this->pageNav->getListFooter(); ?></td>
		</tr>
	</tfoot>

	<tbody>
		<?php

$k = 0;
for ($i = 0, $n = count($this->rows); $i < $n; $i++)
{
    $row = $this->rows[$i];

    if ($row->state == 1)
    {
        $img = 'tick.png';
        $alt = JText::_('JPUBLISHED');
        $state = 1;
    } else
        if ($row->state == 0)
        {
            $img = 'publish_x.png';
            $alt = JText::_('JUNPUBLISHED');
            $state = 0;
        } else
            if ($row->state == -1)
            {
                $img = 'disabled.png';
                $alt = JText::_('COM_SEMINARMAN_ARCHIVED');
                $state = -1;
            } else
                if ($row->state == -2)
                {
                    $img = 'publish_r.png';
                    $alt = JText::_('COM_SEMINARMAN_PENDING');
                    $state = -2;
                } 

?>
		<tr class="<?php echo "row$k"; ?>">
			<td><?php echo $this->pageNav->getRowOffset($i); ?></td>
			<td class="<?php echo $this->direction ? 'left' : 'right'; ?>">
					<a style="cursor:pointer" onclick="window.parent.qfSelectTemplate('<?php echo $row->id; ?>', '<?php echo str_replace(array("'", "\""), array("\\'", ""), $row->title); ?>');">
					<?php echo htmlspecialchars($row->title, ENT_QUOTES, 'UTF-8'); ?>
					</a>
			</td>
			<td class="centered"><?php echo $alt; ?></td>
			<td class="centered"><?php echo $row->id;?></td>
		</tr>
<?php
    $k = 1 - $k;
}
?>
	</tbody>

	</table>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="" />
</form>