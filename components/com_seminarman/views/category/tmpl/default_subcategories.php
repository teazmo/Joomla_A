<?php
/**
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011 Open Source Group GmbH www.osg-gmbh.de
* @website http://www.profinvent.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

defined('_JEXEC') or die('Restricted access');
$Itemid = JRequest::getInt('Itemid');
?>

<h3 class="subcategories"><?php echo JText::_('COM_SEMINARMAN_SUBCATEGORIES'); ?></h3>

<?php

$n = count($this->categories);
$i = 0;

?>

<div class="subcategorieslist">
<?php
foreach ($this->categories as $sub):
?>
		<strong><a href="<?php echo JRoute::_('index.php?option=com_seminarman&view=category&cid=' . $sub->slug . '&Itemid=' . $Itemid); ?>"><?php echo $this->escape($sub->title); ?></a></strong>
		<?php if ($this->params->get('enable_dates', 1) == 1): ?>
		    (<?php echo $sub->assignedseminarmans != null ? $sub->assignedseminarmans : 0; ?>)
		<?php endif; ?>

<?php
    $i++;
    if ($i != $n) echo ',';
endforeach;
?>
</div>