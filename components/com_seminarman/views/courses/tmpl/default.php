<?php
/**
*
* @Copyright Copyright (C) 2018 www.osg-gmbh.de. All rights reserved.
* Copyright (C) 2011-2015 Open Source Group GmbH www.osg-gmbh.de
* @website http://www.profinvent.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
// JHtml::register('behavior.tooltip', $this->clau_tooltip());
$mainframe = JFactory::getApplication();
$params = $mainframe->getParams('com_seminarman');

if (JVERSION >= 3.4) {
    JHtml::_('behavior.formvalidator');
} else {
    JHTML::_('behavior.formvalidation');
}

$Itemid = JRequest::getInt('Itemid');

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
                
        if ((Object.prototype.toString.call(fields) != "[object RadioNodeList]") && (Object.prototype.toString.call(fields) != "[object HTMLCollection]")) {
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
        fields = document.getElementById("field' . $f->id . '");
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
$course_attribs = new JRegistry();
$course_attribs->loadString($this->course->attribs);
$show_course_price = $course_attribs->get('show_price');
$site_timezone = SeminarmanFunctions::getSiteTimezone();
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

    <p class="buttons"><?php echo seminarman_html::favouritesbutton($this->params); echo seminarman_html::printbutton($this->print_link, $this->params); echo seminarman_html::mailbutton('courses', $this->params, $this->course->categoryslug, $this->course->slug); ?></p>

<?php
    if ($this->params->get('show_page_heading', 0)) {
    	$page_heading = trim($this->params->get('page_heading'));
        if (!empty($page_heading)) {
            echo '<h1 class="componentheading">' . $page_heading . '</h1>';
        } else {
        	echo '<h1 class="componentheading">' . $this->course->title . '</h1>';
        }
    }
    
    if (($this->params->get('enable_component_pathway') == 2) && (!empty($this->all_parents))) {
    	$path_way = SMANFunctions::getCatsPath($this->all_parents, 'unit', $Itemid, $this->course->title);
    	echo $path_way;
    }
?>

    <h2 class="seminarman course<?php echo $this->course->id; ?>"><?php echo $this->escape($this->course->title); ?></h2>

    <div class="course_details floattext">
	    <div class="course_info_left floattext">
	
	        <dl class="floattext">
	<?php if ($this->params->get('show_start_date') || is_null($this->params->get('show_start_date'))): ?>
	            <dt class="start_date"><?php echo JText::_('COM_SEMINARMAN_START_DATE') . ':'; ?></dt>
	            <dd class="start_date"><div><?php echo $this->course->start; ?></div></dd>
	<?php endif; ?>
	<?php if ($this->params->get('show_finish_date') || is_null($this->params->get('show_finish_date'))): ?>
	            <dt class="finish_date"><?php echo JText::_('COM_SEMINARMAN_FINISH_DATE') . ':'; ?></dt>
	            <dd class="finish_date"><div><?php echo $this->course->finish; ?></div></dd>
	<?php endif; ?>
	<?php if ($this->params->get('show_booking_deadline')): ?>
	            <dt class="booking_deadline"><?php echo JText::_('COM_SEMINARMAN_BOOKING_DEADLINE') . ':'; ?></dt>
	            <dd class="booking_deadline"><div><?php echo $this->course->deadline ?></div></dd>
	<?php endif; ?>
	<?php if ($this->params->get('show_modify_date')): ?>
	            <dt class="modified"><?php echo JText::_('COM_SEMINARMAN_LAST_REVISED') . ':'; ?></dt>
	            <dd class="modified"><div><?php echo $this->course->modified; ?></div></dd>
	<?php endif; ?>
	            <dt class="reference"><?php echo JText::_('COM_SEMINARMAN_COURSE_CODE') . ':'; ?></dt>
	            <dd class="reference"><div><?php if ($this->course->code<>"") echo $this->course->code; else echo "-"; ?></div></dd>
	<?php if ($this->params->get('show_hits')):?>
	            <dt class="hits"><?php echo JText::_('COM_SEMINARMAN_HITS') . ':'; ?></dt>
	            <dd class="hits"><div><?php echo $this->course->hits; ?></div></dd>
	<?php endif; ?>
	<?php if ($this->params->get('show_favourites')): ?>
	            <dt class="favourites"><?php echo JText::_('COM_SEMINARMAN_FAVOURED') . ':'; ?></dt>
	            <dd class="favourites"><div><?php echo $this->favourites . ' ' . seminarman_html::favoure($this->course, $this->params, $this->favoured); ?></div></dd>
	<?php endif; ?>
	        </dl>
	        <dl class="floattext">
	<?php if ($show_course_price !== 0): ?>
	            <dt class="price"><?php echo JText::_('COM_SEMINARMAN_PRICE') . ':'; ?></dt>
	            <dd class="price"><div>
	<?php echo $this->course->price_detail; ?>
	            </div></dd>
    <?php 
    $dispatcher=JDispatcher::getInstance();
    JPluginHelper::importPlugin('seminarman');
    $html_tmpl=$dispatcher->trigger('onGetAddPriceInfoForCourse',array($this->course));  // we need the course id
    if (isset($html_tmpl) && !empty($html_tmpl)) echo $html_tmpl[0];    
    ?>
	<?php endif; ?>
	<?php if ($this->params->get('show_location')): ?>
	            <dt class="location"><?php echo JText::_('COM_SEMINARMAN_LOCATION') . ':'; ?></dt>
	            <dd class="location"><div>
	            <?php
	            if ( empty( $this->course->location ) ) {
	                echo JText::_('COM_SEMINARMAN_NOT_SPECIFIED');
	            }
	            else {
	                if ( empty( $this->course->url ) || $this->course->url == "http://" ) {
	                    echo $this->course->location;
	                }
	                else {?>
	                    <a href='<?php echo $this->course->url; ?>' target="_blank"><?php echo $this->course->location; ?></a>
	                <?php
	                }
	            }
	            ?>
	            </div></dd>
	<?php endif; ?>
	<?php if ($this->params->get('show_group')): ?>
	            <dt class="group"><?php echo JText::_('COM_SEMINARMAN_GROUP') . ':'; ?></dt>
	            <dd class="group"><div><?php echo empty($this->course->cgroup) ? JText::_('COM_SEMINARMAN_NOT_SPECIFIED') : $this->course->cgroup; ?></div></dd>
	<?php endif;?>
	        </dl>
	<?php if ($this->course->custom_available): ?>
	        <dl class="floattext">
	        <?php if ($this->course->display_custom_1 && !empty($this->course->custom1_val)): ?>
	            <dt class="custom_fld_1"><?php echo $this->course->custom1_lbl . ':'; ?></dt>
	            <dd class="custom_fld_1"><div><?php echo $this->course->custom1_val; ?></div></dd>	        
	        <?php endif; ?>
	        <?php if ($this->course->display_custom_2 && !empty($this->course->custom2_val)): ?>
	            <dt class="custom_fld_2"><?php echo $this->course->custom2_lbl . ':'; ?></dt>
	            <dd class="custom_fld_2"><div><?php echo $this->course->custom2_val; ?></div></dd>	        
	        <?php endif; ?>
	        <?php if ($this->course->display_custom_3 && !empty($this->course->custom3_val)): ?>
	            <dt class="custom_fld_3"><?php echo $this->course->custom3_lbl . ':'; ?></dt>
	            <dd class="custom_fld_3"><div><?php echo $this->course->custom3_val; ?></div></dd>	        
	        <?php endif; ?>
	        <?php if ($this->course->display_custom_4 && !empty($this->course->custom4_val)): ?>
	            <dt class="custom_fld_4"><?php echo $this->course->custom4_lbl . ':'; ?></dt>
	            <dd class="custom_fld_4"><div><?php echo $this->course->custom4_val; ?></div></dd>	        
	        <?php endif; ?>
	        <?php if ($this->course->display_custom_5 && !empty($this->course->custom5_val)): ?>
	            <dt class="custom_fld_5"><?php echo $this->course->custom5_lbl . ':'; ?></dt>
	            <dd class="custom_fld_5"><div><?php echo $this->course->custom5_val; ?></div></dd>	        
	        <?php endif; ?>
	        </dl>
    <?php endif; ?>
	    </div>
	    <div class="course_info_right floattext">
	        <dl class="floattext">
	<?php if ($this->params->get('show_image_in_detail')): ?>        
	<?php if ( $this->course->image <> '' ) : ?>
	        	<dd class="centered">
	                <img src="<?php $baseurl = JURI::base(); echo $baseurl . $this->params->get('image_path', 'images') . '/' . $this->course->image; ?>" alt="">
	            </dd>
	<?php endif; ?>
	<?php endif; ?>
	<?php if ($this->course->bookable): ?>
	            <dd class="centered">
	            	<a class="btn btn-primary" onclick="setVisibility();" href="<?php echo JURI::getInstance()->toString(); ?>#course_appform"><?php echo JText::_('COM_SEMINARMAN_BOOK_COURSE'); ?></a>
	            </dd>
	<?php 
	elseif ( $this->show_application_form == 2  && $this->course->state != 2 ) : ?>
	            <dd class="centered">
	            	<a class="btn btn-primary" onclick="setVisibility();" href="<?php echo JURI::getInstance()->toString(); ?>#course_appform"><?php echo JText::_('COM_SEMINARMAN_APPLY_WL_COURSE'); ?></a>
	            </dd>
	<?php endif;
	if ($this->params->get('show_experience_level')): ?>
	            <dt class="level"><?php echo JText::_('COM_SEMINARMAN_LEVEL') . ':'; ?></dt>
	            <dd class="level"><div><?php $level = $this->escape($this->course->level); echo empty($level) ? JText::_('COM_SEMINARMAN_NOT_SPECIFIED') : $level; ?></div></dd>
	<?php endif; ?>
	<?php if ($this->params->get('show_capacity')): ?>
	            <dt class="capacity"><?php
	            
	            	if ($this->params->get('current_capacity') && $this->params->get('show_capacity') > 1)
	            		echo JText::_('COM_SEMINARMAN_FREE_SEATS') . ':';
	            	else
	            		echo JText::_('COM_SEMINARMAN_SEATS') . ':';
	
	                ?></dt>
	            <dd class="capacity"><div>
	                <?php echo $this->course->capacity; ?>
	            </div></dd>
	<?php else: ?>
	            <div style="display: none;"><?php echo $this->course->capacity; ?></div>
	<?php endif; ?>
	<?php if ($this->params->get('show_tutor')): ?>
	            <dt class="tutor"><?php echo JText::_('COM_SEMINARMAN_TUTOR') . ':'; ?></dt>
				<dd class="tutor"><div>
				<?php
				    foreach($this->course->tutors as $tutor_key => $tutor_content) {
						if ($tutor_content['tutor_published']) {
                            echo '<a href="' . JRoute::_('index.php?option=com_seminarman&view=tutor&id=' . $tutor_key . '&Itemid=' . $Itemid) . '">' . $tutor_content['tutor_display'] . '</a><br />';
                        } else {
 							echo $tutor_content['tutor_display'] . '<br />';
						}
					}
				?>
				</div></dd>                
	<?php endif; ?>
	            <dt class="author"></dt>
	            <dd class="author"><div></div></dd>
	        </dl>
	    </div>
	<?php if (($this->course->count_sessions > 0) &&  ($this->params->get('show_sessions'))) :?>
	    <div class="course_detail floattext">
	
	        <table class="proc100">
	            <tr>
	                <td class="sectiontableheader centered proc20 hepix20"><?php echo JText::_('COM_SEMINARMAN_DATE'); ?></td>
	                <td class="sectiontableheader centered proc20 hepix20"><?php echo JText::_('COM_SEMINARMAN_START_TIME'); ?></td>
	                <td class="sectiontableheader centered proc20 hepix20"><?php echo JText::_('COM_SEMINARMAN_FINISH_TIME'); ?></td>
	                <td class="sectiontableheader centered proc20 hepix20"><?php echo JText::_('COM_SEMINARMAN_DURATION'); ?></td>
	                <td class="sectiontableheader centered proc20 hepix20"><?php echo JText::_('COM_SEMINARMAN_ROOM'); ?></td>
	            </tr>
	
	            <?php foreach ($this->course_sessions as $course_session):
	            echo '<tr>';
	            echo '<td class="centered">' . $course_session->session_date . '</td>';
	            // fix for 24:00:00 (illegal time colock)
	            if ($course_session->start_time == '24:00:00') $course_session->start_time = '23:59:59';
	            if ($course_session->finish_time == '24:00:00') $course_session->finish_time = '23:59:59';
	            echo '<td class="centered">' . date('H:i', strtotime($course_session->start_time)) . '</td>';
	            echo '<td class="centered">' . date('H:i', strtotime($course_session->finish_time)) . '</td>';
	            echo '<td class="centered">' . $course_session->duration . '</td>';
	            echo '<td class="centered">' . $course_session->session_location . '</td>';
	            echo '</tr>';
	            endforeach;
	            ?>
	
	        </table>
	
	    </div>
	<?php endif; ?>
    </div>

    <h3 class="description underline"><?php echo JText::_('COM_SEMINARMAN_DESCRIPTION'); ?></h3>
    <div class="description course_text"><?php echo $this->course->text; ?></div>

    <!--files-->
    <?php

    $n = count($this->files);
    $i = 0;
if ($n != 0):

    ?>
    <h3 class="seminarman course_files underline"><?php echo JText::_('COM_SEMINARMAN_FILES_FOR_DOWNLOAD'); ?></h3>

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
<?php //  VM Product Details Button
//    if (!is_null($this->vmlink)) {
//	    echo '<p>' . JHTML::link($this->vmlink, 'zur Buchung in VirtueMart', array('title' => $this->course->title,'class' => 'product-details')) . '</p>';
//    }
?>

<?php
if ($this->course->bookable) {
	//include "default_loadappform.php";
	if ( $this->show_application_form == 1 )   {
        include "default_loadappform.php";
	}
}
else if (($this->params->get('show_booking_form'))&&($this->show_application_form == 2) && $this->course->state != 2 )  {
        include "default_loadappform.php";
	}
?>

    <!--categories-->
    <?php

    if ($this->params->get('show_categories')):

    ?>
    <h3 class="seminarman course_categories underline"><?php echo JText::_('COM_SEMINARMAN_CATEGORY'); ?></h3>
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
    <h3 class="seminarman course_tags underline"><?php echo JText::_('COM_SEMINARMAN_ASSIGNED_TAGS'); ?></h3>
    <div class="taglist">
<?php foreach ($this->tags as $tag): ?>
        <strong><a href="<?php echo JRoute::_('index.php?option=com_seminarman&view=tags&id=' . $tag->slug . '&Itemid=' . $Itemid); ?>"><?php echo $this->escape($tag->name); ?></a></strong>
        <?php $i++; if ($i != $n) echo ','; ?>
<?php endforeach; ?>
    </div>
    <?php

    endif;

    ?>
    <?php

    endif;

    ?>


    <!--comments-->
    <?php

    if ($this->params->get('show_jcomments') || $this->params->get('show_jomcomments')):

    ?>
    <div class="qf_comments">
        <?php

        if ($this->params->get('show_jcomments')):
        if (file_exists(JPATH_SITE . DS . 'components' . DS . 'com_jcomments' . DS .
        'jcomments.php')):
        require_once (JPATH_SITE . DS . 'components' . DS . 'com_jcomments' . DS .
        'jcomments.php');
        echo JComments::showComments($this->course->id, 'com_seminarman', $this->escape($this->course->title));
        endif;
        endif;

        if ($this->params->get('show_jomcomments')):
        if (file_exists(JPATH_SITE . DS . 'plugins' . DS . 'content' . DS .
        'jom_comment_bot.php')):
        require_once (JPATH_SITE . DS . 'plugins' . DS . 'content' . DS .
        'jom_comment_bot.php');
        echo jomcomment($this->course->id, 'com_seminarman');
        endif;
        endif;

        ?>
    </div>
    <?php

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
