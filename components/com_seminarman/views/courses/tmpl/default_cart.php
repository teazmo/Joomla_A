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
$query = $db->getQuery(true);
$query->select( '*' );
$query->from( '#__seminarman_courses' );
$query->where( 'id ='. $_POST['course_id'] );
$db->setQuery( $query );

//if ( !$db->execute() ) {
//	JError::raiseError(500, $db->stderr(true));
//	return;
//}
$courseRows = $db->loadObject();
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

$display_payment_selection = false;
if ($params->get('enable_payment_selection')) {
	if (($params->get('enable_paypal') > 0) || ($params->get('enable_bank_transfer'))) {
		if ($price_total_booking > 0) {
			$display_payment_selection = true;
		} else {  // Auch wenn der Buchungspreis 0 beträgt, könnte es zusätzliche Gebühren über Plugin geben
			if ((isset($_POST['params']['fee1_selected']) && $_POST['params']['fee1_selected'] > 0) && (isset($_POST['params']['fee1_value']) && $_POST['params']['fee1_value'] > 0))
				$display_payment_selection = true;
		}
	}
}

if (($price_total_booking > 0) || ((isset($_POST['params']['fee1_selected']) && $_POST['params']['fee1_selected'] > 0) && (isset($_POST['params']['fee1_value']) && $_POST['params']['fee1_value'] > 0))) {
	$has_cost = 1;
} else {
	$has_cost = 0;
}

$waitinglist = 0;
if ( isset( $_POST['waitinglist'] ) && $_POST['waitinglist'] == 1 ) {
	$waitinglist = 1;
	$display_payment_selection = false;
}
	

// custom fields
$js = '';
if (!$display_payment_selection):
foreach ($this->fields as $this->fieldGroup){
	foreach ($this->fieldGroup as $f) {
		$f = JArrayHelper::toObject ($f);
		$f->value = $this->escape($f->value);

		if ($f->published == 1 && $f->required == 1 && $f->type == "checkboxtos") {
			$js .= '
		fields = document.getElementById("field' . $f->id . '")
		if(fields.getAttribute("aria-invalid") == "true" || !fields.checked) {
			if(fields.className.indexOf("invalid") < 0) {
				fields.className += " invalid";
			}
			return alert("' . JText::sprintf('COM_SEMINARMAN_ACCEPT_TOS', $f->name) . '");
		}';
		}
	}
}
endif;
?>

<script type="text/javascript">
function submitbuttonSeminarman(task)
{
	var form = document.adminForm;
	var fields;

	if (task == "cancel") {
		Joomla.submitform( task );
	} else {
		<?php echo $js;?>
		var payment_sel =  document.getElementsByName('payment_method[]');
		if (typeof(payment_sel) != 'undefined' && payment_sel != null && payment_sel.length > 0) {
			var payment_selected = false;
		    for (var i = 0, len = payment_sel.length; i < len; i++) {
		          if (payment_sel[i].checked) {
		        	  payment_selected = true;
		          }
		    }
		    if (!payment_selected) return alert("<?php echo JText::_('COM_SEMINARMAN_SELECT_PAYMENT_PLEASE'); ?>");			
		}
		if (document.formvalidator.isValid(form)) {
			if(document.adminForm.submitSeminarman) {
				document.adminForm.submitSeminarman.disabled = true;
			}
			Joomla.submitform( task );
		} else {	
			return alert("<?php echo JText::_('COM_SEMINARMAN_VALUES_NOT_ACCEPTABLE'); ?>");
		}
	}
};
</script>

<div id="seminarman" class="seminarman">
<h2><?php echo JText::_('COM_SEMINARMAN_CART_CONFIRM'); ?></h2>
<br />
    <table class="seminarman_cart">
    <tr><td colspan="2"><h3 class="underline"><?php echo JText::_('COM_SEMINARMAN_CART_REG_DATA');?></h3></td></tr>
    <tr><td class="paramlist_key vtop">&nbsp;</td>
        <td class="paramlist_value vtop"><?php echo $_POST['salutation'] . ' ' . $_POST['first_name'] . ' ' . $_POST['last_name']; ?></td></tr>
    <tr><td class="paramlist_key vtop"><label for="jformemail"><?php echo JText::_('COM_SEMINARMAN_EMAIL'); ?>:</label></td>
        <td class="paramlist_value vtop"><?php echo $_POST['email']; ?></td></tr>
    <tr><td colspan="2">&nbsp;<br>&nbsp;</td></tr>      
    <?php
    // custom fields
    foreach ($this->fields as $name => $this->fieldGroup){
    if ($name != 'ungrouped'){?>
    <tr><td colspan="2"><h3 class="underline"><?php echo JText::_($name); ?></h3></td></tr>
    <?php
    }

            foreach ($this->fieldGroup as $f){
            $f = JArrayHelper::toObject ($f);
            $f->value = $this->escape($f->value);

            ?>
            <tr>
                <td class="paramlist_key vtop" id="lblfield<?php echo $f->id;?>"><label for="lblfield<?php echo $f->id;?>"><?php if ($f->type != "checkboxtos") echo JText::_($f->name) . ':'; ?></label></td>
                <td class="paramlist_value vtop"><?php
                    $var = 'field' . $f->id;
                    if ($f->type != "checkboxtos") {
                    	if (($f->type == "checkbox") || ($f->type == "list") || ($f->type == "time") || ($f->type == "url")) {
                    		if (isset($_POST[$var])) {
                    		    foreach ($_POST[$var] as $i => $f_item) {
                    		        echo $f_item;
                    		        if ($i < 1) {
	                    		        switch ($f->type) {
											case "time":
												echo ":";
												break;
											case "url":
												break;
											default:
												echo "<br />";
										}	
									}
                    		    }
                    		}
                    	// } elseif ($f->type == "date") {
                    	//	$str_datum = "";
                    	//	foreach ($_POST[$var] as $f_item) {
                    	//		$str_datum = $str_datum . "." . $f_item;    
                    	//    }
                    	//    $str_datum = substr($str_datum, 1);
                    	//    echo $str_datum;
                    	} else {
                    	    if (isset($_POST[$var])) echo $_POST[$var]; 
                    	}
                    }
                  ?></td>
            </tr>
            <?php
            }

            ?>
    <tr><td colspan="2">&nbsp;<br>&nbsp;</td></tr>
    <?php
    }

    ?>
    </table>
<?php
  if (!($display_payment_selection)) { // load the payment overview here only if the payment selection is not active
    echo '<br /><br />'.$this->loadTemplate('payment_overview').'<br />';
  }
?>
	<div class="centered">
	<form action="#" method="post" name="adminForm" id="adminForm" class="form-validate cart_submit"  enctype="multipart/form-data">

<?php 
    if ($display_payment_selection):
?>
	<div class="sman_payment_selection">
         <div class="qf_fleft"><label><?php echo JText::_('COM_SEMINARMAN_PAYMENT_METHOD'); ?></label><span class="required">*</span></div>
         <fieldset id="payment_method" class="radio qf_fleft">
            <?php if ($params->get('enable_bank_transfer') > 0): ?>
            <?php 
                $param_bank_fee_value = $params->get('bank_fee_value');
                if(!empty($param_bank_fee_value)) {
                  $bank_fee_value = round(doubleval(str_replace(",", ".", $param_bank_fee_value)), 2);
                  $bank_fee_value_display = JText::sprintf('%.2f', $bank_fee_value);
                  if($bank_fee_value > 0) {
              	    $bank_fee = " (+". $bank_fee_value_display;
                  } else {
                  	$bank_fee = " (". $bank_fee_value_display;
                  }
                  if($params->get('bank_fee_type') == 1) {
                  	$bank_fee .= "%)";
                  } else {
                  	$bank_fee .= " ".$params->get('currency').")";
                  }
                } else {
                	$bank_fee = "";
                }
            ?>
            <input id="payment_method1" type="radio" name="payment_method[]" value="1">
            <label for="jformpayment_method1"><?php echo JText::_('COM_SEMINARMAN_PAYMENT_BANK_TRANSFER').$bank_fee; ?></label>
            <br />
            <?php endif; ?>
            <?php if ($params->get('enable_paypal') > 0): ?>
            <?php 
                $param_paypal_fee_value = $params->get('paypal_fee_value');
                if(!empty($param_paypal_fee_value)) {
                  $paypal_fee_value = round(doubleval(str_replace(",", ".", $param_paypal_fee_value)), 2);
                  $paypal_fee_value_display = JText::sprintf('%.2f', $paypal_fee_value);
                  if($paypal_fee_value > 0) {
              	    $paypal_fee = " (+". $paypal_fee_value_display;
                  } else {
                  	$paypal_fee = " (". $paypal_fee_value_display;
                  }
                  if($params->get('paypal_fee_type') == 1) {
                  	$paypal_fee .= "%)";
                  } else {
                  	$paypal_fee .= " ".$params->get('currency').")";
                  }
                } else {
                	$paypal_fee = "";
                }
            ?>
            <input id="payment_method2" type="radio" style="clear: left;" name="payment_method[]" value="2">
            <label for="jformpayment_method2"><?php echo JText::_('COM_SEMINARMAN_PAYPAL').$paypal_fee; ?></label>
            <br />
            <?php endif; ?>
         </fieldset>	    
	</div>
<?php endif; ?>

    <div class="clear"></div>
	<?php
	    if (!$display_payment_selection):  // show "accept tos" here only if payment selection is not active
	    foreach ($this->fields as $name => $this->fieldGroup){
	    	foreach ($this->fieldGroup as $f){
	    		$f = JArrayHelper::toObject ($f);
	    		$f->value = $this->escape($f->value);
	    		// $var = 'field' . $f->id;
	    		// echo '<input type="hidden" name="' . $var .'" value="' . $_POST[$var] . '" />';
	    		if ($f->type == "checkboxtos") {
	    			$tos = $f->options->{"0"};
	    			// echo '<input type="checkbox" id="cm_tos" /> ' . $tos . '<br /><br />';
	    			echo '<div style="text-align: left; overflow: hidden;">'. SeminarmanCustomfieldsLibrary::getFieldHTML($f , '') . '</div>';
	    		}
	    	}
	    }
	    endif;
	?>
	<button type="button" class="btn btn-primary cancel" onclick="submitbuttonSeminarman('cancel')">
	<?php echo JText::_('COM_SEMINARMAN_CART_CANCEL_BUTTON');?>
	</button>
	<?php 
	    if ($display_payment_selection) {
	    	$action = "checkout";
	    } else {
	    	$action = "save";
	    }
	?>
	<button id="submitSeminarman" type="button" class="btn btn-primary validate" onclick="submitbuttonSeminarman('<?php echo $action; ?>')">
	<?php 
	   if ($action == "save") { 
	   		if ( $waitinglist == 1 ) {
	   			echo JText::_('COM_SEMINARMAN_WL_CONFIRM_BUTTON');
	   		}
	   		else if ($has_cost) {
	    	    echo JText::_('COM_SEMINARMAN_CART_CONFIRM_BUTTON');
	   		} 
	   		else {
	   			echo JText::_('COM_SEMINARMAN_CART_FREE_CONFIRM_BUTTON');
	   		}
	   	} 
	   	else {
	   		echo JText::_('COM_SEMINARMAN_TO_CHECKOUT');
	   	}
	?>
	</button>
	    <input type="hidden" name="course_id" value="<?php echo $_POST['course_id']; ?>" />
	    <input type="hidden" name="email" value="<?php echo $_POST['email']; ?>" />
	    <?php if (isset($_POST['booking_email_cc'])): ?>
	    <input type="hidden" name="booking_email_cc" value="<?php echo $_POST['booking_email_cc']; ?>" />
	    <?php endif; ?>
	    <input type="hidden" name="attendees" value="<?php echo $_POST['attendees']; ?>" />
	    <input type="hidden" name="salutation" value="<?php echo $_POST['salutation']; ?>" />
	    <input type="hidden" name="title" value="<?php echo $_POST['title']; ?>" />
	    <input type="hidden" name="first_name" value="<?php echo $_POST['first_name']; ?>" />
	    <input type="hidden" name="last_name" value="<?php echo $_POST['last_name']; ?>" />
	    <input type="hidden" name="booking_price[]" value="<?php echo $_POST['booking_price'][0]; ?>" />
	    <input type="hidden" name="status" value="<?php echo $_POST['status']; ?>" />
	    <?php 
	    foreach ($this->fields as $name => $this->fieldGroup){
	    	foreach ($this->fieldGroup as $f){
	    		$f = JArrayHelper::toObject ($f);
	    		$f->value = $this->escape($f->value);
	    		$var = 'field' . $f->id;
				if (($f->type == "checkbox") || ($f->type == "list") || ($f->type == "time") || ($f->type == "url")) {
					if (isset($_POST[$var])) {
						foreach ($_POST[$var] as $f_item) {
							echo '<input type="hidden" name="' . $var .'[]" value="' . $f_item . '" />';
						}
					}
	    		} elseif ($f->type != "checkboxtos") {
	    			if (isset($_POST[$var])) {
	    		        echo '<input type="hidden" name="' . $var .'" value="' . $_POST[$var] . '" />';
	    			}
	    		}
	    	}
	    }
	    ?>
	    
<?php 
	$dispatcher=JDispatcher::getInstance();
	JPluginHelper::importPlugin('seminarman');
	$html_tmpl=$dispatcher->trigger('onPostAddPriceForCart',array($courseRows));  // we need the course id
	if(isset($html_tmpl) && !empty($html_tmpl)) echo $html_tmpl[0];
?>	    
	    
	    <input type="hidden" name="has_cost" value="<?php echo $has_cost; ?>" />
	    <input type="hidden" name="option" value="com_seminarman" />
	    <input type="hidden" name="controller" value="application" />
	    <input type="hidden" name="task" value="" />
	<?php
	    echo JHTML::_('form.token');
	?>
	</form>
	</div>
</div>