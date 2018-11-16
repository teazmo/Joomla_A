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

    var $_total = null;

    var $_pagination = null;

    var $_id = null;

    function __construct()
    {
        parent::__construct();

        $mainframe = JFactory::getApplication();

        $limit = $mainframe->getUserStateFromRequest('com_seminarman' . '.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $limitstart = $mainframe->getUserStateFromRequest('com_seminarman' . '.sman_tags_limitstart', 'sman_tags_limitstart', 0, 'int');

        $this->setState('limit', $limit);
        $this->setState('sman_tags_limitstart', $limitstart);

        $array = JRequest::getVar('cid', 0, '', 'array');
        $this->setId((int)$array[0]);

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
            $this->_data = $this->_getList($query, $this->getState('sman_tags_limitstart'), $this->getState('limit'));
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
            $this->_pagination = new JPagination($this->getTotal(), $this->getState('sman_tags_limitstart'), $this->getState('limit'), 'sman_tags_');
        }

        return $this->_pagination;
    }

    function _buildQuery()
    {
    	$db = JFactory::getDBO();
        $where = $this->_buildContentWhere();
        $orderby = $this->_buildContentOrderBy();
        $having = $this->_buildContentHaving();
        
        $query = $db->getQuery(true);
        $query->select( 't.*' );
        $query->select( 'u.name AS editor' );
        $query->select( 'COUNT(rel.tid) AS nrassigned' );
        $query->from( '#__seminarman_tags AS t' );
        $query->join( "LEFT", '#__seminarman_tags_course_relations AS rel ON rel.tid = t.id' );
        $query->join( "LEFT", '#__users AS u ON u.id = t.checked_out' );
        if ( $where != '' ) {
        	$query->where( $where );
        }
        $query->group( 't.id' );
        if ( $having != '' ) {
	        $query->having( $having );
        }
        $query->order( $orderby );            
            
        return $query;
    }

    function _buildContentOrderBy()
    {
        $mainframe = JFactory::getApplication();

        $filter_order = $mainframe->getUserStateFromRequest('com_seminarman' .
            '.tags.filter_order', 'filter_order', 't.name', 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest('com_seminarman' .
            '.tags.filter_order_Dir', 'filter_order_Dir', '', 'word');

        $orderby = $filter_order . ' ' . $filter_order_Dir;

        return $orderby;
    }

    function _buildContentWhere()
    {
        $mainframe = JFactory::getApplication();

        $filter_state = $mainframe->getUserStateFromRequest('com_seminarman' .
            '.tags.filter_state', 'filter_state', '0', 'word');
        $search = $mainframe->getUserStateFromRequest('com_seminarman' . '.tags.search', 'search',
            '', 'string');
        $search = $this->_db->escape(trim(JString::strtolower($search)));

        $where = array();

        if ($filter_state != '0')
        {
            if ($filter_state == 'P')
            {
                $where[] = 't.published = 1';
            } else
                if ($filter_state == 'U')
                {
                    $where[] = 't.published = 0';
                }
        }

        if ($search)
        {
            $where[] = ' LOWER(t.name) LIKE ' . $this->_db->Quote('%' . $this->_db->
                escape($search, true) . '%', false);
        }

        $where = (count($where) ? implode(' AND ', $where) : '');

        return $where;
    }

    function _buildContentHaving()
    {
        $mainframe = JFactory::getApplication();

        $filter_assigned = $mainframe->getUserStateFromRequest('com_seminarman' .
            '.tags.filter_assigned', 'filter_assigned', '', 'word');

        $having = '';

        if ($filter_assigned)
        {
            if ($filter_assigned == 'O')
            {
                $having = ' COUNT(rel.tid) = 0';
            } else
                if ($filter_assigned == 'A')
                {
                    $having = ' COUNT(rel.tid) > 0';
                }
        }

        return $having;
    }

    function publish($cid = array(), $publish = 1)
    {
        $user = JFactory::getUser();

        if (count($cid))
        {
            $cids = implode(',', $cid);

            $query = $this->_db->getQuery(true);
             
            $fields = array( $this->_db->quoteName( 'published' ). ' = ' . (int)$publish );
            $conditions = array( $this->_db->quoteName('id') . ' IN (' . $cids . ')',
            		'( checked_out = 0 OR ( checked_out = ' . (int) $user->get('id') . ' ) )'
            );
             
            $query->update( $this->_db->quoteName( '#__seminarman_tags' ) )->set( $fields )->where( $conditions );
             
            $this->_db->setQuery($query);            
            
            if ( !$this->_db->execute() )
            {
                $this->setError($this->_db->getErrorMsg());
                return false;
            }
        }
        return true;
    }

    function delete($cid = array())
    {
        if (count($cid))
        {
            $cids = implode(',', $cid);
            $query = $this->_db->getQuery(true);
            
            $query->delete( $this->_db->quoteName( '#__seminarman_tags' ) )
            ->where( $this->_db->quoteName( 'id' ) . ' IN (' . $cids . ')' );
            $this->_db->setQuery( $query );

            if (!$this->_db->execute())
            {
                $this->setError($this->_db->getErrorMsg());
                return false;
            }

            $query = $this->_db->getQuery(true);
            
            $query->delete( $this->_db->quoteName( '#__seminarman_tags_course_relations' ) )
            ->where( $this->_db->quoteName( 'tid' ) . ' IN (' . $cids . ')' );
            $this->_db->setQuery( $query );

            if (!$this->_db->execute())
            {
                $this->setError($this->_db->getErrorMsg());
                return false;
            }
        }

        return true;
    }
}

?>