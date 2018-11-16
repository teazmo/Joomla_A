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

class SeminarmanModelCourse extends JModelLegacy
{
    var $_course = null;

    function __construct()
    {
        parent::__construct();

        if (JRequest::getVar('task')!='add') {
          $cid = JRequest::getVar('cid', array(0), '', 'array');
        }

        JArrayHelper::toInteger($cid, array(0));
        $this->setId($cid[0]);
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

    function &getCourse()
    {
        if ($this->_loadCourse())
        {
            if (JString::strlen($this->_course->fulltext) > 1)
            {
                $this->_course->text = $this->_course->introtext . "<hr id=\"system-readmore\" />" .
                    $this->_course->fulltext;
            } else
            {
                $this->_course->text = $this->_course->introtext;
            }
            
            $db = $this->getDbo();
            	
            $query = $db->getQuery(true);
            $query->select( 'name' );
            $query->from('#__users');
            $query->where( "id = ".(int)$this->_course->created_by );
            
            $db->setQuery($query);
            $this->_course->creator = $db->loadResult();

            if ($this->_course->created_by == $this->_course->modified_by)
            {
                $this->_course->modifier = $this->_course->creator;
            } else
            {
            	
            	$query = $db->getQuery(true);
            	$query->select( 'name' );
            	$query->from('#__users');
            	$query->where( "id = ".(int)$this->_course->modified_by );
            	
                $db->setQuery($query);
                $this->_course->modifier = $db->loadResult();
            }

        } else
            $this->_initCourse();
        
        if ($this->getState('task') == 'createFromTmpl')
        {
        	$this->_initFromTmpl(JRequest::getVar('templateId'));
        }
        
        return $this->_course;
    }


    function _loadCourse()
    {

        if (empty($this->_course))
        {
        	$db = $this->getDbo();
        	 
        	$query = $db->getQuery(true);
        	$query->select( '*' );
        	$query->from( '#__seminarman_courses' );
        	$query->where( "id = ".$this->_id );
        	
        	$db->setQuery($query);
            $this->_course = $this->_db->loadObject();

            return (boolean)$this->_course;
        }
        return true;
    }

    function _initCourse()
    {

        if (empty($this->_course))
        {
        	$user = JFactory::getUser();
        	$params = JComponentHelper::getParams( 'com_seminarman' );
            $createdate = JFactory::getDate();
            $nullDate = $this->_db->getNullDate();

            $course = new stdClass();
            $course->id = 0;
            $course->cid[] = 0;
            $course->reference_number = null;
            $course->code = null;
            $course->title = null;
            $course->alias = null;
            $course->text = null;
            $course->plus = 0;
            $course->minus = 0;
            $course->hits = 0;
            $course->version = 0;
            $course->meta_description = null;
            $course->meta_keywords = null;
            $course->created = $nullDate;
            $course->created_by = null;
            $course->created_by_alias = $user->get('name');
            $course->modified = $nullDate;
            $course->modified_by = null;
            $course->attribs = null;
            $course->metadata = null;
            $course->state = 0;
            $course->job_title = null;
            $course->tutor_id = null;
            $course->id_pos_type = null;
            $course->price_type = null;
            $course->vat = $params->get('vat');
            $course->id_experience_level = null;
            $course->job_experience = null;
            $course->id_group = null;
            $course->url = null;
            $course->alt_url = null;
            $course->price = 0;
            $course->currency_price = null;
            $course->capacity = null;
            $course->location = null;
            $course->expire_date = null;
            $createdate = JFactory::getDate();
            $course->publish_up = $createdate->toUnix();
            $course->publish_down = '';
            $course->expire_down = null;
        	$course->capacity = null;
            $this->_course = $course;
            $course->email_template = 0;
            $course->invoice_template = 0;
            $course->attlst_template = 0;
            $course->start_date = '0000-00-00';
            $course->finish_date = '0000-00-00';
            $course->start_time = '';
            $course->finish_time = '';
            $course->templateId = 0;
            $course->new = 1;
            $course->canceled = 0;
            $course->certificate_text = null;
            $course->price2 = null;
            $course->price3 = null;
            $course->price4 = null;
            $course->price5 = null;
            $course->min_attend = 0;
            $course->theme_points = 0;
            $course->certificate_template = 0;
            $course->extra_attach_template = 0;
            return (boolean)$this->_course;
        }
        return true;
    }
    
    function _initFromTmpl($templateId)
    {
    	$this->_initCourse();
    	$user = JFactory::getUser();
    	
    	// get data from template
    	$db = $this->getDbo();
    	
    	$query = $db->getQuery(true);
    	$query->select( '*' );
    	$query->from( '#__seminarman_templates' );
    	$query->where( "id=" . (int) $templateId );
    	 
    	$db->setQuery($query);
    	$tpl = $db->loadObject();
    	
    	if ( !( $tpl ) )
    		return $this->_course;

    	$this->_course->title = $tpl->title;
    	$this->_course->code = $tpl->code;
    	$this->_course->price = $tpl->price;
    	$this->_course->vat = $tpl->vat;
    	$this->_course->text = $tpl->introtext;
    	$this->_course->price_type = $tpl->price_type;
    	$this->_course->location = $tpl->location;
    	$this->_course->url = $tpl->url;
    	$this->_course->email_template = $tpl->email_template;
    	$this->_course->invoice_template = $tpl->invoice_template;
    	$this->_course->attlst_template = $tpl->attlst_template;
    	$this->_course->start_date = $tpl->start_date;
    	$this->_course->finish_date = $tpl->finish_date;
    	$this->_course->id_group = $tpl->id_group;
    	$this->_course->id_experience_level = $tpl->id_experience_level;
    	$this->_course->job_experience = $tpl->job_experience;
    	$this->_course->capacity = $tpl->capacity;
    	$this->_course->attribs = $tpl->attribs;
    	$this->_course->certificate_text = $tpl->certificate_text;
    	
    	$this->_course->price2 = $tpl->price2;
    	$this->_course->price3 = $tpl->price3;
    	$this->_course->price4 = $tpl->price4;
    	$this->_course->price5 = $tpl->price5;
    	$this->_course->min_attend = $tpl->min_attend;
    	$this->_course->theme_points = $tpl->theme_points;
    	
    	$this->_course->meta_keywords = $tpl->meta_keywords;
    	$this->_course->meta_description = $tpl->meta_description;
    	$this->_course->metadata = $tpl->metadata;

    	return (boolean)$this->_course;
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
            JError::raiseWarning(0, 'UNABLE LOAD DATA');
            return false;
        }
    }

    function store($data)
    {  
    	$data['price'] = str_replace(array(',',' '),array('.',''),$data['price']);
    	$data['price2'] = str_replace(array(',',' '),array('.',''),$data['price2']);
        $data['price3'] = str_replace(array(',',' '),array('.',''),$data['price3']);
        $data['price4'] = str_replace(array(',',' '),array('.',''),$data['price4']);
        $data['price5'] = str_replace(array(',',' '),array('.',''),$data['price5']);
        
        $data['start_time'] = str_replace(' ', '', $data['start_time']);
        $data['finish_time'] = str_replace(' ', '', $data['finish_time']);
        
        // if ($data['price2']=='') $data['price2']=NULL;
        // if ($data['price3']=='') $data['price3']=NULL;
        // if ($data['price4']=='') $data['price4']=NULL;
        // if ($data['price5']=='') $data['price5']=NULL;

        if (is_array($data['tutor_id'])) {
        	JArrayHelper::toInteger($data['tutor_id']);
        } else {
        	$data['tutor_id'] = intval($data['tutor_id']);
        }
        $data['tutor_id'] = json_encode($data['tutor_id']);
        
    	require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS . 'helpers' . DS . 'seminarman.php');

        JRequest::checkToken() or jexit('Invalid Token');

        $course = $this->getTable('seminarman_courses', '');
        $user = JFactory::getUser();

        $details = JRequest::getVar('details', array(), 'post', 'array');
        $tags = JRequest::getVar('tag', array(), 'post', 'array');
        $cats = JRequest::getVar('catid', array(), 'post', 'array');
        $files = JRequest::getVar('fid', array(), 'post', 'array');
        $files = array_filter($files);
        $attachs = JRequest::getVar('fattach', array(), 'post', 'array');
        // array filter removed empty array element, that's not what we want for the attachment array
        // $attachs = array_filter($attachs);

        if (!is_array($cats) || count($cats) < 1)
        {
            $this->setError('SELECT CATEGORY');
            return false;
        }

        if (!$course->bind($data))
        {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        $course->bind($details);

        $course->id = (int)$course->id;
        $this->_id = $course->id;

        $nullDate = $this->_db->getNullDate();

        if ($course->id)
        {
            $course->modified = gmdate('Y-m-d H:i:s');
            $course->modified_by = $user->get('id');
        } else
        {
            $course->modified = $nullDate;
            $course->modified_by = '';

            $course->created = gmdate('Y-m-d H:i:s');
            $course->created_by = $user->get('id');
        }
        
        // $course->publish_up = JHTMLSeminarman::localDate2DbDate($course->publish_up);
        // $course->publish_down = JHTMLSeminarman::localDate2DbDate($course->publish_down);
        $course->publish_up = SeminarmanFunctions::formatDateToSQL($course->publish_up);
        $course->publish_down = SeminarmanFunctions::formatDateToSQL($course->publish_down);
        
        $course->vat = str_replace(",", ".", $course->vat);
        
        $course->state = JRequest::getVar('state', 0, '', 'int');
        $params = JRequest::getVar('params', null, 'post', 'array');
        
        // by all-day event the local time should be set all round the day
        $globaltz_start = false;
        $globaltz_finish = false;
        if (is_array($params) && $params['start_date_all']==1) {
        	$course->start_time = "00:00:00";
        	$globaltz_start = true;
        }
        if (is_array($params) && $params['finish_date_all']==1) {
        	$course->finish_time = "23:59:59";
        	$globaltz_finish = true;
        }
        if (is_array($params) && isset($params['fee1_value'])) $params['fee1_value'] = str_replace(",", ".", $params['fee1_value']);
        if (is_array($params) && isset($params['fee1_vat'])) $params['fee1_vat'] = str_replace(",", ".", $params['fee1_vat']);        
        // now convert them to UTC for database
        // $course->start_date = JHTMLSeminarman::localDate2DbDate($course->start_date);
        // $course->finish_date = JHTMLSeminarman::localDate2DbDate($course->finish_date);
        $course_begin = SeminarmanFunctions::formatDateToSQLParts($course->start_date, $course->start_time, $globaltz_start);
        $course_finish = SeminarmanFunctions::formatDateToSQLParts($course->finish_date, $course->finish_time, $globaltz_finish);
        
        $course->start_date = $course_begin[0];
        $course->start_time = $course_begin[1];
        $course->finish_date = $course_finish[0];
        $course->finish_time = $course_finish[1];

        if (is_array($params))
        {
            $txt = array();
            foreach ($params as $k => $v)
            {
                $txt[] = "$k=$v";
            }
            $course->attribs = implode("\n", $txt);
        }

        $metadata = JRequest::getVar('meta', null, 'post', 'array');
        if (is_array($params))
        {
            $txt = array();
            foreach ($metadata as $k => $v)
            {
                if ($k == 'description')
                {
                    $course->meta_description = $v;
                } elseif ($k == 'keywords')
                {
                    $course->meta_keywords = $v;
                } else
                {
                    $txt[] = "$k=$v";
                }
            }
            $course->metadata = implode("\n", $txt);
        }

        seminarman_html::saveContentPrep($course);

        if (!$course->id)
        {
            $course->ordering = $course->getNextOrder();
        }

        if (!$course->check())
        {
            $this->setError($course->getError());
            return false;
        }

        $course->version++;

        if (!$course->store())
        {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        $this->_course = &$course;

        $db = $this->getDbo();
        $query = $db->getQuery(true);
        
        $query->delete( $db->quoteName( '#__seminarman_tags_course_relations' ) )
        	  ->where( $db->quoteName( 'courseid' ) . ' =  '. $course->id );
        $db->setQuery( $query );
        $db->execute();
        
        foreach ($tags as $tag)
        {
	        $query = $db->getQuery(true);
	        
	        $fields = array();
	        $fields[] = "`tid` = '" . $tag. "'" ;
	        $fields[] = "`courseid` = '" . $course->id . "'" ;
	        
	        $query->insert($db->quoteName( '#__seminarman_tags_course_relations' ) )
	        ->set($fields);
	        	
	        $db->setQuery($query);
	        $db->execute();
        }

        $query = $db->getQuery(true);
        
        $query->delete( $db->quoteName( '#__seminarman_cats_course_relations' ) )
        ->where( $db->quoteName( 'courseid' ) . ' =  '. $course->id );
        $db->setQuery( $query );
        $db->execute();

        foreach ($cats as $cat)
        {
        	$query = $db->getQuery(true);
        	
            $fields = array();
            $fields[] = "`catid` = '" . $cat. "'" ;
            $fields[] = "`courseid` = '" . $course->id . "'" ;
             
            $query->insert($db->quoteName( '#__seminarman_cats_course_relations' ) )
            ->set($fields);
            
            $db->setQuery($query);
            $db->execute();
        }
        
        $query = $db->getQuery(true);
        
        $query->delete( $db->quoteName( '#__seminarman_files_course_relations' ) )
        	  ->where( $db->quoteName( 'courseid' ) . ' =  '. $course->id );
        $db->setQuery( $query );
        $db->execute();

        foreach ($files as $fkey => $file)
        {
            // we are sure that the attachs have the same ordered keys as files
            $query = $db->getQuery(true);
             
            $fields = array();
            $fields[] = "`fileid` = '" . $file. "'" ;
            $fields[] = "`courseid` = '" . $course->id . "'" ;
            $fields[] = "`email_attach` = '" . $attachs[$fkey]. "'" ;
             
            $query->insert($db->quoteName( '#__seminarman_files_course_relations' ) )
           		  ->set($fields);
            
            $db->setQuery($query);
            $db->execute();
        }
        
        if ( $data['price2'] == '' ) {
        	$query = $db->getQuery(true);
        	
        	$fields = array( $db->quoteName('price2'). ' = NULL' );
        	$conditions = array( $db->quoteName('id') . ' = ' . $course->id );
        	
        	$query->update( $db->quoteName( '#__seminarman_courses' ) )->set( $fields )->where( $conditions );
        	
        	$db->setQuery($query);
        	$db->execute();
        }
        
        if ($data['price3']=='') {
        	$query = $db->getQuery(true);
        	 
        	$fields = array( $db->quoteName('price3'). ' = NULL' );
        	$conditions = array( $db->quoteName('id') . ' = ' . $course->id );
        	 
        	$query->update( $db->quoteName( '#__seminarman_courses' ) )->set( $fields )->where( $conditions );
        	 
        	$db->setQuery($query);
        	$db->execute();
        }
        
        if ($data['price4']=='') {
        	$query = $db->getQuery(true);
        	 
        	$fields = array( $db->quoteName('price4'). ' = NULL' );
        	$conditions = array( $db->quoteName('id') . ' = ' . $course->id );
        	 
        	$query->update( $db->quoteName( '#__seminarman_courses' ) )->set( $fields )->where( $conditions );
        	 
        	$db->setQuery($query);
        	$db->execute();
        }
        
        if ($data['price5']=='') {   
        	$query = $db->getQuery(true);
        	
        	$fields = array( $db->quoteName('price5'). ' = NULL' );
        	$conditions = array( $db->quoteName('id') . ' = ' . $course->id );
        	 
        	$query->update( $db->quoteName( '#__seminarman_courses' ) )->set( $fields )->where( $conditions );
        	 
        	$db->setQuery($query);
        	$db->execute();
        }
        
        // from the version 2.8.15 we modified all date time fields in order to make the component functional with joomla timezone
        // therefore the fields "start_time" and "finish_time" are not allowed any more to be fed with NULL value.
        
        // if ($data['start_time']=='') {
        	// $query = 'UPDATE #__seminarman_courses SET start_time = NULL WHERE id = ' . $course->id;
        	// $this->_db->setQuery($query);
        	// $this->_db->query();
        // }

        // if ($data['finish_time']=='') {
        //	$query = 'UPDATE #__seminarman_courses SET finish_time = NULL WHERE id = ' . $course->id;
        //	$this->_db->setQuery($query);
        //	$this->_db->query();
        // }
        
        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('seminarman');

        // fire vmengine
        $results = $dispatcher->trigger('onProcessCourse', array($course));        
        
        // $smkid = $this->_course->id;
        // $smkvat = doubleval($this->_course->vat);
        
        // $this->vmupdate($smkid, $smkvat);
        
        // update ics
        SMANFunctions::create_course_ics($course);
        
        return true;

    }
    
    function setNew($cid, $value)
    {
    	JRequest::checkToken() or jexit('Invalid Token');
    	$db = $this->getDbo();
    	$user = JFactory::getUser();    	

    	$query = $db->getQuery(true);
    	
    	$fields = array( $db->quoteName('new'). ' = ' . (int)$value );
    	$conditions = array( $db->quoteName('id') . ' = ' . (int) $cid,
    			'(checked_out=0 OR (checked_out=' . $user->get('id') . '))'
    	);
    	
    	$query->update( $db->quoteName( '#__seminarman_courses' ) )->set( $fields )->where( $conditions );
    	
    	$db->setQuery($query);
    	$db->execute();
    	
    	return true;
    }
    
    function setCanceled($cid, $value)
    {
    	JRequest::checkToken() or jexit('Invalid Token');
    	$db = $this->getDbo();
    	$user = JFactory::getUser();
    	
    	$query = $db->getQuery(true);
    	 
    	$fields = array( $db->quoteName('canceled'). ' = ' . (int)$value );
    	$conditions = array( $db->quoteName('id') . ' = ' . (int) $cid, '(checked_out=0 OR (checked_out=' . $user->get('id') . '))' );
    	 
    	$query->update( $db->quoteName( '#__seminarman_courses' ) )->set( $fields )->where( $conditions );
    	 
    	$db->setQuery($query);
    	$db->execute();
    	 
    	return true;
    }

    function resetHits($id)
    {
        $row = $this->getTable('seminarman_courses', '');
        $row->load($id);
        $row->hits = 0;
        $row->store();
        $row->checkin();
        return $row->id;
    }


    function gettags()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select( '*' );
        $query->from( '#__seminarman_tags' );
        $query->order( "name" );
        
        $db->setQuery( $query );
        $tags = $db->loadObjectlist();
        
        return $tags;
    }

    function getusedtags( $id )
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select( 'DISTINCT tid' );
        
    	if ( $this->getState( 'task' ) == 'createFromTmpl' ) {
        	$query->from( '#__seminarman_tags_template_relations' );
    		$query->where( 'templateid = '.(int)JRequest::getVar('templateId') );
    	}
    	else {
        	$query->from( '#__seminarman_tags_course_relations' );
    		$query->where( 'courseid = ' . (int)$id );
    	}
        $db->setQuery($query);
        $jversion = new JVersion();
		$short_version = $jversion->getShortVersion();
		if (version_compare($short_version, "3.0", 'ge')) {
        	$used = $db->loadColumn();
		} else {
			$used = $db->loadResultArray();
		}
        return $used;
    }

 
    function gethits($id)
    {
        $db = $this->getDbo();
         
        $query = $db->getQuery(true);
        $query->select( 'hits' );
        $query->from( '#__seminarman_courses' );
        $query->order( 'id = ' . (int)$id );
        
        $db->setQuery( $query );
        $hits = $db->loadResult();
        
        return $hits;
    }

    function getCatsselected()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select( 'DISTINCT catid' );
        
    	if ( $this->getState( 'task' ) == 'createFromTmpl' ) {
    		$query->from( '#__seminarman_cats_template_relations' );
    		$query->where( 'templateid = '.(int)JRequest::getVar('templateId') );
    	}
    	else {
        	$query->from( '#__seminarman_cats_course_relations' );
    		$query->where( 'courseid = ' . (int)$this->_id );
    	}
        $db->setQuery($query);
        $jversion = new JVersion();
		$short_version = $jversion->getShortVersion();
		if (version_compare($short_version, "3.0", 'ge')) {
        	$used = $db->loadColumn();
		} else {
			$used = $db->loadResultArray();
		}
        return $used;
    }

    function getFiles()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select( 'DISTINCT rel.fileid, f.filename' );    	
        if ( !( $this->getState('task') == 'createFromTmpl' ) ) {
        	$query->select( 'rel.email_attach' );
        }
    	$query->from( '#__seminarman_files AS f' );
        
    	if ($this->getState('task') == 'createFromTmpl')
    	{
    		$query->join( "LEFT", "#__seminarman_files_template_relations AS rel ON (rel.fileid = f.id)");
    		$query->where( 'rel.templateid = ' . (int)JRequest::getVar('templateId') );
    	} else
    	{
	        $query->join( "LEFT", "#__seminarman_files_course_relations AS rel ON (rel.fileid = f.id)");
	        $query->where( 'rel.courseid = ' . (int)$this->_id );
    	}
    	
        $db->setQuery($query);
        $files = $db->loadObjectList();
        return $files;
    }

	function copycourse($cid = array()){

		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );
		//$this->setRedirect( 'index.php?option=com_seminarman' );

		$cid	= JRequest::getVar( 'cid', null, 'post', 'array' );
		$db		= JFactory::getDBO();
		$table	= JTable::getInstance('seminarman_courses', '');
		//$table = $this->getTable();
		$user	= JFactory::getUser();
		$n		= count( $cid );
		if ($n > 0)
		{
			foreach ($cid as $id)
			{
				if ($table->load( (int)$id ))
				{
					$table->id				= 0;
					$table->title			= 'Copy of ' . $table->title;
					$table->alias			= 'copy-of-' . $table->alias;
					$table->hits			= 0;
					$table->state			= 0;
					$table->publish_up		= $table->publish_up;
					$table->publish_down	= $table->publish_down;
					$table->ordering		= 0;
					$table->date			= $db->getNullDate();

					if (!$table->store()) {

						return false;
					}
				}
				else {
					return JError::raiseWarning( 500, $table->getError() );
				}
			}
		}
		else {
			return JError::raiseWarning( 500, JText::_( 'No items selected' ) );
		}
		return $n;
	}

    /**
     * Retrieves e-mail templates for booking
     *
     * @return array of objects with templates
     */
    function getEmailTemplates()
    {
        $db = $this->getDbo();
         
        $query = $db->getQuery(true);
        $query->select( 'id, title' );
        $query->from( '#__seminarman_emailtemplate' );
        $query->where( 'templatefor=0' );
        
        $db->setQuery( $query );
        $temps = $db->loadObjectList();
        
        return $temps;
    }
    
    function getAttendanceLstTemplate($id = 0)
    {
    	$db = JFactory::getDBO();

    	$query = $db->getQuery(true);
    	$query->select( '*' );
    	$query->from( '#__seminarman_pdftemplate' );
    	$query->where( 'templatefor=1' );
    	
    	if ($id == 0) {
    		$query->where( 'isdefault=1' );
    	} else {
    		$query->where( 'id='.(int)$id );
    	}
    	$query->setLimit( '1' );
    	
    	$db->setQuery($query);
    	return $db->loadObject();
    }
    
    /**
    * returns an key value array that can be used to replace
    * the fields in a pdf template with actual values
    */
    function getAttendanceLstTemplateData()
    {
    	require_once (JPATH_COMPONENT_ADMINISTRATOR . DS . 'helpers' . DS . 'seminarman.php');
    	
    	$db = JFactory::getDBO();
    	
    	$params = JComponentHelper::getParams( 'com_seminarman' );
    	$attlst_status = $params->get("status_of_attlst");
    	switch($attlst_status) {
    	    case "-1":
    	        $attlst_status_string = "IN(0,1)"; // submitted, pending
    	        break;
    	    case "0":
    	        $attlst_status_string = "IN(1,2)"; // pending, paid
    	        break;
    	    case "0.5":
    	        $attlst_status_string = "IN(2)"; // paid
    	        break;
    	    case "1":
    	        $attlst_status_string = "IN(0,1,2)"; // submitted, pending, paid
    	        break;
    	    case "2":
    	        $attlst_status_string = "IN(0,1,2,3)"; // submitted, pending, paid, canceled
    	        break;
    	    case "3":
    	        $attlst_status_string = "IN(4,5)"; // waitinglist, awaiting response
    	        break;
    	    case "4":
    	        $attlst_status_string = "IN(4)"; // waitinglist
    	        break;
    	    case "5":
    	        $attlst_status_string = "IN(0,1,2,4,5)"; // all states except canceled
    	        break;
    	    case "6":
    	        $attlst_status_string = "IN(0,1,2,3,4,5)"; // all states
    	        break;
    	    default:
    	        $attlst_status_string = "IN(1,2)"; // pending, paid
    	}

    	$query = $db->getQuery(true);
    	
    	$query->select( 'NOW() AS `CURRENT_DATE`' );
    	$query->select( 'SUM(a.attendees) AS `ATTENDEES_TOTAL`' );
    	$query->select( 'c.id AS `COURSE_ID`' );
    	$query->select( 'c.title AS `COURSE_TITLE`' );
       	$query->select( 'c.code AS `COURSE_CODE`' );
       	$query->select( 'c.capacity AS `COURSE_CAPACITY`' );
       	$query->select( 'c.location AS `COURSE_LOCATION`' );
       	$query->select( 'c.url AS `COURSE_URL`' );
    	$query->select( 'c.introtext AS `COURSE_INTROTEXT`' );
    	$query->select( 'c.fulltext AS `COURSE_FULLTEXT`' );
       	$query->select( 'c.start_date AS `COURSE_START_DATE`' );
       	$query->select( 'c.finish_date AS `COURSE_FINISH_DATE`' );
    	$query->select( 'c.start_time AS `COURSE_START_TIME`' );
    	$query->select( 'c.finish_time AS `COURSE_FINISH_TIME`' );
    	$query->select( 'c.attribs AS `COURSE_ATTRIBS`' );
    	$query->select( 'c.tutor_id AS `COURSE_TUTOR_IDS`' );
    	$query->from( '#__seminarman_courses AS c' );
	    $query->join( "LEFT", '`#__seminarman_application` AS a ON (a.course_id = c.id AND a.status '.$attlst_status_string. ')' );
    	$query->where( 'c.id = '. (int) $this->_id );

    	$db->setQuery($query);
    	$data = $db->loadAssoc();
 
    	// don't modify $data['CURRENT_DATE']! MySQL NOW() returns the current time in the server time zone.
    	// course_start_date, course_start_time... still in UTC, we are gonna convert them to local time.
    	// compute loaded date time (utc) to local date time
    	$course_start_arr = SeminarmanFunctions::formatUTCtoLocal($data['COURSE_START_DATE'], $data['COURSE_START_TIME']);
    	$course_finish_arr = SeminarmanFunctions::formatUTCtoLocal($data['COURSE_FINISH_DATE'], $data['COURSE_FINISH_TIME']);
    	$data['COURSE_START_DATE'] = $course_start_arr[0];  // local
    	$data['COURSE_START_TIME'] = $course_start_arr[1];  // local
    	$data['COURSE_FINISH_DATE'] = $course_finish_arr[0];  // local
    	$data['COURSE_FINISH_TIME'] = $course_finish_arr[1];  // local
    	
    	// format date
    	$data['CURRENT_DATE'] = JFactory::getDate($data['CURRENT_DATE'])->format(JText::_('COM_SEMINARMAN_DATE_FORMAT1'));
    	$data['COURSE_START_DATE'] = (!empty($data['COURSE_START_DATE']) && $data['COURSE_START_DATE'] !== '0000-00-00') ? JFactory::getDate($data['COURSE_START_DATE'])->format(JText::_('COM_SEMINARMAN_DATE_FORMAT1')) : JText::_('COM_SEMINARMAN_NOVALUE');
    	$data['COURSE_FINISH_DATE'] = (!empty($data['COURSE_FINISH_DATE']) && $data['COURSE_FINISH_DATE'] !== '0000-00-00') ? JFactory::getDate($data['COURSE_FINISH_DATE'])->format(JText::_('COM_SEMINARMAN_DATE_FORMAT1')) : JText::_('COM_SEMINARMAN_NOVALUE');
    	$data['COURSE_START_TIME'] = (!empty($data['COURSE_START_TIME'])) ? date('H:i', strtotime($data['COURSE_START_TIME'])) : '';
    	$data['COURSE_FINISH_TIME'] = (!empty($data['COURSE_FINISH_TIME'])) ? date('H:i', strtotime($data['COURSE_FINISH_TIME'])) : '';

    	// parameters for multiple tutors
    	$course_tutors_id_array = (array)json_decode($data['COURSE_TUTOR_IDS'], true);    	
    	$course_first_tutor_id = $course_tutors_id_array[0];
    	$course_tutors = array();
    	foreach ($course_tutors_id_array as $course_tutors_id) {

    		$query = $db->getQuery(true);
    		$query->select( 'CONCAT_WS(\' \', emp.salutation, emp.other_title, emp.firstname, emp.lastname) AS tutor_combiname, CONCAT_WS(\' \', emp.firstname, emp.lastname) AS tutor_fullname,' . 
      		         ' emp.title AS tutor_displayname, emp.firstname AS tutor_firstname, emp.lastname AS tutor_lastname, emp.salutation AS tutor_salutation, emp.other_title AS tutor_title' );
    		$query->from( '#__seminarman_tutor AS emp' );
    		$query->where( 'emp.id = ' . $course_tutors_id );
    		$db->setQuery($query);
    		$ergebnis = $db->loadAssoc();
    		$course_tutors[$course_tutors_id] = $ergebnis;
    	}

    	$data['COURSE_ALL_TUTORS'] = '';
    	$data['COURSE_ALL_TUTORS_FULLNAME'] = '';
    	$data['COURSE_ALL_TUTORS_COMBINAME'] = '';
    	$printComma = false;
    	foreach ($course_tutors as $tutor_key => $tutor_content) {
    		if ($printComma) {
    			$data['COURSE_ALL_TUTORS'] = $data['COURSE_ALL_TUTORS'] . ', ';
    			$data['COURSE_ALL_TUTORS_FULLNAME'] = $data['COURSE_ALL_TUTORS_FULLNAME'] . ', ';
    			$data['COURSE_ALL_TUTORS_COMBINAME'] = $data['COURSE_ALL_TUTORS_COMBINAME'] . ', ';
    		}
    		$data['COURSE_ALL_TUTORS'] = $data['COURSE_ALL_TUTORS'] . $tutor_content['tutor_displayname'];
    		$data['COURSE_ALL_TUTORS_FULLNAME'] = $data['COURSE_ALL_TUTORS_FULLNAME'] . $tutor_content['tutor_fullname'];
    		$data['COURSE_ALL_TUTORS_COMBINAME'] = $data['COURSE_ALL_TUTORS_COMBINAME'] . $tutor_content['tutor_combiname'];
    		$printComma = true;
    	}
    	
    	// parameters for the first tutor
    	$data['TUTOR'] = $course_tutors[$course_first_tutor_id]['tutor_displayname'];
    	$data['TUTOR_FIRSTNAME'] = $course_tutors[$course_first_tutor_id]['tutor_firstname'];
    	$data['TUTOR_LASTNAME'] = $course_tutors[$course_first_tutor_id]['tutor_lastname'];
    	$data['TUTOR_SALUTATION'] = $course_tutors[$course_first_tutor_id]['tutor_salutation'];
    	$data['TUTOR_OTHER_TITLE'] = $course_tutors[$course_first_tutor_id]['tutor_title'];
    	
    	// custom tutor fields for the first tutor    	
    	$query = $db->getQuery(true);
    	$query->select( 'f.fieldcode, ct.value' );
    	$query->from( '`#__seminarman_fields_values_tutors` AS ct' );
	    $query->join( "LEFT", "#__seminarman_fields AS f ON (ct.field_id = f.id)");
    	$query->where( 'ct.tutor_id = '. (int)$course_first_tutor_id );
    	$query->where( 'f.published = ' . $db->Quote('1') );
    	$db->setQuery($query);
    	
    	$ergebnis = $db->loadAssoc();
    	$course_tutors[$course_tutors_id] = $ergebnis;

    	$db->setQuery($query);
    	foreach ($db->loadRowList() as $row)
    		$data[$row[0]] = $row[1];  
    	
    	// course custom fields
    	$course_attribs = new JRegistry();
    	$course_attribs->loadString($data['COURSE_ATTRIBS']);
    	$custom_fld_1_value = $course_attribs->get('custom_fld_1_value');
    	$custom_fld_2_value = $course_attribs->get('custom_fld_2_value');
    	$custom_fld_3_value = $course_attribs->get('custom_fld_3_value');
    	$custom_fld_4_value = $course_attribs->get('custom_fld_4_value');
    	$custom_fld_5_value = $course_attribs->get('custom_fld_5_value');
    	$data['COURSE_CUSTOM_FIELD_1'] = (!empty($custom_fld_1_value))?$custom_fld_1_value:'';
    	$data['COURSE_CUSTOM_FIELD_2'] = (!empty($custom_fld_2_value))?$custom_fld_2_value:'';
    	$data['COURSE_CUSTOM_FIELD_3'] = (!empty($custom_fld_3_value))?$custom_fld_3_value:'';
    	$data['COURSE_CUSTOM_FIELD_4'] = (!empty($custom_fld_4_value))?$custom_fld_4_value:'';
    	$data['COURSE_CUSTOM_FIELD_5'] = (!empty($custom_fld_5_value))?$custom_fld_5_value:'';
    	
    	// start weekday
    	$langs = JComponentHelper::getParams('com_languages');
    	$selectedLang = $langs->get('site', 'en-GB');
    	if ($selectedLang == "de-DE") {
    		$trans = array(
    				'Monday'    => 'Montag',
    				'Tuesday'   => 'Dienstag',
    				'Wednesday' => 'Mittwoch',
    				'Thursday'  => 'Donnerstag',
    				'Friday'    => 'Freitag',
    				'Saturday'  => 'Samstag',
    				'Sunday'    => 'Sonntag',
    				'Mon'       => 'Mo',
    				'Tue'       => 'Di',
    				'Wed'       => 'Mi',
    				'Thu'       => 'Do',
    				'Fri'       => 'Fr',
    				'Sat'       => 'Sa',
    				'Sun'       => 'So',
    				'January'   => 'Januar',
    				'February'  => 'Februar',
    				'March'     => 'März',
    				'May'       => 'Mai',
    				'June'      => 'Juni',
    				'July'      => 'Juli',
    				'October'   => 'Oktober',
    				'December'  => 'Dezember'
    		);
    		$data['COURSE_START_WEEKDAY'] = (!empty($data['COURSE_START_DATE'])) ? strtr(date('l', strtotime($data['COURSE_START_DATE'])), $trans) : '';
    	} else {
    		$data['COURSE_START_WEEKDAY'] = (!empty($data['COURSE_START_DATE'])) ? date('l', strtotime($data['COURSE_START_DATE'])) : '';
    	}
    	
    	// first session infos
    	$query = $db->getQuery(true);
    	$query->select( '*' );
    	$query->from( '`#__seminarman_sessions`' );
    	$query->where( 'published = 1' );
    	$query->where( 'courseid = ' . $this->_id );
    	$query->order( 'session_date' );
    	$db->setQuery($query);
    	
    	$course_sessions = $db->loadObjectList();
    	
    	if(!empty($course_sessions)){
    		// compute loaded date time (utc) to local date time
    		$session_start_arr = SeminarmanFunctions::formatUTCtoLocal($course_sessions[0]->session_date, $course_sessions[0]->start_time);
    		$session_finish_arr = SeminarmanFunctions::formatUTCtoLocal($course_sessions[0]->session_date, $course_sessions[0]->finish_time);
    		$session_start_date = $session_start_arr[0];  // local
    		$session_start_time = $session_start_arr[1];  // local
    		$session_finish_date = $session_finish_arr[0];  // local
    		$session_finish_time = $session_finish_arr[1];  // local
    		
    		$data['COURSE_FIRST_SESSION_TITLE'] = $course_sessions[0]->title;
    		$data['COURSE_FIRST_SESSION_CLOCK'] = date('H:i', strtotime($session_start_time)) . ' - ' . date('H:i', strtotime($session_finish_time));
    		$data['COURSE_FIRST_SESSION_DURATION'] = $course_sessions[0]->duration;
    		$data['COURSE_FIRST_SESSION_ROOM'] = $course_sessions[0]->session_location;
    		$data['COURSE_FIRST_SESSION_COMMENT'] = $course_sessions[0]->description;
    	} else {
    		$data['COURSE_FIRST_SESSION_TITLE'] = '';
    		$data['COURSE_FIRST_SESSION_CLOCK'] = '';
    		$data['COURSE_FIRST_SESSION_DURATION'] = '';
    		$data['COURSE_FIRST_SESSION_ROOM'] = '';
    		$data['COURSE_FIRST_SESSION_COMMENT'] = '';
    	}
    	
    	return $data;
    }
    
    function getAttendeesData()
    {
    	$db = JFactory::getDBO();
    	
    	$params = JComponentHelper::getParams( 'com_seminarman' );
    	$order_type = $params->get("order_of_attlst");
    	$attlst_status = $params->get("status_of_attlst");

    	switch($attlst_status) {
    	    case "-1":
    	        $attlst_status_string = "IN(0,1)"; // submitted, pending
    	        break;
    	    case "0":
    	        $attlst_status_string = "IN(1,2)"; // pending, paid
    	        break;
    	    case "0.5":
    	        $attlst_status_string = "IN(2)"; // paid
    	        break;
    	    case "1":
    	        $attlst_status_string = "IN(0,1,2)"; // submitted, pending, paid
    	        break;
    	    case "2":
    	        $attlst_status_string = "IN(0,1,2,3)"; // submitted, pending, paid, canceled
    	        break;
    	    case "3":
    	        $attlst_status_string = "IN(4,5)"; // waitinglist, awaiting response
    	        break;
    	    case "4":
    	        $attlst_status_string = "IN(4)"; // waitinglist
    	        break;
    	    case "5":
    	        $attlst_status_string = "IN(0,1,2,4,5)"; // all states except canceled
    	        break;
    	    case "6":
    	        $attlst_status_string = "IN(0,1,2,3,4,5)"; // all states
    	        break;
    	    default:
    	        $attlst_status_string = "IN(1,2)"; // pending, paid
    	}
    	
    	switch($order_type) {
    		case "0":
    			$order_type_string = "a.id";
    			break;
    		case "1":
    			$order_type_string = "a.last_name, a.first_name";
    			break;
    		case "2":
    			$order_type_string = "a.first_name, a.last_name";
    			break;
    		case "3":
    			$order_type_string = "a.email";
    			break;
    		default:
    			$order_type_string = "a.id";
    	}

    	$query = $db->getQuery( true );
		$query->select( ' a.id AS `APPLICATION_ID`' );
    	$query->select( ' a.salutation AS `SALUTATION`' );
       	$query->select( ' a.title AS `TITLE`' );
       	$query->select( ' a.first_name AS `FIRSTNAME`' );
       	$query->select( ' a.last_name AS `LASTNAME`' );
    	$query->select( ' a.pricegroup AS `PRICE_GROUP_ORDERED`' );
    	$query->select( ' a.status AS `PAYMENT_STATUS`' );
       	$query->select( ' a.email AS `EMAIL`' );
    	$query->select( ' a.price_per_attendee AS `PRICE_PER_ATTENDEE`' );
    	$query->select( ' a.price_total AS `PRICE_TOTAL`' );
    	$query->select( ' a.price_vat AS `PRICE_VAT_PERCENT`' );
    	$query->select( ' a.attendees AS `ATTENDEES`' );
    	$query->select( ' a.params AS `PARAMS`' );
    	$query->from( '`#__seminarman_application` AS a' );
	    $query->join( "LEFT", '`#__seminarman_courses` AS c ON a.course_id = c.id AND a.status '.$attlst_status_string);
    	$query->where( 'c.id = '. (int) $this->_id );
    	$query->where( 'published = 1' );
    	$query->order( $order_type_string );
    	$db->setQuery($query);
    	$data = $db->loadAssocList('APPLICATION_ID');
    	
    	foreach ($data as $k => $v) {
    		//unset($data[$k]['id']);
    		if ($data[$k]['PAYMENT_STATUS'] == 1) {
    			$data[$k]['PAYMENT_STATUS'] = JText::_( 'COM_SEMINARMAN_PENDING' );
    		} elseif ($data[$k]['PAYMENT_STATUS'] == 2) {
    			$data[$k]['PAYMENT_STATUS'] = JText::_( 'COM_SEMINARMAN_PAID' );
    		} elseif ($data[$k]['PAYMENT_STATUS'] == 3) {
    			$data[$k]['PAYMENT_STATUS'] = JText::_( 'COM_SEMINARMAN_CANCELED' );
    		} elseif ($data[$k]['PAYMENT_STATUS'] == 4) {
    			$data[$k]['PAYMENT_STATUS'] = JText::_( 'COM_SEMINARMAN_WL' );
    		} elseif ($data[$k]['PAYMENT_STATUS'] == 5) {
    			$data[$k]['PAYMENT_STATUS'] = JText::_( 'COM_SEMINARMAN_AWAITING_RESPONSE' );
    		} elseif ($data[$k]['PAYMENT_STATUS'] == 0) {
    			$data[$k]['PAYMENT_STATUS'] = JText::_( 'COM_SEMINARMAN_SUBMITTED' );
    		}
    		$price_booking = $data[$k]['PRICE_PER_ATTENDEE'];
    		$quantity = $data[$k]['ATTENDEES'];
    		$tax_rate = $data[$k]['PRICE_VAT_PERCENT'] / 100.0;
    		$price_total_booking = $data[$k]['PRICE_TOTAL'];
    		$price_booking_with_tax = $price_booking * (1 + $tax_rate);
    		$price_total_booking_with_tax = $price_total_booking * (1 + $tax_rate);
    		
    		$data[$k]['PRICE_REAL_BOOKING_SINGLE'] = JText::sprintf('%.2f', round($price_booking, 2));
    		$data[$k]['PRICE_REAL_BOOKING_TOTAL'] = JText::sprintf('%.2f', round($price_total_booking, 2));
    		$data[$k]['PRICE_TOTAL_VAT'] = JText::sprintf('%.2f', round($price_total_booking_with_tax, 2));
    		
    		$app_params_obj = new JRegistry();
    		$app_params_obj->loadString($data[$k]['PARAMS']);
    		$app_params = $app_params_obj->toArray();
    		
    		if (isset($app_params['fee1_selected'])) {
    		    $data[$k]['ATTENDEE_EXTRA_CHARGE_SELECTED'] = ($app_params['fee1_selected'] == '1') ? JText::_('JYES') : JText::_('JNO'); 
    		} else {
    		    $data[$k]['ATTENDEE_EXTRA_CHARGE_SELECTED'] = '';
    		}
    		$data[$k]['ATTENDEE_EXTRA_CHARGE_NAME'] = (isset($app_params['fee1_name'])) ? $app_params['fee1_name'] : '';
    		$data[$k]['ATTENDEE_EXTRA_CHARGE_VALUE_NETTO'] = (isset($app_params['fee1_value'])) ? JText::sprintf('%.2f', round($app_params['fee1_value'], 2)) : '';
    		$data[$k]['ATTENDEE_EXTRA_CHARGE_VAT_PERCENT'] = (isset($app_params['fee1_vat'])) ? JText::sprintf('%.2f', round($app_params['fee1_vat'], 2)) . '%' : '';
    	}
    	
    	$query = $db->getQuery(true);
    	$query->select( 'v.applicationid, fieldcode, value' );
    	$query->from( '`#__seminarman_fields_values` AS v' );
    	$query->join( "LEFT", '`#__seminarman_fields` AS f ON v.field_id = f.id' );
    	$query->join( "LEFT", '`#__seminarman_application` AS a ON a.id = v.applicationid' );
    	$query->where( 'a.status '.$attlst_status_string );
    	$query->where( 'a.course_id = '. (int) $this->_id );
    	$query->where( 'a.published = 1' );
    	$query->order( 'v.applicationid' );
    	$db->setQuery($query);
    	
    	foreach ($db->loadRowList() as $record) {
    		$data[$record[0]][$record[1]] = $record[2];
    	}

    	return $data;
    }
    
    function UploadImage($file)
    {
    	if ($file['size'] > 0){
    		jimport('joomla.filesystem.file');
    		$post = JRequest::get('post');
    		$params = JComponentHelper::getParams( 'com_seminarman' );
    
    		$path = COM_SEMINARMAN_IMAGEPATH . DS . $post['logotodelete'];
    		if (JFile::exists($path)){
    			JFile::delete($path);
    		}
    
    		$filename = JFile::makeSafe($post['id'] . $file['name']);
    
    		$src = $file['tmp_name'];
    		$dest = COM_SEMINARMAN_IMAGEPATH . DS . $filename;
    		$extensions = $params->get('image_extensions');
    		$extensions = explode(',', $extensions);
    		for ($i = 0; $i < count($extensions); $i++){
    			if (strtolower(JFile::getExt($filename)) == $extensions[$i]){
    				$fileextensions = 1;
    			}
    		}
    		if ($fileextensions = 1){
    			if (JFile::upload($src, $dest)){
    				// $row->logofilename = $post['id'] . $file['name'];
    				$row->logofilename = $filename;
    
    				require_once JPATH_COMPONENT . DS . 'classes' . DS . 'thumb' . DS .
    				'ThumbLib.inc.php';
    
    				$thumb = PhpThumbFactory::create($dest);
    				$thumb->resize($params->get('pwidth'), $params->get('pheight'));
    				$thumb->save($dest);
    				return $row->logofilename;
    			}else{
    				$msg .= JText::_('Error uploading image');
    				return false;
    			}
    		}else{
    			return false;
    		}
    	}
    }
    
}

?>