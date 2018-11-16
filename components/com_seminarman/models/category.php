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

jimport('joomla.application.component.model');

class SeminarmanModelCategory extends JModelLegacy
{
    var $_id = null;

    var $_data = null;

    var $_childs = null;

    var $_category = null;

    var $_total = null;
    
    var $_bookings = null; // holds the course id of all applications of the current user

    function __construct()
    {
        parent::__construct();

        $mainframe = JFactory::getApplication();

        $params = $mainframe->getParams('com_seminarman');
        
        $orderingDef = $params->get('list_ordering');
        switch ($orderingDef)
        {
        	case '0' :
        		$ordering = 'i.title';
        		break;
        
        	case '1' :
        		$ordering = 'i.start_date';
        		break;
        
        	case '2' :
        		$ordering = 'i.ordering';
        		break;
        }

        $cid = JRequest::getInt('cid', 0);
        $this->setId((int)$cid);
        
        $limit = $mainframe->getUserStateFromRequest('com_seminarman.category.limit', 'limit', $params->def('limit', 0), 'int');
        // $limit = $mainframe->getUserStateFromRequest('com_seminarman.category.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $limitstart = JRequest::getVar('limitstart', 0, '', 'int');
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
        
        $tmpl_limit = $mainframe->getUserStateFromRequest('com_seminarman.category.tmpl_limit', 'tmpl_limit', $mainframe->getCfg('list_limit'), 'int');
        $tmpl_limitstart = JRequest::getVar('tmpl_limitstart', 0, '', 'int');
        $tmpl_limitstart = ($tmpl_limit != 0 ? (floor($tmpl_limitstart / $tmpl_limit) * $tmpl_limit) : 0);

        $archive_limit = $mainframe->getUserStateFromRequest('com_seminarman.category.archive_limit', 'archive_limit', $mainframe->getCfg('list_limit'), 'int');
        $archive_limitstart = JRequest::getVar('archive_limitstart', 0, '', 'int');
        $archive_limitstart = ($archive_limit != 0 ? (floor($archive_limitstart / $archive_limit) * $archive_limit) : 0); 
        
        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
        $this->setState('tmpl_limit', $tmpl_limit);
        $this->setState('tmpl_limitstart', $tmpl_limitstart);
        $this->setState('archive_limit', $archive_limit);
        $this->setState('archive_limitstart', $archive_limitstart);
        
        $this->setState('filter_order', JRequest::getCmd('filter_order', $ordering));
        $this->setState('filter_order_dir', JRequest::getCmd('filter_order_Dir', 'ASC'));
        $this->setState('filter_order2', JRequest::getCmd('filter_order2', $ordering));
        $this->setState('filter_order_dir2', JRequest::getCmd('filter_order_Dir2', 'ASC'));
    }

    function setId($cid)
    {

        $this->_id = $cid;

    }

    function getData()
    {
    	
        if (empty($this->_data))
        {
            $query = $this->_buildQuery($this->_id);
            $this->_data = $this->_getList($query, $this->getState('limitstart'), $this->
                getState('limit'));
        }

        return $this->_data;
    }

    function getTotal()
    {

        if (empty($this->_total))
        {
            $query = $this->_buildQuery();
            $this->_total = $this->_getListCount($query);
        }

        return $this->_total;
    }

    function _buildQuery()
    {
        $where = $this->_buildCourseWhere();

    	$query = SMANFunctions::buildCourseQuery($this, $this->_db, $where);
        return $query;
    }

    function _buildCourseWhere()
    {
    	$where = SMANFunctions::buildCourseWhere($this->_db);
        $where .= ' AND rel.catid = ' . $this->_id;
        return $where;
    }
    
    function getArchive() {
    	$where = SMANFunctions::buildCourseWhere($this->_db, 2);
    	$where .= ' AND rel.catid = ' . $this->_id;
    	$query = SMANFunctions::buildCourseQuery($this, $this->_db, $where);
    	$archive = $this->_getList($query, $this->getState('archive_limitstart'), $this->getState('archive_limit'));
    	return $archive;
    }
    
    function getArchiveTotal() {
    	$where = SMANFunctions::buildCourseWhere($this->_db, 2);
    	$where .= ' AND rel.catid = ' . $this->_id;
    	$query = SMANFunctions::buildCourseQuery($this, $this->_db, $where);
    	$archiveCount = $this->_getListCount($query);
    	return $archiveCount;    	
    }

    function _buildChildsquery()
    {
        $mainframe = JFactory::getApplication();
        
        $user = JFactory::getUser();
        $gid = (int)$user->get('aid');
        $jnow = JFactory::getDate();
        // $now = $jnow->toMySQL();
        $now = $jnow->toSQL();
        $nullDate = $this->_db->getNullDate();

        $state = 1;
        
        $params = $mainframe->getParams('com_seminarman');

        $where = ' WHERE cc.published = 1';
        $where .= ' AND cc.parent_id = ' . $this->_id;
        $where .= ' AND c.id = cc.id';

        switch ($state)
        {
            case 1:
	        	switch($params->get('publish_down')) {
	        		case '1':
	        			$publish_down = 'CONCAT_WS(\' \', i.start_date, i.start_time)';
	        			break;
	        		case '2':
	        			$publish_down = 'CONCAT_WS(\' \', i.finish_date, i.finish_time)';
	        			break;
	        		default:
	        			$publish_down = 'i.publish_down';
	          	}

                $where .= ' AND i.state = 1' . ' AND ( publish_up = ' . $this->_db->Quote($nullDate) .
                    ' OR i.publish_up <= ' . $this->_db->Quote($now) . ' )' . ' AND ( ' . $publish_down . ' = ' .
                    $this->_db->Quote($nullDate) . ' OR ' . $publish_down . ' >= ' . $this->_db->Quote($now) .
                    ' )';

                break;

            case - 1:

                $year = JRequest::getInt('year', date('Y'));
                $month = JRequest::getInt('month', date('m'));

                $where .= ' AND i.state = -1';
                $where .= ' AND YEAR( i.created ) = ' . (int)$year;
                $where .= ' AND MONTH( i.created ) = ' . (int)$month;
                break;

            default:
                $where .= ' AND i.state = ' . (int)$state;
                break;
        }
        
        $query = $this->_db->getQuery(true);
        $query->select( 'c.*' );
        $query->select( 'CASE WHEN CHAR_LENGTH( c.alias ) THEN CONCAT_WS( \':\', c.id, c.alias ) ELSE c.id END AS slug' );
        $query->select( '( SELECT COUNT( DISTINCT i.id ) FROM #__seminarman_courses AS i' .
            ' LEFT JOIN #__seminarman_cats_course_relations AS rel ON rel.courseid = i.id' .
            ' LEFT JOIN #__seminarman_categories AS cc ON cc.id = rel.catid' . $where .
            ' GROUP BY cc.id ) AS assignedseminarmans' );
        $query->from( '#__seminarman_categories AS c' );
        $query->where( 'c.published = 1' );
        $query->where( 'c.parent_id = ' . $this->_id );
        $query->order( 'ordering ASC' );
        
        return $query;
    }
    function getChilds()
    {
        $query = $this->_buildChildsquery();
        $this->_childs = $this->_getList($query);

        $k = 0;
        $count = count($this->_childs);
        for ($i = 0; $i < $count; $i++)
        {
            $category = &$this->_childs[$i];

            $category->subcats = $this->_getsubs($category->id);

            $k = 1 - $k;
        }
        
        return $this->_childs;
    }

    function _getsubs($id)
    {
        $user = JFactory::getUser();
        $gid = (int)$user->get('aid');
        
        $query = $this->_db->getQuery(true);
        $query->select( '*' );
        $query->select( 'CASE WHEN CHAR_LENGTH(alias) THEN CONCAT_WS(\':\', id, alias) ELSE id END as slug' );
        $query->from( '#__seminarman_categories' );
        $query->where( 'published = 1' );
        $query->where( 'parent_id = ' . (int)$id );
        $query->order( 'ordering ASC' );
        
        $this->_db->setQuery($query);
        $this->_subs = $this->_db->loadObjectList();

        return $this->_subs;
    }

    function getTags($id)
    {
    	$query = $this->_db->getQuery(true);
    	$query->select( 'DISTINCT t.name' );
    	$query->select( 'CASE WHEN CHAR_LENGTH(t.alias) THEN CONCAT_WS(\':\', t.id, t.alias) ELSE t.id END as slug' );
    	$query->from( '#__seminarman_tags AS t' );
    	$query->join( "LEFT", '#__seminarman_tags_course_relations AS i ON i.tid = t.id' );
    	$query->where( 'i.courseid = ' . $id );
    	$query->where( 't.published = 1' );
    	$query->order( 't.name' );
    	
    	$this->_db->setQuery($query);
    
    	return $this->_db->loadObjectList();
    }
    
    function getCategory()
    {
        $query = $this->_db->getQuery(true);
        $query->select( 'c.*' );
        $query->select( 'CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug' );
        $query->from( '#__seminarman_categories AS c' );
        $query->where( 'c.id = ' . $this->_id );
        $query->where( 'c.published = 1' );
        
        $this->_db->setQuery($query);
        $this->_category = $this->_db->loadObject();

        if (!$this->_category->published)
        {
            JError::raiseError(404, JText::sprintf('CATEGORY #%d NOT FOUND', $this->_id));
            return false;
        }

        $mainframe = JFactory::getApplication();
        $params = $mainframe->getParams('com_seminarman');
        $orderingDef = $params->get('list_ordering');
        switch ($orderingDef)
        {
            case '0' :
                $ordering = 'i.title';
                break;
                
            case '1' :
                $ordering = 'i.start_date';
                break;
                
            case '2' :
                $ordering = 'i.ordering';
                break;
        }
        $cat_attribs = new JRegistry();
        $cat_attribs->loadString($this->_category->params);
        $cat_attribs_arr = $cat_attribs->toArray();
        
        if (isset($cat_attribs_arr['sorting_option']) && !empty($cat_attribs_arr['sorting_option'])) $ordering = $cat_attribs_arr['sorting_option'];
        if (isset($cat_attribs_arr['sorting_direction']) && !empty($cat_attribs_arr['sorting_direction'])) {
            $ordering_dir = $cat_attribs_arr['sorting_direction'];
        } else {
            $ordering_dir = 'ASC';
        }
        $this->setState('filter_order', JRequest::getCmd('filter_order', $ordering));
        $this->setState('filter_order_dir', JRequest::getCmd('filter_order_Dir', $ordering_dir));  
        
        return $this->_category;
    }

	function getFavourites($courseid)
	{
		$query = $this->_db->getQuery(true);
		$query->select( 'COUNT(id) AS favs' );
		$query->from( '#__seminarman_favourites' );
		$query->where( 'courseid = ' . (int)$courseid );
		    
		$this->_db->setQuery($query);
		$favs = $this->_db->loadResult();
		return $favs;
	}

	function getFavoured($courseid)
	{
		$user = JFactory::getUser();

		$query = $this->_db->getQuery(true);
		$query->select( 'COUNT(id) AS fav' );
		$query->from( '#__seminarman_favourites' );
		$query->where( 'courseid = ' . (int)$courseid );
		$query->where( 'userid= ' . (int)$user->id );
		    
		$this->_db->setQuery($query);
		$fav = $this->_db->loadResult();
		return $fav;
	}

    function gettitles()
    {
        $user = JFactory::getUser();
        $gid = (int)$user->get('aid');

        $query = $this->_db->getQuery(true);
        $query->select( 'id' );
        $query->select( 'title' );
        $query->from( '#__seminarman_experience_level' );
        $query->where( 'published = 1' );
        $query->order( 'ordering ASC' );

        $this->_db->setQuery($query);
        $this->_subs = $this->_db->loadObjectList();

        return $this->_subs;
    }
    
    function _buildQueryLstOfProspects()
    {
    	$query = $this->_db->getQuery(true);
    	$query->select( 'DISTINCT i.*' );
    	$query->select( 'gr.title AS cgroup' );
    	$query->select( 'lev.title AS level' );
    	$query->select( 'i.id AS slug' );
    	$query->from( '`#__seminarman_templates` AS i' );
    	$query->join( "LEFT", '`#__seminarman_cats_template_relations` AS rel ON rel.templateid = i.id');
    	$query->join( "LEFT", '`#__seminarman_atgroup` AS gr ON gr.id = i.id_group');
    	$query->join( "LEFT", '`#__seminarman_experience_level` AS lev ON lev.id = i.id_experience_level');
    	$query->where( 'i.state=1' );
    	$query->where( 'rel.catid='. $this->_id );
    	
    	$this->_db->setQuery( $query );
    	
   		$mainframe = JFactory::getApplication();
   		$params = $mainframe->getParams('com_seminarman');
   		
   		if ($params->get('filter'))
   		{
   			$filter2 = JRequest::getString('filter2', '', 'request');
   			$filter_experience_level2 = JRequest::getString('filter_experience_level2', '', 'request');
   		
   			if ($filter2) {
   				$filter2 = $this->_db->escape(trim(JString::strtolower($filter2)));
   				$like = $this->_db->Quote('%' . $this->_db->escape($filter2, true) . '%', false);
    			$query->where( '( LOWER( i.title ) LIKE ' . $like .' OR LOWER( i.code ) LIKE '. $like .')' );
   					
   			}
   		
	   		if ($filter_experience_level2 > 0)
    			$query->where( 'LOWER( i.id_experience_level ) = ' . $filter_experience_level2 );
	    }
   		

   		$filter_order2 = $this->getState('filter_order2');
   		$filter_order_dir2 = $this->getState('filter_order_dir2');
   		
    	$query->order( $filter_order2 .' '. $filter_order_dir2 . ', i.title' );
    	
   		return $query;
    }
    
    function getLstOfProspects()
    {
   		return $this->_getList($this->_buildQueryLstOfProspects(), $this->getState('tmpl_limitstart'), $this->getState('tmpl_limit'));
    }
    
    function getTotalLstOfProspects()
    {
    	return $this->_getListCount($this->_buildQueryLstOfProspects());
    }
    
    function hasUserBooked($course_id)
    {
    	if (empty($this->_bookings))
    		$this->_loadBookings();
    	
    	return in_array($course_id, $this->_bookings);
    }
    
	function _loadBookings()
	{
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		
		$query = $this->_db->getQuery(true);
		$query->select( 'course_id' );
		$query->from( '`#__seminarman_application`' );
		$query->where( 'user_id = '. $user->id );
		$query->where( 'published = 1' );
		$query->where( '( status < 3 OR status = 4 OR status = 5 )' );
		
		$db->setQuery( $query );
		// $this->_bookings = $db->loadResultArray();
		$jversion = new JVersion();
		$short_version = $jversion->getShortVersion();
		if (version_compare($short_version, "3.0", 'ge')) {
		    $this->_bookings = $db->loadColumn();
		} else {
			$this->_bookings = $db->loadResultArray();
		}
	}
	
}

?>