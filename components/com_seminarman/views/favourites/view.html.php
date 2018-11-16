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

class SeminarmanViewFavourites extends JViewLegacy{
    function display($tpl = null)
    {
        $mainframe = JFactory::getApplication();
        jimport( 'joomla.html.parameter' );
        
        $Itemid = JRequest::getInt('Itemid');

        $document = JFactory::getDocument();
        $menus = JFactory::getApplication()->getMenu();
        $menu = $menus->getActive();
        $params = $mainframe->getParams('com_seminarman');
        $uri = JFactory::getURI();
        $lang = JFactory::getLanguage();
        $model = $this->getModel('favourites');
        $user = JFactory::getUser();

        $limitstart = JRequest::getInt('limitstart');
        $limit = JRequest::getInt('limit', $params->get('course_num'));

        seminarman_html::addSiteStyles($this);

        $jversion = new JVersion();
        $short_version = $jversion->getShortVersion();
        
        if (is_object($menu)){
        	if (version_compare($short_version, "3.0", 'ge')) {
        	    $menu_params = new JRegistry($menu->params);
        	} else {
        		$menu_params = new JParameter($menu->params);
        	}

            if (!$menu_params->get('page_title')){
                $params->set('page_title', JText::_('COM_SEMINARMAN_MY_FAVOURITES'));
            }
        }else{
            $params->set('page_title', JText::_('COM_SEMINARMAN_MY_FAVOURITES'));
        }

        $pathway = $mainframe->getPathWay();
        if ($params->get('enable_component_pathway')) {
        $pathway->addItem($params->get('page_title'), JRoute::_('index.php?option=com_seminarman&view=favourites' . '&Itemid=' . $Itemid));
        }

        $document->setTitle($params->get('page_title'));
        $document->setMetadata('keywords', $params->get('page_title'));

            if ($user->get('guest')){
                $redirectUrl = JRoute::_('index.php?option=com_seminarman&view=favourites' . '&Itemid=' . $Itemid, false);
                $redirectUrl = base64_encode($redirectUrl);
                $redirectUrl = '&return=' . $redirectUrl;
                $joomlaLoginUrl = 'index.php?option=com_users&view=login';
                $finalUrl = $joomlaLoginUrl . $redirectUrl;
                $mainframe->redirect($finalUrl, JText::_('COM_SEMINARMAN_PLEASE_LOGIN_FIRST'));
            }

        $courses = $this->get('Data');
        $total = $this->get('Total');


        $count = count($courses);
    	for($i = 0; $i < $count; $i++){
    		$item = &$courses[$i];
    		$item->count=$i;
    		$category = $model->getCategory($item->id);

            SMANFunctions::setCourse($item, $category, $Itemid, JText::_('COM_SEMINARMAN_DATE_FORMAT2'), JText::_('COM_SEMINARMAN_TIME_FORMAT2'));    		
    	}


        $filter_order = JRequest::getCmd('filter_order', 'i.title');
        $filter_order_Dir = JRequest::getCmd('filter_order_Dir', 'ASC');
        $filter = JRequest::getString('filter');

        $lists = array();
        $lists['filter_order'] = $filter_order;
        $lists['filter_order_Dir'] = $filter_order_Dir;
        $lists['filter'] = $filter;

        $filter_experience_level = JRequest::getString('filter_experience_level');
        $filter_positiontype = JRequest::getString('filter_positiontype');

        $experience_level[] = JHTML::_('select.option', '0', JText::_('COM_SEMINARMAN_ALL'), 'id', 'title');
        $titles = $this->get('titles');
        $experience_level = array_merge($experience_level, $titles);
        $lists['filter_experience_level'] = JHTML::_('select.genericlist', $experience_level,
            'filter_experience_level', 'class="inputbox" size="1" ', 'id', 'title', $filter_experience_level);

        jimport('joomla.html.pagination');

        $pageNav = new JPagination($total, $limitstart, $limit);
        $page = $total - $limit;
        if ($params->get('filter')) {
        	$filter_value = JRequest::getString('filter');
        	$filter_experience_level_value = JRequest::getString('filter_experience_level');
        	$pageNav->setAdditionalUrlParam('filter', $filter_value);
        	$pageNav->setAdditionalUrlParam('filter_experience_level', $filter_experience_level_value);
        }
        
        // $uri->setVar('start', 0);  
        $uri->delVar('start'); // the uri is only used by search, reset display to the first page
        $this->assign('action', $uri->toString());

        $this->assignRef('courses', $courses);
        $this->assignRef('category', $category);
        $this->assignRef('params', $params);
        $this->assignRef('page', $page);
        $this->assignRef('pageNav', $pageNav);
        $this->assignRef('lists', $lists);

        parent::display($tpl);
    }
}

?>