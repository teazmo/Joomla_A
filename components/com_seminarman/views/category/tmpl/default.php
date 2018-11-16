<?php
/**
* @Copyright Copyright (C) 2018 www.osg-gmbh.de. All rights reserved.
* Copyright (C) 2011 Open Source Group GmbH www.osg-gmbh.de
* @website http://www.profinvent.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

defined('_JEXEC') or die('Restricted access');

?>
<div id="seminarman" class="seminarman">

<p class="buttons"><?php echo seminarman_html::favouritesbutton($this->params) . seminarman_html::printbutton($this->print_link, $this->params) . seminarman_html::mailbutton('category', $this->params, $this->category->slug); ?></p>

<?php 
    if ($this->params->get('show_page_heading', 0)) {
    	$page_heading = trim($this->params->get('page_heading'));
        if (!empty($page_heading)) {
            echo '<h1 class="componentheading">' . $page_heading . '</h1>';
        } else {
        	echo '<h1 class="componentheading">' . $this->category->title . '</h1>';
        }
    }
?>

<?php
if ($this->category->id > 0)
	echo $this->loadTemplate('category');

if (count($this->categories) && $this->category->id > 0)
    echo $this->loadTemplate('subcategories');
?>

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

<?php

// compute once if archive is shown //
$show_archive = false;
if (( $this->params->get('enable_archive', -1) == 1 ) || (($this->params->get('enable_archive', -1) == -1) && ($this->params->get('show_archive_in_table', 0) == 1))) {
    $show_archive = true;
}

if ($this->params->get('theme_bootstrap', 0)):
// bootstrap style
  if (($this->params->get('enable_dates', 1) == 1) && !empty($this->courses)) {
      $options = array(
          'active'    => 'panel_sman_courses'    // Not in docs, but DOES work
      );
  } elseif (($this->params->get('enable_salesprospects', 0) == 1) && !empty($this->templates)) {
      $options = array(
          'active'    => 'panel_sman_templates'    // Not in docs, but DOES work
      );
  } elseif ( !empty($this->archive_courses) && $show_archive ) {
      $options = array(
          'active'    => 'panel_sman_archive'    // Not in docs, but DOES work
      );
  }

if ((($this->params->get('enable_dates', 1) == 1) && !empty($this->courses)) || (($this->params->get('enable_salesprospects', 0) == 1) && !empty($this->templates)) || ($show_archive && !empty($this->archive_courses))):
echo JHtml::_('bootstrap.startTabSet', 'mytabs', $options);
if (($this->params->get('enable_dates', 1) == 1) && !empty($this->courses))
{
    echo JHtml::_('bootstrap.addTab', 'mytabs', 'panel_sman_courses', JText::_('COM_SEMINARMAN_DATES'));
    $this->course_table_quelle = 'normal';
    echo '<div class="seminarmancoursepan">' . $this->loadTemplate('courses') . '</div>';
    echo JHtml::_('bootstrap.endTab');
}
if (($this->params->get('enable_salesprospects', 0) == 1) && !empty($this->templates))
{
    echo JHtml::_('bootstrap.addTab', 'mytabs', 'panel_sman_templates', JText::_('COM_SEMINARMAN_LST_OF_SALES_PROSPECTS'));
    echo '<div class="seminarmantemplatepan">' . $this->loadTemplate('templates') . '</div>';
    echo JHtml::_('bootstrap.endTab');
}
if ( !empty($this->archive_courses) && $show_archive )
{
    echo JHtml::_('bootstrap.addTab', 'mytabs', 'panel_sman_archive', JText::_('COM_SEMINARMAN_ARCHIVE'));
    $this->course_table_quelle = 'archive';
    echo '<div class="seminarmanarchivepan">' . $this->loadTemplate('courses') . '</div>';
    echo JHtml::_('bootstrap.endTab');
}
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

if ((($this->params->get('enable_dates', 1) == 1) && !empty($this->courses)) || (($this->params->get('enable_salesprospects', 0) == 1) && !empty($this->templates)) || ($show_archive && !empty($this->archive_courses))):
	echo $tabs->startPane('mytabs');
	if (($this->params->get('enable_dates', 1) == 1) && !empty($this->courses))
	{
	    echo $tabs->startPanel(JText::_('COM_SEMINARMAN_DATES'), 0);
	    $this->course_table_quelle = 'normal';
	    echo '<div class="seminarmancoursepan">' . $this->loadTemplate('courses') . '</div>';
	    echo $tabs->endPanel();
	}
	if (($this->params->get('enable_salesprospects', 0) == 1) && !empty($this->templates))
	{
		echo $tabs->startPanel(JText::_('COM_SEMINARMAN_LST_OF_SALES_PROSPECTS'), 0);
		echo '<div class="seminarmantemplatepan">' . $this->loadTemplate('templates') . '</div>';
		echo $tabs->endPanel();
	}
	if ( !empty($this->archive_courses) && $show_archive )
	{
	
		echo $tabs->startPanel(JText::_('COM_SEMINARMAN_ARCHIVE'), 0);
		$this->course_table_quelle = 'archive';
		echo '<div class="seminarmanarchivepan">' . $this->loadTemplate('courses') . '</div>';
		echo $tabs->endPanel();
	}
	echo $tabs->endPane();
endif;

endif;
?>
</div>