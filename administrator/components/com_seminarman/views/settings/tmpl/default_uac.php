<?php
/**
 * Copyright (C) 2016 Open Source Group GmbH www.osg-gmbh.de
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 **/

defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.html.parameter' );
?>
<table class="adminlist">
<thead>
<tr>
<th class="pix10" style="width: 3%;"></th>
<th class="proc98" style="width: 40%;"><?php echo JText::_('COM_SEMINARMAN_MANAGER_MEMBERS'); ?></th>
			<?php 
			if ((SeminarmanFunctions::isVMEnabled()) && ($this->params->get('trigger_virtuemart') == 1)) {
				echo '<th class="proc98" style="width: 30%;">' . JText::_('COM_SEMINARMAN_ACCESS_TO_COM') . '</th><th class="proc98" style="width: 15%;">VM Publisher</th>';
			} else {
				echo '<th class="proc98" style="width: 45%;">' . JText::_('COM_SEMINARMAN_ACCESS_TO_COM') . '</th>';
			}
			?>
		    <th class="pix30" style="width: 12%;"></th>
		</tr>
	</thead>
	<tbody>
	<tr><td></td><td style="text-align: center;"><?php echo $this->managerlist; ?></td>
	<?php 
	if ((SeminarmanFunctions::isVMEnabled()) && ($this->params->get('trigger_virtuemart') == 1)) {
		echo '<td style="text-align: center;">' . $this->manageraccesslst . '</td><td style="text-align: center;">' . $this->managervmpublist . '</td>';
	} else {
		echo '<td style="text-align: center;">' . $this->manageraccesslst . '</td>';
	}
	?>
	<td></td></tr>
	</tbody>
</table>
<br /><br />
<table class="adminlist">
	<thead>
		<tr>
			<th class="pix10" style="width: 3%;"></th>
			<th class="proc98" style="width: 40%;"><?php echo JText::_('COM_SEMINARMAN_TUTOR_MEMBERS'); ?></th>
			<?php 
			if ((SeminarmanFunctions::isVMEnabled()) && ($this->params->get('trigger_virtuemart') == 1)) {
				echo '<th class="proc98" style="width: 30%;">' . JText::_('COM_SEMINARMAN_ACCESS_TO_COM') . '</th><th class="proc98" style="width: 15%;">VM Publisher</th>';
			} else {
				echo '<th class="proc98" style="width: 45%;">' . JText::_('COM_SEMINARMAN_ACCESS_TO_COM') . '</th>';
			}			
			?>
		    <th class="pix30" style="width: 12%;"></th>
		</tr>
	</thead>
	<tbody>
	<tr><td></td><td style="text-align: center;"><?php echo $this->tutorlist; ?></td>
	<?php 
	if ((SeminarmanFunctions::isVMEnabled()) && ($this->params->get('trigger_virtuemart') == 1)) {
		echo '<td style="text-align: center;">' . $this->tutoraccesslst . '</td><td style="text-align: center;">' . $this->tutorvmpublist . '</td>';
	} else {
		echo '<td style="text-align: center;">' . $this->tutoraccesslst . '</td>';
	}
	?>
	<td></td></tr>
	</tbody>
</table>
<br /><br />