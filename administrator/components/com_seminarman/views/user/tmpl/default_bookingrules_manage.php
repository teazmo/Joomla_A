<?php
/**
 * Copyright (C) 2015 Open Source Group GmbH www.osg-gmbh.de
 * @website http://www.osg-gmbh.de
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 **/
defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.tooltip');
jimport('joomla.utilities.date');
// Load the modal behavior script.
JHTML::_('behavior.modal', 'a.modal');
JToolBarHelper::title(JText::_('COM_SEMINARMAN').': '.JText::_('COM_SEMINARMAN_USER') . ' - ' . JText::_('COM_SEMINARMAN_USER_BOOKING_RULES'), 'user');
// JToolBarHelper::apply();
// JToolBarHelper::save();
JToolBarHelper::cancel('cancel', JText::_('COM_SEMINARMAN_CLOSE'));
$uid = JRequest::getVar('uid');
$user = JFactory::getUser($uid);
$db = JFactory::getDBO();

echo "<h3>" . $user->name . " (" . $user->username . ")</h3>";

$add_new_rule_button = '<a class="btn btn-success modal" style="margin-bottom: 8px;" href="' . JRoute::_('index.php?option=com_seminarman&view=user&layout=modal&tmpl=component&content=addbookingrule&uid='.$uid) . '" rel="{handler: \'iframe\', size: {x: 800, y: 450}}"><span class="icon-new icon-white"></span>' . JText::_('COM_SEMINARMAN_ADD_RULE') . '</a>';

function formatDate($str, $format) {
	$str = trim($str);
	if ((substr($str, 0, 10) != '0000-00-00') && (!empty($str)))
		return JHTML::_('date', $str, $format);
	else
		return JText::_('COM_SEMINARMAN_NOVALUE');
}
?>

<script>
    Joomla.submitbutton = function(task) {
    	Joomla.submitform( task );
    }
</script>
    <span><?php echo JText::_('COM_SEMINARMAN_SELECT'); ?>: </span>
    <select size="1" class="inputbox">
    <option value=""><?php echo JText::_('COM_SEMINARMAN_CATEGORY') . ' ' . JText::_('COM_SEMINARMAN_USER_RULE'); ?></option>
    </select> 
     <?php echo $add_new_rule_button; ?>
    <form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">   
    <table class="adminlist table table-striped" id="adminlist">
    <thead>
    <tr><th style="width: 18%;"><?php echo JText::_('COM_SEMINARMAN_TITLE'); ?></th><th style="width: 25%;"><?php echo JText::_('COM_SEMINARMAN_CATEGORY'); ?></th><th style="width: 10%;"><?php echo JText::_('COM_SEMINARMAN_FROM'); ?></th><th style="width: 10%;"><?php echo JText::_('COM_SEMINARMAN_TO'); ?></th><th style="width: 8%;"><?php echo JText::_('COM_SEMINARMAN_AMOUNT'); ?></th><th style="width: 4%;"><?php echo JText::_('COM_SEMINARMAN_ID'); ?></th><th style="width: 6%;"><?php echo JText::_('JPUBLISHED'); ?></th><th><?php echo JText::_('COM_SEMINARMAN_OPERATIONS'); ?></th></tr>
    </thead>
    <tbody>
    <?php 
      if (!empty($this->userbookingrules)) {
          $i = 0;
          foreach($this->userbookingrules as $bookingrule) {
               $rule_detail = json_decode($bookingrule->rule_text);
               if ($bookingrule->published) {
                  $published = JText::_('JYES');
               } else {
                  $published = JText::_('JNO');
               }
               if(JVERSION < 3.0) {
	             $edit_rule_button = '<a data-original-title="' . JText::_('COM_SEMINARMAN_EDIT') . '" title="' . JText::_('COM_SEMINARMAN_EDIT') . '" class="btn btn-small btn-primary modal hasTooltip" href="' . JRoute::_('index.php?option=com_seminarman&view=user&layout=modal&tmpl=component&content=editbookingrule&uid='.$uid.'&rule_id='.$bookingrule->id) . '" rel="{handler: \'iframe\', size: {x: 800, y: 450}}">'.JText::_('COM_SEMINARMAN_EDIT').'</a>';
               } else {
	             $edit_rule_button = '<a data-original-title="' . JText::_('COM_SEMINARMAN_EDIT') . '" title="' . JText::_('COM_SEMINARMAN_EDIT') . '" class="btn btn-small btn-primary modal hasTooltip" href="' . JRoute::_('index.php?option=com_seminarman&view=user&layout=modal&tmpl=component&content=editbookingrule&uid='.$uid.'&rule_id='.$bookingrule->id) . '" rel="{handler: \'iframe\', size: {x: 800, y: 450}}"><span class="icon-apply icon-white"></span></a>';
               }
               if(JVERSION < 3.0) {
                 $delete_rule_button = '<a data-original-title="' . JText::_('COM_SEMINARMAN_DELETE') . '" title="' . JText::_('COM_SEMINARMAN_DELETE') . '" class="btn btn-small btn-danger hasTooltip" href="' . JRoute::_('index.php?option=com_seminarman&controller=user&task=delete_booking_rule&uid='.$uid.'&rule_id='.$bookingrule->id.'&'.JSession::getFormToken().'=1') .'">'.JText::_('COM_SEMINARMAN_DELETE').'</a>';
               } else {
               	 $delete_rule_button = '<a data-original-title="' . JText::_('COM_SEMINARMAN_DELETE') . '" title="' . JText::_('COM_SEMINARMAN_DELETE') . '" class="btn btn-small btn-danger hasTooltip" href="' . JRoute::_('index.php?option=com_seminarman&controller=user&task=delete_booking_rule&uid='.$uid.'&rule_id='.$bookingrule->id.'&'.JSession::getFormToken().'=1') .'"><span class="icon-delete icon-white"></span></a>';
               }
               
               $query = $db->getQuery(true);
               $query->select( 'title' );
               $query->from( '#__seminarman_categories' );
               $query->where( "id=" . $rule_detail->category );
               
               $db->setQuery( $query );
               $cat_name = $db->loadResult();
               
               echo '<tr>' .
                    '<td>' . $bookingrule->title . '</td>' .
                    '<td>' . $cat_name . '</td>' .
                    '<td>' . formatDate($rule_detail->start_date, JText::_('COM_SEMINARMAN_DATE_FORMAT1')) . '</td>' .
                    '<td>' . formatDate($rule_detail->finish_date, JText::_('COM_SEMINARMAN_DATE_FORMAT1')) . '</td>' .
                    '<td>' . $rule_detail->amount . '</td>' .
                    '<td>' . $bookingrule->id . '</td>' .
                    '<td>' . $published . '</td>' .
                    '<td>' . $edit_rule_button . ' ' . $delete_rule_button . '</td>' .
                    '</tr>';
               $i++;
          }
      } 
    ?>
    </tbody>
    </table>
    <br /> 
    <input type="hidden" name="option" value="com_seminarman" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="controller" value="user" />
	<input type="hidden" name="uid" value="<?php echo $uid; ?>" />
		<?php echo JHTML::_('form.token'); ?>
    </form>
    
   