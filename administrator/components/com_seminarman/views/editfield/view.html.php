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

class SeminarmanViewEditfield extends JViewLegacy
{

    function display($tpl = null)
    {
        $mainframe = JFactory::getApplication();

        $document = JFactory::getDocument();
        $user = JFactory::getUser();
        $lang = JFactory::getLanguage();
    	JHTML::_('behavior.tooltip');
    	JHTML::_('behavior.modal', 'a.modal');

        $cid = JRequest::getVar('cid');
        
        $files = $this->get('Files');

        $js = "
        		function qfSelectFile(id, file) {
        			var i = 'a_id' + id;
        			if(!document.getElementById(i)){
	        			var name = 'a_name'+id;
	        			var li = document.createElement('li');
	        			var txt = document.createElement('input');
	        			var hid = document.createElement('input');
        				var hf = document.getElementById('files');
	        			var clrdiv = document.createElement('div');
	        			clrdiv.setAttribute('class','clr');
	        			txt.setAttribute('size','50');
	        			var filelist = document.getElementById('filelist').getElementsByTagName('ul')[0];
	        			var button = document.createElement('input');
	        			button.type = 'button';
	        			button.name = 'removebutton_'+id;
	        			button.id = 'removebutton_'+id;
	        			$(button).addEvent('click', function() { qfRemoveFile( id ) });
	        			button.value = '" . JText::_('COM_SEMINARMAN_REMOVE') . "';
	        			txt.type = 'text';
	        			txt.disabled = 'disabled';
	        			txt.id	= name;
	        			txt.value	= file;
	        			hid.type = 'hidden';
	        			hid.name = 'fid[]';
	        			hid.value = id;
	        			hid.id = i;
	        			if(hf.value != '')
	        				hf.value += '\\r\\n';
	        			hf.value += id;
	        			filelist.appendChild(li);
	        			li.appendChild(txt);
	        			li.appendChild(button);
	        			li.appendChild(hid);
	        			filelist.appendChild(clrdiv);
	        		}
        		}
        
        		function qfRemoveFile(i) {
	        		var hf_arr = document.getElementById('files').value.split(/(?:\\r\\n|\\r|\\n)/g);
        			var id = 'a_id' + i;
	        		for(var n = 0; n < hf_arr.length; n++) {
						if(hf_arr[n] == document.getElementById(id).value) {
							hf_arr.splice(n, 1);
						}
					}
        			document.getElementById(id).parentNode.remove();
	        		document.getElementById('files').value = hf_arr.join('\\r\\n');
        		}";
        
        $document->addScriptDeclaration($js);
        
        $i = 0;
        $fileselect = '<ul class="adminformlist">';
        $filesId = '';
        
        if ($files)
        {
        	foreach ($files as $file)
        	{
        		if($filesId != '')
        			$filesId .= "\r\n";
        		$filesId .= $file->id;
        		$fileselect .= '<li><input style="background: #ffffff;" type="text" id="a_name' . $i . '" value="' . $file->filename . '" disabled="disabled" size="50" />';
        		$fileselect .= '<input type="hidden" id="a_id' . $i . '" name="fid[]" value="' . $file->id . '" />';
        		$fileselect .= '<input class="inputbox" type="button" onclick="qfRemoveFile(' . $i . ');" value="' . JText::_('COM_SEMINARMAN_REMOVE') . '" /></li>';
        		$fileselect .= '<div class="clr"></div>';
        		$i++;
        	}
        }
        $fileselect .= '</ul>
					<input type="hidden" id="files" name="files" value="' . $filesId . '" />';
        
        $linkfsel = 'index.php?option=com_seminarman&amp;view=fileselement&amp;tmpl=component&amp;index=' . $i;
        
        $document->addStyleSheet('components/com_seminarman/assets/css/seminarmanbackend.css');
        $jversion = new JVersion();
        $short_version = $jversion->getShortVersion();
        if (version_compare($short_version, "3.0", 'ge')) {
        	$document->addStyleSheet('components/com_seminarman/assets/css/seminarmanbackend_j3.x.css');
        }
        if ($lang->isRTL())
        {
            $document->addStyleSheet('components/com_seminarman/assets/css/seminarmanbackend_rtl.css');
        }

        if ($cid && (JRequest::getVar('task')!='newfield') && (JRequest::getVar('task')!='newgroup'))
        {
            if (JRequest::setVar('layout')=='editgroup') {
            	JToolBarHelper::title(JText::_('COM_SEMINARMAN_EDIT_GROUP'), 'config');
            } else {
            	JToolBarHelper::title(JText::_('COM_SEMINARMAN_EDIT_CUSTOM_FIELD'), 'config');
            }

        } else
        {
        	if (JRequest::setVar('layout')=='editgroup') {
        		JToolBarHelper::title(JText::_('COM_SEMINARMAN_ADD_GROUP'), 'config');
        	} else {
        		JToolBarHelper::title(JText::_('COM_SEMINARMAN_ADD_CUSTOM_FIELD'), 'config');        		
        	}
        }
        JToolBarHelper::apply();
        JToolBarHelper::save();
        JToolBarHelper::cancel();

        $model = $this->getModel();
        $row = $this->get('Tag');

        if ($row->id)
        {
            if ($model->isCheckedOut($user->get('id')))
            {
                JError::raiseWarning('SOME_ERROR_CODE', $row->name . ' ' . JText::_('COM_SEMINARMAN_RECORD_EDITED'));
                $mainframe->redirect('index.php?option=com_seminarman&view=editfields');
            }
        }

    	$fieldGroups	= $model->getGroups();
    	$group			= $model->getFieldGroup( $row->ordering );

    	$cft = $model->getCustomfieldsTypes();
        $this->assignRef('row', $row);
    	$this->assignRef('fieldGroups', $fieldGroups);
    	$this->assignRef('group', $group);
    	$this->assignRef('customFieldTypes', $cft );
		$this->assignRef('fileselect', $fileselect);
		$this->assignRef('linkfsel', $linkfsel);

        parent::display($tpl);
    }

}

?>