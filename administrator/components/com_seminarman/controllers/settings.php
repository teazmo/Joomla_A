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

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');


/**
 * Seminarman Component Tags Controller
 */
class SeminarmanControllerSettings extends SeminarmanController
{
	/**
	 * Constructor
	 *
	 * @since 1.0
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra task
		$this->registerTask( 'add'  ,		 	'edit' );
		$this->registerTask( 'apply', 			'save' );
		$this->registerTask( 'applycss', 	'savecss' );
		$this->registerTask( 'applyxml', 	'savexml' );        
	}

	/**
	 * logic for cancel an action
	 *
	 * @access public
	 * @return void
	 * @since 1.0
	 */
	function cancel()
	{
		JRequest::checkToken() or jexit( 'Invalid Token' );
		$this->setRedirect( 'index.php?option=com_seminarman&view=settings');
	}

	/**
	 * Logic to create the view for the edit categoryscreen
	 *
	 * @access public
	 * @return void
	 * @since 1.0
	 */
	function edit( )
	{
		JRequest::setVar( 'view', 'tag' );
		JRequest::setVar( 'hidemainmenu', 1 );

		$model 	= $this->getModel('tag');
		$user	= JFactory::getUser();

		// Error if checkedout by another administrator
		if ($model->isCheckedOut( $user->get('id') )) {
			$this->setRedirect( 'index.php?option=com_seminarman&view=tags', JText::_( 'COM_SEMINARMAN_EDITED_BY_ANOTHER_ADMIN' ) );
		}

		$model->checkout( $user->get('id') );

		parent::display();
	}

	/**
	 *  Add new Tag from course screen
	 *
	 */
	function addtag(){
		$name 	= JRequest::getString('name', '');
		$model 	= $this->getModel('tag');
		$model->addtag($name);
	}

	function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );
		$mainframe = JFactory::getApplication();
		$params = JRequest::getVar( 'params', array(), 'post', 'array' );
		if (is_array( $params )) {
			$txt = array();
			foreach ( $params as $k=>$v ) {
				$txt[] = "$k=$v";
			}
			$params = implode( "\n", $txt );
	
			$db = JFactory::getDBO();
			$params = $db->Quote( $params );			

			$query = $db->getQuery(true);
			 
			$fields = array( $db->quoteName('params'). "=".$params );
			$conditions = array( $db->quoteName('link') . " = 'option=com_seminarman'", 'parent=0' );
			 
			$query->update( $db->quoteName( '#__components' ) )->set( $fields )->where( $conditions );
			 
			$db->setQuery($query);
			$db->execute();
		}
	
		$msg	= JText::_( 'COM_SEMINARMAN_CHANGES_SAVED' );
		$link	= 'index.php?option=com_seminarman&view=settings';
		$mainframe->redirect( $link, $msg );
		return true;
	}

	/**
	 * Saves the css
	 *
	 */
	function savecss()
	{
		JRequest::checkToken() or jexit( 'Invalid Token' );

		// Initialize some variables
		$option			= JRequest::getVar('option');
		$filename		= JRequest::getVar('filename', '', 'post', 'cmd');
		$filecontent	= JRequest::getVar('filecontent', '', '', '', JREQUEST_ALLOWRAW);

		if (!$filecontent) {
			$mainframe->redirect('index.php?option=com_seminarman', JText::_('OPERATION FAILED').': '.JText::_('CONTENT EMPTY'));
		}

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
		$ftp = JClientHelper::getCredentials('ftp');

		$file = JPATH_SITE.DS.'components'.DS.'com_seminarman'.DS.'assets'.DS.'css'.DS.$filename;

		// Try to make the css file writeable
		if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0755')) {
			JError::raiseNotice('SOME_ERROR_CODE', 'COM_SEMINARMAN_COULD_NOT_MAKE_FILE_WRITABLE');
		}

		jimport('joomla.filesystem.file');
		$return = JFile::write($file, $filecontent);

		// Try to make the css file unwriteable
		// Why??? Comment on 18.01.2016
		// It doesn't make sense actually and may always cause problems by the update installation, we comment it out
		// if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0555')) {
		//	JError::raiseNotice('SOME_ERROR_CODE', 'COM_SEMINARMAN_COULD_NOT_MAKE_FILE_UNWRITABLE');
		// }

		if ($return)
		{
			$task = JRequest::getVar('task');
			switch($task)
			{
				case 'applycss' :
					$this->setRedirect('index.php?option=com_seminarman&view=editcss', JText::_('COM_SEMINARMAN_CHANGES_SAVED'));
					break;

				case 'savecss'  :
				default         :
					$this->setRedirect('index.php?option=com_seminarman&view=settings', JText::_('COM_SEMINARMAN_CHANGES_SAVED') );
					break;
			}
		} else {
			$this->setRedirect('index.php?option=com_seminarman&view=settings', JText::_('COM_SEMINARMAN_OPERATION_FAILED').': '.JText::sprintf('COM_SEMINARMAN_FAILED_TO_OPEN_FILE_FOR_WRITING', $file));
		}
	}

	/**
	 * Saves the css
	 *
	 */
	function savexml()
	{

		JRequest::checkToken() or jexit( 'Invalid Token' );

		// Initialize some variables
		$option			= JRequest::getVar('option');
		$filename		= JRequest::getVar('filename', '', 'post', 'cmd');
		$filecontent	= JRequest::getVar('filecontent', '', '', '', JREQUEST_ALLOWRAW);

		if (!$filecontent) {
			$this->setRedirect('index.php?option=com_seminarman', JText::_('OPERATION FAILED').': '.JText::_('CONTENT EMPTY'));
		}

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
		$ftp = JClientHelper::getCredentials('ftp');

		$file = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_seminarman'.DS.'tables'.DS.$filename;

		// Try to make the css file writeable
		if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0755')) {
			JError::raiseNotice('SOME_ERROR_CODE', 'COM_SEMINARMAN_COULD_NOT_MAKE_FILE_WRITABLE');
		}

		jimport('joomla.filesystem.file');
		$return = JFile::write($file, $filecontent);

		// Try to make the css file unwriteable
		if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0555')) {
			JError::raiseNotice('SOME_ERROR_CODE', 'COM_SEMINARMAN_COULD_NOT_MAKE_FILE_UNWRITABLE');
		}

		if ($return)
		{
			$task = JRequest::getVar('task');
			switch($task)
			{
				case 'applyxml' :
					$this->setRedirect('index.php?option=com_seminarman&view=editvirtualtables', JText::_('COM_SEMINARMAN_CHANGES_SAVED'));
					break;

				case 'savexml'  :
				default         :
					$this->setRedirect('index.php?option=com_seminarman&view=settings', JText::_('COM_SEMINARMAN_CHANGES_SAVED') );
					break;
			}
		} else {
			$this->setRedirect('index.php?option=com_seminarman&view=settings', JText::_('COM_SEMINARMAN_OPERATION_FAILED').': '.JText::sprintf('COM_SEMINARMAN_FAILED_TO_OPEN_FILE_FOR_WRITING', $file));
		}
	}
	
	function createmanagergroup(){
        $db = JFactory::getDbo();
        // check if the db is up to date and if a valid manager group already exists
        $query_manager = $db->getQuery(true);
        $query_manager->select('jm_id')	
                      ->from('#__seminarman_usergroups')
                      ->where('sm_id = 1');
        $db->setQuery($query_manager);
        $foundManager = $db->loadResult();
	    if (is_null($foundManager)) {
        	JError::raiseWarning('SOME_ERROR_CODE', 'Failed! It seems that your database was not updated properly, please reinstall the component!');
        	$this->setRedirect('index.php?option=com_seminarman&view=settings'); 
        	return;        	    	
        }

        if ($foundManager > 0) {
            // is it valid?
            $query_jm = $db->getQuery(true);
            $query_jm->select('*')
                     ->from('#__usergroups')
                     ->where('id = ' . $foundManager);
            $db->setQuery($query_jm);
            $result_jm = $db->loadAssoc();
            if (!is_null($result_jm)) {
        	    $this->setRedirect('index.php?option=com_seminarman&view=settings', 'A seminar manager group with ID ' . $foundManager . ' exists already in Joomla user group tree!');
        	    return;                 
            }
        }
		
		// JRequest::checkToken() or jexit( 'Invalid Token' );
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_users' . DS . 'models' , 'UsersModel');
    	
        $model = JModelLegacy::getInstance( 'group', 'UsersModel' );
        $data = array ( 'id' => 0,
                 'title' => 'OSG SeminarManager',
                 'parent_id' => '1' );
        	
        if($model->save($data)){
            $group_name = $data["title"];
            $query = $db->getQuery(true);
            $query->select('*')
                  ->from('#__usergroups AS g')
                  ->where('g.title = "'.$group_name.'"');
            $db->setQuery($query);
            $result = $db->loadAssoc();

            $group_id = $result["id"];
            
            $query = $db->getQuery(true);
            
            $fields = array( "`jm_id`=" . $group_id );
            $conditions = array( "`sm_id`=1 LIMIT 1" );
            
            $query->update( $db->quoteName( '#__seminarman_usergroups' ) )->set( $fields )->where( $conditions );
            
            $db->setQuery($query);
            $db->execute();
            
        	$this->setRedirect('index.php?option=com_seminarman&view=settings','The seminar manager group with id "' . $group_id . '" was successfully created in Joomla user group tree!');
        } else {
        	JError::raiseNotice('SOME_ERROR_CODE', 'Failed! Maybe a group named "OSG SeminarManager" exists in the Joomla user group tree? If yes, rename it first!');
        	$this->setRedirect('index.php?option=com_seminarman&view=settings');
        }    	
	}
	
	function createtrainergroup(){
		$db = JFactory::getDbo();
	    // check if the db is up to date and if a valid manager group already exists
        $query_trainer = $db->getQuery(true);
        $query_trainer->select('jm_id')	
                      ->from('#__seminarman_usergroups')
                      ->where('sm_id = 2');
        $db->setQuery($query_trainer);
        $foundTrainer = $db->loadResult();
	    if (is_null($foundTrainer)) {
        	JError::raiseWarning('SOME_ERROR_CODE', 'Failed! It seems that your database was not updated properly, please reinstall the component!');
        	$this->setRedirect('index.php?option=com_seminarman&view=settings'); 
        	return;        	    	
        }

        if ($foundTrainer > 0) {
            // is it valid?
            $query_jm = $db->getQuery(true);
            $query_jm->select('*')
                     ->from('#__usergroups')
                     ->where('id = ' . $foundTrainer);
            $db->setQuery($query_jm);
            $result_jm = $db->loadAssoc();
            if (!is_null($result_jm)) {
        	    $this->setRedirect('index.php?option=com_seminarman&view=settings', 'A seminar trainer group with ID ' . $foundTrainer . ' exists already in Joomla user group tree!');
        	    return;                 
            }
        }
        		
		// JRequest::checkToken() or jexit( 'Invalid Token' );
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_users' . DS . 'models' , 'UsersModel');
    	
        $model = JModelLegacy::getInstance( 'group', 'UsersModel' );
        $data = array ( 'id' => 0,
                 'title' => 'OSG SeminarTrainer',
                 'parent_id' => '1' );	
        if($model->save($data)){
            $group_name = $data["title"];
            $query = $db->getQuery(true);
            $query->select('*')
                  ->from('#__usergroups AS g')
                  ->where('g.title = "'.$group_name.'"');
            $db->setQuery($query);
            $result = $db->loadAssoc();

            $group_id = $result["id"];
            
            $query = $db->getQuery(true);
            
            $fields = array( "`jm_id`=" . $group_id );
            $conditions = array( "`sm_id`=2 LIMIT 1" );
            
            $query->update( $db->quoteName( '#__seminarman_usergroups' ) )->set( $fields )->where( $conditions );
            
            $db->setQuery($query);
            $db->execute();
            
            $this->setRedirect('index.php?option=com_seminarman&view=settings','The course trainer group with id "' . $group_id . '" was successfully created in Joomla user group tree!');
        }else{
        	JError::raiseNotice('SOME_ERROR_CODE', 'Failed! Maybe a group named "OSG SeminarTrainer" exists in the Joomla user group tree? If yes, rename it first!');
        	$this->setRedirect('index.php?option=com_seminarman&view=settings');   	
        }
	}
	
	function powerupdate(){
        $db = JFactory::getDBO();
        $query1 = "CREATE TABLE IF NOT EXISTS `#__seminarman_usergroups` (
                  `id` int(10) NOT NULL AUTO_INCREMENT,
                  `jm_id` int(10) NOT NULL,
                  `sm_id` int(10) NOT NULL,
                  `title` varchar(100) NOT NULL,
                  PRIMARY KEY (`id`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
        $query2 = "INSERT IGNORE INTO `#__seminarman_usergroups` (`id`, `jm_id`, `sm_id`, `title`) VALUES
                  (1, 0, 1, 'Seminar Manager'),
                  (2, 0, 2, 'Seminar Trainer');";
        $query3 = "ALTER TABLE `#__seminarman_courses` ADD `min_attend` INT( 11 ) NOT NULL DEFAULT '0' AFTER `currency_price`;";
        $query4 = "ALTER TABLE `#__seminarman_courses` ADD `price2` DOUBLE AFTER `price`;";
        $query5 = "ALTER TABLE `#__seminarman_courses` ADD `price3` DOUBLE AFTER `price2`;";
        $query6 = "ALTER TABLE `#__seminarman_templates` ADD `min_attend` INT( 11 ) NOT NULL DEFAULT '0' AFTER `currency_price`;";
        $query7 = "ALTER TABLE `#__seminarman_templates` ADD `price2` DOUBLE AFTER `price`;";
        $query8 = "ALTER TABLE `#__seminarman_templates` ADD `price3` DOUBLE AFTER `price2`;";
     // $query6 = "ALTER TABLE `#__seminarman_tutor` ADD `user_name` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER `user_id`;";
        $query9 = "ALTER TABLE `#__seminarman_courses` ADD `theme_points` INT( 11 ) NOT NULL DEFAULT '0' AFTER `id_experience_level`;";
        $query10 = "ALTER TABLE `#__seminarman_templates` ADD `theme_points` INT( 11 ) NOT NULL DEFAULT '0' AFTER `id_experience_level`;";
        $query11 = "CREATE TABLE IF NOT EXISTS `#__seminarman_pricegroups` (
                  `id` int(10) NOT NULL AUTO_INCREMENT,
                  `gid` int(10) NOT NULL,
                  `jm_groups` varchar(5120) NOT NULL,
                  `reg_group` int(10) NOT NULL,
                  `title` varchar(100) DEFAULT NULL,
                  `calc_mathop` varchar(8) NOT NULL,
                  `calc_value` float NOT NULL DEFAULT 0,
                  PRIMARY KEY (`id`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;"; 
        $query12 = "INSERT IGNORE INTO `#__seminarman_pricegroups` (`id`, `gid`, `jm_groups`, `reg_group`, `title`, `calc_mathop`, `calc_value`) VALUES
                  (1, 2, '', 0, 'Price 2', '-%', 0),
                  (2, 3, '', 0, 'Price 3', '-%', 0);"; 
        $query13 = "ALTER TABLE `#__seminarman_application` ADD `pricegroup` varchar(100) AFTER `attendees`;";      
        $db->setQuery($query1);
        $result = $db->query();
        $db->setQuery($query2);
        $result = $db->query();
        $db->setQuery($query3);
        $result = $db->query();
        $db->setQuery($query4);
        $result = $db->query();
        $db->setQuery($query5);
        $result = $db->query();
        $db->setQuery($query6);
        $result = $db->query();
        $db->setQuery($query7);
        $result = $db->query();
        $db->setQuery($query8);
        $result = $db->query();
        $db->setQuery($query9);
        $result = $db->query();
        $db->setQuery($query10);
        $result = $db->query();
        $db->setQuery($query11);
        $result = $db->query();
        $db->setQuery($query12);
        $result = $db->query();
        $db->setQuery($query13);
        $result = $db->query();        
        
        $this->setRedirect('index.php?option=com_seminarman&view=settings','The database has been updated successfully!');	
	}
	
	function vmpowerupdate(){
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('seminarman');
		
		// fire vmengine
		$results = $dispatcher->trigger('vmpowerupdate', null);
	}
	
	function assignloginrights($managerid = NULL, $trainerid = NULL){
		if (is_null($managerid)) $managerid = $this->getmanagergroupid();
		if (is_null($trainerid)) $trainerid = $this->gettrainergroupid();
		if ($managerid>0 && $trainerid>0){
			$asset	= JTable::getInstance('asset');
			if ($asset->loadByName('root.1'))
			{
				$rules_json = $asset->rules;
				// var_dump($rules_json);				
                $rules_array = json_decode($rules_json, true);
                $rules_array['core.login.site'][$managerid] = 1;
                $rules_array['core.login.site'][$trainerid] = 1;
                $rules_array['core.login.admin'][$managerid] = 1;
                $rules_array['core.login.admin'][$trainerid] = 1;
                $rules_json = json_encode($rules_array);
                // var_dump($rules_json);
                // exit;               
				$asset->rules = $rules_json;

				if (!$asset->check() || !$asset->store()) {
					JError::raiseNotice('SOME_ERROR_CODE', $asset->getError());
					$this->setRedirect('index.php?option=com_seminarman&view=settings');
					return false;
				}
				$this->setRedirect('index.php?option=com_seminarman&view=settings','The both groups of seminar manager and trainer can login in site and backend now!');
				return true;
			}
			else
			{
				$this->setError(JText::_('COM_CONFIG_ERROR_ROOT_ASSET_NOT_FOUND'));
				$this->setRedirect('index.php?option=com_seminarman&view=settings');
				return false;
			}
		}else{
        	JError::raiseNotice('SOME_ERROR_CODE', 'Failed! Have you already created the both groups of seminar manager and trainer?');
        	$this->setRedirect('index.php?option=com_seminarman&view=settings');
        	return false;            
		}
	}
	
	function setmodulevisibleforgroups($managerid = NULL, $trainerid = NULL){
		$jversion = new JVersion();
		$short_version = $jversion->getShortVersion();
		if (is_null($managerid)) $managerid = $this->getmanagergroupid();
		if (is_null($trainerid)) $trainerid = $this->gettrainergroupid();
		if ($managerid>0 && $trainerid>0){
			$level	= JTable::getInstance('viewlevel');
			if ($level->load(3)){
				$rules_json = $level->rules;
				// var_dump($rules_json);
				$rules_array = json_decode($rules_json, true);
				// var_dump($rules_array);
				if(!in_array(intval($managerid), $rules_array)) array_push($rules_array, intval($managerid));
				if(!in_array(intval($trainerid), $rules_array)) array_push($rules_array, intval($trainerid));
				// var_dump($rules_array);
				$rules_json = json_encode($rules_array);
                // var_dump($rules_json);
                $level->rules = $rules_json;
				if (!$level->check() || !$level->store()) {
					JError::raiseNotice('SOME_ERROR_CODE', $level->getError());
					$this->setRedirect('index.php?option=com_seminarman&view=settings');
					return false; 
				}
				// for joomla 2.5 it is enough now
				if (version_compare($short_version, "3.0", 'lt')) {
					$this->setRedirect('index.php?option=com_seminarman&view=settings','The backend menu and toolbar are visible for both groups of seminar manager and trainer!');
					return true;
				}
			}
			else
			{
				$this->setError('Zugriffseben nicht gefunden!');
				$this->setRedirect('index.php?option=com_seminarman&view=settings');
				return false;
			}			
			// for Jooma 3.x! the explicit registrated access level is required for some backend modules
			if (version_compare($short_version, "3.0", 'ge')) {
			if ($level->load(2)){
				$rules_json = $level->rules;
				// var_dump($rules_json);
				$rules_array = json_decode($rules_json, true);
				// var_dump($rules_array);
				if(!in_array(intval($managerid), $rules_array)) array_push($rules_array, intval($managerid));
				if(!in_array(intval($trainerid), $rules_array)) array_push($rules_array, intval($trainerid));
				// var_dump($rules_array);
				$rules_json = json_encode($rules_array);
				// var_dump($rules_json);
				$level->rules = $rules_json;
				if (!$level->check() || !$level->store()) {
					JError::raiseNotice('SOME_ERROR_CODE', $level->getError());
					$this->setRedirect('index.php?option=com_seminarman&view=settings');
					return false;
				}
				$this->setRedirect('index.php?option=com_seminarman&view=settings','The backend menu and toolbar are visible for both groups of seminar manager and trainer!');
				return true;
			}
			else
			{
				$this->setError('Zugriffseben nicht gefunden!');
				$this->setRedirect('index.php?option=com_seminarman&view=settings');
				return false;
			}
			}
	    }else{
        	JError::raiseNotice('SOME_ERROR_CODE', 'failed! Have you already created the both groups of seminar manager and trainer?');
        	$this->setRedirect('index.php?option=com_seminarman&view=settings');  
        	return false;
		}			
	}
	
	function assigncomrights($managerid = NULL, $trainerid = NULL){
		if (is_null($managerid)) $managerid = $this->getmanagergroupid();
		if (is_null($trainerid)) $trainerid = $this->gettrainergroupid();
		if ($managerid>0 && $trainerid>0){
			$asset	= JTable::getInstance('asset');
			if ($asset->loadByName('com_seminarman'))
			{
				$rules_json = $asset->rules;
				// var_dump($rules_json);				
                $rules_array = json_decode($rules_json, true);
                $rules_array['core.manage'][$managerid] = 1;
                $rules_array['core.manage'][$trainerid] = 1;
                $rules_json = json_encode($rules_array);
                // var_dump($rules_json);
                // exit;               
				$asset->rules = $rules_json;

				if (!$asset->check() || !$asset->store()) {
					JError::raiseNotice('SOME_ERROR_CODE', $asset->getError());
					$this->setRedirect('index.php?option=com_seminarman&view=settings');
					return false; 
				}
				$this->setRedirect('index.php?option=com_seminarman&view=settings','Upgrade successfully!');
				return true;
			}
			else
			{
				$this->setError(JText::_('COM_CONFIG_ERROR_ROOT_ASSET_NOT_FOUND'));
				$this->setRedirect('index.php?option=com_seminarman&view=settings');
				return false;
			}
		}else{
        	JError::raiseNotice('SOME_ERROR_CODE', 'Failed! Have you already created the both groups of seminar manager and trainer?');
        	$this->setRedirect('index.php?option=com_seminarman&view=settings'); 
        	return false;
		}
	}
	
	function upgradewizard() {
	  	$this->createmanagergroup();
	  	$this->createtrainergroup();
	  	$this->assignloginrights();
	  	$this->setmodulevisibleforgroups();
	  	$this->assigncomrights();
	}
	
	function createmainvmcategory() {
		
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('seminarman');
		
		// fire vmengine
		$results = $dispatcher->trigger('createmainvmcategory', null);
	}

	function getmanagergroupid(){
		$db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*')
              ->from('#__seminarman_usergroups AS g')
              ->where('g.sm_id = 1');
        $db->setQuery($query);
        $result= $db->loadAssoc();
        if (!is_null($result)){
        	// is it valid?
        	$query_jm = $db->getQuery(true);
        	$query_jm->select('*')
        	         ->from('#__usergroups')
        	         ->where('id = ' . $result['jm_id']);
        	$db->setQuery($query_jm);
        	$result_jm = $db->loadAssoc();
        	if (!is_null($result_jm)) {
        		// valid joomla group
                return $result['jm_id'];
        	} else {
        		return 0;
        	}
        }else{
        	return 0;
        }
	}
	
	function gettrainergroupid(){
		$db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*')
              ->from('#__seminarman_usergroups AS g')
              ->where('g.sm_id = 2');
        $db->setQuery($query);
        $result= $db->loadAssoc();
        if (!is_null($result)){
        	// is it valid?
        	$query_jm = $db->getQuery(true);
        	$query_jm->select('*')
        	         ->from('#__usergroups')
        	         ->where('id = ' . $result['jm_id']);
        	$db->setQuery($query_jm);
        	$result_jm = $db->loadAssoc();
        	if (!is_null($result_jm)) {
        		// valid joomla group
                return $result['jm_id'];
        	}else{
        		return 0;
        	}
        }else{
        	return 0;
        }
	}
	
	function savePricegroups() {
		$db = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		
	    $priceg2 = JRequest::getVar('sec_price', array(), 'post', 'array');
	    JArrayHelper::toInteger($priceg2);
	    $priceg3 = JRequest::getVar('thr_price', array(), 'post', 'array');
	    JArrayHelper::toInteger($priceg3);
	    $priceg4 = JRequest::getVar('fou_price', array(), 'post', 'array');
	    JArrayHelper::toInteger($priceg4);
	    $priceg5 = JRequest::getVar('fif_price', array(), 'post', 'array');
	    JArrayHelper::toInteger($priceg5);
	    
	    $price2_reg = JRequest::getVar('id_usergroup2', '0', 'post');
	    $price3_reg = JRequest::getVar('id_usergroup3', '0', 'post');
	    $price4_reg = JRequest::getVar('id_usergroup4', '0', 'post');
	    $price5_reg = JRequest::getVar('id_usergroup5', '0', 'post');
	    
	    $intersect23 = array_intersect($priceg2, $priceg3);
	    $intersect24 = array_intersect($priceg2, $priceg4);
	    $intersect25 = array_intersect($priceg2, $priceg5);
	    $intersect34 = array_intersect($priceg3, $priceg4);
	    $intersect35 = array_intersect($priceg3, $priceg5);
	    $intersect45 = array_intersect($priceg4, $priceg5);
	    
	    if($intersect23||$intersect24||$intersect25||$intersect34||$intersect35||$intersect45){
        	JError::raiseWarning('SOME_ERROR_CODE', JText::_('COM_SEMINARMAN_ERROR_ASSIGN_GROUP_IN_BOTH'));
        	$this->setRedirect('index.php?option=com_seminarman&view=settings'); 
        	return;	    	
	    }
	    
	    if (!(in_array($price2_reg, $priceg2)) && ($price2_reg != 0)) {
        	JError::raiseWarning('SOME_ERROR_CODE', JText::_('COM_SEMINARMAN_ERROR_REG_GROUP_2_NOT_IN_LIST'));
        	$this->setRedirect('index.php?option=com_seminarman&view=settings'); 
        	return;	    	
	    }
	    
		if (!(in_array($price3_reg, $priceg3)) && ($price3_reg != 0)) {
        	JError::raiseWarning('SOME_ERROR_CODE', JText::_('COM_SEMINARMAN_ERROR_REG_GROUP_3_NOT_IN_LIST'));
        	$this->setRedirect('index.php?option=com_seminarman&view=settings'); 
        	return;	    	
	    }
	    
	    if (!(in_array($price4_reg, $priceg4)) && ($price4_reg != 0)) {
	    	JError::raiseWarning('SOME_ERROR_CODE', JText::_('COM_SEMINARMAN_ERROR_REG_GROUP_4_NOT_IN_LIST'));
	    	$this->setRedirect('index.php?option=com_seminarman&view=settings');
	    	return;
	    }
	    
	    if (!(in_array($price5_reg, $priceg5)) && ($price5_reg != 0)) {
	    	JError::raiseWarning('SOME_ERROR_CODE', JText::_('COM_SEMINARMAN_ERROR_REG_GROUP_5_NOT_IN_LIST'));
	    	$this->setRedirect('index.php?option=com_seminarman&view=settings');
	    	return;
	    }
	    
	    $price2_json = json_encode($priceg2);
	    $price3_json = json_encode($priceg3);
	    $price4_json = json_encode($priceg4);
	    $price5_json = json_encode($priceg5);

	    $price2_name = JRequest::getVar('price2_title', 'Price 2', 'post');
	    $price3_name = JRequest::getVar('price3_title', 'Price 3', 'post');	
	    $price4_name = JRequest::getVar('price4_title', 'Price 4', 'post');
	    $price5_name = JRequest::getVar('price5_title', 'Price 5', 'post');    
	    
	    $price2_math = JRequest::getVar('price2_mathop', '-%', 'post');
	    $price3_math = JRequest::getVar('price3_mathop', '-%', 'post');
	    $price4_math = JRequest::getVar('price4_mathop', '-%', 'post');
	    $price5_math = JRequest::getVar('price5_mathop', '-%', 'post');
	    
	    $price2_wert = doubleval(str_replace(",", ".", JRequest::getVar('price2_value', 0, 'post')));
	    $price3_wert = doubleval(str_replace(",", ".", JRequest::getVar('price3_value', 0, 'post')));
	    $price4_wert = doubleval(str_replace(",", ".", JRequest::getVar('price4_value', 0, 'post')));
	    $price5_wert = doubleval(str_replace(",", ".", JRequest::getVar('price5_value', 0, 'post')));
	    
	    $query_update2 = "UPDATE `#__seminarman_pricegroups` SET `jm_groups`='" . $price2_json . "', `reg_group`=" . $price2_reg . ", `title`='" . $price2_name . "', `calc_mathop`='" . $price2_math . "', `calc_value`=" . $price2_wert . " WHERE `gid`=2";
	    $query_update3 = "UPDATE `#__seminarman_pricegroups` SET `jm_groups`='" . $price3_json . "', `reg_group`=" . $price3_reg . ", `title`='" . $price3_name . "', `calc_mathop`='" . $price3_math . "', `calc_value`=" . $price3_wert . " WHERE `gid`=3";
	    $query_update4 = "UPDATE `#__seminarman_pricegroups` SET `jm_groups`='" . $price4_json . "', `reg_group`=" . $price4_reg . ", `title`='" . $price4_name . "', `calc_mathop`='" . $price4_math . "', `calc_value`=" . $price4_wert . " WHERE `gid`=4";
	    $query_update5 = "UPDATE `#__seminarman_pricegroups` SET `jm_groups`='" . $price5_json . "', `reg_group`=" . $price5_reg . ", `title`='" . $price5_name . "', `calc_mathop`='" . $price5_math . "', `calc_value`=" . $price5_wert . " WHERE `gid`=5";
	    
        $db->setQuery($query_update2);
        if ($result2 = $db->query()) {
            $u2 = true;	
        } else {
            $u2 = false;
        	JError::raiseWarning('SOME_ERROR_CODE', JText::_('COM_SEMINARMAN_ERROR_SAVE_GROUP_2'));
        	$this->setRedirect('index.php?option=com_seminarman&view=settings'); 
        	return;	
        }	
        $db->setQuery($query_update3);
        if ($result3 = $db->query()) {
            $u3 = true;
        } else {
        	$u3 = false;
        	JError::raiseWarning('SOME_ERROR_CODE', JText::_('COM_SEMINARMAN_ERROR_SAVE_GROUP_3'));
        	$this->setRedirect('index.php?option=com_seminarman&view=settings'); 
        	return;
        }
        $db->setQuery($query_update4);
        if ($result4 = $db->query()) {
        	$u4 = true;
        } else {
        	$u4 = false;
        	JError::raiseWarning('SOME_ERROR_CODE', JText::_('COM_SEMINARMAN_ERROR_SAVE_GROUP_4'));
        	$this->setRedirect('index.php?option=com_seminarman&view=settings');
        	return;
        }
        $db->setQuery($query_update5);
        if ($result5 = $db->query()) {
        	$u5 = true;
        } else {
        	$u5 = false;
        	JError::raiseWarning('SOME_ERROR_CODE', JText::_('COM_SEMINARMAN_ERROR_SAVE_GROUP_5'));
        	$this->setRedirect('index.php?option=com_seminarman&view=settings');
        	return;
        }        
        
        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('seminarman');
        
        // fire vmengine
        $results = $dispatcher->trigger('onSavingPricegroups', array($price2_math, $price2_wert, $price3_math, $price3_wert));

	    if ($u2 && $u3) {
        	$this->setRedirect('index.php?option=com_seminarman&view=settings', JText::_('COM_SEMINARMAN_SUCCESS_SAVE_PRICE_GROUPS'));
        } else {
        	JError::raiseWarning('SOME_ERROR_CODE', JText::_('COM_SEMINARMAN_ERROR_SAVE_PRICE_GROUPS'));
        	$this->setRedirect('index.php?option=com_seminarman&view=settings');            	
        }        
        
	}
	
	function setRights() {
		if(($this->assignloginrights())&&($this->setmodulevisibleforgroups())&&($this->assigncomrights())) {
			$this->setRedirect('index.php?option=com_seminarman&view=settings','Upgrade successfully!');
		} else {
			$this->setError('Upgrade unsuccessfully!');
			$this->setRedirect('index.php?option=com_seminarman&view=settings');			
		}
	}
	
	function fixDB() {
		$db = JFactory::getDBO();
		$query1 = "ALTER TABLE `#__seminarman_courses` ADD `price4` DOUBLE AFTER `price3`;";
		$query2 = "ALTER TABLE `#__seminarman_courses` ADD `price5` DOUBLE AFTER `price4`;";
		$query3 = "ALTER TABLE `#__seminarman_templates` ADD `price4` DOUBLE AFTER `price3`;";
		$query4 = "ALTER TABLE `#__seminarman_templates` ADD `price5` DOUBLE AFTER `price4`;";
		$query5 = "INSERT IGNORE INTO `#__seminarman_pricegroups` (`id`, `gid`, `jm_groups`, `reg_group`, `title`, `calc_mathop`, `calc_value`) VALUES  (3, 4, '', 0, 'Price 4', '-%', 0),  (4, 5, '', 0, 'Price 5', '-%', 0);";
		$query6 = "CREATE TABLE IF NOT EXISTS `#__seminarman_fields_values_tutors` (
                  `tutor_id` int(11) NOT NULL,
                  `field_id` int(10) NOT NULL,
                  `value` text NOT NULL,
                  PRIMARY KEY (`tutor_id`,`field_id`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		
		$result_check = $db->getTableColumns('#__seminarman_courses');
		if (!(isset($result_check['price4']))) {
		  $db->setQuery($query1);
		  $result = $db->query();
		}
		if (!(isset($result_check['price5']))) {
		  $db->setQuery($query2);
		  $result = $db->query();
		}
		
		$result_check = $db->getTableColumns('#__seminarman_templates');
		if (!(isset($result_check['price4']))) {
		  $db->setQuery($query3);
		  $result = $db->query();
		}
		if (!(isset($result_check['price5']))) {
		  $db->setQuery($query4);
		  $result = $db->query();
		}
		
		$query_check = $db->getQuery(true);
		$query_check->select('*')
		->from('#__seminarman_pricegroups')
		->where('id=3');
		$db->setQuery($query_check);
		$result_check = $db->loadAssoc();
		if (is_null($result_check)) {
		  $db->setQuery($query5);
		  $result = $db->query();
		}
		
		$result_tables = $db->getTableList();
		$dVar=new JConfig();
		$dPre = $dVar->dbprefix;
		$result_check = in_array($dPre . 'seminarman_fields_values_tutors', $result_tables);
		if ($result_check == false) {
		  $db->setQuery($query6);
		  $result = $db->query();
		}
		
		$this->setRedirect('index.php?option=com_seminarman&view=settings','The database has been fixed successfully!');
	}
	
	function test()
	{
		$app = JFactory::getApplication();
		$app->enqueueMessage('Hola Claudy, repair now!', 'message');
		$this->setRedirect('index.php?option=com_seminarman&view=settings'); 
	}
}
