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

jimport('joomla.application.component.view');

class SeminarmanViewSeminarman extends JViewLegacy
{
    function display($tpl = null)
    {	
    	jimport('joomla.html.pane');
        $jversion = new JVersion();
        $short_version = $jversion->getShortVersion();
        $document = JFactory::getDocument();
        if (version_compare($short_version, "3.0", 'ge')) {
        	$pane = JPaneOSG::getInstance('sliders');
        } else {
        	$pane = JPane::getInstance('sliders');
        }
        $lang = JFactory::getLanguage();
        $params = JComponentHelper::getParams('com_seminarman');

        $latestJobs = $this->get('LatestJobs');
        $latestApplications = $this->get('LatestApplications');

        JToolBarHelper::title(JText::_('COM_SEMINARMAN'), 'seminarman');
        
   		 if (JFactory::getUser()->authorise('core.admin', 'com_seminarman')) {
  			JToolBarHelper::preferences('com_seminarman');
		}

        $document->addStyleSheet('components/com_seminarman/assets/css/seminarmanbackend.css');
        if (version_compare($short_version, "3.0", 'ge')) {
        	$document->addStyleSheet('components/com_seminarman/assets/css/seminarmanbackend_j3.x.css');
        }
        if ($lang->isRTL())
        {
            $document->addStyleSheet('components/com_seminarman/assets/css/seminarmanbackend_rtl.css');
        }

        require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS .
            'helpers' . DS . 'seminarman.php');

        JHTMLSeminarman::buildSideMenu();
        
        if ($params->get('show_updatecheck'))
        {
            $check = $this->get('Update');

        } else
        {
            $check = array();
            $check['enabled'] = 0;
            $check['connect'] = 0;
        }


        $this->assignRef('pane', $pane);
        $this->assignRef('latestApplications', $latestApplications);
        $this->assignRef('latestJobs', $latestJobs);
        $this->assignRef('check', $check);

        parent::display($tpl);

    }

    function quickiconButton($link, $image, $text, $modal = 0)
    {
        $lang = JFactory::getLanguage();
?>
		<div class="<?php echo ($lang->isRTL()) ? 'floright' : 'floleft'; ?>">
			<div class="icon">
	<?php
        if ($modal == 1)
        {
            JHTML::_('behavior.modal');
   ?>
	        <a href="<?php echo $link . '&amp;tmpl=component'; ?>" style="cursor:pointer" class="modal" rel="{handler: 'iframe', size: {x: 650, y: 400}}">
	        <?php echo JHTML::_('image', 'administrator/components/com_seminarman/assets/images/' . $image, $text); ?><span><?php echo $text; ?></span></a>
	<?php
        } else
        {
    ?>
			<a href="<?php echo $link; ?>"><?php echo JHTML::_('image', 'administrator/components/com_seminarman/assets/images/' . $image, $text); ?><span><?php echo $text; ?></span></a>
	<?php
        }        
    ?>
			</div>
		</div>
		<?php
    }
}
?>
