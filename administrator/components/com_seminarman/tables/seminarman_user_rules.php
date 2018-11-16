<?php
/**
 * @version $Id: 2.8.9
 * @package			OSG Seminar Manager
 * @subpackage		Component
 * @author			OSG {@link http://www.osg-gmbh.de}
 * @copyright 		(C) Open Source Group 
 * @license   		GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
* user_rules Table class
*
* @package		OSG Seminar Manager
* @subpackage	seminarman
* @since 2.8.9
*/
class seminarman_user_rules extends JTable
{

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 * @since 1.0
	 */
    function __construct(&$db) 
    {
		parent::__construct('#__seminarman_user_rules', 'id', $db);
	}
}