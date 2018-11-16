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

class SeminarmanModelCourses extends JModelLegacy
{
    var $_course = null;
    var $_tags = null;
    var $_id = null;
    var $_attendeedata = null;

    function __construct()
    {
        parent::__construct();

        if (isset($_POST['course_id']) && ($_POST['course_id'] > 0)) {
        	$id = $_POST['course_id'];
        } else {
            $id = JRequest::getVar('id', 0, '', 'int');
        }
        $this->setId((int)$id);
    }


    function setId($id)
    {

        $this->_id = $id;
        $this->_course = null;
    }

    function get($property, $default = null)
    {
        if ($this->_loadCourse())
        {
            if (isset($this->_course->$property))
            {
                return $this->_course->$property;
            }
        }
        return $default;
    }

    function set($property, $value = null)
    {
        if ($this->_loadCourse())
        {
            $this->_course->$property = $value;
            return true;
        } else
        {
            return false;
        }
    }

    function &getCourse()
    {
        if ($this->_loadCourse())
        {
            $user = JFactory::getUser();
            
            $courseCats = $this->getCategories();
            if (!empty($courseCats)) {
            	$this->_course->catpublished = 1;
            } else {
            	$this->_course->catpublished = 0;
            }

            if (!$this->_course->catpublished && $this->_course->catid)
            {
                JError::raiseError(404, JText::_("CATEGORY NOT PUBLISHED"));
            }

            if ($this->_course->created_by_alias)
            {
                $this->_course->creator = $this->_course->created_by_alias;
            } else
            {
                $query = $this->_db->getQuery(true);
                $query->select( 'name' );
                $query->from( '#__users' );
                $query->where( 'id = ' . (int)$this->_course->created_by );
                
                $this->_db->setQuery($query);
                $this->_course->creator = $this->_db->loadResult();
            }

            if ($this->_course->created_by == $this->_course->modified_by)
            {
                $this->_course->modifier = $this->_course->creator;
            } else
            {
                $query = $this->_db->getQuery(true);
                $query->select( 'name' );
                $query->from( '#__users' );
                $query->where( 'id = ' . (int)$this->_course->modified_by );
                
                $this->_db->setQuery($query);
                $this->_course->modifier = $this->_db->loadResult();
           }

            if ($this->_course->modified == $this->_db->getNulldate())
            {
                $this->_course->modified = null;
            }

            $session = JFactory::getSession();
            $hitcheck = false;
            if ($session->has('hit', 'seminarman'))
            {
                $hitcheck = $session->get('hit', 0, 'seminarman');
                $hitcheck = in_array($this->_course->id, $hitcheck);
            }
            if (!$hitcheck)
            {

                $this->hit();

                $stamp = array();
                $stamp[] = $this->_course->id;
                $session->set('hit', $stamp, 'seminarman');
            }

            $this->_course->text = $this->_course->introtext . chr(13) . chr(13) . $this->_course->
                fulltext;
        } else
        {
            $user = JFactory::getUser();
            $course = JTable::getInstance('seminarman_courses', '');
            if ($user->authorise('com_seminarman', 'state'))
            {
                $course->state = 1;
            }
            $course->id = 0;
            $course->author = null;
            $course->created_by = $user->get('id');
            $course->text = '';
            $course->title = null;
	    	$course->code = null;
            $course->meta_description = '';
            $course->meta_keywords = '';
            $this->_course = $course;
        }

        return $this->_course;
    }

    function _loadCourse()
    {
        $mainframe = JFactory::getApplication();
        $params = $mainframe->getParams('com_seminarman');
        $jnow = JFactory::getDate();
        // $now = $jnow->toMySQL();
        $now = $jnow->toSQL();
        $nullDate = $this->_db->getNullDate();

        if ($this->_id == '0')
        {
            return false;
        }

        if (empty($this->_course))
        {
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
          	
            $query = $this->_db->getQuery(true);
            $query->select( 'i.*' );
            $query->select( '(i.plus / (i.plus + i.minus) ) * 100 AS votes' );
            $query->select( 'c.access AS cataccess' );
            $query->select( 'c.id AS catid' );
            $query->select( 'c.published AS catpublished' );
            $query->select( 'c.title AS categorytitle' );
            $query->select( 'u.name AS author' );
            $query->select( 'CONCAT_WS(\' \', emp.salutation, emp.other_title, emp.firstname, emp.lastname) AS tutor' );
            $query->select( 'emp.published AS tutor_published' );
            $query->select( 'gr.title AS cgroup' );
            $query->select( 'lev.title AS level' );
            $query->select( 'CASE WHEN CHAR_LENGTH(i.alias) THEN CONCAT_WS(\':\', i.id, i.alias) ELSE i.id END as slug' );
            $query->select( 'CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug' );
            $query->from( '#__seminarman_courses AS i' );
            $query->join( "LEFT", '#__seminarman_cats_course_relations AS rel ON rel.courseid = i.id' );
            $query->join( "LEFT", '#__seminarman_atgroup AS gr ON gr.id = i.id_group' );
            $query->join( "LEFT", '#__seminarman_experience_level AS lev ON lev.id = i.id_experience_level' );
            $query->join( "LEFT", '#__seminarman_tutor AS emp ON emp.id = i.tutor_id' );
            $query->join( "LEFT", '#__seminarman_categories AS c ON c.id = rel.catid' );
            $query->join( "LEFT", '#__users AS u ON u.id = i.created_by' );
            $query->where( 'i.id = ' . $this->_id );
            $query->where( 'i.state >= 1' );
            $query->where( '( i.publish_up = ' . $this->_db->Quote($nullDate) . ' OR i.publish_up <= ' . $this->_db->Quote($now) . ' )' );
            $query->where( '(' . $publish_down . ' = ' . $this->_db->Quote($nullDate) . ' OR ' . $publish_down . ' >= ' . $this->_db->Quote($now) . ' OR i.state = 2 )' );
            
            $this->_db->setQuery( $query );
            $this->_course = $this->_db->loadObject();
            
            return (boolean)$this->_course;
        }
        return true;
    }

    function getTags()
    {
        $query = $this->_db->getQuery(true);
        $query->select( 'DISTINCT t.name' );
        $query->select( 'CASE WHEN CHAR_LENGTH(t.alias) THEN CONCAT_WS(\':\', t.id, t.alias) ELSE t.id END as slug' );
        $query->from( '#__seminarman_tags AS t' );
        $query->join( "LEFT", '#__seminarman_tags_course_relations AS i ON i.tid = t.id' );
        $query->where( 'i.courseid = ' . (int)$this->_id );
        $query->where( 't.published = 1' );
        $query->order( 't.name' );
        
        $this->_db->setQuery( $query );
        $this->_tags = $this->_db->loadObjectList();

        return $this->_tags;
    }

    function getCategories()
    {
        $query = $this->_db->getQuery(true);
        $query->select( 'c.id' );
        $query->select( 'c.title' );
        $query->select( 'CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug' );
        $query->from( '#__seminarman_categories AS c' );
        $query->join( "LEFT", '#__seminarman_cats_course_relations AS rel ON rel.catid = c.id' );
        $query->where( 'rel.courseid = ' . $this->_id );
        $query->where( 'c.published = 1' );
        
        $this->_db->setQuery( $query );
        $this->_cats = $this->_db->loadObjectList();
        
        return $this->_cats;
    }

   
    function getAttendee()
    {
        $mainframe = JFactory::getApplication();
        $user = JFactory::getUser();
        if ($user->id != 0)
        {
	        $query = $this->_db->getQuery(true);
	        $query->select( '*' );
	        $query->from( '`#__seminarman_application`' );
	        $query->where( 'user_id = '. (int)$user->get('id') );
	        $query->where( 'course_id = ' . $this->_id );
	        $query->where( 'published <> -2' );
	        $query->where( '( status < 3 OR status = 4 OR status = 5 )' );
	        
	        $this->_db->setQuery($query);
        	$this->_attendeedata = $this->_db->loadObject();
        	
        	if (!empty($this->_attendeedata))
            {
            	$this->_attendeedata->jusertype = null;
            	$this->_attendeedata->attendees = null;
            }
            else
            {
        		$query = $this->_db->getQuery(true);
        		$query->select( '*' );
        		$query->from( '`#__users`' );
        		$query->where( 'id = '. (int)$user->get('id') );
        		$query->where( 'block = 0' );
        	 
        		$this->_db->setQuery( $query );
        		$this->_attendeedata = $this->_db->loadObject();
            	
            	$this->_attendeedata->attendees = null;
            	$namePieces = explode(" ", $this->_attendeedata->name);
            	$this->_attendeedata->first_name = $namePieces[0];
            	$this->_attendeedata->last_name = empty($namePieces[1]) ? '' : $namePieces[1];
            	$this->_attendeedata->email = $user->email;
            	$this->_attendeedata->jusertype = true;
            	$this->_attendeedata->user_id = $user->id;

        		$query = $this->_db->getQuery(true);
        		$query->select( '*' );
        		$query->from( '`#__seminarman_fields_values_users_static`' );
        		$query->where( 'user_id = '.(int)$user->id );

        		$this->_db->setQuery( $query );
        		$row = $this->_db->loadAssoc();
        		
            	$this->_attendeedata->salutation = 0;
            	$this->_attendeedata->salutationStr = $row['salutation'];
            	$this->_attendeedata->title = $row['title'];
            }
            
            $this->_attendeedata->booking_email_cc = null;
        }
        else
        {
            $this->_initattendeedata();
        }
        return $this->_attendeedata;
    }

	function _initattendeedata()
	{
		if (empty($this->_attendeedata))
		{
			$attendeedata = new stdClass();
			$attendeedata->id = null;
			$attendeedata->attendees = null;
			$attendeedata->first_name = null;
			$attendeedata->last_name = null;
			$attendeedata->salutation = 0;
			$attendeedata->title = '';
			$attendeedata->email = null;
			$attendeedata->booking_email_cc = null;
			$attendeedata->user_id = null;
            $attendeedata->jusertype = null;
			$this->_attendeedata = $attendeedata;
			return (boolean)$this->_attendeedata;
		}
		return true;
	}

    function hit()
    {
        if ($this->_id)
        {
            $course = JTable::getInstance('seminarman_courses', '');
            $course->hit($this->_id);
            return true;
        }
        return false;
    }

    function getAlltags()
    {
        $query = $this->_db->getQuery(true);
		$query->select( '*' );
		$query->from( '`#__seminarman_tags`' );
		$query->order( 'name' );

		$this->_db->setQuery( $query );
		$tags = $this->_db->loadObjectlist();
        
        return $tags;
    }

    function getUsedtags()
    {
        $query = $this->_db->getQuery(true);
        $query->select( 'tid' );
        $query->from( '`#__seminarman_tags_course_relations`' );
        $query->where( 'courseid = ' . (int)$this->_id );
        
        $this->_db->setQuery( $query );
        // $used = $this->_db->loadResultArray();
        $jversion = new JVersion();
        $short_version = $jversion->getShortVersion();
        if (version_compare($short_version, "3.0", 'ge')) {
            $used = $this->_db->loadColumn();
        } else {
        	$used = $this->_db->loadResultArray();
        }
        return $used;
    }

    function isCheckedOut($uid = 0)
    {
        if ($this->_loadCourse())
        {
            if ($uid)
            {
                return ($this->_course->checked_out && $this->_course->checked_out != $uid);
            } else
            {
                return $this->_course->checked_out;
            }
        } elseif ($this->_id < 1)
        {
            return false;
        } else
        {
            JError::raiseWarning(0, 'Unable to Load Data');
            return false;
        }
    }

    function checkin()
    {
        if ($this->_id)
        {
            $course = JTable::getInstance('seminarman_courses', '');
            return $course->checkin($this->_id);
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

            $course = JTable::getInstance('seminarman_courses', '');
            return $course->checkout($uid, $this->_id);
        }
        return false;
    }

    function store($data)
    {
        $course = JTable::getInstance('seminarman_courses', '');
        $user = JFactory::getUser();

        if (!$course->bind($data))
        {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        $course->id = (int)$course->id;

        $isNew = ($course->id < 1);

        if ($isNew)
        {
            $course->created = gmdate('Y-m-d H:i:s');
            $course->created_by = $user->get('id');
        } else
        {
            $course->modified = gmdate('Y-m-d H:i:s');
            $course->modified_by = $user->get('id');

            $query = $this->_db->getQuery(true);
            $query->select( 'hits' );
            $query->select( 'minus' );
            $query->select( 'plus' );
            $query->select( 'created' );
            $query->select( 'created_by' );
            $query->select( 'version' );
            $query->from( '`#__seminarman_courses`' );
            $query->where( 'id = ' . (int)$course->id );

            $this->_db->setQuery($query);
            $result = $this->_db->loadObjectList();

            $course->plus = $result->plus;
            $course->minus = $result->minus;
            $course->hits = $result->hits;

            $course->created = $result->created;
            $course->created_by = $result->created_by;

            $course->version = $result->version;
            $course->version++;

            if (!$user->authorise('com_seminarman', 'state'))
            {
                $course->state = $result->state;
            }
        }

        if (!$user->authorise('com_seminarman', 'state'))
        {
            if ($isNew)
            {

                $course->state = -2;
            } else
            {
	            $query = $this->_db->getQuery(true);
	            $query->select( 'state' );
	            $query->from( '`#__seminarman_courses`' );
	            $query->where( 'id = ' . (int)$course->id );

	            $this->_db->setQuery( $query );
	            $result = $this->_db->loadResult();                

                $course->state = $result;
            }
        }

        seminarman_html::saveContentPrep($course);

        if (!$course->check())
        {
            $this->setError($course->getError());
            return false;
        }

        if (!$course->store())
        {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        if ($isNew)
        {
            $this->_id = $course->_db->insertId();
        }

        $course->ordering = $course->getNextOrder();

        $tags = JRequest::getVar('tag', array(), 'post', 'array');

        $query = $this->_db->getQuery(true);
        
        $query->delete( $this->_db->quoteName( '#__seminarman_tags_course_relations' ) )
        ->where( $this->_db->quoteName( 'courseid' ) . ' =  '. $course->id );
        $this->_db->setQuery( $query );
        $this->_db->execute();

        foreach ($tags as $tag)
        {
            $query = $this->_db->getQuery(true);
             
            $fields = array();
            $fields[] = "`tid` = '" . $tag. "'" ;
            $fields[] = "`courseid` = '" . $course->id . "'" ;
             
            $query->insert( $this->_db->quoteName( '#__seminarman_tags_course_relations' ) )
            ->set($fields);
            
            $this->_db->setQuery($query);
            $this->_db->execute();
        }

        $cats = JRequest::getVar('cid', array(), 'post', 'array');

        $query = $this->_db->getQuery(true);
        
        $query->delete( $this->_db->quoteName( '#__seminarman_cats_course_relations' ) )
        ->where( $this->_db->quoteName( 'courseid' ) . ' =  '. $course->id );
        $this->_db->setQuery( $query );
        $this->_db->execute();

        foreach ($cats as $cat)
        {
            $query = $this->_db->getQuery(true);
             
            $fields = array();
            $fields[] = "`catid` = '" . $cat. "'" ;
            $fields[] = "`courseid` = '" . $course->id . "'" ;
             
            $query->insert( $this->_db->quoteName( '#__seminarman_cats_course_relations' ) )
            ->set($fields);
            
            $this->_db->setQuery($query);
            $this->_db->execute();
        }

        $this->_course = &$course;

        return $this->_course->id;
    }

    function storevote($id, $vote)
    {
        if ($vote == 1)
        {
            $target = 'plus';
        } elseif ($vote == 0)
        {
            $target = 'minus';
        } else
        {
            return false;
        }

        $query = $this->_db->getQuery(true);
         
        $fields = array( $this->_db->quoteName( $target ). ' = ( ' . $target . ' + 1 )' );
        $conditions = array( $this->_db->quoteName('id') . ' = ' . (int)$id );
         
        $query->update( $this->_db->quoteName( '#__seminarman_courses' ) )->set( $fields )->where( $conditions );
        
        $this->_db->setQuery($query);
        
        $this->_db->execute();

        return true;
    }

    function getCatsselected()
    {
        $query = $this->_db->getQuery(true);
        $query->select( 'DISTINCT catid' );
        $query->from( '`#__seminarman_cats_course_relations`' );
        $query->where( 'courseid = ' . (int)$this->_id );
        
        $this->_db->setQuery( $query );
        $jversion = new JVersion();
        $short_version = $jversion->getShortVersion();
        if (version_compare($short_version, "3.0", 'ge')) {
            $used = $this->_db->loadColumn();
        } else {
        	$used = $this->_db->loadResultArray();
        }        
        
        return $used;
    }

    function storetag($data)
    {
        $row = $this->getTable('seminarman_tags', '');

        if (!$row->bind($data))
        {
            JError::raiseError(500, $this->_db->getErrorMsg());
            return false;
        }

        if (!$row->check())
        {
            $this->setError($row->getError());
            return false;
        }

        if (!$row->store())
        {
            JError::raiseError(500, $this->_db->getErrorMsg());
            return false;
        }

        return $row->id;
    }

    function addtag($name)
    {
        $obj = new stdClass();
        $obj->name = $name;
        $obj->published = 1;

        $this->storetag($obj);


        return true;
    }

    function getFavourites()
    {
        $query = $this->_db->getQuery(true);
        $query->select( 'COUNT(id) AS favs' );
        $query->from( '`#__seminarman_favourites`' );
        $query->where( 'courseid = ' . (int)$this->_id );
        
        $this->_db->setQuery( $query );
        $favs = $this->_db->loadResult();
        
        return $favs;
    }

    function getFavoured()
    {
        $user = JFactory::getUser();

        $query = $this->_db->getQuery(true);
        $query->select( 'COUNT(id) AS fav' );
        $query->from( '`#__seminarman_favourites`' );
        $query->where( 'courseid = ' . (int)$this->_id );
        $query->where( 'userid= ' . (int)$user->id );
        
        $this->_db->setQuery( $query );
        $fav = $this->_db->loadResult();
        
        return $fav;
    }

    function getFiles()
    {        
        $query = $this->_db->getQuery(true);
        $query->select( 'DISTINCT rel.fileid' );
        $query->select( 'f.filename' );
        $query->select( 'f.altname' );
        $query->from( '`#__seminarman_files` AS f' );
        $query->join( "LEFT", '#__seminarman_files_course_relations AS rel ON rel.fileid = f.id' );
        $query->where( 'rel.courseid = ' . (int)$this->_id );
        
        $this->_db->setQuery( $query );
        $files = $this->_db->loadObjectList();

        $files = seminarman_images::BuildIcons($files);

        return $files;
    }

    function removefav()
    {
        $user = JFactory::getUser();

        $query = $this->_db->getQuery(true);
        
        $query->delete( $this->_db->quoteName( '#__seminarman_favourites' ) )
              ->where( $this->_db->quoteName( 'courseid' ) . ' =  '. (int)$this->_id )
       	      ->where( 'userid = ' . (int)$user->id );
        $this->_db->setQuery( $query );
        
        $remfav = $this->_db->execute();
        return $remfav;
    }

    function addfav()
    {
        $user = JFactory::getUser();

        $obj = new stdClass();
        $obj->courseid = $this->_id;
        $obj->userid = $user->id;

        $addfav = $this->_db->insertObject('#__seminarman_favourites', $obj);
        return $addfav;
    }

    function setcoursestate($id, $state = 1)
    {
        $user = JFactory::getUser();

        if ($id) {
            $query = $this->_db->getQuery(true);
             
            $fields = array( $this->_db->quoteName('state'). ' = ' . (int)$state );
            $conditions = array( $this->_db->quoteName('id') . ' = ' . (int)$id,
            		'( checked_out = 0 OR ( checked_out = ' . (int)$user->get('id') . ' ) )'
            );
             
            $query->update( $this->_db->quoteName( '#__seminarman_courses' ) )->set( $fields )->where( $conditions );

            $this->_db->setQuery($query);
            
            if (!$this->_db->execute())
            {
                $this->setError($this->_db->getErrorMsg());
                return false;
            }
        }
        return true;
    }

	/**
	 * Returns an array of custom editfields which are created from the back end.
	 *
	 * @access	public
	 * @param	string 	User's id.
	 * @returns array  An objects of custom fields.
	 */
	function getEditableCustomfields($applicationId	= null)
	{
		$db   = $this->getDBO();
		$data = array();
		$user = JFactory::getUser();
		
		$data['id']		= $user->id;
		$data['name']	= $user->name;
		$data['email']	= $user->email;

		if (!$user->guest)
		{
			$query = $db->getQuery(true);
			$query->select( 'COUNT(*)' );
			$query->from( '`#__seminarman_fields_values`' );
			$query->where( 'applicationid='.(int)$applicationId );
			$query->where( 'user_id='.(int)$user->id );
			
			$db->setQuery( $query );
			
			if ($db->loadResult() == 0)
			{
				// Es gibt noch keine Anmeldung auf den Kurs. Werte für Felder aus #__seminarman_fields_values_users holen
				// (kann aber auch leer sein)
				
				$query = $this->_db->getQuery(true);
				$query->select( 'f.*' );
				$query->select( 'v.value' );
				$query->from( '`#__seminarman_fields` AS f' );
				$query->join( "LEFT", '`#__seminarman_fields_values_users` AS v ON f.fieldcode = v.fieldcode AND v.user_id = '.(int)$user->id );
				$query->where( 'f.published=1' );
				$query->where( 'f.visible=1' );
				$query->order( 'f.ordering' );
					
				$this->_db->setQuery( $query );
 			}
 			else 
 			{					
				$query = $this->_db->getQuery(true);
				$query->select( 'f.*' );
				$query->select( 'v.value' );
				$query->from( '`#__seminarman_fields` AS f' );
				$query->join( "LEFT", '`#__seminarman_fields_values` AS v ON f.id = v.field_id AND v.applicationid = '.(int)$applicationId );
				$query->where( 'f.published=1' );
				$query->where( 'f.visible=1' );
				$query->order( 'f.ordering' );
					
				$this->_db->setQuery( $query );
			}
		}
		else 
		{
			$query = $this->_db->getQuery(true);
			$query->select( 'f.*' );
			$query->select( 'v.value' );
			$query->from( '`#__seminarman_fields` AS f' );
			$query->join( "LEFT", '`#__seminarman_fields_values` AS v ON f.id = v.field_id AND v.applicationid = 0');
			$query->where( 'f.published=1' );
			$query->where( 'f.visible=1' );
			$query->order( 'f.ordering' );
				
			$this->_db->setQuery( $query );
		}

		$result	= $db->loadAssocList();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		$data['fields']	= array();
		for($i = 0; $i < count($result); $i++)
		{
			// We know that the groups will definitely be correct in ordering.
			if($result[$i]['type'] == 'group' && $result[$i]['purpose'] == 0)
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
			if($result[$i]['type'] == 'group' && $result[$i]['purpose'] != 0)
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


?>
