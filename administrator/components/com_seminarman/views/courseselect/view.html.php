<?php
/**
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011-2014 Open Source Group GmbH www.osg-gmbh.de
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

defined('_JEXEC') or die;

class seminarmanViewCourseselect extends JViewLegacy
{
	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$input = JFactory::getApplication()->input;
		$courses = $this->get('CourseOptions');
    	$params = JComponentHelper::getParams( 'com_seminarman' );
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

		$sortedCourses = array();

		foreach ($courses as $name => $list)
		{
			$tmp = array();

			foreach ($list as $item)
			{
	    		//capacity check
	    		switch ($params->get('current_capacity'))
	    		{
	    			// cases for a parameter
	    			case 1:
	    				$current_capacity_setting=-1;
	    				break;
	
	    			case 2:
	    				$current_capacity_setting=0;
	    				break;
	
	    			default:
	    				$current_capacity_setting=-1;
	    				break;
	    		}
	    		//add currentbookings information
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
				->select('SUM(attendees)')
				->from('#__seminarman_application')
				->where('published = 1')
				->where('course_id = ' . $item->id)
				->where('status > ' . $current_capacity_setting)
				->where('( status < 3 OR status = 5 ) ');
				
				$db->setQuery($query);
				$item->currentBookings = $db->loadResult();
			
				$tmp[JText::_($item->title . '_' . $item->id)] = $item;
			}
			ksort($tmp);
			$sortedCourses[JText::_($name)] = $tmp;
		}
		ksort($sortedCourses);

		$this->courses = $sortedCourses;

		parent::display($tpl);
	}
}
