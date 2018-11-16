<?php
/**
* Copyright (C) 2015 Open Source Group GmbH www.osg-gmbh.de
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

class seminarmanControlleruser extends seminarmanController
{

    function __construct($config = array())
    {
        parent::__construct($config);
        $this->childviewname = 'user';
        $this->parentviewname = 'users';
    }
    
    function edit_booking_rules() {
    	JRequest::setVar('layout', 'default_bookingrules_manage');
    	JRequest::setVar('hidemainmenu', 1);
    	JRequest::setVar('view', $this->childviewname);
    	parent::display();
    }  

    function apply()
    {
    	JRequest::checkToken() or jexit('Invalid Token');
    	$post    = JRequest::get('post');
    	$uid      = JRequest::getInt('uid', 0, 'post');
    	$this->setRedirect('index.php?option=com_seminarman&controller=user&task=edit_booking_rules&uid=' . $uid);
    }

    function save()
    {
    	JRequest::checkToken() or jexit('Invalid Token');
    	$this->setRedirect('index.php?option=com_seminarman&view=' . $this->parentviewname);
    } 

    function save_booking_rule()
    {
    	JRequest::checkToken() or jexit('Invalid Token');
    	$post    = JRequest::get('post');
    	$uid      = JRequest::getInt('user_id', 0, 'post');
    	
    	$model = $this->getModel($this->childviewname);
    	
    	if ($model->store_booking_rule($post)) {
    	    $this->setRedirect('index.php?option=com_seminarman&view=' . $this->childviewname . '&layout=modal&tmpl=component&content=viewbookingrules&reload_parent=1&uid='.$uid, JText::_('COM_SEMINARMAN_OPERATION_SUCCESSFULL'));
    	} else {
    		JError::raiseError(500, $model->getError());
    	}
    }
    
    function delete_booking_rule() {
    	JSession::checkToken('get') or jexit('Invalid Token');
    	
    	$rule_id = JRequest::getVar('rule_id');
    	$uid = JRequest::getVar('uid');
    	$model = $this->getModel($this->childviewname);
    	
    	if ($model->delete_booking_rule($rule_id)) {
    		$this->setRedirect('index.php?option=com_seminarman&controller=user&task=edit_booking_rules&uid=' . $uid, JText::_('COM_SEMINARMAN_OPERATION_SUCCESSFULL'));
    	} else {
    		JError::raiseError(500, $model->getError());
    	}
    }
        
    function cancel()
    {
    	JRequest::checkToken() or jexit('Invalid Token');
    	// $model = $this->getModel($this->childviewname);
    	$this->setRedirect('index.php?option=com_seminarman&view=' . $this->parentviewname);
    }
}