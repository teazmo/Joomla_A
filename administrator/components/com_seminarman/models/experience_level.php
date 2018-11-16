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

class seminarmanModelExperience_level extends JModelLegacy
{
    var $_id = null;

    var $_data = null;

    function __construct()
    {
        parent::__construct();

        $array = JRequest::getVar('cid', array(0), '', 'array');
        $edit = JRequest::getVar('edit', true);
        if ($edit)
            $this->setId((int)$array[0]);
        $this->childviewname = 'experience_level';
    }

    function setId($id)
    {

        $this->_id = $id;
        $this->_data = null;
    }

    function &getData()
    {

        if ($this->_loadData())
        {

            $user = JFactory::getUser();

        } else
            $this->_initData();

        return $this->_data;
    }

    function isCheckedOut($uid = 0)
    {
        if ($this->_loadData())
        {
            if ($uid)
            {
                return ($this->_data->checked_out && $this->_data->checked_out != $uid);
            } else
            {
                return $this->_data->checked_out;
            }
        }
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

    function store($data)
    {
        $row = $this->getTable();

        if (!$row->bind($data))
        {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        $row->date = gmdate('Y-m-d H:i:s');

        if (!$row->id)
        {
            $where = '';
            $row->ordering = $row->getNextOrder($where);
        }

        if (!$row->check())
        {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        if (!$row->store())
        {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        return $row->id;
    }


    function delete($cid = array())
    {
        $result = false;

        if (count($cid))
        {
            JArrayHelper::toInteger($cid);
            $cids = implode(',', $cid);

            $query = $this->_db->getQuery(true);
            $query->select( 'id' );
            $query->from( '#__seminarman_courses' );
        	$query->where( 'id_experience_level IN ( ' . $cids . ' )' );
            $this->_db->setQuery( $query );
            
            $jversion = new JVersion();
			$short_version = $jversion->getShortVersion();
			if (version_compare($short_version, "3.0", 'ge')) {
            	$relatedRecords = $this->_db->loadColumn();
			} else {
				$relatedRecords = $this->_db->loadResultArray();
			}
            if ( !empty( $relatedRecords ) )
            {
                JError::raiseWarning('ERROR_CODE', JText::sprintf('COM_SEMINARMAN_RELATED_N_RECORDS', implode(',', $relatedRecords)));
                return false;
            }

            $query = $this->_db->getQuery(true);
            $query->delete( $this->_db->quoteName( '#__seminarman_' . $this->childviewname ) )
            	  ->where( $this->_db->quoteName( 'id' ) . ' IN (' . $cids . ')' );
            $this->_db->setQuery( $query );
            
            if (!$this->_db->execute())
            {
                $this->setError($this->_db->getErrorMsg());
                return false;
            }
        }

        return true;
    }

    function approve($cid = array(), $approve = 1)
    {
        $user = JFactory::getUser();
        
        if (count($cid)){
        	JArrayHelper::toInteger($cid);
        	$cids = implode(',', $cid);
        
        	$fields = array(
        			$this->_db->quoteName('approved') . ' = ' . (int) $approved
        	);
        	 
        	$conditions = array(
        			$this->_db->quoteName('id') . ' IN ( '.$cids.' )',
        			'( checked_out = 0 OR ( checked_out = '.(int) $user->get('id').' ) )'
        	);
        
        	$query = $this->_db->getQuery(true);
        	$query->update( $this->_db->quoteName( '#__seminarman_' . $this->childviewname ) )
        	->set( $fields )
        	->where( $conditions );
        	 
        	$this->_db->setQuery( $query );
        
        	if (!$this->_db->execute()){
        		$this->setError($this->_db->getErrorMsg());
        		return false;
        	}
        }
        
        return true;
    }

    function publish($cid = array(), $publish = 1)
    {
        $user = JFactory::getUser();
        
        if (count($cid))
        {
            JArrayHelper::toInteger($cid);
            $cids = implode(',', $cid);
        
        	$query = $this->_db->getQuery(true);
        	 
        	$fields = array( $this->_db->quoteName( 'published' ). ' = ' . (int)$publish );
        	$conditions = array( $this->_db->quoteName('id') . ' IN (' . $cids . ')',
        			'( checked_out = 0 OR ( checked_out = ' . (int) $user->get('id') . ' ) )'
        	);
        	 
        	$query->update( $this->_db->quoteName( '#__seminarman_' . $this->childviewname ) )->set( $fields )->where( $conditions );
        	 
        	$this->_db->setQuery($query);
        
        	if ( !$this->_db->execute() )
        	{
        		$this->setError($this->_db->getErrorMsg());
        		return false;
        	}
        }
        return true;
    }


    function move($direction)
    {
        $row = $this->getTable();
        if (!$row->load($this->_id))
        {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        if (!$row->move($direction, ' published >= 0 '))
        {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        return true;
    }

    function saveorder($cid = array(), $order)
    {
        $row = $this->getTable();
        $groupings = array();

        for ($i = 0; $i < count($cid); $i++)
        {
            $row->load((int)$cid[$i]);

            $groupings[] = '';

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

        $groupings = array_unique($groupings);
        foreach ($groupings as $group)
        {
            $row->reorder('');
        }

        return true;
    }

    function _loadData()
    {
        if (empty($this->_data))
        {
        	$query = $this->_db->getQuery(true);
        	$query->select( 'w.*' );
        	$query->from( '#__seminarman_' . $this->childviewname . ' AS w' );
        	$query->where( 'w.id = ' . (int)$this->_id );
            $this->_db->setQuery( $query );
            $this->_data = $this->_db->loadObject();
            return (boolean)$this->_data;
        }
        return true;
    }

    function _initData()
    {

        if (empty($this->_data))
        {
            $experience_level = new stdClass();
            $experience_level->id = 0;
            $experience_level->title = null;
            $experience_level->alias = null;
            $experience_level->code = null;
            $experience_level->description = null;
            $experience_level->date = null;
            $experience_level->hits = 0;
            $experience_level->published = 0;
            $experience_level->checked_out = 0;
            $experience_level->checked_out_time = 0;
            $experience_level->ordering = 0;
            $experience_level->archived = 0;
            $experience_level->approved = 0;
            $experience_level->params = null;
            $this->_data = $experience_level;
            return (boolean)$this->_data;

        }
        return true;
    }
}