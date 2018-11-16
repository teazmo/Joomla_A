<?php
/**
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
 **/

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');
require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS .'helpers' . DS . 'seminarman.php');

class seminarmanControllersalesprospect extends seminarmanController
{

	function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('add', 'display');
		$this->registerTask('edit', 'display');
		$this->registerTask('apply', 'save');
		$this->registerTask('notify', 'notify');
		$this->childviewname = 'salesprospect';
		$this->parentviewname = 'salesprospects';
		$this->_errmsg = "";
	}

	function display($cachable = false, $urlparams = false)
	{
		switch ($this->getTask())
		{
			case 'add':
				{
					JRequest::setVar('hidemainmenu', 1);
					JRequest::setVar('layout', 'form');
					JRequest::setVar('view', $this->childviewname);
					JRequest::setVar('edit', false);

					$model = $this->getModel($this->childviewname);
					$model->checkout();
				}

				break;
			case 'edit':
				{
					JRequest::setVar('hidemainmenu', 1);
					JRequest::setVar('layout', 'form');
					JRequest::setVar('view', $this->childviewname);
					JRequest::setVar('edit', true);

					$model = $this->getModel($this->childviewname);
					$model->checkout();
				}

				break;
		}

		parent::display();
	}


	function save()
	{

		JRequest::checkToken() or jexit('Invalid Token');
		$post = JRequest::get('post');
		$cid = JRequest::getVar('cid', array(0), 'post', 'array');
		$post['id'] = (int)$cid[0];
		$model = $this->getModel($this->childviewname);
		$userId		= JRequest::getVar( 'user_id' , '' , 'POST' );

		if ($requestid = $model->store($post))
			$msg = JText::_('COM_SEMINARMAN_RECORD_SAVED');
		else
			$msg = JText::_('ECOM_SEMINARMAN_ERROR_SAVING');

		// Process and save custom fields
		$model = $this->getModel( 'salesprospect' );
		$values	= array();
		$customfields = $model->getEditableCustomfields( $requestid );

		CMFactory::load( 'libraries' , 'customfields' );

		foreach( $customfields->fields as $group => $fields )
		{
			foreach( $fields as $data )
			{
				// Get value from posted data and map it to the field.
				// Here we need to prepend the 'field' before the id because in the form, the 'field' is prepended to the id.
				$postData				= JRequest::getVar( 'field' . $data['id'] , '' , 'POST' );
				$values[ $data['id'] ]	= SeminarmanCustomfieldsLibrary::formatData( $data['type']  , $postData );

				// @rule: Validate custom customfields if necessary
				if( !SeminarmanCustomfieldsLibrary::validateField( $data['type'] , $values[ $data['id'] ] , $data['required'] ) )
				{
					// If there are errors on the form, display to the user.
					$message	= JText::sprintf('COM_SEMINARMAN_FIELD_N_CONTAINS_IMPROPER_VALUES' ,  $data['name'] );
					$this->setredirect( 'index.php?option=com_seminarman&controller=salesprospect&task=edit&cid[]=' . $post['id'] , $message , 'error' );

					return;
				}
			}
		}
		//save data from custom fields
		$model->saveCustomfields($requestid, $userId, $values);

		$model->checkin();
		if ($this->getTask() == 'apply')
		{
			$link = 'index.php?option=com_seminarman&controller=salesprospect&task=edit&cid[]='.$requestid;
		}
		else
			$link = 'index.php?option=com_seminarman&view=' . $this->parentviewname;
		$this->setRedirect($link, $msg);
	}


	function remove()
	{
		JRequest::checkToken() or jexit('Invalid Token');
		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		if (count($cid) < 1)
			JError::raiseError(500, JText::_('COM_SEMINARMAN_SELECT_ITEM'));

		$model = $this->getModel($this->childviewname);

		if (!$model->delete($cid))
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		
		$msg = JText::_('COM_SEMINARMAN_OPERATION_SUCCESSFULL');
		$this->setRedirect('index.php?option=com_seminarman&view=' . $this->parentviewname, $msg);
	}


	function publish()
	{
		JRequest::checkToken() or jexit('Invalid Token');
		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		if (count($cid) < 1)
			JError::raiseError(500, JText::_('SCOM_SEMINARMAN_SELECT_ITEM'));

		$model = $this->getModel($this->childviewname);

		if (!$model->publish($cid, 1))
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";

		$this->setRedirect('index.php?option=com_seminarman&view=' . $this->parentviewname);
	}


	function unpublish()
	{
		JRequest::checkToken() or jexit('Invalid Token');
		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		if (count($cid) < 1)
			JError::raiseError(500, JText::_('COM_SEMINARMAN_SELECT_ITEM'));

		$model = $this->getModel($this->childviewname);

		if (!$model->publish($cid, 0))
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";

		$this->setRedirect('index.php?option=com_seminarman&view=' . $this->parentviewname);
	}


	function cancel()
	{
		JRequest::checkToken() or jexit('Invalid Token');
		$model = $this->getModel($this->childviewname);
		$model->checkin();
		$this->setRedirect('index.php?option=com_seminarman&view=' . $this->parentviewname);
	}


	function orderup()
	{
		JRequest::checkToken() or jexit('Invalid Token');
		$model = $this->getModel($this->childviewname);
		$model->move(-1);
		$this->setRedirect('index.php?option=com_seminarman&view=' . $this->parentviewname);
	}


	function orderdown()
	{

		JRequest::checkToken() or jexit('Invalid Token');
		$model = $this->getModel($this->childviewname);
		$model->move(1);
		$this->setRedirect('index.php?option=com_seminarman&view=' . $this->parentviewname);
	}


	function saveorder()
	{
		JRequest::checkToken() or jexit('Invalid Token');
		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		$order = JRequest::getVar('order', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);
		JArrayHelper::toInteger($order);
		$model = $this->getModel($this->childviewname);
		$model->saveorder($cid, $order);
		$msg = 'New ordering saved';
		$this->setRedirect('index.php?option=com_seminarman&view=' . $this->parentviewname,
		$msg);
	}

	function notify()
	{
		JRequest::checkToken() or jexit('Invalid Token');
		
		$link = 'index.php?option=com_seminarman&view=' . $this->parentviewname;

		$db = JFactory::getDBO();
		 
		$id = JRequest::getVar('id', 0, 'post', 'int');
		if ($id == 0)
		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		else
		{
			$cid = array();
			$cid[0] = $id;
		}
		
		$notified = 0; // counter for successfull notifications
		foreach ($cid as $id) {
			$field = 'select_course_notify'.(int)$id;
			$course = JRequest::getVar($field, 0, 'post', 'int');
			if ($course != 0)
			{
				$query = $db->getQuery(true);
				 
				$fields = array( $db->quoteName( 'notified_course' ). ' = ' . (int)$course );
				$conditions = array( $db->quoteName('id') . ' = ' . (int)$id );
				
				$query->update( $db->quoteName( '#__seminarman_salesprospect' ) )->set( $fields )->where( $conditions );
				
				$db->setQuery($query);
				$db->execute();
				
				if ($this->_notifyByEmail($id))
				{
					$query = $db->getQuery(true);
						
					$fields = array( $db->quoteName( 'notified' ). ' = UTC_TIMESTAMP()' );
					$conditions = array( $db->quoteName('id') . ' = ' . (int)$id );
					
					$query->update( $db->quoteName( '#__seminarman_salesprospect' ) )->set( $fields )->where( $conditions );
					
					$db->setQuery($query);
					$db->execute();
					
					$notified++;
				}
				else
				{
					// error in _notifyByEmail()
					$msg = "";
					if ($notified > 0)
						$msg .= $notified .' '.  JText::_('COM_SEMINARMAN_N_NOTIFY_OK') .'. '. JText::_('COM_SEMINARMAN_LAST_NOTIFY_NOTOK'). ': ';
					$msg .= $this->_errmsg;
					$this->setRedirect($link, $msg, 'error');
					return;
				}
			}
			else
			{
				// no course selected
				$msg = "";
				if ($notified > 0)
					$msg .= $notified .' '.  JText::_('COM_SEMINARMAN_N_NOTIFY_OK') .'. '. JText::_('COM_SEMINARMAN_LAST_NOTIFY_NOTOK'). ': ';
				$msg .= JText::_('COM_SEMINARMAN_NO_COURSE_SELECTED');
				$this->setRedirect($link, $msg, 'error');
				return;
			}
		}
		$this->setRedirect($link, $notified .' '. JText::_('COM_SEMINARMAN_N_NOTIFY_OK') .'.');
	}

	function _notifyByEmail($id)
	{
		$db = JFactory::getDBO();
		
		$query = $db->getQuery(true);
		$query->select( '*' );
		$query->from( '#__seminarman_emailtemplate' );
		$query->where( 'templatefor=1' );
		$query->where( 'isdefault=1' );
		$query->setLimit( 1 );
		 
		$db->setQuery($query);
		$template = $db->loadObject();
		
		if (!$template)
		{
			$this->_errmsg = JText::_('COM_SEMINARMAN_NO_EMAILTEMPLATE');
			return false;
		}

		$msgSubject = $template->subject;
		$msgBody = $template->body;
		$msgRecipient = $template->recipient;
		$msgRecipientBCC = $template->bcc;
		 
		$message = JFactory::getMailer();
		$config = JFactory::getConfig();
		$params = JComponentHelper::getParams('com_seminarman');
		
		$query = $db->getQuery(true);
		$query->select( 'sp.*, c.reference_number, c.title AS course, c.code, c.introtext, c.fulltext, c. capacity, c.location, c.url, c.start_date, c.finish_date, c.start_time, c.finish_time, c.id AS course_id, c.tutor_id AS course_tutor_ids, c.price AS course_price, c.vat AS course_price_vat, c.attribs AS course_attribs, sp.price_per_attendee, sp.attendees, sp.price_total, sp.price_vat, gr.title AS atgroup, gr.description AS atgroup_desc, ex.title AS experience_level, ex.description AS experience_level_desc' );
		$query->from( '#__seminarman_salesprospect AS sp' );
		$query->join( "LEFT", '#__seminarman_courses AS c ON c.id = sp.notified_course' );
		$query->join( "LEFT", '#__seminarman_atgroup AS gr ON gr.id = c.id_group' );
		$query->join( "LEFT", '#__seminarman_experience_level AS ex ON ex.id = c.id_experience_level' );
		$query->where( 'sp.id = ' . (int)$id );
			
		$db->setQuery($query);
		$queryResult = $db->loadObject();
		
		if (!$queryResult)
		{
			return false;
		}

		// parameters for multiple tutors
		$course_tutors_id_array = (array)json_decode($queryResult->course_tutor_ids, true);
		$course_first_tutor_id = $course_tutors_id_array[0];
		$course_tutors = array();
		foreach ( $course_tutors_id_array as $course_tutors_id ) {
			$query = $db->getQuery(true);
			$query->select( 'CONCAT_WS(\' \', emp.salutation, emp.other_title, emp.firstname, emp.lastname) AS tutor_combiname, CONCAT_WS(\' \', emp.firstname, emp.lastname) AS tutor_fullname,' .
					' emp.title AS tutor_displayname, emp.firstname AS tutor_firstname, emp.lastname AS tutor_lastname, emp.salutation AS tutor_salutation, emp.other_title AS tutor_title, emp.email AS tutor_email' );
			$query->from( '#__seminarman_tutor AS emp' );
			$query->where( 'emp.id = ' . $course_tutors_id );
				
			$db->setQuery( $query );
			$ergebnis = $db->loadAssoc();
			
			$course_tutors[$course_tutors_id] = $ergebnis;
		}
		$queryResult->course_all_tutors = '';
		$queryResult->course_all_tutors_fullname = '';
		$queryResult->course_all_tutors_combiname = '';
		$queryResult->tutor_recipients = '';
		$printComma = false;
		foreach ($course_tutors as $tutor_key => $tutor_content) {
			$tutor_email = trim($tutor_content['tutor_email']);
			if ($printComma) {
				$queryResult->course_all_tutors = $queryResult->course_all_tutors . ', ';
				$queryResult->course_all_tutors_fullname = $queryResult->course_all_tutors_fullname . ', ';
				$queryResult->course_all_tutors_combiname = $queryResult->course_all_tutors_combiname . ', ';
				if (!empty($tutor_email)) $queryResult->tutor_recipients = $queryResult->tutor_recipients . ', ';
			}
			$queryResult->course_all_tutors = $queryResult->course_all_tutors . $tutor_content['tutor_displayname'];
			$queryResult->course_all_tutors_fullname = $queryResult->course_all_tutors_fullname . $tutor_content['tutor_fullname'];
			$queryResult->course_all_tutors_combiname = $queryResult->course_all_tutors_combiname . $tutor_content['tutor_combiname'];
			if (!empty($tutor_email)) $queryResult->tutor_recipients = $queryResult->tutor_recipients . $tutor_email;
			$printComma = true;
		}
		
		// parameters for the first tutor
		$queryResult->tutor = $course_tutors[$course_first_tutor_id]['tutor_displayname'];
		$queryResult->tutor_first_name = $course_tutors[$course_first_tutor_id]['tutor_firstname'];
		$queryResult->tutor_last_name = $course_tutors[$course_first_tutor_id]['tutor_lastname'];
		$queryResult->tutor_salutation = $course_tutors[$course_first_tutor_id]['tutor_salutation'];
		$queryResult->tutor_other_title = $course_tutors[$course_first_tutor_id]['tutor_title'];		
		
		// tutor custom fields for the first tutor
		$query = $db->getQuery(true);
		$query->select( 'f.fieldcode, ct.value' );
		$query->from( '`#__seminarman_fields_values_tutors` AS ct' );
		$query->join( "LEFT", '`#__seminarman_fields` AS f ON ct.field_id = f.id' );
		$query->where( 'ct.tutor_id = '. $course_first_tutor_id );
		$query->where( 'f.published = ' . $db->Quote('1') );
		
		$db->setQuery( $query );		
		$tutor_customs = $db->loadAssocList();
		
		for ($i = 0; $i < count($tutor_customs); $i++) {
			$msgSubject = str_replace('{' . strtoupper($tutor_customs[$i]['fieldcode']) . '}', $tutor_customs[$i]['value'],
					$msgSubject);
			$msgBody = str_replace('{' . strtoupper($tutor_customs[$i]['fieldcode']) . '}', $tutor_customs[$i]['value'],
					$msgBody);
		}
		
		// course custom fields
		$course_attribs = new JRegistry();
		$course_attribs->loadString($queryResult->course_attribs);
		$custom_fld_1_value = $course_attribs->get('custom_fld_1_value');
		$custom_fld_2_value = $course_attribs->get('custom_fld_2_value');
		$custom_fld_3_value = $course_attribs->get('custom_fld_3_value');
		$custom_fld_4_value = $course_attribs->get('custom_fld_4_value');
		$custom_fld_5_value = $course_attribs->get('custom_fld_5_value');
		$queryResult->custom_fld_1_value = (!empty($custom_fld_1_value))?$custom_fld_1_value:'';
		$queryResult->custom_fld_2_value = (!empty($custom_fld_2_value))?$custom_fld_2_value:'';
		$queryResult->custom_fld_3_value = (!empty($custom_fld_3_value))?$custom_fld_3_value:'';
		$queryResult->custom_fld_4_value = (!empty($custom_fld_4_value))?$custom_fld_4_value:'';
		$queryResult->custom_fld_5_value = (!empty($custom_fld_5_value))?$custom_fld_5_value:'';
		
		// app custom fields (it MUST be here after tutor customs, otherwise you get problems; i don't think the below codes are well done, but it works. they are not my codes.)
		$query = $db->getQuery(true);
		$query->select( 'field.*, value.value' );
		$query->from( '`#__seminarman_fields` AS field' );
		$query->join( "LEFT", '`#__seminarman_fields_values_salesprospect` AS value ON field.id = value.field_id AND value.requestid='. (int)$id );
		$query->where( 'field.published=1' );
		$query->order( 'field.ordering' );
		
		$db->setQuery( $query );
		$fields = $db->loadAssocList();
		 
		for ($i = 0; $i < count($fields); $i++) {
			$msgSubject = str_replace('{' . strtoupper($fields[$i]['fieldcode']) . '}', $fields[$i]['value'], $msgSubject);
			$msgBody = str_replace('{' . strtoupper($fields[$i]['fieldcode']) . '}', $fields[$i]['value'], $msgBody);
		}
		
		// $course_price_orig = $queryResult->price_per_attendee;
		// $course_price_total_orig = $queryResult->price_total;
		// the above is incorrect, the current price info about the choosed course should be informed
		$course_price_orig = $queryResult->course_price;
		$course_price_total_orig = $course_price_orig * ($queryResult->attendees);
		
		// calculate and format price
		$lang = JFactory::getLanguage();
		$old_locale = setlocale(LC_NUMERIC, NULL);
		setlocale(LC_NUMERIC, $lang->getLocale());
		$queryResult->price_per_attendee = JText::sprintf('%.2f', round($course_price_orig, 2));
		$queryResult->price_total = JText::sprintf('%.2f', round($course_price_total_orig, 2));
		$queryResult->price_per_attendee_vat = JText::sprintf('%.2f', round((($course_price_orig / 100.0) * $queryResult->course_price_vat) + $course_price_orig, 2));
		$queryResult->price_total_vat = JText::sprintf('%.2f', round((($course_price_total_orig / 100.0) * $queryResult->course_price_vat) + $course_price_total_orig, 2));
		$queryResult->price_vat_percent = $queryResult->course_price_vat;
		$queryResult->price_vat = JText::sprintf('%.2f', round(($queryResult->price_total / 100.0) * $queryResult->price_vat_percent, 2));
		setlocale(LC_NUMERIC, $old_locale);
		
		// compute loaded date time (utc) to local date time
		$course_start_arr = SeminarmanFunctions::formatUTCtoLocal($queryResult->start_date, $queryResult->start_time);
		$course_finish_arr = SeminarmanFunctions::formatUTCtoLocal($queryResult->finish_date, $queryResult->finish_time);
		$course_start_date = $course_start_arr[0];  // local
		$course_start_time = $course_start_arr[1];  // local
		$course_finish_date = $course_finish_arr[0];  // local
		$course_finish_time = $course_finish_arr[1];  // local
		
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
			$COURSE_START_WEEKDAY = (!empty($queryResult->start_date)) ? strtr(date('l', strtotime($course_start_date)), $trans) : '';
		} else {
			$COURSE_START_WEEKDAY = (!empty($queryResult->start_date)) ? date('l', strtotime($course_start_date)) : '';
		}
		
		// first session infos
		$query = $db->getQuery(true);
		$query->select( '*' );
		$query->from( '`#__seminarman_sessions`' );
		$query->where( 'published = 1' );
		$query->where( 'courseid = ' . $queryResult->course_id );
		$query->order( 'session_date' );
		
		$db->setQuery( $query );
		$course_sessions = $db->loadObjectList();
		
		if(!empty($course_sessions)){
			// compute loaded date time (utc) to local date time
			$session_start_arr = SeminarmanFunctions::formatUTCtoLocal($course_sessions[0]->session_date, $course_sessions[0]->start_time);
			$session_finish_arr = SeminarmanFunctions::formatUTCtoLocal($course_sessions[0]->session_date, $course_sessions[0]->finish_time);
			
			$COURSE_FIRST_SESSION_TITLE = $course_sessions[0]->title;
			$COURSE_FIRST_SESSION_CLOCK = date('H:i', strtotime($session_start_arr[1])) . ' - ' . date('H:i', strtotime($session_finish_arr[1]));
			$COURSE_FIRST_SESSION_DURATION = $course_sessions[0]->duration;
			$COURSE_FIRST_SESSION_ROOM = $course_sessions[0]->session_location;
			$COURSE_FIRST_SESSION_COMMENT = $course_sessions[0]->description;
		} else {
			$COURSE_FIRST_SESSION_TITLE = '';
			$COURSE_FIRST_SESSION_CLOCK = '';
			$COURSE_FIRST_SESSION_DURATION = '';
			$COURSE_FIRST_SESSION_ROOM = '';
			$COURSE_FIRST_SESSION_COMMENT = '';
		}
		
		if (!empty( $queryResult->title )) $queryResult->title .= ' ';

		$msgSubject = str_replace('{ADMIN_CUSTOM_RECIPIENT}', $params->get('component_email'), $msgSubject);
		$msgSubject = str_replace('{TUTOR_RECIPENTS}', $queryResult->tutor_recipients, $msgSubject);
		$msgSubject = str_replace('{ATTENDEES}', $queryResult->attendees, $msgSubject);
		$msgSubject = str_replace('{SALUTATION}', $queryResult->salutation, $msgSubject);
		$msgSubject = str_replace('{TITLE}', $queryResult->title, $msgSubject);
		$msgSubject = str_replace('{FIRSTNAME}', $queryResult->first_name, $msgSubject);
		$msgSubject = str_replace('{LASTNAME}', $queryResult->last_name, $msgSubject);
		$msgSubject = str_replace('{EMAIL}', $queryResult->email, $msgSubject);
		$msgSubject = str_replace('{ATTENDEES}', $queryResult->attendees, $msgSubject);
		$msgSubject = str_replace('{COURSE_ID}', $queryResult->course_id, $msgSubject);
		$msgSubject = str_replace('{COURSE_TITLE}', $queryResult->course, $msgSubject);
		$msgSubject = str_replace('{COURSE_CODE}', $queryResult->code, $msgSubject);
		$msgSubject = str_replace('{COURSE_INTROTEXT}', $queryResult->introtext, $msgSubject);
		$msgSubject = str_replace('{COURSE_FULLTEXT}', $queryResult->fulltext, $msgSubject);
		$msgSubject = str_replace('{COURSE_CAPACITY}', $queryResult->capacity, $msgSubject);
		$msgSubject = str_replace('{COURSE_LOCATION}', $queryResult->location, $msgSubject);
		$msgSubject = str_replace('{COURSE_URL}', $queryResult->url, $msgSubject);
		$msgSubject = str_replace('{PRICE_PER_ATTENDEE}', $queryResult->price_per_attendee, $msgSubject);
		$msgSubject = str_replace('{PRICE_PER_ATTENDEE_VAT}', $queryResult->price_per_attendee_vat, $msgSubject);
		$msgSubject = str_replace('{PRICE_TOTAL}', $queryResult->price_total, $msgSubject);
		$msgSubject = str_replace('{PRICE_TOTAL_VAT}', $queryResult->price_total_vat, $msgSubject);
		$msgSubject = str_replace('{PRICE_VAT_PERCENT}', $queryResult->price_vat_percent, $msgSubject);
		$msgSubject = str_replace('{PRICE_VAT}', $queryResult->price_vat, $msgSubject);
		//$msgSubject = str_replace('{COURSE_START_DATE}', $queryResult->start_date, $msgSubject);
		$msgSubject = str_replace('{COURSE_START_DATE}', JFactory::getDate($course_start_date)->format(JText::_('COM_SEMINARMAN_DATE_FORMAT1')), $msgSubject);
		//$msgSubject = str_replace('{COURSE_FINISH_DATE}',  $queryResult->finish_date, $msgSubject);
		$msgSubject = str_replace('{COURSE_FINISH_DATE}', JFactory::getDate($course_finish_date)->format(JText::_('COM_SEMINARMAN_DATE_FORMAT1')), $msgSubject);
		$msgSubject = str_replace('{COURSE_START_TIME}', (!empty($queryResult->start_time)) ? date('H:i', strtotime($course_start_time)) : '', $msgSubject);
		$msgSubject = str_replace('{COURSE_FINISH_TIME}', (!empty($queryResult->finish_time)) ? date('H:i', strtotime($course_finish_time)) : '', $msgSubject);
		$msgSubject = str_replace('{TUTOR}', $queryResult->tutor, $msgSubject);
		$msgSubject = str_replace('{TUTOR_FIRSTNAME}', $queryResult->tutor_first_name, $msgSubject);
		$msgSubject = str_replace('{TUTOR_LASTNAME}', $queryResult->tutor_last_name, $msgSubject);
		$msgSubject = str_replace('{TUTOR_SALUTATION}', $queryResult->tutor_salutation, $msgSubject);
		$msgSubject = str_replace('{TUTOR_OTHER_TITLE}', $queryResult->tutor_other_title, $msgSubject);
		$msgSubject = str_replace('{COURSE_ALL_TUTORS}', $queryResult->course_all_tutors, $msgSubject);
		$msgSubject = str_replace('{COURSE_ALL_TUTORS_FULLNAME}', $queryResult->course_all_tutors_fullname, $msgSubject);
		$msgSubject = str_replace('{COURSE_ALL_TUTORS_COMBINAME}', $queryResult->course_all_tutors_combiname, $msgSubject);
		$msgSubject = str_replace('{GROUP}', $queryResult->atgroup, $msgSubject);
		$msgSubject = str_replace('{GROUP_DESC}', $queryResult->atgroup_desc, $msgSubject);
		$msgSubject = str_replace('{EXPERIENCE_LEVEL}', $queryResult->experience_level, $msgSubject);
		$msgSubject = str_replace('{EXPERIENCE_LEVEL_DESC}', $queryResult->experience_level_desc, $msgSubject);
		$msgSubject = str_replace('{COURSE_START_WEEKDAY}', $COURSE_START_WEEKDAY, $msgSubject);
		$msgSubject = str_replace('{COURSE_FIRST_SESSION_TITLE}', $COURSE_FIRST_SESSION_TITLE, $msgSubject);
		$msgSubject = str_replace('{COURSE_FIRST_SESSION_CLOCK}', $COURSE_FIRST_SESSION_CLOCK, $msgSubject);
		$msgSubject = str_replace('{COURSE_FIRST_SESSION_DURATION}', $COURSE_FIRST_SESSION_DURATION, $msgSubject);
		$msgSubject = str_replace('{COURSE_FIRST_SESSION_ROOM}', $COURSE_FIRST_SESSION_ROOM, $msgSubject);
		$msgSubject = str_replace('{COURSE_FIRST_SESSION_COMMENT}', $COURSE_FIRST_SESSION_COMMENT, $msgSubject);
		$msgSubject = str_replace('{COURSE_CUSTOM_FIELD_1}', $queryResult->custom_fld_1_value, $msgSubject);
		$msgSubject = str_replace('{COURSE_CUSTOM_FIELD_2}', $queryResult->custom_fld_2_value, $msgSubject);
		$msgSubject = str_replace('{COURSE_CUSTOM_FIELD_3}', $queryResult->custom_fld_3_value, $msgSubject);
		$msgSubject = str_replace('{COURSE_CUSTOM_FIELD_4}', $queryResult->custom_fld_4_value, $msgSubject);
		$msgSubject = str_replace('{COURSE_CUSTOM_FIELD_5}', $queryResult->custom_fld_5_value, $msgSubject);

		$msgBody = str_replace('{ADMIN_CUSTOM_RECIPIENT}', $params->get('component_email'), $msgBody);
		$msgBody = str_replace('{TUTOR_RECIPENTS}', $queryResult->tutor_recipients, $msgBody);
		$msgBody = str_replace('{ATTENDEES}', $queryResult->attendees, $msgBody);
		$msgBody = str_replace('{SALUTATION}', $queryResult->salutation, $msgBody);
		$msgBody = str_replace('{TITLE}', $queryResult->title, $msgBody);
		$msgBody = str_replace('{FIRSTNAME}', $queryResult->first_name, $msgBody);
		$msgBody = str_replace('{LASTNAME}', $queryResult->last_name, $msgBody);
		$msgBody = str_replace('{EMAIL}', $queryResult->email, $msgBody);
		$msgBody = str_replace('{COURSE_ID}', $queryResult->course_id, $msgBody);
		$msgBody = str_replace('{COURSE_TITLE}', $queryResult->course, $msgBody);
		$msgBody = str_replace('{COURSE_CODE}', $queryResult->code, $msgBody);
		$msgBody = str_replace('{COURSE_INTROTEXT}', $queryResult->introtext, $msgBody);
		$msgBody = str_replace('{COURSE_FULLTEXT}', $queryResult->fulltext, $msgBody);
		$msgBody = str_replace('{COURSE_CAPACITY}', $queryResult->capacity, $msgBody);
		$msgBody = str_replace('{COURSE_LOCATION}', $queryResult->location, $msgBody);
		$msgBody = str_replace('{COURSE_URL}', $queryResult->url, $msgBody);
		$msgBody = str_replace('{PRICE_PER_ATTENDEE}', $queryResult->price_per_attendee, $msgBody);
		$msgBody = str_replace('{PRICE_PER_ATTENDEE_VAT}', $queryResult->price_per_attendee_vat, $msgBody);
		$msgBody = str_replace('{PRICE_TOTAL}', $queryResult->price_total, $msgBody);
		$msgBody = str_replace('{PRICE_TOTAL_VAT}', $queryResult->price_total_vat, $msgBody);
		$msgBody = str_replace('{PRICE_VAT_PERCENT}', $queryResult->price_vat_percent, $msgBody);
		$msgBody = str_replace('{PRICE_VAT}', $queryResult->price_vat, $msgBody);
		//$msgBody = str_replace('{COURSE_START_DATE}',  $queryResult->start_date, $msgBody);
		$msgBody = str_replace('{COURSE_START_DATE}', JFactory::getDate($course_start_date)->format(JText::_('COM_SEMINARMAN_DATE_FORMAT1')), $msgBody);
		//$msgBody = str_replace('{COURSE_FINISH_DATE}',  $queryResult->finish_date, $msgBody);
		$msgBody = str_replace('{COURSE_FINISH_DATE}', JFactory::getDate($course_finish_date)->format(JText::_('COM_SEMINARMAN_DATE_FORMAT1')), $msgBody);
		$msgBody = str_replace('{COURSE_START_TIME}', (!empty($queryResult->start_time)) ? date('H:i', strtotime($course_start_time)) : '', $msgBody);
		$msgBody = str_replace('{COURSE_FINISH_TIME}', (!empty($queryResult->finish_time)) ? date('H:i', strtotime($course_finish_time)) : '', $msgBody);
		$msgBody = str_replace('{TUTOR}', $queryResult->tutor, $msgBody);
		$msgBody = str_replace('{TUTOR_FIRSTNAME}', $queryResult->tutor_first_name, $msgBody);
		$msgBody = str_replace('{TUTOR_LASTNAME}', $queryResult->tutor_last_name, $msgBody);
		$msgBody = str_replace('{TUTOR_SALUTATION}', $queryResult->tutor_salutation, $msgBody);
		$msgBody = str_replace('{TUTOR_OTHER_TITLE}', $queryResult->tutor_other_title, $msgBody);
		$msgBody = str_replace('{COURSE_ALL_TUTORS}', $queryResult->course_all_tutors, $msgBody);
		$msgBody = str_replace('{COURSE_ALL_TUTORS_FULLNAME}', $queryResult->course_all_tutors_fullname, $msgBody);
		$msgBody = str_replace('{COURSE_ALL_TUTORS_COMBINAME}', $queryResult->course_all_tutors_combiname, $msgBody);
		$msgBody = str_replace('{GROUP}', $queryResult->atgroup, $msgBody);
		$msgBody = str_replace('{GROUP_DESC}', $queryResult->atgroup_desc, $msgBody);
		$msgBody = str_replace('{EXPERIENCE_LEVEL}', $queryResult->experience_level, $msgBody);
		$msgBody = str_replace('{EXPERIENCE_LEVEL_DESC}', $queryResult->experience_level_desc, $msgBody);
		$msgBody = str_replace('{COURSE_START_WEEKDAY}', $COURSE_START_WEEKDAY, $msgBody);
		$msgBody = str_replace('{COURSE_FIRST_SESSION_TITLE}', $COURSE_FIRST_SESSION_TITLE, $msgBody);
		$msgBody = str_replace('{COURSE_FIRST_SESSION_CLOCK}', $COURSE_FIRST_SESSION_CLOCK, $msgBody);
		$msgBody = str_replace('{COURSE_FIRST_SESSION_DURATION}', $COURSE_FIRST_SESSION_DURATION, $msgBody);
		$msgBody = str_replace('{COURSE_FIRST_SESSION_ROOM}', $COURSE_FIRST_SESSION_ROOM, $msgBody);
		$msgBody = str_replace('{COURSE_FIRST_SESSION_COMMENT}', $COURSE_FIRST_SESSION_COMMENT, $msgBody);
		$msgBody = str_replace('{COURSE_CUSTOM_FIELD_1}', $queryResult->custom_fld_1_value, $msgBody);
		$msgBody = str_replace('{COURSE_CUSTOM_FIELD_2}', $queryResult->custom_fld_2_value, $msgBody);
		$msgBody = str_replace('{COURSE_CUSTOM_FIELD_3}', $queryResult->custom_fld_3_value, $msgBody);
		$msgBody = str_replace('{COURSE_CUSTOM_FIELD_4}', $queryResult->custom_fld_4_value, $msgBody);
		$msgBody = str_replace('{COURSE_CUSTOM_FIELD_5}', $queryResult->custom_fld_5_value, $msgBody);

		// additional parameters
		$dispatcher=JDispatcher::getInstance();
		JPluginHelper::importPlugin('seminarman');
		$extData=$dispatcher->trigger('onGenerateSPEmail', array(array($queryResult->course_id, $msgSubject, $msgBody, $queryResult->attendees)));		
		if(isset($extData) && !empty($extData)) {
			$msgSubject = $extData[0]['subject'];
			$msgBody = $extData[0]['body'];
		}
		
		$msgRecipient = str_replace('{EMAIL}', $queryResult->email, $msgRecipient);
		$msgRecipient = str_replace('{ADMIN_CUSTOM_RECIPIENT}', $params->get('component_email'), $msgRecipient);
		$msgRecipient = str_replace('{TUTOR_RECIPIENTS}', $queryResult->tutor_recipients, $msgRecipient);
		 
		$msgRecipients = array_filter(explode(",", str_replace(" ","", trim($msgRecipient))));
		
		if (!empty($msgRecipientBCC))
		{
			$msgRecipientBCC = str_replace('{EMAIL}', $queryResult->email, $msgRecipientBCC);
			$msgRecipientBCC = str_replace('{ADMIN_CUSTOM_RECIPIENT}', $params->get('component_email'), $msgRecipientBCC);
			$msgRecipientBCC = str_replace('{TUTOR_RECIPIENTS}', $queryResult->tutor_recipients, $msgRecipientBCC);
			$msgRecipientBCC = array_filter(explode(",", str_replace(" ","",trim($msgRecipientBCC))));
			$message->addBCC($msgRecipientBCC);
		}
    	
		// $senderEmail = $config->getValue('mailfrom');
		// $senderName = $config->getValue('fromname');
		$senderEmail = $config->get('mailfrom');
		$senderName = $config->get('fromname');
		$message->addRecipient($msgRecipients);
		$message->setSubject($msgSubject);
		$message->setBody($msgBody);
		$sender = array($senderEmail, $senderName);
		$message->setSender($sender);
		$message->IsHTML(true);
		$message->send();
		return true;
	}

}

?>