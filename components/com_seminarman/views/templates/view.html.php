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
jimport('joomla.html.parameter' );

class SeminarmanViewTemplates extends JViewLegacy{
    function display($tpl = null)
    {
        $mainframe = JFactory::getApplication();
        $params = $mainframe->getParams('com_seminarman');
        
        $Itemid = JRequest::getInt('Itemid');
        
        if ($params->get('enable_salesprospects', 0) == 0){
        	$mainframe->redirect('index.php', '');
        }

        $document = JFactory::getDocument();
        $user = JFactory::getUser();
        $menus = JFactory::getApplication()->getMenu();
        $lang = JFactory::getLanguage();
        $menu = $menus->getActive();
        $dispatcher = JDispatcher::getInstance();
        $uri = JFactory::getURI();
        
        $jversion = new JVersion();
        $short_version = $jversion->getShortVersion();

        $limitstart = JRequest::getVar('limitstart', 0, '', 'int');
        $cid = JRequest::getInt('cid', 0);

        if ($this->getLayout() == 'form'){
            $this->_displayForm($tpl);
            return;
        }
        
        require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS .
            'helpers' . DS . 'seminarman.php');

        seminarman_html::addSiteStyles($this);

        if (!$user->id || $user->id == 0){
            $document = JFactory::getDocument();
        }

        $document = JFactory::getDocument();

        $template = $this->get('Template');
        $template->currency_price = $params->get('currency');
        
        // calculate displayed price
        if ($params->get('show_gross_price') == '1') {
        	$template->price += ($template->price / 100) * $template->vat;
        }
        $old_locale = setlocale(LC_NUMERIC, NULL);
        setlocale(LC_NUMERIC, $lang->getLocale());        
        $template->price = JText::sprintf('%.2f', round($template->price, 2));
        setlocale(LC_NUMERIC, $old_locale);
        
        
        $salesProspectsData = $this->get('SalesProspects');

        if (($template->id == 0)){
            $id = JRequest::getInt('id', 0);
            return JError::raiseError(404, JText::sprintf('TEMPLATE #%d NOT FOUND', $id));
        }

        if ($params->get('show_tags')){
            $tags = $this->get('Tags');
        }

        if ($params->get('show_categories')){
            $categories = $this->get('Categories');
        }

        $files = $this->get('Files');

        if ($params->get('trigger_onprepare_content')){
            JPluginHelper::importPlugin('content');
            $results = $dispatcher->trigger('onContentPrepare', array('com_seminarman.templates', &$template, &$params, $limitstart));
        }

        $cats = new seminarman_cats($cid);
        $parents = $cats->getParentlist();
        $pathway = $mainframe->getPathWay();
        $all_parents = $parents;

        if ($params->get('enable_component_pathway') == 1) {
        foreach ($parents as $parent){
            $pathway->addItem($this->escape($parent->title), JRoute::_('index.php?option=com_seminarman&view=category&cid=' . $parent->categoryslug . '&Itemid=' . $Itemid));
        }
        $pathway->addItem($this->escape($template->title), JRoute::_('index.php?option=com_seminarman&view=templates&id=' . $template->slug . '&Itemid=' . $Itemid));
        }
        
        // if (is_object($menu)){
        //	if (version_compare($short_version, "3.0", 'ge')) {
        //	    $menu_params = new JRegistry($menu->params);
        //	} else {
        //		$menu_params = new JParameter($menu->params);
        //	}
        //    if (!$menu_params->get('page_title')){
        //        $params->set('page_title', $template->title);
        //    }
        // }else{
        //    $params->set('page_title', $template->title);
        // }

        $param_page_title = $params->get('page_title'); // workable for PHP 5.4
        if (is_object($menu) && ($menu->query['view'] == 'templates') && !empty($param_page_title)) {
        	$doc_title = $params->get('page_title');
        } else {
        	if ($cid){
        		$parentcat = array_pop($parents);
        		$doc_title = $parentcat->title . ' - ' . $template->title;
        	}else{
        		$doc_title = $template->title;
        	}	
        }

        $document->setTitle($doc_title);

        if ($template->meta_description){
            $document->setDescription($template->meta_description);
        }

        if ($template->meta_keywords){
            $document->setMetadata('keywords', $template->meta_keywords);
        }

        if (version_compare($short_version, "3.0", 'ge')) {
            $mdata = new JRegistry($template->metadata);
        } else {
        	$mdata = new JParameter($template->metadata);
        }
        $mdata = $mdata->toArray();
        foreach ($mdata as $k => $v){
            if ($v){
                $document->setMetadata($k, $v);
            }
        }
    
        // $params->merge($itemParams);

        $print_link = JRoute::_('index.php?option=com_seminarman&view=templates&cid=' . $template->categoryslug . '&id=' . $template->slug . '&Itemid=' . $Itemid . '&pop=1&tmpl=component');
        
        $default = isset($salesProspectsData->salutationStr) ? $salesProspectsData->salutationStr : $salesProspectsData->salutation;
        $lists['salutation'] = JHTMLSeminarman::getListFromXML('Salutation', 'salutation', 0, $default);
        $results = $mainframe->triggerEvent( 'onPrepareContent', array( &$template, &$params, 0 ));

        $data = new stdClass();
        $model = $this->getModel('templates');
        $data->customfields = $model->getEditableCustomfields($salesProspectsData->id);
        
        CMFactory::load('libraries' , 'customfields');

        $fields = $data->customfields ['fields'];
        $this->assignRef('fields', $fields);
        $this->assignRef('attendeedata', $salesProspectsData);
        $this->assignRef('template', $template);
        $this->assignRef('tags', $tags);
        $this->assignRef('categories', $categories);
        $this->assignRef('files', $files);
        $this->assignRef('user', $user);
        $this->assignRef('params', $params);
        $this->assignRef('print_link', $print_link);
        $this->assignRef('parentcat', $parentcat);
        $this->assignRef('lists', $lists);
        $this->assign('action', $uri->toString());
        $this->assignRef('all_parents', $all_parents);

        parent::display($tpl);
    }

    function _displayForm($tpl)
    {
        $mainframe = JFactory::getApplication();

        $document = JFactory::getDocument();
        $user = JFactory::getUser();
        $uri = JFactory::getURI();
        $template = $this->get('Template');
        $tags = $this->get('Alltags');
        $used = $this->get('Usedtags');
        $params = $mainframe->getParams('com_seminarman');

        if (JVERSION >= 3.4) {
            JHtml::_('behavior.formvalidator');
        } else {
            JHTML::_('behavior.formvalidation');
        }
        // JHTML::_('behavior.tooltip');

        if (!is_array($used)){
            $used = array();
        }

        seminarman_html::addSiteStyles($this);

        if ($template->id > 1 && !($user->authorise('com_seminarman', 'edit') || ($user->authorise('com_content', 'edit', 'content', 'own') && $template->created_by == $user->get('id')))){
            JError::raiseError(403, JText::_("ALERTNOTAUTH"));
        }

        $lists = $this->_buildEditLists();

        $editor = JFactory::getEditor();

        $title = $template->id ? JText::_('Edit') : JText::_('New');

        $document->setTitle($title);

        $pathway = $mainframe->getPathWay();
        $pathway->addItem($title, '');
        

        if (JString::strlen($course->fulltext) > 1){
            $template->text = $template->introtext . "<hr id=\"system-readmore\" />" . $template->fulltext;
        }else{
            $template->text = $template->introtext;
        }

        JFilterOutput::objectHTMLSafe($course, ENT_QUOTES);
        // custom fields
        // data structure $data['fields']['ungrouped'][field values] or $data['fields']['group title'][field values]
        // $data['id']		= $user->id;
        // $data['name']	= $user->name;
        // /  $data['email']	= $user->email;
        // Check if user is really allowed to edit
        $user = JFactory::getUser();
        $data = new stdClass();
        $data->customfields = $model->getEditableCustomfields($user->id);

        CMFactory::load('libraries' , 'customfields');
        // $tmpl	= new CMTemplate();
        // $tmpl->set( 'fields' , $data->customfields ['fields'] );
        // echo $tmpl->fetch( 'customfields.edit' );
        $fields = $data->customfields ['fields'];
        $action = $uri->toString();
        $this->assignRef('fields', $fields);
        $this->assign('action', $action);
        $this->assignRef('template', $template);
        $this->assignRef('params', $params);
        $this->assignRef('lists', $lists);
        $this->assignRef('editor', $editor);
        $this->assignRef('user', $user);
        $this->assignRef('tags', $tags);
        $this->assignRef('used', $used);

        parent::display($tpl);
    }
}

?>
