<?php
/**
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011 Open Source Group GmbH www.osg-gmbh.de
* @website http://www.profinvent.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

defined('_JEXEC') or die('Restricted access');

$params = JComponentHelper::getParams('com_seminarman');

$Itemid = JRequest::getInt('Itemid');

$colspan_hide = 0;
if (!($this->params->get( 'show_code_in_my_bookings'))) $colspan_hide += 1;
if (!($this->params->get('invoice_generate'))) $colspan_hide += 1;
if (!($this->params->get('enable_paypal'))) $colspan_hide += 1;
if (!($this->params->get("custom_fld_1_in_table"))) $colspan_hide += 1;
if (!($this->params->get("custom_fld_2_in_table"))) $colspan_hide += 1;
if (!($this->params->get("custom_fld_3_in_table"))) $colspan_hide += 1;
if (!($this->params->get("custom_fld_4_in_table"))) $colspan_hide += 1;
if (!($this->params->get("custom_fld_5_in_table"))) $colspan_hide += 1;
$colspan = 13 - $colspan_hide;
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

<h2 class="seminarman bookings">
	<?php

echo JText::_('COM_SEMINARMAN_BOOKED_COURSES') . ': ';

?>
</h2>

<?php

if (!count($this->courses)):

?>

	<div class="note">
		<?php

    echo JText::_('COM_SEMINARMAN_NO_CURRENT_BOOKINGS');

?>
	</div>

<?php

else:

?>

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
	<tr>
   <?php if ( $this->params->get( 'show_counter_in_my_bookings' ) ) : ?>
   <td class="proc2 centered sectiontableheader<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
      <?php echo JText::_('#'); ?>
   </td>
   <?php else: ?>
  <td class="pix3 centered">
  </td>
   <?php endif; ?>
<?php if ( $this->params->get( 'show_code_in_my_bookings' ) ) : ?> 
<th id="qf_code" class="sectiontableheader"><?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_COURSE_CODE', 'i.code', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
<?php endif; ?>
				<th id="qf_title" class="sectiontableheader"><?php

echo JHTML::_('grid.sort', 'COM_SEMINARMAN_COURSE_TITLE', 'i.title', $this->lists['filter_order_Dir'],
	$this->lists['filter_order']);

				?></th>
				<th id="qf_start_date" class="sectiontableheader"><?php

echo JHTML::_('grid.sort', 'COM_SEMINARMAN_START_DATE', 'i.start_date', $this->lists['filter_order_Dir'],
	$this->lists['filter_order']);

				?></th>
				<th id="qf_finish_date" class="sectiontableheader"><?php

echo JHTML::_('grid.sort', 'COM_SEMINARMAN_FINISH_DATE', 'i.finish_date', $this->lists['filter_order_Dir'],
	$this->lists['filter_order']);

				?></th>
				
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
				
				<th id="qf_price" class="sectiontableheader"><?php

echo JHTML::_('grid.sort', 'COM_SEMINARMAN_PRICE', 'i.price', $this->lists['filter_order_Dir'],
	$this->lists['filter_order']);

				?><?php echo ($this->params->get('show_gross_price') != 2) ? "*" : ""; ?></th>
				
<?php if ($params->get('invoice_generate') == 1): ?>
<th id="qf_invoice" class="sectiontableheader"><?php echo JText::_('COM_SEMINARMAN_INVOICE'); ?></th>
<?php endif; ?>
				
				<?php

				if ($this->params->get('enable_paypal')):

				?>
					<th id="qf_application" class="sectiontableheader"><?php

					echo JText::_('COM_SEMINARMAN_PAY_ONLINE');

					?></th>
				<?php

				endif;

				?>
			</tr>
	</thead>

	<tbody>

	<?php
	$jversion = new JVersion();
	$short_version = $jversion->getShortVersion();
	$count_id = 0;
	foreach ($this->courses as $course):
	
	$stati = $course->booking_state;
	if ($stati == 0) {
		$stati_text = JText::_( 'COM_SEMINARMAN_SUBMITTED' );
	} elseif ($stati == 1) {
		$stati_text = JText::_( 'COM_SEMINARMAN_PENDING' );
	} elseif ($stati == 2) {
		$stati_text = JText::_( 'COM_SEMINARMAN_PAID' );
	} elseif ($stati == 3) {
		$stati_text = JText::_( 'COM_SEMINARMAN_CANCELED' );
	} elseif ($stati == 4) {
    	$stati_text = JText::_( 'COM_SEMINARMAN_WL' );
    } elseif ($stati == 5) {
   		$stati_text = JText::_( 'COM_SEMINARMAN_AWAITING_RESPONSE' );
   	}
	
	//$htmlclassnumber = $course->odd + 1;
	if (version_compare($short_version, "3.0", 'ge')) {
	    $itemParams = new JRegistry($course->attribs);
	} else {
	    $itemParams = new JParameter($course->attribs);
	}
	?>
  			<tr class="sectiontableentry" >
    			 <?php if ( $this->params->get( 'show_counter_in_my_bookings' ) ) : ?>
   <td headers="qf_publish_up" data-title="<?php echo JText::_('#'); ?>">
         <?php
         echo $this->pageNav->getRowOffset( $course->count ); ?>
   </td>
 <?php else: ?> <td headers="qf_publish_up"></td>
 <?php endif; ?>
 <?php if ( $this->params->get( 'show_code_in_my_bookings' ) ) : ?> 
 				<td headers="qf_code" data-title="<?php echo JText::_('COM_SEMINARMAN_COURSE_CODE'); ?>"><?php echo $this->escape($course->code); ?></td>
 <?php endif; ?>
				<td headers="qf_title"  data-title="<?php echo JText::_('COM_SEMINARMAN_COURSE_TITLE'); ?>">
    				<strong><a href="<?php

    				echo JRoute::_('index.php?option=com_seminarman&view=courses&cid=' . $this->category->slug . '&id=' . $course->
    				    slug . '&Itemid=' . $Itemid);

    				?>"><?php

    				echo $this->escape($course->title);
    				?></a></strong><?php
    				echo $course->show_new_icon;
    				echo $course->show_sale_icon;
    				?>


				</td>
		    			<td headers="qf_start_date" data-title="<?php echo JText::_('COM_SEMINARMAN_START_DATE'); ?>">
    				<?php

    				//echo date('d-M-y', strtotime($course->start_date));
    				echo $course->start;

    				?>
				</td>
    			<td headers="qf_finish_date" data-title="<?php echo JText::_('COM_SEMINARMAN_FINISH_DATE'); ?>">
    				<?php

    				//echo date('d-M-y', strtotime($course->finish_date));
    				echo $course->finish;

    				?>
				</td>
				
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
				
    			<td headers="qf_price" data-title="<?php echo JText::_('COM_SEMINARMAN_PRICE'); ?>">
    				<?php

    				echo $course->price_simple;

    				?>
				</td>

 <?php
if ($params->get('invoice_generate') == 1)
{
	if (!empty($course->invoice_filename_prefix) && ($course->price > 0))
	{
		echo '<td class="centered" data-title="' . JText::_('COM_SEMINARMAN_INVOICE') . '"><a href="'. JRoute::_('index.php?option=com_seminarman&view=bookings&layout=invoicepdf&appid=' . $course->applicationid . '&Itemid=' . $Itemid) .'"><img alt="'.$course->invoice_filename_prefix.$course->invoice_number.'.pdf" src="components/com_seminarman/assets/images/mime-icon-16/pdf.png" /></a></td>';
	}
	else
		echo '<td class="centered" data-title="' . JText::_('COM_SEMINARMAN_INVOICE') . '">-</td>';
}
?>				<?php

 				if ($this->params->get('enable_paypal')):

 				?>
					<td headers="qf_book" data-title="<?php echo JText::_('COM_SEMINARMAN_PAY_ONLINE'); ?>"><?php if ($course->price > 0) echo $course->paypal_link; ?>


					</td>
				<?php

				endif;

				?>

			</tr>

			<tr><td colspan="<?php echo $colspan; ?>" class="res_full">
			<div class="tabulka">
<div class="radek">

  	<div class="bunka hlavicka"><div class="matrjoska">
<?php echo JText::_('COM_SEMINARMAN_STATUS').': '.$stati_text; ?>
  	</div></div>

<?php if ($itemParams->get('show_hits', $this->params->get('show_hits'))): ?>
  	<div class="bunka hlavicka"><div class="matrjoska">
<?php echo JText::_('COM_SEMINARMAN_HITS').': '.$course->hits; ?>
  	</div></div>
<?php endif; ?>
<?php if ($itemParams->get('show_hyperlink', $this->params->get('show_hyperlink'))&& $course->alt_url<>"http://" && $course->alt_url<>"https://" && trim($course->alt_url)<>""):?>
  	<div class="bunka hlavicka"><div class="matrjoska">
<?php echo $course->link;?>
  	</div></div>
<?php endif; ?>
<?php if ($itemParams->get('show_tutor', $this->params->get('show_tutor'))):?>

<?php endif; ?>
<?php if ($itemParams->get('show_location', $this->params->get('show_location')) && !empty($course->location)):?>
<div class="bunka hlavicka"><div class="matrjoska">
<?php 
    if ( empty( $course->url ) || $course->url == "http://" ) {
        echo JText::_('COM_SEMINARMAN_LOCATION').': '.$course->location;
    } else {
        echo JText::_('COM_SEMINARMAN_LOCATION') . ': <a target=_blank href="' . $course->url . '">' . $course->location . '</a>';
    }    
?>
  	</div></div>
<?php endif; ?>
<?php if ($itemParams->get('show_group', $this->params->get('show_group')) && !empty($course->cgroup)):?>
<div class="bunka hlavicka"><div class="matrjoska">
<?php echo JText::_('COM_SEMINARMAN_GROUP').': '.$course->cgroup;?>
  	</div></div>
<?php endif; ?>
<?php if ($itemParams->get('show_experience_level', $this->params->get('show_experience_level')) && !empty($course->level)):?>
<div class="bunka hlavicka"><div class="matrjoska">
<?php echo JText::_('COM_SEMINARMAN_LEVEL').': '.$course->level;?>
  	</div></div>
<?php endif; ?>
<?php if ($itemParams->get('show_capacity', $this->params->get('show_capacity'))):?>
	<div class="bunka hlavicka"><div class="matrjoska">
	<?php if ( $itemParams->get('current_capacity', $this->params->get( 'current_capacity' ))) : ?>
	      <?php echo JText::_('COM_SEMINARMAN_FREE_SEATS') .': '; ?>
	<?php else : ?>
		  <?php echo JText::_('COM_SEMINARMAN_SEATS') .': '; ?>
	<?php endif; ?>
	<?php echo $course->capacity; ?>
	</div></div>
<?php endif; ?>
<?php if ($course->cancel_allowed): ?>
    <div class="bunka hlavicka button2-left" style="float: right"><div class="matrjoska">
      <a href="<?php echo JRoute::_('index.php?option=com_seminarman&controller=application&task=cancel_booking&appid='.$course->applicationid.'&'.JSession::getFormToken().'=1'); ?>"><?php echo JText::_('COM_SEMINARMAN_CANCEL'); ?></a>
    </div></div>
<?php endif; ?>
</div>
<div class="cl"></div>
</div>


			</td></tr>
			
	<?php if ($this->params->get('show_register_name', 0)): ?>		
			<tr><td colspan="<?php echo $colspan; ?>" class="res_full">
			<div class="tabulka">
              <div class="radek">
                <div class="bunka hlavicka"><div class="matrjoska">	
                <?php 
                echo JText::_('COM_SEMINARMAN_NAME').': ';
                $display_name_head = trim($course->booking_salutation.' '.$course->booking_title);
                $display_name_tail = trim($course->booking_first_name.' '.$course->booking_last_name);
                if (!empty($display_name_head)) {
                	$display_name = $display_name_head . ' ' . $display_name_tail;
                } else {
                	$display_name = $display_name_tail;
                }
                echo $display_name;
                ?>
                </div></div>
              </div>
            </div>
            </td></tr>		
	<?php endif; ?>			
			
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
