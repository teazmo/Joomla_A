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

class SeminarmanModelTemplate extends JModelLegacy
{
    var $_template = null;

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
        $this->_template = null;
    }

    function get($property, $default = null)
    {
        if ($this->_loadTemplate())
        {
            if (isset($this->_template->$property))
            {
                return $this->_template->$property;
            }
        }
        return $default;
    }

    function &getTemplate()
    {
        if ($this->_loadTemplate())
        {
            if (JString::strlen($this->_template->fulltext) > 1)
            {
                $this->_template->text = $this->_template->introtext . "<hr id=\"system-readmore\" />" .
                    $this->_template->fulltext;
            } else
            {
                $this->_template->text = $this->_template->introtext;
            }

            $query = $this->_db->getQuery(true);
            $query->select( 'name' );
            $query->from( '#__users' );
            $query->where( 'id = ' . (int)$this->_template->created_by );
            
            $this->_db->setQuery($query);
            $this->_template->creator = $this->_db->loadResult();

            if ($this->_template->created_by == $this->_template->modified_by)
            {
                $this->_template->modifier = $this->_template->creator;
            } else
            {
            	$query = $this->_db->getQuery(true);
            	$query->select( 'name' );
            	$query->from( '#__users' );
            	$query->where( 'id = ' . (int)$this->_template->modified_by );
            	
                $this->_db->setQuery($query);
                $this->_template->modifier = $this->_db->loadResult();
            }

        } else
            $this->_initTemplate();

        return $this->_template;
    }


    function _loadTemplate()
    {

        if (empty($this->_template))
        {
        	$query = $this->_db->getQuery(true);
        	$query->select( '*' );
            $query->from( '#__seminarman_templates' );
            $query->where( 'id = ' . $this->_id );
            
            $this->_db->setQuery($query);
            $this->_template = $this->_db->loadObject();

            return (boolean)$this->_template;
        }
        return true;
    }

    function _initTemplate()
    {

        if (empty($this->_template))
        {
        	$params = JComponentHelper::getParams( 'com_seminarman' );
            $createdate = JFactory::getDate();
            $nullDate = $this->_db->getNullDate();

            $template = new stdClass();
            $template->id = 0;
            $template->cid[] = 0;
            $template->name = null;
            $template->title = null;
            $template->code = null;
            $template->text = null;
            $template->reference_number = null;
            $template->version = 0;
            $template->meta_description = null;
            $template->meta_keywords = null;
            $template->created = $nullDate;
            $template->created_by = null;
            $template->created_by_alias = '';
            $template->modified = $nullDate;
            $template->modified_by = null;
            $template->metadata = null;
            $template->state = 0;
            $template->attribs = null;
            $template->job_title = null;
            $template->price_type = null;
            $template->id_experience_level = null;
            $template->job_experience = null;
            $template->id_group = null;
            $template->url = null;
            $template->price = 0;
            $template->vat = $params->get('vat');
            $template->currency_price = null;
            $template->capacity = null;
            $template->location = null;
            $createdate = JFactory::getDate();
        	$template->capacity = null;
            $this->_template = $template;
            $template->email_template = 0;
            $template->invoice_template = 0;
            $template->attlst_template = 0;
            $template->start_date = '0000-00-00';
            $template->finish_date = '0000-00-00';
            $template->certificate_text = null;
            $template->price2 = null;
            $template->price3 = null;
            $template->price4 = null;
            $template->price5 = null;
            $template->min_attend = 0;
            $template->theme_points = 0;
            return (boolean)$this->_template;
        }
        return true;
    }

    function checkin()
    {
        if ($this->_id)
        {
            $template = JTable::getInstance('seminarman_templates', '');
            return $template->checkin($this->_id);
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

            $template = JTable::getInstance('seminarman_templates', '');
            return $template->checkout($uid, $this->_id);
        }
        return false;
    }

    function isCheckedOut($uid = 0)
    {
        if ($this->_loadTemplate())
        {
            if ($uid)
            {
                return ($this->_template->checked_out && $this->_template->checked_out != $uid);
            } else
            {
                return $this->_template->checked_out;
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
        
    	require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS . 'helpers' . DS . 'seminarman.php');

        JRequest::checkToken() or jexit('Invalid Token');

        $template = $this->getTable('seminarman_templates', '');
        $user = JFactory::getUser();

        $details = JRequest::getVar('details', array(), 'post', 'array');
        $tags = JRequest::getVar('tag', array(), 'post', 'array');
        $cats = JRequest::getVar('catid', array(), 'post', 'array');
        $files = JRequest::getVar('fid', array(), 'post', 'array');
        $files = array_filter($files);

        if (!is_array($cats) || count($cats) < 1)
        {
            $this->setError('SELECT CATEGORY');
            return false;
        }

        if (!$template->bind($data))
        {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        $template->bind($details);

        $template->id = (int)$template->id;
        $this->_id = $template->id;

        $nullDate = $this->_db->getNullDate();

        if ($template->id)
        {
            $template->modified = gmdate('Y-m-d H:i:s');
            $template->modified_by = $user->get('id');
        } else
        {
            $template->modified = $nullDate;
            $template->modified_by = '';
            
            $template->created = gmdate('Y-m-d H:i:s');
            $template->created_by = $user->get('id');
        }
        
        // $template->start_date = JHTMLSeminarman::localDate2DbDate($template->start_date);
        // $template->finish_date = JHTMLSeminarman::localDate2DbDate($template->finish_date);
        
        // add 12:00:00 so that the dates stay the same in db (utc) as input (local), the time will get lost after saving
        $template_begin = SeminarmanFunctions::formatDateToSQLParts($template->start_date, "12:00:00");
        $template_finish = SeminarmanFunctions::formatDateToSQLParts($template->finish_date, "12:00:00");        
        $template->start_date = $template_begin[0];
        $template->finish_date = $template_finish[0];
        
        $template->state = JRequest::getVar('state', 0, '', 'int');

        $template->vat = str_replace(",", ".", $template->vat);
        
        $params = JRequest::getVar('params', null, 'post', 'array');
        
        if (is_array($params))
        {
        	$txt = array();
        	foreach ($params as $k => $v)
        	{
        		$txt[] = "$k=$v";
        	}
        	$template->attribs = implode("\n", $txt);
        }
        
        $metadata = JRequest::getVar('meta', null, 'post', 'array');
        if (is_array($params))
        {
        	$txt = array();
        	foreach ($metadata as $k => $v)
        	{
        		if ($k == 'description')
        		{
        			$template->meta_description = $v;
        		} elseif ($k == 'keywords')
        		{
        			$template->meta_keywords = $v;
        		} else
        		{
        			$txt[] = "$k=$v";
        		}
        	}
        	$template->metadata = implode("\n", $txt);
        }
        
        seminarman_html::saveContentPrep($template);
        
        $template->version++;
        
        if (!$template->store())
        {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        $this->_template = &$template;

        $query = $this->_db->getQuery(true);
        $query->delete( $this->_db->quoteName( '#__seminarman_tags_template_relations' ) )
        	  ->where( $this->_db->quoteName( 'templateid' ) . ' = '. $template->id );
        $this->_db->setQuery( $query );
        $this->_db->execute();

        foreach ($tags as $tag)
        {
        	$query = $this->_db->getQuery(true);
            $columns = array( 'tid', 'templateid' );
            $values = array( $tag, $template->id );
            
            $query->insert( $this->_db->quoteName( '#__seminarman_tags_template_relations' ) )
            	  ->columns( $this->_db->quoteName( $columns ) )
            	  ->values( implode( ',', $values ) );
            
        	$this->_db->setQuery( $query );
        	$this->_db->execute();
        }

        $query = $this->_db->getQuery(true);
        $query->delete( $this->_db->quoteName( '#__seminarman_cats_template_relations' ) )
              ->where( $this->_db->quoteName( 'templateid' ) . ' = '. $template->id );
        $this->_db->setQuery( $query );
        $this->_db->execute();

        foreach ($cats as $cat)
        {
            $query = $this->_db->getQuery(true);
            $columns = array( 'catid', 'templateid' );
            $values = array( $cat, $template->id );
            
            $query->insert( $this->_db->quoteName( '#__seminarman_cats_template_relations' ) )
            ->columns( $this->_db->quoteName( $columns ) )
            ->values( implode( ',', $values ) );
            
            $this->_db->setQuery( $query );
            $this->_db->execute();
        }

        $query = $this->_db->getQuery(true);
        $query->delete( $this->_db->quoteName( '#__seminarman_files_template_relations' ) )
        	  ->where( $this->_db->quoteName( 'templateid' ) . ' = '. $template->id );
        $this->_db->setQuery( $query );
        $this->_db->execute();
        
        foreach ($files as $file)
        {
        	$query = $this->_db->getQuery(true);
        	$columns = array( 'fileid', 'templateid' );
        	$values = array( $file, $template->id );
        	
        	$query->insert( $this->_db->quoteName( '#__seminarman_files_template_relations' ) )
        	->columns( $this->_db->quoteName( $columns ) )
        	->values( implode( ',', $values ) );
        	
        	$this->_db->setQuery( $query );
        	$this->_db->execute();
        }
        
        // Tutoren hinzufügen
        $tutor_id = JRequest::getInt('tutor_id', 0, 'post');
        
        if ($tutor_id != 0) {
        	$tutor_prio = JRequest::getInt('tutor_prio', 0, 'post');
        	
        	$query = $this->_db->getQuery(true);
        	$query->select( '*' );
        	$query->from( '#__seminarman_tutor_templates_relations' );
        	$query->where( $this->_db->quoteName( 'tutorid' ) .' = '. $tutor_id );
        	$query->where( $this->_db->quoteName( 'templateid' ) .' = '. $template->id );
        	
        	$this->_db->setQuery($query);
        	$isset = $this->_db->loadObject();

        	$query = $this->_db->getQuery(true);
        	if ( !isset( $isset ) ) {
    	    	$columns = array( 'tutorid', 'templateid', 'priority' );
        		$values = array( $tutor_id, $template->id, $tutor_prio );
        	
        		$query->insert( $this->_db->quoteName( '#__seminarman_tutor_templates_relations' ) )
        		  	  ->columns( $this->_db->quoteName( $columns ) )
        			  ->values( implode( ',', $values ) );
        	}
        	else {
        		$fields = array(
        				$this->_db->quoteName('priority') . ' = ' . $this->_db->quote( $tutor_prio )
        		);
        		
        		$conditions = array(
        				$this->_db->quoteName('tutorid') . ' = ' . (int) $tutor_id,
        				$this->_db->quoteName('templateid') . ' = ' . (int) $template->id
        		);
        		
        		$query->update( $this->_db->quoteName( '#__seminarman_tutor_templates_relations' ) )
        		->set( $fields )
        		->where( $conditions );
        	}
        	$this->_db->setQuery( $query );
        	$this->_db->execute();
        }
        
        // Tutoren löschen
        $tutors_remove = JRequest::getVar('tutors_remove', array(), 'post', 'array');
        
        foreach ($tutors_remove as $tutor)
        {
        	$query = $this->_db->getQuery(true);
        	$query->delete( $this->_db->quoteName( '#__seminarman_tutor_templates_relations' ) )
        		  ->where( $this->_db->quoteName( 'tutorid' ) . ' = '. (int)$tutor )
        		  ->where( $this->_db->quoteName( 'templateid' ) . ' = '. $template->id );
        	$this->_db->setQuery( $query );
        	$this->_db->execute();
        }
        
        if ($data['price2']=='') {
        	$query = $this->_db->getQuery(true);
        	
        	$fields = array(
        			$this->_db->quoteName('price2') . ' = NULL'
        	);
        	
        	$conditions = array(
        			$this->_db->quoteName('id') . ' = ' . (int) $template->id
        	);
        	
        	$query->update( $this->_db->quoteName( '#__seminarman_templates' ) )
        	->set( $fields )
        	->where( $conditions );
        	
        	$this->_db->setQuery($query);
        	$this->_db->execute();
        }
        
        if ($data['price3']=='') {
        	$query = $this->_db->getQuery(true);
        	 
        	$fields = array(
        			$this->_db->quoteName('price3') . ' = NULL'
        	);
        	 
        	$conditions = array(
        			$this->_db->quoteName('id') . ' = ' . (int) $template->id
        	);
        	 
        	$query->update( $this->_db->quoteName( '#__seminarman_templates' ) )
        	->set( $fields )
        	->where( $conditions );
        	 
        	$this->_db->setQuery($query);
        	$this->_db->execute();
        }
        
        if ($data['price4']=='') {
        	$query = $this->_db->getQuery(true);
        	 
        	$fields = array(
        			$this->_db->quoteName('price4') . ' = NULL'
        	);
        	 
        	$conditions = array(
        			$this->_db->quoteName('id') . ' = ' . (int) $template->id
        	);
        	 
        	$query->update( $this->_db->quoteName( '#__seminarman_templates' ) )
        	->set( $fields )
        	->where( $conditions );
        	 
        	$this->_db->setQuery($query);
        	$this->_db->execute();
        }
        
        if ($data['price5']=='') {
        	$query = $this->_db->getQuery(true);
        	 
        	$fields = array(
        			$this->_db->quoteName('price5') . ' = NULL'
        	);
        	 
        	$conditions = array(
        			$this->_db->quoteName('id') . ' = ' . (int) $template->id
        	);
        	 
        	$query->update( $this->_db->quoteName( '#__seminarman_templates' ) )
        	->set( $fields )
        	->where( $conditions );
        	 
        	$this->_db->setQuery($query);
        	$this->_db->execute();
        }

        return true;
    }

    function gettags()
    {
        $query = $this->_db->getQuery(true);
        $query->select( '*' );
        $query->from( '#__seminarman_tags' );
        $query->order( 'name' );
        
        $this->_db->setQuery($query);
        $tags = $this->_db->loadObjectlist();
        return $tags;
    }

    function getusedtags($id)
    {
        $query = $this->_db->getQuery(true);
        $query->select( 'DISTINCT tid' );
        $query->from( '#__seminarman_tags_template_relations' );
        $query->where( 'templateid = '. (int)$this->_id );
            		
        $this->_db->setQuery($query);
        $jversion = new JVersion();
		$short_version = $jversion->getShortVersion();
		if (version_compare($short_version, "3.0", 'ge')) {
        	$used = $this->_db->loadColumn();
		} else {
			$used = $this->_db->loadResultArray();
		}
        return $used;
    }

 
    function getCatsselected()
    {
        $query = $this->_db->getQuery(true);
        $query->select( 'DISTINCT catid' );
        $query->from( '#__seminarman_cats_template_relations' );
        $query->where( 'templateid = '. (int)$this->_id );
            
        $this->_db->setQuery($query);
        $jversion = new JVersion();
		$short_version = $jversion->getShortVersion();
		if (version_compare($short_version, "3.0", 'ge')) {
        	$used = $this->_db->loadColumn();
		} else {
			$used = $this->_db->loadResultArray();
		}
        return $used;
    }
    
    
    function getFiles()
    {
	    $query = $this->_db->getQuery(true);
	    $query->select( 'DISTINCT rel.fileid' );
	    $query->select( 'f.filename' );
	    $query->from( '#__seminarman_files AS f' );
	    $query->join( "LEFT", '#__seminarman_files_template_relations AS rel ON rel.fileid = f.id' );
	    $query->where( 'rel.templateid = '. (int)$this->_id );
	    
	    $this->_db->setQuery($query);
	    $files = $this->_db->loadObjectList();
	    return $files;
    }

    /**
     * Retrieves e-mail templates for booking
     *
     * @return array of objects with templates
     */
    function getEmailTemplates()
    {
        $query = $this->_db->getQuery(true);
        $query->select( 'id' );
        $query->select( 'title' );
        $query->from( '#__seminarman_emailtemplate' );
        $query->where( 'templatefor=0' );
        
        $this->_db->setQuery($query);
        return $this->_db->loadObjectList();
    }
    
    function getTutors()
    {
    	$query = $this->_db->getQuery(true);
    	$query->select( 'DISTINCT t.id' );
    	$query->select( 't.title' );
    	$query->select( 'rel.priority' );
    	$query->from( '#__seminarman_tutor AS t' );
    	$query->join( "LEFT", '#__seminarman_tutor_templates_relations AS rel ON t.id = rel.tutorid' );
    	$query->where( 'rel.templateid = '. (int)$this->_id );
    	$query->order( 'rel.priority DESC, t.title' );
    	
    	$this->_db->setQuery($query);
    	return $this->_db->loadObjectList();
    }
}

?>