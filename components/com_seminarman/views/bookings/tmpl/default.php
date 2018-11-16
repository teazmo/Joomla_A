<?php
/**
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011 Open Source Group GmbH www.osg-gmbh.de
* @website http://www.profinvent.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

defined('_JEXEC') or die('Restricted access');
require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS . 'helpers' . DS . 'seminarman.php');
$params = JComponentHelper::getParams('com_seminarman');

$Itemid = JRequest::getInt('Itemid');
$db = JFactory::getDBO();
$user = JFactory::getUser();
$user_id = (int)$user->get('id');

$colspan_hide = 0;
if (!($this->params->get( 'show_code_in_my_bookings'))) $colspan_hide += 1;
if (!($this->params->get('invoice_generate'))) $colspan_hide += 1;
if (!($this->params->get('enable_paypal'))) $colspan_hide += 1;
if (!($this->params->get("custom_fld_1_in_table"))) $colspan_hide += 1;
if (!($this->params->get("custom_fld_2_in_table"))) $colspan_hide += 1;
if (!($this->params->get("custom_fld_3_in_table"))) $colspan_hide += 1;
if (!($this->params->get("custom_fld_4_in_table"))) $colspan_hide += 1;
if (!($this->params->get("custom_fld_5_in_table"))) $colspan_hide += 1;
if (!($this->params->get('show_begin_time_in_my_bookings'))) $colspan_hide += 1;
if (!($this->params->get('show_finish_time_in_my_bookings'))) $colspan_hide += 1;
if (!($this->params->get('show_price_in_my_bookings'))) $colspan_hide += 1;
if (!($this->params->get('show_invoice_in_my_bookings'))) $colspan_hide += 1;
$colspan = 15 - $colspan_hide;
?>
<script type="text/javascript">

	function tableOrdering( order, dir, task )
	{
		var form = document.getElementById("adminForm");

		form.filter_order.value 	= order;
		form.filter_order_Dir.value	= dir;
		document.getElementById("adminForm").submit( task );
	}
</script>

<div id="seminarman" class="seminarman">

<?php 
    if ($this->params->get('show_page_heading', 0)) {
    	$page_heading = trim($this->params->get('page_heading'));
        if (!empty($page_heading)) {
            echo '<h1 class="componentheading">' . $page_heading . '</h1>';
        }
    }
?>

<?php if($this->params->get('enable_bookings')==1 && $this->params->get('user_booking_rules')==1): ?>
<h2 class="seminarman bookings">
<?php echo JText::_('COM_SEMINARMAN_BOOKING_STATISTIC') . ': '; ?>
</h2>
<table class="seminarmancoursetable">
    <thead>
    <tr><th><?php echo JText::_('COM_SEMINARMAN_TITLE'); ?></th><th><?php echo JText::_('COM_SEMINARMAN_CATEGORY'); ?></th><th><?php echo JText::_('COM_SEMINARMAN_FROM'); ?></th><th><?php echo JText::_('COM_SEMINARMAN_TO'); ?></th><th><?php echo JText::_('COM_SEMINARMAN_AMOUNT'); ?></th><th><?php echo JText::_('COM_SEMINARMAN_BOOKED'); ?></th></tr>
    </thead>
  <?php 
    foreach($this->bookingrules as $bookingrule) {
      $bookingrule_detail = json_decode($bookingrule->rule_text);

      $query = $db->getQuery(true);
      $query->select( 'title' );
      $query->from( '`#__seminarman_categories`' );
      $query->where( 'id=' . $bookingrule_detail->category );
      
      $db->setQuery( $query );
      $cat_name = $db->loadResult();
      
      $booked_amount = JHTMLSeminarman::get_user_booking_total_in_category_rule($bookingrule_detail->category, $user_id, $bookingrule_detail->start_date, $bookingrule_detail->finish_date);
      
      echo "<tr>".
        "<td>".$bookingrule->title."</td>".
        "<td>".$cat_name."</td>".
        "<td>".JHtml::_('date', $bookingrule_detail->start_date, JText::_('COM_SEMINARMAN_DATE_FORMAT1'))."</td>".
        "<td>".JHtml::_('date', $bookingrule_detail->finish_date, JText::_('COM_SEMINARMAN_DATE_FORMAT1'))."</td>".
        "<td>".$bookingrule_detail->amount."</td>".
        "<td>".$booked_amount."</td>".
        "</tr>";
    }  
  ?>
</table>
<?php endif; ?>

<h2 class="seminarman bookings">
	<?php echo JText::_('COM_SEMINARMAN_BOOKED_COURSES') . ': '; ?>
</h2>

<?php if (!count($this->courses)): ?>
	<div class="note">
		<?php echo JText::_('COM_SEMINARMAN_NO_CURRENT_BOOKINGS'); ?>
	</div>
<?php else: ?>

<form action="<?php echo $this->action; ?>" method="post" id="adminForm">
<?php if ($this->params->get('filter') || $this->params->get('display')): ?>
<div id="qf_filter" class="floattext">
		<?php if ($this->params->get('filter')): ?>
		<dl class="qf_fleft">
			<dd>
				<?php echo JText::_('COM_SEMINARMAN_COURSE') . ': ';?>
				<input type="text" name="filter" id="filter" value="<?php echo $this->lists['filter']; ?>" class="text_area" size="15"/>
			</dd>
			<dd>
				<?php echo JText::_('COM_SEMINARMAN_LEVEL') . ': ';?>
				<?php echo $this->lists['filter_experience_level'];?>
				<button  onclick="document.getElementById('adminForm').submit();"><?php echo JText::_('COM_SEMINARMAN_GO');?></button>
				<button onclick="document.getElementById('filter').value='';document.getElementById('filter_experience_level').value=0;document.getElementById('adminForm').submit();"><?php echo JText::_('COM_SEMINARMAN_RESET'); ?></button>
			</dd>
		</dl>
		<?php endif; ?>
		<?php if ($this->params->get('display')): ?>
		<div class="qf_fright">
			<?php
			echo '<label for="limit">' . JText::_('COM_SEMINARMAN_DISPLAY_NUM') . '</label>&nbsp;';
			echo $this->pageNav->getLimitBox();
			?>
		</div>
		<?php endif; ?>
</div>
<?php endif; ?>

<table class="seminarmancoursetable" summary="seminarman">
	<thead>
<?php 
$table_header = SMANFunctions::buildCourseTableHeaderForMyBooking($this->params, $this->courses, $this->lists);
echo $table_header;
?>
	</thead>

	<tbody>

	<?php
	$jversion = new JVersion();
	$short_version = $jversion->getShortVersion();
	$count_id = 0;
	foreach ($this->courses as $course):
	
	//$htmlclassnumber = $course->odd + 1;
	if (version_compare($short_version, "3.0", 'ge')) {
	    $itemParams = new JRegistry($course->attribs);
	} else {
	    $itemParams = new JParameter($course->attribs);
	}
	?>

<?php 
    $table_row = SMANFunctions::buildCourseSingleRowForMyBooking($course, $this->category, $itemParams, $this->params, $this->pageNav, $Itemid);
    echo $table_row;
?>

			<tr><td colspan="<?php echo $colspan; ?>" class="res_full">
			<div class="tabulka">
<div class="radek">

<?php 
    $other_info = SMANFunctions::buildCourseSingleOtherInfoForMyBooking($course, $itemParams, $this->params, $count_id, $Itemid);
    echo $other_info;
?>

</div>
</div>

			</td></tr>		
			
	<?php
	$count_id++;
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
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['filter_order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="" />
	<input type="hidden" name="view" value="bookings" />
	<input type="hidden" name="task" value="" />
	</form>
	<div class="pagination"><?php echo $this->pageNav->getPagesLinks(); ?></div>
<?php endif; ?>
</div>
