<?php
/**
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011 Open Source Group GmbH www.osg-gmbh.de
* @website http://www.profinvent.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.tooltip');

// Load the modal behavior script.
JHTML::_('behavior.modal', 'a.modal');

jimport('joomla.html.pane');
$params = JComponentHelper::getParams('com_seminarman');
if(class_exists ('JPane'))
	$pane	= JPane::getInstance('Tabs');
else
	$pane	= JPaneOSG::getInstance('Tabs');

// Joomla Version specific adjustments
if(JVERSION < 3.0) {
	$mail_icon = 'send';
} else {
	$mail_icon = 'envelope';
}

$edit = JRequest::getVar('edit', true);
$text = !$edit ? JText::_('New') : JText::_('COM_SEMINARMAN_EDIT');

if (SeminarmanFunctions::isSmanbookingPlgEnabled() && $params->get('advanced_booking')) {
	$headline = JText::_('COM_SEMINARMAN_APPLICATION') . ' ('.JText::_('COM_SEMINARMAN_ENHANCED').')';
} else {
	$headline = JText::_('COM_SEMINARMAN_APPLICATION');
}

JToolBarHelper::title($headline . ': <span class="small">[ ' . $text . ' ]</span>', 'applications');
        $alt = JText::_('COM_SEMINARMAN_NOTIFY');
        $bar = JToolBar::getInstance( 'toolbar' );
        $bar->appendButton( 'Standard', $mail_icon, $alt, 'notify_booking', false );
JToolBarHelper::apply();
JToolBarHelper::save();
if (!$edit)
{
    JToolBarHelper::cancel();
} else
{

    JToolBarHelper::cancel('cancel', 'COM_SEMINARMAN_CLOSE');
}

$disable_booking_details = true;

?>

<style type="text/css">
select {
    margin-bottom: 0 !important;
}
</style>

<script type="text/javascript">
	Joomla.submitbutton = function(task, course) {
		var form = document.adminForm;
		if (task == 'cancel') {
			submitform( task );
			return;
		} 
		<?php 
		if (SeminarmanFunctions::isSmanbookingPlgEnabled() && $params->get('advanced_booking')) {
			$dispatcher=JDispatcher::getInstance();
			JPluginHelper::importPlugin('seminarman');
			$html_tmpl=$dispatcher->trigger('onSubmitManualBookingSetCourseJS',array($this->application));
			if (isset($html_tmpl) && !empty($html_tmpl)) echo $html_tmpl[0];
		}		
		?>

		// do field validation
		if (form.first_name.value == ""){
			alert( "<?php echo JText::_('COM_SEMINARMAN_MISSING_FIRST_NAME', true); ?>" );
		} else if(form.last_name.value == '') {
			alert( "<?php echo JText::_('COM_SEMINARMAN_MISSING_LAST_NAME', true); ?>" );
		}
		 else {
			submitform( task );
		}
	}

	function setStatus() {
		
		var status = document.getElementById( 'status' ).selectedIndex;
		var oldstatus = document.getElementById( 'oldstatus' ).value;
		var hasInvoice = document.getElementById( 'hasInvoice' ).value;
		
		var rechnung = false;

		// nur bei status "warteliste" oder "noch nicht bestätigt"
		// zu status "eingegangen" oder "wird bearbeitet" oder "bezahlt"
		if ( ( oldstatus == 4 || oldstatus == 5 ) && ( status < 3 ) ) {
			// wenn in den optionen eingestellt ist, dass keine Rechnung erstellt werden soll, braucht man auch die Abfrage nicht
			if ( <?php echo $params->get('invoice_generate') ?> == 1 ) {
				// Die Frage ist etwas anders wenn die Rechnung auch verschickt werden soll (ebenfalls eine Option im Backend)
				if ( <?php echo $params->get('invoice_attach') ?> == 1 ) {
					if ( hasInvoice == 1 ) {
						rechnung = confirm( "<?php echo JText::_('COM_SEMINARMAN_SEND_INVOICE_AFTER_WAITLIST_AGAIN').' '.JText::_('COM_SEMINARMAN_SENDING_INVOICE_AFTER_SAVE') ?>" );
					}
					else {
						rechnung = confirm( "<?php echo JText::_('COM_SEMINARMAN_SEND_INVOICE_AFTER_WAITLIST').' '.JText::_('COM_SEMINARMAN_SENDING_INVOICE_AFTER_SAVE') ?>" );
					}
				}
				else {
					if ( hasInvoice == 1 ) {
						rechnung = confirm( "<?php echo JText::_('COM_SEMINARMAN_SEND_INVOICE_AFTER_WAITLIST_AGAIN_NO_SENDMAIL').' '.JText::_('COM_SEMINARMAN_SENDING_INVOICE_AFTER_SAVE') ?>" );
					}
					else {
						rechnung = confirm( "<?php echo JText::_('COM_SEMINARMAN_SEND_INVOICE_AFTER_WAITLIST_NO_SENDMAIL').' '.JText::_('COM_SEMINARMAN_SENDING_INVOICE_AFTER_SAVE') ?>" );
					}
				}
			}
			if ( rechnung ) {
				document.getElementById( 'sendInvoice' ).value = 1;
			}
			else {
				document.getElementById( 'sendInvoice' ).value = 0;
			}
		}
	}
</script>

<?php 
	if ((!$this->isNew)&&(SeminarmanFunctions::isSmanbookingPlgEnabled() && $params->get('advanced_booking'))) {
		$dispatcher=JDispatcher::getInstance();
		JPluginHelper::importPlugin('seminarman');
		$manual_obj = new stdClass;
		$manual_obj->app = $this->application;
		$manual_obj->course = $this->course;
		$html_tmpl=$dispatcher->trigger('onAddManualBookingComPricesJS',array($manual_obj));
		if (isset($html_tmpl) && !empty($html_tmpl)) echo $html_tmpl[0];
	}		
?>

<?php 
	if (($this->isNew)&&(SeminarmanFunctions::isSmanbookingPlgEnabled() && $params->get('advanced_booking'))) {
		$dispatcher=JDispatcher::getInstance();
		JPluginHelper::importPlugin('seminarman');
		$manual_obj = new stdClass;
		$manual_obj->app = $this->application;
		$manual_obj->course = $this->course;
		$html_tmpl=$dispatcher->trigger('onAddManualBookingJSForNewApp',array($manual_obj));
		if (isset($html_tmpl) && !empty($html_tmpl)) echo $html_tmpl[0];
	}		
?>

<style type="text/css">
	table.paramlist td.paramlist_key {
		width: 92px;
		text-align: left;
		height: 30px;
	}
</style>

<?php 
    $app_params_obj = new JRegistry();
    $app_params_obj->loadString($this->application->params);
    $app_params = $app_params_obj->toArray();
    
    $extra_fee_open = false;
    if (isset($app_params['fee1_selected']) && ($app_params['fee1_selected']==1)) {
    	if (isset($app_params['fee1_value']) && ($app_params['fee1_value']>0)) {
    		$extra_fee_open = true;
    	}
    }
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<?php
echo $pane->startPane( 'customfields-fields' );
echo $pane->startPanel( JText::_('COM_SEMINARMAN_APPLICATION') , 'details-page' );
?>
	<table class="paramlist admintable" style="width: 100%;">
	<tbody>
	<tr>
	<td>
	
	<div class="row-fluid form-horizontal-desktop">
	<div class="span6">

<div class="well">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_SEMINARMAN_DETAILS'); ?></legend>
		<table class="admintable">
			<tr>
				<td><label for="status"><?php echo JText::_('COM_SEMINARMAN_STATUS'); ?>:</label></td>
				<td><fieldset id="jform_type" class="radio inputbox">
				<?php echo $this->lists['status']; ?>
				</fieldset>
				<?php if (!$this->isNew):?>
				<input type='hidden' id='oldstatus' name='oldstatus' value='<?php echo $this->application->status ?>'>
				<input type='hidden' id='hasInvoice' name='hasInvoice' value='<?php if (!empty($this->application->invoice_filename_prefix) ) {
					echo '1';
				}
				else {
					echo '0';
				} ?>'>
				<input type='hidden' id='sendInvoice' name='invoice' value='0'>
				<?php endif; ?>
				</td>
			</tr>
<?php if ($params->get('enable_paypal') == 1): ?>
			<tr>
				<td><label><?php echo JText::_('COM_SEMINARMAN_PAYPAL_TXNID'); ?>:</label></td>
				<td><label><?php if (empty($this->application->transaction_id)) echo '-'; else echo $this->application->transaction_id; ?></label></td>
			</tr>
<?php endif; ?>
		</table>
	</fieldset>
</div>

<div class="well">
	<fieldset class="adminform">
	<legend><?php echo JText::_('COM_SEMINARMAN_ACCOUNT_DETAILS'); ?></legend>
	<table class="admintable">
		<tr>
			<td><label for="user_id"><?php echo JText::_('COM_SEMINARMAN_USER_NAME'); ?>:</label></td>
			<td ><?php echo $this->lists['username']; ?></td>
		</tr>
		<tr>
			<td class="key"><label for="salutation"><?php echo JText::_('COM_SEMINARMAN_SALUTATION'); ?>:</label></td>
			<td><?php echo $this->lists['salutation']; ?></td>
		</tr>
		<tr>
			<td class="key"><label for="title"><?php echo JText::_('COM_SEMINARMAN_TITLE'); ?>:</label></td>
			<td><input class="text_area" type="text" name="title" id="title" size="32" maxlength="100" value="<?php echo ($this->isNew)?$this->escape($this->attendeedata->title):$this->escape($this->application->title); ?>" /></td>
			
		</tr>  
		<tr>
			<td><label for="first_name"><?php echo JText::_('COM_SEMINARMAN_FIRST_NAME'); ?>:</label></td>
		<td><input class="text_area" type="text" name="first_name" id="first_name" size="32" maxlength="100" value="<?php echo ($this->isNew)?$this->escape($this->attendeedata->first_name):$this->escape($this->application->first_name); ?>" /></td>
		</tr>
		<tr>
			<td><label for="last_name"><?php echo JText::_('COM_SEMINARMAN_LAST_NAME'); ?>:</label></td>
			<td><input class="text_area" type="text" name="last_name" id="last_name" size="32" maxlength="100" value="<?php echo ($this->isNew)?$this->escape($this->attendeedata->last_name):$this->escape($this->application->last_name); ?>" /></td>
		</tr>
        <tr>
			<td><label for="email"><?php echo JText::_('COM_SEMINARMAN_EMAIL'); ?>:</label></td>
			<td><input class="text_area" type="text" name="email" id="email" size="32" maxlength="100" value="<?php echo ($this->isNew)?$this->escape($this->attendeedata->email):$this->escape($this->application->email); ?>" /></td>
		</tr>
        <tr>
			<td><label for="booking_email_cc"><?php echo JText::_('COM_SEMINARMAN_BOOKING_EMAIL_CC_LBL'); ?>:</label></td>
			<td><input class="text_area" type="text" name="params[booking_email_cc]" id="booking_email_cc" size="32" maxlength="100" value="<?php echo (isset($app_params['booking_email_cc']))?$this->escape($app_params['booking_email_cc']):''; ?>" /></td>
		</tr>		
	</table>
	</fieldset>
</div>

<div class="well">
	<fieldset class="adminform">
	<legend><?php echo JText::_('COM_SEMINARMAN_COURSE_DETAILS'); ?></legend>
	<table class="admintable">
		<tr>
			<td><label for="course_id"><?php echo JText::_('COM_SEMINARMAN_ID'); ?>:</label></td>
			<td><input class="text_area" type="text" name="course_id" id="course_id" size="32" maxlength="100" value="<?php echo $this->escape($this->application->course_id); ?>" disabled="disabled" />
			<input type="hidden" name="course_id" id="course_id" value="<?php echo $this->escape($this->application->course_id); ?>" /></td>
		</tr>
        <tr>
			<td><label for="reference_number"><?php echo JText::_('COM_SEMINARMAN_COURSE_CODE'); ?>: </label></td>
			<td><input class="text_area" type="text" name="course_code" id="course_code" size="32" maxlength="100" value="<?php echo $this->escape($this->application->code); ?>" disabled="disabled" /></td>
		</tr>
        <tr>
			<td><label for="course_title"><?php echo JText::_('COM_SEMINARMAN_COURSE_TITLE'); ?>:</label></td>
			<td><input class="text_area" type="text" name="course_title" id="course_title" size="32" maxlength="100" value="<?php echo $this->escape($this->application->course_title); ?>" disabled="disabled" />
			<?php
			if (!($this->isNew)&&(SeminarmanFunctions::isSmanbookingPlgEnabled() && $params->get('advanced_booking'))) {
			    $dispatcher=JDispatcher::getInstance();
			    JPluginHelper::importPlugin('seminarman');
			    $html_tmpl=$dispatcher->trigger('onAddManualBookingCourseBtns',array($this->application));
			    if (isset($html_tmpl) && !empty($html_tmpl)) echo $html_tmpl[0];
			}
			?>
			    <?php 
			        if(JVERSION < 3.0) {
			        	$course_btn = '<input type="button" class="hasTip" title="' . JText::_('COM_SEMINARMAN_EDIT_COURSE') . '"  value="' . JText::_('JACTION_EDIT') . '" onclick="window.open(\'' . JRoute::_('index.php?option=com_seminarman&controller=courses&task=edit&cid[]=' . $this->escape($this->application->course_id)) . '\')" />';
			        } else {
			        	$course_btn = '<a class="btn hasTooltip" href="' . JRoute::_('index.php?option=com_seminarman&controller=courses&task=edit&cid[]=' . $this->escape($this->application->course_id)) . '" target="_blank" title="' . JText::_('COM_SEMINARMAN_EDIT_COURSE') . '" ><span class="icon-edit"></span>' . JText::_('JACTION_EDIT') . '</a>';
			        }
			        echo $course_btn;
				?>
			</td>
		</tr>
        <tr>
			<td><label for="course_price"><?php echo JText::_('COM_SEMINARMAN_PRICE') .' ('. JText::_('COM_SEMINARMAN_NET') .')'; ?>:</label></td>
			<td><input class="text_area" type="text" name="course_price" id="course_price" size="32" maxlength="100" value="<?php echo $this->escape($this->course->price) . " " . $this->escape($this->course->currency_price) . " " . $this->escape($this->course->price_type); ?>" disabled="disabled" /></td>
		</tr>
		<tr>
			<td><label for="course_price_vat"><?php echo JText::_('COM_SEMINARMAN_VAT'); ?>:</label></td>
			<td><input class="text_area" type="text" name="course_price_vat" id="course_price_vat" size="32" maxlength="100" value="<?php echo $this->escape($this->course->price_vat); ?>%" disabled="disabled" /></td>
		</tr>
	</table>
	</fieldset>
</div> 
	
	</div>
	<div class="span6">
	
<?php 
    // display invoice block or not?
    if ($this->isNew) {
    	$invoice_well_display = false;
    	$disable_booking_details = false;
    } else {
	    if (!empty($this->application->invoice_filename_prefix)) {
	    	// if invoice already available, the invoice block is always displayed
	    	$invoice_well_display = true;
	    } elseif ((SeminarmanFunctions::isSmanbookingPlgEnabled() && $params->get('advanced_booking')) && (($this->application->price_per_attendee > 0)||$extra_fee_open)) {
	    	// if manual booking plugin is enabled and fee open
	    	$invoice_well_display = true;
	    	$disable_booking_details = false;
	    } elseif ((SeminarmanFunctions::isSmanbookingPlgEnabled() && $params->get('advanced_booking')) && (!($this->application->price_per_attendee > 0)&&!$extra_fee_open)) {
	    	// if manual booking plugin is enabled but no fee open
	    	$invoice_well_display = false;
	    	$disable_booking_details = false;    	
	    } else {
	    	$invoice_well_display = false;
	    }
    }
?>

<?php if ($invoice_well_display): ?>
<div class="well">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_SEMINARMAN_INVOICE'); ?></legend>
		<div class="centered">
		    <?php if (!empty($this->application->invoice_filename_prefix)): ?>
			<a href="<?php echo JRoute::_('index.php?option=com_seminarman&view=application&layout=invoicepdf&cid[]='. $this->escape($this->application->id)); ?>">
				<img style="display: block; margin-left: auto; margin-right: auto; float: none;" src="components/com_seminarman/assets/images/icon-48-pdf.png" alt="icon_pdf" />
				<?php echo $this->escape($this->application->invoice_filename_prefix) . $this->escape($this->application->invoice_number) . '.pdf'; ?>
			</a>
			<?php
			else:
			if (SeminarmanFunctions::isSmanbookingPlgEnabled() && $params->get('advanced_booking')) {
				$dispatcher=JDispatcher::getInstance();
				JPluginHelper::importPlugin('seminarman');
				$html_tmpl=$dispatcher->trigger('onAddManualBookingInvoiceBtn',array($this->application));
				if (isset($html_tmpl) && !empty($html_tmpl)) echo $html_tmpl[0];
			}
			endif;
			?>
		</div>
	</fieldset>
</div>
<?php endif; ?>

<div class="well">
 
    <?php if (!$this->isNew): ?>
	<fieldset class="adminform">
	<legend><?php echo JText::_('COM_SEMINARMAN_BOOKING_DETAILS'); ?></legend>
	<table class="admintable">
		<tr>
			<td><label for="attendees"><?php echo JText::_('COM_SEMINARMAN_NUMBER_OF_ATTENDEES'); ?>:</label></td>
			<?php if($disable_booking_details) : ?>
			<td><input class="text_area" type="text" name="attendees_disabled" id="attendees_disabled" size="4" maxlength="3" value="<?php echo $this->escape($this->application->attendees); ?>" disabled="disabled" />
			<input type="hidden" name="attendees" id="attendees" value="<?php echo $this->escape($this->application->attendees); ?>" /></td>
			<?php else : ?>
			<td><input class="text_area" type="text" name="attendees" id="attendees" size="4" maxlength="3" value="<?php echo $this->escape($this->application->attendees); ?>" onchange="computeBookingPrice();" onmouseout="computeBookingPrice();" /></td>
			<?php endif; ?>
		</tr>
        <tr>
			<td><label for="price_group"><?php echo JText::_('COM_SEMINARMAN_PRICE_GROUP'); ?>:</label></td>
			<td><input class="text_area" type="text" name="price_group" id="price_group" size="32" maxlength="100" value="<?php echo (!empty($this->application->pricegroup) ? $this->escape($this->application->pricegroup) : JText::_('COM_SEMINARMAN_PRICE_STANDARD')); ?>" disabled="disabled" /></td>
		</tr>
		<?php 
		if ((!$disable_booking_details) && SeminarmanFunctions::isSmanbookingPlgEnabled() && $params->get('advanced_booking')) {
			$dispatcher=JDispatcher::getInstance();
			JPluginHelper::importPlugin('seminarman');
			$html_tmpl=$dispatcher->trigger('onAddManualBookingSelectPriceG',array($this->course));
			if (isset($html_tmpl) && !empty($html_tmpl)) echo $html_tmpl[0];
		}
		?>
        <tr>
			<td><label for="price_per_attendee_disabled"><?php echo JText::_('COM_SEMINARMAN_PRICE_BOOKING') .' ('. JText::_('COM_SEMINARMAN_NET') .')'; ?>:</label></td>
			<td><input class="text_area" type="text" name="price_per_attendee_disabled" id="price_per_attendee_disabled" size="32" maxlength="100" value="<?php echo $this->escape($this->application->price_per_attendee) . " " . $this->escape($this->application->currency_price) . " " . $this->escape($this->application->price_type); ?>" disabled="disabled" />
			<input type="hidden" name="price_per_attendee" id="price_per_attendee" value="<?php echo $this->escape($this->application->price_per_attendee); ?>" /></td>
		</tr>		
        <tr>
			<td><label for="price_total_disabled"><?php echo JText::_('COM_SEMINARMAN_TOTAL_PRICE') .' ('. JText::_('COM_SEMINARMAN_NET') .')'; ?>:</label></td>
			<td><input class="text_area" type="text" name="price_total_disabled" id="price_total_disabled" size="32" maxlength="100" value="<?php echo $this->escape($this->application->price_total) . " " . $this->escape($this->application->currency_price) ." " . $this->escape($this->application->price_type); ?>" disabled="disabled" />
			<input type="hidden" name="price_total" id="price_total" value="<?php echo $this->escape($this->application->price_total); ?>" /></td>
		</tr>
		<tr>
		    <td><label for="price_vat_disabled"><?php echo JText::_('COM_SEMINARMAN_VAT'); ?>:</label></td>
		    <td><input class="text_area" type="text" name="price_vat_disabled" id="price_vat_disabled" size="32" maxlength="100" value="<?php echo $this->escape($this->application->price_vat); ?>%" disabled="disabled" />
		    <input type="hidden" name="price_vat" id="price_vat" value="<?php echo $this->escape($this->application->price_vat); ?>" /></td>
		</tr>
	</table>
	
<?php 
	$dispatcher=JDispatcher::getInstance();
	JPluginHelper::importPlugin('seminarman');
	$application_extend = $this->application;
	$application_extend->disable_edit = $disable_booking_details;
	$html_tmpl=$dispatcher->trigger('onShowAddPriceInfoInMApp',array($application_extend));  // we need the application id
	if(isset($html_tmpl) && !empty($html_tmpl)) echo $html_tmpl[0];
?>

<?php if (isset($app_params['payment_method'])): ?>
<?php 
   if ($app_params['payment_method'] == 1) {
   	 $payment_method_lbl = JText::_('COM_SEMINARMAN_BANK_TRANSFER');
   } elseif ($app_params['payment_method'] == 2) {
     $payment_method_lbl = JText::_('COM_SEMINARMAN_PAYPAL');
   }
   $payment_fee = doubleval(str_replace(",", ".", $app_params['payment_fee']));
   $payment_fee_lbl = JText::sprintf('%.2f', $payment_fee) . ' ' . $this->application->currency_price;
?>
<h3><?php echo JText::_('COM_SEMINARMAN_PAYMENT'); ?></h3>
<table class="admintable">
        <tbody>
        <tr>
			<td><label><?php echo JText::_('COM_SEMINARMAN_PAYMENT_METHOD'); ?>:</label></td>
			<td><input type="text" disabled="disabled" value="<?php echo $payment_method_lbl; ?>" maxlength="100" size="32" id="params_payment_method" class="text_area"></td>
		</tr>
        <tr>
			<td><label><?php echo JText::_('COM_SEMINARMAN_PAYMENT_FEE'); ?>:</label></td>
			<td><input type="text" disabled="disabled" value="<?php echo $this->escape($payment_fee_lbl); ?>" maxlength="100" size="32" id="params_payment_fee" class="text_area"></td>
		</tr>
        </tbody>
</table>
<?php endif; ?>
	
	</fieldset>
	
	<?php else: ?>
	
	<fieldset class="adminform">
	<legend><?php echo JText::_('COM_SEMINARMAN_BOOKING_DETAILS'); ?></legend>
	<table class="admintable">
		<tr>
			<td><label for="attendees"><?php echo JText::_('COM_SEMINARMAN_NUMBER_OF_ATTENDEES'); ?>:</label></td>
			<td><input class="text_area" type="text" name="attendees" id="attendees" size="4" maxlength="3" value="1" 
					onchange="computeBookingPrice()" onmouseout="computeBookingPrice()" /></td>
		</tr>
        <tr>
			<td><label for="price_group"><?php echo JText::_('COM_SEMINARMAN_PRICE_GROUP'); ?>:</label></td>
			<td>
				<fieldset id="booking_price" class="radio" style="margin: 0 0 10px; padding: 0;">
				<?php echo JHTMLSeminarman::get_price_view( $this->application->course_id, 'onchange="computeBookingPrice()"', NULL, 1 ); ?>
				</fieldset>
			</td>
		</tr>
        <tr>
			<td><label for="price_per_attendee"><?php echo JText::_('COM_SEMINARMAN_PRICE_BOOKING') .' ('. JText::_('COM_SEMINARMAN_NET') .')'; ?>:</label></td>
			<td><input class="text_area" type="text" name="price_per_attendee_disabled" id="price_per_attendee_disabled" size="32" maxlength="100" value="<?php echo $this->escape($this->application->price) . " " . $this->escape($this->application->currency_price) . " " . $this->escape($this->application->price_type); ?>" disabled="disabled" />
			<input type="hidden" name="price_per_attendee" id="price_per_attendee" value="<?php echo $this->escape($this->application->price) . " " . $this->escape($this->application->currency_price) . " " . $this->escape($this->application->price_type); ?>" />
			</td>
		</tr>		
        <tr>
			<td><label for="price_total"><?php echo JText::_('COM_SEMINARMAN_TOTAL_PRICE') .' ('. JText::_('COM_SEMINARMAN_NET') .')'; ?>:</label></td>
			<td><input class="text_area" type="text" name="price_total_disabled" id="price_total_disabled" size="32" maxlength="100" value="<?php echo $this->escape($this->application->price) . " " . $this->escape($this->application->currency_price) ." " . $this->escape($this->application->price_type); ?>" disabled="disabled" />
			<input type="hidden" name="price_total" id="price_total" value="<?php echo $this->escape($this->application->price) . " " . $this->escape($this->application->currency_price) ." " . $this->escape($this->application->price_type); ?>" />
			</td>
		</tr>
		<tr>
		    <td><label for="price_vat_disabled"><?php echo JText::_('COM_SEMINARMAN_VAT'); ?>:</label></td>
		    <td><input class="text_area" type="text" name="price_vat_disabled" id="price_vat_disabled" size="32" maxlength="100" value="<?php echo $this->escape($this->application->price_vat); ?>%" disabled="disabled" />
		    <input type="hidden" name="price_vat" id="price_vat" value="<?php echo $this->escape($this->application->price_vat); ?>" /></td>
		</tr>
	</table>
<?php 
	$dispatcher=JDispatcher::getInstance();
	JPluginHelper::importPlugin('seminarman');
	$html_tmpl=$dispatcher->trigger('onGetAddPriceInfoForNewMBooking',array($this->application));  // we need the course id
	if(isset($html_tmpl) && !empty($html_tmpl)) echo $html_tmpl[0];
?>
	</fieldset>	
	
	<?php endif; ?>
	
</div>

<?php if (!$this->isNew): ?>

<div class="well">
	<fieldset class="adminform">
	<legend><?php echo JText::_('COM_SEMINARMAN_CERTIFICATE'); ?></legend>
	<div class="centered">
	<?php if (!empty($this->application->certificate_file)): ?>
		<a href="<?php echo JRoute::_('index.php?option=com_seminarman&view=application&layout=certificatepdf&cid[]='. $this->escape($this->application->id)); ?>">
			<img style="display: block; margin-left: auto; margin-right: auto; float: none;" src="components/com_seminarman/assets/images/icon-48-pdf.png" alt="icon_pdf" />
			<?php echo $this->application->certificate_file; ?>
		</a>
	    <div class="centered">
	    <div class="btn-group">
	    <a class="btn" title="<?php echo JText::_('COM_SEMINARMAN_REGENERATE_CERTIFICATE_TOOLTIP'); ?>" href="index.php?option=com_seminarman&controller=application&task=createCertificate&cid[]=<?php echo $this->escape($this->application->id); ?>"><?php echo JText::_('COM_SEMINARMAN_REGENERATE_CERTIFICATE'); ?></a>
	    </div>
	    </div>
	<?php else: ?>
	    <div class="centered">
	    <div class="btn-group">
	    <a class="btn" title="<?php echo JText::_('COM_SEMINARMAN_CREATE_CERTIFICATE_TOOLTIP'); ?>" href="index.php?option=com_seminarman&controller=application&task=createCertificate&cid[]=<?php echo $this->escape($this->application->id); ?>"><?php echo JText::_('COM_SEMINARMAN_CREATE_CERTIFICATE'); ?></a>
	    </div>
	    </div>
	<?php endif; ?>
	</div>	
	</fieldset>
</div>

<div class="well">
	<fieldset class="adminform">
	<legend><?php echo JText::_('COM_SEMINARMAN_ADDITIONAL_ATTACHMENT'); ?></legend>
	<div class="centered">
	<?php if (!empty($this->application->extra_attach_file)): ?>
		<a href="<?php echo JRoute::_('index.php?option=com_seminarman&view=application&layout=attachmentpdf&cid[]='. $this->escape($this->application->id)); ?>">
			<img style="display: block; margin-left: auto; margin-right: auto; float: none;" src="components/com_seminarman/assets/images/icon-48-pdf.png" alt="icon_pdf" />
			<?php echo $this->application->extra_attach_file; ?>
		</a>
	    <div class="centered">
	    <div class="btn-group">
	    <a class="btn" title="<?php echo JText::_('COM_SEMINARMAN_REGENERATE_ATTACHMENT_TOOLTIP'); ?>" href="index.php?option=com_seminarman&controller=application&task=createEmailAttachment&cid[]=<?php echo $this->escape($this->application->id); ?>"><?php echo JText::_('COM_SEMINARMAN_REGENERATE_ATTACHMENT'); ?></a>
	    </div>
	    </div>
	<?php else: ?>
	    <div class="centered">
	    <div class="btn-group">
	    <a class="btn" title="<?php echo JText::_('COM_SEMINARMAN_CREATE_ATTACHMENT_TOOLTIP'); ?>" href="index.php?option=com_seminarman&controller=application&task=createEmailAttachment&cid[]=<?php echo $this->escape($this->application->id); ?>"><?php echo JText::_('COM_SEMINARMAN_CREATE_ATTACHMENT'); ?></a>
	    </div>
	    </div>
	<?php endif; ?>
	</div>	
	</fieldset>
</div>
	
<div class="well">
	<fieldset class="adminform">
	<legend><?php echo JText::_('COM_SEMINARMAN_PROTOCOL'); ?></legend>
    <div style="max-height: 203px; overflow: auto;">
	<table class="adminlist">
	  <thead>
        <tr>
            <th><?php echo JText::_('COM_SEMINARMAN_DATE'); ?></th><th><?php echo JText::_('COM_SEMINARMAN_RESPONSIBLE_PERSON'); ?></th><th><?php echo JText::_('COM_SEMINARMAN_STATUS'); ?></th>
        </tr>
      </thead>
      <tbody>
<?php 
  if (isset($app_params['protocols'])) {
    $protocols = json_decode($app_params['protocols'], true);
    $protocols = array_reverse($protocols);
    foreach($protocols as $protocol) {
    	$stati_text = '';
    	$stati = intval($protocol['status']);
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
    	echo "<tr><td>" . JHTML::_('date', $protocol['date'], 'Y-m-d H:i:s') . "</td><td>" . $protocol['user'] . "</td><td>" . $stati_text . "</td></tr>";
    }
    echo '<tr><td colspan="3"><input type="hidden" name="params[protocols_history]" value=\'' . $app_params["protocols"] . '\' /></td></tr>';
  } 
?>      
      </tbody>
	</table>
	</div>
	</fieldset>
</div>	

<?php endif; ?>
	
	</div>
	</div>

</td>
</tr>
</tbody>
</table>

<?php echo $pane->endPanel();
// Create custom tabs and display custom fields and data
foreach( $this->user->customfields->fields as $group => $groupFields )
{
	$group = JText::_($group);
	echo $pane->startPanel( $group , $group . '-page' );
?>
	<table class="paramlist admintable" style="width: 100%;">
	<tbody>
<?php
foreach( $groupFields as $field )
{
$field	= JArrayHelper::toObject ( $field );
$field->value = $this->escape( $field->value );
//$field->options = array('yes','no');
?>
		<tr>
			<td class="paramlist_key" id="lblfield<?php echo $field->id;?>"><?php if($field->required == 1) echo '*'; ?><?php echo JText::_( $field->name );?></td>
			<td class="paramlist_value">
<?php
if ($field->type == 'checkboxtos')
{
	if ($field->value == '1') 
		echo JText::_('COM_SEMINARMAN_ACCEPTED');
	else
		echo 'unknown';
	if (is_null($field->value) || ($field->value)=="") {
		echo '<input type="hidden" name="field'. $field->id .'" value="2" />';  // set it as unknown
	} else {
		echo '<input type="hidden" name="field'. $field->id .'" value="'. $this->escape($field->value) .'" />';
	}
}
else
	echo SeminarmanCustomfieldsLibrary::getFieldHTML( $field , '' );
?>
			</td>
		</tr>
<?php
}
?>
	</tbody>
	</table>
<?php
echo $pane->endPanel();

}

echo $pane->startPanel( JText::_('COM_SEMINARMAN_COMMENTS') , 'details-page' );
?>
<table class="paramlist admintable" style="width: 100%;">
<tbody>
	<tr>
		<td><?php echo JText::_('COM_SEMINARMAN_COMMENTS'); ?></td>
		<td class="paramlist_value"><textarea class="text_area" cols="64" rows="12" name="comments" id="comments"><?php echo $this->escape($this->application->comments); ?></textarea></td>
	</tr>
</tbody>
</table>
<?php
echo $pane->endPanel();
echo $pane->endPane();
?>
<div class="clr"></div>
	<input type="hidden" name="option" value="com_seminarman" />
    <input type="hidden" name="controller" value="application" />
	<input type="hidden" name="cid[]" value="<?php echo $this->escape($this->application->id); ?>" />
	<?php if (!$this->isNew): ?>
    <input type="hidden" name="user_id" value="<?php echo $this->escape($this->application->user_id); ?>" />
    <?php endif; ?>
	<input type="hidden" id="fieldcourse" name="fieldcourse" value="" />
	<input type="hidden" id="fieldpriceg" name="fieldpriceg" value="" />
	<input type="hidden" name="task" value="" />
	<?php echo JHTML::_('form.token'); ?>
</form>
