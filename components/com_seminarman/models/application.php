<?php

/**
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011-2014 Open Source Group GmbH www.osg-gmbh.de
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

require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS . 'helpers' . DS . 'seminarman.php');

class seminarmanModelapplication extends JModelLegacy
{
    var $_id = null;

    var $_data = null;

    function __construct()
    {
        parent::__construct();

        $id = JRequest::getVar('id', 0, '', 'int');
        $this->_course_id = JRequest::getVar('course_id', 0, '', 'int');
        $this->setId((int)$id);
    }

    function setId($id)
    {
        $this->_id = $id;
        $this->_data = null;
    }

    function &getData()
    {
        if ($this->_loadData()) {
            $user = JFactory::getUser();

            if (!$this->_data->published) {
                JError::raiseError(404, JText::_("Resource Not Found"));
                return false;
            }

            if (!$this->_data->cat_pub) {
                JError::raiseError(404, JText::_("Resource Not Found"));
                return;
            }

            if ($this->_data->cat_access > $user->get('aid', 0)) {
                JError::raiseError(403, JText::_('ALERTNOTAUTH'));
                return;
            }
        } else
            $this->_initData();

        return $this->_data;
    }
    
    function hit()
    {
        $mainframe = JFactory::getApplication();

        if ($this->_id) {
            $booking = $this->getTable();
            $booking->hit($this->_id);
            return true;
        }
        return false;
    }

    function isCheckedOut($uid = 0)
    {
        if ($this->_loadData()) {
            if ($uid) {
                return ($this->_data->checked_out && $this->_data->checked_out != $uid);
            } else {
                return $this->_data->checked_out;
            }
        }
    }

    function checkin()
    {
        if ($this->_id) {
            $booking = $this->getTable();
            if (!$booking->checkin($this->_id)) {
                $this->setError($this->_db->getErrorMsg());
                return false;
            }
            return true;
        }
        return false;
    }

    function checkout($uid = null)
    {
        if ($this->_id) {
            if (is_null($uid)) {
                $user = JFactory::getUser();
                $uid = $user->get('id');
            }

            $booking = $this->getTable();
            if (!$booking->checkout($uid, $this->_id)) {
                $this->setError($this->_db->getErrorMsg());
                return false;
            }

            return true;
        }
        return false;
    }

    function store($data)
    {
    	$db = $this->getDBO();
    	 
        JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_seminarman'.DS.'tables');
        $row = JTable::getInstance('application', 'Table');
        
        $tempArray = array();
        $dataArray = array('date'=>date('Y-m-d H:i:s'), 'user'=>JText::_('COM_SEMINARMAN_ONLINE_BOOKING'), 'status'=>0);
        array_push($tempArray, $dataArray);
        $data['params']['protocols'] = json_encode($tempArray);
        
        if (isset($_POST['booking_email_cc'])) $data['params']['booking_email_cc'] = $_POST['booking_email_cc'];
        
        if (!$row->bind($data)) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        $row->date = gmdate('Y-m-d H:i:s');
        
        if (!$row->id) {
        	$is_insert = 1;
            $where = 'course_id = ' . (int)$row->course_id;
            $row->ordering = $row->getNextOrder($where);
        }
        else
        	$is_insert = 0;

        if (!$row->check()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }
        
        if (!$row->store()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }
        
        $user = JFactory::getUser();
        if ($user->guest)
        	$uid = $data['user_id'];
        else
        	$uid = $user->id;
        
        $title = $db->quote($data['title']);
        $salutation = $db->quote($data['salutation']);
        
        $query = $db->getQuery(true);
        $query->select( '*' );
        $query->from( '`#__seminarman_fields_values_users_static`' );
        $query->where( 'user_id = '. (int)$uid );
        	
        $db->setQuery( $query );
        $userObj = $db->loadObject();
        
        if ( isset( $userObj ) ) {
        
        	$fields = array(
        			$db->quoteName('salutation') . ' = ' . $salutation,
        			$db->quoteName('title') . ' = ' . $title
        	);
        		
        	$conditions = array(
        			$db->quoteName('user_id') . ' = ' . (int) $uid
        	);
        		
        	$query->update( $db->quoteName( '#__seminarman_fields_values_users_static' ) )
        	->set( $fields )
        	->where( $conditions );
        }
        else {
        	$columns = array( 'user_id', 'salutation', 'title' );
        	$values = array( $uid, $salutation, $title );
        
        	$query->insert( $db->quoteName( '#__seminarman_fields_values_users_static' ) )
        	->columns( $db->quoteName( $columns ) )
        	->values( implode( ',', $values ) );
        }
        $db->setQuery( $query );
        $db->execute();
        
        return ($row->id);
    }
    
    function uploadfile()
    {
        $mainframe = JFactory::getApplication();
        $params = JComponentHelper::getParams('com_seminarman');

        $file = JRequest::getVar('cv', null, 'files', 'array');

        if ($file['size'] > 0) {
            jimport('joomla.filesystem.file');

            $path = COM_SEMINARMAN_CVFILEPATH . DS;

            $filename = seminarman_upload::sanitize($path, $file['name']);

            $filepath = JPath::clean($path . strtolower($filename));

            $src = $file['tmp_name'];

            $dest = $filepath;

            if (!seminarman_upload::check($file, $err)) {
                if ($format == 'json') {
                    jimport('joomla.error.log');
                    $log = JLog::getInstance('com_seminarman.error.php');
                    $log->addEntry(array('comment' => 'Invalid: ' . $filepath . ': ' . $err));
                    header('HTTP/1.0 415 Unsupported Media Type');
                    die('Error. Unsupported Media Type!');
                } else {
                    JError::raiseNotice(100, JText::_($err));

                    if ($return) {
                        $mainframe->redirect(base64_decode($return));
                    }
                    return;
                }
            }

            if (JFile::upload($src, $dest)) {
                return $filename;
            } else {
                $msg .= "File upload failed.";
                return false;
            }
        }
    }

    function copyfile($file)
    {
        $mainframe = JFactory::getApplication();

        jimport('joomla.filesystem.file');
        $filename = JFile::makeSafe($file);

        $path = COM_SEMINARMAN_CVFILEPATH . DS;

        $filename_new = $this->updateFilename($filename);
        $filename_new = seminarman_upload::sanitize($path, $filename_new);

        $src = COM_SEMINARMAN_UPLOADEDCVFILEPATH . DS . $filename;
        $dst = COM_SEMINARMAN_CVFILEPATH . DS . $filename_new;
        if (JFile::copy($src, $dst)) {
            return $filename_new;
        }
        return false;
    }
    function updateFilename($filename)
    {
        $mainframe = JFactory::getApplication();

        jimport('joomla.filesystem.file');

        $filename = preg_replace("/^[.]*/", '', $filename);
        $filename = preg_replace("/[.]*$/", '', $filename);

        $lastdotpos = strrpos($filename, '.');

        $chars = '[^0-9a-zA-Z()_-]';
        $filename = strtolower(preg_replace("/$chars/", '_', $filename));

        $beforedot = substr($filename, 0, $lastdotpos);
        $afterdot = substr($filename, $lastdotpos + 1);

        $filename_new = substr($beforedot, 0, (strlen($beforedot) - 11)) . '.' . $afterdot;
        return $filename_new;
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
        $query->from( '`#__seminarman_emailtemplate`' );
        $query->where( 'templatefor=0' );
        $query->where( $emailCond );
         
        $db->setQuery( $query );
        
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
            
            $msgRecipientCC = (isset($_POST['booking_email_cc']))?$_POST['booking_email_cc']:'';
                    
            if (!JHTMLSeminarman::sendEmailToUserApplication($emaildata, $msgSubject, $msgBody, $msgSender, $msgRecipient, $msgRecipientCC, $msgRecipientBCC, $attachment))
            	return false;
            return true;
        }
        return false;
    }
    
    function sendemailWaitingList($emaildata, $emailTemplate = 0, $attachment = '')
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
		$query->from( '`#__seminarman_emailtemplate`' );
		$query->where( 'templatefor=2' );
		$query->where( $emailCond );
		 
		$db->setQuery( $query );
		
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
			
			$msgRecipientCC = (isset($_POST['booking_email_cc']))?$_POST['booking_email_cc']:'';
            
			if (!JHTMLSeminarman::sendEmailToUserApplication($emaildata, $msgSubject, $msgBody, $msgSender, $msgRecipient, $msgRecipientCC, $msgRecipientBCC, $attachment))
				return false;
			return true;
		}
		return false;
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
        				$db->quoteName('user_id') . ' = ' . $db->quote( $userId )
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
        		$values = array( $applicationId, $userId, $id, $db->quote( $value ) );
        
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
        		$values = array( $userId, $db->quote( $fieldcode ), $db->quote( $value ) );
        			
        		$query->insert( $db->quoteName( '#__seminarman_fields_values_users' ) )
        		->columns( $db->quoteName( $columns ) )
        		->values( implode( ',', $values ) );
        			
        	}
        	$db->setQuery( $query );
        	$db->execute();
        }
    }

    function getAdminEmails()
    {
        $emails = '';
        $db = $this->getDBO();

        $query = $db->getQuery(true);
        $query->select( $db->quoteName('email') );
        $query->from( '`#__users`' );
        $query->where( $db->quoteName('gid') . '=' . $db->quote(24) . ' OR ' . $db->quoteName('gid') . '=' . $db->Quote(25) );
        	
        $db->setQuery( $query );
        $emails = $db->loadResultArray();

        return $emails;
    }

    function getCurrentBookings()
    {
        $mainframe = JFactory::getApplication();
        $params = $mainframe->getParams();
        if ($params->get('current_capacity') == 1) {
            $statusTopLimit = 3;
            $statusBottomLimit = -1;
        }
        if ($params->get('current_capacity') == 2) {
            $statusTopLimit = 3;
            $statusBottomLimit = 0;
        }

        $db = JFactory::getDBO();
        
        $stat = '(( b.status <' . $statusTopLimit = 3 . ' AND b.status >' . $statusBottomLimit.')';
        if ( $params->get('waitinglist_active') ) {
        	$stat .= 'OR ( b.status = 5 )';
        }
       	$stat .= ')';

        $query = $db->getQuery(true);
        $query->select( 'SUM(b.attendees)' );
        $query->from( '`#__seminarman_application` AS b' );
        $query->where( 'b.published = 1' );
        $query->where( 'b.course_id = ' . $this->_course_id );
        $query->where( $stat );
         
        $db->setQuery( $query );
        $current_bookings = $db->loadResult();
        return $current_bookings;
    }
    
    function getCurrentBookingsForUser($user_id = 0)
    {
        $mainframe = JFactory::getApplication();
        $params = $mainframe->getParams();
    	if ($user_id == 0)
    		return 0;
    	
    	$db = JFactory::getDBO();
    	
    	$stat = '(( status < 3 )';
    	if ( $params->get('waitinglist_active') ) {
    		$stat .= 'OR ( status = 4 ) OR ( status = 5 )';
    	}
    	$stat .= ')';
    	
    	$query = $db->getQuery(true);
    	$query->select( 'COUNT(id)' );
    	$query->from( '`#__seminarman_application`' );
    	$query->where( 'user_id = '. (int)$user_id );
    	$query->where( 'course_id = ' . $this->_course_id );
    	$query->where( 'published = 1' );
    	$query->where( $stat );
    	
    	$db->setQuery( $query );
    	
    	return (int)$db->loadResult();
    }


	function sendRegistrationEmail($user, $password)
	{
		$config	= JFactory::getConfig();
		$params = JComponentHelper::getParams('com_users');
		

		$subject = JText::sprintf('COM_SEMINARMAN_EMAIL_ACCOUNT_DETAILS',
			$user->name,
			$config->get('sitename')
		);
		
		if ($params->get('useractivation') == 2)
			$body = JText::sprintf('COM_SEMINARMAN_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY', 
				$user->name,
				$config->get('sitename'),
				JUri::base().'index.php?option=com_users&task=registration.activate&token='.$user->activation,
				JUri::base(),
				 $user->username, 
				 $password
			);
		else if ($params->get('useractivation') == 1)
			$body = JText::sprintf('COM_SEMINARMAN_EMAIL_REGISTERED_WITH_ACTIVATION_BODY',
				$user->name,
				$config->get('sitename'),
				JUri::base().'index.php?option=com_users&task=registration.activate&token='.$user->activation,
				JUri::base(),
				$user->username,
				$password
			);
		else
			$body = JText::sprintf('COM_SEMINARMAN_EMAIL_REGISTERED_BODY',
				$user->name,
				$config->get('sitename'),
				JUri::base()
			);
		
		$message = JFactory::getMailer();
		$message->addRecipient($user->email);
		$message->addBcc($config->get('mailfrom'));
		$message->setSubject(html_entity_decode($subject, ENT_QUOTES));
		$message->setBody($body);
		$message->setSender(array($config->get('mailfrom'), $config->get('fromname')));
		$message->IsHTML(false);
		
		return $message->send();
	}
    
    function getInvoiceNumber()
    {
    	$db = JFactory::getDBO();
    	$params = JComponentHelper::getParams('com_seminarman');

    	$db->setQuery('LOCK TABLES `#__seminarman_invoice_number` WRITE');
    	$db->query();

    	$query = $db->getQuery(true);
    	 
    	$fields = array( $db->quoteName( 'number' ). ' = GREATEST(number+1,'.(int)$params->get('invoice_number_start').')' );
    	$query->update( $db->quoteName( '#__seminarman_invoice_number' ) )->set( $fields );
    	
    	$db->setQuery($query);
    	$db->execute();    	
    	
    	$query = $db->getQuery(true);
    	$query->select( 'number' );
    	$query->from( '`#__seminarman_invoice_number`' );
    	 
    	$db->setQuery( $query );
    	$next = $db->loadResult();
    	
    	$query = $db->getQuery(true);
    	$db->setQuery('UNLOCK TABLES');
    	$db->query();
    	
    	return $next;
    }
    
    function getEmailData( $id ) {
    	
    	$db = JFactory::getDBO();
    	
    	
    	
    	return $emaildata;
    }
}