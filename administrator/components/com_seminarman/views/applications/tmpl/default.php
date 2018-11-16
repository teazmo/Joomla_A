<?php
/**
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011-2016 Open Source Group GmbH www.osg-gmbh.de
* @website http://www.profinvent.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.tooltip');
jimport('joomla.utilities.date');

$params = JComponentHelper::getParams('com_seminarman');
$jversion = new JVersion();
$short_version = $jversion->getShortVersion();
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if( task=='add' ) {
			var selectTag = document.getElementById( "filter_courseid" );
			if(selectTag.value == "0") {
				alert('<?php echo JText::_('COM_SEMINARMAN_SELECT_COURSE_LONG'); ?>');
				return;
			}
			else {
				submitform(task);
			}
		}
		else {
			submitform(task);
		}
	}

function setStatus( rowid, oldstatus, hasInvoice )
{
	var status = document.getElementById( 'status' + rowid ).selectedIndex;

	var rechnung = false;

	// nur bei status "warteliste" oder "noch nicht bestätigt"
	// zu status "eingegangen" oder "wird bearbeitet" oder "bezahlt"
	if ( ( oldstatus == 4 || oldstatus == 5 ) && ( status < 3 ) ) {
		// wenn in den optionen eingestellt ist, dass keine Rechnung erstellt werden soll, braucht man auch die Abfrage nicht
		if ( <?php echo $params->get('invoice_generate') ?> == 1 ) {
			// Die Frage ist etwas anders wenn die Rechnung auch verschickt werden soll (ebenfalls eine Option im Backend)
			if ( <?php echo $params->get('invoice_attach') ?> == 1 ) {
				if ( hasInvoice ) {
					rechnung = confirm( "<?php echo JText::_('COM_SEMINARMAN_SEND_INVOICE_AFTER_WAITLIST_AGAIN') ?>" );
				}
				else {
					rechnung = confirm( "<?php echo JText::_('COM_SEMINARMAN_SEND_INVOICE_AFTER_WAITLIST') ?>" );
				}
			}
			else {
				if ( hasInvoice ) {
					rechnung = confirm( "<?php echo JText::_('COM_SEMINARMAN_SEND_INVOICE_AFTER_WAITLIST_AGAIN_NO_SENDMAIL') ?>" );
				}
				else {
					rechnung = confirm( "<?php echo JText::_('COM_SEMINARMAN_SEND_INVOICE_AFTER_WAITLIST_NO_SENDMAIL') ?>" );
				}
			}
		}
	}
	if ( rechnung ) {
		window.top.location = "index.php?option=com_seminarman&controller=application&task=setstatus&status=" + status + '&cid=' + rowid + '&invoice=' + 1 + '&old=' + oldstatus + '&' + "<?php if (version_compare($short_version, "3.0", 'ge')):  echo JSession::getFormToken(); else: echo JUtility::getToken(); endif;?>" + '=1';
	}
	else {
		window.top.location = "index.php?option=com_seminarman&controller=application&task=setstatus&status=" + status + '&cid=' + rowid + '&invoice=' + 0 + '&old=' + oldstatus + '&' + "<?php if (version_compare($short_version, "3.0", 'ge')):  echo JSession::getFormToken(); else: echo JUtility::getToken(); endif;?>" + '=1';
	}
		
}

function setStatusSelected()
{
	var status = document.getElementById( 'statuslistall' ).value;
	var cbs = document.getElementsByName( 'cid[]' );
	var cidstring='';
	var empty = 1;
	var rechnung = false;
	var question = 0;

	// falls eh keine rechnung generiert werden soll, braucht die Frage nicht gestellt werden und die rechnung ist eh false
	if ( <?php echo $params->get('invoice_generate') ?> == 0 ) {
		question = 1;
		rechnung = false;
	}
	
	for ( var i in cbs ) {
		if ( cbs[i].checked ) {
			empty = 0;
			var cbvalue = cbs[i].value;
			var oldstatus = document.getElementById( 'status' + cbvalue ).selectedIndex;
			alert( "Question: " + question + " Oldstatus: " + oldstatus );
			if ( question == 0 && ( oldstatus == 4 || oldstatus == 5 ) && status < 3 ) {
				question = 1;

				if ( <?php echo $params->get('invoice_attach') ?> == 1 ) {
					rechnung = confirm( "<?php echo JText::_('COM_SEMINARMAN_SEND_INVOICES_AFTER_WAITLIST') ?>" );
				}
				else {
					rechnung = confirm( "<?php echo JText::_('COM_SEMINARMAN_SEND_INVOICES_AFTER_WAITLIST_NO_SENDMAIL') ?>" );
				}
			}
				
			cidstring = cidstring + '&cid[]=' + cbs[i].value;
		}
	}

	if ( !empty ) {
		var invoice = 0;
		if ( rechnung ) {
			invoice = 1;
		}
		window.top.location = "index.php?option=com_seminarman&controller=application&task=setstatusselected&status=" + status + cidstring + '&invoice=' + invoice + '&' + "<?php if (version_compare($short_version, "3.0", 'ge')):  echo JSession::getFormToken(); else: echo JUtility::getToken(); endif;?>" + '=1';
	}
	
}

</script>

<form action="<?php echo $this->requestURL; ?>" method="post" name="adminForm" id="adminForm">
<table class="adminform">
<tr>
   <td class="proc100 left" colspan="2">
      <?php echo JText::_('Filter'); ?>:
      <?php echo $this->lists['filter_search']; ?>
      <input type="text" name="search" id="search" value="<?php echo $this->lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
      <input type="button" onclick="this.form.submit();" value="<?php echo JText::_('COM_SEMINARMAN_GO');?>" />
      <input type="button" onclick="document.getElementById('search').value='';this.form.getElementById('filter_statusid').value='0';this.form.getElementById('filter_courseid').value='0';this.form.getElementById('filter_state').value='';this.form.submit();" value="<?php echo JText::_('COM_SEMINARMAN_RESET'); ?>" />
   </td>
</tr>
<tr>
   <td class="proc100 left">
      <?php echo JText::_('COM_SEMINARMAN_CHANGE_STATUS'); ?>:
   <?php
   	$statuslistall = null;
    $statuslistall[] = JHTML::_('select.option',  '', JText::_( 'JLIB_HTML_SELECT_STATE' ), 'value', 'text' );
    $statuslistall[] = JHTML::_('select.option',  '0', JText::_( 'COM_SEMINARMAN_SUBMITTED' ), 'value', 'text' );
    $statuslistall[] = JHTML::_('select.option',  '1', JText::_( 'COM_SEMINARMAN_PENDING' ), 'value', 'text' );
    $statuslistall[] = JHTML::_('select.option',  '2', JText::_( 'COM_SEMINARMAN_PAID' ), 'value', 'text' );
    $statuslistall[] = JHTML::_('select.option',  '3', JText::_( 'COM_SEMINARMAN_CANCELED' ), 'value', 'text' );
    $statuslistall[] = JHTML::_('select.option',  '4', JText::_( 'COM_SEMINARMAN_WL' ), 'value', 'text' );
    $statuslistall[] = JHTML::_('select.option',  '5', JText::_( 'COM_SEMINARMAN_AWAITING_RESPONSE' ), 'value', 'text' );
//    $thisstatuslistall = JHTML::_('select.genericlist', $statuslistall, 'statuslistall', 'onchange="class="inputbox" size="1"','value', 'text', $row->status );
    $thisstatuslistall = JHTML::_('select.genericlist', $statuslistall, 'statuslistall', 'onchange="" class="inputbox" size="1"','value', 'text' );
    echo $thisstatuslistall;
    ?>
   		<input type="button" onclick="setStatusSelected()" value="<?php echo JText::_('COM_SEMINARMAN_CHANGE_STATUS'); ?>" />
   </td>
   <td>
      <?php echo $this->lists['statusid']; echo $this->lists['courseid']; echo $this->lists['state']; ?>
   </td>
</tr>
</table>


<div id="editcell">
   <table class="adminlist table-striped">
   <thead>
      <tr>
         <th width="5"><?php echo JText::_('COM_SEMINARMAN_NUM'); ?></th>
         <?php if(JVERSION >= 3.0): ?>
         	<th width="20"><?php echo JHtml::_('grid.checkall'); ?></th>
         <?php else: ?>
         	<th width="20"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->applications); ?>);" /></th>
         <?php endif; ?>
         <th width="9%" class="title"><?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_LAST_NAME', 'a.last_name', $this->lists['order_Dir'], $this->lists['order']); ?></th>
         <th width="9%"><?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_FIRST_NAME', 'a.first_name', $this->lists['order_Dir'], $this->lists['order']); ?></th>
         <th width="12%"><?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_EMAIL', 'a.email', $this->lists['order_Dir'], $this->lists['order']); ?></th>
         <th width="12%"><?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_COURSE', 'j.title', $this->lists['order_Dir'], $this->lists['order']); ?></th>
         <th width="12%"><?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_COURSE_CODE', 'j.code', $this->lists['order_Dir'], $this->lists['order']); ?></th>
         <th width="5%"><?php echo JHTML::_('grid.sort',  'COM_SEMINARMAN_ATTENDEES', 'a.attendees', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
         <th width="5%"><?php echo JHTML::_('grid.sort',  'COM_SEMINARMAN_STATUS', 'a.status', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
         <?php if (($params->get('invoice_generate') == 1) || (SeminarmanFunctions::isSmanbookingPlgEnabled() && $params->get('advanced_booking'))): ?>
           <th width="8%"><?php echo JText::_('COM_SEMINARMAN_INVOICE'); ?></th>
         <?php endif; ?>
         <th width="8%"><?php echo JText::_('COM_SEMINARMAN_CERTIFICATE'); ?></th>
         <th width="5%"><?php echo JHTML::_('grid.sort', 'JPUBLISHED', 'a.published', $this->lists['order_Dir'], $this->lists['order']); ?></th>
         <th width="5%"><?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_SAVE_DATE', 'a.date', $this->lists['order_Dir'], $this->lists['order']); ?></th>
         <th width="8%"><span style="float:left"><?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_ORDER', 'a.ordering', $this->lists['order_Dir'], $this->lists['order']); ?></span><?php echo JHTML::_('grid.order', $this->applications); ?></th>
         <th width="1%"><?php echo JHTML::_('grid.sort', 'COM_SEMINARMAN_ID', 'a.id', $this->lists['order_Dir'], $this->lists['order']); ?></th>
      </tr>
   </thead>
   <tfoot>
      <tr>
         <td colspan="<?php echo (($params->get('invoice_generate') == 1)||(SeminarmanFunctions::isSmanbookingPlgEnabled() && $params->get('advanced_booking'))) ? 15 : 14; ?>"><?php echo $this->pagination->getListFooter(); ?></td>
      </tr>
   </tfoot>
   <tbody>
   <?php

$k = 0;
for ($i = 0, $n = count($this->applications); $i < $n; $i++) {
	
    $row = &$this->applications[$i];
    $ordering = ($this->lists['order'] == 'a.ordering');

    $app_params_obj = new JRegistry();
    $app_params_obj->loadString($row->params);
    $app_params = $app_params_obj->toArray();
    
    $extra_fee_open = false;
    if (isset($app_params['fee1_selected']) && ($app_params['fee1_selected']==1)) {
    	if (isset($app_params['fee1_value']) && ($app_params['fee1_value']>0)) {
    		$extra_fee_open = true;
    	}
    }
    
	$hasInvoice = 0;
    if (!empty($row->invoice_filename_prefix)) {
    	$invoiceLink = '<a href="'. JRoute::_('index.php?option=com_seminarman&view=application&layout=invoicepdf&cid[]='. $row->id ) .'">'.
    			'<img style="vertical-align: middle;" alt="'.$row->invoice_filename_prefix.$row->invoice_number.'.pdf" src="../components/com_seminarman/assets/images/mime-icon-16/pdf.png" >'.
    			' '.$row->invoice_number.'</a>';
    	$hasInvoice = 1;
    } else {
    	$invoiceLink = '-';
    }
    
    $hasCert = 0;
    if (!empty($row->certificate_file))
    {
    	$certLink = '<a title="'.$row->certificate_file.'" href="'. JRoute::_('index.php?option=com_seminarman&view=application&layout=certificatepdf&cid[]='. $row->id ) .'">'.
    			'<img style="vertical-align: middle;" alt="'.$row->certificate_file.'" src="../components/com_seminarman/assets/images/mime-icon-16/pdf.png"></a>';
    	$hasCert = 1;
    }
    else
    	$certLink = '-';    
    
    // build status list
   	$statuslistall = null;
    $statuslistall[] = JHTML::_('select.option',  '0', JText::_( 'COM_SEMINARMAN_SUBMITTED' ), 'value', 'text' );
    $statuslistall[] = JHTML::_('select.option',  '1', JText::_( 'COM_SEMINARMAN_PENDING' ), 'value', 'text' );
    $statuslistall[] = JHTML::_('select.option',  '2', JText::_( 'COM_SEMINARMAN_PAID' ), 'value', 'text' );
    $statuslistall[] = JHTML::_('select.option',  '3', JText::_( 'COM_SEMINARMAN_CANCELED' ), 'value', 'text' );
    $statuslistall[] = JHTML::_('select.option',  '4', JText::_( 'COM_SEMINARMAN_WL' ), 'value', 'text' );
    $statuslistall[] = JHTML::_('select.option',  '5', JText::_( 'COM_SEMINARMAN_AWAITING_RESPONSE' ), 'value', 'text' );
    $thisstatuslist = JHTML::_('select.genericlist', $statuslistall, 'status'.$row->id, 'onchange="setStatus('.$row->id.', '.$row->status.', '.$hasInvoice.')" class="inputbox" size="1"','value', 'text', $row->status );
    
?>
      <tr class="<?php echo "row$k"; ?>">
         <td><?php echo $this->pagination->getRowOffset($i); ?></td>
         <td><?php echo JHTML::_('grid.checkedout', $row, $i); ?></td>
         <td>
<?php
	$result = 0;
	if ($row instanceof JTable)
	{
		$result = $row->isCheckedOut( $this->user->get( 'id' ) );
	}
	
	if ( $result )
		echo $this->escape($row->title);
	else {
?>
            <span class="editlinktip hasTip" title="<?php echo JText::_('COM_SEMINARMAN_EDIT_APPLICATION'); ?>::<?php echo $this->escape($row->salutation) . " " . $this->escape($row->first_name) . " " . $this->escape($row->last_name); ?>">
               <a href="<?php echo JRoute::_('index.php?option=com_seminarman&controller=application&task=edit&cid[]='. $row->id); ?>"><?php echo $this->escape($row->last_name); ?></a>
            </span>
<?php    } ?>
         </td>
         <td class="centered"><?php echo $this->escape($row->first_name); ?></td>
         <td class="centered"><?php echo ('<a style="font-size: 1em;" href="mailto:'.$this->escape($row->email).'">'.$this->escape($row->email).'</a>');?></td>
         <td class="centered"><span class="editlinktip hasTip" title="<?php echo JText::_('COM_SEMINARMAN_VIEW_COURSE_DETAILS'); ?>::<?php

    echo $this->escape($row->title).'<br />';
    echo JText::_('COM_SEMINARMAN_START_DATE') .  ': '.$this->escape($row->start_date).'<br />';
    echo JText::_('COM_SEMINARMAN_FINISH_DATE') . ': '.$this->escape($row->finish_date).'<br />';

?>">
               <a href="<?php echo JRoute::_('index.php?option=com_seminarman&controller=courses&task=edit&cid[]='. $this->escape($row->courseid)); ?>"><?php echo $this->escape($row->title); ?></a></span>
         </td>
         <td class="centered"><?php echo $this->escape($row->code); ?></td>
         <td class="centered"><?php echo $this->escape($row->attendees); ?></td>
         <td class="centered">
         <?php echo $thisstatuslist; ?>
         </td>
         <?php if (($params->get('invoice_generate') == 1)||(SeminarmanFunctions::isSmanbookingPlgEnabled() && $params->get('advanced_booking'))): ?>
           <td class="centered"><?php echo $invoiceLink; ?></td>
         <?php endif; ?>
         <td class="centered"><?php echo $certLink; ?></td>
         <td class="centered"><?php echo JHTML::_('jgrid.published', $row->published, $i); ?></td>
         <td class="centered"><?php echo JHTML::date($row->date, JText::_('COM_SEMINARMAN_DATETIME_FORMAT1')); ?></td>
         <td class="order">
          	<span><?php echo $this->pagination->orderUpIcon($i, (true), 'orderup', 'Move Up', $ordering); ?></span>
          	<span><?php echo $this->pagination->orderDownIcon($i, $n, (true), 'orderdown', 'Move Down', $ordering); ?></span>
            <?php $disabled = $ordering ? '' : 'disabled="disabled"'; ?>
            <input type="text" name="order[]" size="2" value="<?php echo $row->ordering; ?>" <?php echo $disabled; ?> class="text_area centered pull-right" />
         </td>
         <td class="centered"><?php echo $row->id; ?>
         </td>
      </tr>
<?php
    $k = 1 - $k;
}
?>
   </tbody>
   </table>
</div>

   <input type="hidden" name="option" value="com_seminarman" />
   <input type="hidden" name="task" value="" />
   <input type="hidden" name="controller" value="application" />
   <input type="hidden" name="view" value="applications" />
   <input type="hidden" name="boxchecked" value="0" />
   <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
   <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
   <?php echo JHTML::_('form.token'); ?>
</form>