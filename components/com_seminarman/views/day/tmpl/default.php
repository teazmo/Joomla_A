<?php
/**
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011 Open Source Group GmbH www.osg-gmbh.de
* @website http://www.profinvent.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

defined('_JEXEC') or die('Restricted access');

?>
<div id="seminarman" class="seminarman">



<script type="text/javascript">
function tableOrdering( order, dir, task ) {

	if (task == 'il') {
		var form = document.getElementById('adminForm2');
		form.filter_order2.value = order;
		form.filter_order_Dir2.value = dir;
		document.getElementById('adminForm2').submit( task );
	}
	else {
		var form = document.getElementById('adminForm');
		form.filter_order.value = order;
		form.filter_order_Dir.value	= dir;
		document.getElementById('adminForm').submit( task );
	}
}
</script>

	<div class="floattext">
		
		<h2 class="seminarman"><?php echo $this->escape($this->day->title); ?></h2>
	</div>
	
	<?php
	
	if ($this->params->get('theme_bootstrap', 0)):
	// bootstrap style
	if(!empty($this->courses)):
	    $options = array(
	        'active'    => 'panel_sman_courses'    // Not in docs, but DOES work
	    );
	    echo JHtml::_('bootstrap.startTabSet', 'mytabs', $options);
	    echo JHtml::_('bootstrap.addTab', 'mytabs', 'panel_sman_courses', JText::_('COM_SEMINARMAN_DATES'));
	    echo '<div>' . $this->loadTemplate('courses') . '</div>';
	    echo JHtml::_('bootstrap.endTab');
	    echo JHtml::_('bootstrap.endTabSet');
	endif;
	else:
	// old mootools style
	jimport('joomla.html.pane');

	$jversion = new JVersion();
	$short_version = $jversion->getShortVersion();
	if (version_compare($short_version, "3.0", 'ge')) {
	    $tabs = JPaneOSGF::getInstance('tabs', array('startOffset'=>0));
    } else {
		$tabs = JPane::getInstance('tabs', array('startOffset'=>0));
	}
	if(!empty($this->courses)):
		echo $tabs->startPane('mytabs');
		echo $tabs->startPanel(JText::_('COM_SEMINARMAN_DATES'), 0);
		echo '<div>' . $this->loadTemplate('courses') . '</div>';
		echo $tabs->endPanel();
		echo $tabs->endPane();
	endif;
	
	endif;
	?>
</div>