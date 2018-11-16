<?php
/**
*
* Copyright (C) 2015 Open Source Group GmbH www.osg-gmbh.de
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class SeminarmanModelList extends JModelLegacy
{
	var $_courses = null;
	
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
		
		$limit = $mainframe->getUserStateFromRequest('com_seminarman.list.limit', 'limit', $params->def('limit', 0), 'int');
		// $limit = $mainframe->getUserStateFromRequest('com_seminarman.category.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = JRequest::getVar('limitstart', 0, '', 'int');
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
		
		$this->setState('filter_order', JRequest::getCmd('filter_order', $ordering));
		$this->setState('filter_order_dir', JRequest::getCmd('filter_order_Dir', 'ASC'));	
	}
	
	function getCourses()
	{
	
		if ($this->_loadCourses()) {
			return $this->_courses;
		}
	}

	function _loadCourses()
	{
		$mainframe = JFactory::getApplication();
	
		if (empty($this->_courses))
		{
	
			$query = $this->_buildQuery();
	
			$this->_courses = $this->_getList($query, $this->getState('limitstart'), $this->
					getState('limit'));
			return (boolean)$this->_courses;
		}
		return true;
	}

	function _buildQuery()
	{
		$where = $this->_buildCourseWhere();
		$orderby = $this->_buildCourseOrderBy();
		
		$query = $this->_db->getQuery(true);
		$query->select( 'DISTINCT i.*' );
		$query->select( 'CASE WHEN CHAR_LENGTH(i.alias) THEN CONCAT_WS(\':\', i.id, i.alias) ELSE i.id END as slug' );
		$query->select( 'rel.tid as tagid' );
		$query->from( '#__seminarman_courses AS i' );
		$query->join( "LEFT", '#__seminarman_tags_course_relations AS rel ON rel.courseid = i.id' );
		if ( $where != '' ) {
			$query->where( $where );
		}
		$query->order( $orderby );
		
		return $query;
	}

	function _buildCourseOrderBy()
	{
		$filter_order = $this->getState('filter_order');
		$filter_order_dir = $this->getState('filter_order_dir');
	
        return $filter_order .' '. $filter_order_dir .', i.title';
	}
	
	function _buildCourseWhere()
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
			
		if (isset($_POST['filter_sman_from'])) {
		    $where = "i.start_date >= " . $this->_db->Quote($_POST['filter_sman_from']);
		} else {
			$where = "i.start_date >= " . $this->_db->Quote('0000-00-00');
		}
		
		if (isset($_POST['filter_sman_to']) && !empty($_POST['filter_sman_to'])) {
			$where .= " AND i.finish_date <= " . $this->_db->Quote($_POST['filter_sman_to']);
		}
		
		if (isset($_POST['filter_sman_tag']) && !empty($_POST['filter_sman_tag'])) {
			$strtags = implode(',',$_POST['filter_sman_tag']);
			$where .= " AND rel.tid IN(" . $strtags . ")";
		}
	
		switch ($state)
		{
			case 1:
				switch($params->get('publish_down')) {
					case '1':
						$publish_down = 'CONCAT_WS(\' \', i.start_date, i.start_time)';
						break;
					case '2':
						$publish_down = 'CONCAT_WS(\' \', i.finish_date, i.finish_time)';
						// $now = $jnow->sub(new DateInterval('P1D'));
						break;
					default:
						$publish_down = 'i.publish_down';
				}
	
				$where .= ' AND i.state = 1' . ' AND ( i.publish_up = ' . $this->_db->Quote($nullDate) .
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
	
	
		if ($params->get('filter'))
		{
	
			$filter = JRequest::getString('filter', '', 'request');
			$filter_experience_level = JRequest::getString('filter_experience_level', '', 'request');
			$filter_positiontype = JRequest::getString('filter_positiontype', '', 'request');
	
	
			if ($filter)
			{
	
				$filter = $this->_db->escape(trim(JString::strtolower($filter)));
				$like = $this->_db->Quote('%'. $this->_db->escape($filter, true) .'%', false);
	
				$where .= ' AND ( LOWER( i.title ) LIKE ' . $like .' OR LOWER( i.code ) LIKE '. $like .')';
			}
		} else {
			$filter_experience_level = null;
		}
	
	
		if ($filter_experience_level>0)
		{
			$where .= ' AND LOWER( i.id_experience_level ) = ' . $filter_experience_level;
		}
	
		return $where . ' GROUP BY i.id';
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
}