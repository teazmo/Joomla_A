<?php
/**
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011 Open Source Group GmbH www.osg-gmbh.de
* @website http://www.profinvent.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined('_JEXEC') or die('Restricted access');
jimport('joomla.html.pane');

$editor = JFactory::getEditor();
// JHTML::_('behavior.calendar');
JHTMLBehavior::formvalidation();

$ADMINPATH = JPATH_BASE . '\components\com_seminarman';

?>
<?php

JToolBarHelper::title(JText::_('COM_SEMINARMAN_EMAIL_TEMPLATE'), 'config');
JToolBarHelper::apply();
JToolBarHelper::save();
JToolBarHelper::cancel();

?>
<script type="text/javascript">
	Joomla.submitbutton = function(task){
		if (task == 'cancel') {
			Joomla.submitform( task );
			return;
		}
		
		var form = document.adminForm;

		// do field validation
		if (form.title.value == ""){
			alert( "<?php echo JText::_('COM_SEMINARMAN_MISSING_NAME', true); ?>" );
		} else {
			Joomla.submitform( task );
		}
	}
</script>

<?php
if(!isset($this->emailtemplate->recipient))
	$this->emailtemplate->recipient = "{EMAIL}";
if(!isset($this->emailtemplate->bcc))
	$this->emailtemplate->bcc = "{ADMIN_CUSTOM_RECIPIENT}";
?>

<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" >
<div class="width-60 fltlft">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_SEMINARMAN_EMAIL_TEMPLATE'); ?></legend>
		<ul class="adminformlist">
			<li>
				<label for="title"><?php echo JText::_('COM_SEMINARMAN_NAME'); ?></label>
				<input class="inputbox required" type="text" name="title" id="title" size="60" maxlength="255" value="<?php if (isset($this->emailtemplate)) echo htmlspecialchars($this->emailtemplate->title, ENT_QUOTES, 'UTF-8'); ?>" />
			</li>
			<li>
				<label for="recipient"><?php echo JText::_('COM_SEMINARMAN_SUBJECT'); ?></label>
				<input class="inputbox" type="text" name="subject" id="subject" size="60" maxlength="255" value="<?php if (isset($this->emailtemplate)) echo htmlspecialchars($this->emailtemplate->subject, ENT_QUOTES, 'UTF-8'); ?>" />
			</li>
			<li>
				<label for="subject"><?php echo JText::_('COM_SEMINARMAN_RECIPIENT');?></label>
				<input class="inputbox" type="text" name="recipient" id="recipient" size="60" maxlength="255" value="<?php echo $this->emailtemplate->recipient; ?>" />
			</li>
			<li>
				<label for="bcc">BCC</label>
				<input class="inputbox" type="text" name="bcc" id="bcc" size="60" maxlength="255" value="<?php echo $this->emailtemplate->bcc; ?>" />
			</li>
			<li>
				<label for="templatefor"><?php echo JText::_('COM_SEMINARMAN_USE_FOR');?></label>
				<?php echo $this->templateforSelect; ?>
			</li>
		</ul>
		<div class="clr"></div>
		<label for="html"><?php echo JText::_('COM_SEMINARMAN_BODY');?></label>
		<div class="clr"></div>
<?php
$editor = JFactory::getEditor();
echo $editor->display('body', $this->emailtemplate->body, '100%', '500', '60', '20', false);
?>
	</fieldset>	
</div>

<div class="width-40 fltrt">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_SEMINARMAN_PARAMETERS'); ?></legend>
		<ul>
			<li>{ADMIN_CUSTOM_RECIPIENT}: <?php echo JText::_('COM_SEMINARMAN_RECIPIENT_FROM_CONFIGURATION'); ?></li>
			<li>{TUTOR_RECIPIENTS}: <?php echo JText::_('COM_SEMINARMAN_TUTOR_RECIPIENTS'); ?></li>
			<li>{ATTENDEES}: <?php echo JText::_('COM_SEMINARMAN_NUMBER_OF_ATTENDEES'); ?></li>
			<li>{SALUTATION}: <?php echo JText::_('COM_SEMINARMAN_SALUTATION'); ?></li>
			<li>{TITLE}: <?php echo JText::_('COM_SEMINARMAN_TITLE'); ?> (<?php echo JText::_('COM_SEMINARMAN_AUTO_SPACE'); ?>)</li>
			<li>{FIRSTNAME}: <?php echo JText::_('COM_SEMINARMAN_FIRST_NAME'); ?></li>
			<li>{LASTNAME}: <?php echo JText::_('COM_SEMINARMAN_LAST_NAME'); ?></li>
			<li>{EMAIL}: <?php echo JText::_('COM_SEMINARMAN_EMAIL'); ?></li>
			<li>{EMAIL_CONFIRM_CC}: <?php echo JText::_('COM_SEMINARMAN_BOOKING_EMAIL_CC_LBL'); ?></li>
<?php
foreach ($this->fields as $field)
	if ($field->type != 'checkboxtos')
		echo '<li>{'. strtoupper($field->fieldcode) .'}: '. $field->name .'</li>';
?>
			<li>{CURRENT_DATE}: <?php echo JText::_('COM_SEMINARMAN_DATE'); ?></li>
			<li>{COURSE_ID}: <?php echo JText::_('COM_SEMINARMAN_COURSE_ID'); ?></li>
			<li>{COURSE_TITLE}: <?php echo JText::_('COM_SEMINARMAN_COURSE_TITLE'); ?></li>
			<li>{COURSE_CODE}: <?php echo JText::_('COM_SEMINARMAN_COURSE_CODE'); ?></li>
			<li>{COURSE_INTROTEXT}: <?php echo JText::_('COM_SEMINARMAN_COURSE_INTROTEXT'); ?></li>
			<li>{COURSE_FULLTEXT}: <?php echo JText::_('COM_SEMINARMAN_COURSE_FULLTEXT'); ?></li>
			<li>{COURSE_CAPACITY}: <?php echo JText::_('COM_SEMINARMAN_CAPACITY'); ?></li>
			<li>{COURSE_LOCATION}: <?php echo JText::_('COM_SEMINARMAN_LOCATION'); ?></li>
			<li>{COURSE_URL}: <?php echo JText::_('COM_SEMINARMAN_HYPERLINK'); ?></li>
			<li>{COURSE_START_DATE}: <?php echo JText::_('COM_SEMINARMAN_START_DATE'); ?></li>
			<li>{COURSE_FINISH_DATE}: <?php echo JText::_('COM_SEMINARMAN_FINISH_DATE'); ?></li>
			<li>{COURSE_START_TIME}: <?php echo JText::_('COM_SEMINARMAN_START_TIME'); ?></li>
			<li>{COURSE_FINISH_TIME}: <?php echo JText::_('COM_SEMINARMAN_FINISH_TIME'); ?></li>
			<li>{COURSE_START_WEEKDAY}: <?php echo JText::_('COM_SEMINARMAN_START_WEEKDAY'); ?></li>
			<li>{COURSE_FIRST_SESSION_TITLE}: <?php echo JText::_('COM_SEMINARMAN_COURSE_FIRST_SESSION_TITLE'); ?></li>
			<li>{COURSE_FIRST_SESSION_CLOCK}: <?php echo JText::_('COM_SEMINARMAN_COURSE_FIRST_SESSION_CLOCK'); ?></li>
			<li>{COURSE_FIRST_SESSION_DURATION}: <?php echo JText::_('COM_SEMINARMAN_COURSE_FIRST_SESSION_DURATION'); ?></li>
			<li>{COURSE_FIRST_SESSION_ROOM}: <?php echo JText::_('COM_SEMINARMAN_COURSE_FIRST_SESSION_ROOM'); ?></li>
			<li>{COURSE_FIRST_SESSION_COMMENT}: <?php echo JText::_('COM_SEMINARMAN_COURSE_FIRST_SESSION_COMMENT'); ?></li>
			<li>{COURSE_CUSTOM_FIELD_1}: <?php echo JText::_('COM_SEMINARMAN_COURSE_CUSTOM_FLD_1'); ?></li>
			<li>{COURSE_CUSTOM_FIELD_2}: <?php echo JText::_('COM_SEMINARMAN_COURSE_CUSTOM_FLD_2'); ?></li>
			<li>{COURSE_CUSTOM_FIELD_3}: <?php echo JText::_('COM_SEMINARMAN_COURSE_CUSTOM_FLD_3'); ?></li>
			<li>{COURSE_CUSTOM_FIELD_4}: <?php echo JText::_('COM_SEMINARMAN_COURSE_CUSTOM_FLD_4'); ?></li>
			<li>{COURSE_CUSTOM_FIELD_5}: <?php echo JText::_('COM_SEMINARMAN_COURSE_CUSTOM_FLD_5'); ?></li>
			<li>{APPLICATION_ID}: <?php echo JText::_('COM_SEMINARMAN_APPLICATION_ID'); ?></li>
			<li>{PRICE_PER_ATTENDEE}: <?php echo JText::_('COM_SEMINARMAN_PRICE_PER_ATTENDEE'); ?></li>
			<li>{PRICE_PER_ATTENDEE_VAT}: <?php echo JText::_('COM_SEMINARMAN_PRICE_PER_ATTENDEE_VAT'); ?></li>
			<li>{PRICE_TOTAL}: <?php echo JText::_('COM_SEMINARMAN_TOTAL_PRICE'); ?></li>
			<li>{PRICE_TOTAL_VAT}: <?php echo JText::_('COM_SEMINARMAN_TOTAL_PRICE_VAT'); ?></li>
			<li>{PRICE_VAT_PERCENT}: <?php echo JText::_('COM_SEMINARMAN_VAT'); ?></li>
			<li>{PRICE_VAT}: <?php echo JText::_('COM_SEMINARMAN_VAT_ABS'); ?></li>
			<li>{PRICE_TOTAL_DISCOUNT}: <?php echo JText::_('COM_SEMINARMAN_TOTAL_DISCOUNT'); ?></li>
			<li>{PRICE_TOTAL_ORIG_VAT}: <?php echo JText::_('COM_SEMINARMAN_TOTAL_ORIG_VAT'); ?></li>
			<li>{PRICE_REAL_BOOKING_SINGLE}: <?php echo JText::_('COM_SEMINARMAN_REAL_BOOKING_SINGLE'); ?></li>
			<li>{PRICE_REAL_BOOKING_TOTAL}: <?php echo JText::_('COM_SEMINARMAN_REAL_BOOKING_TOTAL'); ?></li>
			<li>{PRICE_GROUP_ORDERED}: <?php echo JText::_('COM_SEMINARMAN_PRICE_GROUP_ORDERED'); ?></li>

		<?php 
			$dispatcher=JDispatcher::getInstance();
		 	JPluginHelper::importPlugin('seminarman');
		 	$html_tmpl=$dispatcher->trigger('onEditEmailTemplate');  // some additional parameters
		 	if(isset($html_tmpl) && !empty($html_tmpl)) echo $html_tmpl[0];
		?>
		
		    <li>{PAYMENT_METHOD}: <?php echo JText::_('COM_SEMINARMAN_PAYMENT_METHOD'); ?></li>
		    <li>{PAYMENT_FEE}: <?php echo JText::_('COM_SEMINARMAN_PAYMENT_FEE'); ?></li>
			<li>{PAYMENT_TOTAL}: <?php echo JText::_('COM_SEMINARMAN_PAYMENT_TOTAL'); ?></li>
			<li>{TUTOR}: <?php echo JText::_('COM_SEMINARMAN_DISPLAY_NAME') . ' ' . JText::_('COM_SEMINARMAN_FIRST_TUTOR'); ?></li>
			<li>{TUTOR_FIRSTNAME}: <?php echo JText::_('COM_SEMINARMAN_FIRST_NAME') . ' ' . JText::_('COM_SEMINARMAN_FIRST_TUTOR'); ?></li>
			<li>{TUTOR_LASTNAME}: <?php echo JText::_('COM_SEMINARMAN_LAST_NAME') . ' ' . JText::_('COM_SEMINARMAN_FIRST_TUTOR'); ?></li>
			<li>{TUTOR_SALUTATION}: <?php echo JText::_('COM_SEMINARMAN_SALUTATION') . ' ' . JText::_('COM_SEMINARMAN_FIRST_TUTOR'); ?></li>
			<li>{TUTOR_OTHER_TITLE}: <?php echo JText::_('COM_SEMINARMAN_OTHER_TITLE') . ' ' . JText::_('COM_SEMINARMAN_FIRST_TUTOR'); ?></li>
			<li>{COURSE_ALL_TUTORS}: <?php echo JText::_('COM_SEMINARMAN_ALL_TUTORS_DISPLAYNAME'); ?></li>
			<li>{COURSE_ALL_TUTORS_FULLNAME}: <?php echo JText::_('COM_SEMINARMAN_ALL_TUTORS_FULLNAME'); ?></li>
			<li>{COURSE_ALL_TUTORS_COMBINAME}: <?php echo JText::_('COM_SEMINARMAN_ALL_TUTORS_COMBINAME'); ?></li>
			<li>{GROUP}: <?php echo JText::_('COM_SEMINARMAN_GROUP'); ?></li>
			<li>{GROUP_DESC}: <?php echo JText::_('COM_SEMINARMAN_GROUP').' '.JText::_('COM_SEMINARMAN_DESCRIPTION'); ?></li>
			<li>{EXPERIENCE_LEVEL}: <?php echo JText::_('COM_SEMINARMAN_EXPERIENCE_LEVEL'); ?></li>
			<li>{EXPERIENCE_LEVEL_DESC}: <?php echo JText::_('COM_SEMINARMAN_EXPERIENCE_LEVEL').' '.JText::_('COM_SEMINARMAN_DESCRIPTION'); ?></li>
		</ul>
	</fieldset>
</div>


<input type="hidden" name="check" value="post"/>
<input type="hidden" name="id" value="<?php echo $this->emailtemplate->id; ?>" />
<input type="hidden" name="option" value="com_seminarman" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="emailtemplate" />
<?php echo JHTML::_('form.token');?>
</form>
