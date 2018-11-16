<?php

/**
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2014 Open Source Group GmbH www.osg-gmbh.de
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

class SeminarmanModelDay extends JModelLegacy
{
    var $_id = null;
    
    var $_db = null;
    
    var $_date = null;

    var $_data = null;

    var $_childs = null;

    var $_total = null;
    
    var $_ccid = null;
    
    var $_bookings = null; // holds the course id of all applications of the current user

    function __construct()
    {
        parent::__construct();

        $mainframe = JFactory::getApplication();
		$_db = JFactory::getDBO();

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

        $id = JRequest::getInt('did', date('Ymd'));
        $this->setId((int) $id);

        $l = JRequest::getString('ccid');
        if(!empty($l))
        	$this->setLimit($l);
        	
        $limit = $mainframe->getUserStateFromRequest('com_seminarman.day.limit', 'limit', $params->def('limit', 0), 'int');
        $limitstart = JRequest::getVar('limitstart', 0, '', 'int');
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
        
        $tmpl_limit = $mainframe->getUserStateFromRequest('com_seminarman.day.tmpl_limit', 'tmpl_limit', $mainframe->getCfg('list_limit'), 'int');
        $tmpl_limitstart = JRequest::getVar('tmpl_limitstart', 0, '', 'int');
        $tmpl_limitstart = ($tmpl_limit != 0 ? (floor($tmpl_limitstart / $tmpl_limit) * $tmpl_limit) : 0);
        
        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
        $this->setState('tmpl_limit', $tmpl_limit);
        $this->setState('tmpl_limitstart', $tmpl_limitstart);
        
        $this->setState('filter_order', JRequest::getCmd('filter_order', $ordering));
        $this->setState('filter_order_dir', JRequest::getCmd('filter_order_Dir', 'ASC'));
        $this->setState('filter_order2', JRequest::getCmd('filter_order2', $ordering));
        $this->setState('filter_order_dir2', JRequest::getCmd('filter_order_Dir2', 'ASC'));
    }

    function setId($id)
    {
        $this->_id = $id;
        $this->_date = date('Y-m-d', strtotime(substr($id, 0, 4) . '/' . substr($id, 4, 2) . '/' . substr($id, 6, 2)));
    }

    function setLimit($limit)
    {
        $this->_ccid = explode(':', $limit);
    }

    function getId()
    {
        return $this->_id;
    }

    function getDay()
    {
    	$date = new JDate($this->_date);
        return $date->format(JText::_('DATE_FORMAT_LC'));
    }

    function getData()
    {
    	
        if (empty($this->_data))
        {
            $query = $this->_buildQuery();
            $this->_db->setQuery($query, $this->getState('limitstart'), $this->getState('limit'));
            $this->_data = $this->_db->loadObjectList();
        }

        return $this->_data;
    }

    function getTotal()
    {

        if (empty($this->_total))
        {
            $query = $this->_buildQuery();
            $this->_db->setQuery($query);
            $this->_total = count($this->_db->loadObjectList());
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
        if (isset($this->_ccid) && count($this->_ccid) > 0)
        {
        	$where .= ' AND i.id IN (' . implode(',', $this->_ccid) . ')';
        }
        return $where;
    }

    function getTitles()
    {
        $user = JFactory::getUser();
        $gid = (int)$user->get('aid');

        $query = $this->_db->getQuery(true);
        $query->select( 'id' );
        $query->select( 'title' );
        $query->from( '`#__seminarman_experience_level`' );
        $query->where( 'published = 1' );
        $query->order( 'ordering ASC' );
        
        $this->_db->setQuery( $query );
        $this->_subs = $this->_db->loadObjectList();

        return $this->_subs;
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
		
		$db->setQuery($q);
		// $this->_bookings = $db->loadResultArray();
		$this->_bookings = $db->loadColumn();
	}
	
}

?>
