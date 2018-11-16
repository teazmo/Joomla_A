<?php

defined('JPATH_BASE') or die;
jimport('joomla.utilities.date');

class plgUserSeminarman extends JPlugin
{

	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}

	/**
	 * Remove all user profile information for the given user ID
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param	array		$user		Holds the user data
	 * @param	boolean		$success	True if user was succesfully stored in the database
	 * @param	string		$msg		Message
	 */
	function onUserAfterDelete($user, $success, $msg)
	{
		
 		if (!$success) {
 			return false;
 		}

 		$userId	= JArrayHelper::getValue($user, 'id', 0, 'int');

 		if ($userId)
		{
			try
			{
				$db = JFactory::getDbo();
				$db->setQuery('DELETE FROM #__seminarman_fields_values_users WHERE user_id = '.$userId);

				if (!$db->query()) {
					throw new Exception($db->getErrorMsg());
				}
				
				$db->setQuery('DELETE FROM #__seminarman_fields_values_users_static WHERE user_id = '.$userId);
				
				if (!$db->query()) {
					throw new Exception($db->getErrorMsg());
				}
			}
			catch (JException $e)
			{
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}

		return true;
	}
}
