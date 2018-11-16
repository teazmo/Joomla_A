<?php
/**
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011 Open Source Group GmbH www.osg-gmbh.de
* @website http://www.profinvent.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined('_JEXEC') or die('Restricted access');

?>

<?php

JHTML::_('behavior.tooltip');

?>
<?php

jimport('joomla.utilities.date');

?>

<?php
$jversion = new JVersion();
$short_version = $jversion->getShortVersion();

JToolBarHelper::title(JText::_('COM_SEMINARMAN_COUNTRIES'), 'generic.png');
if (version_compare($short_version, "3.0", 'ge')) {
	JToolBarHelper::custom('goback', 'backward-2', 'backward-2', 'COM_SEMINARMAN_GO_BACK', false, true);
} else {
	JToolBarHelper::customX('goback', 'back.png', 'back_f2.png', 'COM_SEMINARMAN_GO_BACK', false, true);
}
JToolBarHelper::divider();
if (version_compare($short_version, "3.0", 'ge')) {
	JToolBarHelper::addNew();
	JToolBarHelper::editList();
} else {
	JToolBarHelper::addNewX();
	JToolBarHelper::editListX();
}
JToolBarHelper::divider();
JToolBarHelper::publishList();
JToolBarHelper::unpublishList();
JToolBarHelper::divider();
JToolBarHelper::deleteList();

?>
<form action="<?php

echo $this->requestURL;

?>" method="post" name="adminForm" id="adminForm">
<table class="adminform">
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
<div id="editcell">
   <table class="adminlist">
   <thead>
      <tr>
         <th width="5">
            <?php

echo JText::_('COM_SEMINARMAN_NUM');

?>
         </th>
         <?php if (version_compare($short_version, "3.0", 'ge')): ?>
         	<th width="20"><?php echo JHtml::_('grid.checkall'); ?></th>
         <?php else: ?>
			<th width="20"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->courses); ?>);" /></th>
         <?php endif; ?>
         <th class="title">
            <?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_TITLE', 'a.title', $this->lists['order_Dir'], $this->lists['order']); ?>
         </th>
         <th width="5%">
            <?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_CODE', 'a.code', $this->lists['order_Dir'], $this->lists['order']); ?>
         </th>
         <th width="5%">
            <?php echo JHTML::_('grid.sort', 'JPUBLISHED', 'a.published', $this->lists['order_Dir'], $this->lists['order']); ?>
         </th>
         <th width="8%"><span style="float:left">
            <?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_ORDER', 'a.ordering', $this->lists['order_Dir'], $this->lists['order']); ?>
            </span>
            <?php echo JHTML::_('grid.order', $this->courses); ?>
         </th>
         <th width="5%">
            <?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_HITS', 'a.hits', $this->lists['order_Dir'], $this->lists['order']); ?>
         </th>
         <th width="1%">
            <?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_ID', 'a.id', $this->lists['order_Dir'], $this->lists['order']); ?>
         </th>
      </tr>
   </thead>
   <tfoot>
      <tr>
         <td colspan="8">
            <?php

echo $this->pagination->getListFooter();

?>
         </td>
      </tr>
   </tfoot>
   <tbody>
   <?php

$k = 0;
for ($i = 0, $n = count($this->courses); $i < $n; $i++)
{
    $row = &$this->courses[$i];

    $link = JRoute::_('index.php?option=com_seminarman&controller=country&task=edit&cid[]=' .
        $row->id);

    $checked = JHTML::_('grid.checkedout', $row, $i);
    if (version_compare($short_version, "3.0", 'ge')) {
    	$published = JHTML::_('jgrid.published', $row->published, $i);
	} else {
		$published = JHTML::_('grid.published', $row, $i);
	}

    $ordering = ($this->lists['order'] == 'a.ordering');

?>
      <tr class="<?php

    echo "row$k";

?>">
         <td>
            <?php

    echo $this->pagination->getRowOffset($i);

?>
         </td>
         <td>
            <?php

    echo $checked;

?>
         </td>
         <td>
            <?php
	$result = 0;
	if ($row instanceof JTable)
	{
		$result = $row->isCheckedOut( $this->user->get( 'id' ) );
	}
            
	if ( $result )
    {
        echo $this->escape($row->title);
    } 
    else
    {

?>
            <span class="editlinktip hasTip" title="<?php

        echo JText::_('COM_SEMINARMAN_EDIT');

?>::<?php

        echo $this->escape($row->title);

?>">
               <a href="<?php

        echo $link;

?>">
                  <?php

        echo $this->escape($row->title);

?></a></span>
            <?php

    }

?>
         </td>
         <td align="center">
            <?php

    echo $row->code;

?>
         </td>
         <td align="center">
            <?php

    echo $published;

?>
         </td>
         <td class="order">
            <span><?php

    echo $this->pagination->orderUpIcon($i, (true), 'orderup', 'Move Up', $ordering);

?></span>
            <span><?php

    echo $this->pagination->orderDownIcon($i, $n, (true), 'orderdown', 'Move Down',
        $ordering);

?></span>
            <?php

    $disabled = $ordering ? '' : 'disabled="disabled"';

?>
            <input type="text" name="order[]" size="2" value="<?php echo $row->ordering; ?>" <?php echo $disabled; ?> class="text_area pull-right" style="text-align: center" />
         </td>
         <td align="center">
            <?php

    echo $row->hits;

?>
         </td>
         <td align="center">
            <?php

    echo $row->id;

?>
         </td>
      </tr>
      <?php

    $k = 1 - $k;
}

?>
   </tbody>
   </table>
</div>

   <input type="hidden" name="option" value="com_seminarman" />
   <input type="hidden" name="task" value="" />
   <input type="hidden" name="controller" value="country" />
   <input type="hidden" name="view" value="countries" />
   <input type="hidden" name="boxchecked" value="0" />
   <input type="hidden" name="filter_order" value="<?php

echo $this->lists['order'];

?>" />
   <input type="hidden" name="filter_order_Dir" value="<?php

echo $this->lists['order_Dir'];

?>" />
   <?php

echo JHTML::_('form.token');

?>
</form>