<?php
/**
*
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011-15 Open Source Group GmbH www.osg-gmbh.de
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
jimport('joomla.html.parameter');
jimport('joomla.html.pagination');

class SeminarmanViewCategory extends JViewLegacy{
    function display($tpl = null)
    {
        $mainframe = JFactory::getApplication();

        $Itemid = JRequest::getInt('Itemid');

        $document = JFactory::getDocument();
        $menus = JFactory::getApplication()->getMenu();
        $menu = $menus->getActive();
        $model = $this->getModel();
        $params = $mainframe->getParams('com_seminarman');
        $uri = JFactory::getURI();
        $lang = JFactory::getLanguage();
        
        $jversion = new JVersion();
        $short_version = $jversion->getShortVersion();

		seminarman_html::addSiteStyles($this);

        $pathway = $mainframe->getPathWay();

        $category = $this->get('Category');
        $courses = $this->get('Data');
        $archive_courses = $this->get('Archive');
        $templates = $this->get('LstOfProspects');
        
        $dispatcher = JDispatcher::getInstance();
        if ($params->get('trigger_onprepare_content')){
            JPluginHelper::importPlugin('content');
            $results = $dispatcher->trigger('onContentPrepare', array('com_seminarman.category', &$category, &$params));
        }
        
        $limit = $mainframe->getUserStateFromRequest('com_seminarman.' . $this->getLayout() .'.limit', 'limit', $params->def('limit', 0), 'int');
        $tmpl_limit = $mainframe->getUserStateFromRequest('com_seminarman.' . $this->getLayout() .'.tmpl_limit', 'tmpl_limit', $params->def('tmpl_limit', 0), 'int');
        $archive_limit = $mainframe->getUserStateFromRequest('com_seminarman.' . $this->getLayout() .'.archive_limit', 'archive_limit', $params->def('archive_limit', 0), 'int');
        
        $cats = new seminarman_cats((int)$category->id);
        $parents = $cats->getParentlist();
        $categories = $this->get('Childs');

        if ($params->get('enable_component_pathway') == 1) {
        foreach ($parents as $parent){
           $pathway->addItem($this->escape($parent->title), JRoute::_('index.php?option=com_seminarman&view=category&cid=' . $parent->categoryslug . '&Itemid=' . $Itemid));
        }
        }
        
        $print_link = JRoute::_('index.php?option=com_seminarman&view=category&cid=' . $category->slug . '&Itemid=' . $Itemid . '&pop=1&tmpl=component');

        $document->setTitle($params->get('page_title'));

        if ($category->meta_description){
            $document->setDescription($category->meta_description);
        }
        if ($category->meta_keywords){
            $document->setMetadata('keywords', $category->meta_keywords);
        }

        if ($params->get('show_feed_link', 1) == 1){
            $link = '&format=feed';
            $attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
            $document->addHeadLink(JRoute::_($link . '&type=rss'), 'alternate', 'rel', $attribs);
            $attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
            $document->addHeadLink(JRoute::_($link . '&type=atom'), 'alternate', 'rel', $attribs);
        }

        $count = count($courses);
        for($i = 0; $i < $count; $i++){
            $item = &$courses[$i];
            $item->count = $i;

            SMANFunctions::setCourse($item, $category, $Itemid, JText::_('COM_SEMINARMAN_DATE_FORMAT2'), JText::_('COM_SEMINARMAN_TIME_FORMAT2'));
        }
        
        $count = count($archive_courses);
        for($i = 0; $i < $count; $i++){
        	$item = &$archive_courses[$i];
        	$item->count = $i;
        
        	SMANFunctions::setCourse($item, $category, $Itemid, JText::_('COM_SEMINARMAN_DATE_FORMAT2'), JText::_('COM_SEMINARMAN_TIME_FORMAT2'));
        }
        
        $count = count($templates);
        for($i = 0; $i < $count; $i++){
        	$item = &$templates[$i];
        	$item->count = $i;
        	$link = JRoute::_($item->url);
        	$item->currency_price = $params->get('currency');
        
        	// calculate displayed price
        	if ($params->get('show_gross_price') == '1') {
        		$item->price += ($item->price / 100) * $item->vat;
        	}
        	$old_locale = setlocale(LC_NUMERIC, NULL);
        	setlocale(LC_NUMERIC, $lang->getLocale());
        	$item->price = JText::sprintf('%.2f', round($item->price, 2));
        	setlocale(LC_NUMERIC, $old_locale);
        	
        
        	$menuclass = 'category' . $params->get('pageclass_sfx');
        	if (version_compare($short_version, "3.0", 'ge')) {
        	    $itemParams = new JRegistry($item->attribs);
        	} else {
        		$itemParams = new JParameter($item->attribs);
        	}
        
        	if (($item->url) <> 'http://'){
        		switch ($params->get('target', $params->get('target'))){
        			case 1:
        				$item->link = '<a href="' . $link . '" target="_blank" class="' . $menuclass . '">' . JText::_('COM_SEMINARMAN_MORE_DETAILS'). '</a>';
        				break;
        
        			case 2:
        				$item->link = "<a href=\"#\" onclick=\"javascript: window.open('" . $link . "', '', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=780,height=550'); return false\" class=\"$menuclass\">" . JText::_('COM_SEMINARMAN_MORE_DETAILS'). "</a>\n";
        				break;
        
        			default:
        				$item->link = '<a href="' . $link . '" class="' . $menuclass . '">' . JText::_('COM_SEMINARMAN_MORE_DETAILS'). '</a>';
        			break;
        		}
        	}else
        	$item->link = null;
        
        	switch ($itemParams->get('show_sale', $params->get('show_sale'))){
        		case 1:
                	$sale_icon = 'components/com_seminarman/assets/images/' . $lang->getTag() . '_sale_item.png';
                    $item->show_sale_icon = '&nbsp;&nbsp;' . JHTML::_('image', (JFile::exists($sale_icon) ? $sale_icon : 'components/com_seminarman/assets/images/sale_item.png'), JText::_('COM_SEMINARMAN_SALE'));
        			break;
        		default:
        			$item->show_sale_icon = '';
        		break;
        	}
        	 
        }
        

        $lists = array();
        $lists['filter_order'] = $model->getState('filter_order');
        $lists['filter_order2'] = $model->getState('filter_order2');
        $lists['filter_order_Dir'] = $model->getState('filter_order_dir');
        $lists['filter_order_Dir2'] = $model->getState('filter_order_dir2');
        
        $lists['filter'] = JRequest::getString('filter');
        $lists['filter2'] = JRequest::getString('filter2');
        
        $experience_level[] = JHTML::_('select.option', '0', JText::_('COM_SEMINARMAN_ALL'), 'id', 'title');
        $titles = $this->get('titles');
        $experience_level = array_merge($experience_level, $titles);
        $lists['filter_experience_level'] = JHTML::_('select.genericlist', $experience_level, 'filter_experience_level', 'class="inputbox" size="1" ', 'id', 'title', JRequest::getString('filter_experience_level'));
        $lists['filter_experience_level2'] = JHTML::_('select.genericlist', $experience_level, 'filter_experience_level2', 'class="inputbox" size="1" ', 'id', 'title', JRequest::getString('filter_experience_level2'));
        
        $pageNav = new JPagination($this->get('Total'), JRequest::getInt('limitstart'), $limit );
        $pageNav->setAdditionalUrlParam('filter_order', $lists['filter_order']);
        $pageNav->setAdditionalUrlParam('filter_order_Dir', $lists['filter_order_Dir']);
        if ($params->get('filter')) {
          $filter_value = JRequest::getString('filter');
          $filter_experience_level_value = JRequest::getString('filter_experience_level');
          $pageNav->setAdditionalUrlParam('filter', $filter_value);
          $pageNav->setAdditionalUrlParam('filter_experience_level', $filter_experience_level_value);
        }
        
        $pageNav2 = new JPagination($this->get('TotalLstOfProspects'), JRequest::getInt('tmpl_limitstart'), $tmpl_limit, 'tmpl_');
        $pageNav2->setAdditionalUrlParam('filter_order2', $lists['filter_order2']);
        $pageNav2->setAdditionalUrlParam('filter_order_Dir2', $lists['filter_order_Dir2']);
        if ($params->get('filter')) {
        	$filter2_value = JRequest::getString('filter2');
        	$filter_experience_level2_value = JRequest::getString('filter_experience_level2');
        	$pageNav2->setAdditionalUrlParam('filter2', $filter2_value);
        	$pageNav2->setAdditionalUrlParam('filter_experience_level2', $filter_experience_level2_value);
        }
        
        $pageNav3 = new JPagination($this->get('ArchiveTotal'), JRequest::getInt('archive_limitstart'), $archive_limit, 'archive_' );
        $pageNav3->setAdditionalUrlParam('filter_order', $lists['filter_order']);
        $pageNav3->setAdditionalUrlParam('filter_order_Dir', $lists['filter_order_Dir']);
        if ($params->get('filter')) {
        	$pageNav3->setAdditionalUrlParam('filter', $filter_value);
        	$pageNav3->setAdditionalUrlParam('filter_experience_level', $filter_experience_level_value);
        }
        
        // $uri->setVar('start', 0);  
        $uri->delVar('start'); // the uri is only used by search, reset display to the first page
        $this->assign('action', $uri->toString());

        $this->assignRef('params', $params);
        $this->assignRef('categories', $categories);
        $this->assignRef('courses', $courses);
        $this->assignRef('archive_courses', $archive_courses);
        $this->assignRef('templates', $templates);
        $this->assignRef('category', $category);
        $this->assignRef('pageNav', $pageNav);
        $this->assignRef('pageNav2', $pageNav2);
        $this->assignRef('pageNav3', $pageNav3);
        $this->assignRef('lists', $lists);
        $this->assignRef('parents', $parents);
        $this->assignRef('print_link', $print_link);

        parent::display($tpl);
    }
}

?>