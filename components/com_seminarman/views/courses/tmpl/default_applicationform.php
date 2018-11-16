<?php 
/**
*
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011-2016 Open Source Group GmbH www.osg-gmbh.de
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

$html_prices = JHTMLSeminarman::get_price_view($this->course->id, '', $this->vmlink);

if ($this->params->get('enable_payment_overview') == 1) {
	if (($this->params->get('trigger_virtuemart') == 1) && !is_null($this->vmlink)) {
		$next_action = 'save';
	} else {
	    $next_action = 'cart';
	}
} else {
	$next_action = 'save';
}

?>
<form action="<?php echo $this->action ?>" method="post" name="adminForm" id="adminForm" class="form-validate"  enctype="multipart/form-data">
<?php if ($this->show_application_form == 2) echo '<br><span style="color: red;">'.JText::_('COM_SEMINARMAN_INFO_WAITINGLIST').'</span><br>'; ?>
    <table class="ccontentTable paramlist">
        <tbody>
            <tr class="sman_prices_lbl"><td colspan="2"><h3 class="underline"><?php echo JText::_('COM_SEMINARMAN_PRICE_SINGLE');?></h3></td></tr>
            <tr class="sman_prices_detail">
                <td class="paramlist_key vtop">
                    <label for="jformprice"><?php echo JText::_('COM_SEMINARMAN_PRICE_BOOKING'); ?><span class="required">*</span></label>
                </td>
                <td class="paramlist_value vtop">
<fieldset id="booking_price" class="radio" style="margin: 0 0 10px; padding: 0;">
<?php echo $html_prices; ?>
</fieldset>
                </td>    
            </tr>        
            
<?php 
	$dispatcher=JDispatcher::getInstance();
	JPluginHelper::importPlugin('seminarman');
	$html_tmpl=$dispatcher->trigger('onGetAddPriceInfo',array($this->course));  // we need the course id
	if(isset($html_tmpl) && !empty($html_tmpl)) echo $html_tmpl[0];
?>             
                
        	<tr><td colspan="2"><h3 class="underline"><?php echo JText::_('COM_SEMINARMAN_ATTENDEE_DATA');?></h3></td></tr>
 <?php if ($this->params->get('enable_num_of_attendees')): ?>
            <tr>
                <td class="paramlist_key vtop">
                    <label for="jformattendees"><?php echo JText::_('COM_SEMINARMAN_NR_ATTENDEES'); ?><span class="required">*</span></label>
                </td>
                <td class="paramlist_value vtop">
                    <input id='ccb' title="<?php echo JText::_('COM_SEMINARMAN_NR_ATTENDEES') . '::' . JText::_('COM_SEMINARMAN_FILL_IN_DETAILS'); ?>" class="hasTip tipRight inputbox required validate-numeric" type="text" id="attendees" name="attendees" size="5" maxlength="3" value="<?php echo isset($_POST['attendees']) ? $_POST['attendees'] : $this->escape($this->attendeedata->attendees); ?>" />
                </td>
            </tr>
<?php endif; ?>
            <tr>
                <td class="paramlist_key vtop">
                    <label for="jformsalutation"><?php echo JText::_('COM_SEMINARMAN_SALUTATION'); ?><span class="required">*</span></label>
                </td>
                <td class="paramlist_value vtop">
                    <?php
                    $sal = substr_replace($this->lists['salutation'], 'required ', strpos($this->lists['salutation'], 'class=', 0) + 7, 0);
                    if (isset($_POST['salutation'])) {
                    	$sal = substr_replace($sal, 'selected="selected" ', strpos($sal, 'value="' . $_POST['salutation'], 0), 0);
                    }
                    echo $sal;
                    ?>
                </td>
            </tr>
            <tr>
                <td class="paramlist_key vtop">
                    <label for="title"><?php echo JText::_('COM_SEMINARMAN_TITLE'); ?></label>
                </td>
                <td class="paramlist_value vtop">
                    <input title="<?php echo JText::_('COM_SEMINARMAN_TITLE') . '::' . JText::_('COM_SEMINARMAN_FILL_IN_DETAILS'); ?>" class="hasTip tipRight inputbox" type="text" id="title" name="title" size="20" maxlength="250" value="<?php echo isset($_POST['title']) ? $_POST['title'] : $this->escape($this->attendeedata->title); ?>" />
                </td>
            </tr>
            <tr>
                <td class="paramlist_key vtop">
                    <label for="jformfirstname"><?php echo JText::_('COM_SEMINARMAN_FIRST_NAME'); ?><span class="required">*</span></label>
                </td>
                <td class="paramlist_value vtop">
                    <input title="<?php echo JText::_('COM_SEMINARMAN_FIRST_NAME') . '::' . JText::_('COM_SEMINARMAN_FILL_IN_DETAILS'); ?>" class="hasTip tipRight inputbox required" type="text" id="first_name" name="first_name" size="50" maxlength="250" value="<?php echo isset($_POST['first_name']) ? $_POST['first_name'] : $this->escape($this->attendeedata->first_name); ?>" />
                </td>
            </tr>
            <tr>
                <td class="paramlist_key vtop">
                    <label for="jformlastname"><?php echo JText::_('COM_SEMINARMAN_LAST_NAME'); ?><span class="required">*</span></label>
                </td>
                <td class="paramlist_value vtop">
                    <input title="<?php echo JText::_('COM_SEMINARMAN_LAST_NAME') . '::' . JText::_('COM_SEMINARMAN_FILL_IN_DETAILS'); ?>" class="hasTip tipRight inputbox required" type="text" id="last_name" name="last_name" size="50" maxlength="250" value="<?php echo isset($_POST['last_name']) ? $_POST['last_name'] : $this->escape($this->attendeedata->last_name); ?>" />
                </td>
            </tr>
            <tr>
                <td class="paramlist_key vtop">
                    <label for="jformemail"><?php echo JText::_('COM_SEMINARMAN_EMAIL'); ?><span class="required">*</span></label>
                </td>
                <td class="paramlist_value vtop">
                    <input title="<?php echo JText::_('COM_SEMINARMAN_EMAIL') . '::' . JText::_('COM_SEMINARMAN_FILL_IN_DETAILS'); ?>" class="hasTip tipRight inputbox validate-email required" type="text" id="cm_email" name="email" size="50" maxlength="100" value="<?php echo isset($_POST['email']) ? $_POST['email'] : $this->escape($this->attendeedata->email); ?>" />
                </td>
            </tr>
            <tr>
                <td class="paramlist_key vtop">
                    <label for="jformemailconfirm"><?php echo JText::_('COM_SEMINARMAN_EMAIL_CONFIRM'); ?><span class="required">*</span></label>
                </td>
                <td class="paramlist_value vtop">
                    <input title="<?php echo JText::_('COM_SEMINARMAN_EMAIL_CONFIRM') . '::' . JText::_('COM_SEMINARMAN_FILL_IN_DETAILS'); ?>" class="hasTip tipRight inputbox validate-email required" type="text" id="cm_email_confirm" name="email_confirm" size="50" maxlength="100" value="<?php echo isset($_POST['email']) ? $_POST['email'] : $this->escape($this->attendeedata->email); ?>" />
                </td>
            </tr>
            
            <?php if ($this->params->get('booking_email_cc')): ?>
            <tr>
                <td class="paramlist_key vtop">
                    <label for="jformbookingemailcc"><?php echo JText::_('COM_SEMINARMAN_BOOKING_EMAIL_CC'); ?></label>
                </td>
                <td class="paramlist_value vtop">
                    <input title="<?php echo JText::_('COM_SEMINARMAN_BOOKING_EMAIL_CC') . '::' . JText::_('COM_SEMINARMAN_FILL_IN_DETAILS'); ?>" class="hasTip tipRight inputbox validate-email" type="text" id="booking_email_cc" name="booking_email_cc" size="50" maxlength="100" value="<?php echo isset($_POST['booking_email_cc']) ? $_POST['booking_email_cc'] : $this->escape($this->attendeedata->booking_email_cc); ?>" />
                </td>
            </tr>            
            <?php endif; ?>
            
    <?php
    // custom fields
    foreach ($this->fields as $name => $this->fieldGroup){
    if ($name != 'ungrouped'){?>
    <tr><td colspan="2"><h3 class="underline"><?php echo JText::_($name);?></h3></td></tr>
    <?php
    }

    ?>

            <?php
            foreach ($this->fieldGroup as $f){
            $f = JArrayHelper::toObject ($f);
            
            if (isset($_POST['field' . $f->id])) {
				$fp = $_POST['field' . $f->id];
				if (is_array($fp)) {
					switch ($f->type) {
						case "time":
							$f->value = implode(':', $fp);
							break;
						case "url":
							$f->value = implode('', $fp);
							break;
						default:
							$f->value = implode(',', $fp);
					}
				} else {
					$f->value = $fp;
				}
            } else {
				$f->value = $this->escape($f->value);
			}

            ?>
            <tr>
                <td class="paramlist_key vtop" id="lblfield<?php echo $f->id;?>"><label for="lblfield<?php echo $f->id;?>">
                  <?php 
                    if ($f->type != "checkboxtos") { 
                    	if ($f->required == 1) {
                    		echo JText::_($f->name) . '<span class="required">*</span>'; 
                    	} else {
                    		echo JText::_($f->name);
                    	}
                    }
                    ?>
                </label></td>
                <td class="paramlist_value vtop">
                    <?php 
                        if (($f->type == "checkboxtos") && ($this->params->get('enable_payment_overview') == 1)) {
                        	if (($this->params->get('trigger_virtuemart') == 1) && !is_null($this->vmlink)) {
                        		echo SeminarmanCustomfieldsLibrary::getFieldHTML($f , '');
                        	}
                        } else { 
                        	echo SeminarmanCustomfieldsLibrary::getFieldHTML($f , ''); 
                        }
                    ?>
                </td>
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
	    <?php 
	    if ( $this->course->status == 2 ) { ?>
	        <button type="button" class="btn btn-primary validate" disabled="disabled">
	        <?php echo JText::_('COM_SEMINARMAN_ALREADY_BOOKED'); ?>
        </button>
        <?php } else if (!$this->params->get('enable_multiple_bookings_per_user') && ($this->attendeedata->id > 0) && (!$this->attendeedata->jusertype)){ ?>
        <button type="button" class="btn btn-primary validate" disabled="disabled">
        	<?php echo JText::_('COM_SEMINARMAN_ALREADY_BOOKED'); ?>
        </button>
        <?php }else{ ?>
        <button id="submitSeminarman" type="button" class="btn btn-primary validate" onclick="
        if ( document.getElementById('cca') && document.getElementById('ccb') ) {
	        if ( parseInt(document.getElementById('cca').innerHTML) < parseInt(document.getElementById('ccb').value)) {
        		if ( <?php echo $this->params->get('waitinglist_active') ?> == 1) {
        			var wl = confirm( '<?php echo JText::_( 'COM_SEMINARMAN_BOOKING_GF1_WAITINGLIST' ) ?>' + ' (' + document.getElementById('ccb').value + ') ' + '<?php echo JText::_( 'COM_SEMINARMAN_BOOKING_GF2_WAITINGLIST' ) ?>' + ' (' + document.getElementById('cca').innerHTML + ')' + '<?php echo JText::_( 'COM_SEMINARMAN_BOOKING_GF3_WAITINGLIST' ) ?>' + '\n\n' + '<?php echo JText::_( 'COM_SEMINARMAN_BOOKING_GF4_WAITINGLIST' ) ?>' + '\n' + '<?php echo JText::_( 'COM_SEMINARMAN_BOOKING_GF5_WAITINGLIST' ) ?>' + '\n' + '<?php echo JText::_( 'COM_SEMINARMAN_BOOKING_GF6_WAITINGLIST' ) ?>' + document.getElementById('ccb').value + '<?php echo JText::_( 'COM_SEMINARMAN_BOOKING_GF7_WAITINGLIST' ) ?>');
					if ( wl ) {
						document.getElementById( 'waitinglist' ).value = 1;
						document.getElementById( 'status' ).value = 4;
						submitbuttonSeminarman('<?php echo $next_action; ?>');
					}
        		}
        		else {
        			alert( '<?php echo JText::_( 'COM_SEMINARMAN_BOOKING_GREATER_FREESPACES2' ) ?>' + ' (' + document.getElementById('ccb').value + ') ' + '<?php echo JText::_( 'COM_SEMINARMAN_BOOKING_GREATER_FREESPACES3' ) ?>' + ' (' + document.getElementById('cca').innerHTML + ').' );
        		}
        	}
        	else {
        		    submitbuttonSeminarman('<?php echo $next_action; ?>')
        	}
        } 
        else {
        	submitbuttonSeminarman('<?php echo $next_action; ?>')
        }
        ">
<?php
    if (($this->params->get('trigger_virtuemart') == 1) && !is_null($this->vmlink)) {
    	echo JText::_('COM_SEMINARMAN_BOOKING_IN_VM');
    } else {	
        if ($next_action == 'save') {
    	  echo ($this->show_application_form == 2)?JText::_('COM_SEMINARMAN_SUBMIT_WL'):JText::_('COM_SEMINARMAN_SUBMIT');
        } else {
          echo ($this->show_application_form == 2)?JText::_('COM_SEMINARMAN_SUBMIT_WL'):JText::_('COM_SEMINARMAN_NEXT');
        }
    }
?>
        </button>
        <?php } ?>
    </div>

    <input type="hidden" id="status" name="status" value="<?php echo ($this->show_application_form == 2)?'4':'0'; ?>" />
    <input type="hidden" name="course_id" value="<?php echo $this->course->id;?>" />
    <input type="hidden" name="option" value="com_seminarman" />
    <input type="hidden" name="controller" value="application" />
    <input type="hidden" id="waitinglist" name="waitinglist" value="<?php echo ($this->show_application_form == 2)?'1':'0'; ?>" />
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