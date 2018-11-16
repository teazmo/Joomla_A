<?php
/**
 * @version $Id: 1.5.4 2009-10-15
 * @package			Course Manager
 * @subpackage		Component
 * @author			Profinvent {@link http://www.seminarman.com}
 * @copyright 		(C) Profinvent - Joomla Experts
 * @license   		GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

/**
 * seminarman Component session Model
 *
 * @package    Course Manager
 * @subpackage seminarman
 * @since 1.5.0
 */
class seminarmanModelsession extends JModelLegacy
{
   /**
    * session id
    *
    * @var int
    */
   var $_id = null;

   /**
    * session data
    *
    * @var array
    */
   var $_data = null;

   /**
    * Constructor
    *
    * @since 1.5
    */
   function __construct()
   {
      parent::__construct();

      $array = JRequest::getVar('cid', array(0), '', 'array');
      $edit = JRequest::getVar('edit',true);
      if($edit)
         $this->setId((int)$array[0]);
   }

   /**
    * Method to set the session identifier
    *
    * @access  public
    * @param   int session identifier
    */
   function setId($id)
   {
      // Set session id and wipe data
      $this->_id     = $id;
      $this->_data   = null;
   }

    function get($property, $default = null)
    {
        if ($this->_loadData())
        {
            if (isset($this->_data->$property))
            {
                return $this->_data->$property;
            }
        }
        return $default;
    }

   /**
    * Method to get a session
    *
    * @since 1.5
    */
   function &getData()
   {
      // Load the session data
      if ($this->_loadData())
      {
         // Initialize some variables
         $user = JFactory::getUser();
      }
      else  $this->_initData();

      return $this->_data;
   }

   /**
    * Tests if session is checked out
    *
    * @access  public
    * @param   int   A user id
    * @return  boolean  True if checked out
    * @since   1.5
    */
   function isCheckedOut( $uid=0 )
   {
      if ($this->_loadData())
      {
         if ($uid) {
            return ($this->_data->checked_out && $this->_data->checked_out != $uid);
         } else {
            return $this->_data->checked_out;
         }
      }
   }

   /**
    * Method to checkin/unlock the session
    *
    * @access  public
    * @return  boolean  True on success
    * @since   1.5
    */
   function checkin()
   {
      if ($this->_id)
      {
         $session = $this->getTable();
         if(! $session->checkin($this->_id)) {
            $this->setError($this->_db->getErrorMsg());
            return false;
         }
      }
      return false;
   }

   /**
    * Method to checkout/lock the session
    *
    * @access  public
    * @param   int   $uid  User ID of the user checking the article out
    * @return  boolean  True on success
    * @since   1.5
    */
   function checkout($uid = null)
   {
      if ($this->_id)
      {
         // Make sure we have a user id to checkout the article with
         if (is_null($uid)) {
            $user = JFactory::getUser();
            $uid  = $user->get('id');
         }
         // Lets get to it and checkout the thing...
         $session = $this->getTable();
         if(!$session->checkout($uid, $this->_id)) {
            $this->setError($this->_db->getErrorMsg());
            return false;
         }

         return true;
      }
      return false;
   }

   /**
    * Method to store the session
    *
    * @access  public
    * @return  boolean  True on success
    * @since   1.5
    */
   function store($data)
   {
      $row = $this->getTable();

      // Bind the form fields to the web link table
      if (!$row->bind($data)) {
         $this->setError($this->_db->getErrorMsg());
         return false;
      }
      
      require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS . 'helpers' . DS . 'seminarman.php');
      // $row->session_date = JHTMLSeminarman::localDate2DbDate($row->session_date);
      $session_begin = SeminarmanFunctions::formatDateToSQLParts($row->session_date, $row->start_time);
      $session_finish = SeminarmanFunctions::formatDateToSQLParts($row->session_date, $row->finish_time);
      $row->session_date = $session_begin[0];
      $row->start_time = $session_begin[1];
      $row->finish_time = $session_finish[1];

      // Create the timestamp for the date
      $row->date = gmdate('Y-m-d H:i:s');

      // if new item, order last in appropriate group
      if (!$row->id) {
         $where = 'courseid = ' . (int) $row->courseid ;
         $row->ordering = $row->getNextOrder( $where );
      }

      // Make sure the web link table is valid
      if (!$row->check()) {
         $this->setError($this->_db->getErrorMsg());
         return false;
      }

      // Store the web link table to the database
      if (!$row->store()) {
         $this->setError($this->_db->getErrorMsg());
         return false;
      }
      
      $this->_data->id = $row->id;
      return $row->id;
   }



   /**
    * Method to remove a session
    *
    * @access  public
    * @return  boolean  True on success
    * @since   1.5
    */
   function delete($cid = array())
   {
      if ( count( $cid ) ){
      	JArrayHelper::toInteger($cid);
      	$cids = implode(',', $cid);
      
      	$query = $this->_db->getQuery(true);
      
      	$query->delete( $this->_db->quoteName( '#__seminarman_sessions' ) )
      	->where( $this->_db->quoteName( 'id' ) . ' IN (' . $cids . ')' );
      	$this->_db->setQuery( $query );
      
      	if ( !$this->_db->execute() ) {
      		$this->setError($this->_db->getErrorMsg());
      		return false;
      	}
      }
      
      return true;
   }


   /**
    * Method to remove a single session
    *
    * @access  public
    * @return  boolean  True on success
    * @since   1.5
    */
   function deleteone($sessionid)
   {
		if ( $sessionid ) {
			$query = $this->_db->getQuery( true );
			
			$query->delete( $this->_db->quoteName( '#__seminarman_sessions' ) )
				  ->where( $this->_db->quoteName( 'id' ) . ' = ' . $sessionid );
			$this->_db->setQuery( $query );
			
			if ( !$this->_db->execute() ) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		return true;
   }

   /**
    * Method to (un)publish a session
    *
    * @access  public
    * @return  boolean  True on success
    * @since   1.5
    */
   function approve($cid = array(), $approve = 1)
   {
		$user = JFactory::getUser();
		
		if ( count( $cid ) ) {
			JArrayHelper::toInteger($cid);
			$cids = implode(',', $cid);
			
			$fields = array(
				$this->_db->quoteName('approved') . ' = ' . (int) $approved
			);	
			
			$conditions = array(
				$this->_db->quoteName('id') . ' IN ( '.$cids.' )',
				'( checked_out = 0 OR ( checked_out = '.(int) $user->get('id').' ) )'
			);
			
			$query = $this->_db->getQuery(true);
			$query->update( $this->_db->quoteName( '#__seminarman_sessions' ) )
				  ->set( $fields )
				  ->where( $conditions );
			
			$this->_db->setQuery( $query );
			
			if (!$this->_db->execute()){
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		
		return true;
	}

   function publish($cid = array(), $publish = 1)
   {
      $user    = JFactory::getUser();
      
      if (count( $cid ))
      {
      	JArrayHelper::toInteger($cid);
      	$cids = implode( ',', $cid );
      
      	$fields = array(
      			$this->_db->quoteName('published') . ' = ' . (int) $publish
      	);
      	 
      	$conditions = array(
      			$this->_db->quoteName('id') . ' IN ( '.$cids.' )',
      			'( checked_out = 0 OR ( checked_out = '.(int) $user->get('id').' ) )'
      	);
      
      	$query = $this->_db->getQuery(true);
      	$query->update( $this->_db->quoteName( '#__seminarman_sessions' ) )
      	->set( $fields )
      	->where( $conditions );
      	 
      	$this->_db->setQuery( $query );
      	if (!$this->_db->execute()) {
      		$this->setError($this->_db->getErrorMsg());
      		return false;
      	}
      }
      
      return true;
   }


   /**
    * Method to move a session
    *
    * @access  public
    * @return  boolean  True on success
    * @since   1.5
    */
   function move($direction)
   {
      $row = $this->getTable();
      if (!$row->load($this->_id)) {
         $this->setError($this->_db->getErrorMsg());
         return false;
      }

      if (!$row->move( $direction, ' courseid = '.(int) $row->courseid.' AND published >= 0 ' )) {
         $this->setError($this->_db->getErrorMsg());
         return false;
      }

      return true;
   }

   /**
    * Method to move a session
    *
    * @access  public
    * @return  boolean  True on success
    * @since   1.5
    */
   function saveorder($cid = array(), $order)
   {
      $row = $this->getTable();
      $groupings = array();

      // update ordering values
      for( $i=0; $i < count($cid); $i++ )
      {
         $row->load( (int) $cid[$i] );
         // track categories
         $groupings[] = $row->courseid;

         if ($row->ordering != $order[$i])
         {
            $row->ordering = $order[$i];
            if (!$row->store()) {
               $this->setError($this->_db->getErrorMsg());
               return false;
            }
         }
      }

      // execute updateOrder for each parent group
      $groupings = array_unique( $groupings );
      foreach ($groupings as $group){
         $row->reorder('courseid = '.(int) $group);
      }

      return true;
   }

   /**
    * Method to load content session data
    *
    * @access  private
    * @return  boolean  True on success
    * @since   1.5
    */
   function _loadData()
   {
      // Lets load the content if it doesn't already exist
      if (empty($this->_data)){
      	$query = $this->_db->getQuery(true);
      	$query->select( 'w.*' );
      	$query->select( 'cc.title AS course_title' );
      	$query->select( 'cc.state AS course_pub' );
      	$query->from( '#__seminarman_sessions AS w' );
      	$query->join( "LEFT", '#__seminarman_courses AS cc ON w.courseid = cc.id' );
      	$query->where( 'w.id = '. (int)$this->_id );
      
      	$this->_db->setQuery( $query );
      	$this->_data = $this->_db->loadObject();
      
      	return (boolean)$this->_data;
      }
      return true;
   }

   /* Method to fetch course titles
   *
   * @access public
   * @return string
   */
   function getTitles()
   {
	 	$db = JFactory::getDBO();
	 	$query = $db->getQuery(true);
	
	 	$query->select( 'id' );
	 	$query->select( 'CONCAT(title, " (", code, ")") as title' );
	 	$query->select( 'start_date' );
	 	$query->select( 'start_time' );
	 	$query->from( '#__seminarman_courses' );
	 	$query->where( '( state = 1 OR state = 0 )' );
	 		
	 	if ( !( JHTMLSeminarman::UserIsCourseManager() ) ) {
	 		$query->where( 'FIND_IN_SET(' . JHTMLSeminarman::getUserTutorID() . ', replace(replace(tutor_id, \'[\', \'\'), \']\', \'\'))' );
	 	}
	 	$query->order( 'title' );
	 	
	 	$db->setQuery( $query );
	 	$titles = $db->loadObjectlist();
		
		foreach ($titles as $title) {
			// fix for 24:00:00 (illegal time colock)
			if ($title->start_time == '24:00:00') $title->start_time = '23:59:59';
			$title->title = $title->title . (!empty($title->start_date) && $title->start_date != '0000-00-00' ? ' (' . JHTML::date($title->start_date . ' ' . $title->start_time, JText::_('COM_SEMINARMAN_DATE_FORMAT1')) . (!empty($title->start_time) ? ', ' . JHTML::date($title->start_date . ' ' . $title->start_time , 'H:i') : '') . ')' : '');
		}
		
	 	return $titles;
   }


   /**
    * Method to initialise the session data
    *
    * @access  private
    * @return  boolean  True on success
    * @since   1.5
    */
   function _initData()
   {
      // Lets load the content if it doesn't already exist
      if (empty($this->_data))
      {
         $session = new stdClass();
         $session->id                = 0;
         $session->courseid          = 0;
         $session->title             = null;
         $session->alias             = null;
         $session->status            = null;
         $session->session_date      = null;
       	 $session->start_time        = null;
         $session->finish_time       = null;
         $session->duration          = null;
         $session->description       = null;
         $session->session_location  = null;
         $session->date              = null;
         $session->hits              = 0;
         $session->published         = 0;
         $session->checked_out       = 0;
         $session->checked_out_time  = 0;
         $session->ordering          = 0;
         $session->archived          = 0;
         $session->params            = null;
         $this->_data                = $session;
         return (boolean) $this->_data;
      }
      return true;
   }
}