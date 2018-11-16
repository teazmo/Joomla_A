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

class SeminarmanViewBookings extends JViewLegacy{
    function display($tpl = null)
    {
        if ($this->getLayout() == 'invoicepdf')
        {
        	return $this->_viewpdf('bill');
        }
        
        if ($this->getLayout() == 'certificatepdf')
        {
        	return $this->_viewpdf('cert');
        }

        $mainframe = JFactory::getApplication();

        $Itemid = JRequest::getInt('Itemid');
        
        $document = JFactory::getDocument();
        $menus = JFactory::getApplication()->getMenu();
        $menu = $menus->getActive();
        $params = $mainframe->getParams('com_seminarman');
        $uri = JFactory::getURI();
        $lang = JFactory::getLanguage();
        $model = $this->getModel('bookings');
		$user = JFactory::getUser();
		$user_id = (int)$user->get('id');
        jimport( 'joomla.html.parameter' );

        $limitstart = JRequest::getInt('limitstart');
        $limit = $mainframe->getUserStateFromRequest('com_seminarman.bookings.limit', 'limit', $params->def('limit', 0), 'int');
        
        seminarman_html::addSiteStyles($this);

        if (is_object($menu)){
        	$jversion = new JVersion();
        	$short_version = $jversion->getShortVersion();
        	if (version_compare($short_version, "3.0", 'ge')) {
                $menu_params = new JRegistry($menu->params);
            } else {
            	$menu_params = new JParameter($menu->params);
            }

            if (!$menu_params->get('page_title')){
                $params->set('page_title', JText::_('COM_SEMINARMAN_MY_BOOKINGS'));
            }
        }else{
            $params->set('page_title', JText::_('COM_SEMINARMAN_MY_BOOKINGS'));
        }

        $pathway = $mainframe->getPathWay();
        if ($params->get('enable_component_pathway')) {
          $pathway->addItem($params->get('page_title'), JRoute::_('index.php?option=com_seminarman&view=bookings' . '&Itemid=' . $Itemid));
        }

        $document->setTitle($params->get('page_title'));
        $document->setMetadata('keywords', $params->get('page_title'));

            if ($user->get('guest')){
                $redirectUrl = JRoute::_('index.php?option=com_seminarman&view=bookings' . '&Itemid=' . $Itemid, false);
                $redirectUrl = base64_encode($redirectUrl);
                $redirectUrl = '&return=' . $redirectUrl;
                $joomlaLoginUrl = 'index.php?option=com_users&view=login';
                $finalUrl = $joomlaLoginUrl . $redirectUrl;
                $mainframe->redirect($finalUrl, JText::_('COM_SEMINARMAN_PLEASE_LOGIN_FIRST'));
            }

        $courses = $this->get('Data');
        
        $total = $this->get('Total');


        $count = count($courses);
        for($i = 0; $i < $count; $i++){
            $item = &$courses[$i];
        	$item->count=$i;
        	$category = $model->getCategory($item->id);

        	SMANFunctions::setCourse($item, $category, $Itemid, JText::_('COM_SEMINARMAN_DATE_FORMAT2'), JText::_('COM_SEMINARMAN_TIME_FORMAT2'));
        	
        	if ($item->booking_state < 2){
        		// create booking button
        		$item->paypal_link = '<div class="button2-left"><div class="blank"><a href="' . JRoute::_('index.php?option=com_seminarman&view=paypal&bookingid=' . $item->applicationid . '&Itemid=' . $Itemid) . '">' . JText::_('COM_SEMINARMAN_PAY_NOW') . '</a></div></div>';
        	}elseif ($item->booking_state == 3){
        		$item->paypal_link = '<span class="centered italic">' . JText::_('COM_SEMINARMAN_CANCELLED') . '</span>';
        	}elseif ($item->booking_state == 4){
        		$item->paypal_link = '';
        	}elseif ($item->booking_state == 5){
        		$item->paypal_link = '';
        	}else{
        		$item->paypal_link = '<span class="centered italic">' . JText::_('COM_SEMINARMAN_PAID') . '</span>';
        	}
            
            $today = JFactory::getDate()->format('Y-m-d H:i:s');
            
            // fix for 24:00:00 (illegal time colock)
            if ($item->start_time_raw == '24:00:00') $item->start_time_raw = '23:59:59';
            
            if (($user_id != 0) && ($user_id == $item->user_id)) {
            	switch($params->get('cancel_allowed')) {
                    case 0:    // cancel not allowed
                    	$item->cancel_allowed = false;
                    	break;
                    case 1:    // only state "submitted" allowed
                    	if ($item->booking_state > 0 && $item->booking_state != 4) {
                    		$item->cancel_allowed = false;
                    	} else {
                    		if ((trim($params->get('cancel_deadline'))) == '') {
                    			$item->cancel_allowed = true;
                    		} else {
                    			$kursbegindate = new JDate($item->start_date_raw . ' ' . $item->start_time_raw);
                    			$kursbegin = $kursbegindate->format('Y-m-d H:i:s');
                    			if ((strtotime($kursbegin) - strtotime($today)) > (86400 * (int)$params->get('cancel_deadline'))) {
                    				$item->cancel_allowed = true;
                    			} else {
                    				$item->cancel_allowed = false;
                    			}
                    		}
                    	}
                    	break;
                    case 2:    // state "submitted" and "pending" allowed
                    	if ($item->booking_state > 1 && $item->booking_state != 4) {
                    		$item->cancel_allowed = false;
                    	} else {
                    		if ((trim($params->get('cancel_deadline'))) == '') {
                    			$item->cancel_allowed = true;
                    		} else {
                    			$kursbegindate = new JDate($item->start_date_raw . ' ' . $item->start_time_raw);
                    			$kursbegin = $kursbegindate->format('Y-m-d H:i:s');
                    			if ((strtotime($kursbegin) - strtotime($today)) > (86400 * (int)$params->get('cancel_deadline'))) {
                    				$item->cancel_allowed = true;
                    			} else {
                    				$item->cancel_allowed = false;
                    			}                    			 
                    		}                    	
                    	}                    	
                    	break;
                    default:
                    	$item->cancel_allowed = false;
            	}
            } else {
            	$item->cancel_allowed = false;
            }
        }

        $lists = array();
        $lists['filter_order'] = $model->getState('filter_order');
        $lists['filter_order_Dir'] = $model->getState('filter_order_dir');
        $lists['filter'] = JRequest::getString('filter');

        $filter_experience_level = JRequest::getString('filter_experience_level');
        $filter_positiontype = JRequest::getString('filter_positiontype');

        $experience_level[] = JHTML::_('select.option', '0', JText::_('All'), 'id', 'title');
        $titles = $this->get('titles');
        $experience_level = array_merge($experience_level, $titles);
        $lists['filter_experience_level'] = JHTML::_('select.genericlist', $experience_level,
            'filter_experience_level', 'class="inputbox" size="1" ', 'id', 'title', $filter_experience_level);

        jimport('joomla.html.pagination');

        $pageNav = new JPagination($total, $limitstart, $limit);
        $pageNav->setAdditionalUrlParam('filter_order', $lists['filter_order']);
        $pageNav->setAdditionalUrlParam('filter_order_Dir', $lists['filter_order_Dir']);
        $page = $total - $limit;
        if ($params->get('filter')) {
        	$filter_value = JRequest::getString('filter');
        	$filter_experience_level_value = JRequest::getString('filter_experience_level');
        	$pageNav->setAdditionalUrlParam('filter', $filter_value);
        	$pageNav->setAdditionalUrlParam('filter_experience_level', $filter_experience_level_value);
        }
        
        $mybookingrules = $this->get('UserBookingrules');

        // $uri->setVar('start', 0);  
        $uri->delVar('start'); // the uri is only used by search, reset display to the first page
        $this->assign('action', $uri->toString());

        $this->assignRef('courses', $courses);
    	$this->assignRef('category', $category);
        $this->assignRef('params', $params);
        $this->assignRef('page', $page);
        $this->assignRef('pageNav', $pageNav);
        $this->assignRef('lists', $lists);
        $this->assignRef('bookingrules', $mybookingrules);
        
        // if($params->get('enable_bookings')==1 && $params->get('user_booking_rules')==1){
        	// default_list deprecated from this version
        	// $tpl = "list";
        	if (JRequest::getVar('task')=='cancel_booking') $tpl = "cancel_booking";
        // }
        
        parent::display($tpl);
    }
    
    
    function _viewpdf($type)
    {
		$mainframe = JFactory::getApplication();
		$applications = $this->get('Data');
		
		$appid = JRequest::GetInt('appid','0');
		
		if ($appid == 0)
			return JError::raiseError(404, '');
		
		$found = false;
		foreach ($applications as $application) {
			if ($application->applicationid == $appid)
			{
				if ($type == 'bill') {
					$filename = $application->invoice_filename_prefix.$application->invoice_number.'.pdf';
				} elseif ($type == 'cert') {
					$filename = $application->certificate_file;
				}
				$found = true;
				break;
			}
		}
		if (!$found)
			return JError::raiseError(404, '');
		
		$params = JComponentHelper::getParams( 'com_seminarman' );
		
		$filepath = JPATH_ROOT.DS.$params->get('invoice_save_dir').DS.$filename;
		     
		$jversion = new JVersion();
		$short_version = $jversion->getShortVersion();
		if (version_compare($short_version, "3.0", 'ge')) {
		    jimport('joomla.filesystem.file');
    	}
		if (!$pdf_data = JFile::read($filepath))
			return JError::raiseError(404, JText::_('FILE NOT FOUND'));
		
		ob_end_clean();
		header('Content-Type: application/pdf');
		header('Content-Disposition: attachment; filename="'. $filename .'"');
		print $pdf_data;
		flush();
		exit;
    }
}

?>