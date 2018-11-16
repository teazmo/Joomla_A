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

defined('_JEXEC') or die;

$input = JFactory::getApplication()->input;
// Checking if loaded via index.php or component.php
$tmpl = $input->getCmd('tmpl', '');
$document = JFactory::getDocument();
?>

<script type="text/javascript">
	setcourse = function(course)
	{
		<?php if ($tmpl) : ?>
			window.parent.Joomla.submitbutton('application.setCourse', course);
			window.parent.SqueezeBox.close();
		<?php else : ?>
			window.location="index.php?option=com_seminarman&view=application&task=application.setCourse&layout=form&course="+('application.setCourse', course);
		
		<?php endif; ?>
	}
</script>
<legend><?php echo JText::_('COM_SEMINARMAN_SELECT_COURSE'); ?></legend>
	<?php $i = 0; ?>
	<?php foreach ($this->courses as $name => $list) : ?>
	<?php echo JHtml::_('sliders.start', 'category-pane', array('display'=>-1, 'show'=>-1, 'startOffset'=>-1, 'startTransition'=>true));?>
	<?php echo JHtml::_('sliders.panel', preg_replace('#[_].*#', '', $name), 'category_' . $i . '-panel');?>
	<table class="adminlist cellspace1">
		<thead>
			<tr>
				<th width="20%"><?php echo JText::_('COM_SEMINARMAN_TITLE'); ?></th>
				<th width="10"><?php echo JText::_('COM_SEMINARMAN_COURSE_CODE'); ?></th>
				<th width="10%"><?php echo JText::_('COM_SEMINARMAN_START_DATE'); ?></th>
				<th width="10%"><?php echo JText::_('COM_SEMINARMAN_FINISH_DATE'); ?></th>
				<th width="5"><?php echo JText::_('JPUBLISHED'); ?></th>
				<th width="5"><?php echo JText::_('COM_SEMINARMAN_COURSE_CANCELED'); ?></th>
				<th width="5"><?php echo JText::_('COM_SEMINARMAN_BOOKINGS'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php $k = 0; ?>
			<?php foreach ($list as $title => $item) : ?>
			<tr class="<?php echo "row$k"; ?>" title="<?php echo JHTML::_('string.truncate', strip_tags($item->description), 140); ?>" style="cursor: pointer;"  onclick="javascript:setcourse('<?php echo base64_encode(json_encode(array('id' => $item->id, 'title' => $item->title))); ?>')">
				<td align="center"><?php echo $item->title; ?></td>
				<td align="center"><?php echo $item->code; ?></td>
				<td align="center"><?php echo $item->start_date != '0000-00-00' ? JHTML::_('date', $item->start_date, JText::_('COM_SEMINARMAN_DATE_FORMAT1')) : JText::_('COM_SEMINARMAN_NOT_SPECIFIED'); ?></td>
				<td align="center"><?php echo $item->finish_date != '0000-00-00' ? JHTML::_('date', $item->finish_date, JText::_('COM_SEMINARMAN_DATE_FORMAT1')) : JText::_('COM_SEMINARMAN_NOT_SPECIFIED'); ?></td>
				<td align="center"><?php echo JHtml::_('jgrid.published', $item->state, $k, '', false); ?></td>
				<td align="center"><?php echo JHtml::_('jgrid.published', $item->canceled, $k, '', false); ?></td>
				<td align="center"><?php echo $item->currentBookings . "(" . $item->min_attend . ") " . JText::_( 'COM_SEMINARMAN_OF' ) . " " . $item->capacity; ?></td>
			</tr>
			<?php $k = 1 - $k; ?>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php echo JHtml::_('sliders.end'); ?>
	<?php $i++; ?>
	<?php endforeach; ?>