<?php
/**
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011-15 Open Source Group GmbH www.osg-gmbh.de
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

class SeminarmanModelFavourites extends JModelLegacy
{
    var $_data = null;

    var $_total = null;

    function __construct()
    {
        parent::__construct();

        $mainframe = JFactory::getApplication();

        $params = $mainframe->getParams('com_seminarman');

    	$id = JRequest::getInt('id', 0);

    	$this->setId((int)$id);

        $limit = JRequest::getInt('limit', $params->get('limit'));
        $limitstart = JRequest::getInt('limitstart');

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);

        $this->setState('filter_order', JRequest::getCmd('filter_order', 'i.title'));
        $this->setState('filter_order_dir', JRequest::getCmd('filter_order_Dir', 'ASC'));
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
        $user = JFactory::getUser();
    	$where = SMANFunctions::buildCourseWhere($this->_db);
        $where .= ' AND f.userid = ' . (int)$user->get('id');
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
		$query->where( 'published = 1' );
		$query->order( $ordering );
		
		$this->_db->setQuery( $query );
		$this->_subs = $this->_db->loadObjectList();

		return $this->_subs;
	}

	function getCategory($courseid)
	{
		$user = JFactory::getUser();
		$gid = (int)$user->get('aid');

		$query = $this->_db->getQuery(true);
		$query->select( 'DISTINCT c.*' );
		$query->select( 'CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug' );
		$query->from( '`#__seminarman_categories` AS c' );
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


//		if ($this->_category->access > $gid)
//		{
//			JError::raiseError(403, JText::_("ALERTNOTAUTH"));
//			return false;
//		}

		return $this->_category;
	}
}

?>