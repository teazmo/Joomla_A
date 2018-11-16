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

class SeminarmanModelTemplateelement extends JModelLegacy
{
    var $_data = null;

    var $_pagination = null;

    function __construct()
    {
        parent::__construct();

        $mainframe = JFactory::getApplication();

        $limit = $mainframe->getUserStateFromRequest('com_seminarman' . 'limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $limitstart = $mainframe->getUserStateFromRequest('com_seminarman' . 'sman_tmplels_limitstart', 'sman_tmplels_limitstart', 0, 'int');

        $this->setState('limit', $limit);
        $this->setState('sman_tmplels_limitstart', $limitstart);
    }

    function getData()
    {

        if (empty($this->_data))
        {
            $query = $this->_buildQuery();
            $this->_data = $this->_getList($query, $this->getState('sman_tmplels_limitstart'), $this->getState('limit'));
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
            $this->_pagination = new JPagination($this->getTotal(), $this->getState('sman_tmplels_limitstart'), $this->getState('limit'), 'sman_tmplels_');
        }

        return $this->_pagination;
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
        $query->from( '#__seminarman_templates AS i' );
        $query->join( "LEFT", '#__seminarman_cats_template_relations AS rel ON rel.templateid = i.id' );
        $query->join( "LEFT", '#__users AS u ON u.id = i.checked_out' );
        if ( $where != '' ) {
        	$query->where( $where );
        }
        $query->order( $orderby );
        return $query;
    }

    function _buildContentOrderBy()
    {
        $mainframe = JFactory::getApplication();

        $filter_order = $mainframe->getUserStateFromRequest('com_seminarman' .
            '.templates.filter_order', 'filter_order', 'i.ordering', 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest('com_seminarman' .
            '.templates.filter_order_Dir', 'filter_order_Dir', '', 'word');

        $orderby = $filter_order . ' ' . $filter_order_Dir .
            ', i.ordering';

        return $orderby;
    }

    function _buildContentWhere()
    {
        $mainframe = JFactory::getApplication();

        $filter_state = $mainframe->getUserStateFromRequest('com_seminarman' .
            '.templates.filter_state', 'filter_state', '', 'word');
        $search = $mainframe->getUserStateFromRequest('com_seminarman' . '.templates.search',
            'search', '', 'string');
        $search = $this->_db->escape(trim(JString::strtolower($search)));

        $where = array();

        if ($filter_state)
        {
            if ($filter_state == 'P')
            {
                $where[] = 'i.state = 1';
            } else
                if ($filter_state == 'U')
                {
                    $where[] = 'i.state = 0';
                } else
                    if ($filter_state == 'A')
                    {
                        $where[] = 'i.state = -1';
                    } else
                        if ($filter_state == 'W')
                        {
                            $where[] = 'i.state = -2';
                        } else
                            if ($filter_state == 'O')
                            {
                                $where[] = 'i.state = -3';
                            } else
                                if ($filter_state == 'T')
                                {
                                    $where[] = 'i.state = -4';
                                }
        }

        if ($search)
        {
            $where[] = ' LOWER(i.title) LIKE ' . $this->_db->Quote('%' . $this->_db->
                escape($search, true) . '%', false);
        }

        $where = (count($where) ? implode(' AND ', $where) : '');

        return $where;
    }
}

?>