<?php
/**
*
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
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');
jimport( 'joomla.html.parameter' );

class SeminarmanViewSeminarman extends JViewLegacy{
    function display($tpl = null)
    {
    	$mainframe = JFactory::getApplication();
    	$document = JFactory::getDocument();
    	$menus = JFactory::getApplication()->getMenu();
    	$menu = $menus->getActive();
    	$model = $this->getModel();
    	$params = $mainframe->getParams('com_seminarman');
    	$uri = JFactory::getURI();
    	$site_url = JURI::root();
    	
    	$lang = JFactory::getLanguage();
    	seminarman_html::addSiteStyles($this);
    	
    	$pathway = $mainframe->getPathWay();
    	
    	$limit = $mainframe->getUserStateFromRequest('com_seminarman.' . $this->getLayout() .'.limit', 'limit', $params->def('limit', 0), 'int');
    	$tmpl_limit = $mainframe->getUserStateFromRequest('com_seminarman.' . $this->getLayout() .'.tmpl_limit', 'tmpl_limit', $params->def('tmpl_limit', 0), 'int');
    	
    	$this->assign('action', $uri->toString());
    	$this->assignRef('params', $params);    	
    	$this->assignRef('siteurl', $site_url);
    	
        parent::display($tpl);
        
    }
    
}

?>
