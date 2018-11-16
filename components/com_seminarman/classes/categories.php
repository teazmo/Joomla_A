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

class seminarman_cats
{
    var $id = null;

    var $parentcats = array();

    var $category = array();

    function __construct($cid)
    {	
    	$this->id = $cid;
        $this->buildParentCats($this->id);
        $this->getParentCats();
    }

    function getParentCats()
    {
        $db = JFactory::getDBO();

        $this->parentcats = array_reverse($this->parentcats);

        foreach ($this->parentcats as $cid)
        {
			$query = $db->getQuery(true);
			$query->select( 'id, title, CASE WHEN CHAR_LENGTH(alias) THEN CONCAT_WS(\':\', id, alias) ELSE id END as categoryslug' );
			$query->from('#__seminarman_categories');
			$query->where( 'id =' . (int)$cid );
			$query->where( 'published = 1' );
			
			$db->setQuery($query);
            $this->category[] = $db->loadObject();
        }
    }

    function buildParentCats( $cid )
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select( 'parent_id' );
        $query->from('#__seminarman_categories');
        $query->where( 'id = ' . (int)$cid );        
        
        $db->setQuery($query);
        $parcats = $db->loadResult();

        if ($cid != 0)
        {
            array_push( $this->parentcats, $cid );
        }

        if ( $parcats != 0)
        {
            $this->buildParentCats( $parcats );
        }
    }

    function getParentlist()
    {
        return $this->category;
    }

    static function getCategoriesTree($published)
    {
        $db = JFactory::getDBO();

        if ($published)
        {
            $where = 'published = 1';
        } else
        {
            $where = '';
        }

        $query = $db->getQuery(true);
        $query->select( '*, id AS value, title AS text' );
        $query->from('#__seminarman_categories');
        if ( $where != '' ) {
	        $query->where( $where );
        }
        $query->order( 'parent_id, ordering' );
        
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        $levellimit = 10;

        $children = array();
        foreach ($rows as $child)
        {
            $parent = $child->parent_id;
            $list = @$children[$parent] ? $children[$parent] : array();
            array_push($list, $child);
            $children[$parent] = $list;
        }

        $list = seminarman_cats::treerecurse(0, '', array(), $children, true, max(0, $levellimit -
            1));

        return $list;
    }

    static function treerecurse($id, $indent, $list, &$children, $title, $maxlevel = 9999,
        $level = 0, $type = 1)
    {
        if (@$children[$id] && $level <= $maxlevel)
        {
            foreach ($children[$id] as $v)
            {
                $id = $v->id;

                if ($type)
                {
                    $pre = '<sup>|_</sup>&nbsp;';
                    $spacer = '.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                } else
                {
                    $pre = '- ';
                    $spacer = '&nbsp;&nbsp;';
                }

                if ($title)
                {
                    if ($v->parent_id == 0)
                    {
                        $txt = '' . $v->title;
                    } else
                    {
                        $txt = $pre . $v->title;
                    }
                } else
                {
                    if ($v->parent_id == 0)
                    {
                        $txt = '';
                    } else
                    {
                        $txt = $pre;
                    }
                }
                $pt = $v->parent_id;
                $list[$id] = $v;
                //temporarly hidden tree hiararchy
                $list[$id]->treename = "$indent$txt";
                $list[$id]->children = array_key_exists($id, $children) ? count($children[$id]) : 0;

                $list = seminarman_cats::treerecurse($id, $indent . $spacer, $list, $children, $title,
                    $maxlevel, $level + 1, $type);
            }
        }
        return $list;
    }


    static function buildcatselect($list, $name, $selected, $top, $class =
        'class="inputbox"')
    {
        $catlist = array();

        if ($top)
        {
            $catlist[] = JHTML::_('select.option', '0', '- '. JText::_('COM_SEMINARMAN_ALL_CATEGORIES') .' -');
        }

        foreach ($list as $course)
        {
            $catlist[] = JHTML::_('select.option', $course->id, $course->title);
        }
        return JHTML::_('select.genericlist', $catlist, $name, $class, 'value', 'text',
            $selected);
    }

}

?>