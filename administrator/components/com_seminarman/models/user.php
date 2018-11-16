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

jimport('joomla.application.component.model');

require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS .
		'helpers' . DS . 'seminarman.php');

class seminarmanModeluser extends JModelLegacy {
	
	var $_bookingrule = null;
	
	function __construct()
	{
		parent::__construct();
		
		$user_id = JRequest::getVar("uid");
		if (empty($user_id)) $user_id = 0;   // should never happen ;)
		$this->setUserId($user_id);
		
		$rule_id = JRequest::getVar("rule_id");
		if (empty($rule_id)) $rule_id = 0;
		$this->setRuleId($rule_id);
	}

	function setUserId($id)
	{
	
		$this->_userid = $id;
		$this->_userbookingrules = null;
	}	
	
	function setRuleId($id)
	{
	
		$this->_ruleid = $id;
		$this->_bookingrule = null;
	}
	
    function getBookingrule()
    {
        if ($this->_loadBookingrule())
        {
            // currently nothing special here
        } else
            $this->_initBookingrule();
        
        return $this->_bookingrule;
    }


    function _loadBookingrule()
    {

        if (empty($this->_bookingrule))
        {
			$query = $this->_db->getQuery(true);
			$query->select( '*' );
			$query->from( '#__seminarman_user_rules' );
			$query->where( 'id ='. $this->_ruleid );
			
            $this->_db->setQuery( $query );
            $this->_bookingrule = $this->_db->loadObject();

            return (boolean)$this->_bookingrule;
        }
        return true;
    }

    function _initBookingrule()
    {

        if (empty($this->_bookingrule))
        {
        	$user = JFactory::getUser();
        	$params = JComponentHelper::getParams( 'com_seminarman' );
            $createdate = JFactory::getDate();
            $nullDate = $this->_db->getNullDate();

            $rule = new stdClass();
            $rule->id = 0;
            $rule->title = null;
            $rule->user_id = $this->_userid;
            $rule->rule_type = 1;  // 1 for booking rule, currently only this
            $rule->rule_option = 'category_rule';
            $rule->rule_text = null;
            $rule->created = $nullDate;
            $rule->published = 0;
            $rule->archived = 0;
            $rule->attribs = null;
            $this->_bookingrule = $rule;
            
            return (boolean)$this->_bookingrule;
        }
        return true;
    }
    
    function getUserBookingRules() {
    	if ($this->_loadUserBookingrules()) {
    		// right now nothing special
    	} else {
    		// right now nothing special
    	}
    	return $this->_userbookingrules;
    }
    
    function _loadUserBookingrules() {
    	if (empty($this->_userbookingrules))
    	{
    		$query = $this->_db->getQuery(true);
    		$query->select( '*' );
    		$query->from( '#__seminarman_user_rules' );
    		$query->where( 'user_id=' . $this->_userid );
    		
    		$this->_db->setQuery($query);
    		$this->_userbookingrules = $this->_db->loadObjectList();
    		
    		return (boolean)$this->_userbookingrules;
    	}
    	return true;    	
    }
    
    function store_booking_rule($data) {    	
    	if (empty($data)) {
    	    return false;	
    	} else {
    		$rule = array();
    		$rule['category'] = $data['category'];
    		$data['start_date'] = trim($data['start_date']);
    		$data['finish_date'] = trim($data['finish_date']);
    	    if (!empty($data['start_date'])) {
    			$rule['start_date'] = SeminarmanFunctions::formatDateToSQL($data['start_date'].' 00:00:00');
    		} else {
    			$rule['start_date'] = "";
    		}
    		if (!empty($data['finish_date'])) {
    			$rule['finish_date'] = SeminarmanFunctions::formatDateToSQL($data['finish_date'].' 23:59:59');  // den ganzen Tag einschließen, local time
    		} else {
    			$rule['finish_date'] = "";
    		}
    		$rule['amount'] = $data['amount'];
    		$data['rule_text'] = json_encode($rule);
    		
    		JRequest::checkToken() or jexit('Invalid Token');
    		$user_rule = $this->getTable('seminarman_user_rules', '');
    		
    		if ($data['id'] == 0) {  // add new rule
    			$data['created'] = gmdate('Y-m-d H:i:s');
    			if (!$user_rule->bind($data)) {
    				$this->setError($this->_db->getErrorMsg());
    				return false;
    			}
    			if (!$user_rule->store()){
    				$this->setError($this->_db->getErrorMsg());
    				return false;
    			}
    			return true;
    		} else {  // edit rule
    			if (!$user_rule->bind($data)) {
    				$this->setError($this->_db->getErrorMsg());
    				return false;
    			}
    			if (!$user_rule->store()){
    				$this->setError($this->_db->getErrorMsg());
    				return false;
    			}
    			return true;
    		}
    	}
    }
    
    function delete_booking_rule($rule_id) {
    	$db = JFactory::getDBO();
    	$query = $db->getQuery(true);
    	
    	$query->delete( $db->quoteName( '#__seminarman_user_rules' ) )
    		  ->where( $db->quoteName( 'id' ) . ' = ' . $rule_id );
    	$db->setQuery( $query );
    	
    	if ( !$db->execute() ) {
    		$this->setError($db->getErrorMsg());
    		return false;
    	}
    	
    	return true;
    }
}