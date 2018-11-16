<?php
/**
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011-2016 Open Source Group GmbH www.osg-gmbh.de
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

class seminarmanModelapplication extends JModelLegacy
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
        $this->childviewname = 'application';
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
        if ( count( $cid ) ) {
        	JArrayHelper::toInteger($cid);
        	$cids = implode(',', $cid);
			
        	$query = $this->_db->getQuery(true);
        
        	$query->delete( $this->_db->quoteName( '#__seminarman_'. $this->childviewname ) )
        		  ->where( $this->_db->quoteName( 'id' ) . ' IN (' . $cids . ')' );
        	$this->_db->setQuery( $query );
        
        	if ( !$this->_db->execute() ) {
        		$this->setError( $this->_db->getErrorMsg() );
        		return false;
        	}
        
        	$query = $this->_db->getQuery(true);
        
        	$query->delete( $this->_db->quoteName( '#__seminarman_fields_values' ) )
        		  ->where( $this->_db->quoteName( 'applicationid' ) . ' IN (' . $cids . ')' );
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
        
        if ( count( $cid ) ) {
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
        $user    = JFactory::getUser();
        
        if ( count( $cid ) ) {
        	JArrayHelper::toInteger($cid);
        	$cids = implode( ',', $cid );
        
        	$fields = array(
        			$this->_db->quoteName('published') . ' = ' . (int) $publish
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
        	if (!$this->_db->execute()) {
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

	function getEditableCustomfields($applicationId	= null)
	{
		$db			= $this->getDBO();
		$data		= new stdClass();
		$app = JFactory::getApplication();
		
		if (empty($applicationId)) {
			// only read this session variable while creating a booking
			// this session variable is set after selecting an user over the user modal window by manual booking
			$selected_user = $app->getUserState('com_seminarman.selected.user');
		} else {
		
			$selected_user = 0;
		}
		
		if ($selected_user > 0) {

			$query = $db->getQuery(true);
			$query->select( 'COUNT(*)' );
			$query->from( '`#__seminarman_fields_values`' );
			$query->where( 'applicationid='.(int)$applicationId );
			$query->where( 'user_id='.(int)$selected_user );
			
			$db->setQuery( $query );
			if ($db->loadResult() == 0) {
				// Es gibt noch keine Anmeldung auf den Kurs. Werte für Felder aus #__seminarman_fields_values_users holen
				// (kann aber auch leer sein)
				$query = $db->getQuery(true);
				$query->select( 'field.*' );
				$query->select( 'value.value' );
				$query->from( '`#__seminarman_fields` AS field' );
				$query->join( "LEFT", '`#__seminarman_fields_values_users` AS value ON field.fieldcode=value.fieldcode AND value.user_id=' . $db->Quote($selected_user) );
				$query->where( 'published=1' );
				$query->order( 'field.ordering' );
				$db->setQuery( $query );
			} else {
				$query = $db->getQuery(true);
				$query->select( 'field.*' );
				$query->select( 'value.value' );
				$query->from( '`#__seminarman_fields` AS field' );
				$query->join( "LEFT", '`#__seminarman_fields_values` AS value ON field.id=value.field_id AND value.applicationid=' . $db->Quote($applicationId) );
				$query->where( 'published=1' );
				$query->order( 'field.ordering' );
			}
		} else {
			// Attach custom fields into the user object
			$query = $db->getQuery(true);
			$query->select( 'field.*' );
			$query->select( 'value.value' );
			$query->from( '`#__seminarman_fields` AS field' );
			$query->join( "LEFT", '`#__seminarman_fields_values` AS value ON field.id=value.field_id AND value.applicationid=' . $db->Quote($applicationId) );
			$query->where( 'published=1' );
			$query->order( 'field.ordering' );
		}

		$db->setQuery( $query );

		$result	= $db->loadAssocList();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		$data->fields	= array();
		for($i = 0; $i < count($result); $i++)
		{

			// We know that the groups will definitely be correct in ordering.
			if($result[$i]['type'] == 'group' && $result[$i]['purpose'] == 0)
			{
				$add = True;
				$group	= $result[$i]['name'];

				// Group them up
				if(!isset($data->fields[$group]))
				{
					// Initialize the groups.
					$data->fields[$group]	= array();
				}
			}
			if($result[$i]['type'] == 'group' && $result[$i]['purpose'] != 0)
				$add = False;

			// Re-arrange options to be an array by splitting them into an array
			if(isset($result[$i]['options']) && $result[$i]['options'] != '')
			{
				$options	= $result[$i]['options'];
				$options	= explode("\n", $options);
				foreach ($options as $option ){
					$option = trim($option);
				}

				//array_walk($options, array( 'JString' , 'trim' ) );
				$countOfOptions = count($options);
				for($x = 0; $x < $countOfOptions; $x++){
					$options[$x] = trim($options[$x]);
				}
				$result[$i]['options']	= $options;

				//$result[$i]['options'] = array('yes','no');

			}

			if($result[$i]['type'] != 'group' && isset($add)) {
				if($add)
					$data->fields[$group][]	= $result[$i];
			}
		}
		//$this->_dump($data);
		return $data;
	}

	function saveCustomfields($applicationId, $userId, $fields)
	{
		$db = $this->getDBO();
		
		$query = $db->getQuery(true);
		$query->select( '*' );
		$query->from( '`#__seminarman_fields_values`' );
		$query->where( 'applicationid = '. (int)$applicationId );

		$db->setQuery( $query );
		$dbfields = $db->loadAssocList( 'field_id' );

		$query = $db->getQuery(true);
		$query->select( '*' );
		$query->from( '`#__seminarman_fields_values_users`' );
		$query->where( 'user_id = '. (int)$userId );
			
		$db->setQuery( $query );
		$userfields = $db->loadAssocList( 'fieldcode' );

		foreach ($fields as $id => $value) {
		
			$query = $db->getQuery(true);
			 
			if ( isset( $dbfields[ $id ] ) ) {
		
				$fields = array(
						$db->quoteName('value') . ' = ' . $db->quote( $value ),
				        $db->quoteName('user_id') . ' = ' . (int)$userId
				);
		
				$conditions = array(
						$db->quoteName('applicationid') . ' = ' . (int) $applicationId,
						$db->quoteName('field_id') . ' = ' . (int) $id
				);
		
				$query->update( $db->quoteName( '#__seminarman_fields_values' ) )
				->set( $fields )
				->where( $conditions );
		
			}
			else {
				
				$columns = array( 'applicationid', 'user_id', 'field_id', 'value' );
				$values = array( $applicationId, (int)$userId, $id, $db->quote( $value ) );
		
				$query->insert( $db->quoteName( '#__seminarman_fields_values' ) )
				->columns( $db->quoteName( $columns ) )
				->values( implode( ',', $values ) );
			}
			$db->setQuery( $query );
			$db->execute();

			$query = $db->getQuery(true);
			$query->select( 'fieldcode' );
			$query->from( '`#__seminarman_fields`' );
			$query->where( 'id='.(int)$id );
			$db->setQuery( $query );
			
			$fc = $db->loadAssoc();
			$fieldcode = $fc['fieldcode'];
			
			$query = $db->getQuery(true);

			if ( isset( $userfields[ $fieldcode ] ) ) {
				$fields = array(
						$db->quoteName('value') . ' = ' . $db->quote( $value )
				);
		
				$conditions = array(
						$db->quoteName('fieldcode') . ' = ' . $db->quote( $fieldcode ),
						$db->quoteName('user_id') . ' = ' . (int) $userId
				);
		
				$query->update( $db->quoteName( '#__seminarman_fields_values_users' ) )
				->set( $fields )
				->where( $conditions );
			}
			else {
				
				$columns = array( 'user_id', 'fieldcode', 'value' );
				$values = array( (int)$userId, $db->quote( $fieldcode ), $db->quote( $value ) );
		
				$query->insert( $db->quoteName( '#__seminarman_fields_values_users' ) )
				->columns( $db->quoteName( $columns ) )
				->values( implode( ',', $values ) );
				
			}
			$db->setQuery( $query );
			$db->execute();
		}
	}
	

	/**
	 * Method to change the status of an item
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.0
	 */
	function setstatus($cid, $status){
		$user = JFactory::getUser();

		if ( $cid ){
			$query = $this->_db->getQuery(true);
			
			$fields = array(
					$this->_db->quoteName('status') . ' = ' . (int)$status,
					$this->_db->quoteName('date') . ' = "' . gmdate('Y-m-d H:i:s') . '"'
			);
		
			$conditions = array(
					$this->_db->quoteName('id') . ' = ' . (int)$cid,
					'( checked_out = 0 OR ( checked_out = '. (int) $user->get('id'). ' ) )'
			);
		
			$query->update( $this->_db->quoteName( '#__seminarman_application' ) )
			->set( $fields )
			->where( $conditions );
				
			$this->_db->setQuery( $query );

			if (!$this->_db->execute()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}

		}

		return true;
	}

    function update_protocol($cid, $status){
    	$user = JFactory::getUser();
    	
    	if ( $cid ){
    		$query = $this->_db->getQuery(true);
    		$query->select( 'params' );
    		$query->from( '`#__seminarman_application`' );
    		$query->where( 'id = '. (int)$cid );
    		
			$this->_db->setQuery($query);
			$params_string = $this->_db->loadResult();
			$app_params_obj = new JRegistry();
			$app_params_obj->loadString($params_string);
			$app_params = $app_params_obj->toArray();

            if (!empty($app_params['protocols'])) {
            	$tempArray = json_decode($app_params['protocols'], true);
            	$dataArray = array('date'=>gmdate('Y-m-d H:i:s'), 'user'=>JFactory::getUser()->username, 'status'=>$status);
        	    array_push($tempArray, $dataArray);
        	    $protocols = json_encode($tempArray);
            } else {
            	$tempArray = array();
            	$dataArray = array('date'=>gmdate('Y-m-d H:i:s'), 'user'=>JFactory::getUser()->username, 'status'=>$status);
        	    array_push($tempArray, $dataArray);
        	    $protocols = json_encode($tempArray);
            }
            $jversion = new JVersion();
            $short_version = $jversion->getShortVersion();
            if (version_compare($short_version, "3.0", 'ge')) {
            	$app_params_obj->set('protocols', $protocols);
            } else {
            	$app_params_obj->setValue('protocols', $protocols);
            }
			$params_string = $app_params_obj->toString();
            
            $query = $this->_db->getQuery(true);
            	
            $fields = array(
            		$this->_db->quoteName('params') . " = '" . $this->_db->escape( $params_string ) . "'"
            );
            
            $conditions = array(
            		$this->_db->quoteName('id') . ' = ' . (int)$cid
            );
            
            $query->update( $this->_db->quoteName( '#__seminarman_application' ) )
            ->set( $fields )
            ->where( $conditions );
            
            $this->_db->setQuery( $query );
            
            if (!$this->_db->execute()) {
            	$this->setError($this->_db->getErrorMsg());
            	return false;
            }            
    	}
    	return true;
    }

    function _loadData()
    {
        if (empty($this->_data))
        {    
			$mainframe = JFactory::getApplication();
			$query = $this->_db->getQuery(true);
        	$query->select( 'w.*' );
        	$query->select( 'j.reference_number' );
        	$query->select( 'j.title AS course_title' );
        	$query->select( 'j.price' );
        	$query->select( 'j.currency_price' );
        	$query->select( 'j.price_type' );
        	$query->select( 'j.code' );
        	$query->from('#__seminarman_' . $this->childviewname . ' AS w');
			$query->join('LEFT', '#__seminarman_courses AS j ON j.id = w.course_id');
        	$query->where('w.id = ' . (int) $this->_id);
        	
            $this->_db->setQuery($query);
            $this->_data = $this->_db->loadObject();
            
            return (boolean)$this->_data;
        }
        return true;
    }

    function _initData()
    {

        if (empty($this->_data))
        {
            $application = new stdClass();
            $application->id = 0;
            $application->course_id = null;
            $application->user_id = null;
            $application->first_name = null;
            $application->last_name = null;
            $application->title = null;
            $application->salutation = null;
            $application->email = null;
            $application->phone = null;
            //$application->cover_note = null;
            $application->cv = null;
            $application->cv_name = null;
            $application->comments = null;
            $application->date = null;
            $application->hits = 0;
            $application->published = 0;
            $application->checked_out = 0;
            $application->checked_out_time = 0;
            $application->ordering = 0;
            $application->archived = 0;
            $application->approved = 0;
            $application->params = null;
            $this->_data = $application;
            return (boolean)$this->_data;

        }
        return true;
    }


    function getstatus( $cid ) {
    	if ( $cid ){
			$query = $this->_db->getQuery(true);
			$query->select( 'status' );
        	$query->from('#__seminarman_application');
        	$query->where('id = ' . (int)$cid );
        	
            $this->_db->setQuery($query);
			return $this->_db->loadResult();
    	}
    	return false;
    }
    
    function getEmailData( $id ) {
    	if ( $id ){
    		$query = $this->_db->getQuery(true);
    		$query->select( 'course_id' );
    		$query->from('#__seminarman_application');
    		$query->where('id = ' . $id );
    		 
			$this->_db->setQuery($query);
			$cid = $this->_db->loadResult();
			
			if ( $cid ) {
    			$query = $this->_db->getQuery(true);
    			$query->select( '*' );
    			$query->from('#__seminarman_courses');
    			$query->where('id = ' . $cid );

    			$this->_db->setQuery($query);
    			$emaildata = $this->_db->loadAssocList();
    			$emaildata = $emaildata[0];
    			$emaildata['applicationid'] = $id;
    			return array( $emaildata, $emaildata['email_template'] );
			}
    	}		    	
    	return 0;
    }

    function sendemail($emaildata, $emailTemplate = 0, $attachment = '')
    {    	
    	$mainframe = JFactory::getApplication();
    	$db = JFactory::getDBO();
    
    	if ($emailTemplate != 0) {
    		$emailCond = "id=" . $emailTemplate;
    	} else {
    		$emailCond = "isdefault=1";
    	}
    	
    	$query = $db->getQuery(true);
    	$query->select( '*' );
    	$query->from('#__seminarman_emailtemplate');
    	$query->where('templatefor=0' );
    	$query->where( $emailCond );
    	
    	$db->setQuery($query);
    	$template = $db->loadObject();
    	
    	if ($template) {
    		$config = JFactory::getConfig();
    		$msgSubject = $template->subject;
    		$msgBody = $template->body;
    		$jversion = new JVersion();
    		$short_version = $jversion->getShortVersion();
    		if (version_compare($short_version, "3.0", 'ge')) {
    			$msgSender = array($config->get('mailfrom'), $config->get('fromname'));
    		} else {
    			$msgSender = array($config->getValue('mailfrom'), $config->getValue('fromname'));
    		}
    		$msgRecipient = $template->recipient;
    		$msgRecipientBCC = $template->bcc;
    
    		if (!JHTMLSeminarman::sendEmailToUserApplication($emaildata, $msgSubject, $msgBody, $msgSender, $msgRecipient, NULL, $msgRecipientBCC, $attachment))
    			return false;
    		return true;
    	}
    	return false;
    }
    
    function getCourseData ( $courseid )
    {
    	$query = $this->_db->getQuery(true);
    	$query->select( 'j.id as course_id' );
    	$query->select( 'j.reference_number' );
    	$query->select( 'j.title AS course_title' );
    	$query->select( 'j.price' );
    	$query->select( 'j.price2' );
    	$query->select( 'j.price3' );
    	$query->select( 'j.price4' );
    	$query->select( 'j.price5' );
    	$query->select( 'j.currency_price' );
    	$query->select( 'j.price_type' );
    	$query->select( 'j.code' );
    	$query->select( 'j.vat as price_vat' );
    	$query->from('#__seminarman_courses AS j');
    	$query->where('j.id = ' . (int) $courseid );

    	$this->_db->setQuery($query);
    	$data = $this->_db->loadObject();
    
    	//$this->_data = $this->_db->loadObject();
    	return $data;
    }
    
    function addComments($cid, $comment){
    	$user = JFactory::getUser();
    
    	if ( $cid ){
    		$query = $this->_db->getQuery(true);
    		 
    		$fields = array(
    				$this->_db->quoteName('comments') . ' = CONCAT(comments, "\r\n", "' . $comment . '")'
    		);
    		
    		$conditions = array(
    				$this->_db->quoteName('id') . ' = ' . (int)$cid,
    				'( checked_out = 0 OR ( checked_out = '. (int) $user->get('id'). ' ) )'
    		);
    		
    		$query->update( $this->_db->quoteName( '#__seminarman_application' ) )
    		->set( $fields )
    		->where( $conditions );
    		
    		$this->_db->setQuery( $query );
    		
    		if (!$this->_db->execute()) {
    			$this->setError($this->_db->getErrorMsg());
    			return false;
    		}
    
    	}
    	return true;
    }
    
    function getAttendee() {
    	$params = JComponentHelper::getParams('com_seminarman');
        if (SeminarmanFunctions::isSmanbookingPlgEnabled() && $params->get('advanced_booking')) {
        	$dispatcher=JDispatcher::getInstance();
        	JPluginHelper::importPlugin('seminarman');
        	$html_vars=$dispatcher->trigger('onGetManualBookingAttendee',array());
        	if (isset($html_vars[0]) && !empty($html_vars[0])) {
        		return $html_vars[0];
        	} else {
        		return null;
        	}
        } else {
        	return null;
        }
    }
    
}