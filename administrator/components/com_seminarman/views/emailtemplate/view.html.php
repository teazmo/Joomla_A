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

jimport('joomla.application.component.view');

class seminarmanViewEmailtemplate extends JViewLegacy
{
	function display($tpl = null)
	{
		$document = JFactory::getDocument();
		$lang = JFactory::getLanguage();
		$document->addStyleSheet('components/com_seminarman/assets/css/seminarmanbackend.css');
		$jversion = new JVersion();
		$short_version = $jversion->getShortVersion();
		if (version_compare($short_version, "3.0", 'ge')) {
			$document->addStyleSheet('components/com_seminarman/assets/css/seminarmanbackend_j3.x.css');
		}
		if ($lang->isRTL())
		{
			$document->addStyleSheet('components/com_seminarman/assets/css/seminarmanbackend_rtl.css');
		}
		
		$lists = array();
		$emailtemplate = $this->get('data');
		$fields = $this->get('fields');
		
		//$templateforSelect = JHTML::_('select.genericlist', array(JText::_('COM_SEMINARMAN_BOOKING_CONFIRMATION'), JText::_('COM_SEMINARMAN_SALES_PROSPECT_NOTIFICATION')), 'templatefor', null, 'value', 'text', $emailtemplate->templatefor);
		$templateforSelect = JHTML::_('select.genericlist', array(JText::_('COM_SEMINARMAN_BOOKING_CONFIRMATION'), JText::_('COM_SEMINARMAN_SALES_PROSPECT_NOTIFICATION'), JText::_('COM_SEMINARMAN_WAITINGLIST_TEMPLATE')), 'templatefor', null, 'value', 'text', $emailtemplate->templatefor);
		
		$this->assignRef('lists', $lists);
		$this->assignRef('emailtemplate', $emailtemplate);
		$this->assignRef('fields', $fields);
		$this->assignRef('templateforSelect', $templateforSelect);
		parent::display($tpl);
    }
}