<?php
/**
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011 Open Source Group GmbH www.osg-gmbh.de
* @website http://www.profinvent.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

defined('_JEXEC') or die('Restricted access');

function formatDate($str, $format, $usertz = true) {
	if (substr($str, 0, 10) != '0000-00-00') {
		return JHTML::_('date', $str, $format, $usertz);
	}
}

// not in usage yet, maybe later
function formatTime($str, $format, $usertz = true) {
	if (substr($str, 0, 10) != '0000-00-00')
		return JHTML::_('date', $str, $format, $usertz);
	else { 
		$str_output = str_replace('0000-00-00 ', '', $str);
		return substr($str_output, 0, 5);
	}	
}

$site_timezone = SeminarmanFunctions::getSiteTimezone();
$global_timezone = SeminarmanFunctions::getSiteTimezone(true);

if ($this->row->start_date_all == 1) {
	$start_date_usertz = false;
} else {
	$start_date_usertz = true;
}

if ($this->row->finish_date_all == 1) {
	$finish_date_usertz = false;
} else {
	$finish_date_usertz = true;
}
// fix for 24:00:00 (illegal time colock)
if ($this->row->start_time == '24:00:00') $this->row->start_time = '23:59:59';
if ($this->row->finish_time == '24:00:00') $this->row->finish_time = '23:59:59';
?>

<script type="text/javascript">

function resetHits()
{
	document.adminForm.hits.value = 0;
	document.adminForm.hitsdisp.value = 0;
}


// clone informations from template to course
function createFromTmpl()
{
	$task = 'createFromTmpl';
	submitform($task);
}

function apply_discount_2() {
	var price = document.adminForm.elements["price"].value;
	price = price.replace(",", ".");
	var mathop = document.adminForm.elements["price2_mathop"].value;
	var mathval = document.adminForm.elements["price2_value"].value;
	
	if (mathop == '+%') {
        var factor = mathval/100;
        var new_price = parseFloat(price) + parseFloat(price*factor);
	} else if (mathop == '-%') {
        var factor = mathval/100;
        var new_price = parseFloat(price) - parseFloat(price*factor);
	} else if (mathop == '+') {
		var new_price = parseFloat(price) + parseFloat(mathval);
	} else if (mathop == '-') {
		var new_price = parseFloat(price) - parseFloat(mathval);
	}
	
	document.adminForm.elements["price2"].value = new_price;
}

function apply_discount_3() {
	var price = document.adminForm.elements["price"].value;
	price = price.replace(",", ".");
	var mathop = document.adminForm.elements["price3_mathop"].value;
	var mathval = document.adminForm.elements["price3_value"].value;
	
	if (mathop == '+%') {
        var factor = mathval/100;
        var new_price = parseFloat(price) + parseFloat(price*factor);
	} else if (mathop == '-%') {
        var factor = mathval/100;
        var new_price = parseFloat(price) - parseFloat(price*factor);
	} else if (mathop == '+') {
		var new_price = parseFloat(price) + parseFloat(mathval);
	} else if (mathop == '-') {
		var new_price = parseFloat(price) - parseFloat(mathval);
	}
	
	document.adminForm.elements["price3"].value = new_price;
}

function apply_discount_4() {
	var price = document.adminForm.elements["price"].value;
	price = price.replace(",", ".");
	var mathop = document.adminForm.elements["price4_mathop"].value;
	var mathval = document.adminForm.elements["price4_value"].value;
	
	if (mathop == '+%') {
        var factor = mathval/100;
        var new_price = parseFloat(price) + parseFloat(price*factor);
	} else if (mathop == '-%') {
        var factor = mathval/100;
        var new_price = parseFloat(price) - parseFloat(price*factor);
	} else if (mathop == '+') {
		var new_price = parseFloat(price) + parseFloat(mathval);
	} else if (mathop == '-') {
		var new_price = parseFloat(price) - parseFloat(mathval);
	}
	
	document.adminForm.elements["price4"].value = new_price;
}

function apply_discount_5() {
	var price = document.adminForm.elements["price"].value;
	price = price.replace(",", ".");
	var mathop = document.adminForm.elements["price5_mathop"].value;
	var mathval = document.adminForm.elements["price5_value"].value;
	
	if (mathop == '+%') {
        var factor = mathval/100;
        var new_price = parseFloat(price) + parseFloat(price*factor);
	} else if (mathop == '-%') {
        var factor = mathval/100;
        var new_price = parseFloat(price) - parseFloat(price*factor);
	} else if (mathop == '+') {
		var new_price = parseFloat(price) + parseFloat(mathval);
	} else if (mathop == '-') {
		var new_price = parseFloat(price) - parseFloat(mathval);
	}
	
	document.adminForm.elements["price5"].value = new_price;
}

Joomla.submitbutton = function(task){

	var form = document.adminForm;

	if (task == 'cancel') {
		submitform( task );
		return;
	}

	// do field validation
	if (form.title.value == "")
		alert( "<?php echo JText::_('COM_SEMINARMAN_MISSING_TITLE'); ?>" );
	else if(form.catid.selectedIndex == -1)
		alert( "<?php echo JText::_('COM_SEMINARMAN_SELECT_CATEGORY'); ?>" );
	else if(form.tutor_id.value < 1)
		alert( "<?php echo JText::_('COM_SEMINARMAN_SELECT_TUTOR'); ?>" );
	else {
		<?php echo $this->editor->save('text'); ?>
		<?php echo $this->editor->save('certificate_text'); ?>
		Joomla.submitform( task );
	}
};
</script>

<style type="text/css">

fieldset.adminform label {
	min-width: 100px;
	text-align: right;
	padding-right: 10px;
	margin: 3px 0;
}

fieldset input, fieldset select, fieldset img, fieldset button {
    float: left;
    margin: 3px 5px 3px 0;
    width: auto;
}

fieldset.adminform {
	margin: 5px;
}

fieldset.radio {
    border: 0 none;
    float: left;
    margin: 0 0 5px;
    padding: 0 ! important;
}

fieldset.radio label {
    clear: none;
    display: inline;
    float: left;
    padding-left: 0;
    padding-right: 10px;
    min-width: 0 ! important;
}

</style>

<?php
$infoimage = JHTML::image('components/com_seminarman/assets/images/icon-16-hint.png', JText::_('NOTES'));
$params = JComponentHelper::getParams( 'com_seminarman' );

if(!(JHTMLSeminarman::UserIsCourseManager())){
	$hidden = 'style="display: none;"';
	$disabled = 'disabled';
	$readonly = 'readonly="readonly" style="border-width: 0px;"';
	$readonly_price = 'readonly="readonly" style="border-width: 0px;"';
	$apply_2_discount = '';
	$apply_3_discount = '';
	$apply_4_discount = '';
	$apply_5_discount = '';
} else {
	$hidden = '';
	$disabled = '';
	if ($params->get('trigger_virtuemart') == 1) {
        // $readonly_price = 'readonly="readonly" style="background: #ddd;"';
        // due to the changes of multiple price model from vm 2.0.16, all prices can now be edited freely
		$readonly_price = '';
	} else {
	    $readonly_price = '';
	}
	$readonly = '';
	$apply_2_discount = '<input type="button" value="' . JText::_('COM_SEMINARMAN_USE_CALC_RULE_2') . '" onclick="apply_discount_2()" />';
	$apply_3_discount = '<input type="button" value="' . JText::_('COM_SEMINARMAN_USE_CALC_RULE_3') . '" onclick="apply_discount_3()" />';
	$apply_4_discount = '<input type="button" value="' . JText::_('COM_SEMINARMAN_USE_CALC_RULE_4') . '" onclick="apply_discount_4()" />';
	$apply_5_discount = '<input type="button" value="' . JText::_('COM_SEMINARMAN_USE_CALC_RULE_5') . '" onclick="apply_discount_5()" />';	
}
?>

<script type="text/javascript">
function isEmpty(str) {
    return (!str || 0 === str.length);
}
function trim(str) {
	str = String(str);
    return str.replace(/^\s+|\s+$/g,"");
}
function show_calculator(idc) {
	switch(idc)
	{
	case 1:
	    document.getElementById("netto_rechner1").style.display="block";
	    break;
	case 2:
	    document.getElementById("netto_rechner2").style.display="block";
	    break;
	case 3:
	    document.getElementById("netto_rechner3").style.display="block";
	    break;
	case 4:
	    document.getElementById("netto_rechner4").style.display="block";
	    break;
	case 5:
	    document.getElementById("netto_rechner5").style.display="block";
	    break;		
	}
}
function calc_netto(idc) {
	switch(idc)
	{
	case 1:
	    var bruttopreis = document.adminForm.elements["bruttopreis1"].value;
	    break;
	case 2:
	    var bruttopreis = document.adminForm.elements["bruttopreis2"].value;
	    break;
	case 3:
	    var bruttopreis = document.adminForm.elements["bruttopreis3"].value;
	    break;
	case 4:
	    var bruttopreis = document.adminForm.elements["bruttopreis4"].value;
	    break;
	case 5:
	    var bruttopreis = document.adminForm.elements["bruttopreis5"].value;
	    break;
	}
	if ((isNaN(trim(bruttopreis)))||(isEmpty(trim(bruttopreis)))) {
        alert(unescape("Der von Ihnen gegebene Bruttopreis ist ungültig, bitte korrigieren!"));
	} else {
		// alert(document.item_form.elements["tax_percents[][0]"].value);
		// var vat=document.getElementById("tax_percent_name_1").value;
		var vat=<?php echo $this->row->vat; ?>;
		if ((isNaN(trim(vat)))||(isEmpty(trim(vat)))) {
			alert(unescape("Das von Ihnen gegebene Steuerregel ist ungültig, bitte korrigieren!"));
		}else{
			var nettopreis = bruttopreis / (1 + vat/100);
			switch(idc)
			{
			case 1:
			    document.adminForm.elements["price"].value = nettopreis;
	            document.getElementById("netto_rechner1").style.display="none";
	            break;
			case 2:
			    document.adminForm.elements["price2"].value = nettopreis;
	            document.getElementById("netto_rechner2").style.display="none";
	            break;
			case 3:
			    document.adminForm.elements["price3"].value = nettopreis;
	            document.getElementById("netto_rechner3").style.display="none";
	            break;
			case 4:
			    document.adminForm.elements["price4"].value = nettopreis;
	            document.getElementById("netto_rechner4").style.display="none";
	            break;
			case 5:
			    document.adminForm.elements["price5"].value = nettopreis;
	            document.getElementById("netto_rechner5").style.display="none";
	            break;
			}
		}
	}
}
function hide_calc(idc) {
	switch(idc)
	{
	case 1:
	    document.getElementById("netto_rechner1").style.display="none";
	    break;
	case 2:
	    document.getElementById("netto_rechner2").style.display="none";
	    break;
	case 3:
	    document.getElementById("netto_rechner3").style.display="none";
	    break;
	case 4:
	    document.getElementById("netto_rechner4").style.display="none";
	    break;
	case 5:
	    document.getElementById("netto_rechner5").style.display="none";
	    break;
	}
}

function setStartDateAll() {
	if (document.getElementsByName("params[start_date_all]")[1].checked) {
		document.getElementById('start_time').readOnly = true;
		document.getElementById('start_time').value = "00:00";
		document.getElementById('start_all_tz').style.display = "inline";
	} else {
		document.getElementById('start_time').readOnly = false;
		document.getElementById('start_all_tz').style.display = "none";
	}
}

function setFinishDateAll() {
	if (document.getElementsByName("params[finish_date_all]")[1].checked) {
		document.getElementById('finish_time').readOnly = true;
		document.getElementById('finish_time').value = "23:59";
		document.getElementById('finish_all_tz').style.display = "inline";
	} else {
		document.getElementById('finish_time').readOnly = false;
		document.getElementById('finish_all_tz').style.display = "none";
	}	
}
</script>

<?php if(JHTMLSeminarman::UserIsCourseManager() || ($params->get('edit_course_color') == 1)): ?>
<script type="text/javascript" src="<?php echo JURI::base(true); ?>/components/com_seminarman/classes/jscolor/jscolor.js"></script>
<?php endif; ?>
<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">

		<?php echo JHtml::_('bootstrap.startTabSet', 'smanTab', array('active' => 'general')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'smanTab', 'general', JText::_('COM_SEMINARMAN_GENERAL', true)); ?>

<div class="width-40 fltlft">
	<fieldset class="adminform">
	<legend><?php echo JText::_( 'COM_SEMINARMAN_DETAILS' ) . ' <small>('. JText::_('COM_SEMINARMAN_CURRENT_TIME_ZONE') .': ' . $site_timezone . ')</small>'; ?></legend>
	<ul class="adminformlist">
		<li <?php if ($params->get('edit_course_color') == -1) echo $hidden; ?>>
			<label for="color"><?php echo JText::_('COM_SEMINARMAN_COURSE_COLOR'); ?></label>
			<input <?php if ($params->get('edit_course_color') <= 0) echo $readonly; ?> type="text" class="color" name="params[color]" id="color" size="32" maxlength="6" value="<?php echo ($this->row->color ? $this->row->color : $params->get('course_default_color'));?>" />
		</li>
		<li>
			<label><?php echo JText::_('COM_SEMINARMAN_COURSE_ID'); ?></label>
			<input type="text" readonly="readonly" size="32" style="border-width: 0px;" value="<?php echo $this->row->id; ?>" />
		</li>
		<li>
			<label><?php echo JText::_('COM_SEMINARMAN_HITS'); ?></label>
			<input type="text" readonly="readonly" name="hitsdisp" size="<?php echo strlen($this->row->hits); ?>" style="border-width: 0px;" value="<?php echo $this->row->hits; ?>" />
			<input type="button" class="button" name="reset_hits"  value="<?php echo JText::_('COM_SEMINARMAN_RESET'); ?>" onclick="resetHits()" />
			
		</li>
		<li>
			<label><?php echo JText::_('COM_SEMINARMAN_REVISED'); ?></label>
			<input type="text" readonly="readonly" size="32" style="border-width: 0px;" value="<?php echo $this->row->version; ?> <?php echo JText::_('COM_SEMINARMAN_TIMES'); ?>" />
		</li>
		<li>
			<label><?php echo JText::_('COM_SEMINARMAN_CREATED'); ?></label>
			<input type="text" readonly="readonly" size="32" style="border-width: 0px;" value="<?php if ($this->row->created == $this->nullDate) echo JText::_('COM_SEMINARMAN_NEW_COURSE'); else echo JHTML::_('date', $this->row->created, JText::_('DATE_FORMAT_LC2')); ?>" />
		</li>
		<li>
			<label><?php echo JText::_('COM_SEMINARMAN_MODIFIED'); ?></label>
			<input type="text" readonly="readonly" size="32" style="border-width: 0px;" value="<?php if ($this->row->modified == $this->nullDate) echo JText::_('COM_SEMINARMAN_NOT_MODIFIED'); else echo JHTML::_('date', $this->row->modified, JText::_('DATE_FORMAT_LC2')); ?>" />
		</li>
<?php 
    echo $this->lists['select_vm'];
?>		
		<li <?php if ($params->get('edit_course_status') == -1) echo $hidden; ?>>
			<label for="state"><?php echo JText::_('JSTATUS'); ?></label>
			<?php echo $this->lists['state']; ?>
		</li>		
		<li <?php if ($params->get('edit_course_new') == -1) echo $hidden; ?>>
			<label for="new"><?php echo JText::_('COM_SEMINARMAN_NEW'); ?></label>
			<fieldset id="new" class="radio"><?php echo $this->lists['new']; ?></fieldset>
		</li>
		<li <?php if ($params->get('edit_course_canceled') == -1) echo $hidden; ?>>
			<label for="canceled"><?php echo JText::_('COM_SEMINARMAN_COURSE_CANCELED'); ?></label>
			<fieldset id="canceled" class="radio"><?php echo $this->lists['canceled']; ?></fieldset>
		</li>
		<li>
			<label for="course_template"><?php echo JText::_('COM_SEMINARMAN_TEMPLATE');?></label>
			<?php echo $this->lists['templates'];?>
			<input type="button" value="<?php echo JText::_('COM_SEMINARMAN_CLONE');?>" onclick="createFromTmpl()" />
		</li>
		<li <?php if ($params->get('edit_course_title') == -1) echo $hidden; ?>>
			<label for="title"><?php echo JText::_('COM_SEMINARMAN_TITLE') ?><span class="star">&nbsp;*</span></label>
			<input class="inputbox" <?php if ($params->get('edit_course_title') < 1) echo $readonly; ?> type="text" name="title" id="title" size="32" maxlength="254" value="<?php echo $this->row->title; ?>" />
		</li>
		<li <?php if ($params->get('edit_course_alias') == -1) echo $hidden; ?>>
			<label for="alias"><?php echo JText::_('COM_SEMINARMAN_ALIAS') ?></label>
			<input class="inputbox" <?php if ($params->get('edit_course_alias') < 1) echo $readonly; ?> type="text" name="alias" id="alias" size="32" maxlength="254" value="<?php echo $this->row->alias; ?>" />
		</li>
		<li <?php if ($params->get('edit_course_code') == -1) echo $hidden; ?>>
			<label for="code"><?php echo JText::_('COM_SEMINARMAN_COURSE_CODE') ?></label>
			<input class="inputbox" type="text" name="code" id="code" <?php if ($params->get('edit_course_code') < 1) echo $readonly; ?> size="32" maxlength="20" value="<?php echo $this->row->code; ?>" />
		</li>
		
<?php 
// load mailplus plugin if available
$dispatcher=JDispatcher::getInstance();
JPluginHelper::importPlugin('seminarman');
$html_tmpl=$dispatcher->trigger('onAddMailplusForCourse',array($this->row));  // we need the course attribs
if (isset($html_tmpl) && !empty($html_tmpl)) echo $html_tmpl[0];
?>
		
		<li>
			<label for="tutor"><?php echo JText::_('COM_SEMINARMAN_TUTOR'); ?><span class="star">&nbsp;*</span></label>
			<?php echo $this->lists['username']; ?>
		</li>
		<li style="border: 1px solid #ccc; overflow: hidden; clear: left;">
			<label for="start_date"><?php echo JText::_('COM_SEMINARMAN_START_DATE') . ' / ' . JText::_('COM_SEMINARMAN_TIME'); ?></label>
			<?php echo JHTML::calendar( formatDate($this->row->start_date . ' ' . $this->row->start_time, JText::_('COM_SEMINARMAN_DATE_FORMAT1'), $start_date_usertz), 'start_date', 'start_date', JText::_('COM_SEMINARMAN_DATE_FORMAT1_ALT'));?>
		    <label style="clear: none; min-width: 2px; width: auto;">&#47;</label>
		    <input class="inputbox" type="text" name="start_time" id="start_time" size="4" maxlength="5" value="<?php echo (!empty($this->row->start_time)) ? formatTime($this->row->start_date . ' ' . $this->row->start_time, 'H:i', $start_date_usertz) : '';?>" /><label style="clear: none; min-width: 20px; width: auto;">(hh:mm)</label>
		    <label class="hasTooltip" data-original-title="<?php echo JText::_('COM_SEMINARMAN_ALL_DAY_START_DESC'); ?>" for="params_start_date_all"><?php echo JText::_('COM_SEMINARMAN_ALL_DAY'); ?></label>
		    <fieldset id="params_start_date_all" class="radio"><?php echo $this->lists['start_date_all']; ?></fieldset>
		    <span id="start_all_tz" style="float: left;"><?php echo "(".$global_timezone.")"; ?></span>
		</li>
		<li style="border: 1px solid #ccc; margin-top: 5px; overflow: hidden; clear: left;">
			<label for="finish_date"><?php echo JText::_('COM_SEMINARMAN_FINISH_DATE') . ' / ' . JText::_('COM_SEMINARMAN_TIME'); ?></label>
			<?php echo JHTML::calendar( formatDate($this->row->finish_date . ' ' . $this->row->finish_time, JText::_('COM_SEMINARMAN_DATE_FORMAT1'), $finish_date_usertz),  'finish_date', 'finish_date', JText::_('COM_SEMINARMAN_DATE_FORMAT1_ALT'));?>
		    <label style="clear: none; min-width: 2px; width: auto;">&#47;</label>
		    <input class="inputbox" type="text" name="finish_time" id="finish_time" size="4" maxlength="5" value="<?php echo (!empty($this->row->finish_time)) ? formatTime($this->row->finish_date . ' ' . $this->row->finish_time, 'H:i', $finish_date_usertz) : '';?>" /><label style="clear: none; min-width: 20px; width: auto;">(hh:mm)</label>
		    <label class="hasTooltip" data-original-title="<?php echo JText::_('COM_SEMINARMAN_ALL_DAY_FINISH_DESC'); ?>" for="params_finish_date_all"><?php echo JText::_('COM_SEMINARMAN_ALL_DAY'); ?></label>
		    <fieldset id="params_finish_date_all" class="radio"><?php echo $this->lists['finish_date_all']; ?></fieldset>
		    <span id="finish_all_tz" style="float: left;"><?php echo "(".$global_timezone.")"; ?></span>
		</li>
		<li <?php if ($params->get('edit_course_email_tmpl') == -1) echo $hidden; ?>>
			<label for="email_template"><?php echo JText::_('COM_SEMINARMAN_EMAIL_TEMPLATE'); ?></label>
			<?php echo $this->lists['email_template']; ?>
		</li>
		<li <?php if ($params->get('edit_course_invoice_tmpl') == -1) echo $hidden; ?>>
			<label for="invoice_template"><?php echo JText::_('COM_SEMINARMAN_INVOICE_TEMPLATE'); ?></label>
			<?php echo $this->lists['invoice_template']; ?>
		</li>
		<li <?php if ($params->get('edit_course_attlst_tmpl') == -1) echo $hidden; ?>>
			<label for="attlst_template"><?php echo JText::_('COM_SEMINARMAN_ATTENDANCE_LIST_TEMPLATE'); ?></label>
			<?php echo $this->lists['attlst_template']; ?>
		</li>
		<li <?php if ($params->get('edit_course_certificate_tmpl') == -1) echo $hidden; ?>>
			<label for="certificate_template"><?php echo JText::_('COM_SEMINARMAN_CERTIFICATE_TEMPLATE'); ?></label>
			<?php echo $this->lists['certificate_template']; ?>
		</li>
		<li <?php if ($params->get('edit_course_extra_attach_tmpl') == -1) echo $hidden; ?>>
			<label for="extra_attach_template"><?php echo JText::_('COM_SEMINARMAN_EXTRA_ATTACH_TEMPLATE'); ?></label>
			<?php echo $this->lists['extra_attach_template']; ?>
		</li>
		<li <?php if ($params->get('edit_course_group') == -1) echo $hidden; ?>>
			<label for="group"><?php echo JText::_('COM_SEMINARMAN_GROUP') ?></label>
			<?php echo $this->lists['atgroup']; ?>
		</li>
		<li <?php if ($params->get('edit_course_experience_level') == -1) echo $hidden; ?>>
			<label for="experience_level"><?php echo JText::_('COM_SEMINARMAN_EXPERIENCE_LEVEL') ?></label>
			<?php echo $this->lists['experience_level']; ?>
		</li>
		<li <?php if ($params->get('edit_course_theme_points') == -1) echo $hidden; ?>>
			<label for="theme_points"><?php echo JText::_('COM_SEMINARMAN_POINTS'); ?></label>
			<input class="inputbox" <?php if ($params->get('edit_course_theme_points') < 1) echo $readonly; ?> type="text" name="theme_points" id="theme_points" size="10" maxlength="20" value="<?php echo $this->row->theme_points; ?>" />
		</li>

		
		<li <?php if ($params->get('edit_course_min_attendee') == -1) echo $hidden; ?>>
			<label for="min_attend"><?php echo JText::_('COM_SEMINARMAN_MIN_ATTENDEE'); ?></label>
			<input class="inputbox" <?php if ($params->get('edit_course_min_attendee') < 1) echo $readonly; ?> type="text" name="min_attend" id="min_attend" size="10" maxlength="5" value="<?php echo $this->row->min_attend; ?>" />
		</li>
		<li <?php if ($params->get('edit_course_capacity') == -1) echo $hidden; ?>>
			<label for="capacity"><?php echo JText::_('COM_SEMINARMAN_CAPACITY') ?></label>
			<input class="inputbox" <?php if ($params->get('edit_course_capacity') < 1) echo $readonly; ?> type="text" name="capacity" id="capacity" size="10" maxlength="5" value="<?php echo $this->row->capacity; ?>" />
		</li>
		<li <?php if ($params->get('edit_course_location') == -1) echo $hidden; ?>>
			<label for="location"><?php echo JText::_('COM_SEMINARMAN_LOCATION') ?></label>
			<input class="inputbox" <?php if ($params->get('edit_course_location') < 1) echo $readonly; ?> type="text" name="location" id="location" size="32" maxlength="254" value="<?php echo $this->row->location; ?>" />
		</li>
		<li <?php if ($params->get('edit_course_url_location') == -1) echo $hidden; ?>>
			<label for="url"><?php echo JText::_('COM_SEMINARMAN_HYPERLINK') . ' (' . JText::_('COM_SEMINARMAN_LOCATION') . ')'; ?></label>
			<input class="inputbox" <?php if ($params->get('edit_course_url_location') < 1) echo $readonly; ?> type="text" name="url" id="url" size="32" maxlength="254" value="<?php echo $this->row->url; ?>" />
		</li>
		<li <?php if ($params->get('edit_course_url_alternative') == -1) echo $hidden; ?>>
			<label for="alt_url"><?php echo JText::_('COM_SEMINARMAN_HYPERLINK') . ' (' . JText::_('COM_SEMINARMAN_COURSE') . ')'; ?></label>
			<input class="inputbox" <?php if ($params->get('edit_course_url_alternative') < 1) echo $readonly; ?> type="text" name="alt_url" id="alt_url" size="32" maxlength="254" value="<?php echo $this->row->alt_url; ?>" />
		</li>
		<li <?php if ($params->get('edit_course_category') == -1) echo $hidden; ?>>
			<label for="cid"><?php echo JText::_('COM_SEMINARMAN_CATEGORY'); ?><span class="star">&nbsp;*</span></label>
			<?php echo $this->lists['catid']; ?>
		</li>
		<li <?php if ($params->get('edit_course_tags') == -1) echo $hidden; ?>>
			<label for="tags"><?php echo JText::_('COM_SEMINARMAN_TAGS'); ?></label>
			<?php echo $this->lists['tagsselect']; ?>
		</li>
	</ul>
	</fieldset>
	

	
</div>

<div class="width-60 fltlft">

	<fieldset class="adminform" <?php if ($params->get('edit_course_desc') == -1) echo $hidden; ?>>
		<legend><?php echo JText::_('COM_SEMINARMAN_DESCRIPTION'); ?></legend>
		<?php 
		if(JHTMLSeminarman::UserIsCourseManager() || ($params->get('edit_course_desc') == 1)){
		echo $this->editor->display('text', $this->row->text, '100%', '250', '50', '15', false); 
		}else{
		echo '<div style="display: none;">' . $this->editor->display('text', $this->row->text, '100%', '250', '50', '15', false) . '</div>';	
		echo html_entity_decode('<div>' . $this->row->text . '</div>');	
		}
		?>
	</fieldset>
	
</div>
<div class="clr"></div>

<?php echo JHTML::_('form.token'); ?>
<input type="hidden" name="price2_mathop" value="<?php echo $this->lists['price2_mathop']; ?>" />
<input type="hidden" name="price2_value" value="<?php echo $this->lists['price2_value']; ?>" />
<input type="hidden" name="price3_mathop" value="<?php echo $this->lists['price3_mathop']; ?>" />
<input type="hidden" name="price3_value" value="<?php echo $this->lists['price3_value']; ?>" />
<input type="hidden" name="price4_mathop" value="<?php echo $this->lists['price4_mathop']; ?>" />
<input type="hidden" name="price4_value" value="<?php echo $this->lists['price4_value']; ?>" />
<input type="hidden" name="price5_mathop" value="<?php echo $this->lists['price5_mathop']; ?>" />
<input type="hidden" name="price5_value" value="<?php echo $this->lists['price5_value']; ?>" />
<input type="hidden" name="option" value="com_seminarman" />
<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
<input type="hidden" name="reference_number" value="<?php if ($this->row->reference_number) echo $this->row->reference_number; else echo uniqid(); ?>" />
<input type="hidden" name="controller" value="courses" />
<input type="hidden" name="view" value="course" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="version" value="<?php echo $this->row->version; ?>" />
<input type="hidden" name="hits" value="<?php echo $this->row->hits; ?>" />
<input type="hidden" name="minus" value="<?php echo $this->row->minus; ?>" />
<input type="hidden" name="plus" value="<?php echo $this->row->plus; ?>" />
<input type="hidden" name="logotodelete" value="<?php echo $this->row->image; ?>" />

        <?php echo JHtml::_('bootstrap.endTab'); ?>
        
        <?php
            if(JHTMLSeminarman::UserIsCourseManager() || ($params->get('edit_course_prices') == 1)){
                echo JHtml::_('bootstrap.addTab', 'smanTab', 'prices', JText::_('COM_SEMINARMAN_PRICES', true)); 
            } else {
            	echo JHtml::_('bootstrap.addTab', 'smanTab', 'prices', '');
            }
        ?>
<div class="width-40 fltlft">
	<fieldset class="adminform">
	<legend><?php echo JText::_( 'COM_SEMINARMAN_PRICES' ); ?></legend>
	<ul class="adminformlist"> 
		<li>
			<label for="price"><?php echo JText::_('COM_SEMINARMAN_PRICE') ?> (<?php $params = JComponentHelper::getParams( 'com_seminarman' ); echo $params->get( 'currency' ); ?>)</label>
			<input class="inputbox" <?php echo $readonly; ?> type="text" name="price" id="price" size="10" maxlength="20" value="<?php echo $this->row->price; ?>" />
			<input class="calculator" type="button" value="<?php echo JText::_('COM_SEMINARMAN_CALC_BUTTON') ?>" onclick="show_calculator(1);" />
		</li>
<li id="netto_rechner1" style="clear: left; background: #ddd; margin-left: 110px; padding: 10px; overflow: hidden; display: none;">
  <?php echo JText::sprintf('COM_SEMINARMAN_CALC_DESC', $this->row->vat); ?><br>
  <input class="calc_input"  type="text" name="bruttopreis1" id="bruttopreis1" size="10" maxlength="20" value="" />
  <input class="calc_input" type="button" value="<?php echo JText::_('COM_SEMINARMAN_CALC_NET_BUTTON') ?>" onclick="calc_netto(1);" />
  <input class="calc_input" type="button" value="<?php echo JText::_('COM_SEMINARMAN_CALC_CLOSE') ?>" onclick="hide_calc(1);" />
</li>
		<li>
			<label for="price2"><?php echo '2. ' . JText::_('COM_SEMINARMAN_PRICE') ?> (<?php $params = JComponentHelper::getParams( 'com_seminarman' ); echo $params->get( 'currency' ); ?>)</label>
			<input class="inputbox" <?php echo $readonly_price; ?> type="text" name="price2" id="price2" size="10" maxlength="20" value="<?php echo $this->row->price2; ?>" />
			<?php echo $apply_2_discount; ?>
			<input class="calculator" type="button" value="<?php echo JText::_('COM_SEMINARMAN_CALC_BUTTON') ?>" onclick="show_calculator(2);" />
		</li>
<li id="netto_rechner2" style="clear: left; background: #ddd; margin-left: 110px; padding: 10px; overflow: hidden; display: none;">
  <?php echo JText::sprintf('COM_SEMINARMAN_CALC_DESC', $this->row->vat); ?><br>
  <input class="calc_input"  type="text" name="bruttopreis2" id="bruttopreis2" size="10" maxlength="20" value="" />
  <input class="calc_input" type="button" value="<?php echo JText::_('COM_SEMINARMAN_CALC_NET_BUTTON') ?>" onclick="calc_netto(2);" />
  <input class="calc_input" type="button" value="<?php echo JText::_('COM_SEMINARMAN_CALC_CLOSE') ?>" onclick="hide_calc(2);" />
</li>
		<li>
			<label for="price3"><?php echo '3. ' . JText::_('COM_SEMINARMAN_PRICE') ?> (<?php $params = JComponentHelper::getParams( 'com_seminarman' ); echo $params->get( 'currency' ); ?>)</label>
			<input class="inputbox" <?php echo $readonly_price; ?> type="text" name="price3" id="price3" size="10" maxlength="20" value="<?php echo $this->row->price3; ?>" />
		    <?php echo $apply_3_discount; ?>
		    <input class="calculator" type="button" value="<?php echo JText::_('COM_SEMINARMAN_CALC_BUTTON') ?>" onclick="show_calculator(3);" />
		</li>
<li id="netto_rechner3" style="clear: left; background: #ddd; margin-left: 110px; padding: 10px; overflow: hidden; display: none;">
  <?php echo JText::sprintf('COM_SEMINARMAN_CALC_DESC', $this->row->vat); ?><br>
  <input class="calc_input"  type="text" name="bruttopreis3" id="bruttopreis3" size="10" maxlength="20" value="" />
  <input class="calc_input" type="button" value="<?php echo JText::_('COM_SEMINARMAN_CALC_NET_BUTTON') ?>" onclick="calc_netto(3);" />
  <input class="calc_input" type="button" value="<?php echo JText::_('COM_SEMINARMAN_CALC_CLOSE') ?>" onclick="hide_calc(3);" />
</li>
		<li>
			<label for="price4"><?php echo '4. ' . JText::_('COM_SEMINARMAN_PRICE') ?> (<?php $params = JComponentHelper::getParams( 'com_seminarman' ); echo $params->get( 'currency' ); ?>)</label>
			<input class="inputbox" <?php echo $readonly_price; ?> type="text" name="price4" id="price4" size="10" maxlength="20" value="<?php echo $this->row->price4; ?>" />
		    <?php echo $apply_4_discount; ?>
		    <input class="calculator" type="button" value="<?php echo JText::_('COM_SEMINARMAN_CALC_BUTTON') ?>" onclick="show_calculator(4);" />
		</li>
<li id="netto_rechner4" style="clear: left; background: #ddd; margin-left: 110px; padding: 10px; overflow: hidden; display: none;">
  <?php echo JText::sprintf('COM_SEMINARMAN_CALC_DESC', $this->row->vat); ?><br>
  <input class="calc_input"  type="text" name="bruttopreis4" id="bruttopreis4" size="10" maxlength="20" value="" />
  <input class="calc_input" type="button" value="<?php echo JText::_('COM_SEMINARMAN_CALC_NET_BUTTON') ?>" onclick="calc_netto(4);" />
  <input class="calc_input" type="button" value="<?php echo JText::_('COM_SEMINARMAN_CALC_CLOSE') ?>" onclick="hide_calc(4);" />
</li>
		<li>
			<label for="price5"><?php echo '5. ' . JText::_('COM_SEMINARMAN_PRICE') ?> (<?php $params = JComponentHelper::getParams( 'com_seminarman' ); echo $params->get( 'currency' ); ?>)</label>
			<input class="inputbox" <?php echo $readonly_price; ?> type="text" name="price5" id="price5" size="10" maxlength="20" value="<?php echo $this->row->price5; ?>" />
		    <?php echo $apply_5_discount; ?>
		    <input class="calculator" type="button" value="<?php echo JText::_('COM_SEMINARMAN_CALC_BUTTON') ?>" onclick="show_calculator(5);" />
		</li>
<li id="netto_rechner5" style="clear: left; background: #ddd; margin-left: 110px; padding: 10px; overflow: hidden; display: none;">
  <?php echo JText::sprintf('COM_SEMINARMAN_CALC_DESC', $this->row->vat); ?><br>
  <input class="calc_input"  type="text" name="bruttopreis5" id="bruttopreis5" size="10" maxlength="20" value="" />
  <input class="calc_input" type="button" value="<?php echo JText::_('COM_SEMINARMAN_CALC_NET_BUTTON') ?>" onclick="calc_netto(5);" />
  <input class="calc_input" type="button" value="<?php echo JText::_('COM_SEMINARMAN_CALC_CLOSE') ?>" onclick="hide_calc(5);" />
</li>
		<li>
			<label for="vat"><?php echo JText::_('COM_SEMINARMAN_VAT') ?></label>
			<input class="inputbox" <?php echo $readonly; ?> type="text" name="vat" id="vat" size="10" maxlength="20" value="<?php echo $this->row->vat; ?>%" />
		</li>
		<li>
			<label for="price_type"><?php echo JText::_('COM_SEMINARMAN_PRICE_TYPE') ?></label>
			<?php echo $this->lists['price_type']; ?>
		</li>
		
		<?php 
			$dispatcher=JDispatcher::getInstance();
		 	JPluginHelper::importPlugin('seminarman');
		 	$html_tmpl=$dispatcher->trigger('onEditAddPriceInfo',array($this->row));  // we need the course id
		 	if(isset($html_tmpl) && !empty($html_tmpl)) echo $html_tmpl[0];
		?>	
	</ul>
	</fieldset>  
	</div>     
        <?php echo JHtml::_('bootstrap.endTab'); ?>
 
        <?php echo JHtml::_('bootstrap.addTab', 'smanTab', 'documents', JText::_('COM_SEMINARMAN_DOCUMENTS', true)); ?>
<div class="width-40 fltlft">
	<fieldset class="adminform" <?php if ($params->get('edit_course_image') == -1) echo $hidden; ?>>
		<legend><?php echo JText::_('COM_SEMINARMAN_IMAGE'); ?></legend>
		<?php
		$course_image_folder = $params->get('image_path', 'images');
		$course_image_path = JURI::root().$params->get('image_path', 'images'). '/';
		if (isset($this->row->image) && !empty($this->row->image)): 
		?>
			<img id="courseimg" src="<?php echo $course_image_path . $this->row->image; ?>">
			<span <?php if ($params->get('edit_course_image') <= 0) echo $hidden; ?>>
			<input type="checkbox" name="image_remove" value="1" /><?php echo '<span class="readonly">'. JText::_('COM_SEMINARMAN_REMOVE') . '</span>'; ?>
			</span>
		<?php endif; ?>
		<div class="clear"></div>
		<?php if (($course_image_folder == "images") || (substr($course_image_folder, 0, 7) == "images/") || (substr($course_image_folder, 0, 7) == "images\\")): ?>
		    <?php 
		       // the given image upload path matches joomla media folder, use the joomla media modal box instead of the old upload field 
		       if ($course_image_folder == "images") { 
		       	 $media_path = "";
		       } else {
		       	 $media_path = str_replace("images/", "", $course_image_folder);
		       	 $media_path = str_replace("images\\", "", $media_path);
		       }
		       
		    ?>
<div <?php if ($params->get('edit_course_image') <= 0) echo $hidden; ?> class="controls"><div class="input-prepend input-append">
  <input type="text" size="40" class="input-small field-media-input hasTipImgpath" title="" readonly="readonly" value="<?php echo (isset($this->row->image) && !empty($this->row->image))?$this->row->image:''; ?>" id="image_media" name="image_media">
  <a rel="{handler: 'iframe', size: {x: 800, y: 500}}" href="index.php?option=com_media&view=images&tmpl=component&asset=com_seminarman&fieldid=image_media&folder=<?php echo $media_path; ?>" title="<?php echo JText::_('COM_SEMINARMAN_SELECT'); ?>" class="modal btn">
 <?php echo JText::_('COM_SEMINARMAN_SELECT'); ?></a>
 <script>
 function jInsertFieldValue(value, id) {
		var $ = jQuery.noConflict();
		var old_value = $("#" + id).val();
		if (old_value != value) {
			var $elem = $("#" + id);
			$elem.val(baseName(value));
		}
	}
 function baseName(str)
 {
       var file_name = str.split(/(\\|\/)/g).pop();
       var file_path = str.replace(file_name, "");
	   var image_folder = "<?php echo $course_image_folder; ?>";
	   if (image_folder.substr(image_folder.length - 1) != "/") {
          var image_folder_to_sub = image_folder + "/";
	   } else {
		  var image_folder_to_sub = image_folder;
	   }
	   if (file_path.length >= image_folder_to_sub.length) {
	      var base = str.replace(image_folder_to_sub, "");
	      return base;
	   } else {
          var sec_file_path = file_path.split("/").length - 1;
          var sec_folder_path = image_folder_to_sub.split("/").length - 1;
          var diff = sec_folder_path - sec_file_path;
          var rel_path = "";
          for (i = 0; i < diff; i++) {
        	  rel_path += rel_path + "../";
          }
          var base = rel_path + file_name;
          return base;
	   }
 }
 </script>
</div></div> 
		<?php else: ?>
		    <?php // the given image upload path doesn't match joomla media folder, use the old upload field ?>
		    <input <?php if ($params->get('edit_course_image') <= 0) echo $hidden; ?> class="text_area" type="file" name="image" id="image" size="32" maxlength="250" value=""/>	
		<?php endif; ?>	
	</fieldset>
	
	<fieldset class="adminform" <?php if ($params->get('edit_course_documents') == -1) echo $hidden; ?>>
		<legend><?php echo JText::_('COM_SEMINARMAN_FILES'); ?></legend>
		<div id="filelist"><?php echo $this->fileselect; ?></div>
		<div class="button2-left btn" <?php if ($params->get('edit_course_documents') <= 0) echo $hidden; ?>>
			<div class="blank">
				<a class="modal" title="<?php echo JText::_('COM_SEMINARMAN_SELECT'); ?>" href="<?php echo $this->linkfsel; ?>" rel="{handler: 'iframe', size: {x: 850, y: 450}}"><?php echo JText::_('COM_SEMINARMAN_SELECT'); ?></a>
			</div>
		</div>
		<div class="button2-left btn" <?php if ($params->get('edit_course_documents') <= 0) echo $hidden; ?>>
			<div class="blank">
				<a title="<?php echo JText::_('COM_SEMINARMAN_UPLOAD'); ?>" href="<?php	echo "index.php?option=com_seminarman&view=filemanager";?>" target="_blank"><?php echo JText::_('COM_SEMINARMAN_UPLOAD'); ?></a>
			</div>
		</div>
	</fieldset>
	
	<?php
	    if ($params->get('ics_file_name') == 0) {
	        $filename = "ical_course_" . $this->row->id . ".ics";
	    } else {
	        $filename = JFile::makeSafe(str_replace(array('Ä','Ö','Ü','ä','ö','ü','ß'), array('Ae','Oe','Ue','ae','oe','ue','ss'), html_entity_decode($this->row->title, ENT_QUOTES)) . '_' . $this->row->id . '.ics');
	    	$filename = str_replace(' ', '_', $filename);
	    }
	    $icsfile_path = JPATH_ROOT.DS.$params->get('invoice_save_dir').DS . $filename;
	    if (JFile::exists($icsfile_path)): 
	?>
	<br />
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_SEMINARMAN_ICS_FILE'); ?></legend>
		<?php echo '<a href="index.php?option=com_seminarman&controller=courses&task=edit&cid[]='.$this->row->id.'&layout=ics"><span class="icon-calendar"></span> ' . $filename . '</a>'; ?>
	</fieldset>
	<?php endif; ?>
</div> 

<div class="width-60 fltlft">
	<fieldset class="adminform" <?php if ($params->get('edit_course_serial_certificate') == -1) echo $hidden; ?>>
		<legend><?php echo JText::_('COM_SEMINARMAN_SERIAL_CERTIFICATE_TEMPLATE') . " <small>(" . JText::_('COM_SEMINARMAN_CERTIFICATE_PARAMS_SUPPORT') . ")</small>"; ?></legend>
		<?php 
		if(JHTMLSeminarman::UserIsCourseManager() || ($params->get('edit_course_serial_certificate') == 1)){
		echo $this->editor->display('certificate_text', $this->row->certificate_text, '100%', '250', '50', '15', true); 
		}else{
		echo '<div style="display: none;">' . $this->editor->display('certificate_text', $this->row->certificate_text, '100%', '250', '50', '15', false) . '</div>';
		echo html_entity_decode('<div>' . $this->row->certificate_text . '</div>');		
		}
		?>
	</fieldset>
</div>       
        <?php echo JHtml::_('bootstrap.endTab'); ?>  
        
        <?php
            if(JHTMLSeminarman::UserIsCourseManager() || ($params->get('edit_course_params') == 1)){
                echo JHtml::_('bootstrap.addTab', 'smanTab', 'publish_infos', JText::_('COM_SEMINARMAN_PUBLISH_INFORMATION', true));
            } else {
            	echo JHtml::_('bootstrap.addTab', 'smanTab', 'publish_infos', '');
            }
        ?>

<fieldset class="adminform">
<legend><?php echo JText::_('COM_SEMINARMAN_PUBLISH_INFORMATION'); ?></legend>

	<div class="row-fluid form-horizontal-desktop">
	<div class="span6">

<div class="well">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_SEMINARMAN_DETAILS'); ?></legend>
		<?php 
		$fields = $this->form->getFieldset('details');
		foreach( $fields AS $field ){
			echo $field->label;
			echo $field->input;
		}
		?>		
	</fieldset>	
</div>

<div class="well">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_SEMINARMAN_PARAMETERS'); ?></legend>
        <?php 
        $fields = $this->form->getFieldset('basic');
        foreach( $fields AS $field ){
        	echo $field->label;
        	echo $field->input;
        }        
        ?>
	</fieldset>	
</div>

<div class="well">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_SEMINARMAN_METADATA_INFORMATION'); ?></legend>
		<?php 
		$fields = $this->form->getFieldset('metadata');
		foreach( $fields AS $field ){
			echo $field->label;
			echo $field->input;
		}		
		?>
	</fieldset>	
</div>	
	
	</div>
	<div class="span6">
	
<div class="well">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_SEMINARMAN_PARAMETERS_ADVANCED'); ?></legend>
		<?php 
		$fields = $this->form->getFieldset('advanced');
		foreach( $fields AS $field ){
			echo $field->label;
			echo $field->input;
		}		
		?>
	</fieldset>	
</div>	
	
	</div>
	</div>
</fieldset>	       
        <?php echo JHtml::_('bootstrap.endTab'); ?> 
        
		<?php
		    if(JHTMLSeminarman::UserIsCourseManager() || ($params->get('edit_course_custom_fields') == 1)){
		        echo JHtml::_('bootstrap.addTab', 'smanTab', 'custom_fields', JText::_('COM_SEMINARMAN_COURSE_CUSTOM_FLDS', true));
		    } else {
		    	echo JHtml::_('bootstrap.addTab', 'smanTab', 'custom_fields', '');
		    }
		?>

<div class="width-40 fltlft">
	<fieldset class="adminform">
	<legend><?php echo JText::_( 'COM_SEMINARMAN_COURSE_CUSTOM_FLDS' ); ?></legend>
	<ul class="adminformlist">
		<li>
			<label for="custom_fld_1"><?php echo $this->row->custom1_lbl; ?></label>
			<input class="inputbox" type="text" name="params[custom_fld_1_value]" id="custom_fld_1" size="32" maxlength="254" value="<?php echo $this->row->custom1_val; ?>" />
		</li>
		<li>
			<label for="custom_fld_2"><?php echo $this->row->custom2_lbl; ?></label>
			<input class="inputbox" type="text" name="params[custom_fld_2_value]" id="custom_fld_2" size="32" maxlength="254" value="<?php echo $this->row->custom2_val; ?>" />
		</li>
		<li>
			<label for="custom_fld_3"><?php echo $this->row->custom3_lbl; ?></label>
			<input class="inputbox" type="text" name="params[custom_fld_3_value]" id="custom_fld_3" size="32" maxlength="254" value="<?php echo $this->row->custom3_val; ?>" />
		</li>
		<li>
			<label for="custom_fld_4"><?php echo $this->row->custom4_lbl; ?></label>
			<input class="inputbox" type="text" name="params[custom_fld_4_value]" id="custom_fld_4" size="32" maxlength="254" value="<?php echo $this->row->custom4_val; ?>" />
		</li>
		<li>
			<label for="custom_fld_5"><?php echo $this->row->custom5_lbl; ?></label>
			<input class="inputbox" type="text" name="params[custom_fld_5_value]" id="custom_fld_5" size="32" maxlength="254" value="<?php echo $this->row->custom5_val; ?>" />
		</li>	
	</ul>
	</fieldset>
</div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>       
		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
</form>

<script type="text/javascript">
  setStartDateAll();
  setFinishDateAll();

  <?php if (!JHTMLSeminarman::UserIsCourseManager() && $params->get('edit_course_prices') == 0): ?>
      document.getElementById('prices').style.display = "none";
  <?php endif; ?>

  <?php if (!JHTMLSeminarman::UserIsCourseManager() && $params->get('edit_course_params') == 0): ?>
      document.getElementById('publish_infos').style.display = "none";
  <?php endif; ?>  

  <?php if (!JHTMLSeminarman::UserIsCourseManager() && $params->get('edit_course_custom_fields') == 0): ?>
      document.getElementById('custom_fields').style.display = "none";
  <?php endif; ?>    
  
</script>

<?php JHTML::_('behavior.keepalive'); ?>