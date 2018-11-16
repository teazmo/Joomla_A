<?php
/**
 * Copyright (C) 2015 Open Source Group GmbH www.osg-gmbh.de
 * @website http://www.osg-gmbh.de
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 **/
defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.tooltip');
jimport('joomla.utilities.date');
$jversion = new JVersion();
$short_version = $jversion->getShortVersion();

JToolBarHelper::custom('viewfulllist', 'expand-2', '', JText::_('COM_SEMINARMAN_VIEW_FULL_LIST'), false);

// Load the modal behavior script.
JHTML::_('behavior.modal', 'a.modal');
?>
<script>
Joomla.submitbutton = function(task){
    Joomla.submitform( task );
}
</script>

<form action="<?php echo $this->requestURL; ?>" method="post" name="adminForm" id="adminForm">
    <div id="editcell">
    <table class="adminlist table table-striped">
    <thead>
    <tr><th>
    <?php
      echo JHTML::_('grid.sort', 'COM_SEMINARMAN_NAME', 'u.name', $this->lists['order_Dir'], $this->lists['order']);
    ?>
    </th>
    <th>
    <?php
      echo JHTML::_('grid.sort', 'COM_SEMINARMAN_USERNAME', 'u.username', $this->lists['order_Dir'], $this->lists['order']);
    ?>
    </th>
    <th><?php echo JText::_('COM_SEMINARMAN_USER_RULES'); ?></th></tr>
    </thead>
	<tfoot>
		<tr><td colspan="4"><?php echo $this->pageNav->getListFooter(); ?></td></tr>
	</tfoot>
    <tbody>
    <?php 
      for ($i = 0, $n = count($this->users); $i < $n; $i++) {
          $row = &$this->users[$i];
          $edit_booking_rules_link = JRoute::_('index.php?option=com_seminarman&controller=user&task=edit_booking_rules&uid=' . $row->id);
          $edit_booking_rules_button = '<a class="btn" href="'. $edit_booking_rules_link . '">' . JText::_('COM_SEMINARMAN_EDIT_BOOKING_RULES') . '</a>';
          $view_booking_rules_button = '<a class="btn modal" href="' . JRoute::_('index.php?option=com_seminarman&view=user&layout=modal&tmpl=component&content=viewbookingrules&uid='.$row->id) . '" rel="{handler: \'iframe\', size: {x: 800, y: 450}}">' . JText::_('COM_SEMINARMAN_VIEW_BOOKING_RULES') . '</a>';
          echo "<tr><td>" . $row->name . "</td><td>" . $row->username . "</td><td>" . $edit_booking_rules_button . " " . $view_booking_rules_button . "</td></tr>";
      }
    ?>
    </tbody>
    </table>
    
    <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="" />
	
	<input type="hidden" name="controller" value="users" />
    <input type="hidden" name="task" value="" />
    
    </div>
</form>