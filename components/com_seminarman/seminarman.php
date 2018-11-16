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

if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

if (version_compare(JVERSION, "3.6.2", 'ge')) {
    $document = JFactory::getDocument();
    $document->addScriptDeclaration("
	if (typeof jQuery == 'undefined') {
        // jQuery is not loaded
    } else {
        // jQuery is loaded
        jQuery(function($) {
          $('.hasTip').each(function() {
            var title = $(this).attr('title');
            if (title) {
	          var parts = title.split('::', 2);
		      if (typeof jQuery.fn.popover != 'undefined') { // Joomla 3.0+ Bootstrap is loaded
			    // try to build jquery bootstrap tooltip with class hasPopover
			    $(this).addClass('hasPopover');
                $(this).removeClass('hasTip');
                $(this).attr('title', '');
			    $(this).attr('data-original-title', parts[0]);
                $(this).attr('data-content', parts[1]);
                $(this).attr('data-placement', 'top');
			  } else { // Joomla 3.0+ Bootstrap is not loaded
                if (typeof Tips != 'undefined') { // but MooTools available
                    var sman_el = document.id(this);
	                sman_el.store('tip:title', parts[0]);
	                sman_el.store('tip:text', parts[1]);
                }
              }
            }
		  });
		  if (typeof Tips != 'undefined') { // Tips Object comes from mootools-more, use it only if mootools-more is loaded
			var JTooltips = new Tips($('.hasTip').get(), { 'maxTitleChars': 50, 'fixed': false});
		  }
          if (typeof jQuery.fn.popover != 'undefined') $('.hasPopover').popover({'html': true,'trigger': 'hover focus','container': 'body'});
        });
    }
    ");
}

if (version_compare(JVERSION, "3.0", 'ge') && version_compare(JVERSION, "3.6.2", 'lt')) {
    $document = JFactory::getDocument();
    $document->addScriptDeclaration("
	if (typeof jQuery == 'undefined') {
        // jQuery is not loaded
    } else {
        // jQuery is loaded
        jQuery(function($) {
          $('.hasTip').each(function() {
            var title = $(this).attr('title');
            if (title) {
	          var parts = title.split('::', 2);
		      if (typeof jQuery.fn.popover != 'undefined') { // Joomla 3.0+ Bootstrap is loaded
			    // try to build jquery bootstrap tooltip with class hasTooltip
			    $(this).addClass('hasTooltip');
                $(this).removeClass('hasTip');
			    $(this).attr('data-original-title', '<strong>' + parts[0] + '</strong><br />' + parts[1]);
                $(this).attr('title', '<strong>' + parts[0] + '</strong><br />' + parts[1]);
			  } else { // Joomla 3.0+ Bootstrap is not loaded
                if (typeof Tips != 'undefined') { // but MooTools available
                    var sman_el = document.id(this);
	                sman_el.store('tip:title', parts[0]);
	                sman_el.store('tip:text', parts[1]);
                }
              }
            }
		  });
		  if (typeof Tips != 'undefined') { // Tips Object comes from mootools-more, use it only if mootools-more is loaded
			var JTooltips = new Tips($('.hasTip').get(), { 'maxTitleChars': 50, 'fixed': false});
		  }
          if (typeof jQuery.fn.popover != 'undefined') $('.hasTooltip').tooltip({'html': true,'container': 'body'});
        });
    }
    ");
}

if (version_compare(JVERSION, "3.0", 'ge')) {
    $document = JFactory::getDocument();
    $document->addScriptDeclaration("
	if (typeof jQuery == 'undefined') {
        // jQuery is not loaded
    } else {
        // jQuery is loaded
		// The following is a fix for the possible conflict between jQuery Bootstrap 3 tooltip and mootools-more
		jQuery(document).ready(function(event) {
          jQuery('.hasTooltip').on('hidden.bs.tooltip', function(){
            jQuery(this).css('display','');
          });
          jQuery('.hasPopover').on('hidden.bs.popover', function(){
            jQuery(this).css('display','');
          });
        });
    }
    ");
}

class JPaneOSGF {
	var $_type = NULL;
	var $_paneID = NULL;
	
	function __construct() {
		$this->_paneID = '';
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
		
		if ($this->_type == "tabs") {
			if (JVERSION >= 3.1) {
				// bootstrap (todo)
				// $this->_paneID = $PaneID;
				// return JHtml::_('bootstrap.startTabSet', $PaneID, $options);
				return JHtml::_('tabs.start', $PaneID, $options);
			} else {
			    // old mootools
				return JHtml::_('tabs.start', $PaneID, $options);
			}
		}
	}

	function startPanel($title, $titleID) {
		if ($this->_type == "tabs") {
			if (JVERSION >= 3.1) {
				// bootstrap (todo)
				// return JHtml::_('bootstrap.addTab', $this->_paneID, $titleID, $title);
				return JHtml::_('tabs.panel', $title, $titleID);
			} else {
				// old mootools
			    return JHtml::_('tabs.panel', $title, $titleID);
			}
		}
	}

	function endPanel(){
		if (JVERSION >= 3.1) {
			// bootstrap (todo)
			// return JHtml::_('bootstrap.endTab');
			return;
		} else {
		    // old mootools
			return;
		}
	}

	function endPane(){
		if (JVERSION >= 3.1) {
			// bootstrap (todo)
			// return JHtml::_('bootstrap.endTabSet');
			return JHtml::_('tabs.end');
		} else {
		    // old mootools
			return JHtml::_('tabs.end');
		}
	}
}

require_once (JPATH_COMPONENT . DS . 'classes' . DS . 'helper.php');
require_once (JPATH_COMPONENT . DS . 'classes' . DS . 'categories.php');

require_once ( JPATH_ROOT.DS.'components'.DS.'com_seminarman'.DS.'defines.seminarman.php');

$params = JComponentHelper::getParams('com_seminarman');
define('COM_SEMINARMAN_FILEPATH', JPATH_ROOT . DS . $params->get('file_path','components/com_seminarman/uploads'));

$language = JFactory::getLanguage();
$language->load('com_seminarman', JPATH_SITE, 'en-GB', true);
$language->load('com_seminarman', JPATH_SITE, null, true);

JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . DS . 'tables');

require_once (JPATH_COMPONENT . DS . 'controller.php');

if ($controller = JRequest::getWord('controller'))
{
    $path = JPATH_COMPONENT . DS . 'controllers' . DS . $controller . '.php';
    if (file_exists($path))
    {
        require_once $path;
    } else
    {
        $controller = '';
    }
}


$classname = 'SeminarmanController' . ucfirst($controller);
$controller = new $classname();

$controller->execute(JRequest::getVar('task', null, 'default', 'cmd'));

$controller->redirect();

?>