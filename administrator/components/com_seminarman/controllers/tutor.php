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
jimport('joomla.application.component.controller');

class seminarmanControllertutor extends seminarmanController
{

    function __construct($config = array())
    {
        parent::__construct($config);

        $this->registerTask('add', 'display');
        $this->registerTask('edit', 'display');
        $this->registerTask('apply', 'save');
        $this->childviewname = 'tutor';
        if( JHTMLSeminarman::UserIsCourseManager() ) {
        	$this->parentviewname = 'tutors';
        } else {
        	$this->parentviewname = 'seminarman';
        }
    }

    function display($cachable = false, $urlparams = false)
    {
    	
    	JRequest::setVar('layout', 'form');
 	
    	if($this->getTask() == ''){
    		JRequest::setVar('hidemainmenu', 0);
    		JRequest::setVar('view', $this->parentviewname);
    	 } else {
    	 	JRequest::setVar('hidemainmenu', 1);
    	 	JRequest::setVar('view', $this->childviewname);
    	 }
    	JRequest::setVar('edit', ($this->getTask() == 'edit'));
    	
    	$model = $this->getModel($this->childviewname);
    	$model->checkout();
        parent::display();
    }
    
    
    function save()
    {
    	JRequest::checkToken() or jexit('Invalid Token');
    
    	$model   = $this->getModel($this->childviewname);
    	$row     = JTable::getInstance('tutor', 'Table');
    	$post    = JRequest::get('post');
    	$id      = JRequest::getInt('id', 0, 'post');
    	$tmpl_id = JRequest::getInt('template_id', 0, 'post');
    	$task    = JRequest::getVar('task');
    	$file    = JRequest::getVar('logofilename', null, 'files', 'array');
    	$mainframe = JFactory::getApplication();
    	
    	$link_save   = JRoute::_('index.php?option=com_seminarman&view=tutors', false);
    	
    	jimport('joomla.mail.helper');
    	if ((!empty($post['email'])) && !JMailHelper::isEmailAddress($post['email'])) {
    		JError::raiseNotice('SOME_ERROR_CODE', JText::_('COM_SEMINARMAN_MAIL_ADDRESS_INVALID'));
    		// $this->setRedirect(JRoute::_('index.php?option=com_seminarman&controller=tutor&task=edit&cid[]='.$id, false));
    		// return false;
    	}
    
    	if (!$row->load($id)) {
    		$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $row->getError()), 'error');
    		$this->setRedirect($link_save);
    		return false;
    	}

    	if (!(is_null($file))) {
    		$row->logofilename = $model->UploadImage($file);
    	} elseif (isset($post['image_media']) && (!empty($post['image_media']))) {
    		$row->logofilename = $post['image_media'];
    	}
    	
        if (isset($post['image_remove'])) {
    		if ($post['image_remove'] == "1") {
    				$row->logofilename = "";
    		}
    	}
    	
    	if (!$row->id){
    		$post['ordering'] = $row->getNextOrder();
    	}
    	
    	if ((isset($post['juserstate']) && $post['juserstate'] == 1) && (isset($post['user_id']) && $post['user_id'] == 0)) {   		
    		
            JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_users' . DS . 'models' , 'UsersModel');
    	
            $modeljuser = JModelLegacy::getInstance( 'user', 'UsersModel' );
            
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('*')
                  ->from('#__seminarman_usergroups AS g')
                  ->where('g.sm_id = 2');
            $db->setQuery($query);
            $result = $db->loadAssoc();
          // get trainer group id
            $trainer_id = $result["jm_id"];
         
            if ($post['juser_option'] == 0) {
            // if create a new joomla user for trainer  
                $juser = Array();
             // $juser['isjuser'] = $post['juserstate'];
                $juser['name'] = $post['firstname'] . ' ' . $post['lastname'];
                $juser['username'] = $post['user_name'];
                $juser['password'] = $post['jpassword1'];
                $juser['password2'] = $post['jpassword2'];
                $juser['email'] = $post['jemail'];
                $juser['sendEmail'] = '0';
                $juser['block'] = '0';
                $juser['id'] = 0; 
            
                // put the new joomla user into trainer group
                $juser['groups'] = array($trainer_id);
            
                $jstate = $modeljuser->getState();
                $jstate->set('user.id', 0);
                // var_dump($modeljuser);
                // exit;
                if($modeljuser->save($juser)){
            	    jimport('joomla.user.helper');
                    $joomid = JUserHelper::getUserId($juser['username']);
            	    $post['user_id'] = $joomid;
                }else{
            	    JError::raiseNotice('SOME_ERROR_CODE', JText::_('COM_SEMINARMAN_ERROR_SAVE_JOOMLA_ACC'));
            	    $this->setRedirect(JRoute::_('index.php?option=com_seminarman&controller=tutor&task=edit&cid[]='.(int)$row->id, false));
            	    return false;
                }
            } elseif($post['juser_option'] == 1){
            // if select a joomla user for trainer
                $juser_select = $post['juser_id'];
	            jimport('joomla.user.helper');
	            // add the selected joomla user to trainer group
	            if($juser_select > 0) {
	                if(JUserHelper::addUserToGroup($juser_select, $trainer_id)){
	            	    $post['user_id'] = $juser_select;
	                } else {
            	        JError::raiseNotice('SOME_ERROR_CODE', JText::_('COM_SEMINARMAN_ERROR_ASSIGN_JOOMLA_ACC'));
            	        $this->setRedirect(JRoute::_('index.php?option=com_seminarman&controller=tutor&task=edit&cid[]='.(int)$row->id, false));
            	        return false;	            	
	                }
	            } else {
            	    JError::raiseNotice('SOME_ERROR_CODE', JText::_('COM_SEMINARMAN_ERROR_INVALID_JOOMLA_ACC'));
            	    $this->setRedirect(JRoute::_('index.php?option=com_seminarman&controller=tutor&task=edit&cid[]='.(int)$row->id, false));
            	    return false;	            	
	            }	            
            }
    	} 
    	
    	// allow html tags
    	$post['description'] = JRequest::getVar('description', '', 'post', 'string', JREQUEST_ALLOWRAW);
    	
        if (!$row->save($post)) {
    		$this->setMessage(JText::_('JLIB_APPLICATION_ERROR_SAVE_FAILED', $row->getError()), 'error');
    		$this->setRedirect($link_save);
    		return false;
    	} 

        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('seminarman');
        
        // fire vmengine
        $results = $dispatcher->trigger('onProcessTrainer', array($post));
    	
    	$template_remove = JRequest::getVar('template_remove', array(), 'post', 'array');
    	if (!empty($template_remove)) {
    		$model->removeTemplates($template_remove, (int)$row->id);
    	}
    
    	if ($tmpl_id) {
    		$template_prio = JRequest::getInt('template_prio', 0, 'post');
    		$model->addTemplate($tmpl_id, $template_prio, (int)$row->id);
    	}
    	
    	// save custom fields value for tutor
    	$editfields = $model->getEditableCustomfields((int)$row->id);
    	
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
    				return $mainframe->redirect(JRoute::_('index.php?option=com_seminarman&controller=tutor&task=edit&cid[]='.(int)$row->id, false));
    			}
    		}
    	}
    	
    	$model->saveCustomfields((int)$row->id, $customFields);
    
    	$model->checkin();
    	
    	$this->setMessage(JText::_('COM_SEMINARMAN_RECORD_SAVED'));
    	if ($task == 'apply')
    		$this->setRedirect(JRoute::_('index.php?option=com_seminarman&controller=tutor&task=edit&cid[]='.(int)$row->id, false));
    	else
    		$this->setRedirect($link_save);
    
    	return true;
    }


    function remove()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        
        $cid = JRequest::getVar('cid', array(), 'post', 'array');
        JArrayHelper::toInteger($cid);

        if (count($cid) < 1)
        {
            JError::raiseError(500, JText::_('COM_SEMINARMAN_SELECT_ITEM'));
        }

        $model = $this->getModel($this->childviewname);

        if ($model->delete($cid)) {
        	$msg = JText::_('COM_SEMINARMAN_OPERATION_SUCCESSFULL');
        } else {
        	$msg = $model->getError();
        }

        $this->setRedirect('index.php?option=com_seminarman&view=' . $this->parentviewname, $msg);
    }


    function publish()
    {

        JRequest::checkToken() or jexit('Invalid Token');
        $cid = JRequest::getVar('cid', array(), 'post', 'array');
        JArrayHelper::toInteger($cid);

        if (count($cid) < 1)
        {
            JError::raiseError(500, JText::_('COM_SEMINARMAN_SELECT_ITEM'));
        }

        $model = $this->getModel($this->childviewname);

        if (!$model->publish($cid, 1))
        {
            echo "<script> alert('" . $model->getError(true) .
                "'); window.history.go(-1); </script>\n";
        }

        $this->setRedirect('index.php?option=com_seminarman&view=' . $this->parentviewname);
    }


    function unpublish()
    {

        JRequest::checkToken() or jexit('Invalid Token');
        $cid = JRequest::getVar('cid', array(), 'post', 'array');
        JArrayHelper::toInteger($cid);

        if (count($cid) < 1)
        {
            JError::raiseError(500, JText::_('COM_SEMINARMAN_SELECT_ITEM'));
        }

        $model = $this->getModel($this->childviewname);

        if (!$model->publish($cid, 0))
        {
            echo "<script> alert('" . $model->getError(true) .
                "'); window.history.go(-1); </script>\n";
        }

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
        $msg = 'COM_SEMINARMAN_OPERATION_SUCCESSFULL';
        $this->setRedirect('index.php?option=com_seminarman&view=' . $this->parentviewname, $msg);
    }

    function goback()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $this->setRedirect('index.php?option=com_seminarman&view=settings');
    }
    
    function import() {
    	jimport('joomla.filesystem.file');
    	jimport('joomla.mail.helper');
    	
    	$mainframe = JFactory::getApplication();
    	$link_save   = JRoute::_('index.php?option=com_seminarman&view=tutors', false);
    	
    	$file_uno = JPATH_ROOT . DS . 'tmp' . DS . '001.csv';
    	$file_dos = JPATH_ROOT . DS . 'tmp' . DS . '002.csv';
    	
    	$type = JRequest::getVar('type');
    	
    	if ($type == "001") {
    		$file_to_import = $file_uno;
    	} elseif ($type == "002") {
    		$file_to_import = $file_dos;
    	} else {
    		$this->setMessage('File type failed.', 'error');
    		$this->setRedirect($link_save);
    		return false;
    	}
    	
    	if (JFile::exists($file_to_import)){
    		if (($handle = fopen($file_to_import, "r")) !== FALSE) {
    			while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
    				$model   = $this->getModel($this->childviewname);
    				$row     = JTable::getInstance('tutor', 'Table');
    				// array data by type 001:
    				// 0: company
    				// 1: branche
    				// 2: salutation
    				// 3: first name
    				// 4: last name
    				// 5: street
    				// 6: postcode
    				// 7: city
    				// 8: telephone
    				// 9: fax
    				// 10: mobile
    				// 11: email
    				// 12: homepage
    				
    				// array data by type 002:
    				// 0: branch
    				// 1: first name
    				// 2: last name
    				// 3: display name
    				// 4: salutation
    				// 5: street
    				// 6: postcode
    				// 7: city
    				// 8: telephone
    				// 9: mobile
    				// 10: fax
    				// 11: email
    				// 12: homepage
    				
    				// required fields
    				if ($type == "001") {
    				    $tutor_email = $data[11];
    				    $tutor_first_name = $data[3];
    				    $tutor_last_name = $data[4];
    				    $tutor_salutation = $data[2];
    				    $tutor_display_name = $tutor_salutation . ' ' . $tutor_first_name . ' ' . $tutor_last_name;
    				} elseif ($type == "002") {
    					$tutor_email = $data[11];
    					$tutor_first_name = $data[1];
    					$tutor_last_name = $data[2];
    					$tutor_salutation = $data[4]; 
    					$tutor_display_name = $data[3];
    				}
    				    				
    				$read_lines = 1;
    				$imported_lines = 1;
    				if ((!empty($tutor_email) && JMailHelper::isEmailAddress($tutor_email)) && !empty($tutor_first_name) && !empty($tutor_last_name) && !empty($tutor_salutation)) {
                        $id = 0;
                        $tutor_ordering = $row->getNextOrder();
                        if (!$row->load($id)) {
                        	$this->setMessage('CSV Line ' . $read_lines . ': ' . JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $row->getError()), 'error');
                        	// $this->setRedirect($link_save);
                        	// return false;
                        } else {
                        	JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_users' . DS . 'models' , 'UsersModel');
                        	 
                        	$modeljuser = JModelLegacy::getInstance( 'user', 'UsersModel' );
                        	
                        	$db = JFactory::getDbo();
                        	$query = $db->getQuery(true);
                        	$query->select('*')
                        	->from('#__seminarman_usergroups AS g')
                        	->where('g.sm_id = 2');
                        	$db->setQuery($query);
                        	$result = $db->loadAssoc();
                        	// get trainer group id
                        	$trainer_id = $result["jm_id"]; 
                        	
                        	// create a new joomla user for trainer
                        	$juser = Array();
                        	$juser['name'] = $tutor_first_name . ' ' . $tutor_last_name;
                        	$juser['username'] = $tutor_email;
                        	$password = self::generatePassword();
                        	$juser['password'] = $password;
                        	$juser['password2'] = $password;
                        	$juser['email'] = $tutor_email;
                        	$juser['sendEmail'] = '0';
                        	$juser['block'] = '0';
                        	$juser['id'] = 0;
                        	
                        	// put the new joomla user into trainer group
                        	$juser['groups'] = array($trainer_id);
                        	
                        	$jstate = $modeljuser->getState();
                        	$jstate->set('user.id', 0);
                        	// var_dump($juser);
                        	// exit;
                        	if($modeljuser->save($juser)){
                        		jimport('joomla.user.helper');
                        		$joomid = JUserHelper::getUserId($juser['username']);
                        		$tutor_user_id = $joomid;
                        	}else{
                        		$tutor_user_id = '';
                        		JError::raiseNotice('SOME_ERROR_CODE', 'CSV Line ' . $read_lines . ': ' . JText::_('COM_SEMINARMAN_ERROR_SAVE_JOOMLA_ACC'));
                        		// $this->setRedirect(JRoute::_('index.php?option=com_seminarman&controller=tutor&task=edit&cid[]='.(int)$row->id, false));
                        		// return false;
                        	}

                        	// create tutor only if joomla user is created
                        	if (!empty($tutor_user_id)) {
                        		$tutor = array();
                        		$tutor['title'] = $tutor_display_name;
                        		$tutor['firstname'] = $tutor_first_name;
                        		$tutor['lastname'] = $tutor_last_name;
                        		$tutor['salutation'] = $tutor_salutation;
                        		$tutor['email'] = $tutor_email;
                        		$tutor['user_id'] = $tutor_user_id;
                        		$tutor['ordering'] = $tutor_ordering;
                        		$tutor['published'] = 1;
                        		// optional
                        		if ($type == "001") {
                        		    $tutor['comp_name'] = $data[0];
                        		} elseif ($type == "002") {
                        			$tutor['comp_name'] = '';
                        		}
                        		if (!$row->save($tutor)) {
                        			$this->setMessage('CSV Line ' . $read_lines . ': ' . JText::_('JLIB_APPLICATION_ERROR_SAVE_FAILED', $row->getError()), 'error');
                        			// $this->setRedirect($link_save);
                        			// return false;
                        		} else {
                        			$imported_lines += 1;
                        			// tutor successfully created, now update his/her qualified course templates.
                        			$db = JFactory::getDbo();
                        			$query_tmpl = $db->getQuery(true); 
                        			$query_tmpl->select('id')
                        			->from('#__seminarman_templates');
                        			$db->setQuery($query_tmpl);
                        			$result_tmpl = $db->loadColumn();
                        			foreach ($result_tmpl As $tmpl_id) {
                        			    $model->addTemplate($tmpl_id, 0, (int)$row->id);
                        			}
                        			
                        			// send the second email
                        			jimport('joomla.mail.helper');
                        			
                        			$message_body = JText::_('COM_SEMINARMAN_TUTOR_EMAIL_BODY_AFTER_IMPORT');
                        			
                        			$mailer = JFactory::getMailer();
                        			$mailer->setSender(array(JText::_('COM_SEMINARMAN_TUTOR_EMAIL_SENDER_NAME_AFTER_IMPORT'), JText::_('COM_SEMINARMAN_TUTOR_EMAIL_SENDER_ADDRESS_AFTER_IMPORT')));
                        			$mailer->setSubject(JText::_('COM_SEMINARMAN_TUTOR_EMAIL_SUBJECT_AFTER_IMPORT'));
                        			$mailer->setBody($message_body);
                        			$mailer->IsHTML(true);
                        			$mailer->addRecipient($tutor_email);
                        			$rs	= $mailer->Send();
                        		}
                        	}
                        }
    					$read_lines += 1;
    				}
    			    $model->checkin();
    			}
    			fclose($handle);
    		}
    	}
    	$this->setMessage('Read Lines: ' . $read_lines . ' | Successfully Imported Tutors: ' . $imported_lines, 'information');
    	$this->setRedirect($link_save); 
    }
    
    function generatePassword($length = 8) {
    	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789&§$!%()=?_-';
    	$count = mb_strlen($chars);
    
    	for ($i = 0, $result = ''; $i < $length; $i++) {
    		$index = rand(0, $count - 1);
    		$result .= mb_substr($chars, $index, 1);
    	}
    	return $result;
    }
    
}

?>