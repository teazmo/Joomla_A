<?php
/**
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011 Open Source Group GmbH www.osg-gmbh.de
* @website http://www.profinvent.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.html.parameter' );

$colspan_hide = 0;
if (!($this->params->get('show_location'))) $colspan_hide += 1;
if (!($this->params->get('show_price_template'))) $colspan_hide += 1;
if (!($this->params->get('show_sp_button_in_table'))) $colspan_hide += 1;
$colspan = 5 - $colspan_hide;

$Itemid = JRequest::getInt('Itemid');
?>

<form action="<?php echo $this->action;?>" method="post" id="adminForm2">
<?php if ($this->params->get('filter2') || $this->params->get('display')):?>
<div id="qf_filter2" class="floattext">
		<?php if ($this->params->get('filter')):?>
		<dl class="qf_fleft">
			<dd>
				<?php echo JText::_('COM_SEMINARMAN_COURSE') . ': ';?>
				<input type="text" name="filter2" id="filter2" value="<?php echo $this->lists['filter2']; ?>" class="text_area" size="15"/>
			</dd>
			<dd>
				<?php echo JText::_('COM_SEMINARMAN_LEVEL') . ': ';?>
				<?php echo $this->lists['filter_experience_level2'];?>
				<button  onclick="document.getElementById('adminForm2').submit();"><?php echo JText::_('COM_SEMINARMAN_GO');?></button>
			</dd>
		</dl>
		<?php endif;?>
		<?php if ($this->params->get('display')):?>
		<div class="qf_fright">
			<label for="tmpl_limit"><?php echo JText::_('COM_SEMINARMAN_DISPLAY_NUM') ?></label><?php echo $this->pageNav2->getLimitBox(); ?>
		</div>
		<?php endif;?>
</div>
<?php endif;?>

<?php 
// load voting system if available
$dispatcher=JDispatcher::getInstance();
JPluginHelper::importPlugin('seminarman');
$html_tmpl=$dispatcher->trigger('onGetVotingTemplateDESC',array($this->category));  // actually nothing need to be submitted, but maybe later
if (isset($html_tmpl) && !empty($html_tmpl)) echo $html_tmpl[0];
?>

<table class="seminarmancoursetable" summary="seminarman">
<thead>
<tr>
	<th id="qf_code2" class="sectiontableheader"><?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_COURSE_CODE', 'i.code', $this->lists['filter_order_Dir2'], $this->lists['filter_order2'], 'il'); ?></th>
	<th id="qf_title2" class="sectiontableheader"><?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_COURSE_TITLE', 'i.title', $this->lists['filter_order_Dir2'], $this->lists['filter_order2'], 'il'); ?></th>
<?php if ($this->params->get('show_location')): ?>
	<th id="qf_location2" class="sectiontableheader"><?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_LOCATION', 'i.location', $this->lists['filter_order_Dir2'],	$this->lists['filter_order2'], 'il'); ?></th>
<?php endif;?>
<?php if ($this->params->get('show_price_template')): ?>
	<th id="qf_price2" class="sectiontableheader"><?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_PRICE', 'i.price', $this->lists['filter_order_Dir2'], $this->lists['filter_order2'], 'il'); ?><?php echo ($this->params->get('show_gross_price') != 2) ? "*" : ""; ?></th>
<?php endif;?>
<?php if ($this->params->get('show_sp_button_in_table')): ?>
    <th id="qf_application2" class="sectiontableheader"></th>
<?php endif; ?>
</tr>
</thead>

<tbody>

<?php

$i=0;
foreach ($this->templates as $template):
?>
<tr class="sectiontableentry" >
	<td headers="qf_code" data-title="<?php echo JText::_('COM_SEMINARMAN_COURSE_CODE'); ?>"><?php echo $this->escape($template->code); ?></td>
	<td headers="qf_title" data-title="<?php echo JText::_('COM_SEMINARMAN_COURSE_TITLE'); ?>"><strong><a href="<?php echo JRoute::_('index.php?option=com_seminarman&view=templates&cid=' . $this->category->slug . '&id=' . $template->slug . '&Itemid=' . $Itemid); ?>"><?php echo $this->escape($template->title); ?></a></strong></td>
<?php if ($this->params->get('show_location')): ?>
	<td headers="qf_location" data-title="<?php echo JText::_('COM_SEMINARMAN_LOCATION'); ?>"><?php echo $template->location; ?></td>
<?php endif;?>
<?php if ($this->params->get('show_price_template')): ?>	
	<td headers="qf_price" data-title="<?php echo JText::_('COM_SEMINARMAN_PRICE'); ?>">
	<?php
	    $display_free_charge = $this->params->get('display_free_charge');
	    if (!empty($display_free_charge) && ($template->price == 0)) {
	    	echo JText::_($display_free_charge);
	    } else {
	        echo $template->price . '&nbsp;'. $template->currency_price.' ' . $template->price_type; 
	    }
	?>
	</td>
<?php endif;?>
<?php if ($this->params->get('show_sp_button_in_table')): ?>
	<td class="centered" headers="qf_book">
	<?php echo '<div class="button2-left"><div class="blank"><a href="'.JRoute::_('index.php?option=com_seminarman&view=templates&cid=' . $this->category->slug . '&id=' . $template->slug . '&Itemid=' . $Itemid).'#appform">'.JText::_('COM_SEMINARMAN_JOIN_LIST_BTN_IN_TABLE').'</a></div></div>'; ?>
	</td>
<?php endif;?>
</tr>


<?php
$i++;
endforeach;
?>

<?php if ($this->params->get('show_gross_price') != 2): ?>
<tr class="sectiontableentry" >
	<td colspan="<?php echo $colspan; ?>" class="right">*<?php echo ($this->params->get('show_gross_price') == 1) ? JText::_('COM_SEMINARMAN_WITH_VAT') : JText::_('COM_SEMINARMAN_WITHOUT_VAT'); ?></td>
</tr>
<?php endif; ?>
</tbody>
</table>


<input type="hidden" name="option" value="com_seminarman" />
<input type="hidden" name="filter_order2" value="<?php echo $this->lists['filter_order2'];?>" />
<input type="hidden" name="filter_order_Dir2" value="" />
<input type="hidden" name="view" value="category" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="id" value="<?php echo $this->category->id;?>" />
</form>

<div class="pagination"><?php echo $this->pageNav2->getPagesLinks(); ?></div>