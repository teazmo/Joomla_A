<?php
/**
*
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011 Open Source Group GmbH www.osg-gmbh.de
* @website http://www.profinvent.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

$mainframe = JFactory::getApplication();
$params = $mainframe->getParams('com_seminarman');
$Itemid = JRequest::getInt('Itemid');

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
        $checked = '';
        
        if ($f->published == 1 && $f->required == 1 && !($f->type == "checkboxtos" && $this->params->get('enable_payment_overview') == 1)) {
            switch ($f->type) {
                case 'checkbox':
                    // one of the checkboxes contains invalid value -> error
                    // none of the checkboxes selected -> error
                    // important: form element is an array!
                    $js .= '
        fields = form.elements["field' . $f->id . '[]"];
                
        if (Object.prototype.toString.call(fields) != "[object RadioNodeList]") {
                field = fields;
                fields = new Array(field);
        }
                
        for (i = 0; i < fields.length; i++) {
            if(fields[i] && (fields[i].value == "")) {
                if(fields[i].className.indexOf("invalid") < 0) {
                    fields[i].className += " invalid";
                }
                return alert("' . JText::sprintf('COM_SEMINARMAN_FIELD_N_CONTAINS_IMPROPER_VALUES', addslashes($f->name)) . '");
            }
        }';
                    $js .= '
        var selected = false;
        for (i = 0; i < fields.length; i++) {
            if(fields[i] && fields[i].checked) {
                selected = true;
            }
        }
        if (selected == false) {
            return alert("' . JText::sprintf('COM_SEMINARMAN_MISSING', addslashes($f->name)) . '");
        }';                            
                    break;
                case 'radio':
                    // one of the radio boxes contains invalid value -> error
                    // none of the radio boxes selected -> error
                    // important: form element is NOT an array!
                    $js .= '
        fields = form.elements["field' . $f->id . '"];
                
        for (i = 0; i < fields.length; i++) {
            if(fields[i] && (fields[i].value == "")) {
                if(fields[i].className.indexOf("invalid") < 0) {
                    fields[i].className += " invalid";
                }
                return alert("' . JText::sprintf('COM_SEMINARMAN_FIELD_N_CONTAINS_IMPROPER_VALUES', addslashes($f->name)) . '");
            }
        }';
                    $js .= '
        var selected = false;
        for (i = 0; i < fields.length; i++) {
            if(fields[i] && fields[i].checked) {
                selected = true;
            }
        }
        if (selected == false) {
            return alert("' . JText::sprintf('COM_SEMINARMAN_MISSING', addslashes($f->name)) . '");
        }';    
                    break;
                case 'checkboxtos':
                    // checktos field has its own warning message
            $js .= '
        fields = document.getElementById("field' . $f->id . '")
        if(fields.getAttribute("aria-invalid") == "true" || !fields.checked) {
            if(fields.className.indexOf("invalid") < 0) {
                fields.className += " invalid";
            }
            return alert("' . JText::sprintf('COM_SEMINARMAN_ACCEPT_TOS', addslashes($f->name)) . '");
        }';
                    break;
                default:
                    // other fields such as text field, date field, drop down box, list field (incl. multi select), URL field...
                    $js .= '
        fields = document.getElementById("field' . $f->id . '");
        if(fields.getAttribute("aria-invalid") == "true" || fields.value == ""' . $checked . ') {
            if(fields.className.indexOf("invalid") < 0) {
                fields.className += " invalid";
            }
            return alert("' . JText::sprintf('COM_SEMINARMAN_MISSING', addslashes($f->name)) . '");
        }';
            }
        }
    }
}
?>

<script type="text/javascript">
function submitbuttonSeminarman(task)
{
	var form = document.adminForm;
	var fields;
	/* we are going to call formvalidator twice, by the first call we get all invalid fields at once
	   and they will be marked */
	if (document.formvalidator.isValid(form)) {
		if (form.cm_email.value != form.cm_email_confirm.value) {
			if(form.cm_email.className.indexOf("invalid") < 0) {
				form.cm_email.className += " invalid";
			}
			if(form.cm_email_confirm.className.indexOf("invalid") < 0) {
				form.cm_email_confirm.className += " invalid";
			}
			return alert("<?php echo JText::_('COM_SEMINARMAN_MISSING_EMAIL_CONFIRM', true); ?>");
		}
	}

	if (form.first_name.value == "") {
		if(form.first_name.className.indexOf("invalid") < 0) {
			form.first_name.className += " invalid";
		}
		return alert("<?php echo JText::sprintf('COM_SEMINARMAN_MISSING', JText::_('COM_SEMINARMAN_FIRST_NAME', true)); ?>");
	}
	if (form.last_name.value == "") {
		if(form.last_name.className.indexOf("invalid") < 0) {
			form.last_name.className += " invalid";
		}
		return alert("<?php echo JText::sprintf('COM_SEMINARMAN_MISSING', JText::_('COM_SEMINARMAN_LAST_NAME', true)); ?>");
	}
	if (form.salutation.value == '') {
		if(form.salutation.className.indexOf("invalid") < 0) {
			form.salutation.className += " invalid";
		}
		return alert("<?php echo JText::sprintf('COM_SEMINARMAN_MISSING', JText::_('COM_SEMINARMAN_SALUTATION', true)); ?>");
	}
	if (form.cm_email.value == "") {
		if(form.cm_email.className.indexOf("invalid") < 0) {
			form.cm_email.className += " invalid";
		}
		return alert("<?php echo JText::sprintf('COM_SEMINARMAN_MISSING', JText::_('COM_SEMINARMAN_EMAIL', true)); ?>");
	}
	if (form.cm_email_confirm.value == "") {
		if(form.cm_email_confirm.className.indexOf("invalid") < 0) {
			form.cm_email_confirm.className += " invalid";
		}
		return alert("<?php echo JText::sprintf('COM_SEMINARMAN_MISSING', JText::_('COM_SEMINARMAN_EMAIL_CONFIRM', true)); ?>");
	}
	/* some fields such as checkbox, radio box, tos field can not be checked by formvalidator,
	   therefore this part has to be placed outside of formvalidaor before submit */
	<?php echo $js; ?>

	if (document.formvalidator.isValid(form)) {
		if(document.adminForm.submitSeminarman) {
			document.adminForm.submitSeminarman.disabled = true;
		}
	    Joomla.submitform( task );
	} else {
		return alert("<?php echo JText::_('COM_SEMINARMAN_FIELD_CONTAINS_IMPROPER_VALUES'); ?>");
	}
};
</script>

<div id="seminarman" class="seminarman">

    <p class="buttons"><?php echo seminarman_html::printbutton($this->print_link, $this->params); echo seminarman_html::mailbutton('templates', $this->params, $this->template->categoryslug, $this->template->slug); ?></p>

<?php 
    if ($this->params->get('show_page_heading', 0)) {
    	$page_heading = trim($this->params->get('page_heading'));
        if (!empty($page_heading)) {
            echo '<h1 class="componentheading">' . $page_heading . '</h1>';
        } else {
        	echo '<h1 class="componentheading">' . $this->template->title . '</h1>';
        }
    }
    
    if (($this->params->get('enable_component_pathway') == 2) && (!empty($this->all_parents))) {
    	$path_way = SMANFunctions::getCatsPath($this->all_parents, 'unit', $Itemid, $this->template->title);
    	echo $path_way;
    }
?>    

    <h2 class="seminarman course<?php echo $this->template->id; ?>"><?php echo $this->escape($this->template->title); ?></h2>

    <div class="course_details floattext">

        <dl class="course_info_left floattext">
<?php if ($this->params->get('show_modify_date')): ?>
            <dt class="modified"><?php echo JText::_('COM_SEMINARMAN_LAST_REVISED') . ':'; ?></dt>
            <dd class="modified"><div><?php echo $this->template->modified ? JFactory::getDate($this->template->modified)->format("j. F Y") : JText::_('COM_SEMINARMAN_NEVER'); ?></div></dd>
<?php endif; ?>
            <dt class="reference"><?php echo JText::_('COM_SEMINARMAN_COURSE_CODE') . ':'; ?></dt>
            <dd class="reference"><div><?php if ($this->template->code<>"") echo $this->template->code; ?></div></dd>
        </dl>

        <dl class="course_info_right floattext">
        	<dd>
        	<span class="centered">
<?php if ( $this->params->get('image') ) : ?>
                <img src="<?php $baseurl = JURI::base(); echo $baseurl; ?>/images/<?php echo $this->params->get('image'); ?>" alt="<?php echo $this->params->get('image'); ?>">
<?php endif; ?>
            </span>
            </dd>
<?php 
// load voting system if available
$dispatcher=JDispatcher::getInstance();
JPluginHelper::importPlugin('seminarman');
$html_tmpl=$dispatcher->trigger('onGetVotingInfoForTemplate',array($this->template));  // we need the template id
if (isset($html_tmpl) && !empty($html_tmpl)) echo $html_tmpl[0];
?>  
        </dl>

    </div>

    <div class="course_details floattext">

        <dl class="course_info_left floattext">
<?php if ($this->params->get('show_price_template')): ?>
            <dt class="price"><?php echo JText::_('COM_SEMINARMAN_PRICE') . ':'; ?></dt>
            <dd class="price"><div>
            <?php
            $display_free_charge = $this->params->get('display_free_charge');
            if (!empty($display_free_charge) && ($this->template->price == 0)) {
            	echo JText::_($display_free_charge);
            } else { 
                echo $this->escape($this->template->price) . ' ' . $this->escape($this->template->currency_price) . ' ' . $this->escape($this->template->price_type).' '; echo ($this->params->get('show_gross_price') == 1) ? '('.JText::_('COM_SEMINARMAN_WITH_VAT').')' : '('.JText::_('COM_SEMINARMAN_WITHOUT_VAT').')'; 
            }
            ?>
            </div></dd>
<?php endif; ?>            
<?php if ($this->params->get('show_location')): ?>
            <dt class="location"><?php echo JText::_('COM_SEMINARMAN_LOCATION') . ':'; ?></dt>
            <dd class="location"><div><?php echo empty($this->template->location) ? JText::_('COM_SEMINARMAN_NOT_SPECIFIED') : $this->template->location; ?></div></dd>
<?php endif; ?>
       <!--  <dt class="start_date"><?php echo JText::_('COM_SEMINARMAN_DATES') . ':'; ?></dt>
            <dd class="start_date"><div><?php echo JText::_('COM_SEMINARMAN_NOT_SCHEDULED'); ?></div></dd> -->
        </dl>

        <dl class="course_info_right floattext">
<?php if ($this->params->get('show_group')): ?>
            <dt class="group"><?php echo JText::_('COM_SEMINARMAN_GROUP') . ':'; ?></dt>
            <dd class="group"><div><?php echo empty($this->template->cgroup) ? JText::_('COM_SEMINARMAN_NOT_SPECIFIED') : $this->template->cgroup; ?></div></dd>
<?php endif; ?>
<?php if ($this->params->get('show_experience_level')): ?>
            <dt class="level"><?php echo JText::_('COM_SEMINARMAN_LEVEL') . ':'; ?></dt>
            <dd class="level"><div><?php $level = $this->escape($this->template->level); echo empty($level) ? JText::_('COM_SEMINARMAN_NOT_SPECIFIED') : $level; ?></div></dd>
<?php endif; ?>
        </dl>

    </div>

    <h2 class="description"><?php echo JText::_('COM_SEMINARMAN_DESCRIPTION'); ?></h2>
    <div class="description course_text"><?php echo $this->template->text; ?></div>

    <!--files-->
    <?php

    $n = count($this->files);
    $i = 0;
if ($n != 0):

    ?>
    <h2 class="seminarman course_files"><?php echo JText::_('COM_SEMINARMAN_FILES_FOR_DOWNLOAD'); ?></h2>

    <div class="filelist">
        <?php
        foreach ($this->files as $file):
       		echo JHTML::image($file->icon, '') . ' ';
        ?>
        	<strong><a href="<?php echo JRoute::_('index.php?option=com_seminarman&fileid=' . $file->fileid . '&task=download' . '&Itemid=' . $Itemid); ?>"><?php echo $file->altname ? $this->escape($file->altname) : $this->escape($file->filename); ?></a></strong>
        <?php

        $i++;
        if ($i != $n):
        echo ',';
        endif;
        endforeach;

        ?>
    </div>
<?php 
endif;
?>
    <br />  
    <!--application form-->
    <h2 class="componentheading<?php echo $this->params->get('pageclass_sfx'); ?>" id="appform"><?php echo JText::_('COM_SEMINARMAN_ON_LST_OF_SALES_PROSPECTS'); ?></h2>
    <div class="course_applicationform" id="course_appform">
	<?php
	switch ($params->get('enable_bookings')) {
		case 3:
			echo $this->loadTemplate('salesprospectform');
			break;
		case 2:
			echo $this->loadTemplate('salesprospectform');
			break;
		case 1:
			if ($this->user->get('guest'))
				echo JText::_('COM_SEMINARMAN_PLEASE_LOGIN_FIRST') .'.';
			else
				echo  $this->loadTemplate('salesprospectform');
			break;
		default:
			echo JText::_('COM_SEMINARMAN_BOOKINGS_DISABLED') .'.';
	}
	?>
    </div>

<?php 
// load voting system if available
$dispatcher=JDispatcher::getInstance();
JPluginHelper::importPlugin('seminarman');
$html_tmpl=$dispatcher->trigger('onGetVotingJSForTemplate',array($this->template));  // we need the template id
if (isset($html_tmpl) && !empty($html_tmpl)) echo $html_tmpl[0];
?> 

    <!--categories-->
    <?php

    if ($this->params->get('show_categories')):

    ?>
    <h2 class="seminarman course_categories"><?php echo JText::_('COM_SEMINARMAN_CATEGORY'); ?></h2>
    <?php

    $n = count($this->categories);
    $i = 0;

    ?>
    <div class="categorylist">
        <?php

        foreach ($this->categories as $category):

        ?>
        <strong><a href="<?php echo JRoute::_('index.php?option=com_seminarman&view=category&cid=' . $category->slug . '&Itemid=' . $Itemid); ?>"><?php echo $this->escape($category->title); ?></a></strong>
        <?php

        $i++;
        if ($i != $n):
        echo ',';
        endif;
        endforeach;

        ?>
    </div>
    <?php

    endif;

    ?>

    <!--tags-->
    <?php

    if ($this->params->get('show_tags')):
    $n = count($this->tags);
    $i = 0;
    if ($n != 0):

    ?>
    <h2 class="seminarman course_tags"><?php echo JText::_('COM_SEMINARMAN_ASSIGNED_TAGS'); ?></h2>
    <div class="taglist">
<?php foreach ($this->tags as $tag): ?>
        <strong><a href="<?php echo JRoute::_('index.php?option=com_seminarman&view=tags&id=' . $tag->slug . '&Itemid=' . $Itemid); ?>"><?php echo $this->escape($tag->name); ?></a></strong>
        <?php $i++; if ($i != $n) echo ','; ?>
<?php endforeach; ?>
    </div>
    <?php

    endif;
    endif;
    
    ?>
    
 <?php 
 if ($this->params->get('show_button_back')) {
 	if(isset($_SERVER['HTTP_REFERER']) && (!empty($_SERVER['HTTP_REFERER']))){
     $go_back = htmlspecialchars($_SERVER['HTTP_REFERER']);
     echo "<br /><div class='centered'><a class='button' href='".$go_back."'>".JText::_('COM_SEMINARMAN_BACK')."</a></div>";
 	}
 }
 ?>

</div>
