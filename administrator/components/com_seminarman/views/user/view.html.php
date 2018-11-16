<?php
/**
* Copyright (C) 2015 Open Source Group GmbH www.osg-gmbh.de
* @website http://www.osg-gmbh.de
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

jimport('joomla.application.component.view');

class seminarmanViewUser extends JViewLegacy {

	function display($tpl = null) {
		require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS .
				'helpers' . DS . 'seminarman.php');
		if(JHTMLSeminarman::UserIsCourseManager()){
			$mainframe = JFactory::getApplication();
			
			$db = JFactory::getDBO();
			$uri = JFactory::getURI();
			$document = JFactory::getDocument();
			$lang = JFactory::getLanguage();
			
			$document->addStyleSheet('components/com_seminarman/assets/css/seminarmanbackend.css');
			$jversion = new JVersion();
			$short_version = $jversion->getShortVersion();
			if (version_compare($short_version, "3.0", 'ge')) {
				$document->addStyleSheet('components/com_seminarman/assets/css/seminarmanbackend_j3.x.css');
			}
			if ($lang->isRTL())
				$document->addStyleSheet('components/com_seminarman/assets/css/seminarmanbackend_rtl.css');
			
			JToolBarHelper::title(JText::_('COM_SEMINARMAN').': '.JText::_('COM_SEMINARMAN_USER'), 'user');
			
			$requestURL = $uri->toString();
			$lists = array();
			$nullDate = $db->getNullDate();
			
			$viewcontent = JRequest::getVar('content');
			switch ($viewcontent) {
				case "viewbookingrules":
					$refresh_parent = JRequest::getVar("reload_parent");
					if ($refresh_parent) {
						$document->addScriptDeclaration('window.parent.document.location.reload(true);');						
					}
					$tpl = "bookingrules_overview";
					break;
				case "addbookingrule":
					$model = $this->getModel();
					$row = $this->get('Bookingrule');
					
					$lists['id'] = $row->id;
					$lists['title'] = $row->title;
					$lists['user_id'] = $row->user_id;
					$lists['rule_type'] = $row->rule_type;
					$lists['rule_option'] = $row->rule_option;
					$lists['created'] = $row->created;
					$lists['published'] = $row->published;
					$lists['archived'] = $row->archived;
					$lists['rule_text'] = $row->rule_text;
					$lists['attribs'] = $row->attribs;
					
					$lists['published_state'] = JHTML::_('select.booleanlist', 'published', '', $row->published);
					
					$tpl = "bookingrules_editrule";
					break;
				case "editbookingrule":
					$model = $this->getModel();
					$row = $this->get('Bookingrule');
						
					$lists['id'] = $row->id;
					$lists['title'] = $row->title;
					$lists['user_id'] = $row->user_id;
					$lists['rule_type'] = $row->rule_type;
					$lists['rule_option'] = $row->rule_option;
					$lists['created'] = $row->created;
					$lists['published'] = $row->published;
					$lists['archived'] = $row->archived;
					$lists['rule_text'] = $row->rule_text;
					$lists['attribs'] = $row->attribs;
						
					$lists['published_state'] = JHTML::_('select.booleanlist', 'published', '', $row->published);
						
					$tpl = "bookingrules_editrule";	
					break;				
				case "success";
				    $tpl = "success";
				    break;				
			}
			
			$userbookingrules = $this->get('UserBookingrules');
			
			$this->assignRef('requestURL', $requestURL);
			$this->assignRef('lists', $lists);
			$this->assignRef('nullDate', $nullDate);
			$this->assignRef('userbookingrules', $userbookingrules);
			
			parent::display($tpl);
			
		} else {
			$app = JFactory::getApplication();
			$app->redirect('index.php?option=com_seminarman', 'Only seminar manager group can access users.');
		}
	}
	
}