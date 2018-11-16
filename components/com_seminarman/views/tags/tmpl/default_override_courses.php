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
if (!($this->params->get('show_code_in_table'))) $colspan_hide += 1;
if (!($this->params->get('show_tags_in_table'))) $colspan_hide += 1;
if (!($this->params->get('show_begin_date_in_table'))) $colspan_hide += 1;
if (!($this->params->get('show_end_date_in_table'))) $colspan_hide += 1;
if (!($this->params->get('show_location'))) $colspan_hide += 1;
if (!($this->params->get('show_price_in_table'))) $colspan_hide += 1;
if (!($this->params->get('show_booking_deadline_in_table'))) $colspan_hide += 1;

if (!($this->params->get("custom_fld_1_in_table"))) $colspan_hide += 1;
if (!($this->params->get("custom_fld_2_in_table"))) $colspan_hide += 1;
if (!($this->params->get("custom_fld_3_in_table"))) $colspan_hide += 1;
if (!($this->params->get("custom_fld_4_in_table"))) $colspan_hide += 1;
if (!($this->params->get("custom_fld_5_in_table"))) $colspan_hide += 1;

if ($this->course_table_quelle != 'archive') {  // booking allowed only in the normal dates table
  if (!($this->params->get('enable_bookings'))) $colspan_hide += 1;
}
if ($this->course_table_quelle != 'archive') {
  $colspan = 14 - $colspan_hide;
  $courses_quelle = $this->courses;
} else {  // archive table
  $colspan = 13 - $colspan_hide;
  $courses_quelle = $this->archive_courses;
}
// $colspan = ($this->params->get('show_location')) ? 7 : 6;
$Itemid = JRequest::getInt('Itemid');
?>

<form action="<?php echo $this->action;?>" method="post" id="adminForm">
<?php if ($this->params->get('filter') || $this->params->get('display')):?>
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
			<label for="limit"><?php echo JText::_('COM_SEMINARMAN_DISPLAY_NUM') ?></label>
			<?php 
			  if ($this->course_table_quelle != 'archive') { 
			    echo $this->pageNav->getLimitBox(); 
			  } else {
                echo $this->pageNav3->getLimitBox();
              }
			?>
		</div>
		<?php endif;?>
</div>
<?php endif;?>

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
    <th id="qf_custom_fld_1" class="sectiontableheader"><?php echo $courses_quelle[0]->custom1_lbl; ?></th>
<?php endif; ?>
<?php if ($this->params->get("custom_fld_2_in_table")): ?>
    <th id="qf_custom_fld_2" class="sectiontableheader"><?php echo $courses_quelle[0]->custom2_lbl; ?></th>
<?php endif; ?>
<?php if ($this->params->get("custom_fld_3_in_table")): ?>
    <th id="qf_custom_fld_3" class="sectiontableheader"><?php echo $courses_quelle[0]->custom3_lbl; ?></th>
<?php endif; ?>
<?php if ($this->params->get("custom_fld_4_in_table")): ?>
    <th id="qf_custom_fld_4" class="sectiontableheader"><?php echo $courses_quelle[0]->custom4_lbl; ?></th>
<?php endif; ?>
<?php if ($this->params->get("custom_fld_5_in_table")): ?>
    <th id="qf_custom_fld_5" class="sectiontableheader"><?php echo $courses_quelle[0]->custom5_lbl; ?></th>
<?php endif; ?>

<?php if ($this->params->get('show_price_in_table')): ?>
	<th id="qf_price" class="sectiontableheader"><?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_PRICE', 'i.price', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?><?php echo ($this->params->get('show_gross_price') != 2) ? "*" : ""; ?></th>
<?php endif; ?>
<?php if ($this->params->get('show_booking_deadline_in_table')): ?>
	<th id="qf_booking_deadline" class="sectiontableheader"><?php echo JText::_('COM_SEMINARMAN_BOOKING_DEADLINE'); ?></th>
<?php endif; ?>
<?php if (($this->course_table_quelle != 'archive')&&($this->params->get('enable_bookings'))): ?>
	<th id="qf_application" class="sectiontableheader"></th>
<?php endif; ?>
</tr>
</thead>

<tbody>

<?php
$i = 0;
foreach ($courses_quelle as $course): ?>
<tr class="sectiontableentry" >
<?php if ($this->params->get('show_code_in_table')): ?>
	<td headers="qf_code" data-title="<?php echo JText::_('COM_SEMINARMAN_COURSE_CODE'); ?>"><?php echo $this->escape($course->code); ?></td>
<?php endif; ?>
	<td headers="qf_title" data-title="<?php echo JText::_('COM_SEMINARMAN_COURSE_TITLE'); ?>"><strong><a href="<?php echo ($this->params->get('use_alt_link_in_table') && !( empty( $course->alt_url ) || $course->alt_url == "http://" || $course->alt_url == "https://" )) ? $course->alt_url : JRoute::_('index.php?option=com_seminarman&view=courses&mod=1&id=' . $course->slug . '&Itemid=' . $Itemid); ?>"><?php echo $this->escape($course->title); ?></a></strong><?php echo $course->show_new_icon; echo $course->show_sale_icon; ?></td>
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
	<?php echo $course->finish; ?>
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
<?php if (($this->course_table_quelle != 'archive')&&($this->params->get('enable_bookings'))): ?>
	<td class="centered" headers="qf_book">
	<?php echo $course->book_link; ?>
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
<div class="right"></div>

<input type="hidden" name="option" value="com_seminarman" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['filter_order'];?>" />
<input type="hidden" name="filter_order_Dir" value="" />
<input type="hidden" name="view" value="tags" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="id" value="<?php echo $this->tag->id;?>" />
</form>

<div class="pagination">
<?php
  if ($this->course_table_quelle != 'archive') { 
    echo $this->pageNav->getPagesLinks(); 
  } else {
    echo $this->pageNav3->getPagesLinks();
  }
?>
</div>