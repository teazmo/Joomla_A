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

require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS .
		'helpers' . DS . 'seminarman.php');

class SeminarmanModelSeminarman extends JModelLegacy
{
    function __construct()
    {
        parent::__construct();
    }

    function getLatestJobs()
    {
        $query = $this->_db->getQuery(true);
        $query->select( 'id' );
        $query->select( 'title' );
        $query->from( '#__seminarman_courses' );
        $query->where( 'state = 1' );
        if(!(JHTMLSeminarman::UserIsCourseManager())){
        	$query->where( "FIND_IN_SET(" . JHTMLSeminarman::getUserTutorID() . ", replace(replace(tutor_id, '[', ''), ']', ''))" );
        }
        $query->order( 'created DESC' );
        $query->setLimit( 5 );
        
        $this->_db->setQuery($query);
        $genstats = $this->_db->loadObjectList();

        return $genstats;
    }

    function getLatestApplications()
    {
        $query = $this->_db->getQuery(true);
        $query->select( 'a.id' );
        $query->select( 'a.first_name' );
        $query->select( 'a.last_name' );
        $query->select( 'i.title' );
        $query->select( 'a.date' );
        $query->from( '#__seminarman_application AS a' );
        $query->join( "LEFT", '#__seminarman_courses AS i ON i.id = a.course_id' );
        $query->where( 'a.published = 1' );
        if(!(JHTMLSeminarman::UserIsCourseManager())){
        	$query->where( "FIND_IN_SET(" . JHTMLSeminarman::getUserTutorID() . ", replace(replace(i.tutor_id, '[', ''), ']', ''))" );
        }
        $query->order( 'a.id DESC' );
        $query->setLimit( 5 );
        
        $this->_db->setQuery($query);
        $genstats = $this->_db->loadObjectList();
        
        return $genstats;
    }

}

?>