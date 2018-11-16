<?php
/**
 *
 * Copyright (C) 2016 Open Source Group GmbH www.osg-gmbh.de
 * @website https://service.osg-gmbh.de
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die('Restricted access');

$mainframe = JFactory::getApplication();
$params = $mainframe->getParams('com_seminarman');

if (JVERSION >= 3.4) {
    JHtml::_('behavior.formvalidator');
} else {
    JHTML::_('behavior.formvalidation');
}

// list of courses
$db = JFactory::getDBO();
// if (!$db->query()) {
// 	JError::raiseError(500, $db->stderr(true));
// 	return;
// }
$db = JFactory::getDBO();
$query = $db->getQuery(true);
$query->select( '*' );
$query->from( '#__seminarman_courses' );
$query->where( 'id ='. $_POST['course_id'] );
$db->setQuery( $query );

$courseRows = $db->loadObject();

// egtl. $courseRows->start_date, $courseRows->start_time werden nicht mehr benutzt, stattdessen $this->course->start mit Berücksichtigung von Zeitzone
if ($courseRows->start_date != '0000-00-00'){
	$courseRows->start_date = JFactory::getDate($courseRows->start_date)->format("j. M Y");
} else{
	$courseRows->start_date = JText::_('COM_SEMINARMAN_NOT_SPECIFIED');
}
if ($courseRows->finish_date != '0000-00-00'){
	$courseRows->finish_date = JFactory::getDate($courseRows->finish_date)->format("j. M Y");
}else{
	$courseRows->finish_date = JText::_('COM_SEMINARMAN_NOT_SPECIFIED');
}

if (!empty($courseRows->start_time)) {
	// fix for 24:00:00 (illegal time colock)
	if ($courseRows->start_time == '24:00:00') $courseRows->start_time = '23:59:59';
	$courseRows->start_time = date('H:i', strtotime($courseRows->start_time));
} else {
	$courseRows->start_time = '';
}

if (!empty($courseRows->finish_time)) {
	// fix for 24:00:00 (illegal time colock)
	if ($courseRows->finish_time == '24:00:00') $courseRows->finish_time = '23:59:59';
	$courseRows->finish_time = date('H:i', strtotime($courseRows->finish_time));
} else {
	$courseRows->finish_time = '';
}

$price_orig = $courseRows->price;
$price_booking = $price_orig;
if ($_POST['booking_price'][0] == 1) { // 2. price group
	$price_booking = $courseRows->price2;
} elseif ($_POST['booking_price'][0] == 2) { // 3. price group
	$price_booking = $courseRows->price3;
} elseif ($_POST['booking_price'][0] == 3) { // 4. price group
	$price_booking = $courseRows->price4;
} elseif ($_POST['booking_price'][0] == 4) { // 5. price group
	$price_booking = $courseRows->price5;
}
if (empty($_POST['attendees'])) {
	$_POST['attendees'] = 1;
}
$price_total_orig = $price_orig * $_POST['attendees'];
$price_total_booking = $price_booking * $_POST['attendees'];

$price_total_discount = $price_total_booking - $price_total_orig;
if ($price_total_discount > 0) { // Aufpreis
	$price_diff_label = JText::_('COM_SEMINARMAN_CART_EXTRA_TOTAL');
} else { // Rabatte
	$price_diff_label = JText::_('COM_SEMINARMAN_CART_DISCOUNT_TOTAL');
}


$tax_rate = $courseRows->vat / 100.0;
$tax_booking = $price_total_booking * $tax_rate;
$price_total_booking_with_tax = $price_total_booking * (1 + $tax_rate);

// extra fees coming from plugin
$dispatcher=JDispatcher::getInstance();
JPluginHelper::importPlugin('seminarman');
$extrafees=$dispatcher->trigger('onOverviewCalc', array($courseRows));  // we need course attributes etc.
if(isset($extrafees) && !empty($extrafees)) {
	$final_costs = $price_total_booking_with_tax + $extrafees[0];
} else {
	$final_costs = $price_total_booking_with_tax;
}

$post = JRequest::get('post');
$payment_method = 0;
$payment_fee = 0;
if (isset($post['payment_method'])) {
	$payment_method = $post['payment_method'][0];
}
if ($payment_method == 1) { // bank transfer
	$param_bank_fee_value = $params->get('bank_fee_value');
	if(!empty($param_bank_fee_value)) {
		$bank_fee_value = doubleval(str_replace(",", ".", $param_bank_fee_value));
		if ($params->get('bank_fee_type') == 1) { // percentage
			$bank_fee_rate = $bank_fee_value / 100;
			$payment_fee = $final_costs * $bank_fee_rate;
		} else {
			$payment_fee = $bank_fee_value;
		}
	} else {
		$payment_fee = 0;
	}
} elseif ($payment_method == 2) { // paypal
	$param_paypal_fee_value = $params->get('paypal_fee_value');
	if(!empty($param_paypal_fee_value)) {
		$paypal_fee_value = doubleval(str_replace(",", ".", $param_paypal_fee_value));
		if ($params->get('paypal_fee_type') == 1) { // percentage
			$paypal_fee_rate = $paypal_fee_value / 100;
			$payment_fee = $final_costs * $paypal_fee_rate;				
		} else {
			$payment_fee = $paypal_fee_value;
		}		
	} else {
		$payment_fee = 0;
	}
}
// set it as temp attribute
$this->course->selected_payment_fee = $payment_fee;

if ($payment_fee <> 0) {
	$payment_fee_display = JText::sprintf('%.2f', $payment_fee);
} else {
	$payment_fee_display = "";
}

$total_payment = $final_costs + $payment_fee;
$total_payment_display = JText::sprintf('%.2f', $total_payment);
?>
<table class="seminarman_cart_invoice">
<?php switch ($this->params->get('payment_overview_layout')){
	case 1: // wenige Spalten ?>
	<thead>
	<tr>
	<td class="seminarman_cart_course"><?php echo JText::_('COM_SEMINARMAN_COURSE'); ?></td>
	<td class="seminarman_cart_quantity"><?php echo JText::_('COM_SEMINARMAN_CART_QUANTITY'); ?></td>
	<td class="seminarman_cart_price_single"><?php echo JText::_('COM_SEMINARMAN_CART_PRICE_SINGLE') . ' ' . $params->get('currency'); ?></td>
	<td class="seminarman_cart_price_total"><?php echo JText::_('COM_SEMINARMAN_CART_PRICE_TOTAL') . ' ' . $params->get('currency'); ?></td>
	</tr>
	</thead>
	<tbody>
	<tr class="seminarman_cart_item">
	<td class="seminarman_cart_course" data-title="<?php echo JText::_('COM_SEMINARMAN_COURSE'); ?>:"><?php echo $courseRows->title . " (" . $courseRows->code . ")"; ?><br/>
	<?php
	  echo JText::_('COM_SEMINARMAN_START_DATE') . ': ';
	  echo $this->course->start;
	  echo ', ' . JText::_('COM_SEMINARMAN_FINISH_DATE') . ': '; 
	  echo $this->course->finish;
	?>
	</td>
	<td class="seminarman_cart_quantity" data-title="<?php echo JText::_('COM_SEMINARMAN_CART_QUANTITY'); ?>:"><?php echo $_POST['attendees']; ?></td>
	<td class="seminarman_cart_price_single" data-title="<?php echo JText::_('COM_SEMINARMAN_CART_PRICE_SINGLE'); ?>:" data-currency=" <?php echo $params->get('currency'); ?>"><?php echo JText::sprintf('%.2f', round(doubleval(str_replace(",", ".", $this->escape($price_orig))), 2)); ?></td>
	<td class="seminarman_cart_price_total" data-title="<?php echo JText::_('COM_SEMINARMAN_CART_PRICE_TOTAL'); ?>:" data-currency=" <?php echo $params->get('currency'); ?>"><?php echo JText::sprintf('%.2f', round(doubleval(str_replace(",", ".", $this->escape($price_total_orig))), 2)); ?></td>
	</tr>

	<?php if ($courseRows->vat <> 0) { ?>
	<tr>
	<td class="seminarman_cart_netto_total_title" colspan="3"><?php echo JText::_('COM_SEMINARMAN_CART_NETTO_TOTAL'); ?></td>
	<td class="seminarman_cart_netto_total" data-title="<?php echo JText::_('COM_SEMINARMAN_CART_NETTO_TOTAL'); ?>:" data-currency=" <?php echo $params->get('currency'); ?>"><?php echo JText::sprintf('%.2f', round(doubleval(str_replace(",", ".", $this->escape($price_total_orig))), 2)); ?></td>
	</tr>
	<?php } ?>
	<?php if ($price_total_discount <> 0) { ?>
	<tr>
	<td class="seminarman_cart_discount_total_title" colspan="3"><?php echo $price_diff_label; ?></td>
	<td class="seminarman_cart_discount_total" data-title="<?php echo $price_diff_label; ?>:" data-currency=" <?php echo $params->get('currency'); ?>"><?php echo JText::sprintf('%.2f', round(doubleval(str_replace(",", ".", $this->escape($price_total_discount))), 2)); ?></td>
	</tr>
	<?php } ?>
	<?php if ($courseRows->vat <> 0) { ?>
	<tr>
	<td class="seminarman_cart_withoutVat_total_title" colspan="3"><?php echo JText::sprintf('COM_SEMINARMAN_CART_WITHOUT_VAT', JText::sprintf('%.0f', round(doubleval(str_replace(",", ".", $this->escape($courseRows->vat))), 2)) . '%'); ?></td>
	<td class="seminarman_cart_withoutVat_total" data-title="<?php echo JText::sprintf('COM_SEMINARMAN_CART_WITHOUT_VAT', JText::sprintf('%.0f', round(doubleval(str_replace(",", ".", $this->escape($courseRows->vat))), 2)) . '%'); ?>:" data-currency=" <?php echo $params->get('currency'); ?>"><?php echo JText::sprintf('%.2f', round(doubleval(str_replace(",", ".", $this->escape($tax_booking))), 2)); ?></td>
	</tr>
	<?php } ?>
	<tr>
	<td class="seminarman_cart_booking_total_title" colspan="3"><?php echo JText::_('COM_SEMINARMAN_CART_BOOKING_TOTAL'); ?></td>
	<td class="seminarman_cart_booking_total" data-title="<?php echo JText::_('COM_SEMINARMAN_CART_BOOKING_TOTAL'); ?>:" data-currency=" <?php echo $params->get('currency'); ?>"><?php echo JText::sprintf('%.2f', round(doubleval(str_replace(",", ".", $this->escape($price_total_booking_with_tax))), 2)); ?></td>
	</tr>
	<?php
			break;
		default:  // 2 more columns
	?>
	<thead>
	<tr>
	<td class="seminarman_cart_code"><?php echo JText::_('COM_SEMINARMAN_COURSE_CODE'); ?></td>
	<td class="seminarman_cart_course"><?php echo JText::_('COM_SEMINARMAN_COURSE'); ?></td>
	<td class="seminarman_cart_date"><?php echo JText::_('COM_SEMINARMAN_DATE'); ?></td>
	<td class="seminarman_cart_quantity"><?php echo JText::_('COM_SEMINARMAN_CART_QUANTITY'); ?></td>
	<td class="seminarman_cart_price_single"><?php echo JText::_('COM_SEMINARMAN_CART_PRICE_SINGLE') . ' ' . $params->get('currency'); ?></td>
	<td class="seminarman_cart_price_total"><?php echo JText::_('COM_SEMINARMAN_CART_PRICE_TOTAL') . ' ' . $params->get('currency'); ?></td>
	</tr>
	</thead>
	<tbody>
	<tr class="seminarman_cart_item">
	<td class="seminarman_cart_code" data-title="<?php echo JText::_('COM_SEMINARMAN_COURSE_CODE'); ?>:"><?php echo $courseRows->code; ?></td>
	<td class="seminarman_cart_course" data-title="<?php echo JText::_('COM_SEMINARMAN_COURSE'); ?>:"><?php echo $courseRows->title; ?></td>
	<td class="seminarman_cart_date" data-title="<?php echo JText::_('COM_SEMINARMAN_DATE'); ?>">
	<?php
	  echo $this->course->start;
	  ?>
	  <?php
	  echo ' - <br/>'; 
	  echo $this->course->finish;
	?>
	</td>
	<td class="seminarman_cart_quantity" data-title="<?php echo JText::_('COM_SEMINARMAN_CART_QUANTITY'); ?>:"><?php echo $_POST['attendees']; ?></td>
	<td class="seminarman_cart_price_single" data-title="<?php echo JText::_('COM_SEMINARMAN_CART_PRICE_SINGLE'); ?>:" data-currency=" <?php echo $params->get('currency'); ?>"><?php echo JText::sprintf('%.2f', round(doubleval(str_replace(",", ".", $this->escape($price_orig))), 2)); ?></td>
	<td class="seminarman_cart_price_total" data-title="<?php echo JText::_('COM_SEMINARMAN_CART_PRICE_TOTAL'); ?>:" data-currency=" <?php echo $params->get('currency'); ?>"><?php echo JText::sprintf('%.2f', round(doubleval(str_replace(",", ".", $this->escape($price_total_orig))), 2)); ?></td>
	</tr>

	<?php if ($courseRows->vat <> 0) { ?>
	<tr>
	<td class="seminarman_cart_netto_total_title" colspan="5"><?php echo JText::_('COM_SEMINARMAN_CART_NETTO_TOTAL'); ?></td>
	<td class="seminarman_cart_netto_total" colspan="1" data-title="<?php echo JText::_('COM_SEMINARMAN_CART_NETTO_TOTAL'); ?>:" data-currency=" <?php echo $params->get('currency'); ?>"><?php echo JText::sprintf('%.2f', round(doubleval(str_replace(",", ".", $this->escape($price_total_orig))), 2)); ?></td>
	</tr>
	<?php } ?>
	<?php if ($price_total_discount <> 0) { ?>
	<tr>
	<td class="seminarman_cart_discount_total_title" colspan="5"><?php echo $price_diff_label; ?></td>
	<td class="seminarman_cart_discount_total" colspan="1" data-title="<?php echo $price_diff_label; ?>:" data-currency=" <?php echo $params->get('currency'); ?>"><?php echo JText::sprintf('%.2f', round(doubleval(str_replace(",", ".", $this->escape($price_total_discount))), 2)); ?></td>
	</tr>
	<?php } ?>
	<?php if ($courseRows->vat <> 0) { ?>
	<tr>
	<td class="seminarman_cart_withoutVat_total_title" colspan="5"><?php echo JText::sprintf('COM_SEMINARMAN_CART_WITHOUT_VAT', JText::sprintf('%.0f', round(doubleval(str_replace(",", ".", $this->escape($courseRows->vat))), 2)) . '%'); ?></td>
	<td class="seminarman_cart_withoutVat_total" colspan="1" data-title="<?php echo JText::sprintf('COM_SEMINARMAN_CART_WITHOUT_VAT', JText::sprintf('%.0f', round(doubleval(str_replace(",", ".", $this->escape($courseRows->vat))), 2)) . '%'); ?>:" data-currency=" <?php echo $params->get('currency'); ?>"><?php echo JText::sprintf('%.2f', round(doubleval(str_replace(",", ".", $this->escape($tax_booking))), 2)); ?></td>
	</tr>
	<?php } ?>
	<tr>
	<td class="seminarman_cart_booking_total_title" colspan="5"><?php echo JText::_('COM_SEMINARMAN_CART_BOOKING_TOTAL'); ?></td>
	<td class="seminarman_cart_booking_total" colspan="1" data-title="<?php echo JText::_('COM_SEMINARMAN_CART_BOOKING_TOTAL'); ?>:" data-currency=" <?php echo $params->get('currency'); ?>"><?php echo JText::sprintf('%.2f', round(doubleval(str_replace(",", ".", $this->escape($price_total_booking_with_tax))), 2)); ?></td>
	</tr>
	<?php } ?>

<?php 
	$dispatcher=JDispatcher::getInstance();
	JPluginHelper::importPlugin('seminarman');
	$html_tmpl=$dispatcher->trigger('onGetAddPriceForCart',array($courseRows));  // we need the course id
	if(isset($html_tmpl) && !empty($html_tmpl)) echo $html_tmpl[0];
?>
<?php if(!empty($payment_fee)): ?>	
<?php switch ($this->params->get('payment_overview_layout')){
	case 1: // wenige Spalten ?>
	<tr>
	<td colspan="4"></td>
	</tr>
	<tr>
	<td class="seminarman_cart_payment_fee_title" colspan="3"><?php echo JText::_('COM_SEMINARMAN_PAYMENT_FEE'); ?></td>
	<td class="seminarman_cart_payment_fee" data-title="<?php echo JText::_('COM_SEMINARMAN_PAYMENT_FEE'); ?>:" data-currency=" <?php echo $params->get('currency'); ?>"><?php echo $payment_fee_display; ?></td>
	</tr>
	<tr>
	<td class="seminarman_cart_payment_total_title" colspan="3"><?php echo JText::_('COM_SEMINARMAN_PAYMENT_TOTAL'); ?></td>
	<td class="seminarman_cart_payment_total" data-title="<?php echo JText::_('COM_SEMINARMAN_PAYMENT_TOTAL'); ?>:" data-currency=" <?php echo $params->get('currency'); ?>"><?php echo $total_payment_display; ?></td>
	</tr>
	<?php
			break;
		default:  // 2 more columns
	?>
	<tr>
	<td colspan="6"></td>
	</tr>
	<tr>
	<td class="seminarman_cart_payment_fee_title" colspan="5"><?php echo JText::_('COM_SEMINARMAN_PAYMENT_FEE'); ?></td>
	<td class="seminarman_cart_payment_fee" colspan="1" data-title="<?php echo JText::_('COM_SEMINARMAN_PAYMENT_FEE'); ?>:" data-currency=" <?php echo $params->get('currency'); ?>"><?php echo $payment_fee_display; ?></td>
	</tr>
	<tr>
	<td class="seminarman_cart_payment_total_title" colspan="5"><?php echo JText::_('COM_SEMINARMAN_PAYMENT_TOTAL'); ?></td>
	<td class="seminarman_cart_payment_total" colspan="1" data-title="<?php echo JText::_('COM_SEMINARMAN_PAYMENT_TOTAL'); ?>:" data-currency=" <?php echo $params->get('currency'); ?>"><?php echo $total_payment_display; ?></td>
	</tr>	
	<?php } ?>
<?php endif; ?>	
	</tbody>
	</table>