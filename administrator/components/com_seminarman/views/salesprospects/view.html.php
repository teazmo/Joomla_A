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

class seminarmanViewsalesprospects extends JViewLegacy
{
    function display($tpl = null)
    {
        $mainframe = JFactory::getApplication();

        $db = JFactory::getDBO();
        $uri = JFactory::getURI();
        $childviewname = 'salesprospect';
        $document = JFactory::getDocument();
        $lang = JFactory::getLanguage();
        
        $params = JComponentHelper::getParams( 'com_seminarman' );

        $document->addStyleSheet('components/com_seminarman/assets/css/seminarmanbackend.css');
        $jversion = new JVersion();
        $short_version = $jversion->getShortVersion();
        if (version_compare($short_version, "3.0", 'ge')) {
        	$document->addStyleSheet('components/com_seminarman/assets/css/seminarmanbackend_j3.x.css');
        }
        if ($lang->isRTL())
        {
            $document->addStyleSheet('components/com_seminarman/assets/css/seminarmanbackend_rtl.css');
        }

        require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS .
            'helpers' . DS . 'seminarman.php');

        JHTMLSeminarman::buildSideMenu();
        JToolBarHelper::title(JText::_('COM_SEMINARMAN_LST_OF_SALES_PROSPECTS'), 'salesprospects');
        $bar = JToolBar::getInstance('toolbar');
        if (version_compare($short_version, "3.0", 'ge')) {
        	$bar->appendButton('Standard', 'envelope', JText::_('COM_SEMINARMAN_NOTIFY'), 'notify', true);
    	} else {
    		$bar->appendButton('Standard', 'send', JText::_('COM_SEMINARMAN_NOTIFY'), 'notify', true);
    	}
        JToolBarHelper::divider();
        if (version_compare($short_version, "3.0", 'ge')) {
        	JToolBarHelper::editList();
    	} else {
    		JToolBarHelper::editListX();
    	}
        JToolBarHelper::divider();
        JToolBarHelper::deleteList();

        $filter_order = $mainframe->getUserStateFromRequest('com_seminarman'. $childviewname . '.filter_order', 'filter_order', 'a.id', 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest('com_seminarman' . $childviewname . '.filter_order_Dir', 'filter_order_Dir', 'desc', 'word');
    	$filter_templateid = $mainframe->getUserStateFromRequest('com_seminarman'. $childviewname . '.filter_templateid', 'filter_templateid', 0, 'int' );
    	$filter_courseid = $mainframe->getUserStateFromRequest('com_seminarman'. $childviewname . '.filter_courseid', 'filter_courseid', 0, 'int' );
    	$filter_search = $mainframe->getUserStateFromRequest('com_seminarman'. $childviewname . '.filter_search', 'filter_search', '', 'int' );

        $search = $mainframe->getUserStateFromRequest('com_seminarman'. $childviewname .'.search', 'search', '', 'string');
        $search = JString::strtolower($search);

        $model = $this->getModel();
        $salesprospects = $this->get('Data');
        foreach ($salesprospects as $row)
        {
        	$select = array();
        	$select[] = JHTML::_('select.option', 0, '- ' . JText::_('COM_SEMINARMAN_CHOOSE_PLEASE') . ' -');
        	$courses = $model->getCourses();
        	foreach ($courses as $course)
        	{
        		$select[] = JHTML::_('select.option', $course->id, $course->text );
        	}
        	
        	$query = $db->getQuery(true);
        	$query->select( 'id' );
        	$query->from( '#__seminarman_courses' );
        	$query->where( 'templateId = '. (int)$row->template_id );
        	$query->setLimit( '1' );
        	$db->setQuery( $query );
        	$result = $db->loadObject();
        	
        	if ($row->notified_course != 0)
        		$selected = $row->notified_course;
        	elseif(!empty($result))
        		$selected = (int)$result->id;
        	else
        		$selected = 0;
        	
        	$row->select_course_notify = JHTML::_('select.genericlist', $select, 'select_course_notify'.$row->id, 'size="1" class="inputbox"', 'value', 'text', $selected);
        }
        
        $total = $this->get('Total');
        $pagination = $this->get('Pagination');

        $requestURL = $uri->toString();

        // build list of courses
        $titles = $this->get('Courses');
        $catlist[] = JHTML::_('select.option',  '0', '- '. JText::_( 'COM_SEMINARMAN_SELECT_COURSE' ). ' -', 'id');
        $catlist = array_merge( $catlist, $titles );
        $lists['courseid'] = JHTML::_('select.genericlist', $catlist, 'filter_courseid', 'class="inputbox" size="1" onchange="submitform( );"','id', 'text', $filter_courseid );

        $catlist = array();
    	// build list of templates
        $titles = $this->get( 'titles' );
    	$catlist[] = JHTML::_('select.option',  '0', '- '. JText::_( 'COM_SEMINARMAN_SELECT_TEMPLATE' ). ' -', 'id', 'title' );
    	$catlist = array_merge( $catlist, $titles );
    	$lists['templateid'] = JHTML::_('select.genericlist', $catlist, 'filter_templateid', 'class="inputbox" size="1" onchange="submitform( );"','id', 'title', $filter_templateid );
    	
    	//search filter - what field to use for search
    	$filters = array();
    	$filters[] = JHTML::_('select.option', '1', JText::_( 'COM_SEMINARMAN_LAST_NAME' ) );
    	$filters[] = JHTML::_('select.option', '2', JText::_( 'COM_SEMINARMAN_FIRST_NAME' ) );
    	$filters[] = JHTML::_('select.option', '3', JText::_( 'COM_SEMINARMAN_EMAIL' ) );
    	$lists['filter_search'] = JHTML::_('select.genericlist', $filters, 'filter_search', 'size="1" class="inputbox"', 'value', 'text', $filter_search );

        $lists['order_Dir'] = $filter_order_Dir;
        $lists['order'] = $filter_order;

        $lists['search'] = $search;
        
        $user = JFactory::getUser();
        $nulldate = $db->getNullDate();
        $this->assignRef('user', $user);
        $this->assignRef('lists', $lists);
        $this->assignRef('salesprospects', $salesprospects);
        $this->assignRef('pagination', $pagination);
        $this->assignRef('requestURL', $requestURL);
        $this->assignRef('nullDate', $nulldate);

        if(JHTMLSeminarman::UserIsCourseManager() || $params->get('tutor_access_sales_prospects')){
            parent::display($tpl);
        } else {
        	$mainframe->redirect('index.php?option=com_seminarman', 'Permission denied.');
        }
    }
}
