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

$db = JFactory::getDBO();

JToolBarHelper::custom('closefulllist', 'cancel', '', JText::_('COM_SEMINARMAN_CLOSE_FULL_LIST'), false);

// Load the modal behavior script.
JHTML::_('behavior.modal', 'a.modal');

function formatDate($str, $format) {
	$str = trim($str);
	if ($str == 'devnull')
		return "";
	elseif ((substr($str, 0, 10) != '0000-00-00') && (!empty($str)))
		return JHTML::_('date', $str, $format);
	else
		return JText::_('COM_SEMINARMAN_NOVALUE');
}
?>
<script>
Joomla.submitbutton = function(task){
    Joomla.submitform( task );
}
</script>
<form action="<?php echo $this->requestURL; ?>" method="post" name="adminForm" id="adminForm">

<table class="adminform">
<tr>
   <td style="white-space: nowrap">
       <?php 
           if ($this->params->get('common_schema_support') || $this->jsonfuncscreated) echo $this->lists['category']; 
           echo $this->lists['state']; 
       ?>
   </td>
</tr>
</table>

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
    <th><?php echo JText::_('COM_SEMINARMAN_USER_BOOKING_RULE'); ?></th>
    <th><?php echo JText::_('COM_SEMINARMAN_CATEGORY'); ?></th>
    <th>
    <?php
      if ($this->params->get('common_schema_support') || $this->jsonfuncscreated) {
        echo JHTML::_('grid.sort', 'COM_SEMINARMAN_FROM', 'rule_begin', $this->lists['order_Dir'], $this->lists['order']);
      } else {
        echo JText::_('COM_SEMINARMAN_FROM');
      } 
    ?>
    </th>
     <th>
    <?php 
      if ($this->params->get('common_schema_support') || $this->jsonfuncscreated) {
        echo JHTML::_('grid.sort', 'COM_SEMINARMAN_TO', 'rule_finish', $this->lists['order_Dir'], $this->lists['order']); 
      } else {
        echo JText::_('COM_SEMINARMAN_TO');
      }
    ?>
    </th>
    <th><?php echo JText::_('COM_SEMINARMAN_AMOUNT'); ?></th>
    <th><?php echo JText::_('JPUBLISHED'); ?></th>
    <th><?php echo JText::_('COM_SEMINARMAN_SETTINGS'); ?></th></tr>
    </thead>
	<tfoot>
		<tr><td colspan="4"><?php echo $this->pageNav->getListFooter(); ?></td></tr>
	</tfoot>
    <tbody>
    <?php 
      for ($i = 0, $n = count($this->allbookingrules); $i < $n; $i++) {
          $row = &$this->allbookingrules[$i];
          $edit_booking_rules_link = JRoute::_('index.php?option=com_seminarman&controller=user&task=edit_booking_rules&uid=' . $row->id);
          if(JVERSION < 3.0) {
              $edit_booking_rules_button = '<a class="btn" href="'. $edit_booking_rules_link . '">'.JText::_('COM_SEMINARMAN_EDIT').'</span></a>';
          } else {
          	  $edit_booking_rules_button = '<a class="btn" href="'. $edit_booking_rules_link . '"><span class="icon-edit"></span></a>';
          }
          if(JVERSION < 3.0) {
              $view_booking_rules_button = '<a class="btn modal" href="' . JRoute::_('index.php?option=com_seminarman&view=user&layout=modal&tmpl=component&content=viewbookingrules&uid='.$row->id) . '" rel="{handler: \'iframe\', size: {x: 800, y: 450}}">'.JText::_('JSHOW').'</a>';
          } else {
              $view_booking_rules_button = '<a class="btn modal" href="' . JRoute::_('index.php?option=com_seminarman&view=user&layout=modal&tmpl=component&content=viewbookingrules&uid='.$row->id) . '" rel="{handler: \'iframe\', size: {x: 800, y: 450}}"><span class="icon-list"></span></a>';
          }
          if (isset($row->rule_text)) {
            $rule_detail = json_decode($row->rule_text);
            if ($row->rule_published) {
            	$published = JText::_('JYES');
            } else {
            	$published = JText::_('JNO');
            }

            $query = $db->getQuery( true );
            $query->select( 'title' );
            $query->from( '#__seminarman_categories' );
            $query->where( "id=" . $rule_detail->category );
            
            $db->setQuery($query);
            $cat_name = $db->loadResult();
            
            $booked = JHTMLSeminarman::get_user_booking_total_in_category_rule($rule_detail->category, $row->id, $rule_detail->start_date, $rule_detail->finish_date);
            $rule_detail->amount = $booked . " / " . $rule_detail->amount;
          } else {
            $rule_detail = new stdClass();
            $rule_detail->start_date = "devnull";
            $rule_detail->finish_date = "devnull";
            $rule_detail->amount = "";
            $published = "";
            $cat_name = "";
          }
          echo "<tr><td>" . $row->name . "</td>".
             "<td>" . $row->username . "</td>".
             "<td>". $row->rule_title ."</td>".
             "<td>".$cat_name."</td>".
             "<td>".formatDate($rule_detail->start_date, JText::_('COM_SEMINARMAN_DATE_FORMAT1'))."</td>".
             "<td>".formatDate($rule_detail->finish_date, JText::_('COM_SEMINARMAN_DATE_FORMAT1'))."</td>".
             "<td>".$rule_detail->amount."</td>".
             "<td>".$published."</td>".
            "<td>" . $edit_booking_rules_button . " " . $view_booking_rules_button . "</td></tr>";
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