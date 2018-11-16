<form action="<?php echo $this->action ?>" method="post" name="adminForm" id="adminForm" class="form-validate"  enctype="multipart/form-data">
<?php 
// load voting system if available
$dispatcher=JDispatcher::getInstance();
JPluginHelper::importPlugin('seminarman');
$html_tmpl=$dispatcher->trigger('onGetVotingInfoForTemplateForm',array($this->template));  // we need the template id
if (isset($html_tmpl) && !empty($html_tmpl)) echo $html_tmpl[0];
?> 
    <p><?php echo JText::_('COM_SEMINARMAN_LST_OF_SALES_PROSPECTS_DESC'); ?></p> 
    <table class="ccontentTable paramlist">
        <tbody>
        	<tr class="int_std"><td colspan="2"><h2><?php echo JText::_('COM_SEMINARMAN_ATTENDEE_DATA');?></h2></td></tr>
<?php if ($this->params->get('enable_num_of_attendees')): ?>
            <tr class="int_std">
                <td class="paramlist_key vtop">
                    <label for="jformattendees"><?php echo JText::_('COM_SEMINARMAN_NR_ATTENDEES'); ?><span class="required">*</span></label>
                </td>
                <td class="paramlist_value vtop">
                    <input title="<?php echo JText::_('COM_SEMINARMAN_NR_ATTENDEES') . '::' . JText::_('COM_SEMINARMAN_FILL_IN_DETAILS'); ?>" class="hasTip tipRight inputbox required" type="text" id="attendees" name="attendees" size="5" maxlength="3" value="<?php echo $this->escape($this->attendeedata->attendees); ?>" />
                </td>
            </tr>
<?php endif; ?>
            <tr class="int_std">
                <td class="paramlist_key vtop">
                    <label for="jformsalutation"><?php echo JText::_('COM_SEMINARMAN_SALUTATION'); ?><span class="required">*</span></label>
                </td>
                <td class="paramlist_value vtop">
                    <?php 
                      // echo $this->lists['salutation']; 
                    $sal = substr_replace($this->lists['salutation'], 'required ', strpos($this->lists['salutation'], 'class=', 0) + 7, 0);
                    if (isset($_POST['salutation'])) {
                    	$sal = substr_replace($sal, 'selected="selected" ', strpos($sal, 'value="' . $_POST['salutation'], 0), 0);
                    }
                    echo $sal;
                    ?>
                </td>
            </tr>
           <tr class="int_std">
                <td class="paramlist_key vtop">
                    <label for="title"><?php echo JText::_('COM_SEMINARMAN_TITLE'); ?></label>
                </td>
                <td class="paramlist_value vtop">
                    <input title="<?php echo JText::_('COM_SEMINARMAN_TITLE') . '::' . JText::_('COM_SEMINARMAN_FILL_IN_DETAILS'); ?>" class="hasTip tipRight inputbox" type="text" id="title" name="title" size="50" maxlength="250" value="<?php echo $this->escape($this->attendeedata->title); ?>" />
                </td>
            </tr>
            <tr class="int_std">
                <td class="paramlist_key vtop">
                    <label for="jformfirstname"><?php echo JText::_('COM_SEMINARMAN_FIRST_NAME'); ?><span class="required">*</span></label>
                </td>
                <td class="paramlist_value vtop">
                    <input title="<?php echo JText::_('COM_SEMINARMAN_FIRST_NAME') . '::' . JText::_('COM_SEMINARMAN_FILL_IN_DETAILS'); ?>" class="hasTip tipRight inputbox required" type="text" id="first_name" name="first_name" size="50" maxlength="250" value="<?php echo $this->escape($this->attendeedata->first_name); ?>" />
                </td>
            </tr>
            <tr class="int_std">
                <td class="paramlist_key vtop">
                    <label for="jformlastname"><?php echo JText::_('COM_SEMINARMAN_LAST_NAME'); ?><span class="required">*</span></label>
                </td>
                <td class="paramlist_value vtop">
                    <input title="<?php echo JText::_('COM_SEMINARMAN_LAST_NAME') . '::' . JText::_('COM_SEMINARMAN_FILL_IN_DETAILS'); ?>" class="hasTip tipRight inputbox required" type="text" id="last_name" name="last_name" size="50" maxlength="250" value="<?php echo $this->escape($this->attendeedata->last_name); ?>" />
                </td>
            </tr>
            <tr class="int_std">
                <td class="paramlist_key vtop">
                    <label for="jformemail"><?php echo JText::_('COM_SEMINARMAN_EMAIL'); ?><span class="required">*</span></label>
                </td>
                <td class="paramlist_value vtop">
                    <input title="<?php echo JText::_('COM_SEMINARMAN_EMAIL') . '::' . JText::_('COM_SEMINARMAN_FILL_IN_DETAILS'); ?>" class="hasTip tipRight inputbox validate-email required" type="text" id="cm_email" name="email" size="50" maxlength="100" value="<?php echo $this->escape($this->attendeedata->email); ?>" />
                </td>
            </tr>
            <tr class="int_std">
                <td class="paramlist_key vtop">
                    <label for="jformemailconfirm"><?php echo JText::_('COM_SEMINARMAN_EMAIL_CONFIRM'); ?><span class="required">*</span></label>
                </td>
                <td class="paramlist_value vtop">
                    <input title="<?php echo JText::_('COM_SEMINARMAN_EMAIL_CONFIRM') . '::' . JText::_('COM_SEMINARMAN_FILL_IN_DETAILS'); ?>" class="hasTip tipRight inputbox validate-email required" type="text" id="cm_email_confirm" name="email_confirm" size="50" maxlength="100" value="<?php echo $this->escape($this->attendeedata->email); ?>" />
                </td>
            </tr>

    <?php
    // custom fields
    foreach ($this->fields as $name => $this->fieldGroup){
    if ($name != 'ungrouped'){?>
    <tr><td colspan="2"><h2><?php echo JText::_($name); ?></h2></td></tr>
    <?php
    }

    ?>

            <?php

            foreach ($this->fieldGroup as $f){
            $f = JArrayHelper::toObject ($f);
            $f->value = $this->escape($f->value);

            ?>
            <tr>
                <td class="paramlist_key vtop" id="lblfield<?php echo $f->id;?>"><label for="lblfield<?php echo $f->id;?>">
                  <?php 
                    if ($f->required == 1){
                  	  echo JText::_($f->name).'<span class="required">*</span>';
                    } else {
                      echo JText::_($f->name);
                    }
                  ?>
                </label></td>
                <td class="paramlist_value vtop"><?php echo SeminarmanCustomfieldsLibrary::getFieldHTML($f , ''); ?></td>
            </tr>
            <?php
            }

            ?>
    <?php
    }

    ?>
    		<tr>
    			<td></td>
    			<td><p style="float: right;"><span class="required">*</span> <?php echo JText::_('COM_SEMINARMAN_REQUIRED_VALUES'); ?></p></td>
    		</tr>
        </tbody>
    </table>

    <div>
        <?php if (($this->attendeedata->id > 0) && (!$this->attendeedata->jusertype)){ ?>
        <button type="button" class="button validate" disabled="disabled">
            <?php echo JText::_('COM_SEMINARMAN_ALREADY_ON_LIST');?>
        </button>
        <?php }else{ ?>
        <button id="submitSeminarman" type="button" class="button validate" onclick="submitbuttonSeminarman('save')">
            <?php echo JText::_('COM_SEMINARMAN_JOIN_LIST');?>
        </button>
        <?php } ?>
    </div>

    <input type="hidden" name="template_id" value="<?php echo $this->template->id;?>" />
    <input type="hidden" name="option" value="com_seminarman" />
    <input type="hidden" name="controller" value="salesprospect" />
    <input type="hidden" name="task" value="" />
    <?php

    echo JHTML::_('form.token');

    ?>
</form>
<script type="text/javascript">
if (typeof jQuery == 'undefined') {
HTMLElement.prototype.removeClass = function(remove) {
    var newClassName = "";
    var i;
    var classes = this.className.split(" ");
    for(i = 0; i < classes.length; i++) {
        if(classes[i] !== remove) {
            newClassName += classes[i] + " ";
        }
    }
    this.className = newClassName;
}
}

var show_styled_tip = <?php echo $this->params->get('show_tooltip_in_form'); ?>;

if (show_styled_tip == 0) { 
var list = document.getElementById("course_appform").getElementsByClassName("hasTip");
var listlen= list.length;
for (var i = 0; i < listlen; i++) {
    list[i].removeAttribute("title");
}
for (var i = 0; i < listlen; i++) {
    list[0].removeClass("hasTip");
}
}
</script>