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

class SeminarmanModelBookings extends JModelLegacy
{
    var $_data = null;

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
        // $limit = $mainframe->getUserStateFromRequest('com_seminarman.bookings.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $limit = $mainframe->getUserStateFromRequest('com_seminarman.bookings.limit', 'limit', $params->def('limit', 0), 'int');
        $limitstart = JRequest::getVar('limitstart', 0, '', 'int');
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);

        $this->setState('filter_order', JRequest::getCmd('filter_order', $ordering));
        $this->setState('filter_order_dir', JRequest::getCmd('filter_order_Dir', 'ASC'));
        $this->setState('filter_experience_level', JRequest::getCmd('filter_experience_level'));
        $this->setState('filter_positiontype', JRequest::getCmd('filter_positiontype'));
        
    }

    function getData()
    {

        if (empty($this->_data))
        {
            $query = $this->_buildQuery();
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
        $orderby = $this->_buildCourseOrderBy();
    	
    	$query = $this->_db->getQuery(true);
    	$query->select( 'DISTINCT i.*' );
    	$query->select( '(i.plus / (i.plus + i.minus) ) * 100 AS votes' );
    	$query->select( 'CONCAT_WS(\' \', emp.salutation, emp.other_title, emp.firstname, emp.lastname) AS tutor' );
    	$query->select( 'CASE WHEN CHAR_LENGTH(i.alias) THEN CONCAT_WS(\':\', i.id, i.alias) ELSE i.id END as slug' );
    	$query->select( 'gr.title AS cgroup' );
    	$query->select( 'lev.title AS level' );
    	$query->select( 'app.id AS applicationid' );
    	$query->select( 'app.user_id' );
    	$query->select( 'app.status AS booking_state' );
    	$query->select( 'app.invoice_filename_prefix AS invoice_filename_prefix' );
    	$query->select( 'app.invoice_number AS invoice_number' );
    	$query->select( 'app.salutation AS booking_salutation' );
    	$query->select( 'app.title AS booking_title' );
    	$query->select( 'app.first_name AS booking_first_name' );
    	$query->select( 'app.last_name AS booking_last_name' );
    	$query->select( 'app.price_per_attendee AS booking_price' );
    	$query->select( 'app.price_total AS booking_price_total' );
    	$query->select( 'app.price_vat AS booking_vat' );
    	$query->select( 'app.attendees AS booking_amount' );
    	$query->select( 'app.certificate_file AS certificate_file' );
    	$query->from( '`#__seminarman_courses` AS i' );
    	$query->join( "LEFT", '`#__seminarman_application` AS app ON app.course_id = i.id');
    	$query->join( "LEFT", '`#__seminarman_cats_course_relations` AS rel ON rel.courseid = i.id');
    	$query->join( "LEFT", '`#__seminarman_tutor` AS emp ON emp.id = i.tutor_id');
    	$query->join( "LEFT", '`#__seminarman_atgroup` AS gr ON gr.id = i.id_group');
    	$query->join( "LEFT", '`#__seminarman_experience_level` AS lev ON lev.id = i.id_experience_level');
    	$query->join( "LEFT", '`#__seminarman_categories` AS c ON c.id = rel.catid');
    	$query->where( $where );
    	$query->group( 'app.id' );
    	$query->order( $orderby );
    	
        return $query;
    }

    function _buildCourseOrderBy()
    {
        $filter_order = $this->getState('filter_order');
        $filter_order_dir = $this->getState('filter_order_dir');

        $orderby = $filter_order . ' ' . $filter_order_dir . ', i.title';

        return $orderby;
    }

    function _buildCourseWhere()
    {
        $mainframe = JFactory::getApplication();

        $user = JFactory::getUser();
        //$gid = (int)$user->get('aid');
        $params = $mainframe->getParams('com_seminarman');
        $jnow = JFactory::getDate();
        // $now = $jnow->toMySQL();
        $now = $jnow->toSQL();
        $nullDate = $this->_db->getNullDate();

        $state = 1;

        $where = ' app.user_id = ' . (int)$user->get('id');
        //$where .= ' AND c.access <= ' . $gid;

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

	          	// compute if archive is shown //	          	
	          	$show_archive = false;	          	
	          	if (( $params->get('enable_archive', -1) == 1 ) || (($params->get('enable_archive', -1) == -1) && ($params->get('show_archive_in_table', 0) == 1))) {	          	    
	          	    $show_archive = true;	          	    
	          	}	          	
	          	
	          	if( $show_archive ) {	          	    
                    // $where .= ' AND i.state >= 1' . ' AND ( i.publish_up = ' . $this->_db->Quote($nullDate) .
                    // ' OR i.publish_up <= ' . $this->_db->Quote($now) . ' )' . ' AND ( ' . $publish_down . ' = ' .
                    // $this->_db->Quote($nullDate) . ' OR ' . $publish_down . ' >= ' . $this->_db->Quote($now) .
                    // ' OR i.state = 2)'; // new: if course is archived, its publish_down setting will not be considered
                    
                    // from 2.12.x: publish_down not considered any more 
                    $where .= ' AND i.state >= 1' . ' AND ( i.publish_up = ' . $this->_db->Quote($nullDate) .
                    ' OR i.publish_up <= ' . $this->_db->Quote($now) . ' )';
	          	} else {
	          		// $where .= ' AND i.state = 1' . ' AND ( i.publish_up = ' . $this->_db->Quote($nullDate) .
	          		// ' OR i.publish_up <= ' . $this->_db->Quote($now) . ' )' . ' AND ( ' . $publish_down . ' = ' .
	          		// $this->_db->Quote($nullDate) . ' OR ' . $publish_down . ' >= ' . $this->_db->Quote($now) .
	          		// ' )';
	          		
	          		// from 2.12.x: publish_down not considered any more
	          		$where .= ' AND i.state = 1' . ' AND ( i.publish_up = ' . $this->_db->Quote($nullDate) .
	          		' OR i.publish_up <= ' . $this->_db->Quote($now) . ' )';
	          	}

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

    	//$where .= ' AND ses.published = 1';
    	$where .= ' AND app.published = 1';

    	if ($params->get('filter'))
    	{

    		$filter = JRequest::getString('filter', '', 'request');
    		$filter_experience_level = JRequest::getString('filter_experience_level', '', 'request');
    		$filter_positiontype = JRequest::getString('filter_positiontype', '', 'request');


    		if ($filter)
    		{

    			$filter = $this->_db->escape(trim(JString::strtolower($filter)));

    			$where .= ' AND LOWER( i.title ) LIKE ' . $this->_db->Quote('%' . $this->_db->
    			    escape($filter, true) . '%', false);
    		}
    	} else {
			$filter_experience_level = null;
		}

    	if ($filter_experience_level)
    	{
    		$where .= ' AND LOWER( i.id_experience_level ) = ' . $filter_experience_level;
    	}
    	
    	if (JRequest::getInt('appid') > 0) $where .= ' AND app.id = ' . JRequest::getInt('appid');
    	
        return $where;
    }

	function gettitles()
	{
		$user = JFactory::getUser();
		$gid = (int)$user->get('aid');
		$ordering = 'ordering ASC';

		$query = $this->_db->getQuery(true);
		$query->select( 'id' );
		$query->select( 'title' );
		$query->from( '`#__seminarman_experience_level`' );
		$query->where( 'published=1' );
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
		$query->from( '`#__seminarman_categories` AS c' );
		$query->join( "LEFT", '#__seminarman_cats_course_relations AS rel ON rel.catid = c.id' );
		$query->where( 'rel.courseid = ' . $courseid );
		$query->where( 'c.published=1' );
		
		$this->_db->setQuery($query);
		$this->_category = $this->_db->loadObject();

		if (!$this->_category->published)
		{
			JError::raiseError(404, JText::sprintf('CATEGORY #%d NOT FOUND', $this->_id));
			return false;
		}


//		if ($this->_category->access > $gid)
//		{
//			JError::raiseError(403, JText::_("ALERTNOTAUTH"));
//			return false;
//		}
//
		return $this->_category;
	}
	
	function getUserBookingrules() {
		$user = JFactory::getUser();
			
		$query = $this->_db->getQuery(true);
		$query->select( '*' );
		$query->from( '`#__seminarman_user_rules`' );
		$query->where( 'user_id=' . (int)$user->get('id') );
		$query->where( 'published=1' );
		$query->where( 'archived=0' );
		$query->where( 'rule_type=1' );
		$query->where( "rule_option='category'" );
		
		$this->_db->setQuery($query);
		$userbookingrules = $this->_db->loadObjectList();
        return $userbookingrules;
	}
}

?>