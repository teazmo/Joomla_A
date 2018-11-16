<?php
/**
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011-15 Open Source Group GmbH www.osg-gmbh.de
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

class seminarman_html
{
    static function printbutton($print_link, $params)
    {
        if ($params->get('show_print_icon'))
        {

            // JHTML::_('behavior.tooltip');

            $status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';

            if ($params->get('show_icons'))
            {
                // $image = JHTML::_('image.site', 'printButton.png', 'media/system/images/', null, null,
                //    JText::_('COM_SEMINARMAN_PRINT'));
            	$image = JHTML::_('image', 'media/system/images/printButton.png', JText::_('COM_SEMINARMAN_PRINT'));
            } else
            {
                $image = JText::_('COM_SEMINARMAN_ICON_SEPARATOR') . '&nbsp;' . JText::_('COM_SEMINARMAN_PRINT') . '&nbsp;' . JText::
                    _('COM_SEMINARMAN_ICON_SEPARATOR');
            }

            if (JRequest::getInt('pop'))
            {

                $output = '<a href="#" onclick="window.print();return false;">' . $image .
                    '</a>';
            } else
            {

                $overlib = JText::_('COM_SEMINARMAN_PRINT_TIP');
                $text = JText::_('COM_SEMINARMAN_PRINT');

                $output = '<a href="' . JRoute::_($print_link) .
                    '" class="editlinktip hasTip" onclick="window.open(this.href,\'win2\',\'' . $status .
                    '\'); return false;" title="' . $text . '::' . $overlib . '">' . $image . '</a>';
            }

            return $output;
        }
        return;
    }

    static function mailbutton($view, $params, $slug = null, $courseslug = null)
    {
        if ($params->get('show_email_icon'))
        {

            // JHTML::_('behavior.tooltip');
            $uri = JURI::getInstance();
            $base = $uri->toString(array('scheme', 'host', 'port'));

            if ($view == 'category') {
                $link = $base . JRoute::_('index.php?option=com_seminarman&view=' . $view . '&cid=' . $slug, false);
            } elseif ($view == 'courses') {
                $link = $base . JRoute::_('index.php?option=com_seminarman&view=' . $view . '&cid=' . $slug . '&id=' . $courseslug, false);
            } elseif ($view == 'templates') {
                $link = $base . JRoute::_('index.php?option=com_seminarman&view=' . $view . '&cid=' . $slug . '&id=' . $courseslug, false);
            } elseif ($view == 'tags') {
                $link = $base . JRoute::_('index.php?option=com_seminarman&view=' . $view . '&id=' . $slug, false);
            } else {
                $link = $base . JRoute::_('index.php?option=com_seminarman&view=' . $view, false);
            }
            require_once JPATH_SITE . '/components/com_mailto/helpers/mailto.php';
            // $url = 'index.php?option=com_mailto&tmpl=component&link=' . base64_encode($link);
            $url = 'index.php?option=com_mailto&tmpl=component&link=' . MailToHelper::addLink($link);
            $status = 'width=400,height=300,menubar=yes,resizable=yes';

            if ($params->get('show_icons'))
            {
                // $image = JHTML::_('image.site', 'emailButton.png', 'media/system/images/', null, null,
                //    JText::_('COM_SEMINARMAN_EMAIL'));
            	$image = JHTML::_('image', 'media/system/images/emailButton.png', JText::_('COM_SEMINARMAN_EMAIL'));
            } else
            {
                $image = JText::_('COM_SEMINARMAN_ICON_SEPARATOR') . '&nbsp;' . JText::_('COM_SEMINARMAN_EMAIL'). '&nbsp;' . JText::
                    _('COM_SEMINARMAN_ICON_SEPARATOR');
            }

            $overlib = JText::_('COM_SEMINARMAN_EMAIL_TIP');
            $text = JText::_('COM_SEMINARMAN_EMAIL');

            $output = '<a href="' . JRoute::_($url) .
                '" class="editlinktip hasTip" onclick="window.open(this.href,\'win2\',\'' . $status .
                '\'); return false;" title="' . $text . '::' . $overlib . '">' . $image . '</a>';

            return $output;
        }
        return;
    }


    static function saveContentPrep(&$row)
    {	
    	$text = JRequest::getVar('text', '', 'post', 'string', JREQUEST_ALLOWRAW);
        $certificate_text = JRequest::getVar('certificate_text', '', 'post', 'string', JREQUEST_ALLOWRAW);

        $text = str_replace('<br>', '<br />', $text);
        $certificate_text = str_replace('<br>', '<br />', $certificate_text);

        $pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
        $tagPos = preg_match($pattern, $text);

        if ($tagPos == 0)
        {
            $row->introtext = $text;
        } else
        {
            list($row->introtext, $row->fulltext) = preg_split($pattern, $text, 2);
        }
        $row->certificate_text = $certificate_text;

        // the following codes for text filter are actually out of date and need to be updated
        // for now we simply disable them
        jimport('joomla.application.component.helper');
        $config = JComponentHelper::getParams('com_content');
        $user = JFactory::getUser();
        $gid = $user->get('gid');

        $filterGroups = $config->get('filter_groups');

        if (is_array($filterGroups) && in_array($gid, $filterGroups))
        {
            $filterType = $config->get('filter_type');
            $filterTags = preg_split('#[,\s]+#', trim($config->get('filter_tags')));
            $filterAttrs = preg_split('#[,\s]+#', trim($config->get('filter_attritbutes')));
            switch ($filterType)
            {
                case 'NH':
                    $filter = new JFilterInput();
                    break;
                case 'WL':
                    $filter = new JFilterInput($filterTags, $filterAttrs, 0, 0, 0);

                    break;
                case 'BL':
                default:
                    $filter = new JFilterInput($filterTags, $filterAttrs, 1, 1);
                    break;
            }
            // $row->introtext = $filter->clean($row->introtext);
            // $row->fulltext = $filter->clean($row->fulltext);
            // $row->certificate_text = $filter->clean($row->certificate_text);
        } elseif (empty($filterGroups) && $gid != '25')
        {
            $filter = new JFilterInput(array(), array(), 1, 1);
            // $row->introtext = $filter->clean($row->introtext);
            // $row->fulltext = $filter->clean($row->fulltext);
            // $row->certificate_text = $filter->clean($row->certificate_text);
        }

        return true;
    }

    static function favoure($course, $params, $favoured)
    {
        $user = JFactory::getUser();

        // JHTML::_('behavior.tooltip');

        if ($user->id && $favoured)
        {
            if ($params->get('show_icons'))
            {
                // $image = JHTML::_('image.site', 'heart_delete.png',
                //    'components/com_seminarman/assets/images/', null, null, JText::_('COM_SEMINARMAN_REMOVE_FAVOURITE'));
            	$image = JHTML::_('image', 'components/com_seminarman/assets/images/heart_delete.png', JText::_('COM_SEMINARMAN_REMOVE_FAVOURITE'));
            } else
            {
                $image = JText::_('COM_SEMINARMAN_ICON_SEPARATOR') . '&nbsp;' . JText::_('COM_SEMINARMAN_REMOVE_FAVOURITE') .
                    '&nbsp;' . JText::_('COM_SEMINARMAN_ICON_SEPARATOR');
            }
            $overlib = JText::_('COM_SEMINARMAN_REMOVE_FAVOURITE_TIP');
            $text = JText::_('COM_SEMINARMAN_REMOVE_FAVOURITE');

            $link = 'index.php?option=com_seminarman&task=removefavourite&cid=' . $course->categoryslug . '&id=' . $course->
                slug;
            $output = '<a href="' . JRoute::_($link) .
                '" class="editlinktip hasTip" title="' . $text . '::' . $overlib . '">' . $image .
                '</a>';
        } elseif ($user->id)
        {
            if ($params->get('show_icons'))
            {
                // $image = JHTML::_('image.site', 'heart_add.png',
                //    'components/com_seminarman/assets/images/', null, null, JText::_('COM_SEMINARMAN_FAVOURE'));
            	$image = JHTML::_('image', 'components/com_seminarman/assets/images/heart_add.png', JText::_('COM_SEMINARMAN_FAVOURITE'));
            } else
            {
                $image = JText::_('COM_SEMINARMAN_ICON_SEPARATOR') . '&nbsp;' . JText::_('COM_SEMINARMAN_FAVOURE') . '&nbsp;' .
                    JText::_('COM_SEMINARMAN_ICON_SEPARATOR');
            }
            $overlib = JText::_('COM_SEMINARMAN_FAVOURE_TIP');
            $text = JText::_('COM_SEMINARMAN_FAVOURE');

            $link = 'index.php?option=com_seminarman&task=addfavourite&cid=' . $course->categoryslug . '&id=' . $course->
                slug;
            $output = '<a href="' . JRoute::_($link) .
                '" class="editlinktip hasTip" title="' . $text . '::' . $overlib . '">' . $image .
                '</a>';
        } else
        {

            $overlib = JText::_('COM_SEMINARMAN_FAVOURE_LOGIN_TIP');
            $text = JText::_('COM_SEMINARMAN_FAVOURE');

            if ($params->get('show_icons'))
            {
                // $image = JHTML::_('image.site', 'heart_login.png',
                //    'components/com_seminarman/assets/images/', null, null, $text,
                //    'class="editlinktip hasTip" title="' . $text . '::' . $overlib . '"');
            	$image = JHTML::_('image', 'components/com_seminarman/assets/images/heart_login.png', $text, 'class="editlinktip hasTip" title="' . $text . '::' . $overlib . '"');
            } else
            {
                $image = JText::_('COM_SEMINARMAN_ICON_SEPARATOR') . '&nbsp;' .
                    '<span class="editlinktip hasTip" title="' . $text . '::' . $overlib . '">' . $text .
                    '</span>';
            }

            $output = $image;
        }

        return $output;
    }

    static function favouritesbutton($params)
    {
        if ($params->get('show_favourites'))
        {
            // JHTML::_('behavior.tooltip');

            if ($params->get('show_icons'))
            {
                // $image = JHTML::_('image.site', 'heart.png',
                //    'components/com_seminarman/assets/images/', null, null, JText::_('COM_SEMINARMAN_FAVOURITES'));
            	$image = JHTML::_('image', 'components/com_seminarman/assets/images/heart.png', JText::_('COM_SEMINARMAN_FAVOURITES'));
            } else
            {
                $image = JText::_('COM_SEMINARMAN_ICON_SEPARATOR') . '&nbsp;' . JText::_('COM_SEMINARMAN_FAVOURITES') . '&nbsp;' .
                    JText::_('COM_SEMINARMAN_ICON_SEPARATOR');
            }
            $overlib = JText::_('COM_SEMINARMAN_FAVOURITES_TIP');
            $text = JText::_('COM_SEMINARMAN_FAVOURITES');

            $link = 'index.php?option=com_seminarman&view=favourites';
            $output = '<a href="' . JRoute::_($link) .
                '" class="editlinktip hasTip" title="' . $text . '::' . $overlib . '">' . $image .
                '</a>';

            return $output;
        }
        return;
    }

    function removefavbutton(&$params, $course)
    {
        $user = JFactory::getUser();
        // JHTML::_('behavior.tooltip');

        if ($user->id)
        {
            if ($params->get('show_favourites'))
            {
                // JHTML::_('behavior.tooltip');

                if ($params->get('show_icons'))
                {
                    // $image = JHTML::_('image.site', 'delete.png',
                    //    'components/com_seminarman/assets/images/', null, null, JText::_('COM_SEMINARMAN_REMOVE_FAVOURITE'));
                	$image = JHTML::_('image', 'components/com_seminarman/assets/images/delete.png', JText::_('COM_SEMINARMAN_REMOVE_FAVOURITE'));
                } else
                {
                    $image = JText::_('COM_SEMINARMAN_ICON_SEPARATOR') . '&nbsp;' . JText::_('COM_SEMINARMAN_FAVOURITES') . '&nbsp;' .
                        JText::_('COM_SEMINARMAN_ICON_SEPARATOR');
                }
                $overlib = JText::_('COM_SEMINARMAN_REMOVE_FAVOURITE_TIP');
                $text = JText::_('COM_SEMINARMAN_REMOVE_FAVOURITE');

                $link = 'index.php?option=com_seminarman&task=removefavourite&id=' . $course->id;
                $output = '<a href="' . JRoute::_($link) .
                    '" class="editlinktip hasTip" title="' . $text . '::' . $overlib . '">' . $image .
                    '</a>';

                return $output;
            }
        }
        return;
    }
    
    static function addSiteStyles($view) {
    	$document = JFactory::getDocument();
    	$lang = JFactory::getLanguage();
    	
    	$document->addStyleSheet($view->baseurl .
    			'/components/com_seminarman/assets/css/seminarman.css');
    	if ($lang->isRTL()){
    		$document->addStyleSheet($view->baseurl .
    				'/components/com_seminarman/assets/css/seminarman_rtl.css');
    	}
    	
    	$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #seminarman dd { height: 1%; }</style><![endif]-->');
    	
    	// don't worry,  IE10 ignores conditional comments,
    	// therefore, exclude IE9 and IE9- only (actually IE8 and IE8- don't support media query anyway)
    	$stylelink_responsive = '<!--[if !IE]><!-->' ."\n";
    	$stylelink_responsive .= '<link rel="stylesheet" href="'.$view->baseurl .
    			'/components/com_seminarman/assets/css/seminarman.responsive.css'.'" type="text/css" />' ."\n";
    	$stylelink_responsive .= '<![endif]-->' ."\n";
    	$document->addCustomTag($stylelink_responsive);
    	
    	$stylelink_ie9 = '<!--[if lte IE 9]>' ."\n";
    	$stylelink_ie9 .= '<link rel="stylesheet" href="'.$view->baseurl .
    	'/components/com_seminarman/assets/css/seminarman.responsive.ie9.css'.'" type="text/css" />' ."\n";
    	$stylelink_ie9 .= '<![endif]-->' ."\n";
    	$document->addCustomTag($stylelink_ie9);
    	
    }
}

class seminarman_upload
{
    static function check($file, &$err)
    {
        $params = JComponentHelper::getParams('com_seminarman');

        if (empty($file['name']))
        {
            $err = 'Please input a file for upload';
            return false;
        }

        jimport('joomla.filesystem.file');
        if ($file['name'] !== JFile::makesafe($file['name']))
        {
            $err = 'COM_SEMINARMAN_WARNFILENAME';
            return false;
        }

        $format = strtolower(JFile::getExt($file['name']));

        $allowable = explode(',', $params->get('upload_extensions'));
        $ignored = explode(',', $params->get('ignore_extensions'));
        if (!in_array($format, $allowable) && !in_array($format, $ignored))
        {
            $err = JText::_('COM_SEMINARMAN_WARNFILETYPE');
            return false;
        }

        $maxSize = (int)$params->get('upload_maxsize', 0);
        if ($maxSize > 0 && (int)$file['size'] > $maxSize)
        {
            $err = 'COM_SEMINARMAN_WARNFILETOOLARGE';
            return false;
        }

        $imginfo = null;

        $images = explode(',', $params->get('image_extensions'));

        if ($params->get('restrict_uploads', 1))
        {

            if (in_array($format, $images))
            {

                if (($imginfo = getimagesize($file['tmp_name'])) === false)
                {
                    $err = 'COM_SEMINARMAN_WARNINVALIDIMG';
                    return false;
                }

            } else
                if (!in_array($format, $ignored))
                {

                    $allowed_mime = explode(',', $params->get('upload_mime'));
                    $illegal_mime = explode(',', $params->get('upload_mime_illegal'));

                    if (function_exists('finfo_open') && $params->get('check_mime', 1))
                    {

                        $finfo = finfo_open(FILEINFO_MIME);
                        $type = finfo_file($finfo, $file['tmp_name']);
                        if (strlen($type) && !in_array($type, $allowed_mime) && in_array($type, $illegal_mime))
                        {
                            $err = 'COM_SEMINARMAN_WARNINVALIDMIME';
                            return false;
                        }
                        finfo_close($finfo);

                    } else
                        if (function_exists('mime_content_type') && $params->get('check_mime', 1))
                        {

                            $type = mime_content_type($file['tmp_name']);

                            if (strlen($type) && !in_array($type, $allowed_mime) && in_array($type, $illegal_mime))
                            {
                                $err = 'COM_SEMINARMAN_WARNINVALIDMIME';
                                return false;
                            }

                        }
                }
        }
        $xss_check = JFile::read($file['tmp_name'], false, 256);
        $html_tags = array('abbr', 'acronym', 'address', 'applet', 'area', 'audioscope',
            'base', 'basefont', 'bdo', 'bgsound', 'big', 'blackface', 'blink', 'blockquote',
            'body', 'bq', 'br', 'button', 'caption', 'center', 'cite', 'code', 'col',
            'colgroup', 'comment', 'custom', 'dd', 'del', 'dfn', 'dir', 'div', 'dl', 'dt',
            'em', 'embed', 'fieldset', 'fn', 'font', 'form', 'frame', 'frameset', 'h1', 'h2',
            'h3', 'h4', 'h5', 'h6', 'head', 'hr', 'html', 'iframe', 'ilayer', 'img', 'input',
            'ins', 'isindex', 'keygen', 'kbd', 'label', 'layer', 'legend', 'li', 'limittext',
            'link', 'listing', 'map', 'marquee', 'menu', 'meta', 'multicol', 'nobr',
            'noembed', 'noframes', 'noscript', 'nosmartquotes', 'object', 'ol', 'optgroup',
            'option', 'param', 'plaintext', 'pre', 'rt', 'ruby', 's', 'samp', 'script',
            'select', 'server', 'shadow', 'sidebar', 'small', 'spacer', 'span', 'strike',
            'strong', 'style', 'sub', 'sup', 'table', 'tbody', 'td', 'textarea', 'tfoot',
            'th', 'thead', 'title', 'tr', 'tt', 'ul', 'var', 'wbr', 'xml', 'xmp', '!DOCTYPE',
            '!--');
        foreach ($html_tags as $tag)
        {

            if (stristr($xss_check, '<' . $tag . ' ') || stristr($xss_check, '<' . $tag .
                '>'))
            {
                $err = 'WARNIEXSS';
                return false;
            }
        }

        return true;
    }

    static function sanitize($base_Dir, $filename)
    {
        jimport('joomla.filesystem.file');

        $filename = preg_replace("/^[.]*/", '', $filename);
        $filename = preg_replace("/[.]*$/", '', $filename);


        $lastdotpos = strrpos($filename, '.');

        $chars = '[^0-9a-zA-Z()_-]';
        $filename = strtolower(preg_replace("/$chars/", '_', $filename));

        $beforedot = substr($filename, 0, $lastdotpos);
        $afterdot = substr($filename, $lastdotpos + 1);


        $now = time();

        while (JFile::exists($base_Dir . $beforedot . '_' . $now . '.' . $afterdot))
        {
            $now++;
        }

        $filename = $beforedot . '_' . $now . '.' . $afterdot;

        return $filename;
    }
}

class seminarman_images
{
    static function BuildIcons($rows)
    {
        jimport('joomla.filesystem.file');

        $basePath = COM_SEMINARMAN_FILEPATH;

        for ($i = 0, $n = count($rows); $i < $n; $i++)
        {

            if (is_file($basePath . DS . $rows[$i]->filename))
            {
                $path = str_replace(DS, '/', JPath::clean($basePath . DS . $rows[$i]->filename));

                $size = filesize($path);

                if ($size < 1024)
                {
                    $rows[$i]->size = $size . ' bytes';
                } else
                {
                    if ($size >= 1024 && $size < 1024 * 1024)
                    {
                        $rows[$i]->size = sprintf('%01.2f', $size / 1024.0) . ' Kb';
                    } else
                    {
                        $rows[$i]->size = sprintf('%01.2f', $size / (1024.0 * 1024)) . ' Mb';
                    }
                }

                $ext = strtolower(JFile::getExt($rows[$i]->filename));
                switch ($ext)
                {

                    case 'jpg':
                    case 'png':
                    case 'gif':
                    case 'xcf':
                    case 'odg':
                    case 'bmp':
                    case 'jpeg':
                        $rows[$i]->icon = 'components/com_seminarman/assets/images/mime-icon-16/image.png';
                        break;

                    default:
                        $icon = JPATH_SITE . DS . 'components' . DS . 'com_seminarman' . DS . 'assets' . DS .
                            'images' . DS . 'mime-icon-16' . DS . $ext . '.png';
                        if (file_exists($icon))
                        {
                            $rows[$i]->icon = 'components/com_seminarman/assets/images/mime-icon-16/' . $ext .
                                '.png';
                        } else
                        {
                            $rows[$i]->icon = 'components/com_seminarman/assets/images/mime-icon-16/unknown.png';
                        }
                        break;
                }
            }
            else
            {
            	$rows[$i]->icon = 'components/com_seminarman/assets/images/mime-icon-16/unknown.png';
            	$rows[$i]->size = '';
            }

        }

        return $rows;
    }
}

class CMFactory
{
/**
 * Return the view object, responsible for all db manipulation. Singleton
 *
 * @param	string		type	libraries/helper
 * @param	string		name 	class prefix
 */
static function load( $type, $name )
{
	//include_once(JPATH_ROOT.DS.'components'.DS.'com_seminarman'.DS.'libraries'.DS.'error.php');

	include_once(JPATH_ROOT.DS.'components'.DS.'com_seminarman'.DS.$type.DS. strtolower($name) .'.php');

	// If it is a library, we call the object and call the 'load' method
	if( $type == 'libraries' )
	{
		$classname = 'C'.$name ;
		if(	class_exists($classname) ) {
			// @todo
		}
	}
}
}

/**
 * Templating system for seminarman
 */
class CMTemplate {
	var $vars; /// Holds all the template variables
	
	/**
	 * Constructor
	 *
	 * @param $file string the file name you want to load
	 */
	function __construct($file = null) {
		$this->file = $file;
		@ini_set('short_open_tag', 'On');
		$this->set('dummy', true);
	}

	function escape( $text )
	{
		CMFactory::load( 'helpers' , 'string' );

		return cEscape( $text );
	}
	function renderModules($position, $attribs = array())
	{
		jimport( 'joomla.application.module.helper' );

		$modules 	= JModuleHelper::getModules( $position );
		$modulehtml = '';

		foreach($modules as $module)
		{
			// If style attributes are not given or set, we enforce it to use the xhtml style
			// so the title will display correctly.
			if( !isset($attribs['style'] ) )
				$attribs['style']	= 'xhtml';

			$modulehtml .= JModuleHelper::renderModule($module, $attribs);
		}

		echo $modulehtml;
	}

	/**
	 * Get the template full path name, given a templaet name code
	 */
	function _getTemplateFullpath($file)
	{
		$cfg	= CMFactory::getConfig();
		if(!JString::strpos($file, '.php'))
		{
			$filename	= $file;

			// Test if template override exists in joomla's template folder
			$mainframe		= JFactory::getApplication();

			$overridePath	= JPATH_ROOT . DS . 'templates' . DS . $mainframe->getTemplate() . DS . 'html';
			$overrideExists	= JFolder::exists( $overridePath . DS . 'com_seminarman' );
			$template		= SEMINARMAN_COM_PATH . DS . 'templates' . DS . $cfg->get('template') . DS . $filename . '.php';

			// Test override path first
			if( JFile::exists( $overridePath . DS . 'com_seminarman' . DS . $filename . '.php') )
			{
				// Load the override template.
				$file	= $overridePath . DS . 'com_seminarman' . DS . $filename . '.php';
			}
			else if( JFile::exists( $template ) && !$overrideExists )
			{
				// If override fails try the template set in config
				$file	= $template;
			}
			else
			{
				// We assume to use the default template
				$file	= SEMINARMAN_COM_PATH . DS . 'templates' . DS . 'default' . DS . $filename . '.php';
			}
		}

		return $file;
	}

	/**
	 * Set a template variable.
	 */
	function set($name, $value) {
		$this->vars[$name] = $value; //is_object($value) ? $value->fetch() : $value;
	}

	/**
	 * Set a template variable by reference
	 */
	function setRef($name, &$value) {
		$this->vars[$name] =& $value; //is_object($value) ? $value->fetch() : $value;
	}

	function addStylesheet( $file )
	{
		$mainframe	= JFactory::getApplication();
		$cfg		= CMFactory::getConfig();

		if(!JString::strpos($file, '.css'))
		{
			$filename	= $file;

			jimport( 'joomla.filesystem.file' );
			jimport( 'joomla.filesystem.folder' );

			// Test if template override exists in joomla's template folder
			$overridePath	= JPATH_ROOT . DS . 'templates' . DS . $mainframe->getTemplate() . DS . 'html';
			$overrideExists	= JFolder::exists( $overridePath . DS . 'com_community' );
			$template		= SEMINARMAN_COM_PATH . DS . 'templates' . DS . $cfg->get('template') . DS . 'css' . DS . $filename . '.css';

			// Test override path first
			if( JFile::exists( $overridePath . DS . 'com_community' . DS . 'css' . DS . $filename . '.css') )
			{
				// Load the override template.
				$file	= '/templates/' . $mainframe->getTemplate() . '/html/com_community/css/' . $filename . '.css';
			}
			else if( JFile::exists( $template ) && !$overrideExists )
			{
				// If override fails try the template set in config
				$file	=  '/components/com_community/templates/' . $cfg->get('template') . '/css/' . $filename . '.css';
			}
			else
			{
				// We assume to use the default template
				$file	= '/components/com_community/templates/default/css/' . $filename . '.css';
			}
		}

		CAssets::attach( $file , 'css' , rtrim( JURI::root() , '/' ) );
	}

	/***
	 * Allow a template to include other template and inherit all the variable
	 */
	function load($file)
	{
		if($this->vars)
			extract($this->vars, EXTR_REFS);

		$file = $this->_getTemplateFullpath($file);
		include($file);
		return $this;
	}


	/**
	 * Open, parse, and return the template file.
	 *
	 * @param $file string the template file name
	 */
	function fetch($file = null)
	{

		if( JRequest::getVar('format') == 'iphone' )
		{
			$file	.= '.iphone';
		}

		$file = $this->_getTemplateFullpath( $file );

		if(!$file) $file = $this->file;

		if((JRequest::getVar('format') == 'iphone') && (!JFile::exists($file)))
		{
			//if we detected the format was iphone and the template file was not there, return empty content.
			return '';
		}

		// @rule: always add seminarman config object in the template scope so we don't really need
		// to always set it.
		if( !isset( $this->vars['config'] ) && empty($this->vars['config']) )
		{
			$this->vars['config']	= CMFactory::getConfig();
		}

		if($this->vars)
			extract($this->vars, EXTR_REFS);          // Extract the vars to local namespace

		ob_start();                    // Start output buffering
		require($file);                // Include the file
		$contents = ob_get_contents(); // Get the contents of the buffer
		ob_end_clean();                // End buffering and discard
		return $contents;              // Return the contents
	}

	function object_to_array($obj) {
		$_arr = is_object($obj) ? get_object_vars($obj) : $obj;
		$arr = array();
		foreach ($_arr as $key => $val) {
			$val = (is_array($val) || is_object($val)) ? $this->object_to_array($val) : $val;
			$arr[$key] = $val;
		}
		return $arr;
	}
}

//
// class CCachedTemplate extends CMTemplate {
//     var $cache_id;
//     var $expire;
//     var $cached;
//     var $file;
//
//     /**
//      * Constructor.
//      *
//      * @param $cache_id string unique cache identifier
//      * @param $expire int number of seconds the cache will live
//      */
//     function CCachedTemplate($cache_id = "", $cache_timeout = 10000) {
//         $this->CMTemplate();
//         $this->cache_id = AZ_CACHE_PATH . "/cache__". md5($cache_id);
//         $this->cached = false;
//         $this->expire = $cache_timeout;
//     }
//
//     /**
//      * Test to see whether the currently loaded cache_id has a valid
//      * corrosponding cache file.
//      */
//     function is_cached() {
//     	//return false;
//         if($this->cached) return true;
//
//         // Passed a cache_id?
//         if(!$this->cache_id) return false;
//
//         // Cache file exists?
//         if(!file_exists($this->cache_id)) return false;
//
//         // Can get the time of the file?
//         if(!($mtime = filemtime($this->cache_id))) return false;
//
//         // Cache expired?
//         // Implemented as 'never-expires' cache, so, the data need to change
//         // for the cache to be modified
//         if(($mtime + $this->expire) < time()) {
//             @unlink($this->cache_id);
//             return false;
//         }
//
//         else {
//             /**
//              * Cache the results of this is_cached() call.  Why?  So
//              * we don't have to double the overhead for each template.
//              * If we didn't cache, it would be hitting the file system
//              * twice as much (file_exists() & filemtime() [twice each]).
//              */
//             $this->cached = true;
//             return true;
//         }
//     }
//
//     /**
//      * This function returns a cached copy of a template (if it exists),
//      * otherwise, it parses it as normal and caches the content.
//      *
//      * @param $file string the template file
//      */
//     function fetch_cache($file, $processFunc = null) {
//     	// Get the configuration object.
// 		$config	= CMFactory::getConfig();
//
//     	$contents	= "";
// 		$file = SEMINARMAN_COM_PATH .DS. 'templates'.DS.$config->get('template').DS.$file . '.php';
//
//         if($this->is_cached()) {
//             $fp = @fopen($this->cache_id, 'r');
//             if($fp){
//             	$filesize = filesize($this->cache_id);
//             	if($filesize > 0){
//             		$contents = fread($fp, $filesize);
//             	}
//             	fclose($fp);
//             } else {
//             	$contents = $this->fetch($file);
// 			}
//         }
//         else {
//             $contents = $this->fetch($file);
//
//             // Check if caller wants to process contents with another function
// 			if($processFunc)
//                 $contents = $processFunc($contents);
//
// 			if(!empty($contents)){
//
// 	            // Write the cache, only if there is some data
// 	            if($fp = @fopen($this->cache_id, 'w')) {
// 	                fwrite($fp, $contents);
// 	                fclose($fp);
// 	            }
// 	            else {
// 	                //die('Unable to write cache.');
// 	            }
//             }
//
//
//         }
//
//          return $contents;
//     }
// }


class SMANFunctions
{
	static function buildCourseQuery($model, $db, $where)
	{
        $orderby = self::buildCourseOrderBy($model);

		$query = $db->getQuery(true);
				$query->select('i.*, (i.plus / (i.plus + i.minus) ) * 100 AS votes,' .
				' CONCAT_WS(" ", emp.firstname, emp.lastname) as tutor,' .        
				' CASE WHEN CHAR_LENGTH(i.alias) THEN CONCAT_WS(\':\', i.id, i.alias) ELSE i.id END as slug,' .
				' gr.title AS cgroup, lev.title AS level')
				->from('#__seminarman_courses AS i')
            	->leftJoin('#__seminarman_favourites AS f ON f.courseid = i.id')
				->leftJoin('#__seminarman_tags_course_relations AS t ON t.courseid = i.id')
				->leftJoin('#__seminarman_cats_course_relations AS rel ON rel.courseid = i.id')
				->leftJoin('#__seminarman_tutor AS emp ON emp.id = i.tutor_id')
				->leftJoin('#__seminarman_atgroup AS gr ON gr.id = i.id_group')
				->leftJoin('#__seminarman_experience_level AS lev ON lev.id = i.id_experience_level')
				->leftJoin('#__seminarman_categories AS c ON c.id = rel.catid')
				->where($where)
				->group('i.id')
				->order($orderby);
				
        return $query;
    }

    static function buildCourseOrderBy($model)
    {
        $filter_order = $model->getState('filter_order');
        $filter_order_dir = $model->getState('filter_order_dir');
        
		if($filter_order) 
		{
			if ($filter_order == "i.start_date") {
			    return $filter_order .' '. $filter_order_dir .', i.start_time, i.title';	
			} elseif ($filter_order == "i.finish_date") {
				return $filter_order .' '. $filter_order_dir .', i.finish_time, i.title';
			} else {
			    return $filter_order .' '. $filter_order_dir .', i.title';
			}
		} else {
			return 'i.title';
		}
    }

    static function buildCourseWhere($db, $state = 1, $filter = 1)
    {
        $mainframe = JFactory::getApplication();

        $user = JFactory::getUser();
        $gid = (int)$user->get('aid');

        $jnow = JFactory::getDate();
        
        // $now = $jnow->toMySQL();
        $now = $jnow->toSQL();
        $nullDate = $db->getNullDate();

        // $state = 1;

        $params = $mainframe->getParams('com_seminarman');

        switch ($state)
        {
            case - 1:   // apparently it will never happen

                $year = JRequest::getInt('year', date('Y'));
                $month = JRequest::getInt('month', date('m'));

                $where = 'i.state = -1';
                $where .= ' AND YEAR( i.created ) = ' . (int)$year;
                $where .= ' AND MONTH( i.created ) = ' . (int)$month;
                break;

            default:  // possible for published, unpublished, archived, trashed...
            	switch($params->get('publish_down')) {
            		case '1':
            			$publish_down = 'CONCAT_WS(\' \', i.start_date, i.start_time)';
            			break;
            		case '2':
            			$publish_down = 'CONCAT_WS(\' \', i.finish_date, i.finish_time)';
            			break;
            		default:
            			$publish_down = 'i.publish_down';
            	}
            	
				if ($state == 2) {
				// ignore publish_down settings by archived courses
					$publish_down_query = '';
				} else {
					$publish_down_query = ' AND ( ' . $publish_down . ' = ' . $db->Quote($nullDate) . ' OR ' . $publish_down . ' >= ' . $db->Quote($now) . ' )';
				}
               
                $where = 'i.state = ' . (int)$state . ' AND ( i.publish_up = ' . $db->Quote($nullDate) .
                    ' OR i.publish_up <= ' . $db->Quote($now) . ' )' . $publish_down_query;

                break;
        }


        if ($filter && $params->get('filter'))
        {

            $filter = JRequest::getString('filter', '', 'request');
            $filter_experience_level = JRequest::getString('filter_experience_level', '', 'request');
            $filter_positiontype = JRequest::getString('filter_positiontype', '', 'request');

            if ($filter)
            {

                $filter = $db->escape(trim(JString::strtolower($filter)));
                $like = $db->Quote('%'. $db->escape($filter, true) .'%', false);

                $where .= ' AND ( LOWER( i.title ) LIKE ' . $like .' OR LOWER( i.code ) LIKE '. $like .')';
            }
        } else {
            $filter_experience_level = null;
        }

        if ($filter_experience_level>0)
        {
            $where .= ' AND LOWER( i.id_experience_level ) = ' . $filter_experience_level;
        }

        return $where;
    }

    static function setCourse($course, $category = null, $itemid, $date_format, $time_format)
    {
		require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS . 'helpers' . DS . 'seminarman.php');
		
		$db = JFactory::getDBO();
    	$link = JRoute::_($course->alt_url);
    	$lang = JFactory::getLanguage();
    	$app = JFactory::getApplication();
    	$params = $app->getParams('com_seminarman');
		$course->tags = self::getCourseTags($course->id);

		$jversion = new JVersion();
		 
		if (version_compare($jversion->getShortVersion(), "3.0", 'ge')) {
			$course_params = new JRegistry($course->attribs);
		} else {
			$course_params = new JParameter($course->attribs);
		}
		
		// fix for 24:00:00 (illegal time colock)
		if ($course->start_time == '24:00:00') $course->start_time = '23:59:59';
		if ($course->finish_time == '24:00:00') $course->finish_time = '23:59:59';
    
    	$course->start_date_obj = JFactory::getDate($course->start_date . ' ' . $course->start_time);
    	$course->finish_date_obj = JFactory::getDate($course->finish_date . ' ' . $course->finish_time);
    	$course->start_date_raw = $course->start_date;
    	$course->finish_date_raw = $course->finish_date;
    	$course->start_time_raw = $course->start_time;
    	$course->finish_time_raw = $course->finish_time;
    	
    	self::setCoursePrice($course);
    	self::setCourseStatus($course, $category, $itemid);
    
    	$datetime_format = '\<\s\p\a\n\>' . $date_format . ', \<\/\s\p\a\n\><\s\p\a\n\>' . $time_format . '\<\/\s\p\a\n\>';
    	$d_format = '\<\s\p\a\n\>' . $date_format . '\<\/\s\p\a\n\>';
    	$t_format = '\<\s\p\a\n\>' . $time_format . '\<\/\s\p\a\n\>';
    
    	if (!empty($course->modified) && $course->modified != '00:00:00') {
    		$course->modified = self::formatDate($course->modified, $d_format);
    	} else {
    		$course->modified = JText::_('COM_SEMINARMAN_NEVER');
    	}

    	$show_course_booking = $course_params->get('show_booking_form', $params->get('show_booking_form'));
    	$show_booking_deadline = $course_params->get('show_booking_deadline', $params->get('show_booking_deadline'));
    	$bookable_until = preg_replace(array('/[^-\d]/'), '', $course_params->get('booking_deadline', $params->get('booking_deadline')));
    	
    	// we need to know if this course is an all-day event
    	$course_attribs = new JRegistry();
    	$course_attribs->loadString($course->attribs);
    	if (!is_null($course->start_time)) {
    		$start_date_all = $course_attribs->get('start_date_all', 0);
    	} else {
    		// backward compatibility!
    		// in the old version start_time could be saved as NULL value
    		// it was considered as an all-day event
    		$start_date_all = 1;
    	}
    	if (!is_null($course->finish_time)) {
    		$finish_date_all = $course_attribs->get('finish_date_all', 0);
    	} else {
    		// backward compatibility!
    		// in the old version start_time could be saved as NULL value
    		// it was considered as an all-day event
    		$finish_date_all = 1;
    	}
    	$start_date_usertz = ($start_date_all == 1) ? false : true;
    	$finish_date_usertz = ($finish_date_all == 1) ? false : true;

    	// we have decided that the user profile timezone should be used anywhere in frontend, incl. the view for a whole day event
    	$start_date_usertz = true;
    	$finish_date_usertz = true;
    	
    	switch ($params->get('show_datetime_in_table')) {
    		case 1:  //date and time
    			if ($start_date_all) {
    				$start_datetime_format = $d_format;
    			} else {
    				$start_datetime_format = $datetime_format;
    			}
    			if (empty($course->start_time)) {
    				$deadline_time = '00:00:00';
    			} else {
    				$deadline_time = $course->start_time;
    			}
    			$deadline_datetime_format = $datetime_format;
    			$course->start = self::formatDate($course->start_date . ' ' . $course->start_time, $start_datetime_format, 0, $start_date_usertz);
    			$course->deadline = $show_booking_deadline && (!is_null($bookable_until) && $bookable_until !== "") ? self::formatDate($course->start_date . ' ' . $deadline_time, $deadline_datetime_format, $bookable_until, $start_date_usertz) : '';
    
    			if ($finish_date_all) {
    				$finish_datetime_format = $d_format;
    			} else {
    				$finish_datetime_format = $datetime_format;
    			}
    			$course->finish = self::formatDate($course->finish_date . ' ' . $course->finish_time, $finish_datetime_format, 0, $finish_date_usertz, true);
    			break;
    		case 2: // only time
    			if ($start_date_all) {
    				$course->start = $course->deadline = JText::_('COM_SEMINARMAN_NOT_SPECIFIED');
    			} else {
    				$course->start = self::formatDate($course->start_date . ' ' . $course->start_time, $t_format, 0, $start_date_usertz);
    				$course->deadline = $show_booking_deadline && (!is_null($bookable_until) && $bookable_until !== "") ? self::formatDate($course->start_date . ' ' . $course->start_time, $t_format, $bookable_until, $start_date_usertz) : '';
    			}
    			if ($finish_date_all) {
    				$course->finish = JText::_('COM_SEMINARMAN_NOT_SPECIFIED2');
    			} else {
    				$course->finish = self::formatDate($course->finish_date . ' ' . $course->finish_time, $t_format, 0, $finish_date_usertz, true);
    			}
    			break;
    		case 3: // weekday and date
    			$d_format = '\<\s\p\a\n\>' . JText::_('COM_SEMINARMAN_WEEKDAY_FORMAT2') . ', ' . $date_format . '\<\/\s\p\a\n\>';
    			$course->start = self::formatDate($course->start_date . ' ' . $course->start_time, $d_format, 0, $start_date_usertz);
    			$course->finish = self::formatDate($course->finish_date . ' ' . $course->finish_time, $d_format, 0, $finish_date_usertz, true);
    			$course->deadline = $show_booking_deadline && (!is_null($bookable_until) && $bookable_until !== "") ? self::formatDate($course->start_date . ' ' . $course->start_time, $d_format, $bookable_until, $start_date_usertz) : '';
    			break;
    		case 4: // weekday, date and time
    			$d_format = '\<\s\p\a\n\>' . JText::_('COM_SEMINARMAN_WEEKDAY_FORMAT2') . ', ' . $date_format . '\<\/\s\p\a\n\>';
    			$datetime_format = '\<\s\p\a\n\>' . JText::_('COM_SEMINARMAN_WEEKDAY_FORMAT2') . ', ' . $date_format . ', \<\/\s\p\a\n\><\s\p\a\n\>' . $time_format . '\<\/\s\p\a\n\>';
    			if ($start_date_all) {
    				$start_datetime_format = $d_format;
    			} else {
    				$start_datetime_format = $datetime_format;
    			}
    			if (empty($course->start_time)) {
    				$deadline_time = '00:00:00';
    			} else {
    				$deadline_time = $course->start_time;
    			}
    			$deadline_datetime_format = $datetime_format;
    			$course->start = self::formatDate($course->start_date . ' ' . $course->start_time, $start_datetime_format, 0, $start_date_usertz);
    			$course->deadline = $show_booking_deadline && (!is_null($bookable_until) && $bookable_until !== "") ? self::formatDate($course->start_date . ' ' . $deadline_time, $deadline_datetime_format, $bookable_until, $start_date_usertz) : '';
    			 
    			if ($finish_date_all) {
    				$finish_datetime_format = $d_format;
    			} else {
    				$finish_datetime_format = $datetime_format;
    			}
    			$course->finish = self::formatDate($course->finish_date . ' ' . $course->finish_time, $finish_datetime_format, 0, $finish_date_usertz, true);
    			break;
    		case 0: // only date
    		default:
    			$course->start = self::formatDate($course->start_date . ' ' . $course->start_time, $d_format, 0, $start_date_usertz);
    			$course->finish = self::formatDate($course->finish_date . ' ' . $course->finish_time, $d_format, 0, $finish_date_usertz, true);
    			$course->deadline = $show_booking_deadline && (!is_null($bookable_until) && $bookable_until !== "") ? self::formatDate($course->start_date . ' ' . $course->start_time, $d_format, $bookable_until, $start_date_usertz) : '';
    			break;
    	}
    	
    	if ($start_date_all) {
    		$course->start_time_local = '';
    	} else {
    	    $course->start_time_local = self::formatDate($course->start_date . ' ' . $course->start_time, $t_format, 0, $start_date_usertz);
    	}
    	
    	if ($finish_date_all) {
    		$course->finish_time_local = '';
    	} else {
    	    $course->finish_time_local = self::formatDate($course->finish_date . ' ' . $course->finish_time, $t_format, 0, $finish_date_usertz, true);
    	}
    	
    	// custom fields
    	$course->display_custom_1 = ($params->get("custom_fld_1_in_detail"))?true:false;
    	$course->display_custom_2 = ($params->get("custom_fld_2_in_detail"))?true:false;
    	$course->display_custom_3 = ($params->get("custom_fld_3_in_detail"))?true:false;
    	$course->display_custom_4 = ($params->get("custom_fld_4_in_detail"))?true:false;
    	$course->display_custom_5 = ($params->get("custom_fld_5_in_detail"))?true:false;
    	
    	// compatible for PHP 5.3/5.4
    	$custom_fld_1_title = $params->get('custom_fld_1_title');
    	$custom_fld_2_title = $params->get('custom_fld_2_title');
    	$custom_fld_3_title = $params->get('custom_fld_3_title');
    	$custom_fld_4_title = $params->get('custom_fld_4_title');
    	$custom_fld_5_title = $params->get('custom_fld_5_title');
    	$custom_fld_1_value = $course_attribs->get('custom_fld_1_value');
    	$custom_fld_2_value = $course_attribs->get('custom_fld_2_value');
    	$custom_fld_3_value = $course_attribs->get('custom_fld_3_value');
    	$custom_fld_4_value = $course_attribs->get('custom_fld_4_value');
    	$custom_fld_5_value = $course_attribs->get('custom_fld_5_value');
    	
    	$course->custom1_lbl = (!empty($custom_fld_1_title))?JText::_($custom_fld_1_title):JText::_('COM_SEMINARMAN_CUSTOM_FLD_1');
    	$course->custom2_lbl = (!empty($custom_fld_2_title))?JText::_($custom_fld_2_title):JText::_('COM_SEMINARMAN_CUSTOM_FLD_2');
    	$course->custom3_lbl = (!empty($custom_fld_3_title))?JText::_($custom_fld_3_title):JText::_('COM_SEMINARMAN_CUSTOM_FLD_3');
    	$course->custom4_lbl = (!empty($custom_fld_4_title))?JText::_($custom_fld_4_title):JText::_('COM_SEMINARMAN_CUSTOM_FLD_4');
    	$course->custom5_lbl = (!empty($custom_fld_5_title))?JText::_($custom_fld_5_title):JText::_('COM_SEMINARMAN_CUSTOM_FLD_5');
    	
    	$course->custom1_val = (!empty($custom_fld_1_value))?JText::_($custom_fld_1_value):'';
    	$course->custom2_val = (!empty($custom_fld_2_value))?JText::_($custom_fld_2_value):'';
    	$course->custom3_val = (!empty($custom_fld_3_value))?JText::_($custom_fld_3_value):'';
    	$course->custom4_val = (!empty($custom_fld_4_value))?JText::_($custom_fld_4_value):'';
    	$course->custom5_val = (!empty($custom_fld_5_value))?JText::_($custom_fld_5_value):'';
    	
        if (($course->display_custom_1 && !empty($course->custom1_val)) || ($course->display_custom_2 && !empty($course->custom2_val)) || ($course->display_custom_3 && !empty($course->custom3_val)) 
            || ($course->display_custom_4 && !empty($course->custom4_val)) || ($course->display_custom_5 && !empty($course->custom5_val))) {	
            $course->custom_available = true;
        } else {
            $course->custom_available = false;
        }
    	
    	// *** LEGACY ***
    
    	if ($course->start_date != '0000-00-00'){
    		$course->start_date = JFactory::getDate($course->start_date)->format($d_format);
    	} else {
    		$course->start_date = JText::_('COM_SEMINARMAN_NOT_SPECIFIED');
    	}
    		
    	if ($course->finish_date != '0000-00-00'){
    		$course->finish_date = JFactory::getDate($course->finish_date)->format($d_format);
    	} else {
    		$course->finish_date = JText::_('COM_SEMINARMAN_NOT_SPECIFIED');
    	}
    		
    	if (!empty($course->start_time) && $course->start_time != '00:00:00') {
    		$course->start_time = date($t_format, strtotime($course->start_time));
    	} else {
    		$course->start_time = '';
    	}
    		
    	if (!empty($course->finish_time) && $course->finish_time != '00:00:00') {
    		$course->finish_time = date($t_format, strtotime($course->finish_time));
    	} else {
    		$course->finish_time = '';
    	}
    		
    	// ******
    
    	$menuclass = 'category' . $params->get('pageclass_sfx');
    
    	if ((($course->alt_url) <> 'http://') && (($course->alt_url) <> 'https://') && (trim($course->alt_url) <> '')) {
    		switch ($course_params->get('target', $course_params->get('target'))){
    			case 1:
    				$course->link = '<a href="' . $link . '" target="_blank" class="' . $menuclass . '">' . JText::_('COM_SEMINARMAN_MORE_DETAILS'). '</a>';
    				break;
    
    			case 2:
    				$course->link = "<a href=\"#\" onclick=\"javascript: window.open('" . $link . "', '', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=780,height=550'); return false\" class=\"$menuclass\">" . JText::_('COM_SEMINARMAN_MORE_DETAILS') . "</a>\n";
    				break;
    
    			default:
    				$course->link = '<a href="' . $link . '" class="' . $menuclass . '">' . JText::_('COM_SEMINARMAN_MORE_DETAILS'). '</a>';
    				break;
    		}
    	} else
    		$course->link = null;
    
    	switch ($course->new) {
    		case 1:
    			$new_icon = 'components/com_seminarman/assets/images/' . $lang->getTag() . '_new_item.png';
    			$course->show_new_icon = '&nbsp;&nbsp;' . JHTML::_('image', (JFile::exists($new_icon) ? $new_icon : 'components/com_seminarman/assets/images/new_item.png'), JText::_('COM_SEMINARMAN_NEW'));
    			break;
    		default:
    			$course->show_new_icon = '';
    			break;
    	}
    
    	switch ($course_params->get('show_sale', $course_params->get('show_sale'))){
    		case 1:
    			$sale_icon = 'components/com_seminarman/assets/images/' . $lang->getTag() . '_sale_item.png';
    			$course->show_sale_icon = '&nbsp;&nbsp;' . JHTML::_('image', (JFile::exists($sale_icon) ? $sale_icon : 'components/com_seminarman/assets/images/sale_item.png'), JText::_('COM_SEMINARMAN_SALE'));
    			break;
    		default:
    			$course->show_sale_icon = '';
    			break;
    	}
    
    	$query = $db->getQuery(true);
    	$query->select( '*' );
    	$query->from( '#__seminarman_sessions' );
    	$query->where( 'published = 1' );
    	$query->where( 'courseid = ' . $course->id );
    	$query->order( 'session_date, start_time, ordering' );
    	$db->setQuery( $query );
    	
    	$course_sessions = $db->loadObjectList();
    	$course->count_sessions = count($course_sessions);
    	$course->course_sessions = $course_sessions;
    
    	foreach ($course_sessions as $course_session)
    		if ($course_session->session_date != '0000-00-00') {
    		    // compute session date time for different local timezone, always utc date+time as input!
    	        $session_start_str = $course_session->session_date . ' ' . $course_session->start_time;
    	        $session_finish_str =$course_session->session_date . ' ' . $course_session->finish_time;
    	        
    	        $course_session->session_date = JHtml::_('date', $session_start_str, $date_format);
    	        $course_session->start_time = JHtml::_('date', $session_start_str, $time_format);
    	        $course_session->finish_time = JHtml::_('date', $session_finish_str, $time_format);

    		    // $course_session->session_date = JFactory::getDate($course_session->session_date)->format("j. F Y");
    	    } else {
    		    $course_session->session_date = JText::_('COM_SEMINARMAN_NOT_SPECIFIED');
    	    }
    }
    
    static function formatDate($datetime, $format = null, $offset = 0, $usertz = true, $alt_not_specified = false)
    {
    	if ($usertz) {
    	    $site_timezone = JFactory::getUser()->getParam('timezone', JFactory::getConfig()->get('offset'));
    	} else {
    		$site_timezone = JFactory::getConfig()->get('offset');
    	}
    	 
    	if (substr($datetime, 0, 10) != '0000-00-00') {
    		$datetime_obj = new JDate($datetime . ' ' . -1 * (int) $offset . ' day', 'UTC');  // input explicit as utc		
    		$datetime_obj->setTimezone(new DateTimeZone($site_timezone));  // Joomla Local Time
    		if (isset($format)) {
    			$datetime_out = $datetime_obj->format($format, true, true);  // if format exists, output is a string
    		} else {
    			$datetime_out = $datetime_obj; // if format doesn't exist, output is an object, used in setCourseStatus by comparing booking deadline
    		}
    	} else {
    		$datetime_out = ($alt_not_specified) ? JText::_('COM_SEMINARMAN_NOT_SPECIFIED2') : JText::_('COM_SEMINARMAN_NOT_SPECIFIED');
    	}
    	
    	return $datetime_out;
    }
    
    static function setCourseStatus($course, $category = null, $itemid = null) {
    	JLoader::import('category', JPATH_SITE . DS . 'components' . DS . 'com_seminarman' . DS . 'models');
    	require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS . 'helpers' . DS . 'seminarman.php');

    	$app = JFactory::getApplication();
    	$params = $app->getParams('com_seminarman');
    	
    	$jversion = new JVersion();
    	
    	if (version_compare($jversion->getShortVersion(), "3.0", 'ge')) {
    		$course_params = new JRegistry($course->attribs);
    	} else {
    		$course_params = new JParameter($course->attribs);
    	}
    	
    	$db = JFactory::getDBO();
    	$model = JModelLegacy::getInstance('category', 'SeminarmanModel');
    	$show_course_booking = $course_params->get('show_booking_form', $params->get('show_booking_form'));
    	$bookable_until = preg_replace(array('/[^-\d]/'), '', $course_params->get('booking_deadline', $params->get('booking_deadline')));
    	
    	// fix for 24:00:00 (illegal time colock)
    	if ($course->start_time == '24:00:00') $course->start_time = '23:59:59';
    	if ($course->finish_time == '24:00:00') $course->finish_time = '23:59:59';

    	$date = self::formatDate($course->start_date . ' ' . $course->start_time, null, $bookable_until);

    	if ($course_params->get('current_capacity', $params->get('current_capacity')) > 0) {
    		switch ($course_params->get('current_capacity', $params->get('current_capacity'))) {
    			case 1:  // show all
    				$current_capacity_setting = - 1;
    				break;
    			case 2:  // show pending, paid and awaiting response
    				$current_capacity_setting = 0;
    				break;
    			default:
    				$current_capacity_setting = - 1;
    				break;
    		}
    		// add currentbookings information
    		$query = $db->getQuery(true);
    		$query->select( 'SUM(b.attendees)' );
    		$query->from( '#__seminarman_application AS b' );
    		$query->where( 'b.published = 1' );
    		$query->where( 'b.course_id = ' . $course->id );
    		$query->where( '(( b.status > ' . $current_capacity_setting . ' AND b.status < 3 ) OR ( b.status = 5 ))' );
    		$db->setQuery( $query );
    		$allreadybooked = $db->loadResult();
    		
    		$course->maxCapacity = $course->capacity;
    		$course->currentAvailability = ($course->capacity) - $allreadybooked;
    		$course->booked_places = $allreadybooked;
    	
    		if (($course->capacity - $allreadybooked) > 0)
    			$booking_ok = True;
    		else
    			$booking_ok = False;
    		
    		switch ($course_params->get('show_capacity', $params->get('show_capacity'))) {
    			case 0: // hide
    			    $course->capacity = "<span style='display: none;' id='cca'>" . $course->currentAvailability . "</span>" . $course->maxCapacity;
    			    $course->capacity_cal = $course->capacity;
    				break;
    			case 1: // max only
    			    $course->capacity = "<span style='display: none;' id='cca'>" . $course->currentAvailability . "</span>" . $course->maxCapacity;
    			    $course->capacity_cal = $course->capacity;
    				break;
    			case 2: // current only
    				// capacity display for course detail view (id 'cca' is important, it is used for checking if the available places exceed)
    				$course->capacity = "<span id='cca'>" . $course->currentAvailability . "</span>";
    				// capacity display for calendar tooltip
    				$course->capacity_cal = "<span>" . $course->currentAvailability . "</span>";
    				break;
    			case 3: // current of max
    				// capacity display for course detail view (id 'cca' is important, it is used for checking if the available places exceed)
    				$course->capacity = "<span id='cca'>" . $course->currentAvailability . "</span>" . " " . JText::_('COM_SEMINARMAN_OF') . " " . $course->maxCapacity;
    				// capacity display for calendar tooltip
    				$course->capacity_cal = "<span>" . $course->currentAvailability . "</span>" . " " . JText::_('COM_SEMINARMAN_OF') . " " . $course->maxCapacity;
    				break;
    			default:
    				break;    				
    		}
    		
    		switch ($params->get('show_spaces_in_table')) {
    			case 0: // hide
    				$course->capacity_tbl = '';
    				break;
    			case 1: // max only
    				$course->capacity_tbl = $course->maxCapacity;
    				break;
    			case 2: // current only
    				$course->capacity_tbl = $course->currentAvailability;
    				break;
    			case 3: // current of max
    				$course->capacity_tbl = $course->currentAvailability . " " . JText::_('COM_SEMINARMAN_OF') . " " . $course->maxCapacity;
    				break;
    			default:
    				$course->capacity_tbl = '';
    				break;
    		}
    		
    	} else {
    		$course->capacity_tbl = $course->capacity;
    		$course->capacity_cal = $course->capacity;
    		$booking_ok = True;
    	}
    	$user = JFactory::getUser();
    	$course->bookable = False;
    	
    	if ($params->get('enable_bookings') != 0 && $show_course_booking != 0 && $course->state == 1) {
    		// booking rule has affects only if the course booking is allowed for registrated user only. otherwise the booking rule will be simply ignored.
	    	if($params->get('enable_bookings') == 2 || $params->get('enable_bookings') == 3 || $params->get('user_booking_rules') == 0 || ($params->get('enable_bookings') == 1 && $params->get('user_booking_rules') == 1 && $user->id > 0 && JHTMLSeminarman::check_booking_permission($course->id, $user->id))) {
    			if ($date == JText::_('COM_SEMINARMAN_NOT_SPECIFIED') || is_null($bookable_until) || $bookable_until === '' || (JFactory::getDate()->toUnix() <= $date->toUnix())) {
	    			if ($booking_ok) {
	    				if (!$params->get('enable_multiple_bookings_per_user') && $user->id && $model->hasUserBooked($course->id)) {
	    					$course->book_link = '<span class="centered italic">' . JText::_('COM_SEMINARMAN_ALREADY_BOOKED_SHORT') . '</span>';
	    					$course->status = JText::_('COM_SEMINARMAN_ALREADY_BOOKED_SHORT');
	    				} elseif ($course->canceled == 1) {
	    					$course->book_link = '<span class="centered italic">' . JText::_('COM_SEMINARMAN_COURSE_CANCELED') . '</span>';
	    					$course->status = JText::_('COM_SEMINARMAN_COURSE_CANCELED');
	    				} else {
	    					$course->bookable = True;
	    					$course->book_link = '<div class="button2-left"><div class="blank"><a href="' . JRoute::_('index.php?option=com_seminarman&view=courses&' . (isset($category) ? 'cid=' . $category->slug : 'mod=1') . '&id=' . $course->slug . '&Itemid=' . $itemid . '&buchung=1#appform') . '">' . JText::_('COM_SEMINARMAN_BOOK_NOW') . '</a></div></div>';
	    					$course->status = JText::_('COM_SEMINARMAN_COURSE_BOOKABLE');
	    				}
	    			} else {
	    				if ($course->canceled == 1) {
	    					$course->book_link = '<span class="centered italic">' . JText::_('COM_SEMINARMAN_COURSE_CANCELED') . '</span>';
	    					$course->status = JText::_('COM_SEMINARMAN_COURSE_CANCELED');
	    				}
	    				else {
		    				if ( $params->get( 'waitinglist_active' ) == 1 ) {
		    					$course->book_link = '<span class="centered italic">' . JText::_('COM_SEMINARMAN_FULL') . '</span><br><div class="button2-left"><div class="blank"><a href="' . JRoute::_('index.php?option=com_seminarman&view=courses&' . (isset($category) ? 'cid=' . $category->slug : 'mod=1') . '&id=' . $course->slug . '&Itemid=' . $itemid . '&buchung=1#appform') . '">' . JText::_('COM_SEMINARMAN_WAITINGLIST') . '</a></div></div>';
	    						$course->status = JText::_('COM_SEMINARMAN_WAITINGLIST');
	    					}
	    					else {
		    					$course->book_link = '<span class="centered italic">' . JText::_('COM_SEMINARMAN_FULL') . '</span>';
		    					$course->status = JText::_('COM_SEMINARMAN_FULL');
	    					}
	    				}
	    			}
	    		} else {
		    		$course->book_link = '<span class="centered italic">'.JText::_('COM_SEMINARMAN_BOOKING_DEADLINE_EXCEEDED').'</span>';
		    		$course->status = JText::_('COM_SEMINARMAN_BOOKING_DEADLINE_EXCEEDED');
	    		}
    		} else {
    			$course->book_link = '<span class="sman_info_btn">'.JText::_('COM_SEMINARMAN_BOOKING_NOT_ALLOWED').'</span>';
    			$course->status = JText::_('COM_SEMINARMAN_BOOKING_NOT_ALLOWED');
	    	}
    	} else {
    		$course->book_link = '';
    		$course->status = JText::_('COM_SEMINARMAN_BOOKING_DEADLINE_NONE');
    	}
    }

    static function setCoursePrice($course)
    {
		$app = JFactory::getApplication();
		$params = $app->getParams('com_seminarman');
		
    	$course->currency_price = $params->get('currency');
    	$course->price_orig = $course->price;
    	$display_free_charge = $params->get('display_free_charge');
    	 
    	$jversion = new JVersion();
    
    	if (version_compare($jversion->getShortVersion(), "3.0", 'ge')) {
    		$course_params = new JRegistry($course->attribs);
    	} else {
    		$course_params = new JParameter($course->attribs);
    	}
    	
    	// custom menu setting for displayed price: $params->get('show_gross_price')
    	// global setting for displayed price: $params->get('show_gross_price_global')    	
    	if ($params->get('show_gross_price_global') == 0) {
    		if (!is_null($params->get('show_gross_price')) && $params->get('show_gross_price') == '1') {
    			$price_display_gross = true;
    		} else {
    			$price_display_gross = false;
    		}
    	} else {
    	    if (!is_null($params->get('show_gross_price')) && $params->get('show_gross_price') <> '1') {
    			$price_display_gross = false;
    		} else {
    			$price_display_gross = true;
    		}
    	}
    	
    	// calculate displayed price
    	if ($price_display_gross) {
    		$course->price += ($course->price / 100) * $course->vat;
    	}
    
    	if (($params->get('second_currency') != 'NONE') && ($params->get('second_currency') != $params->get('currency'))){
    	    if (doubleval(str_replace(",", ".", $params->get('factor'))) > 0) {
    			$show_2_price = true;
    			$sec_currency = $params->get('second_currency');
    			$factor = doubleval(str_replace(",", ".", $params->get('factor')));
    		} else {
    			$show_2_price = false;
    		}
    	} else {
    		$show_2_price = false;
    	}

    	$lang = JFactory::getLanguage();
    	$old_locale = setlocale(LC_NUMERIC, NULL);
    	setlocale(LC_NUMERIC, $lang->getLocale());
    	$course_price = JText::sprintf('%.2f', round($course->price, 2));
    	if ($show_2_price)
    		$course_2_price = JText::sprintf('%.2f', round(doubleval($factor * (doubleval(str_replace(",", ".", $course->price)))), 2));
    	setlocale(LC_NUMERIC, $old_locale);
    
    	if ($course_params->get('show_price') !== 0) {
    		if (!empty($display_free_charge) && ($course->price == 0)) {
    			// price display for course table
    			$course->price = JText::_($display_free_charge);
    			// price display for my bookings
    			$course->price_simple = JText::_($display_free_charge);
    			// price display for course detail
    			$course->price_detail = JText::_($display_free_charge);
    		} else {
    			if ($show_2_price) {
    				// price display for course table
    				$course->price = $course_price . ' ' . $course->currency_price . ' | ' . $course_2_price . ' ' . $sec_currency . ' ' .$course->price_type;
    			} else {
    				// price display for course table
    				$course->price = $course_price . ' ' . $course->currency_price . ' ' . $course->price_type;
    			}
    			// price display for my bookings (no need for info about the 2nd price and extra fees)
    			$course->price_simple = $course_price . ' ' . $course->currency_price . ' ' . $course->price_type;
    			// price display for course detail (with hint about vat incl. or not if available)
    			$course->price_detail = $course->price;
    			if ($course->vat <> 0) {
    				if ($price_display_gross) {
    				  $course->price_detail .= ' ('.JText::_('COM_SEMINARMAN_WITH_VAT').')';
    			    } else {
    				  $course->price_detail .= ' ('.JText::_('COM_SEMINARMAN_WITHOUT_VAT').')';
    			    }
    			} else {
    				// nothing should be attached
    			}
    		}
    		
    		// only for price display in course table
    		$dispatcher=JDispatcher::getInstance();
    		JPluginHelper::importPlugin('seminarman');
    		$html_tmpl=$dispatcher->trigger('onGetAddPriceInfoForTableSeperate',array($course));  // we need the course id
    		
    		if(isset($html_tmpl) && !empty($html_tmpl)) {
    			$course->price .= $html_tmpl[0];
    		}
    		
    		// in the course detail the display of the extra fee has another layout and is directly written in the layout file
    		// so nothing about extra fee for $course->price_detail here
    		
    	} else {
    		$course->price = '';
    		$course->price_simple = '';
    		$course->price_detail = '';
    	}
    }

    static function getCourseTags($id)
    {
		$db = JFactory::getDBO();
		
    	$query = $db->getQuery(true);
    	$query->select( 'DISTINCT t.name, CASE WHEN CHAR_LENGTH(t.alias) THEN CONCAT_WS(\':\', t.id, t.alias) ELSE t.id END as slug' );
    	$query->from( '#__seminarman_tags AS t' );
    	$query->join( "LEFT", "#__seminarman_tags_course_relations AS i ON i.tid = t.id");
    	$query->where( 'i.courseid = ' . $id );
    	$query->where( 't.published = 1' );
    	$query->order( 't.name' );
    	
    	$db->setQuery($query);
    	return $db->loadObjectList();
    }
    
    static function getCourseRoute($id) {
    	$db = JFactory::getDBO();
    	$lang = JFactory::getLanguage()->getTag();
    	$userId = JFactory::getUser()->id;
    	
    	$access_levels_arr = JAccess::getAuthorisedViewLevels($userId);
    	$access_levels  = implode(',', $access_levels_arr);
    	
    	// get course slug for nice url
		$query = $db->getQuery(true);
		$query->select( 'CASE WHEN CHAR_LENGTH(i.alias) THEN CONCAT_WS(\':\', i.id, i.alias) ELSE i.id END AS slug' );
		$query->select('i.alt_url');
		$query->from( '#__seminarman_courses AS i' );
		$query->where( 'i.id = ' . $id );
		 
		$db->setQuery( $query );
		$course_arr = $db->loadAssoc();
		$course_slug = $course_arr['slug'];
		$course_alt_url = $course_arr['alt_url'];
		
		$global_params = JComponentHelper::getParams('com_seminarman');
		
		if ($global_params->get('use_alt_link_in_table') && !( empty( $course_alt_url ) || $course_alt_url == "http://" || $course_alt_url == "https://" )) {
		    $url_output = array();
		    $url_output['url'] = $course_alt_url;
		    return $url_output;
		}

    	// load all seminarman related menu items (frontend)
    	// menu type: seminarman course
    	$query = $db->getQuery(true);
    	$query->select( '*' );
    	$query->from( '#__menu' );
    	$query->where( '`link` like "index.php?option=com_seminarman&view=courses%"' );
    	$query->where( 'client_id=0' );
    	$query->where( 'published=1' );
    	$query->where( '(language="*" or language = "'.$lang.'")' );
    	$query->where( 'access IN('.$access_levels.')' );
    		
    	$db->setQuery( $query );
    	$menu_items_sman_course = $db->loadObjectList();

    	// menu type: category    	
    	$query = $db->getQuery(true);
    	$query->select( '*' );
    	$query->from( '#__menu' );
    	$query->where( '`link` like "index.php?option=com_seminarman&view=category%"' );
    	$query->where( 'client_id=0' );
    	$query->where( 'published=1' );
    	$query->where( '(language="*" or language = "'.$lang.'")' );
    	$query->where( 'access IN('.$access_levels.')' );
    	
    	$db->setQuery( $query );
    	$menu_items_sman_category = $db->loadObjectList();
    	
    	// menu type: tag    	
    	$query = $db->getQuery(true);
    	$query->select( '*' );
    	$query->from( '#__menu' );
    	$query->where( '`link` like "index.php?option=com_seminarman&view=tags%"' );
    	$query->where( 'client_id=0' );
    	$query->where( 'published=1' );
    	$query->where( '(language="*" or language = "'.$lang.'")' );
    	$query->where( 'access IN('.$access_levels.')' );
    	
    	$db->setQuery( $query );
    	$menu_items_sman_tag = $db->loadObjectList();
    	
    	// menu type: tutors
    	$query = $db->getQuery(true);
    	$query->select( '*' );
    	$query->from( '#__menu' );
    	$query->where( '`link` like "index.php?option=com_seminarman&view=tutors%"' );
    	$query->where( 'client_id=0' );
    	$query->where( 'published=1' );
    	$query->where( '(language="*" or language = "'.$lang.'")' );
    	$query->where( 'access IN('.$access_levels.')' );
    	
    	$db->setQuery( $query );
    	$menu_items_sman_tutors = $db->loadObjectList();
    	
        // we now have all possible menu positions to show the given course
    	// priority 1: is there a menu entry direct for the given course?
    	if(!empty($menu_items_sman_course)) {
	    	foreach ($menu_items_sman_course AS $menu_item) {
	    		if($menu_item->link == 'index.php?option=com_seminarman&view=courses&id='.$id) {
	    			$url_output = array('itemid' => $menu_item->id, 'link' => $menu_item->link, 'type' => 'course');  // add the extra info to url array, maybe we need them later somewhere
	    		    // build url for the given course in this case (direct output)
	    		    $url_output['url'] = JRoute::_('index.php?Itemid='.$menu_item->id);
	    			return $url_output;
	    		}
	    	}
    	}
    	
    	// priority 2: try to find a menu entry with type of course category for the given course
    	// get all categories of the course
    	if(!empty($menu_items_sman_category)) {
	    	require_once ( JPATH_ROOT.DS.'components'.DS.'com_seminarman'.DS.'classes'.DS.'categories.php');
	        
	        $query_cats = $db->getQuery(true);
	        $query_cats->select( 'DISTINCT c.id' );
	        $query_cats->from( '#__seminarman_categories AS c' );
	        $query_cats->join( "LEFT", "#__seminarman_cats_course_relations AS rel ON (rel.catid = c.id)");
	        $query_cats->where( 'rel.courseid = ' . $id . ' AND c.published = 1' );
	        $db->setQuery( $query_cats );
	        
	        $course_cats = $db->loadColumn();  // it is an array

	        foreach ($course_cats AS $course_cat) {
	        	$course_cat_obj = new seminarman_cats($course_cat);

	        	$all_cats = array_merge($course_cats, $course_cat_obj->parentcats);
	        	$all_cats_to_check = array_unique($all_cats, SORT_REGULAR);
	        }  // $all_cats_to_check is the unique cats array. we can reach the given course thru any of the categories in this array 
	        
	        foreach ($menu_items_sman_category AS $menu_item) {
	            foreach ($all_cats_to_check AS $cat_check) {
	        	    if($menu_item->link == 'index.php?option=com_seminarman&view=category&cid='.$cat_check) {
	        	    	$url_output = array('itemid' => $menu_item->id, 'link' => $menu_item->link, 'type' => 'category');  // add the extra info to url array, maybe we need them later somewhere
	        	    	// build url for the given course in this case
	        	    	// get category slug for nice url
	        	    	
	        	    	$query = $db->getQuery(true);
	        	    	$query->select( 'CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END AS slug' );
	        	    	$query->from( '`#__seminarman_categories` AS c' );
	        	    	$query->where( 'c.id = ' . $cat_check );
	        	    	 
	        	    	$db->setQuery( $query );
	        	    	$category_slug = $db->loadResult();
	        	    	
	        	    	$url_output['url'] = JRoute::_('index.php?option=com_seminarman&view=courses&cid='.$category_slug.'&id='.$course_slug.'&Itemid='.$menu_item->id);
	        	    	return $url_output;
	        	    }
	            }
	        }
    	}
    	   	
    	// priority 3: try to find a menu entry with type of tag for the given course
    	if(!empty($menu_items_sman_tag)) {
	        $query = $db->getQuery(true);
	        $query->select( 'DISTINCT t.id' );
	        $query->from( '#__seminarman_tags AS t' );
    		$query->join( "LEFT", "#__seminarman_tags_course_relations AS i ON i.tid = t.id");
	        $query->where( 'i.courseid = ' . $id );
	        $query->where( 't.published = 1' );
	         
	        $db->setQuery( $query );
	        $course_tags = $db->loadColumn();  // it is an array
	        
	        foreach ($menu_items_sman_tag AS $menu_item) {
	        	foreach ($course_tags AS $course_tag) {
	        		if($menu_item->link == 'index.php?option=com_seminarman&view=tags&id='.$course_tag) {
	        			$url_output = array('itemid' => $menu_item->id, 'link' => $menu_item->link, 'type' => 'tag');  // add the extra info to url array, maybe we need them later somewhere
	        			// build url for the given course in this case
	        			$url_output['url'] = JRoute::_('index.php?option=com_seminarman&view=courses&mod=1&id='.$course_slug.'&Itemid='.$menu_item->id);
	        			return $url_output;
	        		}
	        	}
	        }
    	}
        
    	// priority 4: check if the menu entry "trainer list" exists
    	if(!empty($menu_items_sman_tutors)) {
    		foreach ($menu_items_sman_tutors AS $menu_item) {
    			$url_output = array('itemid' => $menu_item->id, 'link' => $menu_item->link, 'type' => 'tutors');  // add the extra info to url array, maybe we need them later somewhere
    			// build url for the given course in this case
    			$url_output['url'] = JRoute::_('index.php?option=com_seminarman&view=courses&mod=1&id='.$course_slug.'&Itemid='.$menu_item->id);
    			return $url_output;
    		}
    	}
    	
    	// nothing found
    	return false;
    }
    
    static function getCatsPath($parents, $view, $Itemid, $course_title = '') {
       	$path = '<div class="sman-path">';
    	$path .= '<span class="sman-path-title">' . JText::_('COM_SEMINARMAN_YOU_HERE') . '</span>';
    	
    	$index = 0;
    	$count = count($parents);
    	foreach ($parents as $parent) {
    		$url = JRoute::_('index.php?option=com_seminarman&view=category&cid=' . $parent->categoryslug . '&Itemid=' . $Itemid);
    		if ((($view == 'cats') && ($index < ($count - 1))) || ($view == 'unit')) {
    		    $path .= '<a class="pathway" href="'. $url .'">' . ($parent->title) . '</a>';
    		    $path .= '<img src="' . JURI::root() . 'components/com_seminarman/assets/images/arrow.png">';
    		} else {
    			$path .= ($parent->title);
    		}
    		$index += 1;
    	}
    	
    	$path .= $course_title;
    	$path .= '</div>';
    	
    	return $path;
    }
    
    static function buildCourseTableHeader($params, $courses, $lists, $enable_booking) {
 
    	$header = '<tr>';
    	
    	$header_items = array();
    	
    	if ($params->get('show_thumbnail_in_table')) {
    		$header_items['image'] = '<th id="qf_image" class="sectiontableheader"></th>';
    	} else {
    		$header_items['image'] = '';
    	}
    	
    	if ($params->get('show_code_in_table')) {
    		$header_items['code'] = '<th id="qf_code" class="sectiontableheader">' . JHTML::_('grid.sort', 'COM_SEMINARMAN_COURSE_CODE', 'i.code', $lists['filter_order_Dir'], $lists['filter_order']) . '</th>';
    	} else {
    		$header_items['code'] = '';
    	}
    	
    	$header_items['title'] = '<th id="qf_title" class="sectiontableheader">' . JHTML::_('grid.sort', 'COM_SEMINARMAN_COURSE_TITLE', 'i.title', $lists['filter_order_Dir'], $lists['filter_order']) . '</th>';
    	
    	if ($params->get('show_tags_in_table')) {
    		$header_items['tags'] = '<th id="qf_tags" class="sectiontableheader">' . JText::_('COM_SEMINARMAN_ASSIGNED_TAGS') . '</th>';
    	} else {
    		$header_items['tags'] = '';
    	}
    	
    	if ($params->get('show_begin_date_in_table')) {
    		$header_items['start_date'] = '<th id="qf_start_date" class="sectiontableheader">' . JHTML::_('grid.sort', 'COM_SEMINARMAN_START_DATE', 'i.start_date', $lists['filter_order_Dir'], $lists['filter_order']) . '</th>';
    	} else {
    		$header_items['start_date'] = '';
    	}
    	if ($params->get('show_end_date_in_table')) {
    		$header_items['finish_date'] = '<th id="qf_finish_date" class="sectiontableheader">' . JHTML::_('grid.sort', 'COM_SEMINARMAN_FINISH_DATE', 'i.finish_date', $lists['filter_order_Dir'],	$lists['filter_order']) . '</th>';
    	} else {
    		$header_items['finish_date'] = '';
    	}
    	if ($params->get('show_begin_time_in_table')) {
    		$header_items['start_time'] = '<th id="qf_start_time" class="sectiontableheader">' .  JText::_('COM_SEMINARMAN_TIME2') . '</th>';
    	} else {
    		$header_items['start_time'] = '';
    	}
    	if ($params->get('show_finish_time_in_table')) {
    		$header_items['finish_time'] = '<th id="qf_finish_time" class="sectiontableheader">' .  JText::_('COM_SEMINARMAN_TIME3') . '</th>';
    	} else {
    		$header_items['finish_time'] = '';
    	}

    	if ($params->get('show_spaces_in_table')) {
    		if (($params->get('show_space_indicator_in_table') == 1) && $params->get('current_capacity')) {
    			$has_span = 'colspan="2" ';
    		} else {
    			$has_span = '';
    		}
    		$header_items['seats'] = '<th id="qf_seats" ' .$has_span. 'class="sectiontableheader">' . JText::_('COM_SEMINARMAN_SEATS') . '</th>';
    	} else {
    		$header_items['seats'] = '';
    	}    	
    	
    	if ($params->get('show_location')) {
    		$header_items['location'] = '<th id="qf_location" class="sectiontableheader">' . JHTML::_('grid.sort', 'COM_SEMINARMAN_LOCATION', 'i.location', $lists['filter_order_Dir'],	$lists['filter_order']) . '</th>';
    	} else {
    		$header_items['location'] = '';
    	}
    	
    	if ($params->get("custom_fld_1_in_table")) {
    		$header_items['custom_1'] = '<th id="qf_custom_fld_1" class="sectiontableheader">' . $courses[0]->custom1_lbl . '</th>';
    	} else {
    		$header_items['custom_1'] = '';
    	}
    	if ($params->get("custom_fld_2_in_table")) {
    		$header_items['custom_2'] = '<th id="qf_custom_fld_2" class="sectiontableheader">' . $courses[0]->custom2_lbl . '</th>';
    	} else {
    		$header_items['custom_2'] = '';
    	}
    	
    	if ($params->get("custom_fld_3_in_table")) {
    		$header_items['custom_3'] = '<th id="qf_custom_fld_3" class="sectiontableheader">' . $courses[0]->custom3_lbl . '</th>';
    	} else {
    		$header_items['custom_3'] = '';
    	}
    	
    	if ($params->get("custom_fld_4_in_table")) {
    		$header_items['custom_4'] = '<th id="qf_custom_fld_4" class="sectiontableheader">' . $courses[0]->custom4_lbl . '</th>';
    	} else {
    		$header_items['custom_4'] = '';
    	}
    	
    	if ($params->get("custom_fld_5_in_table")) {
    		$header_items['custom_5'] = '<th id="qf_custom_fld_5" class="sectiontableheader">' . $courses[0]->custom5_lbl . '</th>';
    	} else {
    		$header_items['custom_5'] = '';
    	}
    	
    	if ($params->get('show_price_in_table')) {
    		$header_items['price'] = '<th id="qf_price" class="sectiontableheader">' . JHTML::_('grid.sort', 'COM_SEMINARMAN_PRICE', 'i.price', $lists['filter_order_Dir'], $lists['filter_order']) . (($params->get('show_gross_price') != 2) ? "*" : "") . '</th>';
    	} else {
    		$header_items['price'] = '';
    	}
    	
        if ($params->get('show_booking_deadline_in_table')) {
        	$header_items['deadline'] = '<th id="qf_booking_deadline" class="sectiontableheader">' . JText::_('COM_SEMINARMAN_BOOKING_DEADLINE') . '</th>';
        } else {
        	$header_items['deadline'] = '';
        }
        
    	if ($enable_booking) {
    		$header_items['booking'] = '<th id="qf_application" class="sectiontableheader"></th>';  	
    	} else {
    		$header_items['booking'] = '';
    	}
    	
    	switch($params->get('custom_fld_layout_in_table')) {
    		case 0:
    		    $header .= $header_items['image'] . $header_items['code'] . $header_items['title'] . $header_items['tags'] . $header_items['start_date'] . $header_items['start_time'] . $header_items['finish_date'] . $header_items['finish_time'] . $header_items['seats'] . $header_items['location'] . $header_items['custom_1'] . $header_items['custom_2'] . $header_items['custom_3']. $header_items['custom_4'] . $header_items['custom_5'] . $header_items['price'] . $header_items['deadline'] . $header_items['booking'];
    		    break;
    		case 1:
    		    $header .= $header_items['image'] . $header_items['code'] . $header_items['title'] . $header_items['custom_1'] . $header_items['tags'] . $header_items['start_date'] . $header_items['start_time'] . $header_items['finish_date'] . $header_items['finish_time'] . $header_items['custom_2'] . $header_items['seats'] . $header_items['location'] . $header_items['custom_3']. $header_items['custom_4'] . $header_items['price'] . $header_items['custom_5'] . $header_items['deadline'] . $header_items['booking'];
    		    break;
    		case 2:
    			$header .= $header_items['image'] . $header_items['code'] . $header_items['title'] . $header_items['custom_1'] . $header_items['custom_2'] . $header_items['tags'] . $header_items['start_date'] . $header_items['start_time'] . $header_items['finish_date'] . $header_items['finish_time'] . $header_items['custom_3']. $header_items['seats'] . $header_items['location'] . $header_items['custom_4'] . $header_items['price'] . $header_items['custom_5'] . $header_items['deadline'] . $header_items['booking'];
    		    break;
    		default:
    			$header .= $header_items['image'] . $header_items['code'] . $header_items['title'] . $header_items['tags'] . $header_items['start_date'] . $header_items['start_time'] . $header_items['finish_date'] . $header_items['finish_time'] . $header_items['seats'] . $header_items['location'] . $header_items['custom_1'] . $header_items['custom_2'] . $header_items['custom_3']. $header_items['custom_4'] . $header_items['custom_5'] . $header_items['price'] . $header_items['deadline'] . $header_items['booking'];
    	}
    	
    	$header .= '</tr>';
    	
    	return $header;
    }
    
    static function buildCourseTableRows($params, $courses, $enable_booking, $category = null, $Itemid) {
    	
    	CMFactory::load( 'helpers' , 'string' );
    	
    	$rows = '';
    	$i = 0;
    	foreach ($courses as $course):
    	    $rows .= '<tr class="sectiontableentry">';	

    	    $row_items = array();
    	    
    	    if ($params->get('show_thumbnail_in_table')) {
    	    	$baseurl = JURI::base();
    	    	$item_image = $baseurl . $params->get('image_path', 'images') . '/' . $course->image;
    	    	$row_items['image'] = '<td class="centered thumb" headers="qf_image"><a href="' . (($params->get('use_alt_link_in_table') && !( empty( $course->alt_url ) || $course->alt_url == "http://" || $course->alt_url == "https://" )) ? $course->alt_url : JRoute::_('index.php?option=com_seminarman&view=courses&' . (isset($category) ? 'cid=' . $category->slug : 'mod=1') . '&id=' . $course->slug . '&Itemid=' . $Itemid)) . '"><img src="'.$item_image.'" alt=""></a></td>';
    	    } else {
    	    	$row_items['image'] = '';
    	    }
    	    
    	    if ($params->get('show_code_in_table')) {
    	    	$row_items['code'] = '<td headers="qf_code" data-title="' . JText::_('COM_SEMINARMAN_COURSE_CODE') . '">' . cEscape($course->code) . '</td>';
    	    } else {
    	    	$row_items['code'] = '';
    	    }
    	    
    	    $row_items['title'] = '<td headers="qf_title" data-title="' . JText::_('COM_SEMINARMAN_COURSE_TITLE') . '"><strong><a href="' . (($params->get('use_alt_link_in_table') && !( empty( $course->alt_url ) || $course->alt_url == "http://" || $course->alt_url == "https://" )) ? $course->alt_url : JRoute::_('index.php?option=com_seminarman&view=courses&' . (isset($category) ? 'cid=' . $category->slug : 'mod=1') . '&id=' . $course->slug . '&Itemid=' . $Itemid)) . '">' . cEscape($course->title) . '</a></strong>' . $course->show_new_icon . $course->show_sale_icon . '</td>';
    	    
    	    if ($params->get('show_tags_in_table')) {
    	        $row_items['tags'] = '<td headers="qf_tags" data-title="' . JText::_('COM_SEMINARMAN_ASSIGNED_TAGS') . '">';
    	    	$tags = $course->tags;
    	        $n = count($tags);
    	        $i = 0;
    	        if ($n != 0):
    	        	foreach ($tags as $tag):
    	    		$row_items['tags'] .= '<span>';
    	    			$row_items['tags'] .= '<a href="' . JRoute::_('index.php?option=com_seminarman&view=tags&id=' . $tag->slug . '&Itemid=' . $Itemid) . '">' . cEscape($tag->name) . '</a>';
    	    		$row_items['tags'] .= '</span>';
    	            $i++; 
    	            if ($i != $n) $row_items['tags'] .= ', ';
    	    		endforeach;
    	        endif;

    	        $row_items['tags'] .= '</td>';
    	    } else {
    	    	$row_items['tags'] = '';
    	    }
    	    
    	    if ($params->get('show_begin_date_in_table')) {
    	    	$row_items['start_date'] = '<td headers="qf_start_date" data-title="' . JText::_('COM_SEMINARMAN_START_DATE') . '">' . $course->start . '</td>';
    	    } else {
    	    	$row_items['start_date'] = '';
    	    }
    	    
    	    if ($params->get('show_end_date_in_table')) {
    	    	$row_items['finish_date'] = '<td headers="qf_finish_date" data-title="' . JText::_('COM_SEMINARMAN_FINISH_DATE') . '">' . $course->finish . '</td>';
    	    } else {
    	    	$row_items['finish_date'] = '';
    	    }
    	    
    	    if ($params->get('show_begin_time_in_table')) {
    	    	$row_items['start_time'] = '<td headers="qf_start_time" data-title="' . JText::_('COM_SEMINARMAN_TIME2') . '">' . $course->start_time_local . '</td>';
    	    } else {
    	    	$row_items['start_time'] = '';
    	    }
    	    
    	    if ($params->get('show_finish_time_in_table')) {
    	    	$row_items['finish_time'] = '<td headers="qf_finish_time" data-title="' . JText::_('COM_SEMINARMAN_TIME3') . '">' . $course->finish_time_local . '</td>';
    	    } else {
    	    	$row_items['finish_time'] = '';
    	    }
    	    
    	    if ($params->get('show_spaces_in_table')) {
    	    	if ($params->get('show_space_indicator_in_table') != 2 || !($params->get('current_capacity'))) {
    	    	    $row_items['seats'] = '<td headers="qf_seats" data-title="' . JText::_('COM_SEMINARMAN_SEATS') . '">' . $course->capacity_tbl . '</td>';
    	    	} else {
    	    		$row_items['seats'] = '';
    	    	}
    	    	if ($params->get('show_space_indicator_in_table') && $params->get('current_capacity')) {
    	    		$lighting = SMANFunctions::state_lighting($course->booked_places, $course->min_attend, $course->maxCapacity);
    	    		$row_items['semaforo'] = '<td class="lighting">'.$lighting.'</td>';
    	    	} else {
    	    		$row_items['semaforo'] = '';
    	    	}
    	    } else {
    	    	$row_items['seats'] = '';
    	    	$row_items['semaforo'] = '';
    	    }
    	    
    	    if ($params->get('show_location')) {
    	    	$row_items['location'] = '<td headers="qf_location" data-title="' . JText::_('COM_SEMINARMAN_LOCATION') . '">';
    	        if (empty($course->location)) {
    	            $row_items['location'] .= JText::_('COM_SEMINARMAN_NOT_SPECIFIED');
    	        } else {
    	            if (empty($course->url) || $course->url == "http://") {
    	                $row_items['location'] .= $course->location;
    	            } else {
    	                $row_items['location'] .= '<a href="' . $course->url. '" target="_blank">' . $course->location . '</a>';
    	            }
    	        }
    	    	$row_items['location'] .= '</td>';
    	    } else {
    	    	$row_items['location'] = '';
    	    }
    	    
    	    if ($params->get("custom_fld_1_in_table")) {
    	    	$row_items['custom_1'] = '<td headers="qf_custom_fld_1" data-title="' . $course->custom1_lbl . '">' . $course->custom1_val . '</td>';
    	    } else {
    	    	$row_items['custom_1'] = '';
    	    }    	    
    	    if ($params->get("custom_fld_2_in_table")) {
    	    	$row_items['custom_2'] = '<td headers="qf_custom_fld_2" data-title="' . $course->custom2_lbl . '">' . $course->custom2_val . '</td>';
    	    } else {
    	    	$row_items['custom_2'] = '';
    	    }
    	    if ($params->get("custom_fld_3_in_table")) {
    	    	$row_items['custom_3'] = '<td headers="qf_custom_fld_3" data-title="' . $course->custom3_lbl . '">' . $course->custom3_val . '</td>';
    	    } else {
    	    	$row_items['custom_3'] = '';
    	    }
    	    if ($params->get("custom_fld_4_in_table")) {
    	    	$row_items['custom_4'] = '<td headers="qf_custom_fld_4" data-title="' . $course->custom4_lbl . '">' . $course->custom4_val . '</td>';
    	    } else {
    	    	$row_items['custom_4'] = '';
    	    }
    	    if ($params->get("custom_fld_5_in_table")) {
    	    	$row_items['custom_5'] = '<td headers="qf_custom_fld_5" data-title="' . $course->custom5_lbl . '">' . $course->custom5_val . '</td>';
    	    } else {
    	    	$row_items['custom_5'] = '';
    	    }
    	    
    	    if ($params->get('show_price_in_table')) {
    	    	$row_items['price'] = '<td headers="qf_price" data-title="' . JText::_('COM_SEMINARMAN_PRICE') . '">' . $course->price . '</td>';
    	    } else {
    	    	$row_items['price'] = '';
    	    }

    	    if ($params->get('show_booking_deadline_in_table')) {
    	    	$row_items['deadline'] = '<td headers="qf_booking_deadline" data-title="' . JText::_('COM_SEMINARMAN_BOOKING_DEADLINE') . '">' . $course->deadline . '</td>';
    	    } else {
    	    	$row_items['deadline'] = '';
    	    }

    	    if ($enable_booking) {
    	    	$row_items['booking'] = '<td class="centered" headers="qf_book">' . $course->book_link . '</td>';
    	    } else {
    	    	$row_items['booking'] = '';
    	    }

    	    switch($params->get('custom_fld_layout_in_table')) {
    	    	case 0:
    	    	    $rows .= $row_items['image'] . $row_items['code'] . $row_items['title'] . $row_items['tags'] . $row_items['start_date'] . $row_items['start_time'] . $row_items['finish_date'] . $row_items['finish_time'] . $row_items['semaforo'] . $row_items['seats'] . $row_items['location'] . $row_items['custom_1'] . $row_items['custom_2'] . $row_items['custom_3']. $row_items['custom_4'] . $row_items['custom_5'] . $row_items['price'] . $row_items['deadline'] . $row_items['booking'];
    	    	    break;
    	    	case 1:
    	    	    $rows .= $row_items['image'] . $row_items['code'] . $row_items['title'] . $row_items['custom_1'] . $row_items['tags'] . $row_items['start_date'] . $row_items['start_time'] . $row_items['finish_date'] . $row_items['finish_time'] . $row_items['custom_2'] . $row_items['semaforo'] . $row_items['seats'] . $row_items['location'] . $row_items['custom_3']. $row_items['custom_4'] . $row_items['price'] . $row_items['custom_5'] . $row_items['deadline'] . $row_items['booking'];
    	    	    break;
    	    	case 2:
    	    		$rows .= $row_items['image'] . $row_items['code'] . $row_items['title'] . $row_items['custom_1'] . $row_items['custom_2'] . $row_items['tags'] . $row_items['start_date'] . $row_items['start_time'] . $row_items['finish_date'] . $row_items['finish_time'] . $row_items['custom_3']. $row_items['semaforo'] . $row_items['seats'] . $row_items['location'] . $row_items['custom_4'] . $row_items['price'] . $row_items['custom_5'] . $row_items['deadline'] . $row_items['booking'];
    	    	    break;
    	    	default:
    	    		$rows .= $row_items['image'] . $row_items['code'] . $row_items['title'] . $row_items['tags'] . $row_items['start_date'] . $row_items['start_time'] . $row_items['finish_date'] . $row_items['finish_time'] . $row_items['semaforo'] . $row_items['seats'] . $row_items['location'] . $row_items['custom_1'] . $row_items['custom_2'] . $row_items['custom_3']. $row_items['custom_4'] . $row_items['custom_5'] . $row_items['price'] . $row_items['deadline'] . $row_items['booking'];
    	    }    	    
    	    
    	    $rows .= '</tr>';
    	    $i++;
    	endforeach;
    	
    	return $rows;
    }
    
    static function buildCourseTableHeaderForMyBooking($params, $courses, $lists) {
    
    	$header = '<tr>';
    
    	if ($params->get('show_counter_in_my_bookings')) {
    		$header .= '<td class="proc2 centered sectiontableheader' . $params->get('pageclass_sfx') . '">' . JText::_('#') . '</td>';
    	} else {
    		$header .= '<td class="pix3 centered"></td>';
    	}
    	
    	$header_items = array();
    	
    	if ($params->get('show_code_in_my_bookings')) {
    		$header_items['code'] = '<th id="qf_code" class="sectiontableheader">' . JHTML::_('grid.sort', 'COM_SEMINARMAN_COURSE_CODE', 'i.code', $lists['filter_order_Dir'], $lists['filter_order']) . '</th>';
    	} else {
    		$header_items['code'] = '';
    	}
    	
    	$header_items['title'] = '<th id="qf_title" class="sectiontableheader">' . JHTML::_('grid.sort', 'COM_SEMINARMAN_COURSE_TITLE', 'i.title', $lists['filter_order_Dir'], $lists['filter_order']) . '</th>';
    	$header_items['start_date'] = '<th id="qf_start_date" class="sectiontableheader">' . JHTML::_('grid.sort', 'COM_SEMINARMAN_START_DATE', 'i.start_date', $lists['filter_order_Dir'], $lists['filter_order']) . '</th>';
    	$header_items['finish_date'] = '<th id="qf_finish_date" class="sectiontableheader">' . JHTML::_('grid.sort', 'COM_SEMINARMAN_FINISH_DATE', 'i.finish_date', $lists['filter_order_Dir'],	$lists['filter_order']) . '</th>';

    	if ($params->get('show_begin_time_in_my_bookings')) {
    		$header_items['start_time'] = '<th id="qf_start_time" class="sectiontableheader">' .  JText::_('COM_SEMINARMAN_TIME2') . '</th>';
    	} else {
    		$header_items['start_time'] = '';
    	}
    	if ($params->get('show_finish_time_in_my_bookings')) {
    		$header_items['finish_time'] = '<th id="qf_finish_time" class="sectiontableheader">' .  JText::_('COM_SEMINARMAN_TIME3') . '</th>';
    	} else {
    		$header_items['finish_time'] = '';
    	}    	
    	
    	if ($params->get("custom_fld_1_in_table")) {
    		$header_items['custom_1'] = '<th id="qf_custom_fld_1" class="sectiontableheader">' . $courses[0]->custom1_lbl . '</th>';
    	} else {
    		$header_items['custom_1'] = '';
    	}
    	if ($params->get("custom_fld_2_in_table")) {
    		$header_items['custom_2'] = '<th id="qf_custom_fld_2" class="sectiontableheader">' . $courses[0]->custom2_lbl . '</th>';
    	} else {
    		$header_items['custom_2'] = '';
    	}
    	if ($params->get("custom_fld_3_in_table")) {
    		$header_items['custom_3'] = '<th id="qf_custom_fld_3" class="sectiontableheader">' . $courses[0]->custom3_lbl . '</th>';
    	} else {
    		$header_items['custom_3'] = '';
    	}
    	if ($params->get("custom_fld_4_in_table")) {
    		$header_items['custom_4'] = '<th id="qf_custom_fld_4" class="sectiontableheader">' . $courses[0]->custom4_lbl . '</th>';
    	} else {
    		$header_items['custom_4'] = '';
    	}
    	if ($params->get("custom_fld_5_in_table")) {
    		$header_items['custom_5'] = '<th id="qf_custom_fld_5" class="sectiontableheader">' . $courses[0]->custom5_lbl . '</th>';
    	} else {
    		$header_items['custom_5'] = '';
    	}
    	if ($params->get('show_price_in_my_bookings')) {
    		$header_items['price'] = '<th id="qf_price" class="sectiontableheader">' . JHTML::_('grid.sort', 'COM_SEMINARMAN_PRICE', 'i.price', $lists['filter_order_Dir'], $lists['filter_order']) . (($params->get('show_gross_price') != 2) ? "*" : "") . '</th>';
    	} else {
    		$header_items['price'] = '';
    	}
    	if (($params->get('invoice_generate') == 1) && $params->get('show_invoice_in_my_bookings')) {
    		$header_items['invoice'] = '<th id="qf_invoice" class="sectiontableheader">' . JText::_('COM_SEMINARMAN_INVOICE') . '</th>';
    	} else {
    		$header_items['invoice'] = '';
    	}
    	
    	if ($params->get('enable_paypal')) {
    		$header_items['pay'] = '<th id="qf_application" class="sectiontableheader">' . JText::_('COM_SEMINARMAN_PAY_ONLINE') . '</th>';
    	} else {
    		$header_items['pay'] = '';
    	}
    	
    	switch($params->get('custom_fld_layout_in_table')) {
    		case 0:
    		    $header .= $header_items['code'] . $header_items['title'] . $header_items['start_date'] . $header_items['start_time'] . $header_items['finish_date'] . $header_items['finish_time'] . $header_items['custom_1'] . $header_items['custom_2'] . $header_items['custom_3']. $header_items['custom_4'] . $header_items['custom_5'] . $header_items['price'] . $header_items['invoice'] . $header_items['pay'];
    		    break;
    		case 1:
    		    $header .= $header_items['code'] . $header_items['title'] . $header_items['custom_1'] . $header_items['start_date'] . $header_items['start_time'] . $header_items['finish_date'] . $header_items['finish_time'] . $header_items['custom_2'] . $header_items['custom_3']. $header_items['custom_4'] . $header_items['price'] . $header_items['custom_5'] . $header_items['invoice'] . $header_items['pay'];
    		    break;
    		case 2:
    			$header .= $header_items['code'] . $header_items['title'] . $header_items['custom_1'] . $header_items['custom_2'] . $header_items['start_date'] . $header_items['start_time'] . $header_items['finish_date'] . $header_items['finish_time'] . $header_items['custom_3']. $header_items['custom_4'] . $header_items['price'] . $header_items['custom_5'] . $header_items['invoice'] . $header_items['pay'];
    		    break;
    		default:
    			$header .= $header_items['code'] . $header_items['title'] . $header_items['start_date'] . $header_items['start_time'] . $header_items['finish_date'] . $header_items['finish_time'] . $header_items['custom_1'] . $header_items['custom_2'] . $header_items['custom_3']. $header_items['custom_4'] . $header_items['custom_5'] . $header_items['price'] . $header_items['invoice'] . $header_items['pay'];
    	}
    	 
    	$header .= '</tr>';
    	 
    	return $header;
    }
    
    static function buildCourseSingleRowForMyBooking($course, $category, $itemParams, $params, $pageNav, $Itemid) {
    	CMFactory::load( 'helpers' , 'string' );
    	$row = '<tr class="sectiontableentry" >';
    	
    	if ($params->get('show_counter_in_my_bookings')) {
    		$row .= '<td headers="qf_publish_up" data-title="' . JText::_('#') . '">' . $pageNav->getRowOffset( $course->count ) . '</td>';
    	} else {
    		$row .= '<td headers="qf_publish_up"></td>';
    	}
    	
    	$row_items = array();
    	
    	if ($params->get('show_code_in_my_bookings')) {
    		$row_items['code'] = '<td headers="qf_code" data-title="' . JText::_('COM_SEMINARMAN_COURSE_CODE') . '">' . cEscape($course->code) . '</td>';
    	} else {
    		$row_items['code'] = '';
    	}
    	
    	$row_items['title'] = '<td headers="qf_title"  data-title="' . JText::_('COM_SEMINARMAN_COURSE_TITLE') . '"><strong><a href="' . JRoute::_('index.php?option=com_seminarman&view=courses&cid=' . $category->slug . '&id=' . $course->slug . '&Itemid=' . $Itemid) . '">' . cEscape($course->title) . '</a></strong>' . $course->show_new_icon . $course->show_sale_icon . '</td>';
    	$row_items['start_date'] = '<td headers="qf_start_date" data-title="' . JText::_('COM_SEMINARMAN_START_DATE') . '">' . $course->start . '</td>';
    	$row_items['finish_date'] = '<td headers="qf_finish_date" data-title="' . JText::_('COM_SEMINARMAN_FINISH_DATE') . '">' . $course->finish . '</td>';

    	if ($params->get('show_begin_time_in_my_bookings')) {
    		$row_items['start_time'] = '<td headers="qf_start_time" data-title="' . JText::_('COM_SEMINARMAN_TIME2') . '">' . $course->start_time_local . '</td>';
    	} else {
    		$row_items['start_time'] = '';
    	}
    		
    	if ($params->get('show_finish_time_in_my_bookings')) {
    		$row_items['finish_time'] = '<td headers="qf_finish_time" data-title="' . JText::_('COM_SEMINARMAN_TIME3') . '">' . $course->finish_time_local . '</td>';
    	} else {
    		$row_items['finish_time'] = '';
    	}    	
    	
    	if ($params->get("custom_fld_1_in_table")) {
    		$row_items['custom_1'] = '<td headers="qf_custom_fld_1" data-title="' . $course->custom1_lbl . '">' . $course->custom1_val . '</td>';
    	} else {
    		$row_items['custom_1'] = '';
    	}
    	if ($params->get("custom_fld_2_in_table")) {
    		$row_items['custom_2'] = '<td headers="qf_custom_fld_2" data-title="' . $course->custom2_lbl . '">' . $course->custom2_val . '</td>';
    	} else {
    		$row_items['custom_2'] = '';
    	}
    	if ($params->get("custom_fld_3_in_table")) {
    		$row_items['custom_3'] = '<td headers="qf_custom_fld_3" data-title="' . $course->custom3_lbl . '">' . $course->custom3_val . '</td>';
    	} else {
    		$row_items['custom_3'] = '';
    	}
    	if ($params->get("custom_fld_4_in_table")) {
    		$row_items['custom_4'] = '<td headers="qf_custom_fld_4" data-title="' . $course->custom4_lbl . '">' . $course->custom4_val . '</td>';
    	} else {
    		$row_items['custom_4'] = '';
    	}
    	if ($params->get("custom_fld_5_in_table")) {
    		$row_items['custom_5'] = '<td headers="qf_custom_fld_5" data-title="' . $course->custom5_lbl . '">' . $course->custom5_val . '</td>';
    	} else {
    		$row_items['custom_5'] = '';
    	}	
    	if ($params->get("show_price_in_my_bookings")) {
    		$row_items['price'] = '<td headers="qf_price" data-title="' . JText::_('COM_SEMINARMAN_PRICE') . '">' . $course->price_simple . '</td>';
    	} else {
    		$row_items['price'] = '';
    	}
    	if (($params->get('invoice_generate') == 1) && $params->get("show_invoice_in_my_bookings")) {	
    		if (!empty($course->invoice_filename_prefix)) {
    			$row_items['invoice'] = '<td class="centered" data-title="' . JText::_('COM_SEMINARMAN_INVOICE') . '"><a href="'. JRoute::_('index.php?option=com_seminarman&view=bookings&layout=invoicepdf&appid=' . $course->applicationid . '&Itemid=' . $Itemid) .'"><img alt="'.$course->invoice_filename_prefix.$course->invoice_number.'.pdf" src="components/com_seminarman/assets/images/mime-icon-16/pdf.png" /></a></td>';
    		} else {
    			$row_items['invoice'] = '<td class="centered" data-title="' . JText::_('COM_SEMINARMAN_INVOICE') . '">-</td>';
    		}
    	} else {
    		$row_items['invoice'] = '';
    	}
    	
    	if ($params->get('enable_paypal')) {
    		$row_items['pay'] = '<td headers="qf_book" data-title="' . JText::_('COM_SEMINARMAN_PAY_ONLINE') . '">' . (($course->price > 0) ? $course->paypal_link : '') . '</td>';
    	} else {
    		$row_items['pay'] = '';
    	}
    	
    	switch($params->get('custom_fld_layout_in_table')) {
    		case 0:
    		    $row .= $row_items['code'] . $row_items['title'] . $row_items['start_date'] . $row_items['start_time'] . $row_items['finish_date'] . $row_items['finish_time'] . $row_items['custom_1'] . $row_items['custom_2'] . $row_items['custom_3']. $row_items['custom_4'] . $row_items['custom_5'] . $row_items['price'] . $row_items['invoice'] . $row_items['pay'];
    		    break;
    		case 1:
    		    $row .= $row_items['code'] . $row_items['title'] . $row_items['custom_1'] . $row_items['start_date'] . $row_items['start_time'] . $row_items['finish_date'] . $row_items['finish_time'] . $row_items['custom_2'] . $row_items['custom_3']. $row_items['custom_4'] . $row_items['price'] . $row_items['custom_5'] . $row_items['invoice'] . $row_items['pay'];
    		    break;
    		case 2:
    			$row .= $row_items['code'] . $row_items['title'] . $row_items['custom_1'] . $row_items['custom_2'] . $row_items['start_date'] . $row_items['start_time'] . $row_items['finish_date'] . $row_items['finish_time'] . $row_items['custom_3']. $row_items['custom_4'] . $row_items['price'] . $row_items['custom_5'] . $row_items['invoice'] . $row_items['pay'];
    		    break;
    		default:
    			$row .= $row_items['code'] . $row_items['title'] . $row_items['start_date'] . $row_items['start_time'] . $row_items['finish_date'] . $row_items['finish_time'] . $row_items['custom_1'] . $row_items['custom_2'] . $row_items['custom_3']. $row_items['custom_4'] . $row_items['custom_5'] . $row_items['price'] . $row_items['invoice'] . $row_items['pay'];
    	}    	
    	
    	$row .= '</tr>';
    	return $row;
    }
    
    static function buildCourseSingleOtherInfoForMyBooking($course, $itemParams, $params, $count_id, $Itemid) {
    	
    	$info_items = array();
    	
    	$stati = $course->booking_state;
    	if ($stati == 0) {
    		$stati_text = JText::_( 'COM_SEMINARMAN_SUBMITTED' );
    	} elseif ($stati == 1) {
    		$stati_text = JText::_( 'COM_SEMINARMAN_PENDING' );
    	} elseif ($stati == 2) {
    		$stati_text = JText::_( 'COM_SEMINARMAN_PAID' );
    	} elseif ($stati == 3) {
    		$stati_text = JText::_( 'COM_SEMINARMAN_CANCELED' );
    	} elseif ($stati == 4) {
    		$stati_text = JText::_( 'COM_SEMINARMAN_WL' );
    	} elseif ($stati == 5) {
    		$stati_text = JText::_( 'COM_SEMINARMAN_AWAITING_RESPONSE' );
    	}
    	
    	$info_items['state'] = '<div class="bunka hlavicka"><div class="matrjoska">'.JText::_('COM_SEMINARMAN_STATUS').': '.$stati_text.'</div></div>';
    	
    	if ($itemParams->get('show_hits', $params->get('show_hits'))) {
    		$info_items['hits'] = '<div class="bunka hlavicka"><div class="matrjoska">'.JText::_('COM_SEMINARMAN_HITS').': '.$course->hits.'</div></div>';
    	} else {
    		$info_items['hits'] = '';
    	}
    	
    	if ($itemParams->get('show_hyperlink', $params->get('show_hyperlink'))&& $course->alt_url<>"http://" && $course->alt_url<>"https://" && trim($course->alt_url)<>"") {
    		$info_items['link'] = '<div class="bunka hlavicka"><div class="matrjoska">'.$course->link.'</div></div>';
    	} else {
    		$info_items['link'] = '';
    	}
    	
    	if ($itemParams->get('show_tutor', $params->get('show_tutor'))) {
    		// in plan
    	}
    	
    	if ($itemParams->get('show_location', $params->get('show_location')) && !empty($course->location)) {
    		$info_items['location'] = '<div class="bunka hlavicka"><div class="matrjoska">';
    		if ( empty( $course->url ) || $course->url == "http://" ) {
    			$info_items['location'] .= JText::_('COM_SEMINARMAN_LOCATION').': '.$course->location;
    		} else {
    			$info_items['location'] .= JText::_('COM_SEMINARMAN_LOCATION') . ': <a target=_blank href="' . $course->url . '">' . $course->location . '</a>';
    		}
    		$info_items['location'] .= '</div></div>';
    	} else {
    		$info_items['location'] = '';
    	}
    	
    	if ($itemParams->get('show_group', $params->get('show_group')) && !empty($course->cgroup)) {
    		$info_items['group'] = '<div class="bunka hlavicka"><div class="matrjoska">'.JText::_('COM_SEMINARMAN_GROUP').': '.$course->cgroup.'</div></div>';
    	} else {
    		$info_items['group'] = '';
    	}
    	
    	if ($itemParams->get('show_experience_level', $params->get('show_experience_level')) && !empty($course->level)) {
    		$info_items['level'] = '<div class="bunka hlavicka"><div class="matrjoska">'.JText::_('COM_SEMINARMAN_LEVEL').': '.$course->level.'</div></div>';
    	} else {
    		$info_items['level'] = '';
    	}
    	
    	if ($itemParams->get('show_capacity', $params->get('show_capacity'))) {
    		$info_items['capacity'] = '<div class="bunka hlavicka"><div class="matrjoska">';
    		if ($itemParams->get('current_capacity', $params->get('current_capacity')) && $itemParams->get('show_capacity', $params->get('show_capacity')) > 1) {
    			$info_items['capacity'] .= JText::_('COM_SEMINARMAN_FREE_SEATS') .': ';
    		} else {
    			$info_items['capacity'] .= JText::_('COM_SEMINARMAN_SEATS') .': ';
    		}
    		$info_items['capacity'] .= $course->capacity.'</div></div>';
    	} else {
    		$info_items['capacity'] = '';
    	}
    	
    	if ($params->get('show_certificate_in_my_bookings') && !empty($course->certificate_file)) {
    		$info_items['certificate'] = '<div class="bunka hlavicka"><div class="matrjoska">';
    		$info_items['certificate'] .= JText::_('COM_SEMINARMAN_CERTIFICATE') .': ';
    		$info_items['certificate'] .= '<a href="'. JRoute::_('index.php?option=com_seminarman&view=bookings&layout=certificatepdf&appid=' . $course->applicationid . '&Itemid=' . $Itemid) .'"><img alt="'.$course->certificate_file.'" src="components/com_seminarman/assets/images/mime-icon-16/pdf.png" /></a>';
    		$info_items['certificate'] .= '</div></div>';
    	} else {
    		$info_items['certificate'] = '';
    	}
    	
    	if ($course->cancel_allowed) {
    		$info_items['cancel'] = '<div class="bunka hlavicka button2-left" style="float: right"><div class="matrjoska">';
    		$info_items['cancel'] .= '<a href="' . JRoute::_('index.php?option=com_seminarman&controller=application&task=cancel_booking&appid='.$course->applicationid.'&'.JSession::getFormToken().'=1') . '">' . JText::_('COM_SEMINARMAN_CANCEL') . '</a>';
    		$info_items['cancel'] .= '</div></div>';;
    	} else {
    		$info_items['cancel'] = '';
    	}
    	
    	$info_items['linebreak'] = '<div class="cl"></div>';
    	
    	if ($params->get('show_register_name', 0)) {
    		$info_items['register_name'] = '<div class="bunka hlavicka"><div class="matrjoska">';
    		$info_items['register_name'] .= JText::_('COM_SEMINARMAN_NAME').': ';
    		$display_name_head = trim(cEscape($course->booking_salutation).' '.cEscape($course->booking_title));
    		$display_name_tail = trim(cEscape($course->booking_first_name).' '.cEscape($course->booking_last_name));
    		if (!empty($display_name_head)) {
    			$info_items['register_name'] .= $display_name_head . ' ' . $display_name_tail;
    		} else {
    			$info_items['register_name'] .= $display_name_tail;
    		}
    		$info_items['register_name'] .= '</div></div>';
    	} else {
    		$info_items['register_name'] = '';
    	}
    	
    	return $info_items['state'] . $info_items['hits'] . $info_items['link'] . $info_items['location'] . $info_items['group'] . $info_items['level'] . $info_items['capacity'] . $info_items['cancel'] . $info_items['linebreak'] . $info_items['register_name'] . $info_items['certificate'];
    	
    }
    
    static function state_lighting($booked, $min, $max) {
    	if (is_null($booked)) $booked = 0;
	    if ($max == 0) {
	       return '<div class="semaforo ausgebucht" title="' . JText::_('COM_SEMINARMAN_EVENT_FULL') . '"></div>';
	    } else {
	       if ($booked < $min) {
	    	 return '<div class="semaforo buchbar" title="' . JText::_('COM_SEMINARMAN_EVENT_BOOKABLE') . '"></div>';
	       } elseif (($booked >= $min) && ($booked < $max)) {
	    	 return '<div class="semaforo garantiert" title="' . JText::_('COM_SEMINARMAN_EVENT_GUARANTEED') . '"></div>';
	       } elseif ($booked >= $max) {
	    	 return '<div class="semaforo ausgebucht" title="' . JText::_('COM_SEMINARMAN_EVENT_FULL') . '"></div>';
	       }
	    }
    }
    
    static function create_course_ics($course) {
    	jimport('joomla.filesystem.file');
    	$mainframe = JFactory::getApplication();
    	$params = JComponentHelper::getParams( 'com_seminarman' );   	
    	
    	CMFactory::load( 'helpers' , 'string' );
    	
    	$address = self::cleanTextForICS($course->location);
    	$description = self::cleanTextForICS($course->introtext . chr(13) . chr(13) . $course->fulltext);
    	$uri = self::getCourseRoute($course->id);

    	$summary = self::cleanTextForICS($course->title);
    	if ($course->start_date != '0000-00-00') {
    	    $datestart_int = strtotime($course->start_date . ' ' . $course->start_time);  // they are UTC, $courseRows from controller application
    	    $datestart = date('Ymd\THis', $datestart_int).'Z';
    	} else {
    		$datestart = '';
    	}
    	if ($course->finish_date != '0000-00-00') {
    	    $dateend_int = strtotime($course->finish_date . ' ' . $course->finish_time);  // tambien UTC
    	    $dateend = date('Ymd\THis', $dateend_int).'Z';
    	} else {
    		$dateend = '';
    	}
    	$jetzt_int = time();
    	$jetzt = date('Ymd\THis', $jetzt_int).'Z';
    	
    	$file_content = "BEGIN:VCALENDAR\n";
    	$file_content .= "X-WR-TIMEZONE:Europe/Berlin\n";
    	$file_content .= "VERSION:2.0\n";
    	$file_content .= "PRODID:-//hacksw/handcal//NONSGML v1.0//EN\n";
    	$file_content .= "CALSCALE:GREGORIAN\n";
    	$file_content .= "BEGIN:VTIMEZONE\n";
    	$file_content .= "TZID:Europe/Berlin\n";
    	$file_content .= "TZURL:http://tzurl.org/zoneinfo/Europe/Berlin\n";
    	$file_content .= "X-LIC-LOCATION:Europe/Berlin\n";
    	$file_content .= "BEGIN:DAYLIGHT\n";
    	$file_content .= "TZOFFSETFROM:+0100\n";
    	$file_content .= "TZOFFSETTO:+0200\n";
    	$file_content .= "TZNAME:CEST\n";
    	$file_content .= "DTSTART:20150328T020000\n";
    	$file_content .= "RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=2SU\n";
    	$file_content .= "END:DAYLIGHT\n";
    	$file_content .= "BEGIN:STANDARD\n";
    	$file_content .= "TZOFFSETFROM:+0200\n";
    	$file_content .= "TZOFFSETTO:+0100\n";
    	$file_content .= "TZNAME:CET\n";
    	$file_content .= "DTSTART:20141026T020000\n";
    	$file_content .= "RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=1SU\n";
    	$file_content .= "END:STANDARD\n";
    	$file_content .= "END:VTIMEZONE\n";
    	$file_content .= "BEGIN:VEVENT\n";
    	$file_content .= "UID:osg_course_schedule" . uniqid() . "\n";
    	// it's not necessary to use the cEscape for address, description and summary, the strip_html_tags is used before instead
    	$file_content .= "LOCATION:" . $address . "\n";
    	$file_content .= "DESCRIPTION:" . $description . "\n";
    	// echo "X-ALT-DESC;FMTTYPE=text/html:" . cEscape($description);
    	$file_content .= "URL;VALUE=URI:" . cEscape($uri) . "\n";
    	$file_content .= "SUMMARY:" . $summary . "\n";
    	$file_content .= "DTSTART:" . $datestart . "\n";
    	$file_content .= "DTEND:" . $dateend . "\n";
    	$file_content .= "DTSTAMP:" . $jetzt . "\n";
    	$file_content .= "BEGIN:VALARM\n";
    	$file_content .= "TRIGGER:-PT72H\n";
    	$file_content .= "REPEAT:1\n";
    	$file_content .= "DURATION:PT15M\n";
    	$file_content .= "ACTION:DISPLAY\n";
    	$file_content .= "DESCRIPTION:Reminder\n";
    	$file_content .= "END:VALARM\n";
    	$file_content .= "END:VEVENT\n";
    	$file_content .= "END:VCALENDAR";
    	
    	if ($params->get('ics_file_name') == 0) {
    	    $filename = "ical_course_" . $course->id . ".ics";
    	} else {
    	    $filename = JFile::makeSafe(str_replace(array('Ä','Ö','Ü','ä','ö','ü','ß'), array('Ae','Oe','Ue','ae','oe','ue','ss'), html_entity_decode($course->title, ENT_QUOTES)) . '_' . $course->id . ".ics");
    		$filename = str_replace(' ', '_', $filename);
    	}
    	
    	if (!(empty($datestart) && empty($dateend))) {
    	    $icsfile = fopen(JPATH_ROOT.DS.$params->get('invoice_save_dir').DS . $filename, "w") or die("Unable to open file!");
    	    fwrite($icsfile, $file_content);
    	    fclose($icsfile);
    	} else {
    		$ics_filepath = JPATH_ROOT.DS.$params->get('invoice_save_dir').DS . $filename;
    		if(JFile::exists($ics_filepath))JFile::delete($ics_filepath);
    	}
    }
    
    static function cleanTextForICS($input) {
    	$output = str_replace("\r\n", "", $input);
    	$output = str_replace("\r", "", $output);
    	$output = str_replace("\n", "", $output);
    	$output = str_replace(PHP_EOL, "", $output);
    	// $output = strip_tags(str_replace('<br />', '\n', $output));
    	// $output = htmlentities($output);
    	$output = self::strip_html_tags($output);
    	return $output;
    }
    
    static function strip_html_tags( $text )
    {
    	// PHP's strip_tags() function will remove tags, but it
    	// doesn't remove scripts, styles, and other unwanted
    	// invisible text between tags.  Also, as a prelude to
    	// tokenizing the text, we need to insure that when
    	// block-level tags (such as <p> or <div>) are removed,
    	// neighboring words aren't joined.
    	$text = preg_replace(
    			array(
    					// Remove invisible content
    					'@<head[^>]*?>.*?</head>@siu',
    					'@<style[^>]*?>.*?</style>@siu',
    					'@<script[^>]*?.*?</script>@siu',
    					'@<object[^>]*?.*?</object>@siu',
    					'@<embed[^>]*?.*?</embed>@siu',
    					'@<applet[^>]*?.*?</applet>@siu',
    					'@<noframes[^>]*?.*?</noframes>@siu',
    					'@<noscript[^>]*?.*?</noscript>@siu',
    					'@<noembed[^>]*?.*?</noembed>@siu',
    
    					// Add line breaks before & after blocks
    					'@<((br)|(hr))@iu',
    					'@</?((address)|(blockquote)|(center)|(del))@iu',
    					'@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
    					'@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
    					'@</?((table)|(th)|(td)|(caption))@iu',
    					'@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
    					'@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
    					'@</?((frameset)|(frame)|(iframe))@iu',
    			),
    			array(
    					' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
    					"[osg_lf]\$0", "[osg_lf]\$0", "[osg_lf]\$0", "[osg_lf]\$0", "[osg_lf]\$0", "[osg_lf]\$0",
    					"[osg_lf]\$0", "[osg_lf]\$0",
    			),
    			$text );
    
    	// Remove all remaining tags and comments and return.
    	return strip_tags( str_replace('[osg_lf]', '\n', $text) );
    }
    
    static function getCourseEmailAttachs( $course_id ) {
    	$db = JFactory::getDBO();
    	$query = $db->getQuery(true);
    	$query->select('f.filename')
    		->from('#__seminarman_files_course_relations AS ref')
    		->leftJoin('#__seminarman_courses AS c ON ref.courseid = c.id')
    		->leftJoin('#__seminarman_files AS f ON ref.fileid = f.id')
    		->where('c.id = ' . $course_id)
    		->where('ref.email_attach = 1');
    	$db->setQuery($query);
    	$result = $db->loadColumn();
    	
    	$attachs = array();
    	$basePath = COM_SEMINARMAN_FILEPATH;
    	if (!empty($result)) {
    		foreach($result AS $filename) {
    			$abspath = str_replace(DS, '/', JPath::clean($basePath . DS . $filename));
    			if (JFile::exists($abspath)) $attachs[] = $abspath;
    		}
    	}
    	
    	return $attachs;
    }
    
}
?>