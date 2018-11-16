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

class SeminarmanViewCategory extends JViewLegacy
{

    function display($tpl = null)
    {
    	
        $mainframe = JFactory::getApplication();

        jimport('joomla.html.pane');

        $editor = JFactory::getEditor();
        $document = JFactory::getDocument();
        $user = JFactory::getUser();
        $lang = JFactory::getLanguage();
        $jversion = new JVersion();
        $short_version = $jversion->getShortVersion();
        if (version_compare($short_version, "3.0", 'ge')) {
        	$pane = JPaneOSG::getInstance('sliders');
    	} else {
    		$pane = JPane::getInstance('sliders');
    	}

        $cid = JRequest::getVar('cid');

        $document->addStyleSheet('components/com_seminarman/assets/css/seminarmanbackend.css');
        if (version_compare($short_version, "3.0", 'ge')) {
        	$document->addStyleSheet('components/com_seminarman/assets/css/seminarmanbackend_j3.x.css');
        }
        if ($lang->isRTL())
        {
            $document->addStyleSheet('components/com_seminarman/assets/css/seminarmanbackend_rtl.css');
        }
        if ($cid && (JRequest::getVar('task')!='add'))
        {
            JToolBarHelper::title(JText::_('COM_SEMINARMAN_EDIT_CATEGORY'), 'qfcategoryedit');

        } else
        {
            JToolBarHelper::title(JText::_('COM_SEMINARMAN_NEW_CATEGORY'), 'qfcategoryedit');

        }
        JToolBarHelper::media_manager();
        JToolBarHelper::divider();
        JToolBarHelper::apply();
        JToolBarHelper::save();
         JToolBarHelper::cancel();

        $model = $this->getModel();
        $row = $this->get('Category');
        $categories = seminarman_cats::getCategoriesTree(0);

        foreach ($categories as $category)
        {

            if ($category->id == $row->id)
            {
                unset($categories[$category->id]);
            }
        }

        if ($row->id)
        {
            if ($model->isCheckedOut($user->get('id')))
            {
                JError::raiseWarning('SOME_ERROR_CODE', $row->title . ' ' . JText::_('COM_SEMINARMAN_RECORD_EDITED'));
                $mainframe->redirect('index.php?option=com_seminarman&view=categories');
            }
        }

        JFilterOutput::objectHTMLSafe($row, ENT_QUOTES, 'text');

        $Lists = array();
        $javascript = "onchange=\"javascript:if (document.forms[0].image.options[selectedIndex].value!='') {document.imagelib.src='../images/' + document.forms[0].image.options[selectedIndex].value} else {document.imagelib.src='../images/blank.png'}\"";
        $Lists['imagelist'] = JHTML::_('list.images', 'image', $row->image, $javascript, '/images/');
    	$javascript2 = "onchange=\"javascript:if (document.forms[0].icon.options[selectedIndex].value!='') {document.iconlib.src='../images/' + document.forms[0].icon.options[selectedIndex].value} else {document.iconlib.src='../images/blank.png'}\"";
    	$Lists['iconlist'] = JHTML::_('list.images', 'icon', $row->icon, $javascript2, '/images/');
    	if (version_compare($short_version, "3.0", 'lt')) {
        	$Lists['access'] = JHTML::_('list.accesslevel', $row);
		}
        $Lists['parent_id'] = seminarman_cats::buildcatselect($categories, 'parent_id', $row->parent_id, true);

        $params = JComponentHelper::getParams('com_seminarman');
        if ($params->get('trigger_virtuemart') == 1) {
        	if (($row->id) == 0) {
        		// create new category and vm engine is on
        		$Lists['select_vm'] = '<tr><td></td><td></td><td><label for="invm">in VirtueMart</label></td><td>' . JHTML::_('select.booleanlist', 'invm', 'class="inputbox"', 1) . '</td></tr>';
        	} else {
        		$db = JFactory::getDBO();
                $query = $db->getQuery(true);
                $query->select('*')
                      ->from('#__seminarman_vm_cat_map')
                      ->where('sm_cat_id = ' . $row->id );
                $db->setQuery($query);
                $result = $db->loadAssoc();
                if (is_null($result)) {
                	// no vm category mapped yet
                	$Lists['select_vm']='<tr><td></td><td></td><td><label for="invm">in VirtueMart</label></td><td>' . JHTML::_('select.booleanlist', 'invm', 'class="inputbox"', 0) . '</td></tr>';
                } else {
                	//a vm category is mapped
           	        $register_vm_id = $result["vm_cat_id"];
            	    $query_check = $db->getQuery(true);
            	    $query_check->select('*')
            	            ->from('#__virtuemart_categories')
            	            ->where('virtuemart_category_id = ' . $register_vm_id);
            	    $db->setQuery($query_check);
            	    $result_check = $db->loadAssoc();
            	    if (is_null($result_check)){
            	    	// an invalid vm category id is mapped
            	    	$Lists['select_vm']='<tr><td></td><td></td><td><label for="invm">' . JText::_('COM_SEMINARMAN_SET_IN_VM') . '</label></td><td>' . JHTML::_('select.booleanlist', 'invm', 'class="inputbox"', 0) . '</td></tr>';
            	    } else {
            	    	// a valid vm category id is already mapped
            	    	$Lists['select_vm']='<tr><td></td><td></td><td><label for="invm">' . JText::_('COM_SEMINARMAN_SET_IN_VM') . '</label></td><td>' . JHTML::_('select.booleanlist', 'invm', 'class="inputbox" disabled', 1) . '<input type="hidden" name="invm" value="1"></td></tr>';
            	    }                    
                }
        	}
        } else {
        	// vm engine is off;
        	$Lists['select_vm']='';
        }       
        
        $cat_attribs = new JRegistry();
        $cat_attribs->loadString($row->params);
        $Lists['attribs'] = $cat_attribs->toArray();
        if (!isset($Lists['attribs']['sorting_option'])) $Lists['attribs']['sorting_option'] = '';
        if (!isset($Lists['attribs']['sorting_direction'])) $Lists['attribs']['sorting_direction'] = 'ASC';
        
        $sorting_options = array();
        $sorting_options[] = array('value' => '', 'text' => JText::_('COM_SEMINARMAN_USE_GLOBAL'));
        $sorting_options[] = array('value' => 'i.title', 'text' => JText::_('COM_SEMINARMAN_COURSE_TITLE'));
        $sorting_options[] = array('value' => 'i.start_date', 'text' => JText::_('COM_SEMINARMAN_START_DATE'));
        $sorting_options[] = array('value' => 'i.finish_date', 'text' => JText::_('COM_SEMINARMAN_FINISH_DATE'));
        $sorting_options[] = array('value' => 'i.location', 'text' => JText::_('COM_SEMINARMAN_LOCATION'));
        $sorting_options[] = array('value' => 'i.price', 'text' => JText::_('COM_SEMINARMAN_PRICE'));
        $sorting_options[] = array('value' => 'i.ordering', 'text' => JText::_('COM_SEMINARMAN_ORDERING'));
        $Lists['sorting_option'] = JHtmlSelect::genericlist($sorting_options, 'params[sorting_option]', null, 'value', 'text', $Lists['attribs']['sorting_option']);
        
        $sorting_directions = array();
        $sorting_directions[] = array('value' => 'ASC', 'text' => JText::_('COM_SEMINARMAN_ORDERING_ASC'));
        $sorting_directions[] = array('value' => 'DESC', 'text' => JText::_('COM_SEMINARMAN_ORDERING_DESC'));
        $Lists['sorting_direction'] = JHtmlSelect::genericlist($sorting_directions, 'params[sorting_direction]', null, 'value', 'text', $Lists['attribs']['sorting_direction']);
        
        $this->assignRef('Lists', $Lists);
        $this->assignRef('row', $row);
        $this->assignRef('editor', $editor);
        $this->assignRef('pane', $pane);

        parent::display($tpl);
    }
    
    function vmenginecheck(){
    	
    }
}

?>