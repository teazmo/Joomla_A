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

class seminarmanViewapplications extends JViewLegacy
{
    function display($tpl = null)
    {
        $mainframe = JFactory::getApplication();

        $db = JFactory::getDBO();
        $uri = JFactory::getURI();
        $childviewname = 'application';
        $document = JFactory::getDocument();
        $params = JComponentHelper::getParams('com_seminarman');
        $lang = JFactory::getLanguage();

        $document->addStyleSheet('components/com_seminarman/assets/css/seminarmanbackend.css');
        if(JVERSION >= 3.0) {
        	$document->addStyleSheet('components/com_seminarman/assets/css/seminarmanbackend_j3.x.css');
        }
        if ($lang->isRTL())
        {
            $document->addStyleSheet('components/com_seminarman/assets/css/seminarmanbackend_rtl.css');
        }

        require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS .
            'helpers' . DS . 'seminarman.php');

        JHTMLSeminarman::buildSideMenu();

        if (SeminarmanFunctions::isSmanbookingPlgEnabled() && $params->get('advanced_booking')) {
            $headline = JText::_('COM_SEMINARMAN_APPLICATIONS') . ' ('.JText::_('COM_SEMINARMAN_ENHANCED').')';
        } else {
        	$headline = JText::_('COM_SEMINARMAN_APPLICATIONS');
        }
        JToolBarHelper::title($headline, 'applications');

        // Joomla Version specific adjustments
        if(JVERSION < 3.0) {
        	$mail_icon = 'send';
        	$pdf_icon = 'pdf';
        } else {
        	$mail_icon = 'envelope';
        	$pdf_icon = '14-pdf';
        }

        $alt = JText::_('COM_SEMINARMAN_SEND_EMAIL');
        $ccert = JText::_('COM_SEMINARMAN_CREATE_CERTIFICATE');
        $bar = JToolBar::getInstance( 'toolbar' );
        JToolBarHelper::custom('createCertificates', $pdf_icon, 'icon over', $ccert, true);
        $bar->appendButton( 'Standard', $mail_icon, $alt, 'notify', true );        
        // JToolBarHelper::custom('Popup', 'send', 'send', $alt, true);
        JToolBarHelper::divider();
        
        if (SeminarmanFunctions::isSmanbookingPlgEnabled() && $params->get('advanced_booking')) {
            $dispatcher=JDispatcher::getInstance();
            JPluginHelper::importPlugin('seminarman');
            $dispatcher->trigger('onAddManualBookingToolbar',array());
        }
        
        if (JVERSION >= 3.0) {
        	JToolBarHelper::editList();
        } else {
        	JToolBarHelper::editListX();
        }        
        JToolBarHelper::divider();
        JToolBarHelper::publishList();
        JToolBarHelper::unpublishList();
        JToolBarHelper::divider();
        JToolBarHelper::trash('trash');
        if ($params->get('enable_bookings_deletable') == 1)
        	JToolBarHelper::deleteList();

        $filter_state = $mainframe->getUserStateFromRequest('com_seminarman' . $childviewname . '.filter_state', 'filter_state', '', 'word');
        $filter_order = $mainframe->getUserStateFromRequest('com_seminarman' . $childviewname . '.filter_order', 'filter_order', 'a.id', 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest('com_seminarman' . $childviewname . '.filter_order_Dir', 'filter_order_Dir', 'desc', 'word');
    	$filter_courseid = $mainframe->getUserStateFromRequest('com_seminarman'.'.applications.filter_courseid', 'filter_courseid', 0, 'int' );
    	$filter_statusid = $mainframe->getUserStateFromRequest('com_seminarman'.'.applications.filter_statusid', 'filter_statusid', 0, 'int' );
    	$filter_search = $mainframe->getUserStateFromRequest('com_seminarman'.'.applications.filter_search', 'filter_search', '', 'int' );

        $search = $mainframe->getUserStateFromRequest('com_seminarman' . $childviewname . '.search', 'search', '', 'string');
        $search = JString::strtolower($search);

        $applications = $this->get('Data');
        $total = $this->get('Total');
        $pagination = $this->get('Pagination');

        $requestURL = $uri->toString();

    	$titles = $this->get( 'titles' );

    	// build list of courses
    	$javascript = 'onchange="document.adminForm.submit();"';
    	$catlist[] = JHTML::_('select.option',  '0', '- '. JText::_( 'COM_SEMINARMAN_SELECT_COURSE' ). ' -', 'id', 'title' );
    	$catlist = array_merge( $catlist, $titles );
    	$lists['courseid'] = JHTML::_('select.genericlist', $catlist, 'filter_courseid', 'class="inputbox" size="1" onchange="submitform( );"','id', 'title', $filter_courseid );

    	//search filter - what field to use for search
    	$filters = array();
    	$filters[] = JHTML::_('select.option', '1', JText::_( 'COM_SEMINARMAN_LAST_NAME' ) );
    	$filters[] = JHTML::_('select.option', '2', JText::_( 'COM_SEMINARMAN_FIRST_NAME' ) );
    	$filters[] = JHTML::_('select.option', '3', JText::_( 'COM_SEMINARMAN_EMAIL' ) );
    	$filters[] = JHTML::_('select.option', '4', JText::_( 'COM_SEMINARMAN_COURSE_CODE' ) );
    	$lists['filter_search'] = JHTML::_('select.genericlist', $filters, 'filter_search', 'size="1" class="inputbox"', 'value', 'text', $filter_search );


    	// build list of states
    	$javascript = 'onchange="document.adminForm.submit();"';
    	$statuslist[] = JHTML::_('select.option',  '0', '- '. JText::_( 'JLIB_HTML_SELECT_STATE' ). ' -', 'id', 'state' );
    	$statuslist[] = JHTML::_('select.option',  '1', JText::_( 'COM_SEMINARMAN_SUBMITTED' ), 'id', 'state' );
    	$statuslist[] = JHTML::_('select.option',  '2', JText::_( 'COM_SEMINARMAN_PENDING' ), 'id', 'state' );
    	$statuslist[] = JHTML::_('select.option',  '3', JText::_( 'COM_SEMINARMAN_PAID' ), 'id', 'state' );
    	$statuslist[] = JHTML::_('select.option',  '4', JText::_( 'COM_SEMINARMAN_CANCELED' ), 'id', 'state' );
    	$statuslist[] = JHTML::_('select.option',  '5', JText::_( 'COM_SEMINARMAN_WL' ), 'id', 'state' );
    	$statuslist[] = JHTML::_('select.option',  '6', JText::_( 'COM_SEMINARMAN_AWAITING_RESPONSE' ), 'id', 'state' );
    	$lists['statusid'] = JHTML::_('select.genericlist', $statuslist, 'filter_statusid', 'class="inputbox" size="1" onchange="submitform( );"','id', 'state', $filter_statusid );

        $lists['state'] = JHTML::_('grid.state', $filter_state, JText::_('JPUBLISHED'), JText::_('JUNPUBLISHED'), null, JText::_('JTRASHED'));
        $lists['order_Dir'] = $filter_order_Dir;
        $lists['order'] = $filter_order;
        $lists['search'] = $search;

       	foreach ($applications as $row)
       	{  
       		// fix for 24:00:00 (illegal time colock)
       		if ($row->start_time == '24:00:00') $row->start_time = '23:59:59';
       		if ($row->finish_time == '24:00:00') $row->finish_time = '23:59:59';
       		
       		if ($row->start_date != '0000-00-00')
	    		$row->start_date = JHTML::date($row->start_date . ' ' .$row->start_time, JText::_('COM_SEMINARMAN_DATE_FORMAT1'));
	       	else
	       		$row->start_date = JText::_('COM_SEMINARMAN_NOT_SPECIFIED');
	        if ($row->finish_date != '0000-00-00')
	        	$row->finish_date = JHTML::date($row->finish_date . ' ' .$row->finish_time, JText::_('COM_SEMINARMAN_DATE_FORMAT1'));
	        else
	        	$row->finish_date = JText::_('COM_SEMINARMAN_NOT_SPECIFIED');
       	}
		
       	$user = JFactory::getUser();
        $this->assignRef('user', $user);
        $this->assignRef('lists', $lists);
        $this->assignRef('applications', $applications);
        $this->assignRef('pagination', $pagination);
        $this->assignRef('requestURL', $requestURL);

        parent::display($tpl);
    }
}
