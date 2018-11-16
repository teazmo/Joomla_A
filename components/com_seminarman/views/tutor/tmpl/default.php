<?php
/**
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011-15 Open Source Group GmbH www.osg-gmbh.de
* @website http://www.profinvent.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

defined('_JEXEC') or die('Restricted access');
if (JVERSION >= 3.4) {
    JHtml::_('behavior.formvalidator');
} else {
    JHTML::_('behavior.formvalidation');
}
// JHtml::register('behavior.tooltip', $this->clau_tooltip());
$Itemid = JRequest::getInt('Itemid');
$comp_data = $this->params->get('tutor_company_data');
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

// if (!($this->params->get('enable_bookings'))) $colspan_hide += 1;
$colspan = 18 + $total_cols_add - $colspan_hide;
// $colspan = ($this->params->get('show_location')) ? 7 : 6;
$Itemid = JRequest::getInt('Itemid');
?>

<div id="seminarman" class="seminarman">
<div class="tutor_block">
<div class="tutor_block_left">
<?php if (!empty($this->tutor->tutor_photo)): ?>
<img src="<?php echo $this->siteurl . $this->params->get('image_path', 'images'). '/' . $this->tutor->tutor_photo; ?>">
<?php endif; ?>
</div>
<div class="tutor_block_right">
<h2 class="tutor_label"><?php echo $this->tutor->tutor_label; ?></h2>
<?php if (($this->params->get('show_company_data')) && !empty($comp_data)): ?>
<br /><br />
<dl class="tutor_company_info">
    <?php if ((in_array("tutor_comp_name", $comp_data)) && !empty($this->tutor->comp_name)): ?>
    <dt><?php echo JText::_('COM_SEMINARMAN_COMPANY_NAME'); ?>:</dt>
    <dd><?php echo $this->tutor->comp_name; ?></dd>
    <?php endif; ?>
    <?php if ((in_array("tutor_primary_phone", $comp_data)) && !empty($this->tutor->primary_phone)): ?>
    <dt><?php echo JText::_('COM_SEMINARMAN_PRIMARY_PHONE'); ?>:</dt>
    <dd><?php echo $this->tutor->primary_phone; ?></dd>
    <?php endif; ?>
    <?php if ((in_array("tutor_fax_number", $comp_data)) && !empty($this->tutor->fax_number)): ?>
    <dt><?php echo JText::_('COM_SEMINARMAN_FAX_NUMBER'); ?>:</dt>
    <dd><?php echo $this->tutor->fax_number; ?></dd>
    <?php endif; ?>
    <?php if ((in_array("tutor_email", $comp_data)) && !empty($this->tutor->email) && (JMailHelper::isEmailAddress($this->tutor->email))): ?>
    <dt><?php echo JText::_('COM_SEMINARMAN_EMAIL'); ?>:</dt>
    <dd><?php echo '<a href="mailto:' . $this->tutor->email . '">' . $this->tutor->email . '</a>'; ?></dd>
    <?php endif; ?>
    <?php if ((in_array("tutor_url", $comp_data)) && !empty($this->tutor->url)): ?>
    <dt><?php echo JText::_('COM_SEMINARMAN_WEBSITE'); ?>:</dt>
    <dd><?php echo $this->tutor->url; ?></dd>
    <?php endif; ?>
    <?php if ((in_array("tutor_street", $comp_data)) && !empty($this->tutor->street)): ?>
    <dt><?php echo JText::_('COM_SEMINARMAN_STREET'); ?>:</dt>
    <dd><?php echo $this->tutor->street; ?></dd>
    <?php endif; ?>
    <?php if ((in_array("tutor_zip", $comp_data)) && !empty($this->tutor->zip)): ?>
    <dt><?php echo JText::_('COM_SEMINARMAN_ZIP'); ?>:</dt>
    <dd><?php echo $this->tutor->zip; ?></dd>
    <?php endif; ?>
    <?php if ((in_array("tutor_city", $comp_data)) && !empty($this->tutor->city)): ?>
    <dt><?php echo JText::_('COM_SEMINARMAN_CITY'); ?>:</dt>
    <dd><?php echo $this->tutor->city; ?></dd>
    <?php endif; ?>
    <?php if ((in_array("tutor_state", $comp_data)) && !empty($this->tutor->state)): ?>
    <dt><?php echo JText::_('COM_SEMINARMAN_STATE'); ?>:</dt>
    <dd><?php echo $this->tutor->state; ?></dd>
    <?php endif; ?>
    <?php if ((in_array("tutor_country", $comp_data)) && !empty($this->tutor->id_country)): ?>
    <?php 
		$query = $db->getQuery(true);
		$query->select( 'title' );
		$query->from( '#__seminarman_country' );
		$query->where( 'id=' . $this->tutor->id_country );
        $db->setQuery($query);
        
        $tutor_country = $db->loadResult();
    ?>
    <dt><?php echo JText::_('COM_SEMINARMAN_COUNTRY'); ?>:</dt>
    <dd><?php echo $tutor_country; ?></dd>
    <?php endif; ?>
    <?php if ((in_array("tutor_company_type", $comp_data)) && !empty($this->tutor->id_comp_type)): ?>
    <?php 
        $query = $db->getQuery(true);
        $query->select( 'title' );
        $query->from( '#__seminarman_company_type' );
        $query->where( 'id=' . $this->tutor->id_comp_type );
        
        $db->setQuery($query);
        $tutor_comp_type = $db->loadResult();
    ?>
    <dt><?php echo JText::_('COM_SEMINARMAN_COMPANY_TYPE'); ?>:</dt>
    <dd><?php echo $tutor_comp_type; ?></dd>
    <?php endif; ?>
    <?php if ((in_array("tutor_industry", $comp_data)) && !empty($this->tutor->industry)): ?>
    <dt><?php echo JText::_('COM_SEMINARMAN_INDUSTRY'); ?>:</dt>
    <dd><?php echo $this->tutor->industry; ?></dd>
    <?php endif; ?>
</dl>
<?php endif; ?>
<div class="clear"></div><br />
<span><?php echo $this->tutor->tutor_desc; ?></span>

    <?php
    // custom fields
    foreach ($this->fields as $name => $this->fieldGroup){
    if ($name != 'ungrouped'){?>

    <?php
    }

    ?>

            <?php

            foreach ($this->fieldGroup as $f){
            $f = JArrayHelper::toObject ($f);
            $f->value = $this->escape($f->value);
              if (!empty($f->value)) {
            ?>
            <div class="custom_fld">
                <h3><?php if ($f->type != "checkboxtos") { if ($f->required == 1) echo ''; echo JText::_($f->name); } ?></h3>
                <div class="custom_fld_value">
                <?php 
                    if ($f->type == "date") {
                       echo JFactory::getDate($f->value)->format("j. M Y");                   		
                    } else {
                        echo SeminarmanCustomfieldsLibrary::getFieldData($f->type , $f->value); 
                    }
                ?>
                </div>
            </div>
            <?php
              }
            }

            ?>
    <?php
    }

    ?>

<br><br>
</div>
</div>
<?php 
  if (!is_null($this->courses)):
?>
<form action="<?php echo $this->action;?>" method="post" id="adminForm">
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
<input type="hidden" name="view" value="tutor" />
<input type="hidden" name="task" value="" />
</form>
<?php endif; ?>
</div>
