<?php
/**
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011-15 Open Source Group GmbH www.osg-gmbh.de
* @website http://www.profinvent.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.html.parameter' );

$colspan_hide = 0;
if (!($this->params->get('show_thumbnail_in_table'))) $colspan_hide += 1;
if (!($this->params->get('show_code_in_table'))) $colspan_hide += 1;
if (!($this->params->get('show_tags_in_table'))) $colspan_hide += 1;
if (!($this->params->get('show_begin_date_in_table'))) $colspan_hide += 1;
if (!($this->params->get('show_end_date_in_table'))) $colspan_hide += 1;
if (!($this->params->get('show_location'))) $colspan_hide += 1;
if (!($this->params->get('show_price_in_table'))) $colspan_hide += 1;
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
// $colspan = ($this->params->get('show_location')) ? 7 : 6;
$Itemid = JRequest::getInt('Itemid');
?>
<div id="seminarman" class="seminarman">
<h2 class="seminarman favourites">
	<?php echo JText::_('COM_SEMINARMAN_FAVOURED_COURSES') . ': '; ?>
</h2>

<?php if (!count($this->courses)): ?>

	<div class="note">
		<?php echo JText::_('COM_SEMINARMAN_NO_FAVOURED_COURSES'); ?>
	</div>

<?php else: ?>

<form action="<?php echo $this->action;?>" method="post" id="adminForm">
<?php if ($this->params->get('filter') || $this->params->get('display')): ?>
<div id="qf_filter" class="floattext">
		<?php if ($this->params->get('filter')):?>
		<dl class="qf_fleft">
			<dd>
				<?php echo JText::_('COM_SEMINARMAN_COURSE') . ': ';?>
				<input type="text" name="filter" id="filter" value="<?php echo $this->lists['filter']; ?>" class="text_area" size="15"/>
			</dd>
			<dd>
				<?php echo JText::_('COM_SEMINARMAN_LEVEL') . ': ';?>
				<?php echo $this->lists['filter_experience_level'];?>
				<button  onclick="document.getElementById('adminForm').submit();"><?php echo JText::_('COM_SEMINARMAN_GO');?></button>
			</dd>
		</dl>
		<?php endif;?>
		<?php if ($this->params->get('display')):?>
		<div class="qf_fright">
			<label for="limit"><?php echo JText::_('COM_SEMINARMAN_DISPLAY_NUM') ?></label><?php echo $this->pageNav->getLimitBox(); ?>
		</div>
		<?php endif;?>
</div>
<?php endif;?>

<?php 
    if ($this->params->get('show_page_heading', 0)) {
    	$page_heading = trim($this->params->get('page_heading'));
        if (!empty($page_heading)) {
            echo '<h1 class="componentheading">' . $page_heading . '</h1>';
        }
    }
?>

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
$table_rows = SMANFunctions::buildCourseTableRows($this->params, $this->courses, $enable_booking, $this->category, $Itemid);
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
<div class="right"></div>

	<input type="hidden" name="option" value="com_seminarman" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['filter_order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="" />
	<input type="hidden" name="view" value="favourites" />
	<input type="hidden" name="task" value="" />
	</form>

<?php endif; ?>
</div>
