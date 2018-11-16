<?php

/**
 *
 * @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
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
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class seminarmanModelSalesProspect extends JModelLegacy
{
	var $_id = null;

	var $_data = null;

	function __construct()
	{
		parent::__construct();

		$id = JRequest::getVar('id', 0, '', 'int');
		$this->_template_id = JRequest::getVar('template_id', 0, '', 'int');
		$this->setId((int)$id);
	}

	function setId($id)
	{
		$this->_id = $id;
		$this->_data = null;
	}
	
	function getData()
	{
		$query = $this->_db->getQuery(true);
		$query->select( 'w.*' );
		$query->select( 'j.reference_number' );
		$query->select( 'j.title AS course_title' );
		$query->select( 'j.price' );
		$query->select( 'j.currency_price' );
		$query->select( 'j.price_type' );
		$query->select( 'j.code' );
		$query->from( '`#__seminarman_salesprospect` AS w' );
		$query->join( "LEFT", '`#__seminarman_templates` AS j ON j.id = w.template_id');
		$query->where( 'w.id = ' . (int)$this->_id );
		
		$this->_db->setQuery( $query );
		return $this->_db->loadObject();
	}
	
	function store($data)
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR . DS .'components'. DS .'com_seminarman'. DS .'tables');

		$row = JTable::getInstance('SalesProspect', 'Table');
		$db = $this->getDBO();

		if (!$row->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		$row->date = gmdate('Y-m-d H:i:s');

		if (!$row->id) {
			$where = 'template_id = ' . (int)$row->template_id;
			$row->ordering = $row->getNextOrder($where);
		}

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
		
		
		// Save voting results if available
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('seminarman');
		
		$results = $dispatcher->trigger('onSaveSPWithVoting', array($row));
		
		return ($row->id);
	}


	function saveCustomfields($requestid, $userId, $fields)
	{
		$db = $this->getDBO();
		
		$query = $db->getQuery(true);
		$query->select( '*' );
		$query->from( '`#__seminarman_fields_values_salesprospect`' );
		$query->where( 'requestid = '. (int)$requestid );
			
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
						$db->quoteName('requestid') . ' = ' . (int) $requestid,
						$db->quoteName('field_id') . ' = ' . (int) $id
				);
		
				$query->update( $db->quoteName( '#__seminarman_fields_values_salesprospect' ) )
				->set( $fields )
				->where( $conditions );
		
			}
			else {
		
				$columns = array( 'requestid', 'user_id', 'field_id', 'value' );
				$values = array( $requestid, $userId, $id, $db->quote( $value ) );
		
				$query->insert( $db->quoteName( '#__seminarman_fields_values_salesprospect' ) )
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
	
	function sendemail($emaildata) {
		
		$config	= JFactory::getConfig();
		$params = JComponentHelper::getParams('com_seminarman');
		$msgRecipient = $params->get('component_email');
		
		if (empty($msgRecipient))
			return False;
		
		$msgSubject = JText::sprintf('COM_SEMINARMAN_EMAIL_SALESPROSPECT_SUBJECT');
		$msgBody = JText::sprintf('COM_SEMINARMAN_EMAIL_SALESPROSPECT_BODY',
				$this->cEscape($emaildata['first_name']),
				$this->cEscape($emaildata['last_name']),
				$this->cEscape($emaildata['email']),
				$this->cEscape($emaildata['course_title']),
				$this->cEscape($emaildata['code'])
			);
		
		$message = JFactory::getMailer();
		$message->addRecipient(array_filter(explode(",", str_replace(" ","", trim($msgRecipient)))));
		$message->setSubject(html_entity_decode($msgSubject, ENT_QUOTES));
		$message->setBody($msgBody);
		$message->setSender(array($config->get('mailfrom'), $config->get('fromname')));
		$message->IsHTML(false);
		
		return $message->send();
		
	}
	
	function cEscape($var, $function='htmlspecialchars') {
		if (in_array($function, array('htmlspecialchars', 'htmlentities'))) {
			return call_user_func($function, $var, ENT_COMPAT, 'UTF-8');
		}
		return call_user_func($function, $var);
	}
	
}