<?php
/**
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011-18 Open Source Group GmbH www.osg-gmbh.de
* @website http://www.profinvent.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

defined('_JEXEC') or die('Restricted access');

$params = JComponentHelper::getParams('com_seminarman');

if (JVERSION >= 3.4) {
    JHtml::_('behavior.formvalidator');
} else {
    JHTML::_('behavior.formvalidation');
}

$Itemid = JRequest::getInt('Itemid');
// $rowid = (int)JRequest::getVar('row');
$rowid = 0;
?>
<script type="text/javascript">
function submitbuttonSeminarman(task)
{
	var form = document.adminForm;
	Joomla.submitform( task );
}
</script>
<?php if ($this->courses[$rowid]->cancel_allowed): ?>
<h2><?php echo JText::_('COM_SEMINARMAN_CANCEL_CONFIRM'); ?></h2>
<h3><?php echo JText::_('COM_SEMINARMAN_CANCEL_CONFIRM_ASK'); ?></h3>
<form action="<?php echo $this->action ?>" method="post" name="adminForm" id="adminForm" class="form-validate"  enctype="multipart/form-data">
<?php echo JText::_('COM_SEMINARMAN_COURSE_CODE').": ".$this->courses[$rowid]->code;?><br />
<?php echo JText::_('COM_SEMINARMAN_COURSE_TITLE').": ".$this->courses[$rowid]->title;?><br />
<?php echo JText::_('COM_SEMINARMAN_DATE').": ".$this->courses[$rowid]->start." - ".$this->courses[$rowid]->finish;?><br />
<?php echo JText::_('COM_SEMINARMAN_LOCATION').": ".$this->courses[$rowid]->location;?><br /><br />
<h3><?php echo JText::_('COM_SEMINARMAN_BOOKING_INFO'); ?></h3>

<?php 
$display_name_head = trim($this->escape($this->courses[$rowid]->booking_salutation).' '.$this->escape($this->courses[$rowid]->booking_title));
$display_name_tail = trim($this->escape($this->courses[$rowid]->booking_first_name).' '.$this->escape($this->courses[$rowid]->booking_last_name));
if (!empty($display_name_head)) {
	$display_name = $display_name_head . ' ' . $display_name_tail;
} else {
	$display_name = $display_name_tail;
}
?>

<?php echo JText::_('COM_SEMINARMAN_NAME').": ".$display_name;?><br />
<?php 
  if ($this->courses[$rowid]->booking_vat > 0) {
  	$vat_display = " (".JText::sprintf('COM_SEMINARMAN_CART_WITHOUT_VAT',JText::sprintf('%.0f', round($this->courses[$rowid]->booking_vat, 2)) . '%').")";
  } else {
  	$vat_display = "";
  }
?>
<?php if ($this->params->get('show_price_in_my_bookings')) echo JText::_('COM_SEMINARMAN_PRICE_BOOKING').": ".JText::sprintf('%.2f', round(doubleval(str_replace(",", ".", $this->courses[$rowid]->booking_price)), 2))." ".$params->get('currency').$vat_display;?><br />
<?php echo JText::_('COM_SEMINARMAN_NR_ATTENDEES').": ".$this->courses[$rowid]->booking_amount;?><br />
<?php if ($this->params->get('show_price_in_my_bookings')) echo JText::_('COM_SEMINARMAN_CART_PRICE_TOTAL').": ".JText::sprintf('%.2f', round(doubleval(str_replace(",", ".", $this->courses[$rowid]->booking_price_total)), 2))." ".$params->get('currency').$vat_display;?><br /><br />
    <input type="hidden" name="application_id" value="<?php echo $this->courses[$rowid]->applicationid;?>" />
    <input type="hidden" name="option" value="com_seminarman" />
    <input type="hidden" name="controller" value="application" />
    <input type="hidden" name="task" value="" />
    <?php echo JHTML::_('form.token'); ?>
<button onclick="submitbuttonSeminarman('cancel_booking_process');"><?php echo JText::_('JYES'); ?></button>&nbsp;&nbsp;<button onclick="submitbuttonSeminarman('no_cancel_booking');"><?php echo JText::_('JNO'); ?></button>
</form>
<?php else: ?>
<h3><?php echo JText::_('COM_SEMINARMAN_CANCEL_NOT_ALLOWED'); ?></h3>
<?php endif; ?>