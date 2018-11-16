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


// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.controller' );

/**
 * Jom Social Customfields Controller
 */
class SeminarmanControllerEditfields extends SeminarmanController
{
	function __construct()
	{
		parent::__construct();

		$this->registerTask( 'publish' , 'savePublish' );
		$this->registerTask( 'unpublish' , 'savePublish' );
		$this->registerTask( 'orderup' , 'saveOrder' );
		$this->registerTask( 'orderdown' , 'saveOrder' );
	}

	/**
	 * Removes the specific field
	 *
	 * @access public
	 *
	 **/
	function removeField()
	{
		$mainframe	= JFactory::getApplication();

 		$ids	= JRequest::getVar( 'cid', array(), 'post', 'array' );

		foreach( $ids as $id )
		{
			$table	= JTable::getInstance( 'editfield', 'Table' );
			$table->load( $id );

			if(!$table->delete( $id ))
			{
				// If there are any error when deleting, we just stop and redirect user with error.
				$message	= JText::_('COM_SEMINARMAN_OPERATION_FAILED');
				$mainframe->redirect( 'index.php?option=com_seminarman&task=customfields' , $message);
				exit;
			}
		}


		$message	= JText::_( 'COM_SEMINARMAN_OPERATION_SUCCESSFULL');
 		$mainframe->redirect( 'index.php?option=com_seminarman&view=editfields' , $message );
	}

	/**
	 * Save the ordering of the entire records.
	 *
	 * @access public
	 *
	 **/
	function saveOrder()
	{
		$mainframe = JFactory::getApplication();

		// Determine whether to order it up or down
		$direction	= ( JRequest::getWord( 'task' , '' ) == 'orderup' ) ? -1 : 1;

		// Get the ID in the correct location
 		$id			= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$db			= JFactory::getDBO();

		if( isset( $id[0] ) )
		{
			$id		= (int) $id[0];

			// Reset ordering enumeration in DB
			$query	= 'SET @x = 0';
			
			$db->setQuery($query);
			$db->execute();

			$query = $db->getQuery(true);
			 
			$fields = array( $db->quoteName( 'ordering' ). ' = (@x:=@x+1)' );
			 
			$query->update( $db->quoteName( '#__seminarman_fields' ) )
				  ->set( $fields )
				  ->order( 'ordering ASC' );
			 
			$db->setQuery($query);
			$db->execute();
			
			// Load the JTable Object.
			$table	= JTable::getInstance( 'editfield' , 'table' );

			$table->load( $id );

			if( $table->type == 'group' )
			{
				$query = $db->getQuery(true);
				$query->select( '*' );
				$query->from( $db->quoteName( '#__seminarman_fields' ) );
				$query->where( $db->quoteName( 'ordering' ) . ' > ' . $db->Quote( $table->ordering ) );
				$query->where( $db->quoteName( 'type' ) . '=' . $db->Quote( 'group' ) );
				$query->order( 'ordering ASC' );
				$query->setLimit( 1 );
				
				$db->setQuery( $query );
				$nextGroup	= $db->loadObject();
				
				if( $nextGroup || $direction == -1 )
				{
					if( $direction == -1 )
					{
						$query = $db->getQuery(true);
						$query->select( '*' );
						$query->from( $db->quoteName( '#__seminarman_fields' ) );
						$query->where( $db->quoteName( 'ordering' ) . ' < ' . $db->Quote( $table->ordering ) );
						$query->where( $db->quoteName( 'type' ) . '=' . $db->Quote( 'group' ) );
						$query->order( 'ordering DESC LIMIT 1' );
						
						$db->setQuery( $query );
						$previousGroup	= $db->loadObject();
						
						$query = $db->getQuery(true);
						$query->select( '*' );
						$query->from( $db->quoteName( '#__seminarman_fields' ) );
						$query->where( $db->quoteName( 'ordering' ) . ' >= ' . $db->Quote( $table->ordering ) );
						if( $nextGroup )
							$query->where( $db->quoteName( 'ordering' ) . ' < ' . $db->Quote( $nextGroup->ordering ) );
						$query->order( 'ordering ASC' );
						
						$db->setQuery( $query );
						$currentFields	= $db->loadObjectList();

						// Get previous fields in the group
						$query = $db->getQuery(true);
						$query->select( '*' );
						$query->from( $db->quoteName( '#__seminarman_fields' ) );
						$query->where( $db->quoteName( 'ordering' ) . ' >= ' . $db->Quote( $previousGroup->ordering ) );
						$query->where( $db->quoteName( 'ordering' ) . ' < ' . $db->Quote( $table->ordering ) );
						$query->order( 'ordering ASC' );
						
						$db->setQuery( $query );
						$previousFields	= $db->loadObjectList();
						
						for( $i = 0; $i < count( $previousFields ); $i++ )
						{
							$row	=& $previousFields[ $i ];
							$row->ordering			= $row->ordering + count( $currentFields );
							
							$query = $db->getQuery(true);
							
							$fields = array( $db->quoteName('ordering') . '=' . $db->Quote( $row->ordering ) );
							$conditions = array( $db->quoteName( 'id' ) . '=' . $db->Quote( $row->id ) );
							
							$query->update( $db->quoteName( '#__seminarman_fields' ) )
								  ->set( $fields )
								  ->where( $conditions );
							
							$db->setQuery($query);
							$db->execute();
						}

						for( $i = 0; $i < count( $currentFields ); $i ++ )
						{
							$row	=& $currentFields[ $i ];

							$row->ordering	= $row->ordering - count( $previousFields );

							$query = $db->getQuery(true);
								
							$fields = array( $db->quoteName('ordering') . '=' . $db->Quote( $row->ordering ) );
							$conditions = array( $db->quoteName( 'id' ) . '=' . $db->Quote( $row->id ) );
								
							$query->update( $db->quoteName( '#__seminarman_fields' ) )
							->set( $fields )
							->where( $conditions );
								
							$db->setQuery($query);
							$db->execute();
						}
					}
					else
					{
						// Get end
						$query = $db->getQuery(true);
						$query->select( 'ordering' );
						$query->from( $db->quoteName( '#__seminarman_fields' ) );
						$query->where( $db->quoteName( 'ordering' ) . ' > ' . $db->Quote( $nextGroup->ordering ) );
						$query->where( $db->quoteName( 'type' ) . '=' . $db->Quote( 'group' ) );
						$query->order( 'ordering ASC LIMIT 1' );
						
						$db->setQuery( $query );
						$nextGroupLimit	= $db->loadResult();
						
						// Get the next group childs
						$query = $db->getQuery(true);
						$query->select( '*' );
						$query->from( $db->quoteName( '#__seminarman_fields' ) );
						
						if( $nextGroupLimit ) {
							$query->where( 'ordering >=' . $nextGroup->ordering );
							$query->where( 'ordering < ' . $nextGroupLimit );						
						}
						else {
							$query->where( 'ordering >=' . $nextGroup->ordering );
						}
						$query->order( 'ordering ASC' );
						
						$db->setQuery( $query );
						$nextGroupChilds	= $db->loadObjectList();
						
						$nextGroupsCount	= count( $nextGroupChilds );

						// Get all children of the current group field
						$query = $db->getQuery(true);
						$query->select( '*' );
						$query->from( $db->quoteName( '#__seminarman_fields' ) );
						$query->where( 'ordering >=' . $table->ordering );
						$query->where( 'ordering < ' . $nextGroup->ordering );
						$query->order( 'ordering ASC' );
						
						$db->setQuery( $query );
						$currentGroupChilds	= $db->loadObjectList();

						$currentGroupsCount	= count( $currentGroupChilds );

						for( $i = 0; $i < count( $nextGroupChilds ); $i++ )
						{
							$row	=& $nextGroupChilds[ $i ];

							//$row->ordering		= $row->ordering - $currentGroupsCount;
							$row->ordering			= $table->ordering++;

							$query = $db->getQuery(true);
							
							$fields = array( $db->quoteName('ordering') . '=' . $db->Quote( $row->ordering ) );
							$conditions = array( $db->quoteName( 'id' ) . '=' . $db->Quote( $row->id ) );
							
							$query->update( $db->quoteName( '#__seminarman_fields' ) )
							->set( $fields )
							->where( $conditions );
							
							$db->setQuery($query);
							$db->execute();
						}

						for( $i = 0; $i < count( $currentGroupChilds ); $i ++ )
						{
							$child	=& $currentGroupChilds[ $i ];

							$child->ordering	= $nextGroupsCount + $child->ordering;
							
							$query = $db->getQuery(true);
								
							$fields = array( $db->quoteName('ordering') . '=' . $db->Quote( $child->ordering ) );
							$conditions = array( $db->quoteName( 'id' ) . '=' . $db->Quote( $child->id ) );
								
							$query->update( $db->quoteName( '#__seminarman_fields' ) )
							->set( $fields )
							->where( $conditions );
								
							$db->setQuery($query);
							$db->execute();
						}
					}
				}
			}
			else
			{
				$table->move( $direction );
			}

			$cache	= JFactory::getCache( 'com_content');
			$cache->clean();

			$mainframe->redirect( 'index.php?option=com_seminarman&view=editfields' );
		}

	}
	
	function newfield()   {
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );
		JRequest::setVar('view', 'editfield');
		JRequest::setVar('hidemainmenu', 1);
	
		$model = $this->getModel('editfield');
		$user = JFactory::getUser();
	
		if ($model->isCheckedOut($user->get('id')))
		{
			$this->setRedirect('index.php?option=com_seminarman&view=editfields', JText::_('ECOM_SEMINARMAN_RECORD_EDITED'));
		}
	
		$model->checkout($user->get('id'));
	
		parent::display();
	}
	
	function newgroup()   {
	
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );
		JRequest::setVar('view', 'editfield');
		JRequest::setVar('layout', 'editgroup');
		JRequest::setVar('hidemainmenu', 1);
	
		$model = $this->getModel('editfield');
		$user = JFactory::getUser();
	
		if ($model->isCheckedOut($user->get('id')))
		{
			$this->setRedirect('index.php?option=com_seminarman&view=editfields', JText::_('COM_SEMINARMAN_RECORD_EDITED'));
		}
	
		$model->checkout($user->get('id'));
	
		parent::display();
	}
	
	function publish()   {
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
	
		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'COM_SEMINARMAN_SELECT_ITEM' ) );
		}
	
		$model = $this->getModel('editfields');
	
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}
	
		$this->setRedirect( 'index.php?option=com_seminarman&view=editfields' );
	}
	
	
	function unpublish()   {
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
	
		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'COM_SEMINARMAN_SELECT_ITEM' ) );
		}
	
		$model = $this->getModel('editfields');
	
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}
	
		$this->setRedirect( 'index.php?option=com_seminarman&view=editfields' );
	}

}