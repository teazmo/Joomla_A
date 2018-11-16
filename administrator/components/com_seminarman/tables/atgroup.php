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

class Tableatgroup extends JTable
{

    function __construct(&$db)
    {
        parent::__construct('#__seminarman_atgroup', 'id', $db);
    }

    function bind($array, $ignore = '')
    {
        if (key_exists('params', $array) && is_array($array['params']))
        {
            $registry = new JRegistry();
            $registry->loadArray($array['params']);
            $array['params'] = $registry->toString();
        }

        return parent::bind($array, $ignore);
    }

    function check()
    {
        if (trim($this->title) == '')
        {
            $this->setError(JText::_('The record must contain a title.'));
            return false;
        }
        $query = $this->_db->getQuery(true);
        $query->select( 'id' );
        $query->from( '#__seminarman_atgroup' );
        $query->where( 'title = ' . $this->_db->Quote( $this->title ) );
        $this->_db->setQuery($query);

        $xid = intval($this->_db->loadResult());
        if ($xid && $xid != intval($this->id))
        {
            $this->setError(JText::sprintf('WARNNAMETRYAGAIN', JText::_('Error occured')));
            return false;
        }

        if (empty($this->alias))
        {
            $this->alias = $this->title;
        }
        $this->alias = JFilterOutput::stringURLSafe($this->alias);
        if (trim(str_replace('-', '', $this->alias)) == '')
        {
            $datenow = JFactory::getDate();
            $this->alias = $datenow->format("Y-m-d-H-i-s");
        }

        return true;
    }
}