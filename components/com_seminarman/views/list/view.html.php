<?php
/**
*
* Copyright (C) 2015 Open Source Group GmbH www.osg-gmbh.de
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
jimport('joomla.html.pagination');

class SeminarmanViewList extends JViewLegacy{
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

    	$courses = $this->get('Courses');
    	
    	$limit = $mainframe->getUserStateFromRequest('com_seminarman.' . $this->getLayout() .'.limit', 'limit', $params->def('limit', 0), 'int');

    	$count = count($courses);
    	for($i = 0; $i < $count; $i++){
    		$item = &$courses[$i];
    		$item->tags = $model->getTags($item->id);
    		$item->count = $i;
    		SMANFunctions::setCourse($item, null, $Itemid, JText::_('COM_SEMINARMAN_DATE_FORMAT2'), JText::_('COM_SEMINARMAN_TIME_FORMAT2'));  		 
    	}

    	$lists = array();
    	$lists['filter_order'] = $model->getState('filter_order');
    	$lists['filter_order_Dir'] = $model->getState('filter_order_dir');
    	
    	$pageNav = new JPagination($this->get('Total'), JRequest::getInt('limitstart'), $limit );
    	$pageNav->setAdditionalUrlParam('filter_order', $lists['filter_order']);
    	$pageNav->setAdditionalUrlParam('filter_order_Dir', $lists['filter_order_Dir']);    	
    	
    	$this->assign('action', $uri->toString());
    	
    	$this->assignRef('courses', $courses);
    	$this->assignRef('siteurl', $site_url);
    	$this->assignRef('params', $params);
    	$this->assignRef('pageNav', $pageNav);
    	$this->assignRef('lists', $lists);  	
    	
        parent::display($tpl);
        
    }
    
    function getCourseCategories($courseid)
    {
    	$db = JFactory::getDBO();
    	
    	$query = $db->getQuery(true);
    	$query->select( 'DISTINCT c.id' );
    	$query->select( 'c.title' );
    	$query->select( 'CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug' );
    	$query->from( '#__seminarman_categories AS c' );
    	$query->join( "LEFT", "#__seminarman_cats_course_relations AS rel ON rel.catid = c.id" );
    	$query->where( 'rel.courseid = ' . $courseid );
    
    	$db->setQuery($query);
    	
    	$course_cats = $db->loadObjectList();
    
    	return $course_cats;
    }    
}

?>
