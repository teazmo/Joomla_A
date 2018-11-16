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

class SeminarmanModelTutor extends JModelLegacy
{
	var $_data = null;
	var $_courses = null;
	var $_id = null;

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
		
		$this->setState('filter_order', JRequest::getCmd('filter_order', $ordering));
		$this->setState('filter_order_dir', JRequest::getCmd('filter_order_Dir', 'ASC'));
		
		// $id = JRequest::getVar('id', 0, 'get', 'int');
		$jversion = new JVersion();
		$short_version = $jversion->getShortVersion();
		if (version_compare($short_version, "3.0", 'ge')) {
		    $id = JFactory::getApplication()->input->get('id');
		} else {
			$id = JRequest::getVar('id', 0, 'get', 'int');
		}
		$this->setId((int)$id);
	}
	
	function setId($id)
	{
	
		$this->_id = $id;
		$this->_data = null;
	}

	function getTutor()
	{
 		if ($this->_loadTutor()) {
		    return $this->_data;
 		}
	}

	function _loadTutor()
	{
		$mainframe = JFactory::getApplication();
	
		if ($this->_id == '0')
		{
			return false;
		}
	
		if (empty($this->_data))
		{
		    $query = $this->_db->getQuery(true);
		    $query->select( 'CONCAT_WS(" ", t.other_title, t.firstname, t.lastname) as tutor_label' );
		    $query->select( 'CASE WHEN CHAR_LENGTH(t.alias) THEN CONCAT_WS(\':\', t.id, t.alias) ELSE t.id END as tutor_slug' );
		    $query->select( 't.id AS tutor_id' );
		    $query->select( 't.logofilename AS tutor_photo' );
		    $query->select( 't.description AS tutor_desc' );
		    $query->select( 't.*' );
		    $query->from( '#__seminarman_tutor AS t' );
		    $query->where( 't.id = ' . $this->_id );

			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			return (boolean)$this->_data;
		}
		return true;
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
		
		if ($this->_id == '0')
		{
			return false;
		}

		if (empty($this->_courses))
		{
		
            $query = $this->_buildQuery($this->_id);
            
            $this->_courses = $this->_getList($query, $this->getState('limitstart'), $this->
                getState('limit'));
			return (boolean)$this->_courses;
		}
		return true;		
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
        $where .= ' AND FIND_IN_SET(' . $this->_id . ', replace(replace(i.tutor_id, "[", ""), "]", ""))';
        return $where;
	}
	
	function getEditableCustomfields($tutorId = null)
	{
		$db   = $this->getDBO();
		$data = array();

		$query = $db->getQuery(true);
		$query->select( 'f.*' );
		$query->select( 'v.value' );
		$query->from( '`#__seminarman_fields` AS f' );
		
		if (!empty($tutorId))
		{
			$query->join( "LEFT", '`#__seminarman_fields_values_tutors` AS v ON f.id = v.field_id AND v.tutor_id = '.(int)$tutorId );
		}
		else
		{
			$query->join( "LEFT", '`#__seminarman_fields_values_tutors` AS v ON f.id = v.field_id AND v.tutor_id = 0' );
		}

		$query->where( 'f.visible=1' );
		$query->where( 'f.published=1' );
		$query->order( 'f.ordering' );
			
		$db->setQuery( $query );
		
		$result	= $db->loadAssocList();
	
		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
	
		$data['fields']	= array();
		for($i = 0; $i < count($result); $i++)
		{
		// We know that the groups will definitely be correct in ordering.
			if($result[$i]['type'] == 'group' && $result[$i]['purpose'] == 2)
			{
			$add = True;
			$group	= $result[$i]['name'];
	
			// Group them up
			if(!isset($data['fields'][$group]))
			{
			// Initialize the groups.
			$data['fields'][$group]	= array();
			}
			}
			if($result[$i]['type'] == 'group' && $result[$i]['purpose'] != 2)
				$add = False;
	
				// Re-arrange options to be an array by splitting them into an array
				if(isset($result[$i]['options']) && $result[$i]['options'] != '')
				{
				$options	= $result[$i]['options'];
				$options	= explode("\n", $options);
	
				$countOfOptions = count($options);
				for($x = 0; $x < $countOfOptions; $x++){
				$options[$x] = trim($options[$x]);
				}
	
				$result[$i]['options']	= $options;
	
				}
	
	
				if($result[$i]['type'] != 'group' && isset($add)){
				if($add)
					$data['fields'][$group][]	= $result[$i];
				}
				}
				
				return $data;
		}
}