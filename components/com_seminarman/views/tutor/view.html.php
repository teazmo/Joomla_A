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

class SeminarmanViewTutor extends JViewLegacy{
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
    	$site_url = JURI::root();
    	
        $jversion = new JVersion();
        $short_version = $jversion->getShortVersion();
    	 
    	$lang = JFactory::getLanguage();
    	seminarman_html::addSiteStyles($this);
    	 
    	$pathway = $mainframe->getPathWay(); 

    	$tutor = $this->get('Tutor');
    	$courses = $this->get('Courses');

    	$count = count($courses);
    	for($i = 0; $i < $count; $i++){
    		$item = &$courses[$i];
    		$item->count=$i;

    		SMANFunctions::setCourse($item, null, $Itemid, JText::_('COM_SEMINARMAN_DATE_FORMAT2'), JText::_('COM_SEMINARMAN_TIME_FORMAT2'));
    	}
    	
    	$data = new stdClass();
    	$data->customfields = $model->getEditableCustomfields($tutor->tutor_id);
    	CMFactory::load('libraries' , 'customfields');
    	
    	$filter_order = JRequest::getCmd('filter_order', 'i.title');
    	$filter_order_Dir = JRequest::getCmd('filter_order_Dir', 'ASC');
    	$filter = JRequest::getString('filter');
    	
    	$lists = array();
    	$lists['filter_order'] = $filter_order;
    	$lists['filter_order_Dir'] = $filter_order_Dir;
    	$lists['filter'] = $filter;
    	
    	$this->assign('action', $uri->toString());

    	$this->assignRef('params', $params);
    	$fields = $data->customfields ['fields'];
    	$this->assignRef('fields', $fields);
    	$this->assignRef('tutor', $tutor);
    	$this->assignRef('courses', $courses);
    	$this->assignRef('siteurl', $site_url);
    	$this->assignRef('lists', $lists);
    	
        parent::display($tpl);
        
    }
    
}

?>
