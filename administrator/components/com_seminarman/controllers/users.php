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

class seminarmanControllerusers extends seminarmanController
{
	function __construct($config = array())
	{
		parent::__construct($config);
		$this->childviewname = 'user';
		$this->parentviewname = 'users';
		
		$this->registerTask('viewfulllist', 'viewfulllist');
		$this->registerTask('closefulllist', 'closefulllist');
	}
	
	function viewfulllist() {
		$this->setRedirect('index.php?option=com_seminarman&view=' . $this->parentviewname . '&show=fulllist');
	}
	
	function closefulllist() {
		$this->setRedirect('index.php?option=com_seminarman&view=' . $this->parentviewname);
	}
}