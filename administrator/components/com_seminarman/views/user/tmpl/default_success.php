<?php
/**
 * Copyright (C) 2015 Open Source Group GmbH www.osg-gmbh.de
 * @website http://www.osg-gmbh.de
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 **/
defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.tooltip');
jimport('joomla.utilities.date');
$uid = JRequest::getVar('uid');
$user = JFactory::getUser($uid);
echo "<h3>" . $user->name . " (" . $user->username . ")</h3>";
?>
<p>
<?php echo JText::_('COM_SEMINARMAN_OPERATION_SUCCESSFULL'); ?>
</p>