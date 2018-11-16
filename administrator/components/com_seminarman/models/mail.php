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

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

require_once (JPATH_COMPONENT_ADMINISTRATOR . DS . 'helpers' . DS . 'seminarman.php');

/**
 * Users mail model.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_seminarman
 * @since	1.6
 */
class SeminarmanModelMail extends JModelAdmin
{
	/**
	 * Method to get the row form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	var $receipts;
	var $subject;
	var $form;
	var $invoice;
	
    function __construct()
    {
        parent::__construct();
        
        $this->form = $this->getForm();

        if (!empty($receipts) && is_array($receipts)) {
        	
        } else {
        	$this->receipts = array();
        }
    }
	
	function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_seminarman.mail', 'mail', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		return $form;
	}
	
    function buildReceiptList(){
		$listReceipts = '<select size="10" multiple="multiple" name="jform[receipt][]" id="jform_receipt" style="min-width: 160px; width: 17em;">';
		foreach ($this->receipts as $receipt) {
			$listReceipts .= '<option selected="selected" value="' . $receipt->id . '">' . $receipt->email . '</option>';
		}
		$listReceipts .= '</select>';
		return $listReceipts;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_seminarman.display.mail.data', array());

		return $data;
	}

	/**
	 * Override preprocessForm to load the user plugin group instead of content.
	 *
	 * @param	object	A form object.
	 * @param	mixed	The data expected for the form.
	 * @throws	Exception if there is an error in the form event.
	 * @since	1.6
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'user')
	{
		parent::preprocessForm($form, $data, $group);
	}

	public function send()
	{
		// Initialise variables.
		$data	= JRequest::getVar('jform', array(), 'post', 'array');
		$app	= JFactory::getApplication();
		$user	= JFactory::getUser();
		$db		= $this->getDbo();
		
		$mode		  = array_key_exists('mode',$data) ? intval($data['mode']) : 0;
		$subject	  = array_key_exists('subject',$data) ? $data['subject'] : '';
		$receipt_id	  = array_key_exists('receipt',$data) ? $data['receipt'] : 0;

		if (empty($receipt_id)) {
		    $app->setUserState('com_seminarman.display.mail.data', $data);
		    $this->setError(JText::_('COM_SEMINARMAN_MAIL_NO_RECIPIENT_SELECTED'));
		    return false;
		}
		
		// $recurse	= array_key_exists('recurse',$data) ? intval($data['recurse']) : 0;
		$bcc		  = array_key_exists('bcc',$data) ? intval($data['bcc']) : 0;
		// $disabled	= array_key_exists('disabled',$data) ? intval($data['disabled']) : 0;
		$message_body = array_key_exists('message',$data) ? $data['message'] : '';
		
		$cc          = array_key_exists('cc',$data) ? trim($data['cc']) : '';
		
		$invoice	 = array_key_exists('invoice',$data) ? intval($data['invoice']) : 0;
		$certificate = array_key_exists('certificate',$data) ? intval($data['certificate']) : 0;
		$ics	 = array_key_exists('ics',$data) ? intval($data['ics']) : 0;
		$addattach	 = array_key_exists('addattach',$data) ? intval($data['addattach']) : 0;
		$course_docs = array_key_exists('course_docs',$data) ? intval($data['course_docs']) : 0;
		$attach      = array_key_exists('attach',$data) ? $data['attach'] : array();
		
		
		if ($cc <> '') {
			$cc = str_replace(';', ',', $cc);
			$cc_arr = array_filter(explode(',', $cc));
			$hasCC = true;
			jimport('joomla.mail.helper');
			foreach ($cc_arr as $cc_add) {
 			    if(!JMailHelper::isEmailAddress($cc_add)) {
 				    $app->enqueueMessage(JText::_('Invalid Email Address (CC)'),'warning');
                    $hasCC = false;
 			    }
			}
		} else {
			$hasCC = false;
		}

		$query = $db->getQuery(true);
		$query->select('a.id, a.course_id, a.email, a.invoice_filename_prefix AS prefix, a.invoice_number AS number, a.user_id, a.certificate_file, a.extra_attach_file, c.start_date, c.start_time, c.finish_date, c.finish_time, c.title AS course_title')
              ->from('#__seminarman_application AS a')
			  ->join('LEFT', '#__seminarman_courses AS c ON c.id = a.course_id')
              ->where('a.id IN ( '. join(',', $receipt_id) .' )');
		$db->setQuery($query);
		$receipts = $db->loadObjectList();
		
        $params = JComponentHelper::getParams('com_seminarman');
		
		$rows = array();
		
		foreach ($receipts as $receipt) {
			$receipt->bill_file = JPATH_ROOT.DS.$params->get('invoice_save_dir').DS.$receipt->prefix . $receipt->number . '.pdf';
			if (!empty($receipt->certificate_file)) {
			    $receipt->cert_file = JPATH_ROOT.DS.$params->get('invoice_save_dir').DS.$receipt->certificate_file;
			} else {
				$receipt->cert_file = null;
			}
			if (!empty($receipt->extra_attach_file)) {
				$receipt->extra_attach_file = JPATH_ROOT.DS.$params->get('invoice_save_dir').DS.$receipt->extra_attach_file;
			} else {
				$receipt->extra_attach_file = null;
			}
			$rows[] = $receipt->email;
			
			$receipt->course_docs = SMANFunctions::getCourseEmailAttachs($receipt->course_id);
			
		}

		// Check to see if there are any users in this group before we continue
		if (!count($rows)) {
			$app->setUserState('com_seminarman.display.mail.data', $data);			
			$this->setError(JText::_('COM_SEMINARMAN_MAIL_NO_USERS_COULD_BE_FOUND_IN_THIS_GROUP'));
			return false;
		}
		
		// Get email template
		$emailTemplate = $data['email_template'];
		
		if ($emailTemplate != 0) {
			$query = $db->getQuery(true);
			$query->select( '*' );
			$query->from( '#__seminarman_emailtemplate' );
			$query->where( 'templatefor=0' );
			$query->where( 'id=' . $emailTemplate );
			
			$db->setQuery($query);
			$template = $db->loadObject();
			
			if ($template) {
				$msgSubject = $template->subject;
				$msgBody = $template->body;
				$msgSender = array($user->email, $user->name);
				$msgRecipient = $template->recipient;
				$msgRecipientCC = '';
				if($hasCC == 1) {
					$msgRecipientCC = $cc_arr;
				}
				$msgRecipientBCC = $template->bcc;
				
				foreach($receipts as $r) {
					$data['applicationid'] = $r->id;
					$data['user_id'] = $r->user_id;
					$data['start_date'] = $r->start_date;
					$data['finish_date'] = $r->finish_date;
					$data['start_time'] = $r->start_time;
					$data['finish_time'] = $r->finish_time;
					$attach_tmp = $attach;
					if ($invoice == 1 && file_exists($r->bill_file)) {
						array_unshift($attach_tmp, $r->bill_file);
					}
					if ($certificate == 1 && file_exists($r->cert_file)) {
						array_unshift($attach_tmp, $r->cert_file);
					}
					// possible ics
					if ($params->get('ics_file_name') == 0) {
					    $ics_filename = "ical_course_" . $r->course_id . ".ics";
					} else {
					    $ics_filename = JFile::makeSafe(str_replace(array('Ä','Ö','Ü','ä','ö','ü','ß'), array('Ae','Oe','Ue','ae','oe','ue','ss'), html_entity_decode($r->course_title, ENT_QUOTES)) . '_' . $r->course_id . ".ics");
						$ics_filename = str_replace(' ', '_', $ics_filename);
					}
					$ics_filepath = JPATH_ROOT.DS.$params->get('invoice_save_dir').DS.$ics_filename;
					if ($ics == 1 && file_exists($ics_filepath)) {
						array_unshift($attach_tmp, $ics_filepath);
					}
					// additional attachment
					if ($addattach == 1 && file_exists($r->extra_attach_file)) {
						array_unshift($attach_tmp, $r->extra_attach_file);
					}
					// course docs attachment
					if ($course_docs == 1 && !empty($r->course_docs)) {
						$attach_tmp = array_merge($r->course_docs, $attach_tmp);
					}
										
					$rs = JHTMLSeminarman::sendEmailToUserApplication($data, $msgSubject, $msgBody, $msgSender, $msgRecipient, $msgRecipientCC, $msgRecipientBCC, $attach_tmp);
				}
			}
		} else {
			$params_users = JComponentHelper::getParams('com_users');
	
			// automatically removes html formatting
			if (!$mode) {
				$message_body = JFilterInput::getInstance()->clean($message_body, 'string');
			}
	
			// Check for a message body and subject
			if (!$message_body || !$subject) {
				$app->setUserState('com_seminarman.display.mail.data', $data);
				$this->setError(JText::_('COM_SEMINARMAN_MAIL_PLEASE_FILL_IN_THE_FORM_CORRECTLY'));
				return false;
			}
			
			foreach ($receipts as $r) {
				// Get the Mailer
				$mailer = JFactory::getMailer();
				
				// Build email message format.
				$mailer->setSender(array($user->email, $user->name));
				$mailer->setSubject($params_users->get('mailSubjectPrefix') . stripslashes($subject));
				$mailer->setBody($message_body . $params_users->get('mailBodySuffix'));
				$mailer->IsHTML($mode);
				$attach_tmp = $attach;
				
				// Add recipients
				if ($bcc == 1) {
					$mailer->addBCC($rows);
					// $mailer->addRecipient($app->getCfg('mailfrom'));
				} else {
					$mailer->addRecipient($r->email);
					
					if ($invoice == 1 && file_exists($r->bill_file)) {
						array_unshift($attach_tmp, $r->bill_file);
					}
					if ($certificate == 1 && file_exists($r->cert_file)) {
						array_unshift($attach_tmp, $r->cert_file);
					}
					// possible ics
					if ($params->get('ics_file_name') == 0) {
						$ics_filename = "ical_course_" . $r->course_id . ".ics";
					} else {
					    $ics_filename = JFile::makeSafe(str_replace(array('Ä','Ö','Ü','ä','ö','ü','ß'), array('Ae','Oe','Ue','ae','oe','ue','ss'), html_entity_decode($r->course_title, ENT_QUOTES)) . '_' . $r->course_id . ".ics");
						$ics_filename = str_replace(' ', '_', $ics_filename);
					}
					$ics_filepath = JPATH_ROOT.DS.$params->get('invoice_save_dir').DS.$ics_filename;
					if ($ics == 1 && file_exists($ics_filepath)) {
						array_unshift($attach_tmp, $ics_filepath);
					}
					// additional attachment
					if ($addattach == 1 && file_exists($r->extra_attach_file)) {
						array_unshift($attach_tmp, $r->extra_attach_file);
					}
					// course docs attachment
					if ($course_docs == 1 && !empty($r->course_docs)) {
						$attach_tmp = array_merge($r->course_docs, $attach_tmp);
					}
				}
				
    			if (!empty($attach_tmp))
					$mailer->addAttachment($attach_tmp);
				
				if($hasCC == 1) {
					$mailer->addCC($cc_arr);
				}
				
				// Send the Mail
				$rs	= $mailer->Send();
				
				if($bcc == 1) {
					break;
				}
			}
		}
		
		//Remove tmp attachment
	    JFile::delete($attach);
		

		// Check for an error
		if (JError::isError($rs)) {
			$app->setUserState('com_seminarman.display.mail.data', $data);
			$this->setError($rs->getError());
			return false;
		} elseif (empty($rs)) {
			$app->setUserState('com_seminarman.display.mail.data', $data);
			$this->setError(JText::_('COM_SEMINARMAN_MAIL_THE_MAIL_COULD_NOT_BE_SENT'));
			return false;
		} else {
			// Fill the data (specially for the 'mode', 'group' and 'bcc': they could not exist in the array
			// when the box is not checked and in this case, the default value would be used instead of the '0'
			// one)
			$data['mode']=$mode;
			$data['subject']=$subject;
			$data['receipt']=$receipt_id;
			// $data['recurse']=$recurse;
			$data['bcc']=$bcc;
			$data['message']=$message_body;
			$app->setUserState('com_seminarman.display.mail.data', array());
			$app->enqueueMessage(JText::plural('COM_SEMINARMAN_MAIL_EMAIL_SENT_TO_N_USERS', count($rows)),'message');
			return true;
		}
	}
	
    function uploadAttach($file) {
        $mainframe = JFactory::getApplication();

        $data = JRequest::getVar('jform', array(), 'post', 'array');
        JRequest::checkToken('request') or jexit('Invalid Token');
        
        jimport('joomla.utilities.date');

        jimport('joomla.client.helper');
        JClientHelper::setCredentialsFromRequest('ftp');

        jimport('joomla.filesystem.file');
        
        foreach ($file['name'] as $key => $name) {
	        $name = JFile::makeSafe($name);
	
	        if (isset($name) && !empty($name)) {
	
	            $path = COM_SEMINARMAN_FILEPATH . DS;
	
	            //$filename = seminarman_upload::sanitize($path, $name);
	            $filepath = JPath::clean(COM_SEMINARMAN_FILEPATH . DS . strtolower($name));
	            
	    		if (JFile::exists($filepath)) {
	    			JFile::delete($filepath);
	    		}
	
	            if (JFile::upload($file['tmp_name'][$key], $filepath)) {
					$data['attach'][] = $filepath;
					JRequest::setVar('jform', $data, 'post');
	            } else {
	                $this->setError(JText::_('COM_SEMINARMAN_OPERATION_FAILED'));
	            	return false;
	            }
	        }
        }
		return true;
    }
}
