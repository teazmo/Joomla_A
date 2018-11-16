<?php
/**
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011 Open Source Group GmbH www.osg-gmbh.de
* @website http://www.profinvent.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.html.pane');
$jversion = new JVersion();
$short_version = $jversion->getShortVersion();
if (version_compare($short_version, "3.0", 'ge')) {
	$pane = JPaneOSG::getInstance('tabs', array('startOffset' => 0));
} else {
	$pane = JPane::getInstance('tabs', array('startOffset' => 0));
}

if (JPluginHelper::isEnabled('seminarman', 'smanadvimportexport')) {
	JPluginHelper::importPlugin('seminarman', 'smanadvimportexport');
	$dispatcher = JDispatcher::getInstance();
	$template_select_courses = $dispatcher->trigger('buildTemplateSelect', 'courses');
	$template_select_applications = $dispatcher->trigger('buildTemplateSelect', 'applications');
	$template_selects = '
			<li id="li_courses_tmp">
				' . $template_select_courses[0] . '
			</li>
			<li id="li_applications_tmp" style="display: none;">
				' . $template_select_applications[0] . '
			</li>';
	$template = $dispatcher->trigger('buildLayout');
	$showOptions="
		switch(document.adminForm1.datatype.value) {
		case 'courses':
			document.getElementById('li_courses_tmp').style.display='block';
			document.getElementById('li_applications_tmp').style.display='none';
			document.getElementById('li_course').style.display='block';
			document.getElementById('li_date').style.display='block';
			document.getElementById('li_template').style.display='none';
            document.getElementById('li_app_state').style.display='none';
			break;
		case 'sessions':
			document.getElementById('li_courses_tmp').style.display='none';
			document.getElementById('li_applications_tmp').style.display='none';
			document.getElementById('li_course').style.display='block';
			document.getElementById('li_date').style.display='block';
			document.getElementById('li_template').style.display='none';
            document.getElementById('li_app_state').style.display='none';
			break;
		case 'applications':
			document.getElementById('li_courses_tmp').style.display='none';
			document.getElementById('li_applications_tmp').style.display='block';
			document.getElementById('li_course').style.display='block';
			document.getElementById('li_date').style.display='block';
			document.getElementById('li_template').style.display='none';
            document.getElementById('li_app_state').style.display='block';
			break;
		case 'salesprospects':
		case 'templates':
			document.getElementById('li_courses_tmp').style.display='none';
			document.getElementById('li_applications_tmp').style.display='none';
			document.getElementById('li_course').style.display='none';
			document.getElementById('li_date').style.display='none';
			document.getElementById('li_template').style.display='block';
            document.getElementById('li_app_state').style.display='none';
			break;
		case 'tutors':
			document.getElementById('li_courses_tmp').style.display='none';
			document.getElementById('li_applications_tmp').style.display='none';
			document.getElementById('li_course').style.display='none';
			document.getElementById('li_date').style.display='none';
			document.getElementById('li_template').style.display='none';
            document.getElementById('li_app_state').style.display='none';
			break;
		}";
	$controller = 'advimportexport';
	$adv_panel = $template[0];
} else {
	$template_selects = '';
	$showOptions = "
		switch(document.adminForm1.datatype.value) {
		case 'courses':
		case 'sessions':
		case 'applications':
			document.getElementById('li_course').style.display='block';
			document.getElementById('li_date').style.display='block';
			document.getElementById('li_template').style.display='none';
            document.getElementById('li_app_state').style.display='block';
			break;
		case 'salesprospects':
		case 'templates':
			document.getElementById('li_course').style.display='none';
			document.getElementById('li_date').style.display='none';
			document.getElementById('li_template').style.display='block';
            document.getElementById('li_app_state').style.display='none';
			break;
		case 'tutors':
			document.getElementById('li_course').style.display='none';
			document.getElementById('li_date').style.display='none';
			document.getElementById('li_template').style.display='none';
            document.getElementById('li_app_state').style.display='none';
			break;
		}";
	$controller = 'importexport';
	$adv_panel = '';
}
?>
<script type="text/javascript">

	Joomla.submitbutton = function(task) {
		var form = document.adminForm;
		if (task == 'cancel') {
			Joomla.submitform( task );
			return;
		}
			Joomla.submitform( task );
	};

	function showOptions() {
		<?php echo $showOptions; ?>
	}
</script>

<style type="text/css">
div.current label, div.current {
    min-width: 200px;
}
</style>
<?php
echo $pane->startPane('pane');
?>

<form id ="adminForm" name="adminForm" action="<?php echo $this->path; ?>" method="post" enctype="multipart/form-data">
<input type="hidden" name="option" value="com_seminarman" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="importexport" />
<input type="hidden" name="view" value="" />
<input type="hidden" name="boxchecked" value="0" />
<?php echo JHTML::_('form.token'); ?>
</form>

<?php echo $pane->startPanel(JText::_('COM_SEMINARMAN_EXPORT_DATA'), 'panel1'); ?>
<form id ="adminForm1" name="adminForm1" action="index.php?option=com_seminarman&controller=<?php echo $controller; ?>&task=exportcsv" method="post" enctype="multipart/form-data">
	<fieldset>
		<ul class="adminformlist">
			<li>
				<label><?php echo JText::_('COM_SEMINARMAN_EXPORT_DATA'); ?></label>
				<?php echo $this->expselect; ?>
			</li>
			<?php echo $template_selects; ?>
			<li id="li_course">
				<label><?php echo JText::_('COM_SEMINARMAN_EXPORT_ONLY_COURSE'); ?></label>
				<?php echo $this->expcourse; ?>
			</li>
			<li id="li_template" style="display: none;">
				<label><?php echo JText::_('COM_SEMINARMAN_EXPORT_ONLY_TEMPLATE'); ?></label>
				<?php echo $this->exptemplate; ?>
			</li>
			<li id="li_date">
				<label><?php echo JText::_('COM_SEMINARMAN_EXPORT_ONLY_BETWEEN'); ?></label>
				<?php echo $this->expfromdate; ?><?php echo $this->exptodate; ?>
			</li>			
			<li id="li_app_state" style="display: none;">
			    <label><?php echo JText::_('COM_SEMINARMAN_STATUS'); ?></label>
			    <select id="csv_app_stati_sel" name="csv_app_stati" class="inputbox">
                	<option value="-1"><?php echo JText::_('COM_SEMINARMAN_ATTLST_SUBMITTED_PENDING_ONLY'); ?></option>
                	<option value="0" selected="selected"><?php echo JText::_('COM_SEMINARMAN_ATTLST_PENDING_PAID_ONLY'); ?></option>
                	<option value="0.5"><?php echo JText::_('COM_SEMINARMAN_ATTLST_PAID_ONLY'); ?></option>
                	<option value="1"><?php echo JText::_('COM_SEMINARMAN_ATTLST_SUBMITTED_PENDING_PAID'); ?></option>
                	<option value="2"><?php echo JText::_('COM_SEMINARMAN_ATTLST_SUBMITTED_PENDING_PAID_CANCELED'); ?></option>
                	<option value="3"><?php echo JText::_('COM_SEMINARMAN_ATTLST_WAITING_NOT_CONFIRMED'); ?></option>
                	<option value="4"><?php echo JText::_('COM_SEMINARMAN_ATTLST_WAITING_ONLY'); ?></option>
                	<option value="5"><?php echo JText::_('COM_SEMINARMAN_ATTLST_ALL_STATES_NO_CANCELED'); ?></option>
                	<option value="6"><?php echo JText::_('COM_SEMINARMAN_ATTLST_ALL_STATES'); ?></option>
                </select>
			</li>
			<li id="li_separator">
			    <label><?php echo JText::_('COM_SEMINARMAN_COLUMN_SEPARATOR'); ?></label>
			    <input type="text" id="csv_separator" name="csv_separator" value=";" />
			</li>
		</ul>
		<div class="clr"></div>
		<input type="submit" value="<?php echo JText::_('COM_SEMINARMAN_EXPORT_DATA');?>"/>
	</fieldset>
</form>
<?php echo $pane->endPanel();
echo $adv_panel;
echo $pane->endPane('pane');?>