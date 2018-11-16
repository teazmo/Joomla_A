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

jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');

require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_seminarman'.DS.'classes'.DS.'pdfdocument.php');
require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS . 'helpers' . DS . 'seminarman.php');

class seminarmanControllerApplication extends seminarmanController
{
	function save()
	{
		JRequest::checkToken() or jexit('Invalid Token');

		$user = JFactory::getUser();
		$mainframe = JFactory::getApplication();
		$params = $mainframe->getParams();
		$post = JRequest::get('post');
		$post['user_id'] = $user->get('id');
		$post['published'] = 1;

		$payment_method = 0;
		if (isset($post['payment_method'])) {
			$payment_method = $post['payment_method'][0];
		} else {  // if booking overview disabled or payment selection disabled, payment method paypal should be defined automatically
			if ($params->get('enable_paypal')) $payment_method = 2;
		}
		
        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('seminarman');
	    // $course = $this->getModel('courses');
	    // $course_id = $course->_id;
		$course_id = intval($post['course_id']);
        // fire vmengine
        $results = $dispatcher->trigger('onProcessBooking', array($course_id));	
        if(!empty($results)){	
		    $vmlink = $results[0];
        }else{
        	$vmlink = null;
        }
        
		switch ($params->get('enable_bookings')) {
			case 3:
				// ok, everyone is allowed to book
				break;
			case 2:
				// ok, everyone is allowed to book
				break;
			case 1:
				// only registered useres are allowed
				if ($user->get('guest'))
				$mainframe->redirect('index.php?option=com_users&view=login&return='. base64_encode(JRoute::_('index.php?option=com_seminarman', false)), JText::_('COM_SEMINARMAN_PLEASE_LOGIN_FIRST'));
				break;
			default:
				// booking disabled
				$mainframe->redirect("index.php", JText::_('COM_SEMINARMAN_BOOKINGS_DISABLED'));
		}
		
		CMFactory::load( 'helpers' , 'emails' );
		if (!$email_arr = isValidInetAddress($post['email']))
		{
			$mainframe->enqueueMessage(JText::_('COM_SEMINARMAN_NO_VALID_EMAIL'), 'error');
			return;
		}
		$post['email'] = $email_arr[0].'@'.$email_arr[1];

		$courseModel = $this->getModel('courses');
		$editfields = $courseModel->getEditableCustomfields($post['user_id']);

		CMFactory::load('libraries', 'customfields');

		$customFields = array();
		foreach ($editfields['fields'] as $group => $fields) {
			foreach ($fields as $data) {
				$postData = JRequest::getVar('field' . $data['id'], '', 'POST');
				$customFields[$data['id']] = SeminarmanCustomfieldsLibrary::formatData($data['type'], $postData);
				if (!SeminarmanCustomfieldsLibrary::validateField($data['type'], $customFields[$data['id']], $data['required'])) {
					if ($data['type'] == 'checkboxtos')
						$message =  JText::sprintf('COM_SEMINARMAN_ACCEPT_TOS', $data['name']);
					else
						$message = JText::sprintf('COM_SEMINARMAN_FIELD_N_CONTAINS_IMPROPER_VALUES', $data['name']);
					$mainframe->enqueueMessage($message, 'error');
					return $this->redirect();
				}
			}
		}

		// list of courses
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select( '*' );
		$query->from( '`#__seminarman_courses`' );
		$query->where( 'id ='. $post['course_id'] );
		
		$db->setQuery( $query );
		$courseRows = $db->loadObject();
		
		if ( !isset( $courseRows ) ) {
			JError::raiseError(500, $db->stderr(true));
			return;
		}

		if (!$params->get('enable_num_of_attendees')) {
			$post['attendees'] = 1;
		}
		$post['start_date'] = $courseRows->start_date;
		$post['finish_date'] = $courseRows->finish_date;
		$post['start_time'] = $courseRows->start_time;
		$post['finish_time'] = $courseRows->finish_time;
		$post['price_per_attendee'] = $courseRows->price;
		$post['price_total'] = $post['price_per_attendee'] * $post['attendees'];
		$post['price_vat'] = $courseRows->vat;		
		
		// capacity check
		$applicationModel = $this->getModel('application');
		if ( $post['status'] != 4 ) {
	    	if ( $params->get('current_capacity') > 0 ) {
                $freespaces = $courseRows->capacity - (int)$applicationModel->getCurrentBookings();
                if ( $freespaces < 1 ) {
               		//$mainframe->redirect(JRoute::_("index.php"), JText::_('COM_SEMINARMAN_COURSE_IS_FULL'));
                    $bgf = JText::_( 'COM_SEMINARMAN_COURSE_IS_FULL' );
                    $mainframe->enqueueMessage($bgf, 'error');
                    $mainframe->redirect(JRoute::_("index.php"));
                }
                else if ( $freespaces < $post['attendees']) {
                    $freespaces = $courseRows->capacity - (int)$applicationModel->getCurrentBookings();

                    $bgf = JText::sprintf('COM_SEMINARMAN_BOOKING_GREATER_FREESPACES',
                   		$post['attendees'],
                    	$freespaces
                        );
                        $mainframe->enqueueMessage($bgf, 'error');
                        $mainframe->redirect(JRoute::_("index.php"));
                }
	        }
        }			
			
		// did this user already book that course?
		if (!$params->get('enable_multiple_bookings_per_user') && $applicationModel->getCurrentBookingsForUser($post['user_id']) > 0)
			$mainframe->redirect(JRoute::_("index.php"), JText::_('COM_SEMINARMAN_ALREADY_BOOKED'));
		
		$usersConfig = JComponentHelper::getParams('com_users');

$query_pricegroup2 = $db->getQuery(true);
$query_pricegroup2->select('*')
                  ->from('#__seminarman_pricegroups')
                  ->where('gid=2');
$db->setQuery($query_pricegroup2);
$priceg2 = $db->loadAssoc();
$priceg2_name = $priceg2['title'];
$priceg2_usg = json_decode($priceg2['jm_groups']);
$priceg2_reg = $priceg2['reg_group'];

$query_pricegroup3 = $db->getQuery(true);
$query_pricegroup3->select('*')
                  ->from('#__seminarman_pricegroups')
                  ->where('gid=3');
$db->setQuery($query_pricegroup3);
$priceg3 = $db->loadAssoc();
$priceg3_name = $priceg3['title'];
$priceg3_usg = json_decode($priceg3['jm_groups']);
$priceg3_reg = $priceg3['reg_group'];

$query_pricegroup4 = $db->getQuery(true);
$query_pricegroup4->select('*')
->from('#__seminarman_pricegroups')
->where('gid=4');
$db->setQuery($query_pricegroup4);
$priceg4 = $db->loadAssoc();
$priceg4_name = $priceg4['title'];
$priceg4_usg = json_decode($priceg4['jm_groups']);
$priceg4_reg = $priceg4['reg_group'];

$query_pricegroup5 = $db->getQuery(true);
$query_pricegroup5->select('*')
->from('#__seminarman_pricegroups')
->where('gid=5');
$db->setQuery($query_pricegroup5);
$priceg5 = $db->loadAssoc();
$priceg5_name = $priceg5['title'];
$priceg5_usg = json_decode($priceg5['jm_groups']);
$priceg5_reg = $priceg5['reg_group'];

		// register user
		if (($post['user_id'] == 0) && ($usersConfig->get('allowUserRegistration') != '0') && ($params->get('enable_bookings') != '3'))
		{
			// is there alread a joomla user with the same email address?
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select( 'id' );
			$query->from( '`#__users`' );
			$query->where( 'email =' . $db->Quote( $post['email'] ) );
			
			$db->setQuery( $query );
			$existing_uid = $db->loadResult();
			if (!empty($existing_uid))
			{
				// yes. set user_id of this application to this user.
				$uid = $existing_uid;
			}
			else
			{
				// no. create a new joomla user
				jimport('joomla.user.helper');
								
    			$data = array();
    			$data['id'] = 0;
    			$data['name'] = $post['first_name'].' '.$post['last_name'];
    			$data['username'] = $post['email'];
    			$data['email'] = $post['email'];
    			$data['groups'] = array(2); // 2: Registered
    			if ($post['booking_price'][0] == 1) { // 2. price group
    			    if ($priceg2_reg != 0) $data['groups'] = array($priceg2_reg);	
    			}
    			if ($post['booking_price'][0] == 2) { // 3. price group
    				if ($priceg3_reg != 0) $data['groups'] = array($priceg3_reg);
    			}
    			if ($post['booking_price'][0] == 3) { // 4. price group
    				if ($priceg4_reg != 0) $data['groups'] = array($priceg4_reg);
    			}
    			if ($post['booking_price'][0] == 4) { // 5. price group
    				if ($priceg5_reg != 0) $data['groups'] = array($priceg5_reg);
    			}
    			$data['block'] = $usersConfig->get('useractivation') > 0 ? 1 : 0;
    			// $data['activation'] = JUtility::getHash(JUserHelper::genRandomPassword());
    			$jversion = new JVersion();
    			$short_version = $jversion->getShortVersion();
    			if (version_compare($short_version, "3.0", 'ge')) {
    			    $data['activation'] = JApplication::getHash(JUserHelper::genRandomPassword());
				} else {
					$data['activation'] = JUtility::getHash(JUserHelper::genRandomPassword());
				}
    			
    			$password = JUserHelper::genRandomPassword();
    			$salt = JUserHelper::genRandomPassword(32);
    			$crypted = JUserHelper::getCryptedPassword($password, $salt);
    			
    			$usern = JUser::getInstance();
    			$usern->bind($data);
    			$usern->set('password', $crypted.':'.$salt);
    			$usern->save();
    			
    			$uid = $usern->id;
    			if ($applicationModel->sendRegistrationEmail($usern, $password)) {
    				if (($params->get('trigger_virtuemart') == 1) && (!is_null($vmlink))) {
    					$mainframe->enqueueMessage(JText::_('COM_SEMINARMAN_REGISTRATION_MAIL_SENT'));
    				}
    			}
			}
			$post['user_id'] = $uid;
		}
		
		if ($post['booking_price'][0] == 66) { // if booking in virtuemart, we gonna create a dumy booking in seminar manager first
			$post['price_per_attendee'] = 0;
			$post['price_total'] = 0;
			$post['status'] = 3;
		} elseif ($post['booking_price'][0] == 1) { // 2. price group
			$post['price_per_attendee'] = $courseRows->price2;
			$post['price_total'] = $post['price_per_attendee'] * $post['attendees'];
			$post['pricegroup'] = $priceg2_name;			
		} elseif ($post['booking_price'][0] == 2) { // 3. price group
			$post['price_per_attendee'] = $courseRows->price3;
			$post['price_total'] = $post['price_per_attendee'] * $post['attendees'];
			$post['pricegroup'] = $priceg3_name;			
		} elseif ($post['booking_price'][0] == 3) { // 4. price group
			$post['price_per_attendee'] = $courseRows->price4;
			$post['price_total'] = $post['price_per_attendee'] * $post['attendees'];
			$post['pricegroup'] = $priceg4_name;			
		} elseif ($post['booking_price'][0] == 4) { // 5. price group
			$post['price_per_attendee'] = $courseRows->price5;
			$post['price_total'] = $post['price_per_attendee'] * $post['attendees'];
			$post['pricegroup'] = $priceg5_name;			
		}
		
		$fee_open = false;
		if ( $post['price_total'] > 0 ) $fee_open = true;
		if ( isset( $post['params']['fee1_value'] ) && ( $post['params']['fee1_value'] > 0 ) && isset( $post['params']['fee1_value'] ) && ( $post['params']['fee1_selected'] ) == 1 ) $fee_open = true;

		// new: if not bill after paypal
		if (( $post['status'] != 4 ) && ( ( $params->get('invoice_generate') == 1 ) && ( $fee_open ) && ( !( $payment_method == 2 && $params->get('enable_paypal') && $params->get('invoice_after_pay') && ($post['status'] != 4 )))) || ( $post['booking_price'][0] == 66) ) {
			$post['invoice_filename_prefix'] = strtolower(str_replace(' ', '_', JText::_('COM_SEMINARMAN_INVOICE_PREFIX'))) . '_';
			$post['invoice_number'] = $applicationModel->getInvoiceNumber();
		} else {
			$post['invoice_number'] = 0-time();
		}		
		
		// save data in application table 
		if (!$applicationid = $applicationModel->store($post))
			return $this->setRedirect(JRoute::_($params->get('application_landingpage')), JText::_('COM_SEMINARMAN_ERROR_PROCESSING_APPLICATION'));
		
		$post['applicationid'] = $applicationid;

		// save custom fields
		$applicationModel->saveCustomfields($applicationid, $post['user_id'], $customFields);

		// create and save invoice (new: if not bill after paypal)
		if (( $post['status'] != 4 ) && $params->get('invoice_generate') == 1 && ($fee_open) && (!($payment_method == 2 && $params->get('enable_paypal') && $params->get('invoice_after_pay'))) && !($post['booking_price'][0] == 66))
		{
			if (!$template = $this->getModel('pdftemplate')->getTemplate($courseRows->invoice_template))
				return $this->setRedirect(JRoute::_($params->get('application_landingpage')), JText::_('COM_SEMINARMAN_ERROR_PROCESSING_APPLICATION'));
			
			$templateData = JHTMLSeminarman::getFieldValuesForTemplate($applicationid);
			$pdf = new PdfInvoice($template, $templateData);
			$pdf->store(JPATH_ROOT.DS.$params->get('invoice_save_dir').DS.$post['invoice_filename_prefix'].$post['invoice_number'].'.pdf');
		}

		if (($params->get('trigger_virtuemart') == 1) && (!is_null($vmlink))) {
			// JRequest::setVar('dummy_booking_' . $courseRows->id, $applicationid);
            $mainframe->setUserState('dummy_booking_' . $courseRows->id, $applicationid);
			
            // redirect to VirtueMart
            $this->setRedirect($vmlink);
			
		} else {

			// from 2.13.x, multiple attachments possible
			$attachment = array();
			
		    // send confimation mail with invoice to applicant (new: if not bill after paypal)
			if (( $post['status'] != 4 ) && ($params->get('invoice_generate') == 1 && $params->get('invoice_attach') == 1 && ($fee_open)) && !($payment_method == 2 && $params->get('enable_paypal') && $params->get('invoice_after_pay')))
			    $attachment[] = $pdf->getFile();
		    //else
			//    $attachment = '';
		    
			// ics file
			if ($params->get('ics_file_name') == 0) {
			    $ics_filename = "ical_course_" . $courseRows->id . ".ics";
			} else {
			    $ics_filename = JFile::makeSafe(str_replace(array('Ä','Ö','Ü','ä','ö','ü','ß'), array('Ae','Oe','Ue','ae','oe','ue','ss'), html_entity_decode($courseRows->title, ENT_QUOTES)) . '_' . $courseRows->id . ".ics");
				$ics_filename = str_replace(' ', '_', $ics_filename);
			}
			$ics_filepath = JPATH_ROOT.DS.$params->get('invoice_save_dir').DS . $ics_filename;
			$courseParams = new JRegistry($courseRows->attribs);
			$params->merge($courseParams);
			if ((!(JFile::exists($ics_filepath))) && ($params->get('ics_by_booking'))) {
				SMANFunctions::create_course_ics($courseRows);
			}
			if ($params->get('ics_by_booking') && JFile::exists($ics_filepath)) $attachment[] = $ics_filepath;
			
			// additional email attachment
			if ($params->get('add_extra_attach')) {
				// JLoader::import( 'Pdftemplate', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS . 'models' );
				$attachment_file = SeminarmanFunctions::createEmailAttachment( $applicationid );
				if ($attachment_file != false) {
					$attachment[] = $attachment_file;
				}
			}
			
			$course_attachments = SMANFunctions::getCourseEmailAttachs($course_id);
			if (!empty($course_attachments)) {
				foreach ($course_attachments AS $course_attachment) {
					$attachment[] = $course_attachment;
				}
			}

		    // in all cases the confirmation email will be sent except one case: paypal enabled && price > 0 && bill after pay
		    if (!($payment_method == 2 && ($params->get('enable_paypal')) && ($fee_open) && ($params->get('invoice_after_pay') && $post['status'] != 4))) {
				
	  		  	if ( $post['status'] != 4 ) {
	    			if (!$applicationModel->sendemail($post, $courseRows->email_template, $attachment))
		   				return $this->setRedirect(JRoute::_($params->get('application_landingpage')), JText::_('COM_SEMINARMAN_ERROR_SENDING_EMAILS'));
	    		}
	    		else { // send warteliste email
		   			require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS . 'helpers' . DS . 'seminarman.php');
		   			$wl_email = JHTMLSeminarman::getWaitingListEmailTemplate();

		   			if (!$applicationModel->sendemailWaitingList($post, $wl_email, $attachment))
		   				return $this->setRedirect(JRoute::_($params->get('application_landingpage')), JText::_('COM_SEMINARMAN_ERROR_SENDING_EMAILS'));
		    	}
		    }
			$thanks_text = JText::_('COM_SEMINARMAN_THANK_YOU_FOR_YOUR_APPLICATION');
			if ( $post['status'] == 4 )
				$thanks_text = JText::_('COM_SEMINARMAN_THANK_YOU_FOR_YOUR_APPLICATION_WAITINGLIST');
				
			    
			// redirect to paypal view, if paypal is enabled
		    if ($payment_method == 2 && ($params->get('enable_paypal')) && ($fee_open) && $post['status'] != 4 )
			    return $this->setRedirect(JRoute::_('index.php?option=com_seminarman&view=paypal&bookingid=' . $applicationid, false), $thanks_text.'!');

		    if($params->get('enable_bookings')==1 && $params->get('user_booking_rules')==1 && $user->id > 0){
		    	$found_rule = JHTMLSeminarman::get_first_used_booking_rule($course_id, $user->id);		    	
		    	if ($found_rule == false) {
		   			$this->setRedirect(JRoute::_($params->get('application_landingpage'),false), $thanks_text.'!');
		    	} else {
		    		if ($found_rule['booked'] < $found_rule['amount']) {
		    			$available_bookings = $found_rule['amount'] - $found_rule['booked'];
		   			    $this->setRedirect(JRoute::_($params->get('application_landingpage'),false), JText::sprintf('COM_SEMINARMAN_THANK_YOU_FOR_YOUR_APPLICATION_W_PARAMS', $found_rule['title'], $available_bookings, $found_rule['finish_date']));
		   			} else {
		   				$this->setRedirect(JRoute::_($params->get('application_landingpage'),false), $thanks_text.'!');
		   			}
		   		}
		   	} else {
		        $this->setRedirect(JRoute::_($params->get('application_landingpage'),false), $thanks_text.'!');
		    }
		}
	}
	
	function cart() {
		
		$view = $this->getView('Courses', 'html');
		$model = $this->getModel('Courses');		
		$view->setModel($model, true);		
		// $view->setLayout('cart');		
		$view->display('cart');
		
	}
	
	function checkout() {
	
		$view = $this->getView('Courses', 'html');
		$model = $this->getModel('Courses');
		$view->setModel($model, true);
		// $view->setLayout('cart');
		$view->display('checkout');
	
	}
	
	function cancel() {
		
		$view = $this->getView('Courses', 'html');
		$model = $this->getModel('Courses');
		$view->setModel($model, true);
		$view->display();
	}
	
	function cancel_booking() {
		JSession::checkToken('get') or jexit('Invalid Token');
		
		$view = $this->getView('Bookings', 'html');
		$model = $this->getModel('Bookings');
		$view->setModel($model, true);
		// $view->setLayout('cart');
		$view->display('cancel_booking');	
	}
	
	function cancel_booking_process() {
		JSession::checkToken('get') or jexit('Invalid Token');
		
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$params = $mainframe->getParams('com_seminarman');
		
		$user = JFactory::getUser();
		$user_id = (int)$user->get('id');
		
		$app_id = JRequest::getVar("application_id");
		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select( 'a.user_id AS app_user' );
		$query->select( 'a.status AS app_status' );
		$query->select( 'c.start_date AS course_start_date' );
		$query->select( 'c.start_time AS course_start_time' );
		$query->from( '`#__seminarman_application` AS a' );
		$query->join( "LEFT", '#__seminarman_courses AS c ON (a.course_id = c.id)' );
		$query->where( 'a.published = 1' );
		$query->where( 'a.id = '. $app_id );
			
		$db->setQuery( $query );
		$item = $db->loadObject();
		
		$today = JFactory::getDate()->format('Y-m-d H:i:s');
		
		// fix for 24:00:00 (illegal time colock)
		if ($item->course_start_time == '24:00:00') $item->course_start_time = '23:59:59';
		
		if (($user_id != 0) && ($user_id == $item->app_user)) {
			switch($params->get('cancel_allowed')) {
				case 0:    // cancel not allowed
					$item->cancel_allowed = false;
					break;
				case 1:    // only state "submitted" allowed
					if ($item->app_status > 0 && $item->app_status != 4) {
						$item->cancel_allowed = false;
					} else {
						if ((trim($params->get('cancel_deadline'))) == '') {
							$item->cancel_allowed = true;
						} else {
							$kursbegindate = new JDate($item->course_start_date . ' ' . $item->course_start_time);
							$kursbegin = $kursbegindate->format('Y-m-d H:i:s');
							if ((strtotime($kursbegin) - strtotime($today)) > (86400 * (int)$params->get('cancel_deadline'))) {
								$item->cancel_allowed = true;
							} else {
								$item->cancel_allowed = false;
							}
						}
					}
					break;
				case 2:    // state "submitted" and "pending" allowed
					if ($item->app_status > 1 && $item->app_status != 4) {
						$item->cancel_allowed = false;
					} else {
						if ((trim($params->get('cancel_deadline'))) == '') {
							$item->cancel_allowed = true;
						} else {
							$kursbegindate = new JDate($item->course_start_date . ' ' . $item->course_start_time);
							$kursbegin = $kursbegindate->format('Y-m-d H:i:s');
							if ((strtotime($kursbegin) - strtotime($today)) > (86400 * (int)$params->get('cancel_deadline'))) {
								$item->cancel_allowed = true;
							} else {
								$item->cancel_allowed = false;
							}
						}
					}
					break;
				default:
					$item->cancel_allowed = false;
			}
		} else {
			$item->cancel_allowed = false;
		}
		
		if ($item->cancel_allowed) {
			$query = $db->getQuery(true);
			 
			$fields = array( $db->quoteName( 'status' ). ' = 3' );
			$conditions = array( $db->quoteName('id') . ' = ' . $app_id	);
			 
			$query->update( $db->quoteName( '#__seminarman_application' ) )->set( $fields )->where( $conditions );
			$db->setQuery($query);
			
			if (!$db->execute())
			{
				$this->setError( $db->getErrorMsg() );
				$mainframe->enqueueMessage($err_msg, 'error');
			} else {
				$this->update_protocol($app_id, 3);
				$this->setRedirect(JRoute::_($params->get('application_landingpage'),false), JText::_('COM_SEMINARMAN_CANCEL_SUCCESS'));
			}
		} else {
			echo JText::_('COM_SEMINARMAN_CANCEL_NOT_ALLOWED');
		}
		
	}
	
	function no_cancel_booking() {
		$mainframe = JFactory::getApplication();
		$params = $mainframe->getParams('com_seminarman');
		$this->setRedirect(JRoute::_($params->get('application_landingpage'),false));
	}

	function update_protocol($cid, $status){
		$user = JFactory::getUser();
		$db = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		 
		if ( $cid ){
			$query = $db->getQuery(true);
			$query->select( 'params' );
			$query->from( '`#__seminarman_application`' );
			$query->where( 'id = '.(int)$cid );
			
			$db->setQuery( $query );
			$params_string = $db->loadResult();
			
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
			$query_update = $db->getQuery(true);
			
			$fields = array( $db->quoteName( 'params' ). " = '" . $db->escape($params_string) . "'" );
			$conditions = array( $db->quoteName('id') . ' = ' . (int)$cid	);
			
			$query_update->update( $db->quoteName( '#__seminarman_application' ) )->set( $fields )->where( $conditions );
			$db->setQuery( $query_update );
				
			if (!$db->execute()) {
				$err_msg = $db->getErrorMsg();
				$mainframe->enqueueMessage($err_msg, 'error');
				return false;
			}
		}
		return true;
	}	
	
}

?>