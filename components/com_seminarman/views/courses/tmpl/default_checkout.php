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

// custom fields
$js = '';
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

$post = JRequest::get('post');
$payment_method = 0;
if (isset($post['payment_method'])) {
	$payment_method = $post['payment_method'][0];
}
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
  <h2><?php echo JText::_('COM_SEMINARMAN_CHECKOUT_HEADER'); ?></h2>
  <?php echo $this->loadTemplate('payment_overview'); ?> 
  <div class="description">
  <p>
  <?php 
    if ($payment_method == 1) { // bank transfer
      $checkout_info = $params->get('bank_confirm_info');
    } elseif ($payment_method == 2) { // paypal
      $checkout_info = $params->get('paypal_confirm_info');
    }
    echo JText::_($checkout_info);
  ?>
  </p>
  </div> 
  <div class="centered">
  <form action="#" method="post" name="adminForm" id="adminForm" class="form-validate cart_submit"  enctype="multipart/form-data">
  	<?php 
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
	?>
	<button type="button" class="btn btn-primary cancel" onclick="submitbuttonSeminarman('cancel')">
	<?php echo JText::_('COM_SEMINARMAN_CART_CANCEL_BUTTON');?>
	</button>
	<button id="submitSeminarman" type="button" class="btn btn-primary validate" onclick="submitbuttonSeminarman('save')">
	<?php
	    if ($_POST['has_cost']) {
	        echo JText::_('COM_SEMINARMAN_CART_CONFIRM_BUTTON');
	    } else {
	    	echo JText::_('COM_SEMINARMAN_CART_FREE_CONFIRM_BUTTON');
	    }
	?>
	</button>
	    <input type="hidden" name="payment_method[]" value="<?php echo $payment_method; ?>" />
	    <input type="hidden" name="params[payment_method]" value="<?php echo $payment_method; ?>" />
	    <input type="hidden" name="params[payment_fee]" value="<?php echo $this->course->selected_payment_fee; ?>" />
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
// list of courses
$db = JFactory::getDBO();
//if (!$db->query()) {
//	JError::raiseError(500, $db->stderr(true));
//	return;
//}

$db = JFactory::getDBO();
$query = $db->getQuery(true);
$query->select( '*' );
$query->from( '#__seminarman_courses' );
$query->where( 'id ='. $_POST['course_id'] );
$db->setQuery( $query );

$courseRows = $db->loadObject();

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

	$dispatcher=JDispatcher::getInstance();
	JPluginHelper::importPlugin('seminarman');
	$html_tmpl=$dispatcher->trigger('onPostAddPriceForCart',array($courseRows));  // we need the course id
	if(isset($html_tmpl) && !empty($html_tmpl)) echo $html_tmpl[0];
?>	    
	    
	    <input type="hidden" name="option" value="com_seminarman" />
	    <input type="hidden" name="controller" value="application" />
	    <input type="hidden" name="task" value="" />
	<?php
	    echo JHTML::_('form.token');
	?> 
  </form> 
  </div>
  </div>