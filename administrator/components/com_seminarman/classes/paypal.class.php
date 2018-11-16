<?php

// no direct access
defined('_JEXEC') or die('Restricted access');
/*******************************************************************************
 *                      PHP Paypal IPN Integration Class
*******************************************************************************
*      Author:     Micah Carrick
*      Email:      email@micahcarrick.com
*      Website:    http://www.micahcarrick.com
*
*      File:       paypal.class.php
*      Version:    1.3.0
*      Copyright:  (c) 2005 - Micah Carrick
*                  You are free to use, distribute, and modify this software
*                  under the terms of the GNU General Public License.
*
*******************************************************************************
*  VERION HISTORY:
*      v1.3.0 [10.10.2005] - Fixed it so that single quotes are handled the
*                            right way rather than simple stripping them.  This
*                            was needed because the user could still put in
*                            quotes.
*
*      v1.2.1 [06.05.2005] - Fixed typo from previous fix :)
*
*      v1.2.0 [05.31.2005] - Added the optional ability to remove all quotes
*                            from the paypal posts.  The IPN will come back
*                            invalid sometimes when quotes are used in certian
*                            fields.
*
*      v1.1.0 [05.15.2005] - Revised the form output in the submit_paypal_post
*                            method to allow non-javascript capable browsers
*                            to provide a means of manual form submission.
*
*      v1.0.0 [04.16.2005] - Initial Version
*
*******************************************************************************
*  DESCRIPTION:
*
*      NOTE: See www.micahcarrick.com for the most recent version of this class
*            along with any applicable sample files and other documentaion.
*
*******************************************************************************
*/

class paypal_class {

	var $last_error;                 // holds the last error encountered

	var $ipn_log;                    // bool: log IPN results to text file?

	var $ipn_log_file;               // filename of the IPN log
	var $ipn_response;               // holds the IPN response from paypal
	var $ipn_data = array();         // array contains the POST values for IPN

	var $fields = array();           // array holds the fields to submit to paypal


	function __construct() {
			
		// initialization constructor.  Called when class is created.

		$this->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
		//$this->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';   // testing paypal url

		$this->last_error = '';

		$this->ipn_log_file = 'ipn_results.log';
		$this->ipn_log = false;
		$this->ipn_response = '';

		// populate $fields array with a few default values.  See the paypal
		// documentation for a list of fields and their data types. These defaul
		// values can be overwritten by the calling script.

		$this->add_field('rm','2');           // Return method = POST
		$this->add_field('cmd','_xclick');

	}

	function add_field($field, $value) {

		// adds a key=>value pair to the fields array, which is what will be
		// sent to paypal as POST variables.  If the value is already in the
		// array, it will be overwritten.
		$this->fields["$field"] = $value;
	}


	function validate_ipn() {

		// parse the paypal URL
		$url_parsed=parse_url($this->paypal_url);

		// generate the post string from the _POST vars aswell as load the
		// _POST vars into an arry so we can play with them from the calling
		// script.
		$post_string = '';
		foreach ($_POST as $field=>$value) {
			$this->ipn_data["$field"] = $value;
			$post_string .= $field.'='.urlencode(stripslashes($value)).'&';
		}
		$post_string.="cmd=_notify-validate"; // append ipn command
		
		$params = JComponentHelper::getParams('com_seminarman');

		// open the connection to paypal
		$socket_option = $params->get('paypal_ipn_socket');
		
		if ($socket_option == 2) {
			$fp = fsockopen ('ssl://'.$url_parsed['host'], "443", $err_num, $err_str, 30);
		} elseif ($socket_option == 3) {
			$fp = fsockopen ('tls://'.$url_parsed['host'], "443", $err_num, $err_str, 30);
		} else {
			$fp = fsockopen($url_parsed['host'],"80",$err_num,$err_str,30);
		}
		if(!$fp) {

			// could not open the connection.  If loggin is on, the error message
			// will be in the log.
			$this->last_error = "fsockopen error no. $errnum: $errstr";
			$this->log_ipn_results(false);
			return false;

		} else {

			// Post the data back to paypal
			fputs($fp, "POST $url_parsed[path] HTTP/1.1\r\n");
			fputs($fp, "Host: $url_parsed[host]\r\n");
			fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
			fputs($fp, "Content-length: ".strlen($post_string)."\r\n");
			fputs($fp, "Connection: close\r\n\r\n");
			fputs($fp, $post_string . "\r\n\r\n");

			// loop through the response from the server and append to variable
			while(!feof($fp)) {
				$this->ipn_response .= fgets($fp, 1024);
			}

			fclose($fp); // close connection

		}

		// if (eregi("VERIFIED",$this->ipn_response)) {
		if (preg_match("/VERIFIED/i",$this->ipn_response)) {

			// Valid IPN transaction.
			$this->log_ipn_results(true);

			//begin doing some backend updates
			$db = JFactory::getDBO();
			$model_pdf = JModelLegacy::getInstance( 'pdftemplate', 'seminarmanModel' );
			$model_app = JModelLegacy::getInstance( 'application', 'seminarmanModel' );
						
			// get application id
			$item_number = $this->ipn_data["item_number"];
			$app_array = explode("#", $item_number);
			$app_id = $app_array[count($app_array)-1];
			
			// get course
			$query = $db->getQuery(true);
			$query->select( 'app.extra_attach_file, c.*' );
			$query->from( '#__seminarman_application AS app' );
			$query->join( 'LEFT', '#__seminarman_courses AS c ON c.id = app.course_id' );
			$query->where( 'app.id = ' . $app_id );
			
			$db->setQuery( $query );
			$course = $db->loadAssoc();
			
			if($params->get('invoice_after_pay')) {
				$attachment = array();
				if($params->get('invoice_generate') == 1) {
				    $bill_prefix = strtolower(str_replace(' ', '_', JText::_('COM_SEMINARMAN_INVOICE_PREFIX'))) . '_';
    	        
				    // get bill number
    			    $bill_number = $model_app->getInvoiceNumber();
    			
    			    // update bill prefix and number
    			    $query = $db->getQuery(true);
    			     
    			    $fields = array( $db->quoteName( 'invoice_filename_prefix' ). ' = "'.$bill_prefix.'"',
    			    				'invoice_number = '.$bill_number  );
    			    $conditions = array( $db->quoteName('id') . ' = '.$app_id );
    			     
    			    $query->update( $db->quoteName( '#__seminarman_application' ) )->set( $fields )->where( $conditions );
    			     
    			    $db->setQuery($query);
    			    $db->execute();
    			    
    			
    			    // create and save invoice
    			    require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_seminarman' . DS . 'helpers' . DS . 'seminarman.php');
    			    require_once (JPATH_COMPONENT_SITE.DS.'classes'.DS.'helper.php');
    			    $tmpl_id = $course['invoice_template'];
    			    $template = $model_pdf->getTemplate($tmpl_id);
    			    $templateData = JHTMLSeminarman::getFieldValuesForTemplate($app_id);
    			    require_once JPATH_ADMINISTRATOR.DS.'components'.DS.'com_seminarman'.DS.'classes'.DS.'pdfdocument.php';
    			    $pdf = new PdfInvoice($template, $templateData);
    			    $pdf->store(JPATH_ROOT.DS.$params->get('invoice_save_dir').DS.$bill_prefix.$bill_number.'.pdf');
    			    
    			    if ($params->get('invoice_attach') == 1) $attachment[] = $pdf->getFile();
    			    
    			    // ics file
    			    if ($params->get('ics_file_name') == 0) {
    			        $ics_filename = "ical_course_" . $course['id'] . ".ics";
    			    } else {
    			    	jimport('joomla.filesystem.file');
    			    	$ics_filename = JFile::makeSafe(str_replace(array('Ä','Ö','Ü','ä','ö','ü','ß'), array('Ae','Oe','Ue','ae','oe','ue','ss'), html_entity_decode($course['title'], ENT_QUOTES)) . '_' . $course['id'] . ".ics");
    			    	$ics_filename = str_replace(' ', '_', $ics_filename);
    			    }
    			    $ics_filepath = JPATH_ROOT.DS.$params->get('invoice_save_dir').DS . $ics_filename;
    			    $courseParams = new JRegistry($course['attribs']);
    			    $params->merge($courseParams);
    			    if ($params->get('ics_by_booking') && file_exists($ics_filepath)) $attachment[] = $ics_filepath;
    			    
    			    // additional email attachment
					if ($params->get('add_extra_attach') && !empty($course['extra_attach_file'])) {
				         $attachment[] = JPATH_ROOT.DS.$params->get('invoice_save_dir').DS.$course['extra_attach_file'];
			        }
			        
			        // course email attachments
			        $course_attachments = SMANFunctions::getCourseEmailAttachs($course['id']);
			        if (!empty($course_attachments)) {
			        	foreach ($course_attachments AS $course_attachment) {
			        		$attachment[] = $course_attachment;
			        	}
			        }
				}
				
				// send confirmation email
				$emaildata = array();
				$user = JFactory::getUser();
				$emaildata['user_id'] = $user->get('id');
				$emaildata['applicationid'] = $app_id;
				$emaildata['start_date'] = $course['start_date'];
				$emaildata['finish_date'] = $course['finish_date'];
				$sendmail = $model_app->sendemail($emaildata, $course['email_template'], $attachment);
			}
			
			if($this->ipn_data["payment_status"] == "Completed") {
				$query = $db->getQuery(true);
				
				$fields = array( $db->quoteName( 'status' ). ' = 2'  );
				$conditions = array( $db->quoteName('id') . ' = '.$app_id );
				
				$query->update( $db->quoteName( '#__seminarman_application' ) )->set( $fields )->where( $conditions );
				
				$db->setQuery($query);
				$db->execute();

				//update protocol
				$query = $db->getQuery(true);
				$query->select( 'params' );
				$query->from( '#__seminarman_application' );
				$query->where( 'id = ' . $app_id );
					
				$db->setQuery( $query );				
				$params_string = $db->loadResult();
				
				$app_params_obj = new JRegistry();
				$app_params_obj->loadString($params_string);
				$app_params = $app_params_obj->toArray();
				if (!empty($app_params['protocols'])) {
					$tempArray = json_decode($app_params['protocols'], true);
					$dataArray = array('date'=>gmdate('Y-m-d H:i:s'), 'user'=>'Paypal IPN', 'status'=>2);
					array_push($tempArray, $dataArray);
					$protocols = json_encode($tempArray);
				} else {
					$tempArray = array();
					$dataArray = array('date'=>gmdate('Y-m-d H:i:s'), 'user'=>'Paypal IPN', 'status'=>2);
					array_push($tempArray, $dataArray);
					$protocols = json_encode($tempArray);
				}
				$jversion = new JVersion();
				$short_version = $jversion->getShortVersion();
				if (version_compare($short_version, "3.0", 'ge')) {
				    $app_params_obj->set('protocols', $protocols);
				} else {
					$app_params_obj->setValue('protocols', $protocols);
				}
				$params_string = $app_params_obj->toString();
				
				$query = $db->getQuery(true);
				
				$fields = array( $db->quoteName( 'params' ). " = '" . $db->escape( $params_string ) . "'" );
				$conditions = array( $db->quoteName('id') . ' = '.$app_id );
				
				$query->update( $db->quoteName( '#__seminarman_application' ) )->set( $fields )->where( $conditions );
				
				$db->setQuery($query);
				$db->execute();
			}
			
			// end doing some backend updates

			return true;

		} else {

			// Invalid IPN transaction.  Check the log for details.
			$this->last_error = 'IPN Validation Failed.';
			$this->log_ipn_results(false);
			
			return false;

		}

	}

	function log_ipn_results($success) {
			
		if (!$this->ipn_log) return;  // is logging turned off?

		// Timestamp
		$text = '['.date('m/d/Y g:i A').'] - ';

		// Success or failure being logged?
		if ($success) $text .= "SUCCESS!\n";
		else $text .= 'FAIL: '.$this->last_error."\n";

		// Log the POST variables
		$text .= "IPN POST Vars from Paypal:\n";
		foreach ($this->ipn_data as $key=>$value) {
			$text .= "$key=$value, ";
		}

		// Log the response from the paypal server
		$text .= "\nIPN Response from Paypal Server:\n ".$this->ipn_response;

		// Write to log
		$fp=fopen($this->ipn_log_file,'a');
		fwrite($fp, $text . "\n\n");

		fclose($fp);  // close file
	}

	function get_submit_paypal_html()
	{
		$html = '<form method="post" name="paypal_form" action="'.$this->paypal_url.'">';
		$html .= '<input type="hidden" name="charset" value="utf-8">';
		foreach ($this->fields as $name => $value)
			$html .= '	<input type="hidden" name="'.$name.'" value="'.$value.'" />';
		$html .= '	<input type="submit" id="submitpaypalbtn" value="'.JText::_('COM_SEMINARMAN_PAY_WITH_PAYPAL').'" onclick="javascript:transfer_paypal()" />';
		$html .= '</form>';
		return $html;
	}
}
