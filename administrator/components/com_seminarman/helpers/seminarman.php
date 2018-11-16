<?php
/**
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011-2014 Open Source Group GmbH www.osg-gmbh.de
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

class JHTMLSeminarman
{
    static function getUserGroups($var, $default, $disabled) {
    	$db = JFactory::getDBO();
    	$query = $db->getQuery(true);
    	$query->select( 'grp.id, grp.title' )
              ->from( '`#__usergroups` AS grp' );
        $db->setQuery($query); 
        $groups = $db->loadObjectList(); 
        
        $types[] = JHtml::_('select.option', '0', '- '. JText::_('COM_SEMINARMAN_CHOOSE_PLEASE') .' -');
        foreach ($groups as $group) {
        	$types[] = JHtml::_('select.option', $group->id, JText::_($group->title));
        }
        
        if ($default == '')
            $default = '0';

        if ($disabled == 1) {
            $disabled = 'disabled';
        } else {
        	$disabled = '';
        }           

        return JHtml::_('select.genericlist', $types, $var, 'class="inputbox" size="1" ' . $disabled . '', 'value', 'text', $default);
    }
	
	static function getSelectUser($var, $default, $disabled)
    {
        $db = JFactory::getDBO();

        $option = '';
        if ($disabled == 1)
            $option = 'disabled';

        if ($default == '')
            $default = '0';

        $query = $db->getQuery(true);
        $query->select( 'id AS value, CONCAT_WS(\' / \', username, name, id ) AS text' );
        $query->from('`#__users`');
        $query->order( 'username' );
        $db->setQuery($query);
        $items = $db->loadObjectList();

        $types[] = JHtml::_('select.option', '0', '- '. JText::_('COM_SEMINARMAN_CHOOSE_PLEASE') .' -');
        foreach ($items as $item) {
            $types[] = JHtml::_('select.option', $item->value, JText::_($item->text));
        }

        if ($disabled == 1) {
            $disabled = 'disabled';
        }

        return JHtml::_('select.genericlist', $types, $var, 'class="inputbox" size="1" ' . $disabled . '', 'value', 'text', $default);
    }
    
    static function getSelectUser_modal($var, $default, $disabled) {
    	$db = JFactory::getDBO();
    	if ($default == '') $default = '0'; 
    	if ($disabled == 1) {
    		// display only the selected user info
    		$query = $db->getQuery(true);
    		$query->select( 'CONCAT_WS(\' / \', username, name, id ) AS text' );
    		$query->from('`#__users`');
    		$query->where( 'id = ' . $default );
    		$db->setQuery($query);
    		$item = $db->loadResult();
    		
    		$html = '<input class="text_area" type="text" name="'.$var.'" id="'.$var.'" size="32" maxlength="100" value="'.$item.'" disabled="disabled" />';
    		return $html;
    	} else {
    		// new app, display user select modal box
    		$field = JFormHelper::loadFieldType('User');
    		
    		$element = new SimpleXMLElement('<field />');
    		$element->addAttribute('name', $var);
    		$element->addAttribute('class', 'readonly');
    		
    		$field->setup($element, $default);
    		
    		return $field->input;
    	}
    }
    
	static function getSelectUserForTrainer($var, $default, $disabled)
    {
        $db = JFactory::getDBO();

        $option = '';
        if ($disabled == 1)
            $option = 'disabled';

        if ($default == '')
            $default = '0';

        $query = $db->getQuery(true);
        $query->select( 'id AS value, CONCAT_WS(\' / \', username, name, id ) AS text' );
        $query->from('`#__users`');
        $query->order( 'username' );
        $db->setQuery( $query );
        $items = $db->loadObjectList();

        $types[] = JHtml::_('select.option', '0', '- '. JText::_('COM_SEMINARMAN_CHOOSE_PLEASE') .' -');
        foreach ($items as $item) {
        	// only if user is neither admin nor course manager nor tutor
        	if (!(JHTMLSeminarman::user_is_admin($item->value)||JHTMLSeminarman::UserIsCourseManager($item->value)||JHTMLSeminarman::getUserTutorID($item->value)>0)){
                $types[] = JHtml::_('select.option', $item->value, JText::_($item->text));
        	}
        }

        if ($disabled == 1) {
            $disabled = 'disabled';
        }

        return JHtml::_('select.genericlist', $types, $var, 'class="inputbox" size="1" ' . $disabled . '', 'value', 'text', $default);
    }
    
    static function getJUserState($uid, $var)
    {
        $value = array();
    	// if create trainer, creating juser is ready to go.
    	if($uid == 0){
        	// return JHTML::_('select.booleanlist', $var, '', true);
        	$disabled = '';
        	$disableform = '';
        	$selected = false;
        	$loginname = '';
        	$jemail = '';
        	$juserid = 0;
        	$value['invm'] = '<span class="readonly">'.JText::_('COM_SEMINARMAN_SAVE_JOOMLA_ACC_FIRST').'</span>'; 
        } else {   	
    	    $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->select( 'u.id AS juid, u.username AS jlogin, u.email AS jemail' );
            $query->from('`#__users` AS u');
            $query->join( "LEFT", '#__seminarman_tutor AS t ON (u.id = t.user_id)' );
            $query->where( 't.id = '.$uid );
            $query->order( 'username' );
            $db->setQuery( $query );
            $item = $db->loadAssoc();

            if (empty($item)){   // tutor has no joomla account yet or his/her joomla account is deleted
            	$disabled = '';
            	$disableform = '';
            	$selected = false;
            	$loginname = '';
            	$jemail = '';
            	$juserid = 0;
            	$value['invm'] = '<span class="readonly">'.JText::_('COM_SEMINARMAN_SAVE_JOOMLA_ACC_FIRST').'</span>'; 
            }else{  // tutor has a joomla account
            	$disabled = 'disabled';
            	$disableform = 'disabled';
            	$selected = true;
            	$loginname = $item['jlogin'];
            	$jemail = $item['jemail'];
            	$juserid = $item['juid'];
	            // Let's check if the virtuemart component exists.
                // jimport('joomla.application.component.helper');
                // $component = JComponentHelper::getComponent('com_virtuemart', true);
                // if (!($component->enabled)) {
                if (!(SeminarmanFunctions::isVMEnabled())) {
      	            $value['invm'] = '<span class="readonly">VirtueMart is either not installed or not enabled</span>';
                } else {
                	$params = JComponentHelper::getParams('com_seminarman');
                    if ($params->get('trigger_virtuemart') == 1) {
                    	$query = $db->getQuery(true);
                    	$query->select( '*' );
                    	$query->from('`#__virtuemart_vmusers` AS v');
                    	$query->where( 'v.virtuemart_user_id = ' . $juserid );
                    	$query->where( '(v.perms = "storeadmin" OR v.perms = "admin")' );
                    	$db->setQuery( $query );
                    	$item_vmuser = $db->loadAssoc();
                    	
                    	if(empty($item_vmuser)){
                    		$value['invm'] = JHTML::_('select.booleanlist', 'invm', '', 0);
                    	} else {
                    		$value['invm'] = JHTML::_('select.booleanlist', 'invm', 'disabled', 1);
                    	}
                    } else {
                        $value['invm'] = '<span class="readonly">VMEngine is not enabled</span>';
                    }
                }
            }        
        }
        $value['selection'] = JHTML::_('select.booleanlist', $var, $disabled . ' onclick="updatejuserform()"', $selected);
        $value['username'] = '<input class="inputbox required" type="text" name="user_name" size="30" value="' . $loginname . '" ' . $disableform . ' />';
        $value['password1'] = '<input class="inputbox required" type="password" name="jpassword1" size="30" ' . $disableform . ' />';
        $value['password2'] = '<input class="inputbox required" type="password" name="jpassword2" size="30" ' . $disableform . ' />';
        $value['email'] = '<input class="inputbox required" type="email" name="jemail" size="30" value="' . $jemail . '" ' . $disableform . ' />';
        $value['userid'] = '<input type="hidden" name="user_id" value="' . $juserid . '" />';
        
        $method[] = JHtml::_('select.option', '0', JText::_('COM_SEMINARMAN_CREATE_NEW_JOOMLA_ACC'));
        $method[] = JHtml::_('select.option', '1', JText::_('COM_SEMINARMAN_SELECT_JOOMLA_ACC'));
        $value['method'] = JHtml::_('select.genericlist', $method, 'juser_option', 'class="inputbox" size="1" ' . $disabled . ' onchange="updatejuserform()"', 'value', 'text', '0');
        
        return $value;
    }
    

    static function getSelectCountry($var, $default, $disabled)
    {
        $db = JFactory::getDBO();

        $option = '';
        if ($disabled == 1)
            $option = 'disabled';

        if ($default == '')
            $default = '0';

        $query = $db->getQuery(true);
        $query->select( 'id AS value, title AS text' );
        $query->from( '`#__seminarman_country`');
        $query->where( 'published=1' );
        $query->order( 'title' );
        $db->setQuery( $query );
        $items = $db->loadObjectList();

        $types[] = JHtml::_('select.option', '0', '- '. JText::_('COM_SEMINARMAN_CHOOSE_PLEASE') .' -');
        foreach ($items as $item) {
            $types[] = JHtml::_('select.option', $item->value, JText::_($item->text));
        }

        return JHtml::_('select.genericlist', $types, $var, 'class="inputbox" size="1" ' . $option . '', 'value', 'text', $default);
    }
    

	static function getSelectExperienceLevel($var, $default, $disabled = '')
	{
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);
		$query->select( 'id AS value, title AS text' );
		$query->from( '#__seminarman_experience_level' );
		$db->setQuery( $query );
		$items = $db->loadObjectList();

		$types[] = JHtml::_('select.option', '0', '- '. JText::_('COM_SEMINARMAN_NOVALUE') .' -');
		foreach ($items as $item)  {
			$types[] = JHtml::_('select.option', $item->value, JText::_($item->text));
		}

		return JHtml::_('select.genericlist', $types, $var, 'class="inputbox" size="1" ' . $disabled, 'value', 'text', $default);
	}
	

	static function getSelectATGroup($var, $default, $disabled)
	{
		$db = JFactory::getDBO();

		$option = '';
		if ($disabled == 1)
			$option = 'disabled';

		$query = $db->getQuery(true);
		$query->select( 'id AS value, title AS text' );
		$query->from( '#__seminarman_atgroup' );
		$db->setQuery( $query );
		$items = $db->loadObjectList();

		$types[] = JHtml::_('select.option', '0', '- '. JText::_('COM_SEMINARMAN_NOVALUE') .' -');
		foreach ($items as $item)  {
			$types[] = JHtml::_('select.option', $item->value, JText::_($item->text));
		}

		return JHtml::_('select.genericlist', $types, $var, 'class="inputbox" size="1" ' . $option . '', 'value', 'text', $default);
	}
	

    static function getSelectCompType($var, $default, $disabled)
    {
        $db = JFactory::getDBO();

        $option = '';
        if ($disabled == 1)
            $option = 'disabled';
        
        $query = $db->getQuery(true);
        $query->select( 'id AS value, title AS text' );
        $query->from( '`#__seminarman_company_type`');
        $query->order( 'ordering' );
        $db->setQuery( $query );
        $items = $db->loadObjectList();

        $types[] = JHtml::_('select.option', '0', '- '. JText::_('COM_SEMINARMAN_CHOOSE_PLEASE') .' -');
        foreach ($items as $item) {
            $types[] = JHtml::_('select.option', $item->value, JText::_($item->text));
        }

        return JHtml::_('select.genericlist', $types, $var, 'class="inputbox" size="1" ' . $option . '', 'value', 'text', $default);
    }

   
    static function getSelectTutor($var, $default = 0, $templateId = 0)
    {
    	$db = JFactory::getDBO();

    	if ( empty($templateId ) )
    	{
    		// Kurs wurde ohne Template angelegt oder Template existiert nicht mehr
    		// -> alle Trainer auflisten
    		$query = $db->getQuery(true);
    		$query->select( 'id AS value, CONCAT(title, CONCAT(\' (\', id, \')\')) AS text' );
    		$query->from( '#__seminarman_tutor' );
    		$query->order( 'title' );
    		$db->setQuery($query);
    		
    		$types[] = JHtml::_('select.option', '0', '- ' . JText::_('COM_SEMINARMAN_CHOOSE_PLEASE') . ' -');
    		foreach ($db->loadObjectList() as $tutor)
    			$types[] = JHtml::_('select.option', $tutor->value, JText::_($tutor->text));
    	}
    	else
    	{
    		// Kurs kommt von Template
    		// -> geeignete Trainer oben auflisten und nach priority sortieren, darunter alle übrigen
    		$query = $db->getQuery(true);
    		$query->select( 'distinct t.id AS value, CONCAT(t.title, CONCAT(\' (id: \', t.id, \', prio: \', rel.priority, \')\')) AS text' );
    		$query->from( '#__seminarman_tutor AS t' );
			$query->join( "LEFT", "#__seminarman_tutor_templates_relations AS rel ON rel.tutorid = t.id");
    		$query->where( 'rel.templateid = ' . (int)$templateId );
    		$query->order( 'rel.priority DESC' );
			
    		$db->setQuery($query);
    		$tutors = $db->loadObjectList();

    		if (empty($tutors)) {
    			$types[] = JHtml::_('select.option', 0, '- '.  JText::_('COM_SEMINARMAN_NO_QUALIFIED_TUTORS') .' -');
    		} else {
    			$types[] = JHtml::_('select.option', 0, '- '.  JText::_('COM_SEMINARMAN_QUALIFIED_TUTORS') .' -');
    			foreach ( $tutors as $tutor ) {
    				$types[] = JHtml::_('select.option', $tutor->value, JText::_($tutor->text));
    			}
    		}

    		// übrige Trainer
    		$query = $db->getQuery(true);
    		
    		$query->select( 'distinct t.id AS value, CONCAT(t.title, CONCAT(\' (id: \', t.id, \')\')) AS text' );
    		$query->from( '#__seminarman_tutor AS t' );
    		$query->where( 'id NOT IN ('.
    						' SELECT DISTINCT t.id FROM #__seminarman_tutor AS t'.
    						' LEFT JOIN #__seminarman_tutor_templates_relations AS rel '.
    						' ON rel.tutorid = t.id'.
    						' WHERE rel.templateid = ' . (int)$templateId .')' );
    		$db->setQuery($query);
    		$tutors = $db->loadObjectList();
    		if (!empty($tutors)) {
    			$types[] = JHtml::_('select.option', 0, '- '.  JText::_('COM_SEMINARMAN_REMAINING_TUTORS') .' -');
    			foreach( $tutors as $tutor )
    			$types[] = JHtml::_('select.option', $tutor->value, JText::_($tutor->text));
    		}
    	}

    	return JHtml::_('select.genericlist', $types, $var, 'class="inputbox" size="8" multiple="multiple"', 'value', 'text', $default);
    }
    
    
    static function getSelectTemplate($var, $maxlength = 80)
    {
    	$db = JFactory::getDBO();
    	$query = $db->getQuery(true);
    	$query->select( 'id, CONCAT(IF(LENGTH(`name`) > '.$maxlength.', CONCAT(LEFT(`name`, '.($maxlength - 3).'), "..."), `name`), \' (\', id, \')\') AS text' );
    	$query->from( '`#__seminarman_templates`');
    	$query->order( 'name' );
    	$db->setQuery( $query );
    
    	$types[] = JHtml::_('select.option', '0', '- '. JText::_('COM_SEMINARMAN_CHOOSE_PLEASE') .' -');
    	foreach ($db->loadObjectList() as $template) {
    		$types[] = JHtml::_('select.option', $template->id, JText::_($template->text));
    	}
    
    	return JHtml::_('select.genericlist', $types, $var, 'class="inputbox" size="1" ', 'value', 'text', 0);
    }
    
    
    static function getVirtualTable()
    {
       $jversion = new JVersion();
       $short_version = $jversion->getShortVersion();
       if (version_compare($short_version, "3.0", 'ge')) {
    	  $pathToXML_File = JPATH_COMPONENT_ADMINISTRATOR.DS.'tables'.DS.'virtual_tables.xml';
    	  $xml=JFactory::getXML($pathToXML_File);    	
    	  $tables = array();
    	  foreach($xml->table as $table) {
    	    $tables[] = $table;
    	  }    	
    	  return $tables;
       } else {
       	  $parser = JFactory::getXMLParser('Simple');
       	  $pathToXML_File = JPATH_COMPONENT_ADMINISTRATOR.DS.'tables'.DS.'virtual_tables.xml';
       	  $parser->loadFile($pathToXML_File);
       	  $document = &$parser->document;
       	  $result =  &$document->table;
       	  return $result;
       }
    }

    
    static function getTableFromXML($tableTitle)
    {
        $tables = JHTMLSeminarman::getVirtualTable();
        $jversion = new JVersion();
        $short_version = $jversion->getShortVersion();
        for ($i = 0, $c = count($tables); $i < $c; $i++)
        {
        	if (version_compare($short_version, "3.0", 'ge')) {
        		$album = $tables[$i];
            	$name = $album->title;
            	if ($name == $tableTitle) {
            		if ($values = $album->values) {
                    	$listing = array();
                    	foreach($values->value as $wert) {
                    		$listing[]=$wert;
                    	}
                    	for ($ti = 0, $tc = count($listing); $ti < $tc; $ti++) {
                        	$value = &$listing[$ti];
                        	$XMLvalue[$ti] = $value;
                    	}
                	}
                	return $XMLvalue;
            	}
			} else {
				$album = $tables[$i];
				$name = $album->getElementByPath('title');
				if ($name->data() == $tableTitle) {
					if ($values = $album->getElementByPath('values')) {
						$listing = $values->value;
						for ($ti = 0, $tc = count($listing); $ti < $tc; $ti++) {
							$value = &$listing[$ti];
							$XMLvalue[$ti] = $value->data();
						}
					}
					return $XMLvalue;
				}
			}
        }
    }

    
    static function getListFromXML($tableTitle, $db_field, $disabled, $default)
    {
        $values = JHTMLSeminarman::getTableFromXML($tableTitle);

        $option = '';
        if ($disabled == 1)
            $option = 'disabled';

        $options[] = JHtml::_('select.option', '', '- ' . JText::_('COM_SEMINARMAN_CHOOSE_PLEASE') . ' -');
        foreach ($values as $course)  {
            $options[] = JHtml::_('select.option', $course, JText::_($course));
        }

        return JHtml::_('select.genericlist', $options, $db_field, 'class="inputbox" size="1"'. $option, 'value', 'text', $default);
    }
    
    static function getStatusListForApplicationViews( $selected = 0, $stuff = 'class="inputbox" size="1"' ) {
    	$statuslist[] = JHTML::_('select.option',  '0', JText::_( 'COM_SEMINARMAN_SUBMITTED' ), 'value', 'text' );
    	$statuslist[] = JHTML::_('select.option',  '1', JText::_( 'COM_SEMINARMAN_PENDING' ), 'value', 'text' );
    	$statuslist[] = JHTML::_('select.option',  '2', JText::_( 'COM_SEMINARMAN_PAID' ), 'value', 'text' );
    	$statuslist[] = JHTML::_('select.option',  '3', JText::_( 'COM_SEMINARMAN_CANCELED' ), 'value', 'text' );
    	$statuslist[] = JHTML::_('select.option',  '4', JText::_( 'COM_SEMINARMAN_WL' ), 'value', 'text' );
    	$statuslist[] = JHTML::_('select.option',  '5', JText::_( 'COM_SEMINARMAN_AWAITING_RESPONSE' ), 'value', 'text' );
    	if ( $selected )
    		return JHTML::_('select.genericlist', $statuslist, 'status', $stuff,'value', 'text', $selected );
    	
    	return JHTML::_('select.genericlist', $statuslist, 'status', $stuff,'value', 'text' );
    }


    static function getSelectEmailTemplate($var, $default = '', $disabled = '', $default_txt = null)
    {
    	$db = JFactory::getDBO();

    	$query = $db->getQuery(true);
    	$query->select( 'id AS value, title AS text' );
    	$query->from( '#__seminarman_emailtemplate' );
    	$query->where( 'templatefor=0' );
    	$query->order( 'id' );
    	$db->setQuery($query);
    	$templates = $db->loadObjectList();
    
    	$types[] = JHtml::_('select.option', '0', '- '. (isset($default_txt) ? $default_txt : JText::_('COM_SEMINARMAN_DEFAULT')) .' -');
    	foreach ($templates as $template) {
    		$types[] = JHtml::_('select.option', $template->value, JText::_($template->text));
    	}
    
    	return JHtml::_('select.genericlist', $types, $var, 'class="inputbox" size="1" ' . $disabled, 'value', 'text', $default);
    }
    
    
    static function getSelectPdfTemplate($var, $default = '', $templatefor=0, $disabled = '')
    {
    	$db = JFactory::getDBO();

    	$query = $db->getQuery(true);
    	$query->select( 'id AS value, name AS text' );
    	$query->from( '#__seminarman_pdftemplate' );
    	$query->where( 'templatefor='.(int)$templatefor );
    	$query->order( 'id' );
    	$db->setQuery($query);
    	$templates = $db->loadObjectList();
    
    	$types[] = JHtml::_('select.option', '0', '- '. JText::_('COM_SEMINARMAN_DEFAULT') .' -');
    	foreach ($templates as $template) {
    		$types[] = JHtml::_('select.option', $template->value, JText::_($template->text));
    	}
    
    	return JHtml::_('select.genericlist', $types, $var, 'class="inputbox" size="1" ' . $disabled, 'value', 'text', $default);
    }

    static function getWaitingListEmailTemplate()
    {
    	$db = JFactory::getDBO();
    	
    	$query = $db->getQuery(true);
    	$query->select( 'id' );
    	$query->from( '#__seminarman_emailtemplate' );
    	$query->where( 'templatefor=2' );
    	$query->where( 'isdefault=1' );
    	$db->setQuery($query);
    	$templates = $db->loadObjectList();
    
    	return $templates[0]->id;
    }

    static function sendEmailToUserApplication($emaildata, $msgSubject, $msgBody, $msgSender, $msgRecipient, $msgRecipientCC = '', $msgRecipientBCC = '', $attachment = '')
    {
    	if (empty($msgRecipient))
    		return False;
    	 
    	$mainframe = JFactory::getApplication();
    	$db = JFactory::getDBO();
    	$message = JFactory::getMailer();
    	
    	$params = JComponentHelper::getParams('com_seminarman');
    	
    	$query = $db->getQuery(true);
    	$query->select( 'app.*, NOW() AS `current_date`, c.reference_number, c.title AS course, c.price AS course_price_orig, c.code, c.introtext, c.fulltext, c.capacity, c.location, c.url, c.id AS course_id, c.tutor_id AS course_tutor_ids, c.attribs AS course_attribs, app.id AS application_id, app.price_per_attendee, app.price_total, app.price_vat, gr.title AS atgroup, gr.description AS atgroup_desc, ex.title AS experience_level, ex.description AS experience_level_desc' );
    	$query->from( '#__seminarman_application AS app' );
    	$query->join( "LEFT", '#__seminarman_courses AS c ON c.id = app.course_id' );
    	$query->join( "LEFT", '#__seminarman_atgroup AS gr ON gr.id = c.id_group' );
    	$query->join( "LEFT", '#__seminarman_experience_level AS ex ON ex.id = c.id_experience_level' );
    	$query->where( 'app.id = ' . $emaildata['applicationid'] );    
    	$db->setQuery($query);
    	$queryResult = $db->loadObject();
    	
    	if ($queryResult) {
    		// what is the below for? not my codes
    		//$userQuery = "SELECT name, email FROM " . $db->quoteName('#__users') . "
			//WHERE id = " . $emaildata['user_id'];
    
    		//$db->setQuery($userQuery);
    		//$user = $db->loadObject();
    
    		// what is this (below)? not my codes
    		//$tutorQuery = "SELECT name, email FROM " . $db->quoteName('#__users') . "
			//WHERE id = " . $queryResult->user_id;
    
    		//$db->setQuery($tutorQuery);
    		//$tutor = $db->loadObject();
    		// what is above?
    
    		$current_date = JFactory::getDate($queryResult->current_date)->format(JText::_('COM_SEMINARMAN_DATE_FORMAT1'));
    		
    		// parameters for multiple tutors
    		$course_tutors_id_array = (array)json_decode($queryResult->course_tutor_ids, true);
    		$course_first_tutor_id = $course_tutors_id_array[0];
    		$course_tutors = array();
    		foreach ($course_tutors_id_array as $course_tutors_id) {
    			$query = $db->getQuery(true);
    			$query->select( 'CONCAT_WS(\' \', emp.salutation, emp.other_title, emp.firstname, emp.lastname) AS tutor_combiname, CONCAT_WS(\' \', emp.firstname, emp.lastname) AS tutor_fullname, emp.title AS tutor_displayname, emp.firstname AS tutor_firstname, emp.lastname AS tutor_lastname, emp.salutation AS tutor_salutation, emp.other_title AS tutor_title, emp.email AS tutor_email' );
    			$query->from( '#__seminarman_tutor AS emp' );
    			$query->where( 'emp.id = ' . $course_tutors_id );
    			$db->setQuery($query);
    			$ergebnis = $db->loadAssoc();
    			
    			$course_tutors[$course_tutors_id] = $ergebnis;
    		}
    		$queryResult->course_all_tutors = '';
    		$queryResult->course_all_tutors_fullname = '';
    		$queryResult->course_all_tutors_combiname = '';
    		$queryResult->tutor_recipients = '';
    		$printComma = false;
    		foreach ($course_tutors as $tutor_key => $tutor_content) {
    			$tutor_email = trim($tutor_content['tutor_email']);
    			if ($printComma) {
    				$queryResult->course_all_tutors = $queryResult->course_all_tutors . ', ';
    				$queryResult->course_all_tutors_fullname = $queryResult->course_all_tutors_fullname . ', ';
    				$queryResult->course_all_tutors_combiname = $queryResult->course_all_tutors_combiname . ', ';
    				if (!empty($tutor_email)) $queryResult->tutor_recipients = $queryResult->tutor_recipients . ', ';
    			}
    			$queryResult->course_all_tutors = $queryResult->course_all_tutors . $tutor_content['tutor_displayname'];
    			$queryResult->course_all_tutors_fullname = $queryResult->course_all_tutors_fullname . $tutor_content['tutor_fullname'];
    			$queryResult->course_all_tutors_combiname = $queryResult->course_all_tutors_combiname . $tutor_content['tutor_combiname'];
    			if (!empty($tutor_email)) $queryResult->tutor_recipients = $queryResult->tutor_recipients . $tutor_email;
    			$printComma = true;
    		}
    		
    		// parameters for the first tutor
    		$queryResult->tutor = $course_tutors[$course_first_tutor_id]['tutor_displayname'];
    		$queryResult->tutor_first_name = $course_tutors[$course_first_tutor_id]['tutor_firstname'];
    		$queryResult->tutor_last_name = $course_tutors[$course_first_tutor_id]['tutor_lastname'];
    		$queryResult->tutor_salutation = $course_tutors[$course_first_tutor_id]['tutor_salutation'];
    		$queryResult->tutor_other_title = $course_tutors[$course_first_tutor_id]['tutor_title'];
    		   		
    		// tutor custom fields for the first tutor
    		$query = $db->getQuery(true);
    		$query->select( 'f.fieldcode, ct.value' );
    		$query->from( '#__seminarman_fields_values_tutors AS ct' );
    		$query->join( "LEFT", '#__seminarman_fields AS f ON ct.field_id = f.id' );
    		$query->where( 'ct.tutor_id = '. $course_first_tutor_id );
    		$query->where( 'f.published = ' . $db->Quote('1') );
    		$db->setQuery($query);
    		$tutor_customs = $db->loadAssocList();
    
    		for ($i = 0; $i < count($tutor_customs); $i++) {
    			$msgSubject = str_replace('{' . strtoupper($tutor_customs[$i]['fieldcode']) . '}', $tutor_customs[$i]['value'],
    					$msgSubject);
    			$msgBody = str_replace('{' . strtoupper($tutor_customs[$i]['fieldcode']) . '}', $tutor_customs[$i]['value'],
    					$msgBody);
    		}
    		
    		// course custom fields
    		$course_attribs = new JRegistry();
    		$course_attribs->loadString($queryResult->course_attribs);
    		$custom_fld_1_value = $course_attribs->get('custom_fld_1_value');
    		$custom_fld_2_value = $course_attribs->get('custom_fld_2_value');
    		$custom_fld_3_value = $course_attribs->get('custom_fld_3_value');
    		$custom_fld_4_value = $course_attribs->get('custom_fld_4_value');
    		$custom_fld_5_value = $course_attribs->get('custom_fld_5_value');
    		$queryResult->custom_fld_1_value = (!empty($custom_fld_1_value))?$custom_fld_1_value:'';
    		$queryResult->custom_fld_2_value = (!empty($custom_fld_2_value))?$custom_fld_2_value:'';
    		$queryResult->custom_fld_3_value = (!empty($custom_fld_3_value))?$custom_fld_3_value:'';
    		$queryResult->custom_fld_4_value = (!empty($custom_fld_4_value))?$custom_fld_4_value:'';
    		$queryResult->custom_fld_5_value = (!empty($custom_fld_5_value))?$custom_fld_5_value:'';
    
    		// app custom fields (it MUST be here after tutor customs, otherwise you have problems; i don't think the below codes are well done, but it works. they are not my codes.)
    		$query = $db->getQuery(true);
    		$query->select( 'field.*, value.value' );
    		$query->from( '#__seminarman_fields AS field' );
    		$query->join( "LEFT", '#__seminarman_fields_values AS value ON field.id=value.field_id AND value.applicationid=' . $emaildata['applicationid'] );
    		$query->where( 'field.published=' . $db->Quote('1') );
    		$query->order( 'field.ordering' );
    		$db->setQuery( $query );
    		$fields = $db->loadAssocList();
    
    		for ($i = 0; $i < count($fields); $i++) {
    			$msgSubject = str_replace('{' . strtoupper($fields[$i]['fieldcode']) . '}', $fields[$i]['value'],
    					$msgSubject);
    			$msgBody = str_replace('{' . strtoupper($fields[$i]['fieldcode']) . '}', $fields[$i]['value'],
    					$msgBody);
    		}
    
    		// calculate and format price
    		$lang = JFactory::getLanguage();
    
    		$price_orig = $queryResult->course_price_orig;
    		$price_booking = $queryResult->price_per_attendee;
    		$quantity = $queryResult->attendees;
    		$tax_rate = $queryResult->price_vat / 100.0;
    		$price_total_orig = $price_orig * $quantity;
    		$price_total_booking = $queryResult->price_total;
    		$price_orig_with_tax = $price_orig * (1 + $tax_rate);
    		$price_booking_with_tax = $price_booking * (1 + $tax_rate);
    		$price_total_orig_with_tax = $price_total_orig * (1 + $tax_rate);
    		$price_total_booking_with_tax = $price_total_booking * (1 + $tax_rate);
    		$tax_orig = $price_total_orig * $tax_rate;
    		$tax_booking = $price_total_booking * $tax_rate;
    
    		$old_locale = setlocale(LC_NUMERIC, NULL);
    		setlocale(LC_NUMERIC, $lang->getLocale());
    		// if (doubleval($price_total_orig) == doubleval($price_total_booking)) {
    		//    $queryResult->price_per_attendee = JText::sprintf('%.2f', $price_booking);
    		//    $queryResult->price_total = JText::sprintf('%.2f', $price_total_booking);
    		//    $queryResult->price_vat_percent = $queryResult->price_vat;
    		//    $queryResult->price_per_attendee_vat = JText::sprintf('%.2f', $price_booking_with_tax);
    		//    $queryResult->price_total_vat = JText::sprintf('%.2f', $price_total_booking_with_tax);
    		//    $queryResult->price_vat = JText::sprintf('%.2f', $tax_booking);
    		// } else {
    		//    $queryResult->price_per_attendee = '<s>' . JText::sprintf('%.2f', $price_orig) . '</s> ' . JText::sprintf('%.2f', $price_booking);
    		//    $queryResult->price_total = '<s>' . JText::sprintf('%.2f', $price_total_orig) . '</s> ' . JText::sprintf('%.2f', $price_total_booking);
    		//    $queryResult->price_vat_percent = $queryResult->price_vat;
    		//    $queryResult->price_per_attendee_vat = '<s>' . JText::sprintf('%.2f', $price_orig_with_tax) . '</s> ' . JText::sprintf('%.2f', $price_booking_with_tax);
    		//    $queryResult->price_total_vat = '<s>' . JText::sprintf('%.2f', $price_total_orig_with_tax) . '</s> ' . JText::sprintf('%.2f', $price_total_booking_with_tax);
    		//    $queryResult->price_vat = JText::sprintf('%.2f', $tax_booking);
    		// }
    		// $queryResult->price_vat_percent = $queryResult->price_vat;
    		// $queryResult->price_per_attendee_vat = JText::sprintf('%.2f', (($queryResult->price_per_attendee / 100.0) * $queryResult->price_vat) + $queryResult->price_per_attendee);
    		// $queryResult->price_total_vat = JText::sprintf('%.2f', (($queryResult->price_total / 100.0) * $queryResult->price_vat) + $queryResult->price_total);
    		// $queryResult->price_vat = JText::sprintf('%.2f', ($queryResult->price_total / 100.0) * $queryResult->price_vat_percent);
    		// $queryResult->price_per_attendee = JText::sprintf('%.2f', $queryResult->price_per_attendee);
    		// $queryResult->price_total = JText::sprintf('%.2f', $queryResult->price_total);
    
    		$queryResult->price_per_attendee = JText::sprintf('%.2f', round($price_orig, 2));
    		$queryResult->price_total = JText::sprintf('%.2f', round($price_total_orig,2));
    		$queryResult->price_vat_percent = $queryResult->price_vat;
    		$queryResult->price_per_attendee_vat = JText::sprintf('%.2f', round($price_orig_with_tax, 2));
    		$queryResult->price_total_vat = JText::sprintf('%.2f', round($price_total_booking_with_tax, 2));
    		$queryResult->price_vat = JText::sprintf('%.2f', round($tax_booking, 2));
    		$queryResult->price_total_discount = JText::sprintf('%.2f', round(($price_total_booking - $price_total_orig), 2));
    		$queryResult->price_total_orig_vat = JText::sprintf('%.2f', round($price_total_orig_with_tax, 2));
    
    		$queryResult->price_booking_single = JText::sprintf('%.2f', round($price_booking, 2));
    		$queryResult->price_booking_total = JText::sprintf('%.2f', round($price_total_booking, 2));
    
    		setlocale(LC_NUMERIC, $old_locale);
    		
    		// compute loaded date time (utc) to local date time
    		$course_start_arr = SeminarmanFunctions::formatUTCtoLocal($emaildata['start_date'], $emaildata['start_time']);
    		$course_finish_arr = SeminarmanFunctions::formatUTCtoLocal($emaildata['finish_date'], $emaildata['finish_time']);
    		$course_start_date = $course_start_arr[0];  // local
    	    $course_start_time = $course_start_arr[1];  // local
    	    $course_finish_date = $course_finish_arr[0];  // local
    	    $course_finish_time = $course_finish_arr[1];  // local	
    
    		// start weekday
    		$langs = JComponentHelper::getParams('com_languages');
    		$selectedLang = $langs->get('site', 'en-GB');
    		if ($selectedLang == "de-DE") {
    			$trans = array(
    					'Monday'    => 'Montag',
    					'Tuesday'   => 'Dienstag',
    					'Wednesday' => 'Mittwoch',
    					'Thursday'  => 'Donnerstag',
    					'Friday'    => 'Freitag',
    					'Saturday'  => 'Samstag',
    					'Sunday'    => 'Sonntag',
    					'Mon'       => 'Mo',
    					'Tue'       => 'Di',
    					'Wed'       => 'Mi',
    					'Thu'       => 'Do',
    					'Fri'       => 'Fr',
    					'Sat'       => 'Sa',
    					'Sun'       => 'So',
    					'January'   => 'Januar',
    					'February'  => 'Februar',
    					'March'     => 'März',
    					'May'       => 'Mai',
    					'June'      => 'Juni',
    					'July'      => 'Juli',
    					'October'   => 'Oktober',
    					'December'  => 'Dezember'
    			);
    			$COURSE_START_WEEKDAY = (!empty($emaildata['start_date'])) ? strtr(date('l', strtotime($course_start_date)), $trans) : '';
    		} else {
    			$COURSE_START_WEEKDAY = (!empty($emaildata['start_date'])) ? date('l', strtotime($course_start_date)) : '';
    		}
    
    		// first session infos
    		$query = $db->getQuery(true);
    		$query->select( '*' );
    		$query->from( '#__seminarman_sessions' );
    		$query->where( 'published = 1' );
    		$query->where( 'courseid = ' . $queryResult->course_id );
    		$query->order( 'session_date' );
    		$db->setQuery($query);
    		$course_sessions = $db->loadObjectList();
    
    		if(!empty($course_sessions)){
    			// compute loaded date time (utc) to local date time
    			$session_start_arr = SeminarmanFunctions::formatUTCtoLocal($course_sessions[0]->session_date, $course_sessions[0]->start_time);
    			$session_finish_arr = SeminarmanFunctions::formatUTCtoLocal($course_sessions[0]->session_date, $course_sessions[0]->finish_time);
    			
    			$COURSE_FIRST_SESSION_TITLE = $course_sessions[0]->title;
    			$COURSE_FIRST_SESSION_CLOCK = date('H:i', strtotime($session_start_arr[1])) . ' - ' . date('H:i', strtotime($session_finish_arr[1]));
    			$COURSE_FIRST_SESSION_DURATION = $course_sessions[0]->duration;
    			$COURSE_FIRST_SESSION_ROOM = $course_sessions[0]->session_location;
    			$COURSE_FIRST_SESSION_COMMENT = $course_sessions[0]->description;
    		} else {
    			$COURSE_FIRST_SESSION_TITLE = '';
    			$COURSE_FIRST_SESSION_CLOCK = '';
    			$COURSE_FIRST_SESSION_DURATION = '';
    			$COURSE_FIRST_SESSION_ROOM = '';
    			$COURSE_FIRST_SESSION_COMMENT = '';
    		}
    
    		if (!empty( $queryResult->title )) $queryResult->title .= ' ';
    		
    		// something for payment fee
    		$app_params_obj = new JRegistry();
    		$app_params_obj->loadString($queryResult->params);
    		$app_params = $app_params_obj->toArray();
    		 
    		if (isset($app_params['payment_method'])) {
    			if ($app_params['payment_method'] == 1) {
    				$payment_method_lbl = JText::_('COM_SEMINARMAN_BANK_TRANSFER');
    			} elseif ($app_params['payment_method'] == 2) {
    				$payment_method_lbl = JText::_('COM_SEMINARMAN_PAYPAL');
    			}
    			$payment_fee = doubleval(str_replace(",", ".", $app_params['payment_fee']));
    			$payment_fee_lbl = JText::sprintf('%.2f', $payment_fee);
    		} else {
    			$payment_method_lbl = "";
    			$payment_fee_lbl = "";
    			$payment_fee = 0;
    		}
    		
    		// confirm email cc
    		if (isset($app_params['booking_email_cc'])) {
    			$booking_email_cc = $app_params['booking_email_cc'];
    		} else {
    			$booking_email_cc = '';
    		}
    		
    		// extra fees coming from plugin
    		$dispatcher=JDispatcher::getInstance();
    		JPluginHelper::importPlugin('seminarman');
    		$extrafees=$dispatcher->trigger('onPaypalCalc', array($queryResult));  // we need booking id, attendees etc.
    		if(isset($extrafees) && !empty($extrafees)) {
    			$payment_total = $price_total_booking_with_tax + $extrafees[0] + $payment_fee;
    		} else {
    			$payment_total = $price_total_booking_with_tax + $payment_fee;
    		}
    		$payment_total_lbl = JText::sprintf('%.2f', $payment_total);
    
    		$msgSubject = str_replace('{ADMIN_CUSTOM_RECIPIENT}', $params->get('component_email'), $msgSubject);
    		$msgSubject = str_replace('{TUTOR_RECIPENTS}', $queryResult->tutor_recipients, $msgSubject);
    		$msgSubject = str_replace('{ATTENDEES}', JHTMLSeminarman::cEscape($queryResult->attendees), $msgSubject);
    		$msgSubject = str_replace('{TITLE}', JHTMLSeminarman::cEscape($queryResult->title), $msgSubject);
    		$msgSubject = str_replace('{SALUTATION}', JHTMLSeminarman::cEscape($queryResult->salutation), $msgSubject);
    		$msgSubject = str_replace('{FIRSTNAME}', JHTMLSeminarman::cEscape($queryResult->first_name), $msgSubject);
    		$msgSubject = str_replace('{LASTNAME}', JHTMLSeminarman::cEscape($queryResult->last_name), $msgSubject);
    		$msgSubject = str_replace('{EMAIL}', JHTMLSeminarman::cEscape($queryResult->email), $msgSubject);
    		$msgSubject = str_replace('{EMAIL_CONFIRM_CC}', JHTMLSeminarman::cEscape($booking_email_cc), $msgSubject);
    		// $msgSubject = str_replace('{ATTENDEES}', $queryResult->attendees, $msgSubject);
    		$msgSubject = str_replace('{APPLICATION_ID}', JHTMLSeminarman::cEscape($queryResult->application_id), $msgSubject);
    		$msgSubject = str_replace('{COURSE_ID}', $queryResult->course_id, $msgSubject);
    		$msgSubject = str_replace('{COURSE_TITLE}', $queryResult->course, $msgSubject);
    		$msgSubject = str_replace('{COURSE_CODE}', $queryResult->code, $msgSubject);
    		$msgSubject = str_replace('{COURSE_INTROTEXT}', $queryResult->introtext, $msgSubject);
    		$msgSubject = str_replace('{COURSE_FULLTEXT}', $queryResult->fulltext, $msgSubject);
    		$msgSubject = str_replace('{COURSE_CAPACITY}', $queryResult->capacity, $msgSubject);
    		$msgSubject = str_replace('{COURSE_LOCATION}', $queryResult->location, $msgSubject);
    		$msgSubject = str_replace('{COURSE_URL}', $queryResult->url, $msgSubject);
    		$msgSubject = str_replace('{PRICE_PER_ATTENDEE}', JHTMLSeminarman::cEscape($queryResult->price_per_attendee), $msgSubject);
    		$msgSubject = str_replace('{PRICE_PER_ATTENDEE_VAT}', JHTMLSeminarman::cEscape($queryResult->price_per_attendee_vat), $msgSubject);
    		$msgSubject = str_replace('{PRICE_TOTAL}', JHTMLSeminarman::cEscape($queryResult->price_total), $msgSubject);
    		$msgSubject = str_replace('{PRICE_TOTAL_VAT}', JHTMLSeminarman::cEscape($queryResult->price_total_vat), $msgSubject);
    		$msgSubject = str_replace('{PRICE_VAT_PERCENT}', JHTMLSeminarman::cEscape($queryResult->price_vat_percent), $msgSubject);
    		$msgSubject = str_replace('{PRICE_VAT}', JHTMLSeminarman::cEscape($queryResult->price_vat), $msgSubject);
    		$msgSubject = str_replace('{PRICE_TOTAL_DISCOUNT}', JHTMLSeminarman::cEscape($queryResult->price_total_discount), $msgSubject);
    		$msgSubject = str_replace('{PRICE_TOTAL_ORIG_VAT}', JHTMLSeminarman::cEscape($queryResult->price_total_orig_vat), $msgSubject);
    		$msgSubject = str_replace('{PRICE_REAL_BOOKING_SINGLE}', JHTMLSeminarman::cEscape($queryResult->price_booking_single), $msgSubject);
    		$msgSubject = str_replace('{PRICE_REAL_BOOKING_TOTAL}', JHTMLSeminarman::cEscape($queryResult->price_booking_total), $msgSubject);
    		$msgSubject = str_replace('{PRICE_GROUP_ORDERED}', JHTMLSeminarman::cEscape($queryResult->pricegroup), $msgSubject);
    		$msgSubject = str_replace('{COURSE_START_DATE}', (!empty($emaildata['start_date']) && $emaildata['start_date'] != '0000-00-00') ? JFactory::getDate($course_start_date)->format(JText::_('COM_SEMINARMAN_DATE_FORMAT1')) : JText::_('COM_SEMINARMAN_NOVALUE'), $msgSubject);
    		$msgSubject = str_replace('{COURSE_FINISH_DATE}', (!empty($emaildata['finish_date']) && $emaildata['finish_date'] != '0000-00-00') ? JFactory::getDate($course_finish_date)->format(JText::_('COM_SEMINARMAN_DATE_FORMAT1')) : JText::_('COM_SEMINARMAN_NOVALUE'), $msgSubject);
    		$msgSubject = str_replace('{COURSE_START_TIME}', (!empty($emaildata['start_time'])) ? date('H:i', strtotime($course_start_time)) : '', $msgSubject);
    		$msgSubject = str_replace('{COURSE_FINISH_TIME}', (!empty($emaildata['finish_time'])) ? date('H:i', strtotime($course_finish_time)) : '', $msgSubject);
    		$msgSubject = str_replace('{TUTOR}', $queryResult->tutor, $msgSubject);
    		$msgSubject = str_replace('{TUTOR_FIRSTNAME}', $queryResult->tutor_first_name, $msgSubject);
    		$msgSubject = str_replace('{TUTOR_LASTNAME}', $queryResult->tutor_last_name, $msgSubject);
    		$msgSubject = str_replace('{TUTOR_SALUTATION}', $queryResult->tutor_salutation, $msgSubject);
    		$msgSubject = str_replace('{TUTOR_OTHER_TITLE}', $queryResult->tutor_other_title, $msgSubject);
    		$msgSubject = str_replace('{COURSE_ALL_TUTORS}', $queryResult->course_all_tutors, $msgSubject);
    		$msgSubject = str_replace('{COURSE_ALL_TUTORS_FULLNAME}', $queryResult->course_all_tutors_fullname, $msgSubject);
    		$msgSubject = str_replace('{COURSE_ALL_TUTORS_COMBINAME}', $queryResult->course_all_tutors_combiname, $msgSubject);
    		$msgSubject = str_replace('{GROUP}', $queryResult->atgroup, $msgSubject);
    		$msgSubject = str_replace('{GROUP_DESC}', $queryResult->atgroup_desc, $msgSubject);
    		$msgSubject = str_replace('{EXPERIENCE_LEVEL}', $queryResult->experience_level, $msgSubject);
    		$msgSubject = str_replace('{EXPERIENCE_LEVEL_DESC}', $queryResult->experience_level_desc, $msgSubject);
    		$msgSubject = str_replace('{COURSE_START_WEEKDAY}', $COURSE_START_WEEKDAY, $msgSubject);
    		$msgSubject = str_replace('{COURSE_FIRST_SESSION_TITLE}', $COURSE_FIRST_SESSION_TITLE, $msgSubject);
    		$msgSubject = str_replace('{COURSE_FIRST_SESSION_CLOCK}', $COURSE_FIRST_SESSION_CLOCK, $msgSubject);
    		$msgSubject = str_replace('{COURSE_FIRST_SESSION_DURATION}', $COURSE_FIRST_SESSION_DURATION, $msgSubject);
    		$msgSubject = str_replace('{COURSE_FIRST_SESSION_ROOM}', $COURSE_FIRST_SESSION_ROOM, $msgSubject);
    		$msgSubject = str_replace('{COURSE_FIRST_SESSION_COMMENT}', $COURSE_FIRST_SESSION_COMMENT, $msgSubject);
    		$msgSubject = str_replace('{CURRENT_DATE}', $current_date, $msgSubject);
    		$msgSubject = str_replace('{PAYMENT_METHOD}', JHTMLSeminarman::cEscape($payment_method_lbl), $msgSubject);
    		$msgSubject = str_replace('{PAYMENT_FEE}', JHTMLSeminarman::cEscape($payment_fee_lbl), $msgSubject);
    		$msgSubject = str_replace('{PAYMENT_TOTAL}', JHTMLSeminarman::cEscape($payment_total_lbl), $msgSubject);
    		$msgSubject = str_replace('{COURSE_CUSTOM_FIELD_1}', $queryResult->custom_fld_1_value, $msgSubject);
    		$msgSubject = str_replace('{COURSE_CUSTOM_FIELD_2}', $queryResult->custom_fld_2_value, $msgSubject);
    		$msgSubject = str_replace('{COURSE_CUSTOM_FIELD_3}', $queryResult->custom_fld_3_value, $msgSubject);
    		$msgSubject = str_replace('{COURSE_CUSTOM_FIELD_4}', $queryResult->custom_fld_4_value, $msgSubject);
    		$msgSubject = str_replace('{COURSE_CUSTOM_FIELD_5}', $queryResult->custom_fld_5_value, $msgSubject);
    
    		$msgBody = str_replace('{ADMIN_CUSTOM_RECIPIENT}', $params->get('component_email'), $msgBody);
    		$msgBody = str_replace('{TUTOR_RECIPENTS}', $queryResult->tutor_recipients, $msgBody);
    		$msgBody = str_replace('{APPLICATION_ID}', JHTMLSeminarman::cEscape($queryResult->application_id), $msgBody);
    		$msgBody = str_replace('{ATTENDEES}', JHTMLSeminarman::cEscape($queryResult->attendees), $msgBody);
    		$msgBody = str_replace('{SALUTATION}', JHTMLSeminarman::cEscape($queryResult->salutation), $msgBody);
    		$msgBody = str_replace('{TITLE}', JHTMLSeminarman::cEscape($queryResult->title), $msgBody);
    		$msgBody = str_replace('{FIRSTNAME}', JHTMLSeminarman::cEscape($queryResult->first_name), $msgBody);
    		$msgBody = str_replace('{LASTNAME}', JHTMLSeminarman::cEscape($queryResult->last_name), $msgBody);
    		$msgBody = str_replace('{EMAIL}', JHTMLSeminarman::cEscape($queryResult->email), $msgBody);
    		$msgBody = str_replace('{EMAIL_CONFIRM_CC}', JHTMLSeminarman::cEscape($booking_email_cc), $msgBody);
    		$msgBody = str_replace('{COURSE_ID}', JHTMLSeminarman::cEscape($queryResult->course_id), $msgBody);
    		$msgBody = str_replace('{COURSE_TITLE}', $queryResult->course, $msgBody);
    		$msgBody = str_replace('{COURSE_CODE}', $queryResult->code, $msgBody);
    		$msgBody = str_replace('{COURSE_INTROTEXT}', $queryResult->introtext, $msgBody);
    		$msgBody = str_replace('{COURSE_FULLTEXT}', $queryResult->fulltext, $msgBody);
    		$msgBody = str_replace('{COURSE_CAPACITY}', $queryResult->capacity, $msgBody);
    		$msgBody = str_replace('{COURSE_LOCATION}', $queryResult->location, $msgBody);
    		$msgBody = str_replace('{COURSE_URL}', $queryResult->url, $msgBody);
    		$msgBody = str_replace('{PRICE_PER_ATTENDEE}', JHTMLSeminarman::cEscape($queryResult->price_per_attendee), $msgBody);
    		$msgBody = str_replace('{PRICE_PER_ATTENDEE_VAT}', JHTMLSeminarman::cEscape($queryResult->price_per_attendee_vat), $msgBody);
    		$msgBody = str_replace('{PRICE_TOTAL}', JHTMLSeminarman::cEscape($queryResult->price_total), $msgBody);
    		$msgBody = str_replace('{PRICE_TOTAL_VAT}', JHTMLSeminarman::cEscape($queryResult->price_total_vat), $msgBody);
    		$msgBody = str_replace('{PRICE_VAT_PERCENT}', JHTMLSeminarman::cEscape($queryResult->price_vat_percent), $msgBody);
    		$msgBody = str_replace('{PRICE_VAT}', JHTMLSeminarman::cEscape($queryResult->price_vat), $msgBody);
    		$msgBody = str_replace('{PRICE_TOTAL_DISCOUNT}', JHTMLSeminarman::cEscape($queryResult->price_total_discount), $msgBody);
    		$msgBody = str_replace('{PRICE_TOTAL_ORIG_VAT}', JHTMLSeminarman::cEscape($queryResult->price_total_orig_vat), $msgBody);
    		$msgBody = str_replace('{PRICE_REAL_BOOKING_SINGLE}', JHTMLSeminarman::cEscape($queryResult->price_booking_single), $msgBody);
    		$msgBody = str_replace('{PRICE_REAL_BOOKING_TOTAL}', JHTMLSeminarman::cEscape($queryResult->price_booking_total), $msgBody);
    		$msgBody = str_replace('{PRICE_GROUP_ORDERED}', $queryResult->pricegroup, $msgBody);
    		//$msgBody = str_replace('{COURSE_START_DATE}', strftime("%a, %d %B %Y", strtotime($emaildata['start_date'])), $msgBody);
    		$msgBody = str_replace('{COURSE_START_DATE}', (!empty($emaildata['start_date']) && $emaildata['start_date'] != '0000-00-00') ? JFactory::getDate($course_start_date)->format(JText::_('COM_SEMINARMAN_DATE_FORMAT1')) : JText::_('COM_SEMINARMAN_NOVALUE'), $msgBody);
    		//$msgBody = str_replace('{COURSE_FINISH_DATE}', strftime("%a, %d %B %Y", strtotime($emaildata['finish_date'])), $msgBody);
    		$msgBody = str_replace('{COURSE_FINISH_DATE}', (!empty($emaildata['finish_date']) && $emaildata['finish_date'] !== '0000-00-00') ? JFactory::getDate($course_finish_date)->format(JText::_('COM_SEMINARMAN_DATE_FORMAT1')) : JText::_('COM_SEMINARMAN_NOVALUE'), $msgBody);
    		$msgBody = str_replace('{COURSE_START_TIME}', (!empty($emaildata['start_time'])) ? date('H:i', strtotime($course_start_time)) : '', $msgBody);
    		$msgBody = str_replace('{COURSE_FINISH_TIME}', (!empty($emaildata['finish_time'])) ? date('H:i', strtotime($course_finish_time)) : '', $msgBody);
    		$msgBody = str_replace('{TUTOR}', $queryResult->tutor, $msgBody);
    		$msgBody = str_replace('{TUTOR_FIRSTNAME}', $queryResult->tutor_first_name, $msgBody);
    		$msgBody = str_replace('{TUTOR_LASTNAME}', $queryResult->tutor_last_name, $msgBody);
    		$msgBody = str_replace('{TUTOR_SALUTATION}', $queryResult->tutor_salutation, $msgBody);
    		$msgBody = str_replace('{TUTOR_OTHER_TITLE}', $queryResult->tutor_other_title, $msgBody);
    		$msgBody = str_replace('{COURSE_ALL_TUTORS}', $queryResult->course_all_tutors, $msgBody);
    		$msgBody = str_replace('{COURSE_ALL_TUTORS_FULLNAME}', $queryResult->course_all_tutors_fullname, $msgBody);
    		$msgBody = str_replace('{COURSE_ALL_TUTORS_COMBINAME}', $queryResult->course_all_tutors_combiname, $msgBody);
    		$msgBody = str_replace('{GROUP}', $queryResult->atgroup, $msgBody);
    		$msgBody = str_replace('{GROUP_DESC}', $queryResult->atgroup_desc, $msgBody);
    		$msgBody = str_replace('{EXPERIENCE_LEVEL}', $queryResult->experience_level, $msgBody);
    		$msgBody = str_replace('{EXPERIENCE_LEVEL_DESC}', $queryResult->experience_level_desc, $msgBody);
    		$msgBody = str_replace('{COURSE_START_WEEKDAY}', $COURSE_START_WEEKDAY, $msgBody);
    		$msgBody = str_replace('{COURSE_FIRST_SESSION_TITLE}', $COURSE_FIRST_SESSION_TITLE, $msgBody);
    		$msgBody = str_replace('{COURSE_FIRST_SESSION_CLOCK}', $COURSE_FIRST_SESSION_CLOCK, $msgBody);
    		$msgBody = str_replace('{COURSE_FIRST_SESSION_DURATION}', $COURSE_FIRST_SESSION_DURATION, $msgBody);
    		$msgBody = str_replace('{COURSE_FIRST_SESSION_ROOM}', $COURSE_FIRST_SESSION_ROOM, $msgBody);
    		$msgBody = str_replace('{COURSE_FIRST_SESSION_COMMENT}', $COURSE_FIRST_SESSION_COMMENT, $msgBody);
    		$msgBody = str_replace('{CURRENT_DATE}', $current_date, $msgBody);
    		$msgBody = str_replace('{PAYMENT_METHOD}', JHTMLSeminarman::cEscape($payment_method_lbl), $msgBody);
    		$msgBody = str_replace('{PAYMENT_FEE}', JHTMLSeminarman::cEscape($payment_fee_lbl), $msgBody);
    		$msgBody = str_replace('{PAYMENT_TOTAL}', JHTMLSeminarman::cEscape($payment_total_lbl), $msgBody);
    		$msgBody = str_replace('{COURSE_CUSTOM_FIELD_1}', $queryResult->custom_fld_1_value, $msgBody);
    		$msgBody = str_replace('{COURSE_CUSTOM_FIELD_2}', $queryResult->custom_fld_2_value, $msgBody);
    		$msgBody = str_replace('{COURSE_CUSTOM_FIELD_3}', $queryResult->custom_fld_3_value, $msgBody);
    		$msgBody = str_replace('{COURSE_CUSTOM_FIELD_4}', $queryResult->custom_fld_4_value, $msgBody);
    		$msgBody = str_replace('{COURSE_CUSTOM_FIELD_5}', $queryResult->custom_fld_5_value, $msgBody);
    		
    		// additional parameters
    		$dispatcher=JDispatcher::getInstance();
    		JPluginHelper::importPlugin('seminarman');
    		$extData=$dispatcher->trigger('onGenerateConfirmEmail', array(array($emaildata['applicationid'], $quantity, $price_total_booking_with_tax, $msgSubject, $msgBody)));
    		if(isset($extData) && !empty($extData)) {
    			$msgSubject = $extData[0]['subject'];
    			$msgBody = $extData[0]['body'];
    		}     		
    		
    		$msgRecipient = str_replace('{EMAIL}', $queryResult->email, $msgRecipient);
    		$msgRecipient = str_replace('{ADMIN_CUSTOM_RECIPIENT}', $params->get('component_email'), $msgRecipient);
    		$msgRecipient = str_replace('{TUTOR_RECIPIENTS}', $queryResult->tutor_recipients, $msgRecipient);
    
    		$msgRecipients = array_filter(explode(",", str_replace(" ","", trim($msgRecipient))));
    		
    		$message->addRecipient($msgRecipients);
    		
    		if (!empty($msgRecipientCC))
    		{
    			$msgRecipientCC = str_replace('{EMAIL}', $queryResult->email, $msgRecipientCC);
    			$msgRecipientCC = str_replace('{ADMIN_CUSTOM_RECIPIENT}', $params->get('component_email'), $msgRecipientCC);
    			$msgRecipientCC = str_replace('{TUTOR_RECIPIENTS}', $queryResult->tutor_recipients, $msgRecipientCC);
    			$msgRecipientCC = array_filter(explode(",", str_replace(" ","", trim($msgRecipientCC))));
    			$message->addCC($msgRecipientCC);
    		}
    		if (!empty($msgRecipientBCC))
    		{
    			$msgRecipientBCC = str_replace('{EMAIL}', $queryResult->email, $msgRecipientBCC);
    			$msgRecipientBCC = str_replace('{ADMIN_CUSTOM_RECIPIENT}', $params->get('component_email'), $msgRecipientBCC);
    			$msgRecipientBCC = str_replace('{TUTOR_RECIPIENTS}', $queryResult->tutor_recipients, $msgRecipientBCC);
    			$msgRecipientBCC = array_filter(explode(",", str_replace(" ","", trim($msgRecipientBCC))));
    			$message->addBCC($msgRecipientBCC);
    		}
    		
    		$message->setSubject($msgSubject);
    		$message->setBody($msgBody);
    		$message->setSender($msgSender);
    		$message->IsHTML(true);
    
    		if (!empty($attachment))
    			$message->addAttachment($attachment);
    			
    		$sent = $message->send();
    		return $sent;
    
    	}
    }
    
    /**
     * returns an key value array that can be used to replace
     * the fields in a pdf template with actual values
     * @param $applicationid (int)
     */
    static function getFieldValuesForTemplate($applicationid)
    {
    	$db = JFactory::getDBO();
    	$data = array();
    
    	// application data    	
    	$query = $db->getQuery(true);
    	$query->select( 'a.id AS `APPLICATION_ID`' );
    	$query->select( 'a.invoice_number AS `INVOICE_NUMBER`' );
    	$query->select( 'a.date AS `INVOICE_DATE`' );
    	$query->select( 'a.attendees AS `ATTENDEES`' );
    	$query->select( 'a.salutation AS `SALUTATION`' );
    	$query->select( 'a.title AS `TITLE`' );
    	$query->select( 'a.first_name AS `FIRSTNAME`' );
    	$query->select( 'a.last_name AS `LASTNAME`' );
    	$query->select( 'a.email AS `EMAIL`' );
    	$query->select( 'a.price_per_attendee AS `PRICE_PER_ATTENDEE`' );
    	$query->select( 'a.price_total AS `PRICE_TOTAL`' );
    	$query->select( 'a.price_vat AS `PRICE_VAT_PERCENT`' );
    	$query->select( 'a.pricegroup AS `PRICE_GROUP_ORDERED`' );
    	$query->select( 'a.status AS `PAYMENT_STATUS`' );
    	$query->select( 'a.params AS `BOOKING_PARAMS`' );
    	$query->select( 'NOW() AS `CURRENT_DATE`' );
    	$query->select( 'c.code AS `COURSE_CODE`' );
    	$query->select( 'c.title AS `COURSE_TITLE`' );
    	$query->select( 'c.capacity AS `COURSE_CAPACITY`' );
    	$query->select( 'c.location AS `COURSE_LOCATION`' );
    	$query->select( 'c.url AS `COURSE_URL`' );
    	$query->select( 'c.start_date AS `COURSE_START_DATE`' );
    	$query->select( 'c.finish_date AS `COURSE_FINISH_DATE`' );
    	$query->select( 'c.start_time AS `COURSE_START_TIME`' );
    	$query->select( 'c.finish_time AS `COURSE_FINISH_TIME`' );
    	$query->select( 'c.introtext AS `COURSE_INTROTEXT`' );
    	$query->select( 'c.fulltext AS `COURSE_FULLTEXT`' );
    	$query->select( 'c.tutor_id AS `COURSE_TUTOR_IDS`' );
    	$query->select( 'c.price AS `COURSE_PRICE_ORIG`' );
    	$query->select( 'c.id AS `COURSE_ID`' );
    	$query->select( 'c.attribs AS `COURSE_ATTRIBS`' );
    	$query->select( 'g.title AS `GROUP`' );
    	$query->select( 'l.title AS `EXPERIENCE_LEVEL`' );
    	$query->from( '`#__seminarman_application` AS a' );
    	$query->join( "LEFT", '`#__seminarman_courses` AS c ON a.course_id = c.id' );
    	$query->join( "LEFT", '`#__seminarman_atgroup` AS g ON c.id_group = g.id' );
    	$query->join( "LEFT", '`#__seminarman_experience_level` AS l ON c.id_experience_level = l.id' );
    	$query->where( 'a.id = '. (int) $applicationid );
    	$db->setQuery( $query );
    	$data = $db->loadAssoc();
    	
        // security
        $data['APPLICATION_ID'] = JHTMLSeminarman::cEscape($data['APPLICATION_ID']);
        $data['ATTENDEES'] = JHTMLSeminarman::cEscape($data['ATTENDEES']);
        $data['SALUTATION'] = JHTMLSeminarman::cEscape($data['SALUTATION']);
        $data['TITLE'] = JHTMLSeminarman::cEscape($data['TITLE']);
        $data['FIRSTNAME'] = JHTMLSeminarman::cEscape($data['FIRSTNAME']);
        $data['LASTNAME'] = JHTMLSeminarman::cEscape($data['LASTNAME']);
        $data['EMAIL'] = JHTMLSeminarman::cEscape($data['EMAIL']);
        $data['PRICE_PER_ATTENDEE'] = JHTMLSeminarman::cEscape($data['PRICE_PER_ATTENDEE']);
        $data['PRICE_TOTAL'] = JHTMLSeminarman::cEscape($data['PRICE_TOTAL']);
        $data['PRICE_VAT_PERCENT'] = JHTMLSeminarman::cEscape($data['PRICE_VAT_PERCENT']);
        $data['PRICE_GROUP_ORDERED'] = JHTMLSeminarman::cEscape($data['PRICE_GROUP_ORDERED']);
        $data['PAYMENT_STATUS'] = JHTMLSeminarman::cEscape($data['PAYMENT_STATUS']);
    
    	if ($data['PAYMENT_STATUS'] == 0) {
    		$data['PAYMENT_STATUS'] = JText::_( 'COM_SEMINARMAN_SUBMITTED' );
    	} elseif ($data['PAYMENT_STATUS'] == 1) {
    		$data['PAYMENT_STATUS'] = JText::_( 'COM_SEMINARMAN_PENDING' );
    	} elseif ($data['PAYMENT_STATUS'] == 2) {
    		$data['PAYMENT_STATUS'] = JText::_( 'COM_SEMINARMAN_PAID' );
    	} elseif ($data['PAYMENT_STATUS'] == 3) {
    		$data['PAYMENT_STATUS'] = JText::_( 'COM_SEMINARMAN_CANCELED' );
    	}
    	
    	$data['CURRENT_DATE'] = JFactory::getDate($data['CURRENT_DATE'])->format(JText::_('COM_SEMINARMAN_DATE_FORMAT1'));
    
    	// custom fields
    	$query = $db->getQuery(true);
    	$query->select( 'fieldcode, value' );
    	$query->from( '`#__seminarman_fields_values` AS v' );
    	$query->join( "LEFT", '`#__seminarman_fields` AS f ON v.field_id = f.id' );
    	$query->where( 'applicationid = '. (int) $applicationid );
    	$db->setQuery( $query );
    	
    	foreach ($db->loadRowList() as $row)
    		$data[$row[0]] = JHTMLSeminarman::cEscape($row[1]);
    		
    	// parameters for multiple tutors
    	$course_tutors_id_array = (array)json_decode($data['COURSE_TUTOR_IDS'], true);
    	$course_first_tutor_id = $course_tutors_id_array[0];
    	$course_tutors = array();
    	foreach ($course_tutors_id_array as $course_tutors_id) {
    		$query = $db->getQuery(true);
    		$query->select( 'CONCAT_WS(\' \', emp.salutation, emp.other_title, emp.firstname, emp.lastname) AS tutor_combiname, CONCAT_WS(\' \', emp.firstname, emp.lastname) AS tutor_fullname, emp.title AS tutor_displayname, emp.firstname AS tutor_firstname, emp.lastname AS tutor_lastname, emp.salutation AS tutor_salutation, emp.other_title AS tutor_title' );
    		$query->from( '`#__seminarman_tutor` AS emp' );
    		$query->where( 'emp.id = ' . $course_tutors_id );
    		$db->setQuery( $query );
    		$ergebnis = $db->loadAssoc();
    		
    		$course_tutors[$course_tutors_id] = $ergebnis;
    	}
    	$data['COURSE_ALL_TUTORS'] = '';
    	$data['COURSE_ALL_TUTORS_FULLNAME'] = '';
    	$data['COURSE_ALL_TUTORS_COMBINAME'] = '';
    	$printComma = false;
    	foreach ($course_tutors as $tutor_key => $tutor_content) {
    		if ($printComma) {
    			$data['COURSE_ALL_TUTORS'] = $data['COURSE_ALL_TUTORS'] . ', ';
    			$data['COURSE_ALL_TUTORS_FULLNAME'] = $data['COURSE_ALL_TUTORS_FULLNAME'] . ', ';
    			$data['COURSE_ALL_TUTORS_COMBINAME'] = $data['COURSE_ALL_TUTORS_COMBINAME'] . ', ';
    		}
    		$data['COURSE_ALL_TUTORS'] = $data['COURSE_ALL_TUTORS'] . $tutor_content['tutor_displayname'];
    		$data['COURSE_ALL_TUTORS_FULLNAME'] = $data['COURSE_ALL_TUTORS_FULLNAME'] . $tutor_content['tutor_fullname'];
    		$data['COURSE_ALL_TUTORS_COMBINAME'] = $data['COURSE_ALL_TUTORS_COMBINAME'] . $tutor_content['tutor_combiname'];
    		$printComma = true;
    	}
    
    	// parameters for the first tutor
    	$data['TUTOR'] = $course_tutors[$course_first_tutor_id]['tutor_displayname'];
    	$data['TUTOR_FIRSTNAME'] = $course_tutors[$course_first_tutor_id]['tutor_firstname'];
    	$data['TUTOR_LASTNAME'] = $course_tutors[$course_first_tutor_id]['tutor_lastname'];
    	$data['TUTOR_SALUTATION'] = $course_tutors[$course_first_tutor_id]['tutor_salutation'];
    	$data['TUTOR_OTHER_TITLE'] = $course_tutors[$course_first_tutor_id]['tutor_title'];
    
    	// custom tutor fields for the first tutor
    	$query = $db->getQuery(true);
    	$query->select( 'f.fieldcode, ct.value' );
    	$query->from( '`#__seminarman_fields_values_tutors` AS ct' );
    	$query->join( "LEFT", '`#__seminarman_fields` AS f ON ct.field_id = f.id' );
    	$query->where( 'ct.tutor_id = '. (int)$course_first_tutor_id );
    	$query->where( 'f.published = ' . $db->Quote('1') );
    	$db->setQuery( $query );
    	foreach ($db->loadRowList() as $row)
    		$data[$row[0]] = $row[1];
    	
    	// course custom fields
    		$course_attribs = new JRegistry();
    		$course_attribs->loadString($data['COURSE_ATTRIBS']);
    		$custom_fld_1_value = $course_attribs->get('custom_fld_1_value');
    		$custom_fld_2_value = $course_attribs->get('custom_fld_2_value');
    		$custom_fld_3_value = $course_attribs->get('custom_fld_3_value');
    		$custom_fld_4_value = $course_attribs->get('custom_fld_4_value');
    		$custom_fld_5_value = $course_attribs->get('custom_fld_5_value');
    		$data['COURSE_CUSTOM_FIELD_1'] = (!empty($custom_fld_1_value))?$custom_fld_1_value:'';
    		$data['COURSE_CUSTOM_FIELD_2'] = (!empty($custom_fld_2_value))?$custom_fld_2_value:'';
    		$data['COURSE_CUSTOM_FIELD_3'] = (!empty($custom_fld_3_value))?$custom_fld_3_value:'';
    		$data['COURSE_CUSTOM_FIELD_4'] = (!empty($custom_fld_4_value))?$custom_fld_4_value:'';
    		$data['COURSE_CUSTOM_FIELD_5'] = (!empty($custom_fld_5_value))?$custom_fld_5_value:'';    	
    
    	// calculate and format prices
    	$lang = JFactory::getLanguage();
    
    	$price_orig = $data['COURSE_PRICE_ORIG'];
    	$price_booking = $data['PRICE_PER_ATTENDEE'];
    	$quantity = $data['ATTENDEES'];
    	$tax_rate = $data['PRICE_VAT_PERCENT'] / 100.0;
    	$price_total_orig = $price_orig * $quantity;
    	$price_total_booking = $data['PRICE_TOTAL'];
    	$price_orig_with_tax = $price_orig * (1 + $tax_rate);
    	$price_booking_with_tax = $price_booking * (1 + $tax_rate);
    	$price_total_orig_with_tax = $price_total_orig * (1 + $tax_rate);
    	$price_total_booking_with_tax = $price_total_booking * (1 + $tax_rate);
    	$tax_orig = $price_total_orig * $tax_rate;
    	$tax_booking = $price_total_booking * $tax_rate;
    
    	$old_locale = setlocale(LC_NUMERIC, NULL);
    	setlocale(LC_NUMERIC, $lang->getLocale());
    	// if (doubleval($price_total_orig) == doubleval($price_total_booking)) {
    	//    $data['PRICE_PER_ATTENDEE'] = JText::sprintf('%.2f', $price_booking);
    	//    $data['PRICE_TOTAL'] = JText::sprintf('%.2f', $price_total_booking);
    	//    $data['PRICE_PER_ATTENDEE_VAT'] = JText::sprintf('%.2f', $price_booking_with_tax);
    	//    $data['PRICE_TOTAL_VAT'] = JText::sprintf('%.2f', $price_total_booking_with_tax);
    	//    $data['PRICE_VAT'] = JText::sprintf('%.2f', $tax_booking);
    	// } else {
    	//    $data['PRICE_PER_ATTENDEE'] = '<s>' . JText::sprintf('%.2f', $price_orig) . '</s> ' . JText::sprintf('%.2f', $price_booking);
    	//    $data['PRICE_TOTAL'] = '<s>' . JText::sprintf('%.2f', $price_total_orig) . '</s> ' . JText::sprintf('%.2f', $price_total_booking);
    	//    $data['PRICE_PER_ATTENDEE_VAT'] = '<s>' . JText::sprintf('%.2f', $price_orig_with_tax) . '</s> ' . JText::sprintf('%.2f', $price_booking_with_tax);
    	//    $data['PRICE_TOTAL_VAT'] = '<s>' . JText::sprintf('%.2f', $price_total_orig_with_tax) . '</s> ' . JText::sprintf('%.2f', $price_total_booking_with_tax);
    	//    $data['PRICE_VAT'] = JText::sprintf('%.2f', $tax_booking);
    	// }
    	// $data['PRICE_PER_ATTENDEE_VAT'] = JText::sprintf('%.2f', (($data['PRICE_PER_ATTENDEE'] / 100.0) * $data['PRICE_VAT_PERCENT']) + $data['PRICE_PER_ATTENDEE']);
    	// $data['PRICE_TOTAL_VAT'] = JText::sprintf('%.2f', (($data['PRICE_TOTAL'] / 100.0) * $data['PRICE_VAT_PERCENT']) + $data['PRICE_TOTAL']);
    	// $data['PRICE_VAT'] = JText::sprintf('%.2f', ($data['PRICE_TOTAL'] / 100.0) * $data['PRICE_VAT_PERCENT']);
    	// $data['PRICE_PER_ATTENDEE'] = JText::sprintf('%.2f', $data['PRICE_PER_ATTENDEE']);
    	// $data['PRICE_TOTAL'] = JText::sprintf('%.2f', $data['PRICE_TOTAL']);
    	$data['PRICE_PER_ATTENDEE'] = JText::sprintf('%.2f', round($price_orig, 2));
    	$data['PRICE_TOTAL'] = JText::sprintf('%.2f', round($price_total_orig, 2));
    	$data['PRICE_PER_ATTENDEE_VAT'] = JText::sprintf('%.2f', round($price_orig_with_tax, 2));
    	$data['PRICE_TOTAL_DISCOUNT'] = JText::sprintf('%.2f', round(($price_total_booking - $price_total_orig), 2));
    	$data['PRICE_VAT'] = JText::sprintf('%.2f', round($tax_booking, 2));
    	$data['PRICE_TOTAL_VAT'] = JText::sprintf('%.2f', round($price_total_booking_with_tax, 2));
    	$data['PRICE_TOTAL_ORIG_VAT'] = JText::sprintf('%.2f', round($price_total_orig_with_tax, 2));
    
    	$data['PRICE_REAL_BOOKING_SINGLE'] = JText::sprintf('%.2f', round($price_booking, 2));
    	$data['PRICE_REAL_BOOKING_TOTAL'] = JText::sprintf('%.2f', round($price_total_booking, 2));
    
    	setlocale(LC_NUMERIC, $old_locale);
    
    	// compute loaded date time (utc) to local date time
    	$course_start_arr = SeminarmanFunctions::formatUTCtoLocal($data['COURSE_START_DATE'], $data['COURSE_START_TIME']);
    	$course_finish_arr = SeminarmanFunctions::formatUTCtoLocal($data['COURSE_FINISH_DATE'], $data['COURSE_FINISH_TIME']);
    	$course_start_date = $course_start_arr[0];  // local
    	$course_start_time = $course_start_arr[1];  // local
    	$course_finish_date = $course_finish_arr[0];  // local
    	$course_finish_time = $course_finish_arr[1];  // local
    
    	// format date
    	$data['INVOICE_DATE'] = JFactory::getDate($data['INVOICE_DATE'])->format(JText::_('COM_SEMINARMAN_DATE_FORMAT1'));
    	$data['COURSE_START_DATE'] = (!empty($data['COURSE_START_DATE']) && $data['COURSE_START_DATE'] !== '0000-00-00') ? JFactory::getDate($course_start_date)->format(JText::_('COM_SEMINARMAN_DATE_FORMAT1')) : JText::_('COM_SEMINARMAN_NOT_SPECIFIED');
    	$data['COURSE_FINISH_DATE'] = (!empty($data['COURSE_FINISH_DATE']) && $data['COURSE_FINISH_DATE'] !== '0000-00-00') ? JFactory::getDate($course_finish_date)->format(JText::_('COM_SEMINARMAN_DATE_FORMAT1')) : JText::_('COM_SEMINARMAN_NOT_SPECIFIED');
    	$data['COURSE_START_TIME'] = (!empty($data['COURSE_START_TIME'])) ? date('H:i', strtotime($course_start_time)) : '';
    	$data['COURSE_FINISH_TIME'] = (!empty($data['COURSE_FINISH_TIME'])) ? date('H:i', strtotime($course_finish_time)) : '';
    
    	// start weekday
    	$langs = JComponentHelper::getParams('com_languages');
    	$selectedLang = $langs->get('site', 'en-GB');
    	if ($selectedLang == "de-DE") {
    		$trans = array(
    				'Monday'    => 'Montag',
    				'Tuesday'   => 'Dienstag',
    				'Wednesday' => 'Mittwoch',
    				'Thursday'  => 'Donnerstag',
    				'Friday'    => 'Freitag',
    				'Saturday'  => 'Samstag',
    				'Sunday'    => 'Sonntag',
    				'Mon'       => 'Mo',
    				'Tue'       => 'Di',
    				'Wed'       => 'Mi',
    				'Thu'       => 'Do',
    				'Fri'       => 'Fr',
    				'Sat'       => 'Sa',
    				'Sun'       => 'So',
    				'January'   => 'Januar',
    				'February'  => 'Februar',
    				'March'     => 'März',
    				'May'       => 'Mai',
    				'June'      => 'Juni',
    				'July'      => 'Juli',
    				'October'   => 'Oktober',
    				'December'  => 'Dezember'
    		);
    		$data['COURSE_START_WEEKDAY'] = (!empty($data['COURSE_START_DATE'])) ? strtr(date('l', strtotime($course_start_date)), $trans) : '';
    	} else {
    		$data['COURSE_START_WEEKDAY'] = (!empty($data['COURSE_START_DATE'])) ? date('l', strtotime($course_start_date)) : '';
    	}
    
    	// first session infos
    	$query = $db->getQuery(true);
    	$query->select( '*' );
    	$query->from( '`#__seminarman_sessions`' );
    	$query->where( 'published = 1' );
    	$query->where( 'courseid = ' . $data['COURSE_ID'] );
    	$query->order( 'session_date' );
    	$db->setQuery( $query );
    	$course_sessions = $db->loadObjectList();
    
    	if(!empty($course_sessions)){
    		// compute loaded date time (utc) to local date time
    		$session_start_arr = SeminarmanFunctions::formatUTCtoLocal($course_sessions[0]->session_date, $course_sessions[0]->start_time);
    		$session_finish_arr = SeminarmanFunctions::formatUTCtoLocal($course_sessions[0]->session_date, $course_sessions[0]->finish_time);
    		 
    		$data['COURSE_FIRST_SESSION_TITLE'] = $course_sessions[0]->title;
    		$data['COURSE_FIRST_SESSION_CLOCK'] = date('H:i', strtotime($session_start_arr[1])) . ' - ' . date('H:i', strtotime($session_finish_arr[1]));
    		$data['COURSE_FIRST_SESSION_DURATION'] = $course_sessions[0]->duration;
    		$data['COURSE_FIRST_SESSION_ROOM'] = $course_sessions[0]->session_location;
    		$data['COURSE_FIRST_SESSION_COMMENT'] = $course_sessions[0]->description;
    	} else {
    		$data['COURSE_FIRST_SESSION_TITLE'] = '';
    		$data['COURSE_FIRST_SESSION_CLOCK'] = '';
    		$data['COURSE_FIRST_SESSION_DURATION'] = '';
    		$data['COURSE_FIRST_SESSION_ROOM'] = '';
    		$data['COURSE_FIRST_SESSION_COMMENT'] = '';
    	}
    
    	// additional parameters
    	$dispatcher=JDispatcher::getInstance();
    	JPluginHelper::importPlugin('seminarman');
    	$extraparams=$dispatcher->trigger('onGeneratePDFBill', array(array($applicationid, $quantity, $price_total_booking_with_tax)));  // we need these 3 things for the implement of our plugin
    	if(isset($extraparams) && !empty($extraparams)) $data = array_merge($data, $extraparams[0]);
    	
    	// something for payment fee
    	$app_params_obj = new JRegistry();
    	$app_params_obj->loadString($data['BOOKING_PARAMS']);
    	$app_params = $app_params_obj->toArray();
    	
    	// security
    	if (isset($app_params['payment_method'])) $app_params['payment_method'] = JHTMLSeminarman::cEscape($app_params['payment_method']);
    	if (isset($app_params['payment_fee'])) $app_params['payment_fee'] = JHTMLSeminarman::cEscape($app_params['payment_fee']);
    	if (isset($app_params['booking_email_cc'])) $app_params['booking_email_cc'] = JHTMLSeminarman::cEscape($app_params['booking_email_cc']);
    	if (isset($app_params['fee1_name'])) $app_params['fee1_name'] = JHTMLSeminarman::cEscape($app_params['fee1_name']);
    	if (isset($app_params['fee1_value'])) $app_params['fee1_value'] = JHTMLSeminarman::cEscape($app_params['fee1_value']);
    	if (isset($app_params['fee1_vat'])) $app_params['fee1_vat'] = JHTMLSeminarman::cEscape($app_params['fee1_vat']);
    	if (isset($app_params['fee1_selected'])) $app_params['fee1_selected'] = JHTMLSeminarman::cEscape($app_params['fee1_selected']);
    	
    	if (isset($app_params['payment_method'])) {
    		if ($app_params['payment_method'] == 1) {
    			$payment_method_lbl = JText::_('COM_SEMINARMAN_BANK_TRANSFER');
    		} elseif ($app_params['payment_method'] == 2) {
    			$payment_method_lbl = JText::_('COM_SEMINARMAN_PAYPAL');
    		}
    		$payment_fee = doubleval(str_replace(",", ".", $app_params['payment_fee']));
    		$payment_fee_lbl = JText::sprintf('%.2f', $payment_fee);
    		
    		if (isset($data['TOTAL_W_EXTRA_FEES_DOUBLEVAL'])) { // comes from extra fee plugin
    			$payment_total = $data['TOTAL_W_EXTRA_FEES_DOUBLEVAL'];
    		} else { // extra fee plugin not set
    			$payment_total = $price_total_booking_with_tax;
    		}
    		$payment_total += $payment_fee;
    		$payment_total_lbl = JText::sprintf('%.2f', $payment_total);
    		
    		$data['PAYMENT_METHOD'] = $payment_method_lbl;
    		$data['PAYMENT_FEE'] = $payment_fee_lbl;
    		$data['PAYMENT_TOTAL'] = $payment_total_lbl;    		
    	} else {
    		$data['PAYMENT_METHOD'] = "";
    		$data['PAYMENT_FEE'] = "";
    		
    		if (isset($data['TOTAL_W_EXTRA_FEES_DOUBLEVAL'])) { // comes from extra fee plugin
    			$payment_total = $data['TOTAL_W_EXTRA_FEES_DOUBLEVAL'];
    		} else { // extra fee plugin not set
    			$payment_total = $price_total_booking_with_tax;
    		}
    		$payment_total_lbl = JText::sprintf('%.2f', $payment_total);
    		$data['PAYMENT_TOTAL'] = $payment_total_lbl;
    	}
    
    	return $data;
    }

	static function localDate2DbDate($str) {
		$db = JFactory::getDBO();
 		if (empty($str) || $str == JText::_('COM_SEMINARMAN_NEVER'))
 			return "0000-00-00";
 		
    	$date = JFactory::getDate($str)->format($db->getDateFormat());
 		if ($date)
 			return $date;
 		else
 			return "0000-00-00";
	}
	
	static function UserIsCourseManager($uid = null){
		
		if (empty($uid)) {
			$user = JFactory::getUser();
		} else {
			$user = JFactory::getUser($uid);
		}
		
		$userGroups = $user->getAuthorisedGroups();
		
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*')
              ->from('#__seminarman_usergroups AS g')
              ->where('g.sm_id = 1');
        $db->setQuery($query);
        $result = $db->loadAssoc();

        $manager_id = $result["jm_id"];

        $ismanager = false;
        
        if ($user->authorise('core.admin', 'com_seminarman')) {
        	$ismanager = true;
        } else {        
            foreach ($userGroups as $gid) {
        	    if ($gid == $manager_id) {
        		    $ismanager = true;
        		    break;
        	    }
            }
        }
		
		return $ismanager;
		
	}
	
	static function getUserTutorID($uid = null){
		if (empty($uid)) {
			$user = JFactory::getUser();
		} else {
			$user = JFactory::getUser($uid);
		}
		$userId = $user->get('id');	

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*')
              ->from('#__seminarman_tutor AS t')
              ->where('t.user_id = '.(int)$userId);		
        $db->setQuery($query);
        $result = $db->loadAssoc();

        if(empty($result)){
        	$tutor_id = 0;
        }else{
        	$tutor_id = $result["id"];
        }
        return $tutor_id;
	}
	
    static function user_is_admin($uid) {
        jimport( 'joomla.user.helper' );
        $groups = JUserHelper::getUserGroups($uid);
        //8 is for Super User and 7 is for Administrator
    	foreach($groups as $temp) {
			if(in_array($temp, Array(7,8))){
					return true;
                    break;
			}
		}
		return false;
    }
    
    static function get_price_view ($course_id, $jsfunction = '', $vmlink = NULL, $bmode = 0) {
    
    	$db = JFactory::getDbo();
    	$params = JComponentHelper::getParams( 'com_seminarman' );

    	$lang = JFactory::getLanguage();
    	$old_locale = setlocale(LC_NUMERIC, NULL);
    	setlocale(LC_NUMERIC, $lang->getLocale());
    	
    	$html = "";
    
    	$query = $db->getQuery(true);
    	$query->select('*')
    	->from('#__seminarman_courses')
    	->where('id = '.(int)$course_id);
    	$db->setQuery($query);
    	$course = $db->loadObject();
    
    	$currency = $params->get( 'currency' );
    
    	$tax_rate = doubleval(str_replace(",", ".", $course->vat))/100;
    	//             $tax_rate = doubleval(str_replace(",", ".", $this->escape($course->vat)))/100;
    
    	$standard_netto = $course->price;
    	//$standard_netto = $this->escape($this->price_before_vat);
    
    	$standard_brutto = JText::sprintf('%.2f', round(doubleval(str_replace(",", ".", $standard_netto)) * (1 + $tax_rate), 2));
    	$standard_netto = JText::sprintf('%.2f', round(doubleval(str_replace(",", ".", $standard_netto)), 2));
    	//             $standard_brutto = JText::sprintf('%.2f', round(doubleval(str_replace(",", ".", $this->escape($standard_netto))) * (1 + $tax_rate), 2));
    	//             $standard_netto = JText::sprintf('%.2f', round(doubleval(str_replace(",", ".", $this->escape($standard_netto))), 2));
    
    
    	$display_party = array();
    	
    	$display_free_charge = $params->get('display_free_charge');
    
    	if ($params->get('show_price_1') == 0) {
    		$price1_label = JText::_('COM_SEMINARMAN_NET') . ': '
    		. $standard_netto . ' ' . $currency . ', ' . JText::_('COM_SEMINARMAN_GROSS_WITH_TAX') . ': ' . $standard_brutto . ' ' . $currency;
    		if($course->vat == 0) {
    			$price1_label = $standard_netto . ' ' . $currency;
    		}
    	} elseif ($params->get('show_price_1') == 1) {
    		$price1_label = JText::_('COM_SEMINARMAN_PRICE_STANDARD');
    	} else {
    		$price1_label = JText::_('COM_SEMINARMAN_PRICE_STANDARD').' ('.JText::_('COM_SEMINARMAN_NET') . ': '
    		. $standard_netto . ' ' . $currency . ', ' . JText::_('COM_SEMINARMAN_GROSS_WITH_TAX') . ': ' . $standard_brutto . ' ' . $currency.')';
    		if($course->vat == 0) {
    			$price1_label = JText::_('COM_SEMINARMAN_PRICE_STANDARD').' ('.$standard_netto . ' ' . $currency.')';
    		}    		
    	}
    	
    	if (!empty($display_free_charge) && ($standard_netto == 0)) {
    		$price1_label = JText::_($params->get('display_free_charge'));
    	}
    
    	// price1
    	$display_party[ 'display_price1' ] = '<input id="booking_price1" type="radio" value="0" '. ($bmode == 2 ? '' : 'checked="checked" ') .'name="booking_price[]" '.$jsfunction.'><label for="jformbookingprice1">' . $price1_label . '</label>';
    
    	// price2-5
    	for ( $i=2; $i < 6; $i++ ) {
    		$val = JHTMLSeminarman::_get_price_display ( $db, $course, $i, $display_party, $price1_label, $jsfunction, $bmode );
    	}
    
    	if (($params->get('trigger_virtuemart') == 1)  && !is_null($vmlink)) {
    		$display_party[ 'display_price1' ] = '<input id="booking_price1" type="radio" value="66" '. ($bmode == 2 ? '' : 'checked="checked" ') .'name="booking_price[]" '.$jsfunction.'><label for="jformbookingprice1">'
    		. JText::_('COM_SEMINARMAN_PRICE_SHOW_IN_VM') . '</label>';
    		$display_party[ 'display_price2' ] = '';
    		$display_party[ 'display_price3' ] = '';
    		$display_party[ 'display_price4' ] = '';
    		$display_party[ 'display_price5' ] = '';
    	}
    
    	$html .= $display_party[ 'display_price1' ];
    	$html .= $display_party[ 'display_price2' ];
    	$html .= $display_party[ 'display_price3' ];
    	$html .= $display_party[ 'display_price4' ];
    	$html .= $display_party[ 'display_price5' ];

    	setlocale(LC_NUMERIC, $old_locale);
    	
    	return $html;
    }    
    
    static function _get_price_display ( $db, $course, $num, &$display_party, $price1_label, $jsfunction, $bmode = 0 ) {
    
    	// fetch the pricegrp from db
    	$query_pricegroup = $db->getQuery(true);
    	$query_pricegroup->select('*')
    	->from('#__seminarman_pricegroups')
    	->where("gid=$num");
    	$db->setQuery($query_pricegroup);
    	$priceg = $db->loadAssoc();
    	$priceg_name = $priceg['title'];
    	$priceg_usg = json_decode($priceg['jm_groups']);
    
    	if (is_null($priceg_usg)) {
    		$priceg_usg = array();
    	}
    
    	// build view
    	$user = JFactory::getUser();
    	$current_usergroups = $user->getAuthorisedGroups();
    
    	$courseprice = $course->{"price$num"};
    	$coursevat = $course->vat;
    	$params = JComponentHelper::getParams( 'com_seminarman' );
    	$currency = $params->get( 'currency' );
    	$tax_rate = doubleval(str_replace(",", ".", $course->vat))/100;
    	$param_show = $params->get("show_price_$num");
    	
    	$display_free_charge = $params->get('display_free_charge');
    
    	if (!is_null($courseprice) && !($param_show == 0)) {
    		$price_netto = JText::sprintf('%.2f', round(doubleval(str_replace(",", ".", $courseprice)), 2));
    		$price_brutto = JText::sprintf('%.2f', round(doubleval(str_replace(",", ".", $courseprice)) * (1 + $tax_rate), 2));
    
    		$dp_chck = '<input id="booking_price'.$num.'" type="radio" value="' . ($num-1) . '" checked="checked" name="booking_price[]" '.$jsfunction.' style="clear: left;" >';
    		$dp_unck = '<input id="booking_price'.$num.'" type="radio" value="' . ($num-1) . '" name="booking_price[]" '.$jsfunction.' style="clear: left;" >';
    
    		$label_n = '<label for="jformbookingprice'.$num.'" >'
    		. $priceg_name . ' (' . $price_netto . ' ' . $currency . ')</label>';
    		$label_nb = '<label for="jformbookingprice'.$num.'" >'
    		. $priceg_name . ' (' . JText::_('COM_SEMINARMAN_NET') . ': ' . $price_netto . ' ' . $currency . ', ' .
    		JText::_('COM_SEMINARMAN_GROSS_WITH_TAX') . ': ' . $price_brutto . ' ' . $currency . ')</label>';
    		$label_ng = '<label for="jformbookingprice'.$num.'" >'
    		. $priceg_name . '<span style="display: none;"> ' . $price_netto . ' ' . $currency . ' </span> ' . '</label>';
    		
    		if (!empty($display_free_charge) && ($courseprice == 0)) {
    			$label_n = '<label for="jformbookingprice'.$num.'" >'
    		               . $priceg_name . ' (' . JText::_($params->get('display_free_charge')) . ')</label>';
    			$label_nb = '<label for="jformbookingprice'.$num.'" >'
    			           . $priceg_name . ' (' . JText::_($params->get('display_free_charge')) . ')</label>';    			
    		}
			if ($bmode > 0) {
				$display_price = '<br>'. $dp_unck . $label_nb;
    		} else if ($param_show == 1) {  // Anzeige nur für getroffene Nutzer
    			if (array_intersect($current_usergroups, $priceg_usg)) { // getroffen
    
    				for( $i = 1; $i < $num; $i++ ) {
    					$display_party[ "display_price$num" ] = '';
    				}
    				$display_price = '<br>'. $dp_chck . $label_nb;
    				if($coursevat == 0) {
    					$display_price = '<br>'. $dp_chck . $label_n;
    				}
    			}
    			else {  // nicht getroffen
    				$display_price = '';
    			}
    		} elseif ($param_show == 2) {  // Anzeige für alle Nutzer
    			if (array_intersect($current_usergroups, $priceg_usg)) { // getroffen
    				$display_party[ 'display_price1' ] = '<input id="booking_price1" type="radio" value="0" name="booking_price[]" style="clear: left;" '.$jsfunction.' ><label for="jformbookingprice1">' . $price1_label . '</label>';
    				$display_price = '<br>'.$dp_chck . $label_nb;
    				if($coursevat == 0) {
    					$display_price = '<br>'.$dp_chck . $label_n;
    				}
    			}
    			else {
    				$display_price = '<br>'.$dp_unck . $label_nb;
    				if($coursevat == 0) {
    					$display_price = '<br>'.$dp_unck . $label_n;
    				}
    			}
    		} elseif ($param_show == 3) {  // Anzeige ohne Preiswert
    			if (array_intersect($current_usergroups, $priceg_usg)) { // getroffen
    				$display_party[ 'display_price1' ] = '<input id="booking_price1" type="radio" value="0" name="booking_price[]" style="clear: left;" '.$jsfunction.' ><label for="jformbookingprice1">' . $price1_label . '</label>';
    				$display_price = '<br>'.$dp_chck . $label_ng;
    			} else {
    				$display_price = '<br>'.$dp_unck . $label_ng;
    			}
    		}
    	} else { // Anzeige für keine Nutzer oder kein x. Preis definiert
    		$display_price = '';
    	}
    
    	$display_party[ "display_price$num"] = $display_price;
    	return true;
    }

    static function check_booking_permission($course_id, $user_id) {
    	$db = JFactory::getDBO();
    	$mainframe = JFactory::getApplication();
    	$params = $mainframe->getParams('com_seminarman');
    	
    	$date = JFactory::getDate()->format("Y-m-d");
    	
    	// categories, which the course belongs to
    	$query = $db->getQuery(true);
    	$query->select( 'DISTINCT c.id' );
    	$query->from( '`#__seminarman_categories` AS c' );
    	$query->join( "left", '#__seminarman_cats_course_relations AS rel ON rel.catid = c.id' );
    	$query->where( "rel.courseid=" . $course_id );
    	
    	$db->setQuery($query);
    	$categories = $db->loadColumn();
    	
    	$query = $db->getQuery(true);
    	$query->select( '*' );
    	$query->from( '`#__seminarman_courses`' );
    	$query->where( "id=" . $course_id ); 
    	$db->setQuery($query);
    	$course = $db->loadObject();
    	
    	$course_start = $course->start_date . ' ' . $course->start_time;
    	$course_finish = $course->finish_date . ' ' . $course->finish_time;
    	
    	if ($params->get('enable_bookings') > 1) {  // all users allowed to book
    	    return true;	
    	} elseif ($params->get('enable_bookings') == 1) { // only registered users
    		$query = $db->getQuery(true);
    		$query->select( 'rule_text' );
    		$query->from( '`#__seminarman_user_rules`' );
    		$query->where( "user_id=".$user_id );
    		$query->where( "published=1" );
    		$query->where( "archived=0" );
    		$query->where( "rule_type=1" );
    		$query->where( "rule_option='category'" );
    		$db->setQuery($query);
    		
    		$user_rules = $db->loadColumn();
    		foreach ($user_rules as $user_rule_text) {
    		   //initial
               $category_ok = false;
               $date_ok = false;
               $amount_ok = false;
    			
    		   $user_rule = json_decode($user_rule_text);
    		   $booked_amount = JHTMLSeminarman::get_user_booking_total_in_category_rule($user_rule->category, $user_id, $user_rule->start_date, $user_rule->finish_date);
    		   
    		   if($user_rule->category == 0) {  // user is allowed to book in all categories
    		   	  $category_ok = true;
    		   } elseif (in_array($user_rule->category, $categories)) {  // allowed category in this rule matches one category of the course
    		   	  $category_ok = true;
    		   }
    		   
    		   if(trim($user_rule->start_date) == '') {  // no limit for start date
    		   	  if(trim($user_rule->finish_date) == '') { // no limit for finish date
    		   	  	$date_ok = true;
    		   	  } else {
    		   	  	if (strtotime($course_finish) <= strtotime(trim($user_rule->finish_date))) {
    		   	  		$date_ok = true;
    		   	  	}
    		   	  }
    		   } else {
    		   	  if (strtotime($course_start) >= strtotime(trim($user_rule->start_date))) {
    		   	  	if (trim($user_rule->finish_date) == '') {
    		   	  		$date_ok = true;
    		   	  	} else {
    		   	  		if (strtotime($course_finish) <= strtotime(trim($user_rule->finish_date))) {
    		   	  			$date_ok = true;
    		   	  		}
    		   	  	}	
    		   	  }
    		   }
    		   
    		   if(trim($user_rule->amount) == '') {
    		   	  $amount_ok = true;
    		   } elseif ($booked_amount < trim($user_rule->amount)) {
    		   	  $amount_ok = true;
    		   }
    		   
    		   if ($category_ok && $date_ok && $amount_ok) return true;
    		}
    		return false;
    	} else {
    		return false;
    	}
    }
    
    static function get_first_used_booking_rule($course_id, $user_id) {
    	$db = JFactory::getDBO();
    	$mainframe = JFactory::getApplication();
    	$params = $mainframe->getParams('com_seminarman');
    	 
    	$date = JFactory::getDate()->format("Y-m-d");
    	 
    	// categories, which the course belongs to
    	$query = $db->getQuery(true);
    	$query->select( 'DISTINCT c.id' );
    	$query->from( '`#__seminarman_categories` AS c' );
    	$query->join( "LEFT", "#__seminarman_cats_course_relations AS rel ON rel.catid = c.id" );
    	$query->where( "rel.courseid=" . $course_id );
    	$db->setQuery( $query );
    	$categories = $db->loadColumn();
    	
    	$query = $db->getQuery(true);
    	$query->select( '*' );
    	$query->from( '`#__seminarman_courses`' );
    	$query->where( "id=" . $course_id );
    	$db->setQuery( $query );
    	$course = $db->loadObject();
    	
    	$course_start = $course->start_date . ' ' . $course->start_time;
    	$course_finish = $course->finish_date . ' ' . $course->finish_time;
    	 
    	if ($params->get('enable_bookings') > 1) {  // all users allowed to book
    		return true;
    	} elseif ($params->get('enable_bookings') == 1) { // only registered users
    		$query = $db->getQuery(true);
    		$query->select( 'title, rule_text' );
    		$query->from( '`#__seminarman_user_rules`' );
    		$query->where( "user_id=".$user_id );
    		$query->where( "published=1" );
    		$query->where( "archived=0" );
    		$query->where( "rule_type=1" );
    		$query->where( "rule_option='category'" );
    		$db->setQuery( $query );
    		$user_rules = $db->loadAssocList();
    		
    		foreach ($user_rules as $user_rule_row) {
    			//initial
    			$category_ok = false;
    			$date_ok = false;
    			$amount_ok = false;
    			 
    			$user_rule = json_decode($user_rule_row['rule_text']);
    			$booked_amount = JHTMLSeminarman::get_user_booking_total_in_category_rule($user_rule->category, $user_id, $user_rule->start_date, $user_rule->finish_date);
    				
    			if($user_rule->category == 0) {  // user is allowed to book in all categories
    				$category_ok = true;
    			} elseif (in_array($user_rule->category, $categories)) {  // allowed category in this rule matches one category of the course
    				$category_ok = true;
    			}
    				
    			if(trim($user_rule->start_date) == '') {  // no limit for start date
    				if(trim($user_rule->finish_date) == '') { // no limit for finish date
    					$date_ok = true;
    				} else {
    					if (strtotime($course_finish) <= strtotime(trim($user_rule->finish_date))) {
    						$date_ok = true;
    					}
    				}
    			} else {
    				if (strtotime($course_start) >= strtotime(trim($user_rule->start_date))) {
    					if (trim($user_rule->finish_date) == '') {
    						$date_ok = true;
    					} else {
    						if (strtotime($course_finish) <= strtotime(trim($user_rule->finish_date))) {
    							$date_ok = true;
    						}
    					}
    				}
    			}
    				
    			if(trim($user_rule->amount) == '') {
    				$amount_ok = true;
    			} elseif ($booked_amount <= trim($user_rule->amount)) {   // important "<=" statt "<"
    				$amount_ok = true;
    			}
    			
    			if ($category_ok && $date_ok && $amount_ok) {
    				// return the found rule
    				$found_rule = array();
    				$found_rule['category'] = $user_rule->category;
    				$found_rule['amount'] = $user_rule->amount;
    				$found_rule['start_date'] = $user_rule->start_date;
    				$found_rule['finish_date'] = $user_rule->finish_date;
    				$found_rule['title'] = $user_rule_row['title'];
    				$found_rule['booked'] = $booked_amount;
    				return $found_rule;
    			}
    		}
    		return false;
    	} else {
    		return false;
    	}
    }
    
    static function get_user_booking_total_in_category($category_id, $user_id) {
    	$db = JFactory::getDBO();
    	$mainframe = JFactory::getApplication();
    	$params = $mainframe->getParams('com_seminarman');
    	
    	$statquery = SeminarmanFunctions::getStatusQuery( 'a.status');

    	$query = $db->getQuery(true);
    		$query->select( 'COUNT(a.id)' );
    	    $query->from( '`#__seminarman_application` AS a' );
    	
    	if ($category_id > 0) {
    	    $query->join( "LEFT", "`#__seminarman_cats_course_relations` AS c ON (a.course_id = c.courseid)" );
    	    $query->where( 'c.catid = ' . (int)$category_id );  
    	}
    	
    	$query->where( 'a.user_id = '. (int)$user_id );
    	$query->where( 'a.published = 1 AND '.$statquery );
    	
    	$db->setQuery( $query );
    	return (int)$db->loadResult();
    }

    static function get_user_booking_total_in_category_rule($category_id, $user_id, $start_date, $finish_date) {  // with begin & finish date params
    	$db = JFactory::getDBO();
    	$statquery = SeminarmanFunctions::getStatusQuery( 'a.status');

    	$query = $db->getQuery(true);
    	$query->select( 'COUNT(a.id)' );
    	$query->from( '`#__seminarman_application` AS a' );
    	 
    	if ($category_id > 0) {
    		$query->join( "LEFT", "`#__seminarman_cats_course_relations` AS c ON (a.course_id = c.courseid)" );
    		$query->where( 'c.catid = ' . (int)$category_id );
    	}
    	 
    	$query->where( 'a.user_id = '. (int)$user_id );
    	$query->where( 'a.published = 1' );
    	$query->where( $statquery );
    	 
    	$db->setQuery( $query );
    	$total = (int)$db->loadResult();

    	// course date controlling
    	$query = $db->getQuery(true);
    	$query->select( 'CONCAT_WS(" ", c.start_date, c.start_time) AS start, CONCAT_WS(" ", c.finish_date, c.finish_time) AS finish' );
    	$query->from( '`#__seminarman_application` AS a' );
    		$query->join( "LEFT", "`#__seminarman_courses` AS c ON a.course_id = c.id" );
    	
    	if ($category_id > 0) {
    		$query->join( "LEFT", "`#__seminarman_cats_course_relations` AS r ON c.id = r.courseid" );
    		$query->where( 'r.catid = ' . (int)$category_id );
    	}
    	
    	$query->where( 'a.user_id = '. (int)$user_id );
    	$query->where( 'a.published = 1' );
    	$query->where( $statquery );

    	$db->setQuery( $query );
    	$bookingdetails = $db->loadObjectList();

    	$rule_date_case = 0;
    	if (trim($start_date) == '') {
    		if (trim($finish_date) == '') {
    			$rule_date_case = 1;
    		} else {
    			$rule_date_case = 2;
    		}
    	} else {
    		if (trim($finish_date) == '') {
    			$rule_date_case = 3;
    		} else {
    			$rule_date_case = 4;
    		}
    	}    	
    	
    	foreach($bookingdetails as $detail) {
    	    switch ($rule_date_case) {
            	case 1:  // Zeitraum unbegrenzt in der Regel
            		// do nothing
            		break;
            	case 2:  // Nur Enddatum defined in der Regel
            		if (strtotime($detail->finish) > strtotime($finish_date)) $total--;
            		break;
            	case 3: // Nur Startdatum defined in der Regel
            		if (strtotime($detail->start) < strtotime($start_date)) $total--;
            		break;
            	case 4: // Start- und Enddatum defined in der Regel
            		if ((strtotime($detail->start) < strtotime($start_date)) || (strtotime($detail->finish) > strtotime($finish_date))) $total--;
            		break;
            }
    	}
    	
    	return $total;
    }        
    
    static function get_user_booking_total_in_category_rule_protocoll($category_id, $user_id, $start_date, $finish_date) {  // with begin & finish date params
    	$db = JFactory::getDBO();
    	$statquery = SeminarmanFunctions::getStatusQuery( 'a.status' );

    	$query = $db->getQuery(true);
    	$query->select( 'COUNT(a.id)' );
    	$query->from( '`#__seminarman_application` AS a' );
    	 
    	if ($category_id > 0) {
    		$query->join( "LEFT", "`#__seminarman_cats_course_relations` AS c ON (a.course_id = c.courseid)" );
    		$query->where( 'c.catid = ' . (int)$category_id );
    	}
    	 
    	$query->where( 'a.user_id = '. (int)$user_id );
    	$query->where( 'a.published = 1' );
    	$query->where( $statquery );
    	 
    	$db->setQuery( $query );
    	$total = (int)$db->loadResult();

    	// booking date controlling
    	$query = $db->getQuery(true);
    	$query->select( 'a.params' );
    	$query->from( '`#__seminarman_application` AS a' );
    	$query->join( "LEFT", "`#__seminarman_cats_course_relations` AS c ON a.course_id = c.courseid" );
    	
    	if ($category_id > 0) {
    		$query->where( 'c.catid = ' . (int)$category_id );
    	}
    	
    	$query->where( 'a.user_id = '. (int)$user_id );
    	$query->where( 'a.published = 1' );
    	$query->where( $statquery );
    	 
    	$db->setQuery( $query );
    	$bookingdetails = $db->loadObjectList();

    	$protocols = array();
    	foreach($bookingdetails as $detail) {
    		$detail_obj = json_decode($detail->params);
    		if (isset($detail_obj->protocols)) {
    			$protocols[] = $detail_obj->protocols;
    		}
    	}
    	
    	$rule_date_case = 0;
    	if (trim($start_date) == '') {
    		if (trim($finish_date) == '') {
    			$rule_date_case = 1;
    		} else {
    			$rule_date_case = 2;
    		}    		
    	} else {
    		if (trim($finish_date) == '') {
    			$rule_date_case = 3;
    		} else {
    			$rule_date_case = 4;
    		}    		
    	}
        
    	foreach($protocols as $protocol) {
    		$booking_infos = json_decode($protocol);
            $first_booking_info = $booking_infos[0];
            $first_booking_date = date('Y-m-d', strtotime($first_booking_info->date));
            
            switch ($rule_date_case) {
            	case 1:  // Zeitraum unbegrenzt in der Regel
            		// do nothing
            		break;
            	case 2:  // Nur Enddatum defined in der Regel
            		if (strtotime($first_booking_date) > strtotime($finish_date)) $total--;
            		break;
            	case 3: // Nur Startdatum defined in der Regel
            		if (strtotime($first_booking_date) < strtotime($start_date)) $total--;
            		break;
            	case 4: // Start- und Enddatum defined in der Regel
            		if ((strtotime($first_booking_date) < strtotime($start_date)) || (strtotime($first_booking_date) > strtotime($finish_date))) $total--;
            		break;
            }
    	}
    	
    	return $total;
    }
    
    /**
     * Generate User Input Select
     *
     * @param int $userId
     */
    public static function getUserInput_oldie($userId)
    {
    	// Initialize variables.
    	$html = array();
    	$link = 'index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;field=user_id';
    	// Initialize some field attributes.
    	$attr = ' class="inputbox"';
    	// Load the modal behavior script.
    	JHtml::_('behavior.modal', 'a.modal_user_id');
    	// Build the script.
    	$script   = array();
    	$script[] = '	function jSelectUser_user_id(id, title) {';
    	$script[] = '		var old_id = document.getElementById("juser_id").value;';
    	$script[] = '		if (old_id != id) {';
    	$script[] = '			document.getElementById("juser_id").value = id;';
    	$script[] = '			document.getElementById("juser_id_name").value = title;';
    	$script[] = '		}';
    	$script[] = '		SqueezeBox.close();';
    	$script[] = '	}';
    	// Add the script to the document head.
    	JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
    	// Load the current username if available.
    	$table = JTable::getInstance('user');
    	if ($userId)
    	{
    		$table->load($userId);
    	}
    	else
    	{
    		$table->username = JText::_('COM_SEMINARMAN_USER_NAME');
    	}
    	// Create a dummy text field with the user name.
    	$html[] = '<div class="fltlft">';
    	$html[] = '	<input type="text" id="juser_id_name"' . ' value="' . htmlspecialchars($table->name, ENT_COMPAT, 'UTF-8') . '"' .
    			' disabled="disabled"' . $attr . ' />';
    	$html[] = '</div>';
    	// Create the user select button.
    	$html[] = '<div class="button2-left">';
    	$html[] = '<div class="blank">';
    	$html[] = '<a class="modal_user_id" title="' . JText::_('JLIB_FORM_CHANGE_USER') . '"' . ' href="' . $link . '"' .
    			' rel="{handler: \'iframe\', size: {x: 800, y: 500}}">';
    	$html[] = '	' . JText::_('JLIB_FORM_CHANGE_USER') . '</a>';
    	$html[] = '</div>';
    	$html[] = '</div>';
    	// Create the real field, hidden, that stored the user id.
    	$html[] = '<input type="hidden" id="juser_id" name="juser_id" value="' . $userId . '" />';
    
    	return implode("\n", $html);
    }
    
    /**
     * Generate User Input Select
     *
     * @param int $userId
     *
     * @return string
     */
    public static function getUserInput($userId)
    {
    	$field = JFormHelper::loadFieldType('User');
    
    	$element = new SimpleXMLElement('<field />');
    	$element->addAttribute('name', 'juser_id');
    	$element->addAttribute('class', 'readonly');

    	$field->setup($element, $userId);
    
    	return $field->input;
    }
    
    public static function getInvoiceNumber()
    {
    	$db = JFactory::getDBO();
    	$params = JComponentHelper::getParams('com_seminarman');
    	$db->setQuery( 'LOCK TABLES `#__seminarman_invoice_number` WRITE' );
        $db->execute();
    	
    	$query = $db->getQuery(true);
        $fields = array( $db->quoteName( 'number' ). ' = GREATEST(number+1,'.(int)$params->get( 'invoice_number_start' ) . ')' );
        $query->update( $db->quoteName( '#__seminarman_invoice_number' ) )->set( $fields );
        $db->setQuery($query);
        $db->execute();
    	
    	$query = $db->getQuery(true);
    	$query->select( 'number' );
    	$query->from( '`#__seminarman_invoice_number`' );
    	$db->setQuery( $query );
    	$next = $db->loadResult();
    
    	$db->setQuery( 'UNLOCK TABLES' );
        $db->execute();
    
    	return $next;
    }

    /**
     * Method to set an invoice
     *
     * @access	public
     * @return	boolean	True on success
     * @since	1.0
     */
    static function setInvoice($cid, $iprefix, $inumber){
    	$user = JFactory::getUser();
    	$db = JFactory::getDBO();
    
    	if ( $cid ){
    		$query = $db->getQuery(true);
    		$fields = array( $db->quoteName( "invoice_filename_prefix" ) . " = '" . $iprefix . "'",
    				$db->quoteName( "invoice_number" ) . " = " . $inumber );
    		$conditions = array( $db->quoteName('id') . ' = ' . (int)$cid,
    				'(' . $db->quoteName( 'checked_out' ) . ' = 0 OR ( '. $db->quoteName( 'checked_out' ) . ' = '. (int) $user->get('id'). ' ) )'
    		);
    		
    		$query->update( $db->quoteName( '#__seminarman_application' ) )->set( $fields )->where( $conditions );
    		$db->setQuery($query);
    		
    		if (!$db->execute()) {
    			JError::raiseError(500, $db->stderr(true));
    			return false;
    		}
    	}
    	return true;
    }

    static function createInvoice ( $cid ) {
    
    	require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'pdfdocument.php';
    	require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'pdftemplate.php';
    	require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS .
    			'helpers' . DS . 'seminarman.php');
    	$params = JComponentHelper::getParams('com_seminarman');
    
    	$pdftemplateModel = new seminarmanModelPdftemplate;
    
    	$db = JFactory::getDBO();
    	$query = $db->getQuery(true);
    	$query->select( '*' );
    	$query->from( '`#__seminarman_application`' );
    	$query->where( 'id ='. $cid );
    	$db->setQuery( $query );
    	
    	$app = $db->loadObject();

    	$query = $db->getQuery(true);
    	$query->select( '*' );
    	$query->from( '`#__seminarman_courses`' );
    	$query->where( 'id ='. $app->course_id );
    	$db->setQuery( $query );
    	
    	$courseRows = $db->loadObject();
    	
    	if ( !isset( $courseRows ) ) {
    		return false;
    	}
    
    	if (!$template = $pdftemplateModel->getTemplate( $courseRows->invoice_template ))
    		return false;
    
    	$invoice_filename_prefix = strtolower(str_replace(' ', '_', JText::_('COM_SEMINARMAN_INVOICE_PREFIX'))) . '_';
    	$invoice_number = JHTMLSeminarman::getInvoiceNumber();
    
    	JHTMLSeminarman::setInvoice( $cid, $invoice_filename_prefix, $invoice_number );
    
    	$templateData = JHTMLSeminarman::getFieldValuesForTemplate( $cid );
    	// please keep the following lines, invoice date has to be set to now (local time), not the application date
    	$site_timezone = JFactory::getUser()->getParam('timezone', JFactory::getConfig()->get('offset'));
    	$templateData[ 'INVOICE_DATE' ] = JFactory::getDate()->setTimezone(new DateTimeZone($site_timezone))->format( JText::_( 'COM_SEMINARMAN_DATE_FORMAT1' ) );
    
    	$pdf = new PdfInvoice($template, $templateData);
    	$pdf->store(JPATH_ROOT.DS.$params->get('invoice_save_dir').DS.$invoice_filename_prefix.$invoice_number.'.pdf');
    
    	return $pdf->getFile();
    }
    
    static function checkInvoiceNotThere ( $cid ) {
    
    	$db = JFactory::getDBO();

    	$query = $db->getQuery(true);
    	$query->select( 'invoice_filename_prefix, invoice_number' );
    	$query->from( '`#__seminarman_application`' );
    	$query->where( 'id = ' . $cid );
    	$db->setQuery( $query );
    	$app = $db->loadObject();

    	if ( ( isset( $app->invoice_filename_prefix ) && ( $app->invoice_filename_prefix != '' ) ) || ( $app->invoice_number > 0 ) ) {
    		return false;
    	}
    	return true;
    }
    
    static function cEscape($var, $function='htmlspecialchars') {
	    if (in_array($function, array('htmlspecialchars', 'htmlentities'))) {
			return call_user_func($function, $var, ENT_COMPAT, 'UTF-8');
		}
		return call_user_func($function, $var);
	}
	
	static function buildSideMenu() {
		
		$params = JComponentHelper::getParams('com_seminarman');
		
		$active = array();
		$active['seminarman'] = false;
		$active['applications'] = false;
		$active['salesprospects'] = false;
		$active['courses'] = false;
		$active['templates'] = false;
		$active['categories'] = false;
		$active['tags'] = false;
		$active['tutors'] = false;
		$active['tutor'] = false;
		$active['users'] = false;
		$active['settings'] = false;
		
		$view_active = JRequest::getVar('view', 'seminarman');
		$active[$view_active] = true;
		
		if( JHTMLSeminarman::UserIsCourseManager() ){
			JSubMenuHelper::addEntry(JText::_('COM_SEMINARMAN_HOME'), 'index.php?option=com_seminarman', $active['seminarman']);
			JSubMenuHelper::addEntry(JText::_('COM_SEMINARMAN_APPLICATIONS'),'index.php?option=com_seminarman&view=applications', $active['applications']);
			JSubMenuHelper::addEntry(JText::_('COM_SEMINARMAN_LST_OF_SALES_PROSPECTS'), 'index.php?option=com_seminarman&view=salesprospects', $active['salesprospects']);
			JSubMenuHelper::addEntry(JText::_('COM_SEMINARMAN_COURSES'),'index.php?option=com_seminarman&view=courses', $active['courses']);
			JSubMenuHelper::addEntry(JText::_('COM_SEMINARMAN_TEMPLATES'),'index.php?option=com_seminarman&view=templates', $active['templates']);
			JSubMenuHelper::addEntry(JText::_('COM_SEMINARMAN_CATEGORIES'),'index.php?option=com_seminarman&view=categories', $active['categories']);
			JSubMenuHelper::addEntry(JText::_('COM_SEMINARMAN_TAGS'),'index.php?option=com_seminarman&view=tags', $active['tags']);
			JSubMenuHelper::addEntry(JText::_('COM_SEMINARMAN_TUTORS'),'index.php?option=com_seminarman&view=tutors', $active['tutors']);
			JSubMenuHelper::addEntry(JText::_('COM_SEMINARMAN_USERS'),'index.php?option=com_seminarman&view=users', $active['users']);
			JSubMenuHelper::addEntry(JText::_('COM_SEMINARMAN_SETTINGS'),'index.php?option=com_seminarman&view=settings', $active['settings']);
		} else {
			JSubMenuHelper::addEntry(JText::_('COM_SEMINARMAN_HOME'), 'index.php?option=com_seminarman', $active['seminarman']);
			JSubMenuHelper::addEntry(JText::_('COM_SEMINARMAN_APPLICATIONS'),'index.php?option=com_seminarman&view=applications', $active['applications']);
			if ($params->get('tutor_access_sales_prospects')) JSubMenuHelper::addEntry(JText::_('COM_SEMINARMAN_LST_OF_SALES_PROSPECTS'), 'index.php?option=com_seminarman&view=salesprospects', $active['salesprospects']);
			JSubMenuHelper::addEntry(JText::_('COM_SEMINARMAN_COURSES'),'index.php?option=com_seminarman&view=courses', $active['courses']);
			if ($params->get('tutor_access_tags')) JSubMenuHelper::addEntry(JText::_('COM_SEMINARMAN_TAGS'),'index.php?option=com_seminarman&view=tags', $active['tags']);
			if ($params->get('tutor_access_tutors')) {
				$tutor_id = JHTMLSeminarman::getUserTutorID();
				JSubMenuHelper::addEntry(JText::_('COM_SEMINARMAN_TUTOR'),'index.php?option=com_seminarman&controller=tutor&task=edit&cid[]='.$tutor_id, $active['tutor']);
			}
		}
	}
    
}

class SeminarmanFunctions {
	
    static function isVMEnabled(){
        $db = JFactory::getDbo();
        
        $query = $db->getQuery(true);
        $query->select( 'enabled' );
        $query->from( '`#__extensions`' );
        $query->where( "name = 'virtuemart'" );
        $db->setQuery( $query );
        
        $vm_enabled = ($db->loadResult() == 1);
        if(!$vm_enabled){
            return false;
        }
        return true;
    }
    
    static function isVMEngineEnabled() {
        $db = JFactory::getDbo();
        
        $query = $db->getQuery(true);
        $query->select( 'enabled' );
        $query->from( '`#__extensions`' );
        $query->where( "name = 'plg_seminarman_vmengine'" );
        $db->setQuery( $query );
        
        $vmengine_enabled = ($db->loadResult() == 1);
        if(!$vmengine_enabled){
            return false;
        }
        return true;
    }
    
    static function isVMSMPlgEnabled() {
    	$db = JFactory::getDbo();
    	
    	$query = $db->getQuery(true);
    	$query->select( 'enabled' );
    	$query->from( '`#__extensions`' );
    	$query->where( "name = 'plg_vmcustom_smansync'" );
    	$db->setQuery( $query );
    	
    	$vmsmplg_enabled = ($db->loadResult() == 1);
    	if(!$vmsmplg_enabled){
    		return false;
    	}
    	return true;
    }
    
    static function isSmanbookingPlgEnabled() {
    	$db = JFactory::getDbo();
    	 
    	$query = $db->getQuery(true);
    	$query->select( 'enabled' );
    	$query->from( '`#__extensions`' );
    	$query->where( "element = 'smanbooking'" );
    	$db->setQuery( $query );
    	 
    	$smbookingplg_enabled = ($db->loadResult() == 1);
    	if(!$smbookingplg_enabled){
    		return false;
    	} else {
    		return true;
    	}
    	return true;
    }
    
    static function isSmanwaitingPlgEnabled() {
    	$db = JFactory::getDbo();
    	
    	$query = $db->getQuery(true);
    	$query->select( 'enabled' );
    	$query->from( '`#__extensions`' );
    	$query->where( "element = 'smanwaiting'" );
    	$db->setQuery( $query );
    	
    	$smwaitingplg_enabled = ($db->loadResult() == 1);
    	if(!$smwaitingplg_enabled){
    		return false;
    	} else {
    		return true;
    	}
    	return true;
    }
    
    static function isSmanpdflistPlgEnabled() {
    	$db = JFactory::getDbo();
    	 
    	$query = $db->getQuery(true);
    	$query->select( 'enabled' );
    	$query->from( '`#__extensions`' );
    	$query->where( "element = 'smanpdflist'" );
    	$db->setQuery( $query );
    	 
    	$smpdflistplg_enabled = ($db->loadResult() == 1);
    	if(!$smpdflistplg_enabled){
    		return false;
    	} else {
    		return true;
    	}
    	return true;
    }
    
    static function sendReminderEmails($sending_list, $bccEmail) {
    	$db     = JFactory::getDbo();
    	$mailer = JFactory::getMailer();
    	if ($bccEmail)
    	{
    		$mailer->AddBCC($bccEmail);
    	}
    	$fromName = JFactory::getConfig()->get('fromname');
    	$fromEmail = JFactory::getConfig()->get('mailfrom');
    	foreach($sending_list as $target) {
    		$target_rule_detail = json_decode($target->rule_text);
    		$booked = JHTMLSeminarman::get_user_booking_total_in_category_rule($target_rule_detail->category, $target->id, $target_rule_detail->start_date, $target_rule_detail->finish_date);
    		$total = $target_rule_detail->amount;
    		if (trim($total) <> '') {
    			$available = $total - $booked;
    		} else {
    			$available = JText::_('COM_SEMINARMAN_REMINDER_EMAIL_UNLIMITED');
    		}
    		// we are sure this function will be called only if the expire date (finish_date) exists
    		$now = JFactory::getDate()->format('Y-m-d');
    		$expire_days = (strtotime($target_rule_detail->finish_date)-strtotime($now))/(60 * 60 * 24);
    		$subject = JText::sprintf('COM_SEMINARMAN_REMINDER_EMAIL_SUBJECT', $target->title, $expire_days);
    		$body = JText::sprintf('COM_SEMINARMAN_REMINDER_EMAIL_BODY', $target->name, $target->title, $expire_days, $available);
    		
    		$mailer->sendMail($fromEmail, $fromName, $target->email, $subject, $body, 1);
    		
    		$att_obj = json_decode($target->attribs);
    		if(is_null($att_obj)) $att_obj = new stdClass;
    		$att_obj->remind_date = time();
    		$att_json = json_encode($att_obj);
    		
    		$query = $db->getQuery(true);
    		 
    		$fields = array( $db->quoteName('attribs'). ' = \''.$att_json.'\'' );
    		$conditions = array( $db->quoteName('id') . ' = ' . (int) $target->rule_id );
    		 
    		$query->update( $db->quoteName( '#__seminarman_user_rules' ) )->set( $fields )->where( $conditions );
    		 
    		$db->setQuery( $query );
    		$db->execute();
			$mailer->ClearAddresses();
    	}
    	
    	return true;
    }
    
    
    // the function below should be used by saving a datetime input in database.
    // in joomla all datetime should be saved as utc in database, regardless of how your web server
    // or database server is configurated.
    // therefore, be sure of your datetime input with the consideration of your joomla timezone setting
    static function formatDateToSQL($input) {

    	if (empty($input) || !((int) $input > 0)) {
    		$input = '0000-00-00';
    	}
    	
    	// Get the user timezone setting defaulting to the server timezone setting.
    	$offset = JFactory::getUser()->getParam('timezone', JFactory::getConfig()->get('offset'));

    	// Return a MySQL formatted datetime string in UTC.
    	$return = JFactory::getDate($input, $offset)->toSql();
    	return $return;
    }
    
    // unfortunately in some tables the date and the time are saved seperately and they should be in utc
    static function formatDateToSQLParts($date_part, $time_part, $globaltz = false) {
    	
    	if ( !(preg_match('#^(([0-1][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?)$#', $time_part)) ) {
    		$time_part = '';
    	}
    	
    	if (empty($date_part) || $date_part == '0000-00-00' || !((int) $date_part > 0)) {
    		return array('0000-00-00', $time_part);
    	}
    	 
    	if ($globaltz == true) {
    	  // in some cases, for example all-day event, we have to use gloabal timezone only
    	  $offset = JFactory::getConfig()->get('offset');
    	} else {
    	  // Get the user timezone setting defaulting to the server timezone setting.
    	  $offset = JFactory::getUser()->getParam('timezone', JFactory::getConfig()->get('offset'));
    	}
    	
    	// fix for illegal format 24:00:00
    	if ($time_part == "24:00:00") $time_part = "23:59:59";
    	 
    	// Return a MySQL formatted datetime string in UTC.
    	$input = $date_part . ' ' . $time_part;
    	$utc_form = JFactory::getDate($input, $offset)->toSql();
    	
    	$utc_array = explode(" ", $utc_form);
    	 
    	return $utc_array;
    }
    
    // unfortunately in some tables the date and the time are saved seperately and they are in utc
    static function formatUTCtoLocal ($date_part, $time_part) {
    	if (empty($date_part) || $date_part == '0000-00-00') {
    		return array('0000-00-00', $time_part);
    	}
    	// fix for 24:00:00 (illegal time colock)
    	if ($time_part == '24:00:00') $time_part = '23:59:59';
    	$combined_utc = $date_part . ' ' . $time_part;   	
    	$combined_local = JHtml::_('date', $combined_utc, 'Y-m-d H:i:s');    	
    	$local_array = explode(" ", $combined_local);
    	
    	return $local_array;
    }
    
    static function getSiteTimezone($globalonly = false) {
    	// Current Joomla Site Timezone
    	// if user timezone is set, use that timezone
    	// if user timezone not set, use joomla global timezone
    	// frontend public site use joomla global timezone, if no one logged in
    	 if ($globalonly) {
    	   return JFactory::getConfig()->get('offset');
    	 } else {
    	   return JFactory::getUser()->getParam('timezone', JFactory::getConfig()->get('offset'));
    	 }
    }
    
    static function getStatusQuery( $statname ) {
    	$query = ' (( '.$statname .' < 3 ) ';
    	$params = JComponentHelper::getParams('com_seminarman');
    	if ( $params->get('waitinglist_active') ) {
    		$query .= 'OR ( '.$statname.' = 4 ) OR ( '.$statname.' = 5 )';
    	}
    	$query .= ')';
    	
    	return $query;		
    }
    
    static function setCertificate($cid, $iprefix, $inumber){
    	$user = JFactory::getUser();
    	$db = JFactory::getDBO();
    	 
    	if ( $cid ){
    		$query = $db->getQuery(true);
    		 
    		$fields = array( $db->quoteName('certificate_file'). " = '". $iprefix.$inumber.".pdf'" );
    		$conditions = array( $db->quoteName('id') . ' = ' . (int)$cid,
    				'( checked_out = 0 OR ( checked_out = '. (int) $user->get('id'). ' ) )'
    		);
    		 
    		$query->update( $db->quoteName( '#__seminarman_application' ) )->set( $fields )->where( $conditions );
    		 
    		$db->setQuery( $query );
    		 
    		if (!$db->execute()) {
    			JError::raiseError(500, $db->stderr(true));
    			return false;
    		}
    	}
    	return true;
    }
    
    static function createCertificate($cid) {
    
    	require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'pdfdocument.php';
    	require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'pdftemplate.php';
    	require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS . 'helpers' . DS . 'seminarman.php');
    	$params = JComponentHelper::getParams('com_seminarman');
    
    	$pdftemplateModel = new seminarmanModelPdftemplate;
    
    	$db = JFactory::getDBO();
    	
    	$query = $db->getQuery(true);
    	$query->select( '*' );
    	$query->from( '`#__seminarman_application`' );
    	$query->where( 'id ='. $cid );
    	$db->setQuery( $query );
    	$app = $db->loadObject();
    	 
    	$uid = $app->user_id;
    	
    	$query = $db->getQuery(true);
    	$query->select( '*' );
    	$query->from( '`#__seminarman_courses`' );
    	$query->where( 'id ='. $app->course_id );
    	$db->setQuery( $query );
    
    	$courseRows = $db->loadObject();
    
    	if (!$template = $pdftemplateModel->getTemplateForCert($courseRows->certificate_template)) return false;
    
    	$certificate_filename_prefix = strtolower(str_replace(' ', '_', JText::_('COM_SEMINARMAN_CERTIFICATE'))) . '_';
    	$certificate_number = $uid.'_'.$cid;
    
    	$cer_update = SeminarmanFunctions::setCertificate( $cid, $certificate_filename_prefix, $certificate_number );
    	if (!$cer_update) return false;

    	$templateData = JHTMLSeminarman::getFieldValuesForTemplate( $cid );
    	// please keep the following lines, invoice date has to be set to now (local time), not the application date
    	$site_timezone = JFactory::getUser()->getParam('timezone', JFactory::getConfig()->get('offset'));
    	// pdf-param "INVOICE_DATE" used as certificate creat date
    	$templateData[ 'INVOICE_DATE' ] = JFactory::getDate()->setTimezone(new DateTimeZone($site_timezone))->format( JText::_( 'COM_SEMINARMAN_DATE_FORMAT1' ) );

    	$pdf = new PdfInvoice($template, $templateData);
    	$pdf->store(JPATH_ROOT.DS.$params->get('invoice_save_dir').DS.$certificate_filename_prefix.$certificate_number.'.pdf');
    
    	return true;
    }
    
    static function createCertificates() {
    
    	$cid = JRequest::getVar('cid', array(), 'post', 'array');
    	JArrayHelper::toInteger($cid);
    
    	if (count($cid) < 1)
    		JError::raiseError(500, JText::_('COM_SEMINARMAN_SELECT_ITEM'));
    
    		$cids = implode(',', $cid);
    		$msg = '';
    
    		foreach ( $cid as $c ) {
    				$ret = SeminarmanFunctions::createCertificate($c);
    				if ($ret) {
    				  $msg .= JText::_('COM_SEMINARMAN_CERTIFICATE_CREATED_SUCCESSFULLY').$c."<br />";
    				} else {
    				  $msg .= JText::_('COM_SEMINARMAN_CERTIFICATE_ERROR').$c."<br />";
    				}
    		}
    
    		$link = 'index.php?option=com_seminarman&view=applications';
    
    		JFactory::getApplication()->redirect($link, $msg);
    }
    
    static function setEmailAttachment($cid, $iprefix, $inumber){
    	$user = JFactory::getUser();
    	$db = JFactory::getDBO();
    
    	if ( $cid ){
    		$query = $db->getQuery(true);
    		 
    		$fields = array( $db->quoteName('extra_attach_file'). " = '". $iprefix.$inumber.".pdf'" );
    		$conditions = array( $db->quoteName('id') . ' = '.(int)$cid,
    				'( checked_out = 0 OR ( checked_out = '. (int) $user->get('id'). ' ) )'
    		);
    		 
    		$query->update( $db->quoteName( '#__seminarman_application' ) )->set( $fields )->where( $conditions );
    		 
    		$db->setQuery( $query );
    		if (!$db->execute()) {
    			JError::raiseError(500, $db->stderr(true));
    			return false;
    		}
    	}
    	return true;
    }
    
    static function createEmailAttachment($cid) {
    
    	require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'pdfdocument.php');
    	// require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'pdftemplate.php');
    	require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS . 'helpers' . DS . 'seminarman.php');
    	$params = JComponentHelper::getParams('com_seminarman');
    
    	// $pdftemplateModel = new seminarmanModelPdftemplate;
    	$pdftemplateModel = JModelLegacy::getInstance('Pdftemplate', 'seminarmanModel'); 
    
    	$db = JFactory::getDBO();
    	$query = $db->getQuery(true);
    	$query->select( '*' );
    	$query->from( '`#__seminarman_application`' );
    	$query->where( 'id ='. $cid );
    	$db->setQuery( $query );
    	$app = $db->loadObject();
    
    	$uid = $app->user_id;
    
    	$query = $db->getQuery(true);
    	$query->select( '*' );
    	$query->from( '`#__seminarman_courses`' );
    	$query->where( 'id ='. $app->course_id );
    	$db->setQuery( $query );
    	$courseRows = $db->loadObject();
    	
    	if (!isset( $courseRows )) {
    		JError::raiseError(500, $db->stderr(true));
    		return false;
    	}
    
    
    	if (!$template = $pdftemplateModel->getTemplateForAttach($courseRows->extra_attach_template)) return false;
    
    	$attach_filename_prefix = strtolower(str_replace(' ', '_', JText::_('COM_SEMINARMAN_EMAIL_ATTACHMENT_PREFIX'))) . '_';
    	$attach_number = $uid.'_'.$cid;
    
    	$cer_update = SeminarmanFunctions::setEmailAttachment( $cid, $attach_filename_prefix, $attach_number );
    	if (!$cer_update) return false;
    
    	$templateData = JHTMLSeminarman::getFieldValuesForTemplate( $cid );
    	// please keep the following lines, invoice date has to be set to now (local time), not the application date
    	$site_timezone = JFactory::getUser()->getParam('timezone', JFactory::getConfig()->get('offset'));
    	// pdf-param "INVOICE_DATE" used as certificate creat date
    	$templateData[ 'INVOICE_DATE' ] = JFactory::getDate()->setTimezone(new DateTimeZone($site_timezone))->format( JText::_( 'COM_SEMINARMAN_DATE_FORMAT1' ) );
    
    	$pdf = new PdfInvoice($template, $templateData);
    	$pdf->store(JPATH_ROOT.DS.$params->get('invoice_save_dir').DS.$attach_filename_prefix.$attach_number.'.pdf');
    	
    	$filename = $pdf->getFile();
    
    	if (empty($filename)) {
    		return false;
    	} else {
    		return $filename;
    	}
    }
    
    static function createEmailAttachments() {
    
    	$cid = JRequest::getVar('cid', array(), 'post', 'array');
    	JArrayHelper::toInteger($cid);
    
    	if (count($cid) < 1)
    		JError::raiseError(500, JText::_('COM_SEMINARMAN_SELECT_ITEM'));
    
    		$cids = implode(',', $cid);
    		$msg = '';
    
    		foreach ( $cid as $c ) {
    			$ret = SeminarmanFunctions::createEmailAttachment($c);
    			if ($ret) {
    				$msg .= JText::_('COM_SEMINARMAN_EMAIL_ATTACHMENT_CREATED_SUCCESSFULLY').$c."<br />";
    			} else {
    				$msg .= JText::_('COM_SEMINARMAN_EMAIL_ATTACHMENT_ERROR').$c."<br />";
    			}
    		}
    
    		$link = 'index.php?option=com_seminarman&view=applications';
    
    		JFactory::getApplication()->redirect($link, $msg);
    }    
}
