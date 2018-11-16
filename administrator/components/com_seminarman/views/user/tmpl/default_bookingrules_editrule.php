<?php
/**
 * Copyright (C) 2015 Open Source Group GmbH www.osg-gmbh.de
 * @website http://www.osg-gmbh.de
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 **/
defined('_JEXEC') or die('Restricted access');

function formatDate($str, $format) {
	$str = trim($str);
	if ((substr($str, 0, 10) != '0000-00-00') && (!empty($str)))
		return JHTML::_('date', $str, $format);
}

JHTML::_('behavior.tooltip');
jimport('joomla.utilities.date');
$user = JFactory::getUser($this->lists['user_id']);
echo "<h3>" . $user->name . " (" . $user->username . ")</h3>";
?>
<?php 

  if (empty($this->lists['rule_text'])) {
      $start_date="0000-00-00";
      $finish_date="0000-00-00";
      $cat = 0;  // all categories;
      $amount = '';
  } else {
      $details = json_decode($this->lists['rule_text']);
      $start_date = $details->start_date;
      $finish_date = $details->finish_date;
      $cat = $details->category;
      $amount = $details->amount;      
  }
  
  if ($this->lists['created'] == $this->nullDate) {
      $this->lists['created'] = JText::_('COM_SEMINARMAN_NEW');
  } else {
      $this->lists['created'] = JHTML::_('date', $this->lists['created'], JText::_('DATE_FORMAT_LC2'));
  }
  
  $categories = seminarman_cats::getCategoriesTree(1);
  $list_categories = seminarman_cats::buildcatselect($categories, 'category', $cat, true, 'class="inputbox"');
?>
<script>
Joomla.submitbutton = function(task){

	var form = document.adminForm;

	if (task == 'cancel') {
		window.parent.SqueezeBox.close();
	}

	// do field validation
	if (form.title.value == "")
		alert( "<?php echo JText::_('COM_SEMINARMAN_MISSING_TITLE'); ?>" );
	else if(form.category.selectedIndex == -1)
		alert( "<?php echo JText::_('COM_SEMINARMAN_SELECT_CATEGORY'); ?>" );
	else {
		Joomla.submitform( task );
	}
};
</script>
<style type="text/css">

fieldset.adminform label {
	min-width: 100px;
	text-align: right;
	padding-right: 10px;
	margin: 3px 0;
}

fieldset input, fieldset select, fieldset img, fieldset button {
    float: left;
    margin: 3px 5px 3px 0;
    width: auto;
}

fieldset.adminform {
	margin: 5px;
}

fieldset.radio {
    border: 0 none;
    float: left;
    margin: 0 0 5px;
    padding: 0 ! important;
}

fieldset.radio label {
    clear: none;
    display: inline;
    float: left;
    padding-left: 0;
    padding-right: 10px;
    min-width: 0 ! important;
}

</style>
<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
	<fieldset class="adminform">
	<legend>
	<?php 
	   if (JRequest::getVar('content') == "addbookingrule") {
	      echo JText::_( 'COM_SEMINARMAN_ADD_RULE' ); 
	   } else {
          echo JText::_( 'COM_SEMINARMAN_EDIT_RULE' );
       }
	?>
	</legend>
	<div class="width-50 fltlft">
	<ul class="adminformlist">
		<li>
			<label for="published"><?php echo JText::_('JPUBLISHED'); ?></label>
			<fieldset id="published" class="radio"><?php echo $this->lists['published_state']; ?></fieldset>
		</li>
		<li>
			<label for="title"><?php echo JText::_('COM_SEMINARMAN_TITLE') ?><span class="star">&nbsp;*</span></label>
			<input class="inputbox" type="text" name="title" id="title" size="32" maxlength="254" value="<?php echo $this->lists['title']; ?>" />
		</li>
		<li>
			<label for="category"><?php echo JText::_('COM_SEMINARMAN_CATEGORY') ?><span class="star">&nbsp;*</span></label>
			<?php echo $list_categories; ?>
		</li>
		<li>
			<label for="start_date"><?php echo JText::_('COM_SEMINARMAN_START_DATE'); ?></label>
			<?php echo JHTML::calendar( formatDate($start_date, JText::_('COM_SEMINARMAN_DATE_FORMAT1')), 'start_date', 'start_date', JText::_('COM_SEMINARMAN_DATE_FORMAT1_ALT'));?>		
		</li>
		<li>
			<label for="finish_date"><?php echo JText::_('COM_SEMINARMAN_FINISH_DATE'); ?></label>
			<?php echo JHTML::calendar( formatDate($finish_date, JText::_('COM_SEMINARMAN_DATE_FORMAT1')),  'finish_date', 'finish_date', JText::_('COM_SEMINARMAN_DATE_FORMAT1_ALT'));?>
		</li>	
		<li>
			<label for="amount"><?php echo JText::_('COM_SEMINARMAN_QUANTITY') ?></label>
			<input class="inputbox" type="text" name="amount" id="amount" size="10" maxlength="5" value="<?php echo $amount; ?>" />
		</li>
		<li>
			<label >&nbsp;</label>
			<div style="float: left; margin-top: 15px;"><a class="btn" href="javascript:void(0);" onclick="Joomla.submitbutton('save_booking_rule');"><?php echo JText::_( 'COM_SEMINARMAN_SAVE' );?></a>&nbsp;&nbsp;<a class="btn" href="javascript:void(0);" onclick="Joomla.submitbutton('cancel');"><?php echo JText::_( 'COM_SEMINARMAN_CANCEL' );?></a></div>
		</li>
	</ul>
	</div>
	<div class="width-50 fltlft">
	<ul class="adminformlist">
		<li>
			<label><?php echo JText::_('COM_SEMINARMAN_ID'); ?></label>
			<input type="text" readonly="readonly" size="32" style="border-width: 0px;" value="<?php echo $this->lists['id']; ?>" />
		</li>
		<li>
			<label><?php echo JText::_('COM_SEMINARMAN_CREATED'); ?></label>
			<input type="text" readonly="readonly" size="32" style="border-width: 0px;" value="<?php echo $this->lists['created']; ?>" />
		</li>
		<li>
			<label><?php echo JText::_('COM_SEMINARMAN_USER_RULE_TYPE'); ?></label>
			<input type="text" readonly="readonly" size="32" style="border-width: 0px;" value="<?php echo JText::_('COM_SEMINARMAN_USER_BOOKING_RULE'); ?>" />
		</li>
		<li>
			<label><?php echo JText::_('COM_SEMINARMAN_USER_RULE_OPTION'); ?></label>
			<input type="text" readonly="readonly" size="32" style="border-width: 0px;" value="<?php echo JText::_('COM_SEMINARMAN_CATEGORY') . ' / ' . JText::_('COM_SEMINARMAN_PERIOD') . ' / ' . JText::_('COM_SEMINARMAN_AMOUNT'); ?>" />
		</li>	
	</ul>
	</div>
	</fieldset>
	<?php echo JHTML::_('form.token'); ?>
	<input type="hidden" name="option" value="com_seminarman" />
	<input type="hidden" name="controller" value="user" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="id" value="<?php echo $this->lists['id']; ?>" />
    <input type="hidden" name="user_id" value="<?php echo $this->lists['user_id']; ?>" />
    <input type="hidden" name="rule_type" value="1" />
    <input type="hidden" name="rule_option" value="category" />
</form>