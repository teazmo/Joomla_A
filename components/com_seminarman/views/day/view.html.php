<?php
/**
*
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2014-15 Open Source Group GmbH www.osg-gmbh.de
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
jimport('joomla.html.parameter');
jimport('joomla.html.pagination');

class SeminarmanViewDay extends JViewLegacy{
    function display($tpl = null)
    {
        $mainframe = JFactory::getApplication();

        $Itemid = JRequest::getInt('Itemid');

        $document = JFactory::getDocument();
        $menus = JFactory::getApplication()->getMenu();
        $menu = $menus->getActive();
        $model = $this->getModel();
        $params = $mainframe->getParams('com_seminarman');
        $uri = JFactory::getURI();
        $lang = JFactory::getLanguage();

        seminarman_html::addSiteStyles($this);

        $day = new stdClass();
        $day->id = $this->get('Id');
        $day->title = $this->get('Day');
        
        $courses = $this->get('Data');
        
        $limit = $mainframe->getUserStateFromRequest('com_seminarman.' . $this->getLayout() .'.limit', 'limit', $params->def('limit', 0), 'int');

        $count = count($courses);
        for($i = 0; $i < $count; $i++){
            $item = &$courses[$i];
            $item->count = $i;
            
            SMANFunctions::setCourse($item, null, $Itemid, JText::_('COM_SEMINARMAN_DATE_FORMAT2'), JText::_('COM_SEMINARMAN_TIME_FORMAT2'));
        }
        

        $lists = array();
        $lists['filter_order'] = $model->getState('filter_order');
        $lists['filter_order2'] = $model->getState('filter_order2');
        $lists['filter_order_Dir'] = $model->getState('filter_order_dir');
        $lists['filter_order_Dir2'] = $model->getState('filter_order_dir2');
        
        $lists['filter'] = JRequest::getString('filter');
        $lists['filter2'] = JRequest::getString('filter2');
        
        $experience_level[] = JHTML::_('select.option', '0', JText::_('COM_SEMINARMAN_ALL'), 'id', 'title');
        $titles = $this->get('Titles');
        $experience_level = array_merge($experience_level, $titles);
        $lists['filter_experience_level'] = JHTML::_('select.genericlist', $experience_level, 'filter_experience_level', 'class="inputbox" size="1" ', 'id', 'title', JRequest::getString('filter_experience_level'));
        $lists['filter_experience_level2'] = JHTML::_('select.genericlist', $experience_level, 'filter_experience_level2', 'class="inputbox" size="1" ', 'id', 'title', JRequest::getString('filter_experience_level2'));
        
        $pageNav = new JPagination($this->get('Total'), JRequest::getInt('limitstart'), $limit );
        $pageNav->setAdditionalUrlParam('filter_order', $lists['filter_order']);
        $pageNav->setAdditionalUrlParam('filter_order_Dir', $lists['filter_order_Dir']);
        if ($params->get('filter')) {
        	$filter_value = JRequest::getString('filter');
        	$filter_experience_level_value = JRequest::getString('filter_experience_level');
        	$pageNav->setAdditionalUrlParam('filter', $filter_value);
        	$pageNav->setAdditionalUrlParam('filter_experience_level', $filter_experience_level_value);
        }
        
        // $uri->setVar('start', 0);  
        $uri->delVar('start'); // the uri is only used by search, reset display to the first page
        $this->assign('action', $uri->toString());

        $this->assignRef('params', $params);
        $this->assignRef('day', $day);
        $this->assignRef('courses', $courses);
        $this->assignRef('pageNav', $pageNav);
        $this->assignRef('lists', $lists);

        parent::display($tpl);
    }
}

?>