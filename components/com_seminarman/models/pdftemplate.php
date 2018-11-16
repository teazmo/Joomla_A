<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class seminarmanModelPdftemplate extends JModelLegacy
{
	function getTemplate($id = 0)
	{
		$db = JFactory::getDBO();
		
		$query = $db->getQuery(true);
		$query->select( '*' );
		$query->from( '`#__seminarman_pdftemplate`' );
		$query->where( 'templatefor=0' );
		
		if ($id == 0) {
			$query->where( 'isdefault=1' );
		} else {
			$query->where( 'id='.(int)$id );
		}
		$query->setLimit( 1 );
		
		$db->setQuery( $query );
		return $db->loadObject();
	}
	
	function getTemplateForAttach($id = 0)
	{
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);
		$query->select( '*' );
		$query->from( '`#__seminarman_pdftemplate`' );
		$query->where( 'templatefor=3' );
		
		if ($id == 0) {
			$query->where( 'isdefault=1' );
		} else {
			$query->where( 'id='.(int)$id );
		}
		$query->setLimit( 1 );
		
		$db->setQuery( $query );
		return $db->loadObject();
	}
}


?>