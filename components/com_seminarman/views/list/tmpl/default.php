<?php
/**
*
* Copyright (C) 2015 Open Source Group GmbH www.osg-gmbh.de
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
// JHtml::register('behavior.tooltip', $this->clau_tooltip());
$mainframe = JFactory::getApplication();
$params = $mainframe->getParams('com_seminarman');
if (JVERSION >= 3.4) {
    JHtml::_('behavior.formvalidator');
} else {
    JHTML::_('behavior.formvalidation');
}
$Itemid = JRequest::getInt('Itemid');
$db = JFactory::getDBO();
jimport('joomla.mail.helper');

$colspan_hide = 0;
if (!($this->params->get('show_thumbnail_in_table'))) $colspan_hide += 1;
if (!($this->params->get('show_code_in_table'))) $colspan_hide += 1;
if (!($this->params->get('show_tags_in_table'))) $colspan_hide += 1;
if (!($this->params->get('show_location'))) $colspan_hide += 1;
if (!($this->params->get('show_price_in_table'))) $colspan_hide += 1;
if (!($this->params->get('show_begin_date_in_table'))) $colspan_hide += 1;
if (!($this->params->get('show_end_date_in_table'))) $colspan_hide += 1;
if (!($this->params->get('show_booking_deadline_in_table'))) $colspan_hide += 1;
if (!($this->params->get('enable_bookings'))) $colspan_hide += 1;
if (!($this->params->get("custom_fld_1_in_table"))) $colspan_hide += 1;
if (!($this->params->get("custom_fld_2_in_table"))) $colspan_hide += 1;
if (!($this->params->get("custom_fld_3_in_table"))) $colspan_hide += 1;
if (!($this->params->get("custom_fld_4_in_table"))) $colspan_hide += 1;
if (!($this->params->get("custom_fld_5_in_table"))) $colspan_hide += 1;
if (!($this->params->get('show_begin_time_in_table'))) $colspan_hide += 1;
if (!($this->params->get('show_finish_time_in_table'))) $colspan_hide += 1;
if (!($this->params->get('show_spaces_in_table'))) $colspan_hide += 1;

if ($this->params->get('show_spaces_in_table') && ($this->params->get('show_space_indicator_in_table') == 1) && $this->params->get('current_capacity')) {
	$total_cols_add = 1;
} else {
	$total_cols_add = 0;
}

$colspan = 18 + $total_cols_add - $colspan_hide;
$display_free_charge = $this->params->get('display_free_charge');
?>
<div id="seminarman" class="seminarman">
<h2><?php echo JText::_('COM_SEMINARMAN_SEARCH_RESULTS'); ?></h2>

<?php if (!empty($this->courses)): ?>

<form action="<?php echo $this->action;?>" method="post" id="adminForm">
<?php if ($this->params->get('display')):?>
<div id="qf_filter" class="floattext">
		<div class="qf_fright">
			<label for="limit"><?php echo JText::_('COM_SEMINARMAN_DISPLAY_NUM') ?></label><?php echo $this->pageNav->getLimitBox(); ?>
		</div>
</div>
<?php endif; ?>
<table class="seminarmancoursetable" summary="seminarman">
<thead>
<?php
    if ($this->params->get('enable_bookings')) {
    	$enable_booking = true;
    } else {
    	$enable_booking = false;
    }
    $table_header = SMANFunctions::buildCourseTableHeader($this->params, $this->courses, $this->lists, $enable_booking);
    echo $table_header;
?>
</thead>

<tbody>

<?php
$table_rows = SMANFunctions::buildCourseTableRows($this->params, $this->courses, $enable_booking, null, $Itemid);
echo $table_rows;
?>


<?php 
// custom menu setting for displayed price: $params->get('show_gross_price')
// global setting for displayed price: $params->get('show_gross_price_global')
if ($this->params->get('show_gross_price_global') == 0) {
	if (!is_null($this->params->get('show_gross_price')) && $this->params->get('show_gross_price') == '1') {
		$price_display_gross = true;
	} else {
		$price_display_gross = false;
	}
} else {
	if (!is_null($this->params->get('show_gross_price')) && $this->params->get('show_gross_price') <> '1') {
		$price_display_gross = false;
	} else {
		$price_display_gross = true;
	}
}
if (!is_null($this->params->get('show_gross_price')) && $this->params->get('show_gross_price') == '2') {
	$show_tax_in_footer = false;
} else {
	$show_tax_in_footer = true;   // auch wenn die Kurstabelle aus irgendwelchem Module vorkommt, das nicht unter einem Menutyp von Sman steht.
}
?>

<?php if ($show_tax_in_footer): ?>
<tr class="sectiontableentry" >
	<td colspan="<?php echo $colspan; ?>" class="right">*<?php echo ($price_display_gross) ? JText::_('COM_SEMINARMAN_WITH_VAT') : JText::_('COM_SEMINARMAN_WITHOUT_VAT'); ?></td>
</tr>
<?php endif; ?>

<?php if ($this->params->get('show_spaces_in_table') && $this->params->get('show_space_indicator_in_table') && $this->params->get('current_capacity')): ?>
<tr class="sectiontableentry" >
	<td colspan="<?php echo $colspan; ?>" class="right">
  <div class="footer_notes"><div class="semaforo buchbar align-left"></div><div class="light_desc align-left"><?php echo JText::_('COM_SEMINARMAN_EVENT_BOOKABLE'); ?></div>
  <div class="semaforo garantiert align-left"></div><div class="light_desc align-left"><?php echo JText::_('COM_SEMINARMAN_EVENT_GUARANTEED'); ?></div>
  <div class="semaforo ausgebucht align-left"></div><div class="light_desc align-left"><?php echo JText::_('COM_SEMINARMAN_EVENT_FULL'); ?></div></div>
  </td>
</tr>
<?php endif; ?>
</tbody>
</table>
<input type="hidden" name="option" value="com_seminarman" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['filter_order'];?>" />
<input type="hidden" name="filter_order_Dir" value="" />
<input type="hidden" name="view" value="list" />
<input type="hidden" name="task" value="" />
</form>
<div class="pagination"><?php echo $this->pageNav->getPagesLinks(); ?></div>
<?php 
else:
echo JText::_('COM_SEMINARMAN_NO_COURSE');
endif;
?>
</div>