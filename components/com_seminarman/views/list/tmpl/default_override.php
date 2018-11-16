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

$colspan = 14 - $colspan_hide;
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
<tr>
<?php if ($this->params->get('show_code_in_table')): ?>
	<th id="qf_code" class="sectiontableheader"><?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_COURSE_CODE', 'i.code', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
<?php endif; ?>
	<th id="qf_title" class="sectiontableheader"><?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_COURSE_TITLE', 'i.title', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
<?php if ($this->params->get('show_tags_in_table')): ?>
	<th id="qf_tags" class="sectiontableheader"><?php echo JText::_('COM_SEMINARMAN_ASSIGNED_TAGS'); ?></th>
<?php endif; ?>
<?php if ($this->params->get('show_begin_date_in_table')): ?>
	<th id="qf_start_date" class="sectiontableheader"><?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_START_DATE', 'i.start_date', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
<?php endif; ?>
<?php if ($this->params->get('show_end_date_in_table')): ?>
	<th id="qf_finish_date" class="sectiontableheader"><?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_FINISH_DATE', 'i.finish_date', $this->lists['filter_order_Dir'],	$this->lists['filter_order']); ?></th>
<?php endif; ?>
<?php if ($this->params->get('show_location')): ?>
	<th id="qf_location" class="sectiontableheader"><?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_LOCATION', 'i.location', $this->lists['filter_order_Dir'],	$this->lists['filter_order']); ?></th>
<?php endif; ?>

<?php if ($this->params->get("custom_fld_1_in_table")): ?>
    <th id="qf_custom_fld_1" class="sectiontableheader"><?php echo $this->courses[0]->custom1_lbl; ?></th>
<?php endif; ?>
<?php if ($this->params->get("custom_fld_2_in_table")): ?>
    <th id="qf_custom_fld_2" class="sectiontableheader"><?php echo $this->courses[0]->custom2_lbl; ?></th>
<?php endif; ?>
<?php if ($this->params->get("custom_fld_3_in_table")): ?>
    <th id="qf_custom_fld_3" class="sectiontableheader"><?php echo $this->courses[0]->custom3_lbl; ?></th>
<?php endif; ?>
<?php if ($this->params->get("custom_fld_4_in_table")): ?>
    <th id="qf_custom_fld_4" class="sectiontableheader"><?php echo $this->courses[0]->custom4_lbl; ?></th>
<?php endif; ?>
<?php if ($this->params->get("custom_fld_5_in_table")): ?>
    <th id="qf_custom_fld_5" class="sectiontableheader"><?php echo $this->courses[0]->custom5_lbl; ?></th>
<?php endif; ?>

<?php if ($this->params->get('show_price_in_table')): ?>
	<th id="qf_price" class="sectiontableheader"><?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_PRICE', 'i.price', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?><?php echo ($this->params->get('show_gross_price') != 2) ? "*" : ""; ?></th>
<?php endif; ?>
<?php if ($this->params->get('show_booking_deadline_in_table')): ?>
	<th id="qf_booking_deadline" class="sectiontableheader"><?php echo JText::_('COM_SEMINARMAN_BOOKING_DEADLINE'); ?></th>
<?php endif; ?>
<?php if ($this->params->get('enable_bookings')): ?>
	<th id="qf_application" class="sectiontableheader"></th>
<?php endif; ?>
</tr>
</thead>

<tbody>

<?php
if (($this->params->get('second_currency') != 'NONE') && ($this->params->get('second_currency') != $this->params->get('currency'))){
   	if (doubleval($this->params->get('factor')) > 0) {
        $show_2_price = true;
        $sec_currency = $this->params->get('second_currency');
        $factor = doubleval(str_replace(",", ".", $this->params->get('factor')));		
    } else {
        $show_2_price = false;		
    }
} else {
    $show_2_price = false;    	
}
$i=0;
foreach ($this->courses as $course):
    $course_attribs = new JRegistry();
    $course_attribs->loadString($course->attribs);
    $show_course_price = $course_attribs->get('show_price');
    $show_course_booking = $course_attribs->get('show_booking_form');
?>
<tr class="sectiontableentry" >
<?php if ($this->params->get('show_code_in_table')): ?>
	<td headers="qf_code" data-title="<?php echo JText::_('COM_SEMINARMAN_COURSE_CODE'); ?>"><?php echo $this->escape($course->code); ?></td>
<?php endif; ?>
	<td headers="qf_title" data-title="<?php echo JText::_('COM_SEMINARMAN_COURSE_TITLE'); ?>"><strong><a href="<?php echo ($this->params->get('use_alt_link_in_table') && !( empty( $course->alt_url ) || $course->alt_url == "http://" || $course->alt_url == "https://" )) ? $course->alt_url : JRoute::_('index.php?option=com_seminarman&view=courses&id=' . $course->slug . '&mod=1&Itemid=' . $Itemid); ?>"><?php echo $this->escape($course->title); ?></a></strong><?php echo $course->show_new_icon; echo $course->show_sale_icon; ?></td>
<?php if ($this->params->get('show_tags_in_table')): ?>
	<td headers="qf_tags" data-title="<?php echo JText::_('COM_SEMINARMAN_ASSIGNED_TAGS'); ?>">
	<?php 
	$tags = $course->tags;
    $n = count($tags);
    $i = 0;
    if ($n != 0):
    	foreach ($tags as $tag): ?>
		<span>
			<a href="<?php echo JRoute::_('index.php?option=com_seminarman&view=tags&id=' . $tag->slug . '&Itemid=' . $Itemid); ?>"><?php echo $this->escape($tag->name); ?></a>
		</span>
        <?php $i++; if ($i != $n) echo ',';
		endforeach;
    endif;
    ?>
    </td>
<?php endif; ?>
<?php if ($this->params->get('show_begin_date_in_table')): ?>
	<td headers="qf_start_date" data-title="<?php echo JText::_('COM_SEMINARMAN_START_DATE'); ?>">
	<?php echo $course->start; ?>
	</td>
<?php endif; ?>
<?php if ($this->params->get('show_end_date_in_table')): ?>
	<td headers="qf_finish_date" data-title="<?php echo JText::_('COM_SEMINARMAN_FINISH_DATE'); ?>">
	<?php echo $course->finish;	?>
	</td>
<?php endif; ?>
<?php if ($this->params->get('show_location')): ?>
	<td headers="qf_location" data-title="<?php echo JText::_('COM_SEMINARMAN_LOCATION'); ?>">
        <?php
    if ( empty( $course->location ) ) {
            echo JText::_('COM_SEMINARMAN_NOT_SPECIFIED');
    }
    else {
                if ( empty( $course->url ) || $course->url == "http://" ) {
                        echo $course->location;
                }
                else {?>
                        <a href='<?php echo $course->url; ?>' target="_blank"><?php echo $course->location; ?></a>
                        <?php
                }
    }
    ?>	
	</td>
<?php endif; ?>

<?php if ($this->params->get("custom_fld_1_in_table")): ?>
	<td headers="qf_custom_fld_1" data-title="<?php echo $course->custom1_lbl; ?>">
<?php echo $course->custom1_val; ?>
    </td>
<?php endif; ?>
<?php if ($this->params->get("custom_fld_2_in_table")): ?>
	<td headers="qf_custom_fld_2" data-title="<?php echo $course->custom2_lbl; ?>">
<?php echo $course->custom2_val; ?>
    </td>
<?php endif; ?>
<?php if ($this->params->get("custom_fld_3_in_table")): ?>
	<td headers="qf_custom_fld_3" data-title="<?php echo $course->custom3_lbl; ?>">
<?php echo $course->custom3_val; ?>
    </td>
<?php endif; ?>
<?php if ($this->params->get("custom_fld_4_in_table")): ?>
	<td headers="qf_custom_fld_4" data-title="<?php echo $course->custom4_lbl; ?>">
<?php echo $course->custom4_val; ?>
    </td>
<?php endif; ?>
<?php if ($this->params->get("custom_fld_5_in_table")): ?>
	<td headers="qf_custom_fld_5" data-title="<?php echo $course->custom5_lbl; ?>">
<?php echo $course->custom5_val; ?>
    </td>
<?php endif; ?>

<?php if ($this->params->get('show_price_in_table')): ?>
	<td headers="qf_price" data-title="<?php echo JText::_('COM_SEMINARMAN_PRICE'); ?>">
<?php echo $course->price; ?>
    </td>
<?php endif; ?>
<?php if ($this->params->get('show_booking_deadline_in_table')): ?>
	<td headers="qf_booking_deadline" data-title="<?php echo JText::_('COM_SEMINARMAN_BOOKING_DEADLINE'); ?>">
<?php echo $course->deadline; ?>
    </td>
<?php endif; ?>
<?php if ($this->params->get('enable_bookings')): ?>
	<td class="centered" headers="qf_book">
	<?php
	    if ($show_course_booking !== 0) echo $course->book_link; 
	?>
	</td>
<?php endif; ?>
</tr>

<?php
$i++;
endforeach;
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