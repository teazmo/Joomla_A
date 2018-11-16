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

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.path');

class seminarmanModelCourseselect extends JModelLegacy
{
	/**
	 * Method to get the available menu item type options.
	 *
	 * @return  array  Array of groups with menu item types.
	 * @since   1.6
	 */
	public function getCourseOptions()
	{
		jimport('joomla.filesystem.file');

		$lang = JFactory::getLanguage();
		$list = array();

		// Get the list of components.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('id , title')
			->from('#__seminarman_categories')
			->where('access = 1')
			->order('title ASC');
		
		$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->
				getState('limit'));
		
		$db->setQuery($query);

		foreach ($db->loadObjectList() as $category)
		{
			if ($options = $this->getCourseOptionsByCategory($category->id))
			{
				$list[$category->title . '_' . $category->id] = $options;
			}
		}

		return $list;
	}
	
	protected function getCourseOptionsByCategory($cat)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
		->select('c.id, c.title, c.code, c.introtext AS ' . $db->quoteName('description') . ', c.state, c.canceled, c.min_attend, c.capacity, c.start_date, c.finish_date, c.publish_up, c.publish_down')
		->from('#__seminarman_courses AS c')
		->join('LEFT', '#__seminarman_cats_course_relations AS cc ON cc.courseid = c.id')
		->where('catid = ' . $cat)
		->where('access = 1')
		->where('state != 2 AND state != -2')
		->order('title ASC');
		
		if(!(JHTMLSeminarman::UserIsCourseManager())){
		    $query->where("FIND_IN_SET(" . JHTMLSeminarman::getUserTutorID() . ", replace(replace(c.tutor_id, '[', ''), ']', ''))");
		}
		
		$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->
				getState('limit'));
		
		$db->setQuery($query);
		
		return $db->loadObjectList();
	}
}
