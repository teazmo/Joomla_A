<?php
/**
 * Copyright (C) 2015 Open Source Group GmbH www.osg-gmbh.de
 * @website http://www.osg-gmbh.de
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 **/
defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.tooltip');
jimport('joomla.utilities.date');
JToolBarHelper::title(JText::_('COM_SEMINARMAN_USER') . ': ' . JText::_('COM_SEMINARMAN_USER_BOOKING_RULES'), 'user');
// JToolBarHelper::apply();
// JToolBarHelper::save();
JToolBarHelper::cancel();
$uid = JRequest::getVar('uid');
$user = JFactory::getUser($uid);
$db = JFactory::getDBO();

function formatDate($str, $format) {
	$str = trim($str);
	if ((substr($str, 0, 10) != '0000-00-00') && (!empty($str)))
		return JHTML::_('date', $str, $format);
		else
			return JText::_('COM_SEMINARMAN_NOVALUE');
}

echo "<h3>" . $user->name . " (" . $user->username . ")</h3>";
?>

    <span><?php echo JText::_('COM_SEMINARMAN_USER_RULE_OPTION'); ?>: </span>
    <select size="1" class="inputbox">
    <option value=""><?php echo JText::_('COM_SEMINARMAN_CATEGORY') . ' ' . JText::_('COM_SEMINARMAN_USER_RULE'); ?></option>
    </select> 
  
    <table class="adminlist table table-striped" id="adminlist">
    <thead>
    <tr><th style="width: 18%;"><?php echo JText::_('COM_SEMINARMAN_TITLE'); ?></th><th style="width: 25%;"><?php echo JText::_('COM_SEMINARMAN_CATEGORY'); ?></th><th style="width: 10%;"><?php echo JText::_('COM_SEMINARMAN_FROM'); ?></th><th style="width: 10%;"><?php echo JText::_('COM_SEMINARMAN_TO'); ?></th><th style="width: 8%;"><?php echo JText::_('COM_SEMINARMAN_AMOUNT'); ?></th><th style="width: 4%;"><?php echo JText::_('COM_SEMINARMAN_ID'); ?></th><th style="width: 6%;"><?php echo JText::_('JPUBLISHED'); ?></th></tr>
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
               
               $query = $db->getQuery( true );
               $query->select( 'title' );
       		   $query->from( '#__seminarman_categories' );
               $query->where( "id=" . $rule_detail->category );
               
               $db->setQuery( $query );
               $cat_name = $db->loadResult();
               
               $booked = JHTMLSeminarman::get_user_booking_total_in_category_rule($rule_detail->category, $uid, $rule_detail->start_date, $rule_detail->finish_date);
               
               echo '<tr>' .
                    '<td>' . $bookingrule->title . '</td>' .
                    '<td>' . $cat_name . '</td>' .
                    '<td>' . formatDate($rule_detail->start_date, JText::_('COM_SEMINARMAN_DATE_FORMAT1')) . '</td>' .
                    '<td>' . formatDate($rule_detail->finish_date, JText::_('COM_SEMINARMAN_DATE_FORMAT1')) . '</td>' .
                    '<td>' . $booked . ' / ' . $rule_detail->amount . '</td>' .
                    '<td>' . $bookingrule->id . '</td>' .
                    '<td>' . $published . '</td>' .
                    '</tr>';
               $i++;
          }
      } 
    ?>
    </tbody>
    </table>
    
   