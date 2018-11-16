<?php
/**
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
 **/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class seminarmanModelusers extends JModelLegacy {
	var $_data = null;	
	var $_total = null;
	var $_pagination = null;
	
	var $_allbookingrules = null;
	
	var $_jsonfuncs_created = false;
	
	function __construct()
	{
		parent::__construct();
	
		$mainframe = JFactory::getApplication();
		$this->childviewname = 'user';
	
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest('com_seminarman' . '.sman_users_limitstart', 'sman_users_limitstart', 0, 'int');
	
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
	
		$this->setState('limit', $limit);
		$this->setState('sman_users_limitstart', $limitstart);
		
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('osg');
		$jsonfuncs_created = $dispatcher->trigger('createSQLJSONFuncs', array());
		
		if (isset($jsonfuncs_created[0])&&($jsonfuncs_created[0] === true)) {
			$this->_jsonfuncs_created = true;
		} else {
			$this->_jsonfuncs_created = false;
		}
	}
	
	function getJsonfuncscreated(){
		return $this->_jsonfuncs_created;
	}
	
	function getData()
	{		
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('sman_users_limitstart'), $this->getState('limit'));
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
	
	function getPagination()
	{
	
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('sman_users_limitstart'), $this->getState('limit'), 'sman_users_');
		}
	
		return $this->_pagination;
	}
	
	function _buildQuery()
	{
		$db	= JFactory::getDBO();
	
		$where = $this->_buildContentWhere();
		$orderby = $this->_buildContentOrderBy();

		$query = $db->getQuery(true);
		$query->select( 'u.*' );
		$query->from('#__users AS u');
		if ( $where != '' ){
			$query->where( $where );
		}
		$query->order( $orderby );
		
		return $query;
	}
	
	function _buildContentOrderBy()
	{
		$mainframe = JFactory::getApplication();
		
		$filter_order = $mainframe->getUserStateFromRequest('com_seminarman' .
				'.users.filter_order', 'filter_order', 'u.id', 'cmd');
		$filter_order_Dir = $mainframe->getUserStateFromRequest('com_seminarman' .
				'.users.filter_order_Dir', 'filter_order_Dir', '', 'word');
	
		if ($filter_order == 'rule_begin' || $filter_order == 'rule_finish') $filter_order = 'u.id';    // fixes if common_schema disabled
		$orderby = $filter_order . ' ' . $filter_order_Dir . ', u.id';
		
		return $orderby;
	}
	
	function _buildContentWhere()
	{
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		
		$where = '';
				
		return $where;
	}
	
	function getAllbookingrules() {
		if (empty($this->_allbookingrules))
		{
			$query = $this->_buildQueryBookingRules();
			$this->_allbookingrules = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}		
		
		return $this->_allbookingrules;		
	}
	
	function _buildQueryBookingRules()
	{
		$db	= JFactory::getDBO();
		$query = $db->getQuery(true);
	
		$params = JComponentHelper::getParams( 'com_seminarman' );
		
		$where = $this->_buildContentBookingRulesWhere();
		$orderby = $this->_buildContentBookingRulesOrderBy();

		$query->select( 'u.*' );
		$query->select( 'r.title AS rule_title' );
		$query->select( 'r.rule_text AS rule_text' );
		
		if ($params->get('common_schema_support')) {
			$query->select( 'common_schema.extract_json_value(r.rule_text, "/start_date") AS rule_begin' );
			$query->select( 'common_schema.extract_json_value(r.rule_text, "/finish_date") AS rule_finish' );
		} 
		elseif($this->_jsonfuncs_created) {
			$query->select( 'extract_json_value(r.rule_text, "/start_date") AS rule_begin' );
			$query->select( 'extract_json_value(r.rule_text, "/finish_date") AS rule_finish' );
		}

		$query->select( 'r.published AS rule_published' );
		$query->from('#__users AS u');
		$query->join( "LEFT", '#__seminarman_user_rules AS r ON (u.id = r.user_id)' );
		if ( $where != '' ){
			$query->where( $where );
		}
		$query->order( $orderby );
		
		return $query;
	}
	
	function _buildContentBookingRulesOrderBy()
	{
		$mainframe = JFactory::getApplication();
	
		$filter_order = $mainframe->getUserStateFromRequest('com_seminarman' .
				'.users.filter_order', 'filter_order', 'u.id', 'cmd');
		$filter_order_Dir = $mainframe->getUserStateFromRequest('com_seminarman' .
				'.users.filter_order_Dir', 'filter_order_Dir', '', 'word');
	
		$orderby = $filter_order . ' ' . $filter_order_Dir . ', u.id';
	
		return $orderby;
	}
	
	function _buildContentBookingRulesWhere()
	{
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$params = JComponentHelper::getParams( 'com_seminarman' );
		
		$filter_state = $mainframe->getUserStateFromRequest('com_seminarman' .
				'.users.filter_state', 'filter_state', '', 'word');
		$filter_category = $mainframe->getUserStateFromRequest('com_seminarman' .
				'.users.filter_category', 'filter_category', '');
		
		// $where = array();
		
		$where[] = ' r.published != -1';
		
		if ($filter_state)
		{
			if ($filter_state == 'P')
			{
				$where[] = 'r.published = 1';
			} else
				if ($filter_state == 'U')
				{
					$where[] = 'r.published = 0';
				} else
					if ($filter_state == 'A')
					{
						$where[] = 'r.published = -1';
					} else
						if ($filter_state == 'W')
						{
							$where[] = 'r.published = -2';
						} else
							if ($filter_state == 'O')
							{
								$where[] = 'r.published = -3';
							} else
								if ($filter_state == 'T')
								{
									$where[] = 'r.published = -4';
								}
		}
		
		$where = (count($where) ? implode(' AND ', $where) : '');  // don't use where clause
		
		if ($params->get('common_schema_support') && $filter_category) {
			$where .= ' AND ' . 'common_schema.extract_json_value(r.rule_text, "/category") = '.$filter_category;
		} elseif ($this->_jsonfuncs_created && $filter_category) {
			$where .= ' AND ' . 'extract_json_value(r.rule_text, "/category") = '.$filter_category;
		}
		return $where;
	}
}