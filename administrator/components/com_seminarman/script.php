<?php
/**
 * @Copyright Copyright (C) 2011 Open Source Group GmbH
 * @website http://www.osg-gmbh.de
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

jimport( 'joomla.filesystem.file' );
jimport( 'joomla.filesystem.folder' );

if(!defined('DS')){
define('DS',DIRECTORY_SEPARATOR);
}

class com_seminarmanInstallerScript
{
	function preflight($type, $parent)
	{
		$jversion = new JVersion();
		$this->release = $parent->get("manifest")->version;
		$this->minimum_joomla_release = $parent->get("manifest")->attributes()->version;

		if (version_compare($jversion->getShortVersion(), $this->minimum_joomla_release, 'lt')) {
			Jerror::raiseWarning(null, 'Cannot install in a Joomla release prior to '.$this->minimum_joomla_release);
			return false;
		}

		if (version_compare(PHP_VERSION, '5.2', 'lt')) {
			Jerror::raiseWarning(null, 'Cannot install on '. PHP_VERSION .'. Need PHP 5.2 or greater.');
			return false;
		}

		if ($type == 'update') {
			$this->oldRelease = $this->getParam('version');
			if (version_compare($this->release, $this->oldRelease, 'lt')) {
				Jerror::raiseWarning(null, 'Cannot upgrade from '. $this->oldRelease .' to ' . $this->release);
				return false;
			}
			
			/*
			 * In versions prior 1.0.3 of this extension, there was no update schema path.
			 * In order to run the SQL update scripts on those versions, we insert the necessary
			 * database entry now.
			 */
			$short_version = $jversion->getShortVersion();
			if (version_compare($short_version, "2.5.5", 'lt')) {
			  if (version_compare($this->oldRelease, "1.0.3", 'lt')) {
				$row = JTable::getInstance('extension');
				$eid = $row->find(array( 'element' => strtolower($parent->get('element')), 'type' =>'component' ));
				
				$db = JFactory::getDBO();
				$query = $db->getQuery(true);
				$query->clear();
				$query->insert('#__schemas')->set('extension_id = '. $eid)->set('version_id = '. $db->quote($this->oldRelease));
				$db->setQuery($query);
				$db->query();
			  }
			}
			
			// in the previous releases the following css file might turn to be unwritable and it causes problems by the update
			// we fix it from the version 2.10.2
			
			// Set FTP credentials, if given
			jimport('joomla.client.helper');
			JClientHelper::setCredentialsFromRequest('ftp');
			$ftp = JClientHelper::getCredentials('ftp');
			
			$css_file = JPATH_SITE.DS.'components'.DS.'com_seminarman'.DS.'assets'.DS.'css'.DS.'seminarman.css';
			
			// Try to make the css file writeable
			if (!$ftp['enabled'] && JPath::isOwner($css_file) && !JPath::setPermissions($css_file, '0755')) {
				JError::raiseWarning(null, 'Cannot upgrade from '. $this->oldRelease .' to ' . $this->release . ': ' . JText::_('COM_SEMINARMAN_COULD_NOT_MAKE_FILE_WRITABLE'));
				return false;
			}
		}
	}


	function install($parent)
	{
		$src_path = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_seminarman'.DS.'install_data'.DS;
		$dst_path = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_seminarman'.DS.'models'.DS;

		$files = array(
			'course_j2.5.xml',
      'course_j3.x.xml',
      'template_j2.5.xml',
			'template_j3.x.xml'
		);

		foreach ($files as $f)
		if (! JFile::copy($src_path . $f, $dst_path . $f))
		return false;

		if (! JFile::copy($src_path.'virtual_tables.xml', JPATH_ADMINISTRATOR.DS.'components'.DS.'com_seminarman'.DS.'tables'.DS.'virtual_tables.xml'))
		return false;

		if (JFolder::create(JPATH_ROOT.DS.'invoices'))
		{
			$htaccess_fname = JPATH_ROOT.DS.'invoices'.DS.'.htaccess';
			if (!JFile::exists($htaccess_fname))
			{
				$htaccees_content = "Deny from all\n";
				JFile::write($htaccess_fname, $htaccees_content);
				chmod($htaccess_fname, 0444);
			}
		}

		echo '<p>Installation of '. $this->release .' successfull.</p>';
	}


	function update($parent) {
		$action = version_compare($this->release, $this->oldRelease, 'eq') ? 'Reinstallation of ' : 'Update to ';
		echo '<p>'. $action . $this->release .' successfull.</p>';
	}


	function postflight($type, $parent)
	{
		// default values for component parameters
		$params['enable_bookings'] = '2';
		$params['enable_loginform'] = '1';
		$params['enable_multiple_bookings_per_user'] = '0';
		$params['enable_bookings_deletable'] = '0';
		$params['enable_num_of_attendees'] = '1';
		$params['show_price_1'] = '0';
		$params['show_price_2'] = '1';
		$params['show_price_3'] = '1';
		$params['show_price_4'] = '1';
		$params['show_price_5'] = '1';
		$params['enable_salesprospects'] = '1';
		$params['show_price_template'] = '1';
		$params['enable_payment_overview'] = '0';
		$params['show_modify_date'] = '1';
		$params['show_hits'] = '1';
		$params['show_sessions'] = '1';
		$params['show_tags'] = '1';
		$params['show_favourites'] = '1';
		$params['show_categories'] = '1';
		$params['show_hyperlink'] = '1';
		$params['show_experience_level'] = '1';
		$params['show_location'] = '1';
		$params['show_group'] = '1';
		$params['show_tutor'] = '1';
		$params['show_capacity'] = '3';
		$params['current_capacity'] = '2';
		$params['show_booking_deadline'] = '0';
		$params['filter'] = '1';
		$params['display'] = '1';
		$params['limit'] = '10';
		$params['catlimit'] = '5';
		$params['list_ordering'] = '0';
		$params['show_icons'] = '1';
		$params['show_print_icon'] = '1';
		$params['show_email_icon'] = '1';
		$params['show_state_icon'] = '1';
		$params['currency'] = 'EUR';
		$params['second_currency'] = 'NONE';
		$params['factor'] = '0';
		$params['component_email'] = '';
		$params['vat'] = '19';
		$params['invoice_generate'] = '1';
		$params['invoice_attach'] = '1';
		$params['invoice_save_dir'] = 'invoices';
		$params['invoice_number_start'] = '1000';
		$params['upload_extensions'] = 'bmp,csv,doc,gif,ico,jpg,jpeg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls,BMP,CSV,DOC,GIF,ICO,JPG,JPEG,ODG,ODP,ODS,ODT,PDF,PNG,PPT,SWF,TXT,XCF,XLS';
		$params['upload_maxsize'] = '10000000';
		$params['file_path'] = 'components/com_seminarman/upload';
		$params['restrict_uploads'] = '1';
		$params['check_mime'] = '1';
		$params['image_extensions'] = 'bmp,gif,jpg,png';
		$params['ignore_extensions'] = '';
		$params['pwidth'] = '100';
		$params['pheight'] = '100';
		$params['image_path'] = 'images';
		$params['upload_mime'] = 'image/jpeg,image/gif,image/png,image/bmp,application/x-shockwave-flash,application/msword,application/excel,application/pdf,application/powerpoint,text/plain,application/x-zip';
		$params['upload_mime_illegal'] = 'text/html';
		$params['enable_flash'] = '0';
		$params['feed_summary'] = '0';
		$params['enable_paypal'] = '0';
		$params['paypal_email'] = '';
		$params['paypal_sandbox'] = '';
		$params['invoice_after_pay'] = '0';
		$params['trigger_onprepare_content'] = '0';
		$params['trigger_virtuemart'] = '0';
		$params['trigger_points'] = '0';
		$params['advanced_booking'] = '0';
		$params['waitinglist_active'] = '0';
		$params['user_booking_rules'] = '0';
		$params['common_schema_support'] = '0';
		$params['application_landingpage'] = 'index.php';
        $params['display_free_charge'] = 'COM_SEMINARMAN_FREE_OF_CHARGE';
        $params['show_price_in_table'] = '1';
        $params['show_tags_in_table'] = '0';
        $params['course_default_color'] = 'DDDDDD';
        $params['show_begin_date_in_table'] = '1';
        $params['show_end_date_in_table'] = '1';
        $params['show_datetime_in_table'] = '0';
        $params['show_booking_deadline_in_table'] = '0';
        $params['show_company_data'] = '0';
        $params['tutor_company_data'] = '';
        $params['order_of_attlst'] = '0';
        $params['use_alt_link_in_table'] = '0';
        $params['show_tooltip_in_form'] = '1';
        $params['show_booking_form'] = '1';
        $params['booking_deadline'] = 'n';
        $params['cancel_allowed'] = '0';
        $params['cancel_deadline'] = '0';
        $params['minstability'] = 'rc';
        $params['publish_down'] = '0';
        $params['status_of_attlst'] = '0';
        $params['show_start_date'] = '1';
        $params['show_finish_date'] = '1';
        $params['show_modify_date'] = '1';
        $params['payment_overview_layout'] = '0';
        $params['show_gross_price_global'] = '0';
        $params['enable_payment_selection'] = '0';
        $params['enable_bank_transfer'] = '1';
        $params['paypal_ipn_socket'] = '2';
        $params['enable_component_pathway'] = '1';
        $params['show_button_back'] = '0';
        $params['show_sp_button_in_table'] = '0';
        $params['show_register_name'] = '0';
        $params['show_code_in_table'] = '1';
        $params['show_code_in_my_bookings'] = '1';
        $params['booking_email_cc'] = '0';
        $params['custom_fld_1_title'] = '';
        $params['custom_fld_2_title'] = '';
        $params['custom_fld_3_title'] = '';
        $params['custom_fld_4_title'] = '';
        $params['custom_fld_5_title'] = '';
        $params['custom_fld_1_in_table'] = '0';
        $params['custom_fld_2_in_table'] = '0';
        $params['custom_fld_3_in_table'] = '0';
        $params['custom_fld_4_in_table'] = '0';
        $params['custom_fld_5_in_table'] = '0';
        $params['custom_fld_1_in_detail'] = '0';
        $params['custom_fld_2_in_detail'] = '0';
        $params['custom_fld_3_in_detail'] = '0';
        $params['custom_fld_4_in_detail'] = '0';
        $params['custom_fld_5_in_detail'] = '0';
        $params['custom_fld_layout_in_table'] = '0';
        $params['show_begin_time_in_table'] = '0';
        $params['show_finish_time_in_table'] = '0';
        $params['show_begin_time_in_my_bookings'] = '0';
        $params['show_finish_time_in_my_bookings'] = '0';
        $params['show_certificate_in_my_bookings'] = '0';
        $params['show_spaces_in_table'] = '0';
        $params['show_space_indicator_in_table'] = '0';
        $params['show_thumbnail_in_table'] = '0';
        $params['show_image_in_detail'] = '1';
        $params['ics_by_booking'] = '0';
        $params['ics_file_name'] = '0';
        $params['add_extra_attach'] = '0';
        
        $params['edit_course_color'] = '1';
        $params['edit_course_status'] = '1';
        $params['edit_course_new'] = '1';
        $params['edit_course_canceled'] = '1';
        $params['edit_course_title'] = '0';
        $params['edit_course_code'] = '0';
        $params['edit_course_alias'] = '0';
        $params['edit_course_desc'] = '0';
        $params['edit_course_email_tmpl'] = '0';
        $params['edit_course_invoice_tmpl'] = '0';
        $params['edit_course_attlst_tmpl'] = '0';
        $params['edit_course_certificate_tmpl'] = '0';
        $params['edit_course_extra_attach_tmpl'] = '0';
        $params['edit_course_group'] = '0';
        $params['edit_course_experience_level'] = '0';
        $params['edit_course_theme_points'] = '0';
        $params['edit_course_min_attendee'] = '1';
        $params['edit_course_capacity'] = '1';
        $params['edit_course_location'] = '1';
        $params['edit_course_url_location'] = '1';
        $params['edit_course_url_alternative'] = '1';
        $params['edit_course_category'] = '0';
        $params['edit_course_tags'] = '1';
        $params['edit_course_image'] = '1';
        $params['edit_course_documents'] = '1';
        $params['edit_course_serial_certificate'] = '0';
        $params['edit_course_prices'] = '0';
        $params['edit_course_params'] = '1';
        $params['edit_course_custom_fields'] = '1';
        
        $params['tutor_access_sales_prospects'] = '1';
        $params['tutor_access_tags'] = '1';
        $params['tutor_access_tutors'] = '0';
        $params['display_product_info'] = '1';
        $params['display_product_copyright'] = '1';
        $params['display_lastest_feeds'] = '1';
        
        $params['show_price_in_my_bookings'] = '1';
        $params['show_invoice_in_my_bookings'] = '1';
        
        $params['theme_bootstrap'] = '0';
        $params['show_counter_in_my_bookings'] = '1';
        
		$this->setParams( $params );
	}


	/*
	 * get a variable from the manifest file (actually, from the manifest cache).
	*/
	function getParam($name) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select( 'manifest_cache' );
		$query->from( '`#__extensions`' );
		$query->where( 'name = "com_seminarman"' );
			
		$db->setQuery( $query );
		
		$manifest = json_decode($db->loadResult(), true);
		return $manifest[$name];
	}


	function setParams($param_array) {
		if ( count($param_array) > 0 ) {

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select( 'params' );
			$query->from( '`#__extensions`' );
			$query->where( 'name = "com_seminarman"' );
				
			$db->setQuery( $query );
			
			$params = json_decode( $db->loadResult(), true );

			foreach ( $param_array as $name => $value ) {
				// if (empty($params[ (string) $name ])) {
				// Bugfix in 2.2.2
				if (!isset($params[ (string) $name ])) {
					    $params[ (string) $name ] = (string) $value;
				}
			}

			$paramsString = json_encode( $params );
			$fields = array(
					$db->quoteName('params') . ' = ' . $db->quote( $paramsString )
			);
			
			$conditions = array(
					$db->quoteName('name') . ' = "com_seminarman"'
			);
			
			$query->update( $db->quoteName( '#__extensions' ) )
			->set( $fields )
			->where( $conditions );
			
        	$db->setQuery( $query );
			$db->execute();
			
		}
	}
}
