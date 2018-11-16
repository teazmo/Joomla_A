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

class SeminarmanViewTags extends JViewLegacy
{

    function display($tpl = null)
    {
        $mainframe = JFactory::getApplication();

        $db = JFactory::getDBO();
        $document = JFactory::getDocument();
        $user = JFactory::getUser();
        $lang = JFactory::getLanguage();
        
        $params = JComponentHelper::getParams( 'com_seminarman' );

        JHTML::_('behavior.tooltip');

        $filter_order = $mainframe->getUserStateFromRequest('com_seminarman.tags.filter_order', 'filter_order', 't.name', 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest('com_seminarman.tags.filter_order_Dir', 'filter_order_Dir', '', 'word');
        $filter_state = $mainframe->getUserStateFromRequest('com_seminarman.tags.filter_state', 'filter_state', '*', 'word');
        $filter_assigned = $mainframe->getUserStateFromRequest('com_seminarman.tags.filter_assigned', 'filter_assigned', '*', 'word');
        $search = $mainframe->getUserStateFromRequest('com_seminarman.tags.search', 'search',
            '', 'string');
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

        JToolBarHelper::title(JText::_('COM_SEMINARMAN_TAGS'), 'tags');
        JToolBarHelper::addNew();
        JToolBarHelper::editList();
        JToolBarHelper::divider();
        JToolBarHelper::publishList();
        JToolBarHelper::unpublishList();
        JToolBarHelper::divider();
        JToolBarHelper::deleteList();

        $rows = $this->get('Data');
        $pageNav = $this->get('Pagination');

        $lists = array();

        $assigned = array();
        $assigned[] = JHTML::_('select.option', '0', '- ' . JText::_('COM_SEMINARMAN_ALL_TAGS') . ' -');
        $assigned[] = JHTML::_('select.option', 'O', JText::_('COM_SEMINARMAN_ORPHANED'));
        $assigned[] = JHTML::_('select.option', 'A', JText::_('COM_SEMINARMAN_ASSIGNED'));

        $lists['assigned'] = JHTML::_('select.genericlist', $assigned, 'filter_assigned',
            'class="inputbox" size="1" onchange="submitform( );"', 'value', 'text', $filter_assigned);

        $lists['state'] = JHTML::_('grid.state', $filter_state, JText::_('JPUBLISHED'), JText::_('JUNPUBLISHED'));
        
        $lists['search'] = $search;

        $lists['order_Dir'] = $filter_order_Dir;
        $lists['order'] = $filter_order;

        $this->assignRef('lists', $lists);
        $this->assignRef('rows', $rows);
        $this->assignRef('user', $user);
        $this->assignRef('pageNav', $pageNav);
        $this->assignRef('direction', $lang);

        if(JHTMLSeminarman::UserIsCourseManager() || $params->get('tutor_access_tags')){
            parent::display($tpl);
        } else {
        	$mainframe->redirect('index.php?option=com_seminarman', 'Permission denied.');
        }
        
    }
}

?>
