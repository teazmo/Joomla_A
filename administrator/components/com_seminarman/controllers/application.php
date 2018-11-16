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
jimport('joomla.application.component.controller');

class seminarmanControllerapplication extends seminarmanController
{

    function __construct($config = array())
    {
        parent::__construct($config);

        $this->registerTask('add', 'display');
        $this->registerTask('edit', 'display');
        $this->registerTask('apply', 'save');
        $this->childviewname = 'application';
        $this->parentviewname = 'applications';
    }

    function display( $cachable = false, $urlparams = false )
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
        $post       = JRequest::get('post');
        $cid        = JRequest::getVar('cid', array(0), 'post', 'array');
        $post['id'] = (int)$cid[0];
        $model      = $this->getModel($this->childviewname);
        $userId     = JRequest::getVar( 'user_id' , '' , 'POST' );
        
        if ($this->getTask() == 'setCourse') $post['id'] = 0;
        $isNew = ($post['id'] == 0);
        
        require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS . 'helpers' . DS . 'seminarman.php');
        $params = JComponentHelper::getParams('com_seminarman');
        
        if ($isNew) {
        	$post['invoice_number'] = 0-time();
        	$post['published'] = 1;
        	
        	$tempArray = array();
        	// datetime always sent as utc (gmdate)
        	$dataArray = array('date'=>gmdate('Y-m-d H:i:s'), 'user'=>JFactory::getUser()->username . " (" . strtolower(JText::_('COM_SEMINARMAN_CREATED')) . ")", 'status'=>$post['status']);
        	if(isset($post['price_per_attendee'])) {
        		if (trim($post['price_per_attendee']) != "") {
        			$post['price_per_attendee'] = str_replace(",", ".", $post['price_per_attendee']);
        			$dataArray['price'] = JText::sprintf('%.2f', round($post['price_per_attendee'], 2));
        		} else {
        			unset($post['price_per_attendee']);
        		}
        	}
        	if(isset($post['price_total'])) {
        		if (trim($post['price_total']) != "") {
        			$post['price_total'] = str_replace(",", ".", $post['price_total']);
        			$dataArray['price_total'] = JText::sprintf('%.2f', round($post['price_total'], 2));
        		} else {
        			unset($post['price_total']);
        		}
        	}
        	array_push($tempArray, $dataArray);
        	$post['params']['protocols'] = json_encode($tempArray);        	
        } else {       
	        $post['params'] = JRequest::getVar('params', null, 'post', 'array');
	        
	        if(isset($post['params']['protocols_history'])){
	        	$tempArray = json_decode($post['params']['protocols_history'], true);
	        	// datetime always sent as utc (gmdate)
	        	$dataArray = array('date'=>gmdate('Y-m-d H:i:s'), 'user'=>JFactory::getUser()->username, 'status'=>$post['status']);
	        	if(isset($post['price_per_attendee'])) {
	        		if (trim($post['price_per_attendee']) != "") {
	        			$post['price_per_attendee'] = str_replace(",", ".", $post['price_per_attendee']);
	        			$dataArray['price'] = JText::sprintf('%.2f', round($post['price_per_attendee'], 2));
	        		} else {
	        			unset($post['price_per_attendee']);
	        		}
	        	}
	        	if(isset($post['price_total'])) {
	        		if (trim($post['price_total']) != "") {
	        			$post['price_total'] = str_replace(",", ".", $post['price_total']);
	        			$dataArray['price_total'] = JText::sprintf('%.2f', round($post['price_total'], 2));
	        		} else {
	        			unset($post['price_total']);
	        		}
	        	} 
	        	array_push($tempArray, $dataArray);
	        	$post['params']['protocols'] = json_encode($tempArray);
	        	unset($post['params']['protocols_history']);
	        } else {
	        	$tempArray = array();
	        	// datetime always sent as utc (gmdate)
	        	$dataArray = array('date'=>gmdate('Y-m-d H:i:s'), 'user'=>JFactory::getUser()->username, 'status'=>$post['status']);
	        	if(isset($post['price_per_attendee'])) {
	        		if (trim($post['price_per_attendee']) != "") {
	        			$post['price_per_attendee'] = str_replace(",", ".", $post['price_per_attendee']);
	        			$dataArray['price'] = JText::sprintf('%.2f', round($post['price_per_attendee'], 2));
	        		} else {
	        			unset($post['price_per_attendee']);
	        		}
	        	}
	        	if(isset($post['price_total'])) {
	        		if (trim($post['price_total']) != "") {
	        			$post['price_total'] = str_replace(",", ".", $post['price_total']);
	        			$dataArray['price_total'] = JText::sprintf('%.2f', round($post['price_total'], 2));
	        		} else {
	        			unset($post['price_total']);
	        		}
	        	}
	        	array_push($tempArray, $dataArray);
	        	$post['params']['protocols'] = json_encode($tempArray);
	        }      
        }
        
        // price group
        if (SeminarmanFunctions::isSmanbookingPlgEnabled() && $params->get('advanced_booking')) {
        	$dispatcher=JDispatcher::getInstance();
        	JPluginHelper::importPlugin('seminarman');
        	$html_vars=$dispatcher->trigger('onPostManualBookingSelectPriceG',array($post));
        	if (isset($html_vars[0]) && !empty($html_vars[0])) {
        		if ($html_vars[0]['groups'] != 0) $post['groups'] = array($html_vars[0]['groups']);
        		$post['pricegroup'] = $html_vars[0]['pricegroup'];
        	}
        }        

    	// Process and save custom fields
    	$model = $this->getModel( 'application' );
    	$values	= array();
    	$customfields	= $model->getEditableCustomfields( $cid[0] );

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
    				if ($isNew && ($data['type'] == 'checkboxtos')) continue;
    				// If there are errors on the form, display to the user.
    				$message	= JText::sprintf('COM_SEMINARMAN_FIELD_N_CONTAINS_IMPROPER_VALUES' ,  $data['name'] );
    				
    				if ($this->getTask() == 'setCourse') {
    				    $redirect_id = $cid[0];
    				} else {
    				    $redirect_id = $post['id'];
    				}
    				
    				$this->setredirect( 'index.php?option=com_seminarman&controller=application&task=edit&cid[]=' . $redirect_id , $message , 'error' );

					return;
    			}
    		}
    	}
    	
    	if ($applicationid = $model->store($post))
    	{
    		$msg = JText::_('COM_SEMINARMAN_RECORD_SAVED');
    	}
    	else
    	{
    		$msg = JText::_('COM_SEMINARMAN_ERROR_SAVING');
    		$this->setredirect( 'index.php?option=com_seminarman&controller=application&task=edit&cid[]=' . $post['id'] , $msg , 'error' );
    	
    		return;
    	}
    	
    	//save data from custom fields
    	$model->saveCustomfields($applicationid, $userId, $values);

        $model->checkin();

        if ($this->getTask() == 'apply' || $this->getTask() == 'setCourse')
        {
        	$link = 'index.php?option=com_seminarman&controller=application&task=edit&cid[]='.$applicationid;
        	
        	// this part is for plugin manual booking
        	if ($this->getTask() == 'setCourse' && $model->setstatus($cid[0], 3)
        			&& $model->addComments($cid[0], JText::sprintf('PLG_SEMINARMAN_SMANBOOKING_APPLICATION_MOVED', $applicationid))
        			&& $model->update_protocol($cid[0], 3)) {
        				$msg .= '<br/>' . JText::sprintf('PLG_SEMINARMAN_SMANBOOKING_STATUS_CANCLED', $cid[0]);
        			}

        	if ( $post['invoice'] == 1 && ( $post['oldstatus'] != $post['status'] ) ) {
        		if ( $params->get('invoice_generate') == 1 ) {
        	
        			$invoice = JHTMLSeminarman::createInvoice( $post['id'] );
        	
        			$attachment = '';
        			if ( $params->get('invoice_attach') == 1 ) {
        				$attachment = $invoice;
        			}
        	
        			/* NOCH EMAIL VERSCHICKEN; DIE FUNKTION IM MODEL UMSCHREIBEN; IST AUS FRONTEND KOPIERT */
					list( $emaildata, $emailtemplate ) = $model->getEmailData( $applicationid );
        			$success = $model->sendemail( $emaildata, $emailtemplate, $attachment );
        		}
        	}
        }
        else
        	$link = 'index.php?option=com_seminarman&view=' . $this->parentviewname;
        
        $this->setRedirect($link, $msg);
    }


    function remove()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        
        $params = JComponentHelper::getParams('com_seminarman');
        if ($params->get('enable_bookings_deletable') == 1)
        {
	        $cid = JRequest::getVar('cid', array(), 'post', 'array');
	        JArrayHelper::toInteger($cid);
	
	        if (count($cid) < 1)
	            JError::raiseError(500, JText::_('COM_SEMINARMAN_SELECT_ITEM'));
	
	        $model = $this->getModel($this->childviewname);
	
	        if (!$model->delete($cid))
	            echo "<script> alert('" . $model->getError(true) ."'); window.history.go(-1); </script>\n";

	        $msg = JText::_('COM_SEMINARMAN_OPERATION_SUCCESSFULL');
        }
        else
        	$msg = '';
        
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
            echo "<script> alert('" . $model->getError(true) ."'); window.history.go(-1); </script>\n";

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
            echo "<script> alert('" . $model->getError(true) ."'); window.history.go(-1); </script>\n";

        $this->setRedirect('index.php?option=com_seminarman&view=' . $this->parentviewname);
    }
    
    function trash()
    {
    	JRequest::checkToken() or jexit('Invalid Token');
    	$cid = JRequest::getVar('cid', array(), 'post', 'array');
    	JArrayHelper::toInteger($cid);
    
    	if (count($cid) < 1)
    		JError::raiseError(500, JText::_('COM_SEMINARMAN_SELECT_ITEM'));
    
    	$model = $this->getModel($this->childviewname);
    
    	if (!$model->publish($cid, -2))
    		echo "<script> alert('" . $model->getError(true) ."'); window.history.go(-1); </script>\n";
    
    	$this->setRedirect('index.php?option=com_seminarman&view=' . $this->parentviewname, JText::_('COM_SEMINARMAN_OPERATION_SUCCESSFULL'));
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
        $this->setRedirect('index.php?option=com_seminarman&view=' . $this->parentviewname, JText::_('COM_SEMINARMAN_OPERATION_SUCCESSFULL'));
    }

	function changestatus()   {
		// Check for request forgeries
		JRequest::checkToken( 'get' ) or jexit( 'Invalid Token' );
		$cid  = JRequest::getVar( 'cid' );
		//JArrayHelper::toInteger($cid);
		$status = JRequest::getVar('status');
		// Checkin the booking
		$model = $this->getModel('application');

		if ($status == 5)  {
			$status = 0;
		} else {
			$status = $status + 1;
		}

		if($model->setstatus($cid, $status)){
			$model->update_protocol($cid, $status);
		}
		$msg = JText::_( 'COM_SEMINARMAN_STATUS_UPDATED' );
		$this->setRedirect( 'index.php?option=com_seminarman&view=applications', $msg  );
	}

	function setstatus( $cid = null, $status = null, $oldstatus = null, $invoice = null )   {
		// Check for request forgeries
		JSession::checkToken( 'get' ) or jexit( 'Invalid Token' );

        $params = JComponentHelper::getParams('com_seminarman');
		
        if ( !isset( $cid ) ) {
			$cid  = JRequest::getVar( 'cid' );
        }
        if ( !isset( $status ) ) {
			$status = JRequest::getVar('status');
        }
        if ( !isset( $oldstatus ) ) {
			$oldstatus = JRequest::getVar('old');
        }
        if ( !isset( $invoice ) ) {
			$invoice = JRequest::getVar('invoice');
        }
		
		// Checkin the booking
		$model = $this->getModel('application');
		if($model->setstatus($cid, $status)){
			$model->update_protocol($cid, $status);
		}
		
		if ( $invoice ) {
			if ( $params->get('invoice_generate') == 1 ) {
				require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS .
						'helpers' . DS . 'seminarman.php');
				
				$invoice = JHTMLSeminarman::createInvoice( $cid );
				
				$attachment = '';
				if ( $params->get('invoice_attach') == 1 ) {
					$attachment = $invoice;
				}

				/* NOCH EMAIL VERSCHICKEN; DIE FUNKTION IM MODEL UMSCHREIBEN; IST AUS FRONTEND KOPIERT */
				list( $emaildata, $emailtemplate ) = $model->getEmailData( $cid );
	   			$success = $model->sendemail( $emaildata, $emailtemplate, $attachment );
			}
		}
		
		$msg = JText::_( 'COM_SEMINARMAN_STATUS_UPDATED' );
		$this->setRedirect( 'index.php?option=com_seminarman&view=applications', $msg  );
	}
	
	function setstatusselected()   {
		// Check for request forgeries
		JRequest::checkToken( 'get' ) or jexit( 'Invalid Token' );

        $params = JComponentHelper::getParams('com_seminarman');
		$cids = JRequest::getVar('cid', array(), 'get', 'array');
	
		//JArrayHelper::toInteger($cid);
		$status = JRequest::getVar('status');
		$oldstatus = JRequest::getVar('old');
		$invoice = JRequest::getVar('invoice');
		// Checkin the booking
		$model = $this->getModel('application');
	
		foreach ( $cids as $cid ) {
			$oldstatus = $model->getstatus( $cid );
			
			if ( $invoice && ( $oldstatus == 4 || $oldstatus == 5 ) ) {
				if ( $params->get('invoice_generate') == 1 ) {
					require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS .
							'helpers' . DS . 'seminarman.php');
					
					$invoice = JHTMLSeminarman::createInvoice( $cid );
				
					$attachment = '';
					if ( $params->get('invoice_attach') == 1 ) {
						$attachment = $invoice;
					}

					/* NOCH EMAIL VERSCHICKEN; DIE FUNKTION IM MODEL UMSCHREIBEN; IST AUS FRONTEND KOPIERT */
					list( $emaildata, $emailtemplate ) = $model->getEmailData( $cid );
	   				$success = $model->sendemail( $emaildata, $emailtemplate, $attachment );
				}
			}
			
			$model->setstatus($cid, $status);
		}
	
		$msg .= JText::_( 'COM_SEMINARMAN_STATUS_UPDATED' );
		$this->setRedirect( 'index.php?option=com_seminarman&view=applications', $msg  );
	}
	
	function notify()
	{
        $cid = JRequest::getVar('cid', array(), 'post', 'array');
        JArrayHelper::toInteger($cid);

        if (count($cid) < 1)
            JError::raiseError(500, JText::_('SCOM_SEMINARMAN_SELECT_ITEM'));

        $cids = implode(',', $cid);
        
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('a.id, a.email')
        ->from('#__seminarman_application AS a')
        ->where('a.id IN ( '. $cids .' )');
        $db->setQuery($query);
        $result = $db->loadObjectList();
        
        $app = JFactory::getApplication();
        $app->setUserState('com_seminarman.call.mail.from', 'applications');
        
        $mailmodel = $this->getModel('Mail');
        // $mailmodel->start =  'application';
        $mailmodel->receipts = $result;
		// $state = $mailmodel->getState();
		$mailview = $this->getView('mail', 'html', 'SeminarmanView');
		$mailview->setModel($mailmodel, true);
		$mailview->display();
		// parent::display();
	}
	
	function notify_booking()
	{
        $cid = JRequest::getVar('cid', array(), 'post', 'array');
        $cid = Intval($cid[0]);
        // JArrayHelper::toInteger($cid);

        if (empty($cid))
            JError::raiseError(500, JText::_('SCOM_SEMINARMAN_SELECT_ITEM'));

        // $cids = implode(',', $cid);

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('a.id AS id, a.email AS email, a.status AS status, a.invoice_filename_prefix AS prefix, a.invoice_number AS number, c.title AS title')
              ->from('#__seminarman_application AS a')
			  ->join('LEFT', '#__seminarman_courses AS c ON c.id = a.course_id')
              ->where('a.id = '. $cid);
        $db->setQuery($query);
        $result = $db->loadObjectList();
        
        $app = JFactory::getApplication();
        $app->setUserState('com_seminarman.call.mail.from', 'application');
		
		$mailmodel = $this->getModel('Mail');
		// $mailmodel->start =  'application';
		$mailmodel->receipts = array($result[0]);
		
		$stati = intval($result[0]->status);
		if ($stati == 0) {
			$stati_text = JText::_( 'COM_SEMINARMAN_SUBMITTED' );
		} elseif ($stati == 1) {
			$stati_text = JText::_( 'COM_SEMINARMAN_PENDING' );
		} elseif ($stati == 2) {
			$stati_text = JText::_( 'COM_SEMINARMAN_PAID' );
		} elseif ($stati == 3) {
			$stati_text = JText::_( 'COM_SEMINARMAN_CANCELED' );
		} elseif ($stati == 4) {
			$stati_text = JText::_( 'COM_SEMINARMAN_WL' );
		} elseif ($stati == 5) {
			$stati_text = JText::_( 'COM_SEMINARMAN_AWAITING_RESPONSE' );
		}

		$course_title = $result[0]->title;
		$bill_number = $result[0]->number;
		
		$mailmodel->subject = JText::_( 'COM_SEMINARMAN_YOUR_BILL' ) . ' ' . $bill_number . ' ' .  JText::_( 'COM_SEMINARMAN_FOR_BOOKING' ) . ' "' . $course_title . '": ' . $stati_text;
		
		// $state = $mailmodel->getState();
		$mailview = $this->getView('mail', 'html', 'SeminarmanView');
		$mailview->setModel($mailmodel, true);
		$mailview->display();
		// parent::display();
	}

	function createEmailAttachment ( $cidpost = null ) {
	
		require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS . 'helpers' . DS . 'seminarman.php');
	
		$params = JComponentHelper::getParams('com_seminarman');
		//JRequest::checkToken() or jexit('Invalid Token');
		$post       = JRequest::get('post');
		$cids       = JRequest::getVar('cid', array(0), 'get', 'array');
		$post['id'] = (int)$cids[0];
		$userId     = JRequest::getVar( 'user_id' , '' , 'POST' );
	
		if ( isset( $cidpost ) ) {
			$cid = $cidpost;
		}
		else {
			$cid = $post['id'];
		}
	
		$ret = SeminarmanFunctions::createEmailAttachment( $cid );
		if ( $ret ) {
			$msg = JText::_('COM_SEMINARMAN_EMAIL_ATTACHMENT_CREATED_SUCCESSFULLY').$cid;
		}
		else {
			$msg = JText::_('COM_SEMINARMAN_EMAIL_ATTACHMENT_ERROR').$cid;
		}
	
		if ( !$cidpost ) {
			$link = 'index.php?option=com_seminarman&controller=application&task=edit&cid[]='.$cid;
	
			$this->setRedirect($link, $msg);
		}
		else {
			return true;
		}
	}
	
	function createEmailAttachments() {
		require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS . 'helpers' . DS . 'seminarman.php');
		SeminarmanFunctions::createEmailattachments();
	}	
	
	function createCertificate ( $cidpost = null ) {
		
		require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS . 'helpers' . DS . 'seminarman.php');
	
		$params = JComponentHelper::getParams('com_seminarman');
		//JRequest::checkToken() or jexit('Invalid Token');
		$post       = JRequest::get('post');
		$cids        = JRequest::getVar('cid', array(0), 'get', 'array');
		$post['id'] = (int)$cids[0];
		$userId     = JRequest::getVar( 'user_id' , '' , 'POST' );
	
		if ( isset( $cidpost ) ) {
			$cid = $cidpost;
		}
		else {
			$cid = $post['id'];
		}
	
		$ret = SeminarmanFunctions::createCertificate( $cid );
		if ( $ret ) {
			$msg = JText::_('COM_SEMINARMAN_CERTIFICATE_CREATED_SUCCESSFULLY').$cid;
		}
		else {
			$msg = JText::_('COM_SEMINARMAN_CERTIFICATE_ERROR').$cid;
		}
	
		if ( !$cidpost ) {
			$link = 'index.php?option=com_seminarman&controller=application&task=edit&cid[]='.$cid;
	
			$this->setRedirect($link, $msg);
		}
		else {
			return true;
		}
	}
	
	function createCertificates() {
		require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS . 'helpers' . DS . 'seminarman.php');
		SeminarmanFunctions::createCertificates();
	}
	
	function createInvoice() {
		require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS . 'helpers' . DS . 'seminarman.php');		
		$params = JComponentHelper::getParams('com_seminarman');
		if (SeminarmanFunctions::isSmanbookingPlgEnabled() && $params->get('advanced_booking')) {
			$dispatcher=JDispatcher::getInstance();
			JPluginHelper::importPlugin('seminarman');
			$dispatcher->trigger('onCreateManualBookingInvoice',array());			
		}
	}
	
	function createInvoices() {
		require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS . 'helpers' . DS . 'seminarman.php');		
		$params = JComponentHelper::getParams('com_seminarman');
		if (SeminarmanFunctions::isSmanbookingPlgEnabled() && $params->get('advanced_booking')) {
			$dispatcher=JDispatcher::getInstance();
			JPluginHelper::importPlugin('seminarman');
			$dispatcher->trigger('onCreateManualBookingInvoices',array());			
		}
	}
	
	function update_user_info() {
		$app = JFactory::getApplication();
		$app->setUserState('com_seminarman.selected.user', $_POST['user_id']);
		$this->setRedirect('index.php?option=com_seminarman&controller=application&task=add');		
	}

	function setCourse()
	{
		$app = JFactory::getApplication();
		
		// Get the posted values from the request.
		$data = JRequest::get('post');
		
		// Get the type.
		$type = $data['fieldcourse'];
	
		$type = json_decode(base64_decode($type));
		$title = isset($type->title) ? $type->title : null;
		$courseid = isset($type->id) ? $type->id : 0;

		$data['status'] = 0;
		$data['course_title'] = $title;
		$data['course_id'] = $courseid;
		
		$_POST = $data;
		
		$this->save();
	}
	
}