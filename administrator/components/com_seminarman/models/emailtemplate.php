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

jimport('joomla.application.component.model');

class seminarmanModelEmailtemplate extends JModelLegacy
{
	var $id = null;
	
	function __construct()
	{
		parent::__construct();
		$this->id = JRequest::getVar('id');
	}
	
	
	function getData()
	{
		if ($this->id == 0) {
			$data = new stdClass();
			$data->id = 0;
			$data->templatefor = 0;
			$data->title = null;
			$data->subject = null;
			$data->body = null;
			$data->recipient = null;
			$data->bcc = null;
			$data->status = null;
			$data->isDefault = 0;
			return $data;
		}
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select( 'w.*' );
		$query->from( '#__seminarman_emailtemplate AS w' );
		$query->where( 'w.id = '. (int)$this->id );
		
		$db->setQuery( $query );
		return $db->loadObject();
	}


	function delete($cid = array())
	{
		if (count($cid))
		{
			$db = JFactory::getDBO();

			JArrayHelper::toInteger($cid);
			$cids = implode(',', $cid);

			/* are there still courses with this? */
			$query = $db->getQuery(true);
			$query->select( 'COUNT(id) AS count' );
			$query->select( 'GROUP_CONCAT(id) AS ids' );
			$query->from( '#__seminarman_courses' );
			$query->where( 'email_template IN ('. $cids .')' );
			
			$db->setQuery( $query );
			$res = $db->loadAssoc();
			if ($res['count'] > 0) {
				$this->setError(JText::sprintf('COM_SEMINARMAN_RELATED_N_RECORDS', JText::_('COM_SEMINARMAN_COURSE') .' '. $res['ids']));
				return false;
			}

			/* are there still templates with this? */
			$query = $db->getQuery(true);
			$query->select( 'COUNT(id) AS count' );
			$query->select( 'GROUP_CONCAT(id) AS ids' );
			$query->from( '#__seminarman_templates' );
			$query->where( 'email_template IN ('. $cids .')' );
			$db->setQuery( $query );
			$res = $db->loadAssoc();
			
			if ($res['count'] > 0) {
				$this->setError(JText::sprintf('COM_SEMINARMAN_RELATED_N_RECORDS', JText::_('COM_SEMINARMAN_TEMPLATE') .' '. $res['ids']));
				return false;
			}

			$query = $db->getQuery(true);
			
			$query->delete( $db->quoteName( '#__seminarman_emailtemplate' ) )
			->where( $db->quoteName( 'id' ) . ' IN (' . $cids . ')' );
			$db->setQuery( $query );
			
			if ( !$db->execute() ) {
				$this->setError($db->getErrorMsg());
				return false;
			}
			$this->makeOneDefault();
		}

		return true;
	}
	

	function makeOneDefault()
	{
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);
		$query->select( 'COUNT(id)' );
		$query->from( '#__seminarman_emailtemplate' );
		$query->where( 'templatefor=0' );
		$query->where( 'isdefault=1' );
		$db->setQuery( $query );

		$count = $db->loadResult();

		if ($count == 0) {
			$fields = array(
					$db->quoteName('isdefault') . ' = 1'
			);
			
			$conditions = array(
					'templatefor=0'
			);
			
			$query = $db->getQuery(true);
			$query->update( $db->quoteName( '#__seminarman_emailtemplate' ) )
			->set( $fields )
			->where( $conditions )
			->setLimit( 1 );
			
			$db->setQuery( $query );
			$db->execute();
		}
		$query = $db->getQuery(true);
		$query->select( 'COUNT(id)' );
		$query->from( '#__seminarman_emailtemplate' );
		$query->where( 'templatefor=1' );
		$query->where( 'isdefault=1' );
		$db->setQuery( $query );
		$count = $db->loadResult();
		
		if ($count == 0) {
			$fields = array(
					$db->quoteName('isdefault') . ' = 1'
			);
				
			$conditions = array(
					'templatefor=1'
			);
				
			$query = $db->getQuery(true);
			$query->update( $db->quoteName( '#__seminarman_emailtemplate' ) )
			->set( $fields )
			->where( $conditions )
			->setLimit( 1 );
				
			$db->setQuery( $query );
			$db->execute();
		}
	}
	

	function storeEmailTemplate()
	{
		$db = JFactory::getDBO();
		
		$row = $this->getTable('emailtemplate');

		$data = JRequest::get('post');
		$data['body'] = JRequest::getVar('body', '', 'post', 'string', JREQUEST_ALLOWRAW);

		if (!$row->bind($data)) {
			$this->setError($db->getErrorMsg());
			return false;
		}
		if (!$row->store()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		$this->makeOneDefault();

		return $row->id;

	}


	function getFields()
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select( 'DISTINCT name' );
		$query->select( 'fieldcode' );
		$query->select( 'type' );
		$query->from( '#__seminarman_fields' );
		$query->where( 'published = 1' );
		$query->where( "type NOT LIKE '%group%'" );
		$db->setQuery( $query );
		
		return $db->loadObjectList();
	}

	
	function setDefault($id)
	{
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);
		$query->select( 'templatefor' );
		$query->from( '#__seminarman_emailtemplate' );
		$query->where( 'id='.(int)$id );
		$db->setQuery( $query );
		
		$tf = $db->loadResult();
		
		$fields = array(
				$db->quoteName('isdefault') . ' = 1'
		);
		
		$conditions = array(
				'templatefor='.(int)$tf,
				'id='. (int)$id
		);
		
		$query = $db->getQuery(true);
		$query->update( $db->quoteName( '#__seminarman_emailtemplate' ) )
		->set( $fields )
		->where( $conditions );
		
		$db->setQuery( $query );
		$db->execute();
		
		$fields = array(
				$db->quoteName('isdefault') . ' = 0'
		);
		
		$conditions = array(
				'templatefor='.(int)$tf,
				'id<>'. (int)$id
		);
		
		$query = $db->getQuery(true);
		$query->update( $db->quoteName( '#__seminarman_emailtemplate' ) )
		->set( $fields )
		->where( $conditions );
		
		$db->setQuery( $query );
		$db->execute();
	}
}