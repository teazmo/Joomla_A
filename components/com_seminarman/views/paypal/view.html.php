<?php
/**
 * @version $Id: 1.5.4 2009-10-15
 * @package       Course Manager
 * @subpackage    Component
 * @author        Profinvent {@link http://www.profinvent.com}
 * @copyright     (C) Profinvent - Joomla Experts
 * @license       GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the seminarman component
 *
 * @static
 * @package    Course Manager
 * @subpackage seminarman
 * @since 1.5.0
 */
class seminarmanViewpaypal extends JViewLegacy
{
   function display($tpl = null)
   {
      $mainframe = JFactory::getApplication();

      if($this->getLayout() <> 'form') {
         $this->_displayForm($tpl);
         return;
      }

      parent::display($tpl);
   }

   function _displayForm($tpl)
   {
      $mainframe = JFactory::getApplication();
      jimport( 'joomla.html.parameter' );

      $pathway = $mainframe->getPathway();
      $document = JFactory::getDocument();
      $model = $this->getModel();
      $user = JFactory::getUser();
      $uri = JFactory::getURI();
      $params = $mainframe->getParams();

      // JHTML::_('behavior.tooltip');
      if (JVERSION >= 3.4) {
          JHtml::_('behavior.formvalidator');
      } else {
          JHTML::_('behavior.formvalidation');
      }
      
      //get course id from url
      $bookingid = (int) JRequest::getVar('bookingid');
      
      //Get data from Model - course details
      $db = JFactory::getDBO();
      
      $query = $db->getQuery(true);
      $query->select( 'b.*' );
      $query->select( 'cr.title' );
      $query->select( 'cr.code' );
      $query->from( '`#__seminarman_application` AS b' );
      $query->join( "LEFT", '`#__seminarman_courses` AS cr ON cr.id = b.course_id' );
      $query->where( 'b.id = '.$bookingid );
      
      $db->setQuery( $query );
      
      $bookingDetails = $db->loadObject();
      $bookingDetails->bookingid = $bookingid;

   	  if ( (!$bookingid) || ($bookingDetails->email==null))
   	  	JError::raiseError( 403, JText::_('COM_SEMINARMAN_ALERTNOTAUTH') );

      //check if booking is enabled
   	  if ( ($params->get( 'enable_bookings') ) == '0' )
   	  	JError::raiseError( 403, JText::_('COM_SEMINARMAN_ALERTNOTAUTH') );

   	  $query = $db->getQuery(true);
   	  $query->select( 'field.*' );
   	  $query->select( 'value.value' );
   	  $query->from( '`#__seminarman_fields` AS field' );
   	  $query->join( "LEFT", '`#__seminarman_fields_values` AS value ON field.id=value.field_id AND value.applicationid=' . $bookingid );
   	  $query->where( 'field.published=' . $db->Quote('1') );
   	  $query->order( 'field.ordering' );
   	  
   	  $db->setQuery( $query );
   	  $fields = $db->loadAssocList();
   	  
   	  // Set page title
   	  $menus = JFactory::getApplication()->getMenu();
   	  $menu = $menus->getActive();
   	  
   	  $jversion = new JVersion();
   	  $short_version = $jversion->getShortVersion();
   	  
   	  // because the application sets a default page title, we need to get it
   	  // right from the menu item itself
   	  if (is_object( $menu )) {
   	  	if (version_compare($short_version, "3.0", 'ge')) {
   	  	    $menu_params = new JRegistry( $menu->params );
   	  	} else {
   	  		$menu_params = new JParameter( $menu->params );
   	  	}
   	  	if (!$menu_params->get( 'page_title')) {
   	  		$params->set('page_title', JText::_( JText::_('COM_SEMINARMAN_ONLINE_PAYMENT') ));
   	  	}
   	  }
   	  else {
   	  	$params->set('page_title', JText::_( JText::_('COM_SEMINARMAN_ONLINE_PAYMENT') ));
   	  }
   	  
   	  $document->setTitle( $params->get( 'page_title' ) );

 	  // calculate displayed price
   	  $amount = $bookingDetails->price_per_attendee;
   	  $amount += ($amount / 100) * $bookingDetails->price_vat;   	  
   	  // paypal has to round it before submit, it causes 1 cent error, we fix it by just submitting the summary :/
   	  $amount = $amount * $bookingDetails->attendees;
   	  
   	  // extra fees coming from plugin
   	  $dispatcher=JDispatcher::getInstance();
   	  JPluginHelper::importPlugin('seminarman');
   	  $extrafees=$dispatcher->trigger('onPaypalCalc', array($bookingDetails));  // we need booking id, attendees etc.
   	  if(isset($extrafees) && !empty($extrafees)) $amount += $extrafees[0];
   	  
   	  // maybe payment fee
   	  $app_params_obj = new JRegistry();
   	  $app_params_obj->loadString($bookingDetails->params);
   	  $app_params = $app_params_obj->toArray();
   	  
   	  if (isset($app_params['payment_fee'])) {
   	  	$payment_fee = doubleval(str_replace(",", ".", $app_params['payment_fee']));
   	  	$amount += $payment_fee;
   	  }
   	  
   	  $amount = round($amount, 2);
   	  
   	  $username = $user->get('username');
   	  $useremail = $user->get('email');
   	  $userid = $user->get('id');
   	  $this->assignRef('username', $username);
   	  $this->assignRef('email', $useremail);
   	  $this->assignRef('userid', $userid);
   	  $this->assign('action', $uri->toString());
   	  $this->assignRef('lists', $lists);
   	  $this->assignRef('amount', $amount);
   	  $this->assignRef('bookingDetails', $bookingDetails);
   	  $this->assignRef('course_sessions', $course_sessions);
   	  $this->assignRef('courseid', $courseid);
   	  $this->assignRef('fields', $fields);
   	  $this->assignRef('params', $params);
   	  parent::display($tpl);
   }
}
?>
