<?php
/**
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011 Open Source Group GmbH www.osg-gmbh.de
* @website http://www.profinvent.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

defined('_JEXEC') or die('Restricted access');
jimport('joomla.html.pane');

JHtml::_('behavior.modal');
// JHtml::_('behavior.mootools');
JHtml::_('behavior.framework');

?>

<script type="text/javascript">

Joomla.submitbutton = function(task)
{
	var form = document.adminForm;
	if (task == 'cancel') {
		Joomla.submitform( task );
		return;
	}

	// do field validation
	if (form.name.value == "") {
		alert( "<?php echo JText::_('COM_SEMINARMAN_MISSING_NAME', true); ?>" );
		return;
	}
<?php 
	$editor = JFactory::getEditor();
	echo $editor->save('html');
?>
	Joomla.submitform( task );
};

function qfSelectFile($id, $name)
{
	document.adminForm.srcpdf.value = $name;
	window.parent.document.getElementById('sbox-btn-close').fireEvent('click');
}

function toggleParams()
{
	var f = document.adminForm;
	var lis = f.parameters.getElementsByTagName('li');
	var lis_invoice = f.parameters.getElementsByClassName('li_invoice');
	var lis_attlst = f.parameters.getElementsByClassName('li_attlst');

	for (i=0; i<lis.length; i++) lis[i].style.display='none';
	if (f.templatefor.value == 0) {
		f.parameters.children[0].innerHTML = '<?php echo JText::_('COM_SEMINARMAN_PARAMETERS').' '.JText::_('COM_SEMINARMAN_INVOICE'); ?>';
		for (i=0; i<lis_invoice.length; i++) lis_invoice[i].style.display='';
	} else if (f.templatefor.value == 1) {
		f.parameters.children[0].innerHTML = '<?php echo JText::_('COM_SEMINARMAN_PARAMETERS').' '.JText::_('COM_SEMINARMAN_ATTENDANCE_LIST'); ?>';
		for (i=0; i<lis_attlst.length; i++) lis_attlst[i].style.display='';
	} else if (f.templatefor.value == 2) {
		f.parameters.children[0].innerHTML = '<?php echo JText::_('COM_SEMINARMAN_PARAMETERS').' '.JText::_('COM_SEMINARMAN_CERTIFICATE'); ?>';
		for (i=0; i<lis_invoice.length; i++) lis_invoice[i].style.display='';
	} else if (f.templatefor.value == 3) {
		f.parameters.children[0].innerHTML = '<?php echo JText::_('COM_SEMINARMAN_PARAMETERS').' '.JText::_('COM_SEMINARMAN_ADDITIONAL_ATTACHMENT'); ?>';
		for (i=0; i<lis_invoice.length; i++) lis_invoice[i].style.display='';
	}
}

window.addEvent('domready', toggleParams);
</script>

<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" >

<div class="width-70 fltlft">
	
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_SEMINARMAN_PDF_TEMPLATE'); ?></legend>
		<ul class="adminformlist">
			<li>
				<label for="name"><?php echo JText::_('COM_SEMINARMAN_NAME'); ?><span class="star">&nbsp;*</span></label>
				<input class="inputbox required" type="text" name="name" id="name" size="50" maxlength="255" value="<?php echo htmlspecialchars($this->template->name, ENT_QUOTES, 'UTF-8'); ?>" />
			</li>
			<li>
				<label for="templatefor"><?php echo JText::_('COM_SEMINARMAN_USE_FOR'); ?></label>
				<?php echo $this->templateforSelect; ?>
			</li>
			<li>
				<label for="srcpdf"><?php echo JText::_('COM_SEMINARMAN_PRINT_ON_PDF'); ?></label>
				<input type="text" name="srcpdf" id="srcpdf" size="50" maxlength="255" value="<?php echo $this->template->srcpdf; ?>" />
				<input type="button" name="clearbtn" id="clearbtn" value="X" onclick="this.form.srcpdf.value = ''"/>
				<label></label>
				<div class="button2-left btn">
					<div class="blank">
						<a class="modal" title="<?php echo JText::_('COM_SEMINARMAN_SELECT'); ?>" href="<?php echo $this->linkfsel; ?>" rel="{handler: 'iframe', size: {x: 850, y: 450}}"><?php echo JText::_('COM_SEMINARMAN_SELECT'); ?></a>
					</div>
				</div>
				<div class="button2-left btn">
					<div class="blank">
						<a title="<?php echo JText::_('COM_SEMINARMAN_UPLOAD'); ?>" href="<?php echo JRoute::_('index.php?option=com_seminarman&view=filemanager');?>" target="_blank"><?php echo JText::_('COM_SEMINARMAN_UPLOAD'); ?></a>
					</div>
				</div>
			</li>
			<li><label><?php echo JText::_('COM_SEMINARMAN_PDF_MARGIN_DESC'); ?></label></li>
			<li>
				<label for="y">&nbsp;&nbsp;<?php echo JText::_('COM_SEMINARMAN_PDF_MARGIN_TOP'); ?></label>
				<input class="inputbox required" type="text" name="margin_top" id="margin_top" size="10" maxlength="255" value="<?php echo $this->template->margin_top; ?>" />
			</li>
			<li class="fltlft">
				<label for="margin_bottom"><?php echo JText::_('COM_SEMINARMAN_PDF_MARGIN_BOTTOM'); ?></label>
				<input class="inputbox required" type="text" name="margin_bottom" id="margin_bottom" size="10" maxlength="255" value="<?php echo $this->template->margin_bottom; ?>" />
			</li>
			<li>
				<label for="margin_left">&nbsp;&nbsp;<?php echo JText::_('COM_SEMINARMAN_PDF_MARGIN_LEFT'); ?></label>
				<input class="inputbox required" type="text" name="margin_left" id="margin_left" size="10" maxlength="255" value="<?php echo $this->template->margin_left; ?>" />
			</li>
			<li class="fltlft">
				<label for="margin_right"><?php echo JText::_('COM_SEMINARMAN_PDF_MARGIN_RIGHT'); ?></label>
				<input class="inputbox required" type="text" name="margin_right" id="margin_right" size="10" maxlength="255" value="<?php echo $this->template->margin_right; ?>" />
			</li>
			<li>
				<label for="paperformat"><?php echo JText::_('COM_SEMINARMAN_PAPERFORMAT'); ?></label>
				<?php echo $this->paperformatSelect; ?>
			</li>
			<li>
				<label for="orientation"><?php echo JText::_('COM_SEMINARMAN_PDF_ORIENTATION'); ?></label>
				<?php echo $this->orientationSelect; ?>
			</li>
		</ul>
		<div class="clr"></div>
		<label for="html"><?php echo JText::_('COM_SEMINARMAN_BODY');?></label>
		<div class="clr"></div>
<?php
$editor = JFactory::getEditor();
echo $editor->display('html', $this->template->html, '100%', '500', '60', '20', true);
?>
	</fieldset>
</div>

<div class="width-30 fltrt">
	<fieldset class="adminform" id="parameters">
		<legend><?php echo JText::_('COM_SEMINARMAN_PARAMETERS'); ?></legend>
		<ul>
			<li class="li_invoice"><a href="#" onclick="window.parent.jInsertEditorText('{INVOICE_NUMBER}', 'html'); return false;">{INVOICE_NUMBER}</a>: <?php echo JText::_('COM_SEMINARMAN_INVOICE_NUMBER'); ?></li>
			<li class="li_invoice"><a href="#" onclick="window.parent.jInsertEditorText('{INVOICE_DATE}', 'html'); return false;">{INVOICE_DATE}</a>: <?php echo JText::_('COM_SEMINARMAN_INVOICE_DATE'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{CURRENT_DATE}', 'html'); return false;">{CURRENT_DATE}</a>: <?php echo JText::_('COM_SEMINARMAN_DATE'); ?></li>
			<li class="li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{ATTENDEES_TOTAL}', 'html'); return false;">{ATTENDEES_TOTAL}</a>: <?php echo JText::_('COM_SEMINARMAN_NUMBER_OF_ATTENDEES_TOTAL'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{ATTENDEES}', 'html'); return false;">{ATTENDEES}</a>: <?php echo JText::_('COM_SEMINARMAN_NUMBER_OF_ATTENDEES'); ?></li>
			<li class="li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{LINE_INDEX}', 'html'); return false;">{LINE_INDEX}</a>: <?php echo JText::_('COM_SEMINARMAN_LINE_INDEX'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{SALUTATION}', 'html'); return false;">{SALUTATION}</a>: <?php echo JText::_('COM_SEMINARMAN_SALUTATION'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{TITLE}', 'html'); return false;">{TITLE}</a>: <?php echo JText::_('COM_SEMINARMAN_TITLE'); ?>  (<?php echo JText::_('COM_SEMINARMAN_AUTO_SPACE'); ?>)</li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{FIRSTNAME}', 'html'); return false;">{FIRSTNAME}</a>: <?php echo JText::_('COM_SEMINARMAN_FIRST_NAME'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{LASTNAME}', 'html'); return false;">{LASTNAME}</a>: <?php echo JText::_('COM_SEMINARMAN_LAST_NAME'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{EMAIL}', 'html'); return false;">{EMAIL}</a>: <?php echo JText::_('COM_SEMINARMAN_EMAIL'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{APPLICATION_ID}', 'html'); return false;">{APPLICATION_ID}</a>: <?php echo JText::_('COM_SEMINARMAN_APPLICATION_ID'); ?></li>
<?php
foreach ($this->fields as $field)
	if ($field->type != 'checkboxtos')
		echo '<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText(\'{'. strtoupper($field->fieldcode) .'}\', \'html\'); return false;">{'. strtoupper($field->fieldcode) .'}</a>: '. $field->name .'</li>';
?>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{COURSE_ID}', 'html'); return false;">{COURSE_ID}</a>: <?php echo JText::_('COM_SEMINARMAN_COURSE_ID'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{COURSE_TITLE}', 'html'); return false;">{COURSE_TITLE}</a>: <?php echo JText::_('COM_SEMINARMAN_COURSE_TITLE'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{COURSE_CODE}', 'html'); return false;">{COURSE_CODE}</a>: <?php echo JText::_('COM_SEMINARMAN_COURSE_CODE'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{COURSE_INTROTEXT}', 'html'); return false;">{COURSE_INTROTEXT}</a>: <?php echo JText::_('COM_SEMINARMAN_COURSE_INTROTEXT'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{COURSE_FULLTEXT}', 'html'); return false;">{COURSE_FULLTEXT}</a>: <?php echo JText::_('COM_SEMINARMAN_COURSE_FULLTEXT'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{COURSE_CAPACITY}', 'html'); return false;">{COURSE_CAPACITY}</a>: <?php echo JText::_('COM_SEMINARMAN_CAPACITY'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{COURSE_LOCATION}', 'html'); return false;">{COURSE_LOCATION}</a>: <?php echo JText::_('COM_SEMINARMAN_LOCATION'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{COURSE_URL}', 'html'); return false;">{COURSE_URL}</a>: <?php echo JText::_('COM_SEMINARMAN_HYPERLINK'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{COURSE_START_DATE}', 'html'); return false;">{COURSE_START_DATE}</a>: <?php echo JText::_('COM_SEMINARMAN_START_DATE'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{COURSE_FINISH_DATE}', 'html'); return false;">{COURSE_FINISH_DATE}</a>: <?php echo JText::_('COM_SEMINARMAN_FINISH_DATE'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{COURSE_START_TIME}', 'html'); return false;">{COURSE_START_TIME}</a>: <?php echo JText::_('COM_SEMINARMAN_START_TIME'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{COURSE_FINISH_TIME}', 'html'); return false;">{COURSE_FINISH_TIME}</a>: <?php echo JText::_('COM_SEMINARMAN_FINISH_TIME'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{COURSE_START_WEEKDAY}', 'html'); return false;">{COURSE_START_WEEKDAY}</a>: <?php echo JText::_('COM_SEMINARMAN_START_WEEKDAY'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{COURSE_FIRST_SESSION_TITLE}', 'html'); return false;">{COURSE_FIRST_SESSION_TITLE}</a>: <?php echo JText::_('COM_SEMINARMAN_COURSE_FIRST_SESSION_TITLE'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{COURSE_FIRST_SESSION_CLOCK}', 'html'); return false;">{COURSE_FIRST_SESSION_CLOCK}</a>: <?php echo JText::_('COM_SEMINARMAN_COURSE_FIRST_SESSION_CLOCK'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{COURSE_FIRST_SESSION_DURATION}', 'html'); return false;">{COURSE_FIRST_SESSION_DURATION}</a>: <?php echo JText::_('COM_SEMINARMAN_COURSE_FIRST_SESSION_DURATION'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{COURSE_FIRST_SESSION_ROOM}', 'html'); return false;">{COURSE_FIRST_SESSION_ROOM}</a>: <?php echo JText::_('COM_SEMINARMAN_COURSE_FIRST_SESSION_ROOM'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{COURSE_FIRST_SESSION_COMMENT}', 'html'); return false;">{COURSE_FIRST_SESSION_COMMENT}</a>: <?php echo JText::_('COM_SEMINARMAN_COURSE_FIRST_SESSION_COMMENT'); ?></li>
			<li class="li_invoice"><a href="#" onclick="window.parent.jInsertEditorText('{PRICE_PER_ATTENDEE}', 'html'); return false;">{PRICE_PER_ATTENDEE}</a>: <?php echo JText::_('COM_SEMINARMAN_PRICE_PER_ATTENDEE'); ?></li>
			<li class="li_invoice"><a href="#" onclick="window.parent.jInsertEditorText('{PRICE_PER_ATTENDEE_VAT}', 'html'); return false;">{PRICE_PER_ATTENDEE_VAT}</a>: <?php echo JText::_('COM_SEMINARMAN_PRICE_PER_ATTENDEE_VAT'); ?></li>
			<li class="li_invoice"><a href="#" onclick="window.parent.jInsertEditorText('{PRICE_TOTAL}', 'html'); return false;">{PRICE_TOTAL}</a>: <?php echo JText::_('COM_SEMINARMAN_TOTAL_PRICE'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{PRICE_TOTAL_VAT}', 'html'); return false;">{PRICE_TOTAL_VAT}</a>: <?php echo JText::_('COM_SEMINARMAN_TOTAL_PRICE_VAT'); ?></li>
			<li class="li_invoice"><a href="#" onclick="window.parent.jInsertEditorText('{PRICE_VAT_PERCENT}', 'html'); return false;">{PRICE_VAT_PERCENT}</a>: <?php echo JText::_('COM_SEMINARMAN_VAT'); ?></li>
			<li class="li_invoice"><a href="#" onclick="window.parent.jInsertEditorText('{PRICE_VAT}', 'html'); return false;">{PRICE_VAT}</a>: <?php echo JText::_('COM_SEMINARMAN_VAT_ABS'); ?></li>
			<li class="li_invoice"><a href="#" onclick="window.parent.jInsertEditorText('{PRICE_TOTAL_DISCOUNT}', 'html'); return false;">{PRICE_TOTAL_DISCOUNT}</a>: <?php echo JText::_('COM_SEMINARMAN_TOTAL_DISCOUNT'); ?></li>
			<li class="li_invoice"><a href="#" onclick="window.parent.jInsertEditorText('{PRICE_TOTAL_ORIG_VAT}', 'html'); return false;">{PRICE_TOTAL_ORIG_VAT}</a>: <?php echo JText::_('COM_SEMINARMAN_TOTAL_ORIG_VAT'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{PRICE_REAL_BOOKING_SINGLE}', 'html'); return false;">{PRICE_REAL_BOOKING_SINGLE}</a>: <?php echo JText::_('COM_SEMINARMAN_REAL_BOOKING_SINGLE'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{PRICE_REAL_BOOKING_TOTAL}', 'html'); return false;">{PRICE_REAL_BOOKING_TOTAL}</a>: <?php echo JText::_('COM_SEMINARMAN_REAL_BOOKING_TOTAL'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{PRICE_GROUP_ORDERED}', 'html'); return false;">{PRICE_GROUP_ORDERED}</a>: <?php echo JText::_('COM_SEMINARMAN_PRICE_GROUP_ORDERED'); ?></li>

		<?php 
			$dispatcher=JDispatcher::getInstance();
		 	JPluginHelper::importPlugin('seminarman');
		 	$html_tmpl=$dispatcher->trigger('onEditPDFTemplate');  // some additional parameters
		 	if(isset($html_tmpl) && !empty($html_tmpl)) echo $html_tmpl[0];
		?>
			
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{PAYMENT_STATUS}', 'html'); return false;">{PAYMENT_STATUS}</a>: <?php echo JText::_('COM_SEMINARMAN_PAYMENT_STATUS'); ?></li>
			<li class="li_invoice"><a href="#" onclick="window.parent.jInsertEditorText('{PAYMENT_METHOD}', 'html'); return false;">{PAYMENT_METHOD}</a>: <?php echo JText::_('COM_SEMINARMAN_PAYMENT_METHOD'); ?></li>
			<li class="li_invoice"><a href="#" onclick="window.parent.jInsertEditorText('{PAYMENT_FEE}', 'html'); return false;">{PAYMENT_FEE}</a>: <?php echo JText::_('COM_SEMINARMAN_PAYMENT_FEE'); ?></li>
			<li class="li_invoice"><a href="#" onclick="window.parent.jInsertEditorText('{PAYMENT_TOTAL}', 'html'); return false;">{PAYMENT_TOTAL}</a>: <?php echo JText::_('COM_SEMINARMAN_PAYMENT_TOTAL'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{TUTOR}', 'html'); return false;">{TUTOR}</a>: <?php echo JText::_('COM_SEMINARMAN_DISPLAY_NAME') . ' ' . JText::_('COM_SEMINARMAN_FIRST_TUTOR'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{TUTOR_FIRSTNAME}', 'html'); return false;">{TUTOR_FIRSTNAME}</a>: <?php echo JText::_('COM_SEMINARMAN_FIRST_NAME') . ' ' . JText::_('COM_SEMINARMAN_FIRST_TUTOR'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{TUTOR_LASTNAME}', 'html'); return false;">{TUTOR_LASTNAME}</a>: <?php echo JText::_('COM_SEMINARMAN_LAST_NAME') . ' ' . JText::_('COM_SEMINARMAN_FIRST_TUTOR'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{TUTOR_SALUTATION}', 'html'); return false;">{TUTOR_SALUTATION}</a>: <?php echo JText::_('COM_SEMINARMAN_SALUTATION') . ' ' . JText::_('COM_SEMINARMAN_FIRST_TUTOR'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{TUTOR_OTHER_TITLE}', 'html'); return false;">{TUTOR_OTHER_TITLE}</a>: <?php echo JText::_('COM_SEMINARMAN_OTHER_TITLE') . ' ' . JText::_('COM_SEMINARMAN_FIRST_TUTOR'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{COURSE_ALL_TUTORS}', 'html'); return false;">{COURSE_ALL_TUTORS}</a>: <?php echo JText::_('COM_SEMINARMAN_ALL_TUTORS_DISPLAYNAME'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{COURSE_ALL_TUTORS_FULLNAME}', 'html'); return false;">{COURSE_ALL_TUTORS_FULLNAME}</a>: <?php echo JText::_('COM_SEMINARMAN_ALL_TUTORS_FULLNAME'); ?></li>
			<li class="li_invoice li_attlst"><a href="#" onclick="window.parent.jInsertEditorText('{COURSE_ALL_TUTORS_COMBINAME}', 'html'); return false;">{COURSE_ALL_TUTORS_COMBINAME}</a>: <?php echo JText::_('COM_SEMINARMAN_ALL_TUTORS_COMBINAME'); ?></li>
			<li class="li_invoice"><a href="#" onclick="window.parent.jInsertEditorText('{GROUP}', 'html'); return false;">{GROUP}</a>: <?php echo JText::_('COM_SEMINARMAN_GROUP'); ?></li>
			<li class="li_invoice"><a href="#" onclick="window.parent.jInsertEditorText('{EXPERIENCE_LEVEL}', 'html'); return false;">{EXPERIENCE_LEVEL}</a>: <?php echo JText::_('COM_SEMINARMAN_EXPERIENCE_LEVEL'); ?></li>
			<li class="li_invoice"><a href="#" onclick="window.parent.jInsertEditorText('{COURSE_CUSTOM_FIELD_1}', 'html'); return false;">{COURSE_CUSTOM_FIELD_1}</a>: <?php echo JText::_('COM_SEMINARMAN_COURSE_CUSTOM_FLD_1'); ?></li>
			<li class="li_invoice"><a href="#" onclick="window.parent.jInsertEditorText('{COURSE_CUSTOM_FIELD_2}', 'html'); return false;">{COURSE_CUSTOM_FIELD_2}</a>: <?php echo JText::_('COM_SEMINARMAN_COURSE_CUSTOM_FLD_2'); ?></li>
			<li class="li_invoice"><a href="#" onclick="window.parent.jInsertEditorText('{COURSE_CUSTOM_FIELD_3}', 'html'); return false;">{COURSE_CUSTOM_FIELD_3}</a>: <?php echo JText::_('COM_SEMINARMAN_COURSE_CUSTOM_FLD_3'); ?></li>
			<li class="li_invoice"><a href="#" onclick="window.parent.jInsertEditorText('{COURSE_CUSTOM_FIELD_4}', 'html'); return false;">{COURSE_CUSTOM_FIELD_4}</a>: <?php echo JText::_('COM_SEMINARMAN_COURSE_CUSTOM_FLD_4'); ?></li>
			<li class="li_invoice"><a href="#" onclick="window.parent.jInsertEditorText('{COURSE_CUSTOM_FIELD_5}', 'html'); return false;">{COURSE_CUSTOM_FIELD_5}</a>: <?php echo JText::_('COM_SEMINARMAN_COURSE_CUSTOM_FLD_5'); ?></li>
			<li class="li_attlst"><?php echo JText::_('COM_SEMINARMAN_PDF_LOOP_DESC'); ?></li>
		</ul>
	</fieldset>


</div>

<input type="hidden" name="id" value="<?php echo $this->template->id; ?>" />
<input type="hidden" name="option" value="com_seminarman" />
<input type="hidden" name="task" value="apply" />
<input type="hidden" name="controller" value="pdftemplate" />
<?php echo JHTML::_('form.token');?>
</form>
