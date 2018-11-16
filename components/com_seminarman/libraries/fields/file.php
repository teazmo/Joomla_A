<?php
/**
 * @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
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
defined('_JEXEC') or die('Restricted access');

class CFieldsFile
{
	function getFieldHTML( $field , $required )
	{
		JLoader::import( 'editfield', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS . 'models' );

		$Itemid = JRequest::getInt('Itemid');
		
		// If maximum is not set, we define it to a default
		$field->max	= empty( $field->max ) ? 200 : $field->max;

		$class	= ($field->required == 1) ? ' required' : '';
		CMFactory::load( 'helpers' , 'string' );

		$model = JModelLegacy::getInstance('editfield', 'SeminarmanModel');
		$model->setId($field->id);
		$files = $model->getFiles();
		
		$html = '';
		
		$n = count($files);
		$i = 0;
		if ($n != 0){
		    $html .= '
				<div class="filelist">';
		        foreach ($files as $file){
		       		$html .= '
		        	<strong><a href="' . JRoute::_('index.php?option=com_seminarman&fileid=' . $file->id . '&task=download' . '&Itemid=' . $Itemid) . '">' . ($file->altname ? $file->altname : $file->filename) . '</a></strong>';
		        
			        $i++;
			        if ($i != $n)
			        	$html .= ', ';
		        }
		    $html .= '
		    	</div>';
		}
		
		$html .= '<span id="errfield' . $field->id . 'msg" style="display:none;">&nbsp;</span>';

		return $html;
	}

	function isValid( $value , $required )
	{
		if( $required && empty($value))
		{
			return false;
		}
		return true;
	}
}