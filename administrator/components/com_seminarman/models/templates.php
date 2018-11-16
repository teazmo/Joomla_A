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

class SeminarmanModelTemplates extends JModelLegacy
{
    var $_pagination = null;

    function __construct()
    {
        parent::__construct();

        $mainframe = JFactory::getApplication();

        $limit = $mainframe->getUserStateFromRequest('com_seminarman' . '.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $limitstart = $mainframe->getUserStateFromRequest('com_seminarman' . '.sman_tmpls_limitstart', 'sman_tmpls_limitstart', 0, 'int');

        $this->setState('limit', $limit);
        $this->setState('sman_tmpls_limitstart', $limitstart);

        $array = JRequest::getVar('cid', 0, '', 'array');
        $this->setId((int)$array[0]);
    }
    
    function _buildQuery()
    {
    	$db = JFactory::getDBO();
    	$where = $this->_buildContentWhere();
    	$orderby = $this->_buildContentOrderBy();
    	
    	$query = $db->getQuery(true);
    	$query->select( 'DISTINCT rel.templateid' );
    	$query->select( 'i.*' );
    	$query->select( 'u.name AS editor' );
    	$query->select( '(SELECT COUNT(id) FROM #__seminarman_salesprospect WHERE template_id = i.id) AS no_salesprospects' );
    	$query->from( '#__seminarman_templates AS i' );
    	$query->join( "LEFT", '#__seminarman_cats_template_relations AS rel ON rel.templateid = i.id' );
    	$query->join( "LEFT", '#__users AS u ON u.id = i.checked_out' );
    	if ( $where != '' ) {
    		$query->where( $where );
    	}
    	if ( $orderby != "" ) {
	    	$query->order( $orderby );
    	}
    	return $query;
    }
    
    function _buildContentOrderBy()
    {
    	$mainframe = JFactory::getApplication();
    
    	$filter_order = $mainframe->getUserStateFromRequest('com_seminarman' . '.templates.filter_order', 'filter_order', 'i.id', 'cmd');
    	$filter_order_Dir = $mainframe->getUserStateFromRequest('com_seminarman' . '.templates.filter_order_Dir', 'filter_order_Dir', '', 'word');
    
    	if (!empty($filter_order))
    	return $filter_order . ' ' . $filter_order_Dir;
    	return "";
    }
    
    function _buildContentWhere()
    {
    	$mainframe = JFactory::getApplication();
    
    	$filter_state = $mainframe->getUserStateFromRequest('com_seminarman.templates.filter_state', 'filter_state', '', 'word');
    	$filter_category = $mainframe->getUserStateFromRequest('com_seminarman.templates.filter_category', 'filter_category', '');
    	$search = $mainframe->getUserStateFromRequest('com_seminarman.templates.search', 'search', '', 'string');
    	$search = $this->_db->escape(trim(JString::strtolower($search)));
    
    	$where = array();
    
    	if ($filter_category)
    		$where[] = 'rel.catid = '.$filter_category;
    	
    	if ($search)
    	{
    		$searchterm = $this->_db->Quote('%' . $this->_db->escape($search, true) . '%', false);
    		$where[] = ' (LOWER(i.name) LIKE ' . $searchterm . ' OR LOWER(i.title) LIKE ' . $searchterm . ')';
    	}
    
    	if ($filter_state)
    	{
    		if ($filter_state == 'P')
    		{
    			$where[] = 'i.state = 1';
    		} else
    		if ($filter_state == 'U')
    		{
    			$where[] = 'i.state = 0';
    		}
    	}
    	
    	$where = (count($where) ? implode(' AND ', $where) : '');
    
    	return $where;
    }
    
    function setId($id)
    {
    	$this->_id = $id;
    	$this->_data = null;
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
  
    function getPagination()
    {

        if (empty($this->_pagination))
        {
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination($this->getTotal(), $this->getState('sman_tmpls_limitstart'), $this->getState('limit'), 'sman_tmpls_');
        }

        return $this->_pagination;
    }
    
    function getData()
    {
    
    	if (empty($this->_data))
    	{
    		$query = $this->_buildQuery();
    		$this->_data = $this->_getList($query, $this->getState('sman_tmpls_limitstart'), $this->getState('limit'));
    
    		$k = 0;
    		$count = count($this->_data);
    		for ($i = 0; $i < $count; $i++)
    		{
    			$course = &$this->_data[$i];
    			$course->categories = $this->getCategories($course->id);
    			$k = 1 - $k;
    		}
    
    	}
    
    	return $this->_data;
    }
    
    function getCategories($id)
    {
    	$db = JFactory::getDBO();
    	
    	$query = $db->getQuery(true);
    	$query->select( 'DISTINCT c.id' );
    	$query->select( 'c.title' );
    	$query->from( '#__seminarman_categories AS c' );
    	$query->join( "LEFT", '#__seminarman_cats_template_relations AS rel ON rel.catid = c.id' );
    	$query->where( 'rel.templateid = ' . (int)$id );
    	
    	$this->_db->setQuery($query);
    
    	$this->_cats = $this->_db->loadObjectList();
    	return $this->_cats;
    }
   
    function checkin()
    {
    	if ($this->_id)
    	{
    		$group = $this->getTable();
    		if (!$group->checkin($this->_id))
    		{
    			$this->setError($this->_db->getErrorMsg());
    			return false;
    		}
    	}
    	return false;
    }
    
    function checkout($uid = null)
    {
    	if ($this->_id)
    	{
    
    		if (is_null($uid))
    		{
    			$user = JFactory::getUser();
    			$uid = $user->get('id');
    		}
    
    		$group = $this->getTable();
    		if (!$group->checkout($uid, $this->_id))
    		{
    			$this->setError($this->_db->getErrorMsg());
    			return false;
    		}
    
    		return true;
    	}
    	return false;
    }
    
    function move($direction)
    {
    	$row = JTable::getInstance('seminarman_templates', '');
    
    	if (!$row->load($this->_id))
    	{
    		$this->setError($this->_db->getErrorMsg());
    		return false;
    	}
    
    	if (!$row->move($direction))
    	{
    		$this->setError($this->_db->getErrorMsg());
    		return false;
    	}
    
    	return true;
    }
    
    function saveorder($cid = array(), $order)
    {
    	$row = JTable::getInstance('seminarman_templates', '');

    	for ($i = 0; $i < count($cid); $i++)
    	{
	    	$row->load((int)$cid[$i]);
	    	
	    	if ($row->ordering != $order[$i])
	    	{
		    	$row->ordering = $order[$i];
		    	if (!$row->store())
		    	{
		    			$this->setError($this->_db->getErrorMsg());
		    			return false;
		    	}
	    	}
    	}
    	
    	return true;
    }
    
    function delete($cid)
    {
    	
    	if (count($cid))
    	{
    		$cids = implode(',', $cid);
    		$query = $this->_db->getQuery(true);
    	
    		$query->delete( $this->_db->quoteName( '#__seminarman_templates' ) )
    		->where( $this->_db->quoteName( 'id' ) . ' IN (' . $cids . ')' );
    		$this->_db->setQuery( $query );
    	
    		if (!$this->_db->execute())
    		{
    			$this->setError($this->_db->getErrorMsg());
    			return false;
    		}
    	
    		$query = $this->_db->getQuery(true);
    	
    		$query->delete( $this->_db->quoteName( '#__seminarman_tags_template_relations' ) )
    		->where( $this->_db->quoteName( 'templateid' ) . ' IN (' . $cids . ')' );
    		$this->_db->setQuery( $query );
    	
    		if (!$this->_db->execute())
    		{
    			$this->setError($this->_db->getErrorMsg());
    			return false;
    		}
    	
    		$query = $this->_db->getQuery(true);
    	
    		$query->delete( $this->_db->quoteName( '#__seminarman_cats_template_relations' ) )
    		->where( $this->_db->quoteName( 'templateid' ) . ' IN (' . $cids . ')' );
    		$this->_db->setQuery( $query );
    	
    		if (!$this->_db->execute())
    		{
    			$this->setError($this->_db->getErrorMsg());
    			return false;
    		}
    	
    		$query = $this->_db->getQuery(true);
    	
    		$query->delete( $this->_db->quoteName( '#__seminarman_files_template_relations' ) )
    		->where( $this->_db->quoteName( 'templateid' ) . ' IN (' . $cids . ')' );
    		$this->_db->setQuery( $query );
    		$this->_db->execute();
    	
    		$query = $this->_db->getQuery(true);
    	
    		$query->delete( $this->_db->quoteName( '#__seminarman_tutor_templates_relations' ) )
    		->where( $this->_db->quoteName( 'templateid' ) . ' IN (' . $cids . ')' );
    		$this->_db->setQuery( $query );
    		$this->_db->execute();
    		
    		return true;
    	}
    	
    	return false;
    }
}

?>