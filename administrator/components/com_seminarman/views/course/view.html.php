<?php
/**
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011 Open Source Group GmbH www.osg-gmbh.de
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

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

class SeminarmanViewCourse extends JViewLegacy
{

    function display($tpl = null)
    {
        $mainframe = JFactory::getApplication();

        jimport('joomla.html.pane');

        $editor = JFactory::getEditor();
        $document = JFactory::getDocument();
        $user = JFactory::getUser();
        $db = JFactory::getDBO();
        $lang = JFactory::getLanguage();
        $jversion = new JVersion();
        $short_version = $jversion->getShortVersion();
        $params = JComponentHelper::getParams('com_seminarman');
        if (version_compare($short_version, "3.0", 'ge')) {
        	$pane = JPaneOSG::getInstance('sliders');
    	} else {
    		$pane = JPane::getInstance('sliders');
    	}

        JHTML::_('behavior.tooltip');

        $nullDate = $db->getNullDate();

        $cid = JRequest::getVar('cid');

        $document->addStyleSheet('components/com_seminarman/assets/css/seminarmanbackend.css');
        if (version_compare($short_version, "3.0", 'ge')) {
        	$document->addStyleSheet('components/com_seminarman/assets/css/seminarmanbackend_j3.x.css');
        }
        if ($lang->isRTL())
        {
            $document->addStyleSheet('components/com_seminarman/assets/css/seminarmanbackend_rtl.css');
        }

        require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS .
            'helpers' . DS . 'seminarman.php');

        JToolBarHelper::apply();
        JToolBarHelper::save();
        
        if ($cid && (JRequest::getVar('task')!='add')) {
            JToolBarHelper::title(JText::_('COM_SEMINARMAN_EDIT_COURSE'), 'courseedit');
            JToolBarHelper::save2copy('savecopy');
        } else {
            JToolBarHelper::title(JText::_('COM_SEMINARMAN_NEW_COURSE'), 'courseedit');
        }
        
        JToolBarHelper::cancel();

        $model = $this->getModel();
        $row = $this->get('Course');
        $files = $this->get('Files');
        $tags = $this->get('Tags');
        $usedtags = $model->getusedtags($row->id);
        $categories = seminarman_cats::getCategoriesTree(1);
        $selectedcats = $this->get('Catsselected');
        $disabled = 0;

        if ($row->id)
        {
            if ($model->isCheckedOut($user->get('id')))
            {
                JError::raiseWarning('SOME_ERROR_CODE', $row->title . ' ' . JText::_('COM_SEMINARMAN_RECORD_EDITED'));
                $mainframe->redirect('index.php?option=com_seminarman&view=categories');
            }
            $disabled = 1;
        }
        
        if ($this->getLayout() == 'ics')
        {
        	$this->_viewics($row->id, $row->title);
        	return;
        }

        JFilterOutput::objectHTMLSafe($row, ENT_QUOTES);

        $lists = array();
        
        if(JHTMLSeminarman::UserIsCourseManager()){
            $lists['catid'] = seminarman_cats::buildcatselect($categories, 'catid[]', $selectedcats, false,
                'multiple="multiple" size="8"');
        }else{
            if($params->get('edit_course_category') == 1) {
            	// editable
            	$lists['catid'] = seminarman_cats::buildcatselect($categories, 'catid[]', $selectedcats, false,
            			'multiple="multiple" size="8"');
            } elseif ($params->get('edit_course_category') == -1) {
            	// not editable, not visible
            	$lists['catid'] = seminarman_cats::buildcatselect($categories, 'catid[]', $selectedcats, false,
            			'multiple="multiple" size="8" style="display: none;"');            	
            } else {
            	// not editable, but visible
            	$lists['catid'] = seminarman_cats::buildcatselect($categories, 'catid_orig[]', $selectedcats, false,
            			'multiple="multiple" size="8" disabled') . seminarman_cats::buildcatselect($categories, 'catid[]', $selectedcats, false,
            			'multiple="multiple" size="8" style="display: none;"');            	
            }
        }

        if (version_compare($short_version, "3.0", 'ge')) {
        	$form = new JForm('params');
        	$form->loadFile( JPATH_COMPONENT . DS . 'models' . DS . 'course_j3.x.xml' );
        	$active = (intval($row->created_by) ? intval($row->created_by) : $user->get('id'));
        	$form->setValue('created_by', 'details', $active);
        	$form->setValue('created_by_alias', 'details', $row->created_by_alias);
        	
        	// seems useless
        	if ($row->created != "0000-00-00 00:00:00")
        		$form->setValue('created', NULL, JHTML::_('date', $row->created, JText::_('COM_SEMINARMAN_DATE_FORMAT1')));
        	else
        		$form->setValue('created', NULL, JHTML::_('date', NULL, JText::_('COM_SEMINARMAN_DATE_FORMAT1')));
        	
        	if (JHTML::_('date', $row->publish_up, 'Y') <= 1969 || $row->publish_up == $db->getNullDate() || $row->publish_up == '')
        		$form->setValue('publish_up', 'details', '');
        	else
        		// $form->setValue('publish_up', 'details', JHTML::_('date', $row->publish_up, JText::_('COM_SEMINARMAN_DATE_FORMAT1')));
        		// be careful here! joomla 3.x (core) calender field will format the datetime input again, so feed the original (utc) datetime here 
        		$form->setValue('publish_up', 'details', $row->publish_up);
        	 
        	if (JHTML::_('date', $row->publish_down, 'Y') <= 1969 || $row->publish_down == $db->getNullDate() || $row->publish_down == '' )
        		$form->setValue('publish_down', 'details', '');
        	else
        		// $form->setValue('publish_down', 'details', JHTML::_('date', $row->publish_down,  JText::_('COM_SEMINARMAN_DATE_FORMAT1')));
        		// be careful here! joomla 3.x (core) calender field will format the datetime input again, so feed the original (utc) datetime here
        		$form->setValue('publish_down', 'details', $row->publish_down);
        	
        	// $form->loadINI($row->attribs);
        	$data_course = array();
        	$data_attribs = new JRegistry();
        	$data_attribs->loadString($row->attribs);
        	$data_course['params'] = $data_attribs->toArray();
        	
        	$row->color = $data_attribs->get('color');
        	
    	// compatible for PHP 5.3/5.4
    	$custom_fld_1_title = $params->get('custom_fld_1_title');
    	$custom_fld_2_title = $params->get('custom_fld_2_title');
    	$custom_fld_3_title = $params->get('custom_fld_3_title');
    	$custom_fld_4_title = $params->get('custom_fld_4_title');
    	$custom_fld_5_title = $params->get('custom_fld_5_title');
    	$custom_fld_1_value = $data_attribs->get('custom_fld_1_value');
    	$custom_fld_2_value = $data_attribs->get('custom_fld_2_value');
    	$custom_fld_3_value = $data_attribs->get('custom_fld_3_value');
    	$custom_fld_4_value = $data_attribs->get('custom_fld_4_value');
    	$custom_fld_5_value = $data_attribs->get('custom_fld_5_value'); 
        	
        	$row->custom1_lbl = (!empty($custom_fld_1_title))?JText::_($custom_fld_1_title):JText::_('COM_SEMINARMAN_CUSTOM_FLD_1');
        	$row->custom2_lbl = (!empty($custom_fld_2_title))?JText::_($custom_fld_2_title):JText::_('COM_SEMINARMAN_CUSTOM_FLD_2');
        	$row->custom3_lbl = (!empty($custom_fld_3_title))?JText::_($custom_fld_3_title):JText::_('COM_SEMINARMAN_CUSTOM_FLD_3');
        	$row->custom4_lbl = (!empty($custom_fld_4_title))?JText::_($custom_fld_4_title):JText::_('COM_SEMINARMAN_CUSTOM_FLD_4');
        	$row->custom5_lbl = (!empty($custom_fld_5_title))?JText::_($custom_fld_5_title):JText::_('COM_SEMINARMAN_CUSTOM_FLD_5');
        	
        	$row->custom1_val = (!empty($custom_fld_1_value))?$custom_fld_1_value:'';
        	$row->custom2_val = (!empty($custom_fld_2_value))?$custom_fld_2_value:'';
        	$row->custom3_val = (!empty($custom_fld_3_value))?$custom_fld_3_value:'';
        	$row->custom4_val = (!empty($custom_fld_4_value))?$custom_fld_4_value:'';
        	$row->custom5_val = (!empty($custom_fld_5_value))?$custom_fld_5_value:'';
        	
        	$form->setValue('description', 'meta', $row->meta_description);
        	$form->setValue('keywords', 'meta', $row->meta_keywords);
        	// $form->loadINI($row->metadata);
        	$data_metadata = new JRegistry();
        	$data_metadata->loadString($row->metadata);
        	$data_course['meta'] = $data_metadata->toArray();
        	$form->bind($data_course);
        } else {
        	$form = new JParameter('', JPATH_COMPONENT . DS . 'models' . DS . 'course_j2.5.xml');
        	$active = (intval($row->created_by) ? intval($row->created_by) : $user->get('id'));
        	$form->set('created_by', $active);
        	$form->set('created_by_alias', $row->created_by_alias);
        	
        	// seems useless
        	if ($row->created != "0000-00-00 00:00:00")
        		$form->set('created', JHTML::_('date', $row->created, JText::_('COM_SEMINARMAN_DATE_FORMAT1')));
        	else
        		$form->set('created', JHTML::_('date', NULL, JText::_('COM_SEMINARMAN_DATE_FORMAT1')));
        	
        	if (JHTML::_('date', $row->publish_up, 'Y') <= 1969 || $row->publish_up == $db->getNullDate() || $row->publish_up == '')
        		$form->setValue('publish_up', JText::_('COM_SEMINARMAN_ALWAYS'));
        	else
        		// $form->setValue('publish_up', JHTML::_('date', $row->publish_up, JText::_('COM_SEMINARMAN_DATE_FORMAT1')));
        		// be careful here! joomla 2.5 (core) calender field will NOT format the datetime input to local, so feed the local datetime here
        		$form->setValue('publish_up', JHTML::_('date', $row->publish_up, 'Y-m-d H:i'));
        	 
        	if (JHTML::_('date', $row->publish_down, 'Y') <= 1969 || $row->publish_down == $db->getNullDate() || $row->publish_down == '' )
        		$form->set('publish_down', JText::_('COM_SEMINARMAN_NEVER'));
        	else
        		// $form->set('publish_down', JHTML::_('date', $row->publish_down,  JText::_('COM_SEMINARMAN_DATE_FORMAT1')));
        		// be careful here! joomla 2.5 (core) calender field will NOT format the datetime input to local, so feed the local datetime here
        		$form->setValue('publish_down', JHTML::_('date', $row->publish_down,  'Y-m-d H:i'));        	    
        	
        	$form->loadINI($row->attribs);
        	$form->set('description', $row->meta_description);
        	$form->set('keywords', $row->meta_keywords);
        	$form->loadINI($row->metadata);
        }

        $js = "
        		function qfSelectFile(id, file) {
        
        			var name = 'a_name'+id;
        			var ixid = 'a_id'+id;
        		    var attachchxid = 'fattach_chx'+id;
        		    var attachid = 'fattach'+id;
        		    var attachspnid = 'fattach_spn'+id;
        			var li = document.createElement('li');
        			var txt = document.createElement('input');
        			var hid = document.createElement('input');
        		
        		    var chx = document.createElement('input');
        		    var spn = document.createElement('span');
        		    var chxhid = document.createElement('input');
        		
        			var clrdiv = document.createElement('div');
        			clrdiv.setAttribute('class','clr');
        			txt.setAttribute('size','50');
        
        
        			var filelist = document.getElementById('filelist').getElementsByTagName('ul')[0];
        
        			var button = document.createElement('input');
        			button.type = 'button';
        			button.name = 'removebutton_'+id;
        			button.id = 'removebutton_'+id;
        			$(button).addEvent('click', function() { qfRemoveFile('" . JText::_('REMOVED') .
                    "', id ) });
        			button.value = '" . JText::_('COM_SEMINARMAN_REMOVE') . "';
        			
        			txt.type = 'text';
        			txt.disabled = 'disabled';
        			txt.id	= name;
        			txt.value	= file;
        
        			hid.type = 'hidden';
        			hid.name = 'fid[]';
        			hid.value = id;
        			hid.id = ixid;
        					
        		    chx.type = 'checkbox';
        			chx.value = '1';
        		    chx.id = attachchxid;
        			chx.onclick = function() {
                        toggle_email_attach_check(attachchxid, attachid)
                    };

        			chxhid.type = 'hidden';
        			chxhid.name = 'fattach[]';
        			chxhid.value = 0;
        			chxhid.id = attachid;
        					
        			spn.id = attachspnid;
        			spn.innerHTML = '" . JText::_('COM_SEMINARMAN_AS_ATTACHMENT') . "';
        					
        			filelist.appendChild(li);
        			li.appendChild(txt);
        			li.appendChild(button);
        			li.appendChild(hid);
        			li.appendChild(chx);
        			li.appendChild(spn);
        			li.appendChild(chxhid);
        			filelist.appendChild(clrdiv);
        		}
        					
        		function toggle_email_attach_check(visible_attachchxid, hidden_attachid) {
                        if(document.getElementById(visible_attachchxid).checked) {
                            document.getElementById(hidden_attachid).value = '1';
                        } else {
        					document.getElementById(hidden_attachid).value = '0';
        				}        					
        	    }
        
        		function qfRemoveFile(file, i) {
        
        			var name = 'a_name' + i;
        			var id = 'a_id' + i;
        			var attach = 'fattach' + i;	
        			var attach_chx = 'fattach_chx' + i;	
        			var attach_spn = 'fattach_spn' + i;	
        
        			document.getElementById(id).value = 0;
        			document.getElementById(name).value = file;
        			jQuery('#'+attach).remove();
        			jQuery('#'+attach_chx).remove();
        			jQuery('#'+attach_spn).remove();
        		}";

        $document->addScriptDeclaration($js);

        JHTML::_('behavior.modal', 'a.modal');

        $i = 0;
        $fileselect = '<ul class="adminformlist">';
        if ($files)
        {
        	foreach ($files as $file)
        	{
        		$fileselect .= '<li><input style="background: #ffffff;" type="text" id="a_name'. $i .'" value="'. $file->filename .'" disabled="disabled" size="50" />';
        		$fileselect .= '<input type="hidden" id="a_id'. $i .'" name="fid[]" value="'. $file->fileid .'" />';
        		if (JHTMLSeminarman::UserIsCourseManager() || $params->get('edit_course_documents') == 1) {
        		    $fileselect .= '<input class="inputbox" type="button" onclick="qfRemoveFile(\''. JText::_('COM_SEMINARMAN_REMOVE') .'\', '. $i . ');" value="'. JText::_('COM_SEMINARMAN_REMOVE') .'" />';
        		    if (isset($file->email_attach) && $file->email_attach) {
        		    	$email_attach_checked = " checked";
        		    } else {
        		    	$email_attach_checked = "";
        		    }
        		    $fileselect .= '<input id="fattach_chx'. $i .'" value="1" onclick="toggle_email_attach_check(\'fattach_chx'.$i.'\', \'fattach'.$i.'\')" type="checkbox"' . $email_attach_checked . '><span id="fattach_spn' . $i . '">' . JText::_('COM_SEMINARMAN_AS_ATTACHMENT') . '</span>';
        		    $fileselect .= '<input type="hidden" id="fattach'. $i .'" name="fattach[]" value="'. (isset($file->email_attach) ? $file->email_attach : '0') .'" />';
        		    $fileselect .= '</li>';
        		}
        		$fileselect .= '<div class="clr"></div>';
        		$i++;
        	}
        }
        $fileselect .= '</ul>';
        
        $linkfsel = 'index.php?option=com_seminarman&amp;view=fileselement&amp;tmpl=component&amp;index=' . $i;

        // $lists['state'] = JHTML::_('select.booleanlist', 'state', '', $row->state);
        $state_options = array("1"=>JText::_('JPUBLISHED'), "0"=>JText::_('JUNPUBLISHED'), "2"=>JText::_('JARCHIVED'), "-2"=>JText::_('JTRASHED'));
        
        if(JHTMLSeminarman::UserIsCourseManager() || ($params->get('edit_course_status') == 1)){
            $lists['state'] = JHtml::_('select.genericlist', $state_options, 'state', 'class="inputbox" size="1" ', 'value', 'text', $row->state);
        } else {
        	$lists['state'] = JHtml::_('select.genericlist', $state_options, 'state', 'class="inputbox" size="1" disabled ', 'value', 'text', $row->state) . '<input type="hidden" name="state" value="' . $row->state . '" />';
        }
        if(JHTMLSeminarman::UserIsCourseManager() || ($params->get('edit_course_new') == 1)){
            $lists['new'] = JHTML::_('select.booleanlist', 'new', '', $row->new);
        } else {
        	$lists['new'] = JHTML::_('select.booleanlist', 'new', ' disabled ', $row->new) . '<input type="hidden" name="new" value="' . $row->new . '" />';
        }
        if(JHTMLSeminarman::UserIsCourseManager() || ($params->get('edit_course_canceled') == 1)){
            $lists['canceled'] = JHTML::_('select.booleanlist', 'canceled', '', $row->canceled);
        } else {
        	$lists['canceled'] = JHTML::_('select.booleanlist', 'canceled', ' disabled ', $row->canceled) . '<input type="hidden" name="canceled" value="' . $row->canceled . '" />';
        }
        if(JHTMLSeminarman::UserIsCourseManager() || ($params->get('edit_course_experience_level') == 1)){
            $lists['experience_level'] = JHTMLSeminarman::getSelectExperienceLevel('id_experience_level', $row->id_experience_level);
        }else{
            $lists['experience_level'] = JHTMLSeminarman::getSelectExperienceLevel('id_experience_level', $row->id_experience_level, 'disabled') . '<input type="hidden" name="id_experience_level" value="' . $row->id_experience_level . '" />';
        }
        
        if(JHTMLSeminarman::UserIsCourseManager() || ($params->get('edit_course_group') == 1)){
            $lists['atgroup'] = JHTMLSeminarman::getSelectATGroup('id_group', $row->id_group, '');
        }else{
        	$lists['atgroup'] = JHTMLSeminarman::getSelectATGroup('id_group', $row->id_group, 1) . '<input type="hidden" name="id_group" value="' . $row->id_group . '" />';
        }
        
        $lists['job_experience'] = JHTMLSeminarman::getListFromXML('Job Experience', 'job_experience', 0, $row->job_experience);
        
        if(JHTMLSeminarman::UserIsCourseManager() || ($params->get('edit_course_prices') == 1)){
            $lists['price_type'] = JHTMLSeminarman::getListFromXML('Price Type', 'price_type', '', $row->price_type);
        }else{
            $lists['price_type'] = JHTMLSeminarman::getListFromXML('Price Type', 'price_type', 1, $row->price_type) . '<input type="hidden" name="price_type" value="' . $row->price_type . '" />';	
        }
        
        if(JHTMLSeminarman::UserIsCourseManager() || ($params->get('edit_course_email_tmpl') == 1)){
            $lists['email_template'] = JHTMLSeminarman::getSelectEmailTemplate('email_template', $row->email_template);
        }else{
        	$lists['email_template'] = JHTMLSeminarman::getSelectEmailTemplate('email_template', $row->email_template, 'disabled') . '<input type="hidden" name="email_template" value="' . $row->email_template . '" />';
        }
        
        if(JHTMLSeminarman::UserIsCourseManager() || ($params->get('edit_course_invoice_tmpl') == 1)){
            $lists['invoice_template'] = JHTMLSeminarman::getSelectPdfTemplate('invoice_template', $row->invoice_template, 0);
        }else{
        	$lists['invoice_template'] = JHTMLSeminarman::getSelectPdfTemplate('invoice_template', $row->invoice_template, 0, 'disabled') . '<input type="hidden" name="invoice_template" value="' . $row->invoice_template . '" />';
        }
        
        if(JHTMLSeminarman::UserIsCourseManager() || ($params->get('edit_course_attlst_tmpl') == 1)){
            $lists['attlst_template'] = JHTMLSeminarman::getSelectPdfTemplate('attlst_template', $row->attlst_template, 1);
        }else{
        	$lists['attlst_template'] = JHTMLSeminarman::getSelectPdfTemplate('attlst_template', $row->attlst_template, 1, 'disabled') . '<input type="hidden" name="attlst_template" value="' . $row->attlst_template . '" />';
        }
        
        if(JHTMLSeminarman::UserIsCourseManager() || ($params->get('edit_course_certificate_tmpl') == 1)){
        	$lists['certificate_template'] = JHTMLSeminarman::getSelectPdfTemplate('certificate_template', $row->certificate_template, 2);
        }else{
        	$lists['certificate_template'] = JHTMLSeminarman::getSelectPdfTemplate('certificate_template', $row->certificate_template, 2, 'disabled') . '<input type="hidden" name="certificate_template" value="' . $row->certificate_template . '" />';
        }

        if(JHTMLSeminarman::UserIsCourseManager() || ($params->get('edit_course_extra_attach_tmpl') == 1)){
        	$lists['extra_attach_template'] = JHTMLSeminarman::getSelectPdfTemplate('extra_attach_template', $row->extra_attach_template, 3);
        }else{
        	$lists['extra_attach_template'] = JHTMLSeminarman::getSelectPdfTemplate('extra_attach_template', $row->extra_attach_template, 3, 'disabled') . '<input type="hidden" name="extra_attach_template" value="' . $row->extra_attach_template . '" />';
        }        
        
        (count($tags) > 9) ? ($tags_size = 10) : ($tags_size = count($tags) + 1);
        if(JHTMLSeminarman::UserIsCourseManager() || ($params->get('edit_course_tags') == 1)){
            $lists['tagsselect'] = '<select size="'. $tags_size .'" multiple="multiple" name="tag[]" id="tag">';
            foreach ($tags as $tag) {
        	    $lists['tagsselect'] .= '<option ';
        	    foreach ($usedtags as $used)
        	    if ($used == $tag->id)
        	    $lists['tagsselect'] .= 'selected="selected" ';
        	    $lists['tagsselect'] .= 'value="'. $tag->id .'">'. $tag->name .'</option>';
            }
            $lists['tagsselect'] .= '</select>';
        } else {
        	$lists['tagsselect'] = '<select disabled size="'. $tags_size .'" multiple="multiple" name="tag_orig[]" id="tag_orig">';
        	foreach ($tags as $tag) {
        		$lists['tagsselect'] .= '<option ';
        		foreach ($usedtags as $used)
        			if ($used == $tag->id)
        				$lists['tagsselect'] .= 'selected="selected" ';
        				$lists['tagsselect'] .= 'value="'. $tag->id .'">'. $tag->name .'</option>';
        	}
        	$lists['tagsselect'] .= '</select>';  
        	$lists['tagsselect'] .= '<select style="display: none;" size="'. $tags_size .'" multiple="multiple" name="tag[]" id="tag">';
        	foreach ($tags as $tag) {
        		$lists['tagsselect'] .= '<option ';
        		foreach ($usedtags as $used)
        			if ($used == $tag->id)
        				$lists['tagsselect'] .= 'selected="selected" ';
        				$lists['tagsselect'] .= 'value="'. $tag->id .'">'. $tag->name .'</option>';
        	}
        	$lists['tagsselect'] .= '</select>';
        }
        
        // get all templates
        $query = $db->getQuery( true );
        if(JHTMLSeminarman::UserIsCourseManager()){
        	$query->select( 'id AS value' );
        	$query->select( 'CONCAT(name, \' (\', id, \')\') as text' );
        	$query->from( '#__seminarman_templates' );
        	$query->order( 'id' );
        }else{
            $teacherid = JHTMLSeminarman::getUserTutorID();	
            $query->select( 't.id AS value' );
            $query->select( 'CONCAT(t.name, \' (\', id, \')\') as text' );
            $query->from( '#__seminarman_templates AS t' );
            $query->join( "LEFT", "#__seminarman_tutor_templates_relations AS r ON (t.id = r.templateid)");
            $query->where( "r.tutorid = '" . $teacherid . "'" );
        	$query->order( 't.id' );
        }
        $db->setQuery( $query );
        $templates = $db->loadObjectList();
        
        // build select list of template names
        $types[] = JHTML::_('select.option', 0, '- ' . JText::_('COM_SEMINARMAN_CHOOSE_PLEASE') . ' -');
        
       	foreach ($templates as $template)
       		$types[] = JHTML::_('select.option', $template->value, JText::_($template->text));
       	
       	// template aus post
       	$templateId = JRequest::getVar('templateId', 0);
       	if ($templateId == 0)
       		if (!empty($row->templateId))
       			$templateId = $row->templateId;
        
        $lists['templates'] = JHTML::_('select.genericlist', $types, 'templateId', 'class="inputbox" size="1" ', 'value', 'text', $templateId);
        
        if(JHTMLSeminarman::UserIsCourseManager()){
            $lists['username'] = JHTMLSeminarman::getSelectTutor('tutor_id[]', (array)json_decode($row->tutor_id, true), $templateId);

        }else{
            $teacherid = JHTMLSeminarman::getUserTutorID();	
            $query_tutor = $db->getQuery(true);
            $query_tutor->select( 'id AS value, CONCAT(title, CONCAT(\' (\', id, \')\')) AS text' );
            $query_tutor->from( '#__seminarman_tutor' );
            $query_tutor->where( 'id = ' . $teacherid );
            $db->setQuery($query_tutor);
            foreach ($db->loadObjectList() as $tutor)
    			    $teachers[] = JHtml::_('select.option', $tutor->value, JText::_($tutor->text));
    	    $lists['username'] = JHtml::_('select.genericlist', $teachers, 'tutor_id', 'class="inputbox" size="1" ', 'value', 'text', $teacherid);
        }
        
        if ($params->get('trigger_virtuemart') == 1) {
        	if (($row->id) == 0) {
        		// create new course and vm engine is on
        		$lists['select_vm'] = '<li><label for="invm">In VirtueMart</label><fieldset id="invm" class="radio">' . JHTML::_('select.booleanlist', 'invm', 'class="inputbox"', 1) . '</fieldset></li>';
        	} else {
        		$db = JFactory::getDBO();
                $query = $db->getQuery(true);
                $query->select('*')
                      ->from('#__seminarman_vm_course_product_map')
                      ->where('sm_course_id = ' . $row->id );
                $db->setQuery($query);
                $result = $db->loadAssoc(); 
                if (is_null($result)) {
                	// no vm product mapped yet
                	$lists['select_vm'] = '<li><label for="invm">In VirtueMart</label><fieldset id="invm" class="radio">' . JHTML::_('select.booleanlist', 'invm', 'class="inputbox"', 0) . '</fieldset></li>';
                } else {
                	// a vm product is mapped, is this valid?
                	$register_vm_id = $result["vm_product_id"];
            	    $query_check = $db->getQuery(true);
            	    $query_check->select('*')
            	            ->from('#__virtuemart_products')
            	            ->where('virtuemart_product_id = ' . $register_vm_id);
            	    $db->setQuery($query_check);
            	    $result_check = $db->loadAssoc();
            	    if (is_null($result_check)){
            	    	// invalid
            	    	$lists['select_vm'] = '<li><label for="invm">' . JText::_('COM_SEMINARMAN_SET_IN_VM') . '</label><fieldset id="invm" class="radio">' . JHTML::_('select.booleanlist', 'invm', 'class="inputbox"', 0) . '</fieldset></li>';
            	    } else {
            	    	// valid
            	    	$lists['select_vm'] = '<li><label for="invm">' . JText::_('COM_SEMINARMAN_SET_IN_VM') . '</label><fieldset id="invm" class="radio">' . JHTML::_('select.booleanlist', 'invm', 'class="inputbox" disabled', 1) . '<input type="hidden" name="invm" value="1"></fieldset></li>';
            	    }
                }      		
        	}
        } else {
        	$lists['select_vm'] = '';
        }

        $query_price_rule2 = $db->getQuery(true);
        $query_price_rule2->select('*')
                          ->from('#__seminarman_pricegroups')
                          ->where('gid=2');
        $db->setQuery($query_price_rule2);
        $result_rule2 = $db->loadAssoc();
        if(!is_null($result_rule2)) {
        	$lists['price2_mathop'] = $result_rule2['calc_mathop'];
        	$lists['price2_value'] = $result_rule2['calc_value'];
        } else {
        	$lists['price2_mathop'] = '-';
        	$lists['price2_value'] = 0;
        }

        $query_price_rule3 = $db->getQuery(true);
        $query_price_rule3->select('*')
                          ->from('#__seminarman_pricegroups')
                          ->where('gid=3');
        $db->setQuery($query_price_rule3);
        $result_rule3 = $db->loadAssoc();
        if(!is_null($result_rule3)) {
        	$lists['price3_mathop'] = $result_rule3['calc_mathop'];
        	$lists['price3_value'] = $result_rule3['calc_value'];
        } else {
        	$lists['price3_mathop'] = '-';
        	$lists['price3_value'] = 0;
        } 

        $query_price_rule4 = $db->getQuery(true);
        $query_price_rule4->select('*')
        ->from('#__seminarman_pricegroups')
        ->where('gid=4');
        $db->setQuery($query_price_rule4);
        $result_rule4 = $db->loadAssoc();
        if(!is_null($result_rule4)) {
        	$lists['price4_mathop'] = $result_rule4['calc_mathop'];
        	$lists['price4_value'] = $result_rule4['calc_value'];
        } else {
        	$lists['price4_mathop'] = '-';
        	$lists['price4_value'] = 0;
        }
        
        $query_price_rule5 = $db->getQuery(true);
        $query_price_rule5->select('*')
        ->from('#__seminarman_pricegroups')
        ->where('gid=5');
        $db->setQuery($query_price_rule5);
        $result_rule5 = $db->loadAssoc();
        if(!is_null($result_rule5)) {
        	$lists['price5_mathop'] = $result_rule5['calc_mathop'];
        	$lists['price5_value'] = $result_rule5['calc_value'];
        } else {
        	$lists['price5_mathop'] = '-';
        	$lists['price5_value'] = 0;
        }
        
        $course_attribs = new JRegistry();
        $course_attribs->loadString($row->attribs);
        if (!is_null($row->start_time)) {
        	$start_date_all = $course_attribs->get('start_date_all', 0);        	
        } else {
        	// backward compatibility!
        	// in the old version start_time could be saved as NULL value
        	// it was considered as an all-day event
        	$start_date_all = 1;
        }
        $lists['start_date_all'] = JHTML::_('select.booleanlist', 'params[start_date_all]', 'onclick="setStartDateAll()"', $start_date_all);
        
        if (!is_null($row->finish_time)) {
        	$finish_date_all = $course_attribs->get('finish_date_all', 0);
        } else {
        	// backward compatibility!
        	// in the old version start_time could be saved as NULL value
        	// it was considered as an all-day event
        	$finish_date_all = 1;
        }
        $lists['finish_date_all'] = JHTML::_('select.booleanlist', 'params[finish_date_all]', 'onclick="setFinishDateAll()"', $finish_date_all);
        
        $row->start_date_all = $start_date_all;
        $row->finish_date_all = $finish_date_all;
        
        $this->assignRef('lists', $lists);
        $this->assignRef('row', $row);
        $this->assignRef('editor', $editor);
        $this->assignRef('pane', $pane);
        $this->assignRef('nullDate', $nullDate);
        $this->assignRef('form', $form);
        $this->assignRef('fileselect', $fileselect);
        $this->assignRef('linkfsel', $linkfsel);
        $this->assignRef('tags', $tags);
        $this->assignRef('usedtags', $usedtags);
        parent::display($tpl);
    }
    
    function _viewics($fid, $ftitle)
    {
    	jimport('joomla.filesystem.file');    	
    	
    	$mainframe = JFactory::getApplication();
    	$params = JComponentHelper::getParams( 'com_seminarman' );
    	
    	if ($params->get('ics_file_name') == 0) {
		    $filename = "ical_course_" . $fid . ".ics";
    	} else {
    	    $filename = JFile::makeSafe(str_replace(array('Ä','Ö','Ü','ä','ö','ü','ß'), array('Ae','Oe','Ue','ae','oe','ue','ss'), html_entity_decode($ftitle, ENT_QUOTES)) . '_' . $fid . ".ics");
    		$filename = str_replace(' ', '_', $filename);
    	}
		$icsfile_path = JPATH_ROOT.DS.$params->get('invoice_save_dir').DS . $filename;

    	if (!(JFile::exists($icsfile_path)))
    		$mainframe->redirect('index.php?option=com_seminarman&view=courses');
    		
    		$ics_data = file_get_contents($icsfile_path);
    		ob_end_clean();
    		header('Content-Type: text/calendar; charset=utf-8');
    		header('Content-Disposition: attachment; filename="'. $filename .'"');
    		print $ics_data;
    		flush();
    		exit;
    		 
    }
}

?>