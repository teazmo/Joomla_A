<?php
/**
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011-2014 Open Source Group GmbH www.osg-gmbh.de
* @website http://www.profinvent.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 2 of the License, or
* any later version.
* 
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
**/

// no direct access
defined('_JEXEC') or die;
$script = '		
Joomla.submitbutton = function(pressbutton) {
	var form = document.adminForm;
	if (pressbutton == \'cancel\') {
		Joomla.submitform(pressbutton);
		return;
	}';

// do field validation
$script .= '
	if (!checkTemplate()) {
		if (form.jform_subject.value == ""){
			alert("'.JText::_('COM_SEMINARMAN_MAIL_PLEASE_FILL_IN_THE_SUBJECT', true).'");
		} else if (getSelectedValue(\'adminForm\',\'jform[receipt][]\') < 0){
			alert("'.JText::_('COM_SEMINARMAN_MAIL_PLEASE_SELECT_A_GROUP', true).'");
		} else if (form.jform_message.value == ""){
			alert("'.JText::_('COM_SEMINARMAN_MAIL_PLEASE_FILL_IN_THE_MESSAGE', true).'");
		} else {
			Joomla.submitform(pressbutton);
		}
	} else {
		Joomla.submitform(pressbutton);
	}
}
				
function checkTemplate() {
	var temp = document.adminForm.getElementById("jformemail_template");
	var ops = document.adminForm.getElementsByClassName("optional");
				
	switch (temp.options[temp.selectedIndex].value) {
		case "0":
			for(var i = 0; i < ops.length; i++) {
				switch(ops[i].tagName) {
					case "li":
						ops[i].style.display = "list-item";
						break;
					case "ul":
					default:
						ops[i].style.display = "block";
					}
			}
			return false;
			break;
		default:
			for(var i = 0; i < ops.length; i++) {
				ops[i].style.display = "none";
			}
			return true;
	}
}
				
function checkSettings(obj) {
	switch(obj.id) {
		case "jform_bcc":
			var hide_uno = document.adminForm.getElementById("jform_invoice");
			var hide_dos = document.adminForm.getElementById("jform_certificate");
		    var hide_tres = document.adminForm.getElementById("jform_ics");
			var hide_cuatro = document.adminForm.getElementById("jform_addattach");
			var hide_cinco = document.adminForm.getElementById("jform_course_docs");
			break;
		case "jform_invoice":
			var hide_uno = document.adminForm.getElementById("jform_bcc");	
			break;
		case "jform_certificate":
			var hide_uno = document.adminForm.getElementById("jform_bcc");	
			break;
		case "jform_ics":
			var hide_uno = document.adminForm.getElementById("jform_bcc");	
			break;
		case "jform_addattach":
			var hide_uno = document.adminForm.getElementById("jform_bcc");	
			break;
		case "jform_course_docs":
			var hide_uno = document.adminForm.getElementById("jform_bcc");	
			break;
	}	
	if(obj.checked === true) {
		hide_uno.checked = false;
		if (hide_dos) hide_dos.checked = false;
		if (hide_tres) hide_tres.checked = false;
		if (hide_cuatro) hide_cuatro.checked = false;
		if (hide_cinco) hide_cinco.checked = false;
	}
}
					
function addAttach() {
	var itm = document.getElementsByClassName("file-upload")[0];
	var cln = itm.cloneNode(true);
	cln.value = "";
	itm.parentNode.appendChild(cln);
}
					
window.onload = function() {
	checkTemplate();
}';

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');

JFactory::getDocument()->addScriptDeclaration($script);

$app = JFactory::getApplication();
?>

<form action="<?php echo JRoute::_('index.php?option=com_seminarman&view=mail'); ?>" name="adminForm" method="post" id="adminForm" enctype="multipart/form-data">

	<div class="width-30 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_SEMINARMAN_MAIL_SETTINGS'); ?></legend>
			<ul class="adminformlist">
			<li><?php echo $this->getModel()->form->getLabel('email_template'); ?>
				<?php echo JHTMLSeminarman::getSelectEmailTemplate('jform[email_template]', 0, 'style="width: 17em;" onchange="checkTemplate();"', JText::_('COM_SEMINARMAN_MAIL_TEMPLATE_NONE')); ?></li>

			<li><?php echo $this->getModel()->form->getLabel('receipt'); ?>
			<?php echo $this->getModel()->buildReceiptList(); ?></li>

			<li class="optional"><?php echo $this->getModel()->form->getLabel('bcc'); ?>
			<?php echo substr_replace($this->getModel()->form->getInput('bcc'), ' onClick="checkSettings(this);"', 7, 0); ?></li>
			
			<li><?php echo $this->getModel()->form->getLabel('cc'); ?>
			<?php echo $this->getModel()->form->getInput('cc'); ?></li>
			
			<li class="optional"><?php echo $this->getModel()->form->getLabel('mode'); ?>
			<?php echo $this->getModel()->form->getInput('mode'); ?></li>
			
			<li><?php echo $this->getModel()->form->getLabel('invoice'); ?>
			<?php echo substr_replace($this->getModel()->form->getInput('invoice'), ' onClick="checkSettings(this);"', 7, 0); ?></li>
			
			<li><?php echo $this->getModel()->form->getLabel('certificate'); ?>
			<?php echo substr_replace($this->getModel()->form->getInput('certificate'), ' onClick="checkSettings(this);"', 7, 0); ?></li>
			
			<li><?php echo $this->getModel()->form->getLabel('ics'); ?>
			<?php echo substr_replace($this->getModel()->form->getInput('ics'), ' onClick="checkSettings(this);"', 7, 0); ?></li>
			
			<li><?php echo $this->getModel()->form->getLabel('addattach'); ?>
			<?php echo substr_replace($this->getModel()->form->getInput('addattach'), ' onClick="checkSettings(this);"', 7, 0); ?></li>
			
			<li><?php echo $this->getModel()->form->getLabel('course_docs'); ?>
			<?php echo substr_replace($this->getModel()->form->getInput('course_docs'), ' onClick="checkSettings(this);"', 7, 0); ?></li>
			</ul>
		</fieldset>
		<br/>
		<fieldset class="actions">
			<legend><?php echo JText::_('COM_SEMINARMAN_UPLOAD'); ?> [ <?php echo JText::_('COM_SEMINARMAN_MAX'); ?>&nbsp;<?php echo ($this->params->get('upload_maxsize') / 1000000); ?>M ]</legend>
			<input type="file" class="file-upload" onchange="addAttach()" name="attach[]" />
			<span id="upload-clear"></span>
		</fieldset>
	</div>

	<div class="width-70 fltrt">
		<fieldset class="adminform optional">
			<legend><?php echo JText::_('COM_SEMINARMAN_MSG_BLOCK'); ?></legend>
			<ul class="adminformlist">
			<li><?php echo $this->getModel()->form->getLabel('subject'); ?>
			<?php
                 if (empty($this->getModel()->subject)) {
			         echo $this->getModel()->form->getInput('subject');
                 } else {
                 	 echo $this->getModel()->form->getInput('subject', '', $this->getModel()->subject);
                 }
			?>
			</li>

			<li><?php echo $this->getModel()->form->getLabel('message'); ?>
			<?php echo $this->getModel()->form->getInput('message'); ?></li>
			</ul>
		</fieldset>
   <input type="hidden" name="option" value="com_seminarman" />
   <input type="hidden" name="task" value="" />
   <input type="hidden" name="controller" value="mail" />

		<?php echo JHtml::_('form.token'); ?>
	</div>
	<div class="clr"></div>
</form>