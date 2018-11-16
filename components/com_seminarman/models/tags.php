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

class SeminarmanModelTags extends JModelLegacy
{
    var $_data = null;

    var $_tag = null;

    var $_total = null;

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
        
        $id = JRequest::getInt('id', 0);
        $this->setId((int)$id);
        
        $limit = $mainframe->getUserStateFromRequest('com_seminarman.tag.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $limitstart = JRequest::getVar('limitstart', 0, '', 'int');
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $tmpl_limit = $mainframe->getUserStateFromRequest('com_seminarman.tag.tmpl_limit', 'tmpl_limit', $mainframe->getCfg('list_limit'), 'int');
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

    function setId($id)
    {

        $this->_id = $id;
        $this->_data = null;
    }

    function getData()
    {

        if (empty($this->_data))
        {
            $query = $this->_buildQuery();
            $this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
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
        $where .= ' AND t.tid = ' . $this->_id;
        return $where;
    }
    
    function getArchive() {
    	$where = SMANFunctions::buildCourseWhere($this->_db, 2);
    	$where .= ' AND t.tid = ' . $this->_id;
    	$query = SMANFunctions::buildCourseQuery($this, $this->_db, $where);
    	$archive = $this->_getList($query, $this->getState('archive_limitstart'), $this->getState('archive_limit'));
    	return $archive;
    }
    
    function getArchiveTotal() {
    	$where = SMANFunctions::buildCourseWhere($this->_db, 2);
    	$where .= ' AND t.tid = ' . $this->_id;
    	$query = SMANFunctions::buildCourseQuery($this, $this->_db, $where);
    	$archiveCount = $this->_getListCount($query);
    	return $archiveCount;    	
    }

    function getTag()
    {
        $query = $this->_db->getQuery(true);
        $query->select( 't.name' );
        $query->select( 't.id' );
        $query->select( 'CASE WHEN CHAR_LENGTH(t.alias) THEN CONCAT_WS(\':\', t.id, t.alias) ELSE t.id END as slug' );
        $query->from( '#__seminarman_tags AS t' );
        $query->where( 't.id = ' . $this->_id );
        $query->where( 't.published = 1' );
        
        $this->_db->setQuery($query);
        $this->_tag = $this->_db->loadObject();

        return $this->_tag;
    }

	function gettitles()
	{
		$user = JFactory::getUser();
		$gid = (int)$user->get('aid');
		$ordering = 'ordering ASC';
		
        $query = $this->_db->getQuery(true);
        $query->select( 'id' );
        $query->select( 'title' );
        $query->from( '#__seminarman_experience_level' );
        $query->where( 'published = 1' );
        $query->order( $ordering );

		$this->_db->setQuery($query);
		$this->_subs = $this->_db->loadObjectList();

		return $this->_subs;
	}

	function getCategory($courseid)
	{

		$user = JFactory::getUser();
		$gid = (int)$user->get('aid');

		$query = $this->_db->getQuery(true);
		$query->select( 'c.*' );
		$query->select( 'CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug' );
		$query->from( '#__seminarman_categories AS c' );
		$query->join( "LEFT", '#__seminarman_cats_course_relations AS rel ON rel.catid = c.id' );
		$query->where( 'rel.courseid = ' . $courseid );
		$query->where( 'c.published = 1' );
            
		$this->_db->setQuery($query);
		$this->_category = $this->_db->loadObject();

		if (!$this->_category->published)
		{
			JError::raiseError(404, JText::sprintf('CATEGORY #%d NOT FOUND', $this->_id));
			return false;
		}

		return $this->_category;
	}

	function getCategoryOfTemplate($templateid)
	{
	
		$user = JFactory::getUser();
		$gid = (int)$user->get('aid');
		
		$query = $this->_db->getQuery(true);
		$query->select( 'c.*' );
		$query->select( 'CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug' );
		$query->from( '#__seminarman_categories AS c' );
		$query->join( "LEFT", '#__seminarman_cats_template_relations AS rel ON rel.catid = c.id' );
		$query->where( 'rel.templateid = ' . $templateid );
		$query->where( 'c.published = 1' );
	
		$this->_db->setQuery($query);
		$this->_category = $this->_db->loadObject();
	
		if (!$this->_category->published)
		{
			JError::raiseError(404, JText::sprintf('CATEGORY #%d NOT FOUND', $this->_id));
			return false;
		}
	
		return $this->_category;
	}
	
	function _buildQueryLstOfProspects()
	{
		$query = $this->_db->getQuery(true);
		$query->select( 'DISTINCT i.*' );
		$query->select( 'gr.title AS cgroup' );
		$query->select( 'lev.title AS level' );
		$query->select( 'i.id AS slug' );
		$query->from( '#__seminarman_templates AS i' );
		$query->join( "LEFT", '#__seminarman_tags_template_relations AS rel ON rel.templateid = i.id' );
		$query->join( "LEFT", '#__seminarman_atgroup AS gr ON gr.id = i.id_group' );
		$query->join( "LEFT", '#__seminarman_experience_level AS lev ON lev.id = i.id_experience_level' );
		$query->where( 'i.state=1' );
		$query->where( 'rel.tid='. $this->_id );
		
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
		
		$query->order( $filter_order2 .' '. $filter_order_dir2 .', i.title' );
			
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
		
		$query = $db->getQuery(true);
		$query->select( 'course_id' );
		$query->from( '`#__seminarman_application`' );
		$query->where( 'user_id = '. $user->id );
		$query->where( 'published = 1' );
		
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