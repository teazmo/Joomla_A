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

class SeminarmanViewCourses extends JViewLegacy
{

    function display($tpl = null)
    {
        $mainframe = JFactory::getApplication();

        $user = JFactory::getUser();
        $db = JFactory::getDBO();
        $document = JFactory::getDocument();
        $lang = JFactory::getLanguage();
    	$params = JComponentHelper::getParams( 'com_seminarman' );


        JHTML::_('behavior.tooltip');

        $filter_order = $mainframe->getUserStateFromRequest('com_seminarman.courses.filter_order', 'filter_order', 'i.ordering', 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest('com_seminarman.courses.filter_order_Dir', 'filter_order_Dir', '', 'word');
        $filter_state = $mainframe->getUserStateFromRequest('com_seminarman.courses.filter_state', 'filter_state', '*', 'word');
		$filter_category = $mainframe->getUserStateFromRequest('com_seminarman.courses.filter_category', 'filter_category', '*');
		$filter_search = $mainframe->getUserStateFromRequest('com_seminarman'.'.applications.filter_search', 'filter_search', '', 'int' );
    	$search = $mainframe->getUserStateFromRequest('com_seminarman.courses.search', 'search', '', 'string');
        $search = $db->escape(trim(JString::strtolower($search)));

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

        JToolBarHelper::title(JText::_('COM_SEMINARMAN_COURSES'), 'courses');
        if ((JHTMLSeminarman::UserIsCourseManager()) || (JHTMLSeminarman::getUserTutorID() > 0)) JToolBarHelper::addNew();
        JToolBarHelper::editList();
        JToolBarHelper::divider();
        JToolBarHelper::publishList();
        JToolBarHelper::unpublishList();
        JToolBarHelper::archiveList();
        JToolBarHelper::divider();
        if ($filter_state == 'T') {
        	JToolBarHelper::deleteList();        	
        } else {
            JToolBarHelper::trash('trash');
        }
        JToolBarHelper::divider();
        if (version_compare($short_version, "3.0", 'ge')) {
        	JToolBarHelper::custom('attendancelist','file-2','file-2', JText::_('COM_SEMINARMAN_ATTENDANCE_LIST').' (PDF)');
    	} else {
    		JToolBarHelper::custom('attendancelist','stats','stats', JText::_('COM_SEMINARMAN_ATTENDANCE_LIST').' (PDF)');
    	}
        
        if (SeminarmanFunctions::isSmanpdflistPlgEnabled() && $params->get('alt_pdflist')) {
        	$plugin_pdflist = JPluginHelper::getPlugin('seminarman', 'smanpdflist');
        	$pdflist_params = new JRegistry($plugin_pdflist->params);
        	if (($pdflist_params->get('template_1_id') > 0)) {
        		if (version_compare($short_version, "3.0", 'ge')) {
        			JToolBarHelper::custom('attendancelist_alt', 'file-2','file-2', $pdflist_params->get('template_1_title'), true);
    			} else {
    				JToolBarHelper::custom('attendancelist_alt', 'stats', 'stats', $pdflist_params->get('template_1_title'), true);
    			}
        	}
        }
        
        if (SeminarmanFunctions::isSmanpdflistPlgEnabled() && $params->get('alt_pdflist')) {
        	$plugin_pdflist = JPluginHelper::getPlugin('seminarman', 'smanpdflist');
        	$pdflist_params = new JRegistry($plugin_pdflist->params);
        	if (($pdflist_params->get('template_2_id') > 0)) {
        		if (version_compare($short_version, "3.0", 'ge')) {
        			JToolBarHelper::custom('attendancelist_alt_three', 'file-2','file-2', $pdflist_params->get('template_2_title'), true);
    			} else {
    				JToolBarHelper::custom('attendancelist_alt_three', 'stats', 'stats', $pdflist_params->get('template_2_title'), true);
    			}
        	}
        }
        
        if (version_compare($short_version, "3.0", 'ge')) {
        	JToolBarHelper::custom('certificatelist','file-2','file-2', JText::_('COM_SEMINARMAN_SERIAL_CERTIFICATE').' (PDF)', true);
    	} else {
    		JToolBarHelper::custom('certificatelist','stats','stats', JText::_('COM_SEMINARMAN_SERIAL_CERTIFICATE').' (PDF)', true);
    	}
        
        $rows = $this->get('Data');
        $pageNav = $this->get('Pagination');
        
        //search filter - what field to use for search
        $filters = array();
        $filters[] = JHTML::_('select.option', '1', JText::_( 'COM_SEMINARMAN_COURSE_TITLE' ) );
        $filters[] = JHTML::_('select.option', '2', JText::_( 'COM_SEMINARMAN_COURSE_CODE' ) );
        $lists['filter_search'] = JHTML::_('select.genericlist', $filters, 'filter_search', 'size="1" class="inputbox"', 'value', 'text', $filter_search );

    	foreach ($rows as $row):
    	$db = JFactory::getDBO();
    	$query = $db->getQuery(true);
    	$query->select( '*' );
    	$query->from( '#__seminarman_sessions' );
    	$query->where( 'published = 1' );
    	$query->where( 'courseid = '.$row->id );
    	$query->order( 'ordering' );
    	$db->setQuery( $query );
    		
    	$course_sessions = $db->loadObjectList();
		$row->count_sessions = count($course_sessions);

		// fix for 24:00:00 (illegal time colock)
		if ($row->start_time == '24:00:00') $row->start_time = '23:59:59';
		if ($row->finish_time == '24:00:00') $row->finish_time = '23:59:59';		
		
    	//set start and finish dates
    	if ($row->start_date != '0000-00-00')
    		$row->start_date = JHTML::_('date', $row->start_date . ' ' . $row->start_time, JText::_('COM_SEMINARMAN_DATE_FORMAT1'));
        else
        	$row->start_date = JText::_('COM_SEMINARMAN_NOT_SPECIFIED');

        if ($row->finish_date != '0000-00-00')
        	$row->finish_date = JHTML::_('date', $row->finish_date . ' ' . $row->finish_time, JText::_('COM_SEMINARMAN_DATE_FORMAT1'));
        else
        	$row->finish_date = JText::_('COM_SEMINARMAN_NOT_SPECIFIED');

    		//capacity check
    		switch ($params->get('current_capacity'))
    		{
    			// cases for a parameter
    			case 1:
    				$current_capacity_setting=-1;
    				break;

    			case 2:
    				$current_capacity_setting=0;
    				break;

    			default:
    				$current_capacity_setting=-1;
    				break;
    		}
    		//add currentbookings information
    		$db = JFactory::getDBO();
    		$query = $db->getQuery(true);
    		
    		$query->select( 'SUM(b.attendees)' );
    		$query->from( '#__seminarman_application AS b' );
    		$query->where( 'b.published = 1' );
    		$query->where( 'b.course_id = '.$row->id );
    		$query->where( 'b.status > '.$current_capacity_setting );
    		$query->where( '( b.status < 3 OR b.status = 5 )' );
    		
    		$db->setQuery( $query );
			$row->currentBookings = $db->loadResult();
			
			if (empty($row->currentBookings)) $row->currentBookings = 0;
    		$row->currentAvailability = ($row->capacity)-($row->currentBookings);
    		
        	$data_attribs = new JRegistry();
        	$data_attribs->loadString($row->attribs);
        	$row->color = $data_attribs->get('color');
		endforeach;
		
        $lists['state'] = JHTML::_('grid.state', $filter_state, JText::_('JPUBLISHED'), JText::_('JUNPUBLISHED'), JText::_('JARCHIVED'), JText::_('JTRASHED'));
        $lists['search'] = $search;
        $lists['order_Dir'] = $filter_order_Dir;
        $lists['order'] = $filter_order;
        $ordering = ($lists['order'] == 'i.ordering');
        $categories = seminarman_cats::getCategoriesTree(0);
        $lists['category'] = seminarman_cats::buildcatselect($categories, 'filter_category', $filter_category, true, 'class="inputbox" onchange="submitform( );"');

        $this->assignRef('lists', $lists);
        $this->assignRef('rows', $rows);
        $this->assignRef('pageNav', $pageNav);
        $this->assignRef('ordering', $ordering);
        $this->assignRef('user', $user);
        $this->assignRef('direction', $lang);

        parent::display($tpl);
    }
}

?>
