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

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * Seminarman Component Tags Controller
 */
class SeminarmanControllerImportexport extends SeminarmanController
{

	function exportcsv()
	{
		$config = JFactory::getConfig();
		$db = JFactory::getDBO();
		
		$jinput = JFactory::getApplication()->input;
		
		$from_date = JRequest::getVar('from_date');
		$to_date = JRequest::getVar('to_date');
		$type = JRequest::getVar('datatype');
		$course = JRequest::getInt('course', 0);
		$template = JRequest::getInt('template', 0);	

		$query = $db->getQuery(true);
		
		switch ($type) {
			case 'courses':
				$query->select( 't.*' );
        		$query->from( '#__seminarman_courses AS t' );

        		if ($course != 0)
	        		$query->where( 'id='.$course );
				break;
			case 'sessions':
				$query->select( 't.*' );
        		$query->from( '#__seminarman_sessions AS t' );
        		$query->join( "LEFT", '#__seminarman_courses AS c ON t.courseid = c.id' );
				if ($course != 0)
	        		$query->where( 'courseid='.$course );
					break;
			case 'applications':
			    
			    $attlst_status = $jinput->get('csv_app_stati', '0');
			    switch($attlst_status) {
			        case "-1":
			            $attlst_status_string = "IN(0,1)"; // submitted, pending
			            break;
			        case "0":
			            $attlst_status_string = "IN(1,2)"; // pending, paid
			            break;
			        case "0.5":
			            $attlst_status_string = "IN(2)"; // paid
			            break;
			        case "1":
			            $attlst_status_string = "IN(0,1,2)"; // submitted, pending, paid
			            break;
			        case "2":
			            $attlst_status_string = "IN(0,1,2,3)"; // submitted, pending, paid, canceled
			            break;
			        case "3":
			            $attlst_status_string = "IN(4,5)"; // waitinglist, awaiting response
			            break;
			        case "4":
			            $attlst_status_string = "IN(4)"; // waitinglist
			            break;
			        case "5":
			            $attlst_status_string = "IN(0,1,2,4,5)"; // all states except canceled
			            break;
			        case "6":
			            $attlst_status_string = "IN(0,1,2,3,4,5)"; // all states
			            break;
			        default:
			            $attlst_status_string = "IN(1,2)"; // pending, paid
			    }
			    
				$query->select( 'c.title AS course_title, c.start_date AS start_date_utc, t.*,'.
						'GROUP_CONCAT(f.fieldcode ORDER BY f.id SEPARATOR "|0$g|") AS custom_fieldcodes,'.
						'GROUP_CONCAT(v.value ORDER BY v.field_id SEPARATOR "|0$g|") AS custom_values' );
        		$query->from( '#__seminarman_application AS t' );
        		$query->join( "LEFT", '#__seminarman_courses AS c ON t.course_id = c.id' );
        		$query->join( "LEFT", '#__seminarman_fields_values AS v ON t.id = v.applicationid' );
        		$query->join( "LEFT", '#__seminarman_fields AS f ON f.id = v.field_id' );
        		$query->where('t.status '.$attlst_status_string);
				if ($course != 0)
					$query->where( 'course_id='.$course );
				$query->group( $db->quoteName( 't.id' ) );
				break;
			case 'salesprospects':
				$query->select( 't.*,'.
						'GROUP_CONCAT(f.fieldcode ORDER BY f.id SEPARATOR "|0$g|") AS custom_fieldcodes,'.
						'GROUP_CONCAT(v.value ORDER BY v.field_id SEPARATOR "|0$g|") AS custom_values' );
				$query->from( '#__seminarman_salesprospect AS t' );
				$query->join( "LEFT", '#__seminarman_fields_values_salesprospect AS v ON t.id = v.requestid' );
				$query->join( "LEFT", '#__seminarman_fields AS f ON f.id = v.field_id' );
				if ($template != 0)
					$query->where( 'template_id='.$template );
				$query->group( $db->quoteName( 't.id' ) );
				break;
			case 'templates':
				$query->select( 't.*' );
				$query->from( '#__seminarman_templates AS t' );
				if ($template != 0)
					$query->where( 'id='.$template );
				break;
			case 'tutors':
				$query->select( 't.*' );
				$query->from( '#__seminarman_tutor AS t' );
				break;
			default:
				$this->setRedirect('index.php?option=com_seminarman&view=importexport');
				return;
		}
		
		if ($type == 'courses')
		{
			if( $from_date != "" )
				$query->where( 'start_date >= "'.date("Y-m-d", strtotime($from_date)).'"' );
				
			if( $to_date != "" )
				$query->where( 'finish_date <= "'.date("Y-m-d", strtotime($to_date)).'"' );
		}
		else if ($type == 'sessions' || $type == 'applications')
		{
			$db->setQuery('SET SESSION group_concat_max_len=65536');
			$db->query();
				
			if( $from_date != "" )
				$query->where( 'c.start_date >= "'.date("Y-m-d", strtotime($from_date)).'"' );
			if( $to_date != "" )
				$query->where( 'c.finish_date <= "'.date("Y-m-d", strtotime($to_date)).'"' );
		}
		
		$db->setQuery($query);
		$data = $db->loadAssocList();
		
		if (!count($data)) {
			$this->setRedirect('index.php?option=com_seminarman&view=importexport', JText::_('COM_SEMINARMAN_EXPORT_NO_DATA'));
			return;
		}
		
		if ($type == 'applications' || $type == 'salesprospects') {
			foreach ($data as &$record) {
				if (!empty($record['custom_values'])) {
					// $record += array_combine(explode('|0$g|', $record['custom_fieldcodes']), explode('|0$g|', $record['custom_values']));
					$custom_array_keys = explode('|0$g|', $record['custom_fieldcodes']);
					$custom_array_values = explode('|0$g|', $record['custom_values']);
					$min_count = min(count($custom_array_keys), count($custom_array_values));
					$custom_array = array_combine(array_slice($custom_array_keys, 0, $min_count), array_slice($custom_array_values, 0, $min_count));
					$record = $record + $custom_array;
				}
				unset($record['custom_fieldcodes']);
				unset($record['custom_values']);
			}
		}
		
		$jversion = new JVersion();
		$short_version = $jversion->getShortVersion();
		if (version_compare($short_version, "3.0", 'ge')) {
			$tmpFileName = $config->get('tmp_path').DS.$type.'.csv';
		} else {
			$tmpFileName = $config->getValue('tmp_path').DS.$type.'.csv';
		}
		$this->_createTempFile($tmpFileName, $data);
	
		header("Content-Description: File Transfer");
		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment; filename=\"".basename($tmpFileName)."\"");
	
		flush();
		readfile ($tmpFileName);
		exit;
	}
	
	function _createTempFile($tmpFileName, $data)
	{
		$tmpfile = fopen($tmpFileName, "w");
	
		// filter out some fields
		$filter_fields = array (
				'plus', 'minus', 'bgcolor'
		);
	
		$towrite = '';
	
		// header
		$header = array();
		foreach ($data as $record) {
			foreach ($record as $field => $v) {
				$header[$field] = '';
			}
		}
		
		foreach ($filter_fields as $f) {
			unset($header[$f]);
		}
		
		$jinput = JFactory::getApplication()->input;
		$csv_separator = $jinput->get('csv_separator', ';', 'RAW');
			
		$towrite .= '"'.implode('"'.$csv_separator.'"', array_keys($header)).'"' . "\n";
			
		// escape and output records
		foreach ($data as &$record) {
			$row = $header;
			foreach ($record as $k => &$v) {
				$v = addcslashes($v, "\r\n");
				$v = str_replace('"', '""', $v);
				$row[$k] = $v;
			}
			
			$towrite .= '"'.implode('"'.$csv_separator.'"', $row).'"' . "\n";
		}
	
		fwrite($tmpfile, $towrite."\n");
	}
		
	function cancel()
	{
		JRequest::checkToken() or jexit('Invalid Token');
		$this->setRedirect('index.php?option=com_seminarman&view=settings');
	}
	
}