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
jimport('joomla.filesystem.file');

class SeminarmanModelFileselement extends JModelLegacy
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
        $limitstart = $mainframe->getUserStateFromRequest('com_seminarman' . '.sman_fileels_limitstart', 'sman_fileels_limitstart', 0, 'int');

        $this->setState('limit', $limit);
        $this->setState('sman_fileels_limitstart', $limitstart);

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
            $this->_data = $this->_getList($query, $this->getState('sman_fileels_limitstart'), $this->getState('limit'));

            $this->_data = seminarman_images::BuildIcons($this->_data);

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
            $this->_pagination = new JPagination($this->getTotal(), $this->getState('sman_fileels_limitstart'), $this->getState('limit'), 'sman_fileels_');
        }

        return $this->_pagination;
    }

    function _buildQuery()
    {
        $db = JFactory::getDBO();
        $where = $this->_buildContentWhere();
        $orderby = $this->_buildContentOrderBy();
        
        $query = $db->getQuery(true);
        $query->select( 'f.*' );
        $query->select( 'u.name AS uploader' );
        $query->select( 'COUNT(rel.fileid) AS nrassigned' );
        $query->from( '#__seminarman_files AS f' );
        $query->join( "LEFT", '#__seminarman_files_course_relations AS rel ON rel.fileid = f.id' );
        $query->join( "LEFT", '#__users AS u ON u.id = f.uploaded_by' );
        if ( $where != '' ) {
        	$query->where( $where );
        }
        $query->group( 'f.id' );
        $query->order( $orderby );
        return $query;
    }

    function _buildContentOrderBy()
    {
        $mainframe = JFactory::getApplication();

        $filter_order = $mainframe->getUserStateFromRequest('com_seminarman' .
            '.fileselement.filter_order', 'filter_order', 'f.filename', 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest('com_seminarman' .
            '.fileselement.filter_order_Dir', 'filter_order_Dir', '', 'word');

        $orderby = $filter_order . ' ' . $filter_order_Dir .
            ', f.filename';

        return $orderby;
    }

    function _buildContentWhere()
    {
        $mainframe = JFactory::getApplication();

        $search = $mainframe->getUserStateFromRequest('com_seminarman' . '.fileselement.search',
            'search', '', 'string');
        $filter = $mainframe->getUserStateFromRequest('com_seminarman' . '.fileselement.filter',
            'filter', '', 'int');
        $search = $this->_db->escape(trim(JString::strtolower($search)));

        $where = array();

        if ($search && $filter == 1)
        {
            $where[] = ' LOWER(f.filename) LIKE ' . $this->_db->Quote('%' . $this->_db->
                escape($search, true) . '%', false);
        }

        if ($search && $filter == 2)
        {
            $where[] = ' LOWER(f.altname) LIKE ' . $this->_db->Quote('%' . $this->_db->
                escape($search, true) . '%', false);
        }

        $where = (count($where) ? implode(' AND ', $where) : '');

        return $where;
    }
}

?>