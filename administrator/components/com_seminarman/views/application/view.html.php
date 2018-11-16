<?php
/**
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011-2016 Open Source Group GmbH www.osg-gmbh.de
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

class seminarmanViewapplication extends JViewLegacy
{
    function display($tpl = null)
    {
        $mainframe = JFactory::getApplication();
        $childviewname = 'application';

        if ($this->getLayout() == 'form')
        {
            $this->_displayForm($tpl);
            return;
        }

        if ($this->getLayout() == 'invoicepdf')
        {
        	$this->_viewpdf('bill');
        	return;
        }    
        
        if ($this->getLayout() == 'certificatepdf')
        {
        	$this->_viewpdf('cert');
        	return;
        }
        
        if ($this->getLayout() == 'attachmentpdf')
        {
        	$this->_viewpdf('attachment');
        	return;
        }

        $application = $this->get('data');

        if ($application->url)
        {
            $mainframe->redirect($application->url);
        }

        parent::display();
    }
    
    function _viewpdf($type)
    {   
    	$mainframe = JFactory::getApplication();
    	$application = $this->get('data');
    	$params = JComponentHelper::getParams( 'com_seminarman' );
    	
    	if ($type == 'bill') {
    	    $filename = $application->invoice_filename_prefix.$application->invoice_number.'.pdf';
    	} elseif ($type == 'cert') {
    		$filename = $application->certificate_file;
    	} elseif ($type == 'attachment') {
    		$filename = $application->extra_attach_file;
    	}
    	$filepath = JPATH_ROOT.DS.$params->get('invoice_save_dir').DS.$filename;
    	
    	if(JVERSION >= 3.0) {
    		jimport('joomla.filesystem.file');
    	}
    	if (!$pdf_data = JFile::read($filepath))
    		$mainframe->redirect('index.php?option=com_seminarman&view=applications');
    	
    	ob_end_clean();
    	header('Content-Type: application/pdf');
    	header('Content-Disposition: attachment; filename="'. $filename .'"');
    	print $pdf_data;
    	flush();
    	exit;
   		
    }
    
    function _displayForm($tpl)
    {
        $mainframe = JFactory::getApplication();

        $db = JFactory::getDBO();
        $uri = JFactory::getURI();
        $user = JFactory::getUser();
        $model = $this->getModel();
        $document = JFactory::getDocument();
        $lang = JFactory::getLanguage();

        $document->addStyleSheet('components/com_seminarman/assets/css/seminarmanbackend.css');
        if(JVERSION >= 3.0) {
        	$document->addStyleSheet('components/com_seminarman/assets/css/seminarmanbackend_j3.x.css');
        }
        if ($lang->isRTL())
        {
            $document->addStyleSheet('components/com_seminarman/assets/css/seminarmanbackend_rtl.css');
        }
        
        JHTML::_('behavior.modal', 'a.modal');

        require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS . 'helpers' . DS . 'seminarman.php');
        require_once( JPATH_ROOT . DS . 'components' . DS . 'com_seminarman' . DS . 'libraries' . DS . 'customfields.php' );

        $lists = array();

        $application = $this->get('data');

        $isNew = ($application->id < 1);
        
        if ($isNew) {
        	$attendeedata = $this->get('attendee');
        	
        	$courseid = JRequest::getVar('filter_courseid');        	
        	if(!(is_null($courseid))) {
        		// save the course id in php session for the possible redirect after selecting user
        		$mainframe->setUserState('com_seminarman.new.app.of.course', $courseid);
        	} else {
        		// a user selected
        		$courseid = $mainframe->getUserState('com_seminarman.new.app.of.course');
        	}
        } else {
        	$courseid = $application->course_id;        	
        }
        $coursedata = $model->getCourseData( $courseid );  
        
        if ($isNew) {
        	foreach ($coursedata as $name=>$value) {
        		$application->$name = $value;
        	}
        	
        	$application->user_id = $mainframe->getUserState('com_seminarman.selected.user');
        	
        	if(is_null($application->user_id)) {
        		$application->user_id = 0;
        	}
        }

        if ($model->isCheckedOut($user->get('id')))
        {
            $msg = JText::_('COM_SEMINARMAN_RECORD_EDITED');
            $mainframe->redirect('index.php?option=' . $option, $msg);
        }

        if (!$isNew)
        {
            $model->checkout($user->get('id'));
            $disabled = 1;
            $formType['isNew'] = null;
        } else
        {
            $formType['isNew'] = 1;
            $disabled = 0;
            $application->published = 1;
            $application->approved = 1;
            $application->order = 0;
            
            $application->user_id = $mainframe->getUserState('com_seminarman.selected.user');
            
            if(is_null($application->user_id)) {
            	$application->user_id = 0;
            }
        }
    	$params = JComponentHelper::getParams( 'com_seminarman' );
    	$application->currency_price = $params->get( 'currency' );

        if (!$isNew) {
        	$query = $db->getQuery(true);
        	$query->select( 'ordering AS value' );
        	$query->select( 'CONCAT_WS(\' \', first_name, last_name) AS text' );
        	$query->from( '#__seminarman_application' );
        	$query->order( 'ordering' );

	        if(JVERSION >= 3.0) {
	        	$lists['ordering'] = JHTML::_('list.ordering', $application->id, $query);
	        } else {
	        	$lists['ordering'] = JHTML::_('list.specificordering', $application, $application->id, $query);
	    	}
	        $lists['published'] = JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $application->published);
	        $lists['salutation'] = JHTMLSeminarman::getListFromXML('Salutation', 'salutation', 0, $this->escape($application->salutation));
	        $lists['username'] = JHTMLSeminarman::getSelectUser_modal('user_id', $application->user_id, $disabled);
	        // build status list
	        $lists['status'] = JHTMLSeminarman::getStatusListForApplicationViews( $application->status, 'onchange="setStatus()" class="inputbox" size="1"' );
	        
	        // build course list
	        $query = $db->getQuery(true);
	        $query->select('id AS value, title AS text');
	        $query->from('#__seminarman_courses');
	        $db->setQuery($query);
	         
	        foreach ($db->loadObjectList() as $course)
	        	$courses[] = JHtml::_('select.option', $course->value, JText::_($course->text));
	        
	        $lists['course'] = JHtml::_('select.genericlist', $courses, 'course_title', 'class="inputbox" size="1" ', 'value', 'text', $application->course_title);
        } else {
        	$default = isset($attendeedata->salutationStr) ? $attendeedata->salutationStr : $attendeedata->salutation;
        	$lists['salutation'] = JHTMLSeminarman::getListFromXML('Salutation', 'salutation', 0, $default); 

        	$query = $db->getQuery(true);
        	$query->select( 'id AS value' );
        	$query->select( 'CONCAT_WS(\' / \', username, name, id ) AS text' );
        	$query->from( '#__users' );
        	$query->where( 'block = 0' );
        	$query->order( 'username' );
        	
        	$db->setQuery( $query );
        	$items = $db->loadObjectList();
        	
        	$types[] = JHtml::_('select.option', '0', '- '. JText::_('COM_SEMINARMAN_CHOOSE_PLEASE') .' -');
        	foreach ($items as $item) {
        		$types[] = JHtml::_('select.option', $item->value, JText::_($item->text));
        	}
        	
        	if(JVERSION < 3.0) {
        		$lists['username'] = JHtml::_('select.genericlist', $types, 'user_id', 'class="inputbox" size="1" onchange="update_user_info()"', 'value', 'text', $application->user_id);
        	} else {
        		$lists['username'] = JHTMLSeminarman::getSelectUser_modal('user_id', $application->user_id, $disabled);
        	}
        	
        	// build status list
        	$lists['status'] = JHTMLSeminarman::getStatusListForApplicationViews( 0 );
        }
     
        
    	JFilterOutput::objectHTMLSafe($group, ENT_QUOTES, 'description');

    	$customfields	= $model->getEditableCustomfields( $application->id );
    	$user->customfields	=& $customfields;
    	$this->assignRef('user' , $user);
        $this->assignRef('lists', $lists);
        $this->assignRef('application', $application);
        $this->assignRef('course', $coursedata);
        $this->assignRef('params', $params);
        $this->assignRef('isNew', $isNew);
		if ($isNew) $this->assignRef('attendeedata', $attendeedata);
        
        parent::display($tpl);
    }
    
}