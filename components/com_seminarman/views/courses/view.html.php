<?php
/**
*
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011-15 Open Source Group GmbH www.osg-gmbh.de
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
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');
jimport( 'joomla.html.parameter' );

class SeminarmanViewCourses extends JViewLegacy{
    function display($tpl = null)
    {
        $mainframe = JFactory::getApplication();

        $Itemid = JRequest::getInt('Itemid');
        
        $document = JFactory::getDocument();
        $user = JFactory::getUser();
        $menus = JFactory::getApplication()->getMenu();
        $lang = JFactory::getLanguage();
        $menu = $menus->getActive();
        $dispatcher = JDispatcher::getInstance();
        $params = $mainframe->getParams('com_seminarman');
        $uri = JFactory::getURI();

        $limitstart = JRequest::getVar('limitstart', 0, '', 'int');
        $cid = JRequest::getInt('cid', 0);

        if ($this->getLayout() == 'form'){
            $this->_displayForm($tpl);
            return;
        }

        require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS .
            'helpers' . DS . 'seminarman.php');

        seminarman_html::addSiteStyles($this);

        if (!$user->id || $user->id == 0){
            $document = JFactory::getDocument();
        }

        $document = JFactory::getDocument();

        $course = $this->get('Course');
        $course->currency_price = $params->get('currency');
        
        $price_before_vat = $course->price;
        $category = $this->get('Categories');
        SMANFunctions::setCourse($course, $category[0], $Itemid, JText::_('COM_SEMINARMAN_DATE_FORMAT2'), JText::_('COM_SEMINARMAN_TIME_FORMAT2'));
      
        $attendeedata = $this->get('attendee');

        if (($course->id == 0)){
            $id = JRequest::getInt('id', 0);
            // return JError::raiseError(404, JText::sprintf('COURSE #%d NOT FOUND', $id));
            return JFactory::getApplication()->enqueueMessage(JText::_('COM_SEMINARMAN_COURSE_NOT_DISPLAY'), 'notice');
        }

        if ($params->get('show_tags')){
            $tags = $this->get('Tags');
        }

        $categories = $this->get('Categories');

        if ($params->get('show_favourites')){
            $favourites = $this->get('Favourites');
            $favoured = $this->get('Favoured');
        }

        $files = $this->get('Files');

        if ($params->get('trigger_onprepare_content')){
            JPluginHelper::importPlugin('content');
            $results = $dispatcher->trigger('onContentPrepare', array('com_seminarman.courses', &$course, &$params, $limitstart));
        }

        $cats = new seminarman_cats($cid);
        $parents = $cats->getParentlist();
        $pathway = $mainframe->getPathWay();
        $all_parents = $parents;
        
        if ($params->get('enable_component_pathway') == 1) {
        foreach ($parents as $parent){
            $pathway->addItem($this->escape($parent->title), JRoute::_('index.php?option=com_seminarman&view=category&cid=' .
                    $parent->categoryslug . '&Itemid=' . $Itemid));
        }
        $pathway->addItem($this->escape($course->title), JRoute::_('index.php?option=com_seminarman&view=courses&id=' .
                $course->slug . '&Itemid=' . $Itemid));
        }
        
        $jversion = new JVersion();
        $short_version = $jversion->getShortVersion();
        
        // if (is_object($menu)){
        //	if (version_compare($short_version, "3.0", 'ge')) {
        //        $menu_params = new JRegistry($menu->params);
        //    } else {
        //    	$menu_params = new JParameter($menu->params);
        //    }
        //    if (!$menu_params->get('page_title')){
        //        $params->set('page_title', $course->title);
        //    }
        // } else {
        //    $params->set('page_title', $course->title);
        // }

        if ($course->meta_description){
            $document->setDescription($course->meta_description);
        }

        if ($course->meta_keywords){
            $document->setMetadata('keywords', $course->meta_keywords);
        }

        if (version_compare($short_version, "3.0", 'ge')) {
        	$mdata = new JRegistry($course->metadata);
        	$itemParams = new JRegistry($course->attribs);
        } else {
        	$mdata = new JParameter($course->metadata);
    		$itemParams = new JParameter($course->attribs);
        }
        $mdata = $mdata->toArray();
        foreach ($mdata as $k => $v){
            if ($v){
                $document->setMetadata($k, $v);
            }
        }
    	
        $params->merge($itemParams);
        $param_page_title = $params->get('page_title');  // workable for PHP 5.4
        if (is_object($menu) && ($menu->query['view'] == 'courses') && !empty($param_page_title)) {
        	$doc_title = $params->get('page_title');
        } else {
            $param_html_page_title = $params->get('html_page_title');  // workable for PHP 5.4
            if ((!is_null($param_html_page_title)) && (!empty($param_html_page_title))) {
        		$html_page_title = $params->get('html_page_title');
        	} else {
        		$html_page_title = $course->title;
        	}
        	if ($cid){
        		$parentcat = array_pop($parents);
        		$doc_title = $parentcat->title . ' - ' . $html_page_title;
        	}else{
        		$doc_title = $html_page_title;
        	}
        }
        
        $document->setTitle($doc_title);

        $print_link = JRoute::_('index.php?option=com_seminarman&view=courses&cid=' . $course->categoryslug .
            '&id=' . $course->slug . '&Itemid=' . $Itemid . '&pop=1&tmpl=component');

        $default = isset($attendeedata->salutationStr) ? $attendeedata->salutationStr : $attendeedata->salutation;
        $lists['salutation'] = JHTMLSeminarman::getListFromXML('Salutation', 'salutation', 0,  $default);

        $show_application_form = 1;
        
        if ($course->canceled){
        	$show_application_form = 0;
            $mainframe->enqueueMessage(JText::_('COM_SEMINARMAN_COURSE_IS_CANCELED'));
        } else {
        	if ($params->get('current_capacity') && $course->currentAvailability < 1){
        	
        		// new for waiting list
        		if ( $params->get( 'waitinglist_active' ) ) {
        			$show_application_form = 2;
        			if ($params->get('show_booking_form') && $course->state != 2) $mainframe->enqueueMessage(JText::_('COM_SEMINARMAN_NO_SEATS_LEFT_WL'));
        		}
        		else {
        			// no more bookings can be made for the course selected
        			$show_application_form = 0;
        			$mainframe->enqueueMessage(JText::_('COM_SEMINARMAN_NO_SEATS_LEFT'));
        		}
        	}        	
        }
        JPluginHelper::importPlugin('content');
        $results = $mainframe->triggerEvent( 'onPrepareContent', array( &$course, &$params, 0 ));

        $data = new stdClass();
        $model = $this->getModel('courses');
        $data->customfields = $model->getEditableCustomfields($attendeedata->id);
        CMFactory::load('libraries' , 'customfields');
        
        JPluginHelper::importPlugin('seminarman');
        
        // fire vmengine
        $ergebnisse = $dispatcher->trigger('onShowingCourse', array($course->id)); 
        if (!empty($ergebnisse)) {      
            $vmlink = $ergebnisse[0];
        } else {
        	$vmlink = null;
        }

        // multiple toturs available ab 2.6.0
        $db = JFactory::getDBO();
        $course_tutors_id_array = (array)json_decode($course->tutor_id, true);
        $course_tutors = array();
        foreach ($course_tutors_id_array as $course_tutors_id) {

        	$query = $db->getQuery(true);
        	$query->select( 'CONCAT_WS(\' \', emp.salutation, emp.other_title, emp.firstname, emp.lastname) AS tutor_display' );
        	$query->select( 'emp.published AS tutor_published' );
        	$query->from( '#__seminarman_tutor AS emp' );
        	$query->where( 'emp.id = ' . $course_tutors_id );
        	$db->setQuery( $query );
        	
        	$ergebnis = $db->loadAssoc();
        	$course_tutors[$course_tutors_id] = $ergebnis;
        }
        $course->tutors = $course_tutors;
        
        $fields = $data->customfields ['fields'];
        $action = $uri->toString();
        $this->assignRef('fields', $fields);
        $this->assignRef('course', $course);
        $this->assignRef('tags', $tags);
        $this->assignRef('categories', $categories);
        $this->assignRef('attendeedata', $attendeedata);
        $this->assignRef('favourites', $favourites);
        $this->assignRef('favoured', $favoured);
        $this->assignRef('files', $files);
        $this->assignRef('user', $user);
        $this->assignRef('params', $params);
        $this->assignRef('print_link', $print_link);
        $this->assignRef('parentcat', $parentcat);
        $this->assignRef('course_sessions', $course->course_sessions);
        $this->assignRef('lists', $lists);
        $this->assign('action', $action);
        $this->assignRef('vmlink', $vmlink);
        $this->assignRef('price_before_vat', $price_before_vat);
        $this->assignRef('show_application_form', $show_application_form);
        $this->assignRef('all_parents', $all_parents);

        parent::display($tpl);
        
    }
    
}

?>
