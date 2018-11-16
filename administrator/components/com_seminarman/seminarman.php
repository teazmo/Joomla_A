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
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_seminarman')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

$jversion = new JVersion();
$short_version = $jversion->getShortVersion();
if (version_compare($short_version, "3.0", 'ge')) {
JHTML::_('behavior.framework', true);
$document = JFactory::getDocument();
$document->addScriptDeclaration("
  window.addEvent('domready', function() {
    $$('.hasTip').each(function(el) {
      var title = el.get('title');
      if (title) {
	var parts = title.split('::', 2);
	el.store('tip:title', parts[0]);
	el.store('tip:text', parts[1]);
      }
    });
    var JTooltips = new Tips($$('.hasTip'), { maxTitleChars: 50, fixed: false});
  });
");
}
require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS . 'helpers' . DS . 'seminarman.php');
if(!JHTMLSeminarman::UserIsCourseManager()){
	$document->addStyleSheet('components/com_seminarman/assets/css/seminarmantutor.css');
}

class JPaneOSG {
	var $_type = NULL;
	
	function __construct() {
		
	}
	
	static function getInstance($typ) {
	    $obj = New self();
	    $obj->_type = $typ;    	
	    return $obj;
	}

	function startPane($PaneID, $options=NULL) {
		if (is_null($options)):
		$options = array(
				'onActive' => 'function(title, description){
                description.setStyle("display", "block");
                title.addClass("open").removeClass("closed");
            }',
				'onBackground' => 'function(title, description){
                description.setStyle("display", "none");
                title.addClass("closed").removeClass("open");
            }',
				'startOffset' => 0,  // 0 starts on the first tab, 1 starts the second, etc...
				'useCookie' => true, // using cookie to load last tab state.
		);
		endif;
		
	   if ($this->_type == "sliders") {
	   	    return JHtml::_('sliders.start', $PaneID, $options);
	   } else {
			return JHtml::_('tabs.start', $PaneID, $options);
		}		
	}
	
	function startPanel($title, $titleID) {
		if ($this->_type == "sliders") {
			return JHtml::_('sliders.panel', $title, $titleID);
		} else {
			return JHtml::_('tabs.panel', $title, $titleID);
		}
	}
	
	function endPanel(){
		return;
	}
	
	function endPane(){
		if ($this->_type == "sliders") {
		    return JHtml::_('sliders.end');
		} elseif ($this->_type == "tabs") {
			return JHtml::_('tabs.end');
		}
	}
}

require_once (JPATH_COMPONENT_SITE.DS.'classes'.DS.'helper.php');
require_once (JPATH_COMPONENT_SITE.DS.'classes'.DS.'categories.php');

// Set the table directory
JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');

//Set filepath
$params = JComponentHelper::getParams('com_seminarman');
define('COM_SEMINARMAN_FILEPATH',    JPATH_ROOT.DS.$params->get('file_path', 'components/com_seminarman/upload'));
define('COM_SEMINARMAN_IMAGEPATH',    JPATH_ROOT.DS.$params->get('image_path', 'images'));
define('COM_SEMINARMAN_CVFILEPATH',    JPATH_ROOT.DS.$params->get('cv_file_path', 'components/com_seminarman/upload/cv'));
define('COM_SEMINARMAN_UPLOADEDCVFILEPATH',    JPATH_ROOT.DS.$params->get('uploaded_cv_file_path', 'components/com_seminarman/upload/user_cv'));

// Require the base controller
require_once (JPATH_COMPONENT.DS.'controller.php');

$language = JFactory::getLanguage();
$language->load('com_seminarman', JPATH_ADMINISTRATOR, 'en-GB', true);
$language->load('com_seminarman', JPATH_ADMINISTRATOR, null, true);

// Require specific controller if requested
if( $controller = JRequest::getWord('controller') ) {
	$path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
	if (file_exists($path)) {
		require_once $path;
	} else {
		$controller = '';
	}
}

//Create the controller
$classname  = 'SeminarmanController'.$controller;
$controller = new $classname( );

// Perform the Request task
$controller->execute( JRequest::getWord('task'));
$controller->redirect();

?>