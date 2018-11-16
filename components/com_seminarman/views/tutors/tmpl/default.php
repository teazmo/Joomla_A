<?php
/**
*
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2012 Open Source Group GmbH www.osg-gmbh.de
* @website http://www.profinvent.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
if (JVERSION >= 3.4) {
    JHtml::_('behavior.formvalidator');
} else {
    JHTML::_('behavior.formvalidation');
}
// JHtml::register('behavior.tooltip', $this->clau_tooltip());
$mainframe = JFactory::getApplication();
$params = $mainframe->getParams('com_seminarman');
$Itemid = JRequest::getInt('Itemid');
?>

<div id="seminarman" class="seminarman">

<?php 
    if ($this->params->get('show_page_heading', 0)) {
    	$page_heading = trim($this->params->get('page_heading'));
        if (!empty($page_heading)) {
            echo '<h1 class="componentheading">' . $page_heading . '</h1>';
        }
    }
?>

    <?php echo JText::_('COM_SEMINARMAN_TUTORS_OVERVIEW_DESC');?>
<?php
foreach ($this->tutors as $tutor): 
?>
<div class="tutor_block">
<div class="tutor_block_left">
<?php if (!empty($tutor->tutor_photo)): ?>
<a href="<?php echo JRoute::_('index.php?option=com_seminarman&view=tutor&id=' . $tutor->tutor_slug . '&Itemid=' . $Itemid); ?>"><img src="<?php echo $this->siteurl .$params->get('image_path', 'images'). '/' . $tutor->tutor_photo; ?>"></a>
<?php endif; ?>
</div>
<div class="tutor_block_right">
<h3 class="tutor_label"><?php echo $tutor->tutor_label; ?></h3>
<span>
  <?php 
    // echo substr($tutor->tutor_desc, 0, 200) . ' ...'; 
    echo JHTML::_('string.truncate', ($tutor->tutor_desc), 200);
  ?>
</span><br><br>
<a href="<?php echo JRoute::_('index.php?option=com_seminarman&view=tutor&id=' . $tutor->tutor_slug . '&Itemid=' . $Itemid); ?>"><?php echo JText::_('COM_SEMINARMAN_MORE');?></a>
</div>
</div>
<?php 
endforeach;
?>
</div>
