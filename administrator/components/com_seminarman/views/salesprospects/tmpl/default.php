<?php
/**
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011 Open Source Group GmbH www.osg-gmbh.de
* @website http://www.profinvent.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.tooltip');
jimport('joomla.utilities.date');
$jversion = new JVersion();
$short_version = $jversion->getShortVersion();
?>

<script type="text/javascript">
function notify(id) {
	var f = document.adminForm;
	f.id.value = id;
	Joomla.submitform('notify', f);
}
</script>

<form action="<?php echo $this->requestURL; ?>" method="post" name="adminForm" id="adminForm">
<table class="adminform">
<tr>
   <td class="proc100 left">
      <?php echo JText::_('Filter'); ?>:
      <?php echo $this->lists['filter_search']; ?>
      <input type="text" name="search" id="search" value="<?php echo $this->lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
      <input type="button" onclick="this.form.submit();" value="<?php echo JText::_('COM_SEMINARMAN_GO');?>" />
      <input type="button" onclick="document.getElementById('search').value='';this.form.getElementById('filter_courseid').value='0';this.form.getElementById('filter_templateid').value='0';this.form.submit();" value="<?php echo JText::_('COM_SEMINARMAN_RESET'); ?>" />
   </td>
   <td>
      <?php echo $this->lists['templateid']; echo $this->lists['courseid']; ?>
   </td>
</tr>
</table>
<div id="editcell">
   <table class="adminlist">
   <thead>
      <tr>
         <th width="5"><?php echo JText::_('COM_SEMINARMAN_NUM'); ?></th>
         <?php if (version_compare($short_version, "3.0", 'ge')): ?>
         	<th width="20"><?php echo JHtml::_('grid.checkall'); ?></th>
         <?php else: ?>
			<th width="20"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->salesprospects); ?>);" /></th>
         <?php endif; ?>
         <th class="title"><?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_LAST_NAME', 'a.last_name', $this->lists['order_Dir'], $this->lists['order']); ?></th>
         <th width="10%"><?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_FIRST_NAME', 'a.first_name', $this->lists['order_Dir'], $this->lists['order']); ?></th>
         <th width="15%"><?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_EMAIL', 'a.email', $this->lists['order_Dir'], $this->lists['order']); ?></th>
         <th width="15%"><?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_TEMPLATE', 'j.title', $this->lists['order_Dir'], $this->lists['order']); ?></th>
         <th width="10%"><?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_COURSE_CODE', 'j.code', $this->lists['order_Dir'], $this->lists['order']); ?></th>
         <th width="5%"><?php echo JHTML::_('grid.sort',  'COM_SEMINARMAN_ATTENDEES', 'a.attendees', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
         <th width="5%"><?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_DATE', 'a.date', $this->lists['order_Dir'], $this->lists['order']); ?></th>
         <th width="20%"><?php echo JHTML::_('grid.sort',  'COM_SEMINARMAN_NOTIFICATION', 'a.notified', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
         <th width="8%"><span style="float:left"><?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_ORDER', 'a.ordering', $this->lists['order_Dir'], $this->lists['order']); ?></span><?php echo JHTML::_('grid.order', $this->salesprospects); ?></th>
         <th width="1%"><?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_ID', 'a.id', $this->lists['order_Dir'], $this->lists['order']); ?></th>
      </tr>
   </thead>
   <tfoot>
      <tr>
         <td colspan="12"><?php echo $this->pagination->getListFooter(); ?></td>
      </tr>
   </tfoot>
   <tbody>
   <?php

$k = 0;
for ($i = 0, $n = count($this->salesprospects); $i < $n; $i++)
{
    $row = &$this->salesprospects[$i];

    $link = JRoute::_('index.php?option=com_seminarman&controller=salesprospect&task=edit&cid[]='. $this->escape($row->id));
    $link_template = JRoute::_('index.php?option=com_seminarman&controller=templates&task=edit&cid[]='. $this->escape($row->template_id));        

    $checked = JHTML::_('grid.checkedout', $row, $i);

    $ordering = ($this->lists['order'] == 'a.ordering');

?>
      <tr class="<?php echo "row$k"; ?>">
         <td><?php echo $this->pagination->getRowOffset($i); ?></td>
         <td><?php echo $checked; ?></td>
         <td>
<?php
	$result = 0;
	if ($row instanceof JTable)
	{
		$result = $row->isCheckedOut( $this->user->get( 'id' ) );
	}

	if ( $result )
		echo $this->escape($row->title);
	else {
?>
            <span class="editlinktip hasTip" title="<?php echo JText::_('COM_SEMINARMAN_EDIT_REQUEST'); ?>::<?php echo $this->escape($row->salutation) . " " . $this->escape($row->first_name) . " " . $this->escape($row->last_name); ?>">
               <a href="<?php echo $link; ?>"><?php echo $this->escape($row->last_name); ?></a>
            </span>
<?php    } ?>
         </td>
         <td class="centered"><?php echo $this->escape($row->first_name); ?></td>
         <td class="centered"><?php echo ('<a href="mailto:'.$this->escape($row->email).'">'.$this->escape($row->email).'</a>');?></td>
         <td class="centered"><a href="<?php echo $link_template; ?>"><?php echo $this->escape($row->title); ?></a></td>
         <td class="centered"><?php echo $this->escape($row->code); ?></td>
         <td class="centered"><?php echo $this->escape($row->attendees); ?></td>
         <td class="centered"><?php echo JHTML::date($row->date, JText::_('COM_SEMINARMAN_DATETIME_FORMAT1')); ?></td>
         <td class="centered">
         	<div class="left" style="display: inline-block;">
         	<p style="margin: 0;"><?php echo $row->notified == $this->nullDate ? '<span class="red">'. JText::_('COM_SEMINARMAN_NOT_NOTIFIED') .'</span>' : JHTML::date($row->notified, JText::_('COM_SEMINARMAN_DATETIME_FORMAT1')); ?></p>
         	<?php echo $row->select_course_notify; ?>
         	<input type="button" style="padding: 0;" value="<?php echo JText::_('COM_SEMINARMAN_NOTIFY'); ?>" onclick="javascript:notify('<?php echo $this->escape($row->id); ?>')" />
         	</div>
         </td>
         <td class="order">
          	<span><?php echo $this->pagination->orderUpIcon($i, (true), 'orderup', 'Move Up', $ordering); ?></span>
          	<span><?php echo $this->pagination->orderDownIcon($i, $n, (true), 'orderdown', 'Move Down', $ordering); ?></span>
            <?php $disabled = $ordering ? '' : 'disabled="disabled"'; ?>
            <input type="text" name="order[]" size="2" value="<?php echo $row->ordering; ?>" <?php echo $disabled; ?> class="text_area centered pull-right" />
         </td>
         <td class="centered"><?php echo $row->id; ?>
         </td>
      </tr>
      <?php

    $k = 1 - $k;
}

?>
   </tbody>
   </table>
</div>

   <input type="hidden" name="id" value="" />
   <input type="hidden" name="option" value="com_seminarman" />
   <input type="hidden" name="task" value="" />
   <input type="hidden" name="controller" value="salesprospect" />
   <input type="hidden" name="view" value="salesprospects" />
   <input type="hidden" name="boxchecked" value="0" />
   <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
   <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
   <?php echo JHTML::_('form.token'); ?>
</form>