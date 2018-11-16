<?php
/**
* @Copyright Copyright (C) 2010 www.profinvent.com. All rights reserved.
* Copyright (C) 2011 Open Source Group GmbH www.osg-gmbh.de
* @website http://www.profinvent.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

defined('_JEXEC') or die('Restricted access');

?>

	<table style="border-collapse: collapse; border-spacing: 0; width: 100%; border: 0;">
		<tr>
			<td class="vtop">
			<div class="adminlist">
            <div class="cpanel-left">
						<div id="cpanel">
						<?php

global $option;

$params = JComponentHelper::getParams('com_seminarman');

if(JHTMLSeminarman::UserIsCourseManager()){

		$link = 'index.php?option=com_seminarman&amp;view=applications';
		SeminarmanViewSeminarman::quickiconButton($link, 'icon-48-applications.png', JText::_('COM_SEMINARMAN_APPLICATION'));
		
		$link = 'index.php?option=com_seminarman&amp;view=salesprospects';
		SeminarmanViewSeminarman::quickiconButton($link, 'icon-48-inter.png', JText::_('COM_SEMINARMAN_LST_OF_SALES_PROSPECTS'));
		
		$link = 'index.php?option=com_seminarman&amp;view=courses';
		SeminarmanViewSeminarman::quickiconButton($link, 'icon-48-courses.png', JText::_('COM_SEMINARMAN_COURSES'));
		
		$link = 'index.php?option=com_seminarman&amp;view=course';
		SeminarmanViewSeminarman::quickiconButton($link, 'icon-48-courseedit.png', JText::_('COM_SEMINARMAN_NEW_COURSE'));
		
		$link = 'index.php?option=com_seminarman&amp;view=templates';
		SeminarmanViewSeminarman::quickiconButton($link, 'icon-48-templates.png', JText::_('COM_SEMINARMAN_TEMPLATES'));
		
		$link = 'index.php?option=com_seminarman&amp;view=templates';
		SeminarmanViewSeminarman::quickiconButton($link, 'icon-48-templateedit.png', JText::_('COM_SEMINARMAN_NEW_TEMPLATE'));
		
		$link = 'index.php?option=com_seminarman&amp;view=tags';
		SeminarmanViewSeminarman::quickiconButton($link, 'icon-48-tags.png', JText::_('COM_SEMINARMAN_TAGS'));
		
		$link = 'index.php?option=com_seminarman&amp;view=categories';
		SeminarmanViewSeminarman::quickiconButton($link, 'icon-48-categories.png', JText::_('COM_SEMINARMAN_CATEGORIES'));
		
		$link = 'index.php?option=com_seminarman&amp;view=tutors';
		SeminarmanViewSeminarman::quickiconButton($link, 'icon-48-tutors.png', JText::_('COM_SEMINARMAN_TUTORS'));
		
		$link = 'index.php?option=com_seminarman&amp;view=users';
		SeminarmanViewSeminarman::quickiconButton($link, 'icon-48-user.png', JText::_('COM_SEMINARMAN_USERS'));
		
		$link = 'index.php?option=com_seminarman&amp;view=settings';
		SeminarmanViewSeminarman::quickiconButton($link, 'icon-48-config.png', JText::_('COM_SEMINARMAN_SETTINGS'));
		
		$link = 'index.php?option=com_installer&view=update';
		$current_product_xml =  JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_seminarman'.DS.'seminarman.xml';
		$current_sman_info = simplexml_load_file($current_product_xml);
		$current_sman_version = $current_sman_info->version;
		$update_product_xml = 'http://smanupdate.osg-gmbh.de/sman/osgseminarman_update.xml';
		$update_xml_headers=get_headers($update_product_xml);
		if (stripos($update_xml_headers[0],"200 OK") && file_get_contents($update_product_xml)) {
		    $update_sman_info = simplexml_load_file($update_product_xml);
		    $update_sman_version = $update_sman_info->update->version;
		} else {
		    $update_sman_info = false;
		    $update_sman_version = false;
		}
		if ($update_sman_info == false) {
		    SeminarmanViewSeminarman::quickiconButton($link, 'nosupport-32.png', JText::_('COM_SEMINARMAN_LIVEUPDATE_ICON_UNSUPPORTED'));
		} else {
		    if (version_compare($current_sman_version, $update_sman_version, 'lt')) {
		        
		        if( preg_match('#^[0-9\.]*a[0-9\.]*#', $update_sman_version) == 1 ) {
		            $stability = 'alpha';
		        } elseif( preg_match('#^[0-9\.]*b[0-9\.]*#', $update_sman_version) == 1 ) {
		            $stability = 'beta';
		        } elseif( preg_match('#^[0-9\.]*rc[0-9\.]*#', $update_sman_version) == 1 ) {
		            $stability = 'rc';
		        } elseif( preg_match('#^[0-9\.]*$#', $update_sman_version) == 1 ) {
		            $stability = 'stable';
		        } else {
		            $stability = 'svn';
		        }
		        
		        $minStability = $params->get('minstability', 'stable');
		        $update_check = true;
		        switch($minStability) {
		            case 'alpha':
		            default:
		                // Reports any stability level as an available update
		                break;
		                
		            case 'beta':
		                // Do not report alphas as available updates
		                if(in_array($stability, array('alpha'))) $update_check = false;
		                break;
		                
		            case 'rc':
		                // Do not report alphas and betas as available updates
		                if(in_array($stability, array('alpha','beta'))) $update_check = false;
		                break;
		                
		            case 'stable':
		                // Do not report alphas, betas and rcs as available updates
		                if(in_array($stability, array('alpha','beta','rc'))) $update_check = false;
		                break;
		        }
		        if ($update_check) {
		            SeminarmanViewSeminarman::quickiconButton($link, 'update-32.png', JText::_('COM_SEMINARMAN_LIVEUPDATE_ICON_UPDATES'));
		        } else {
		            SeminarmanViewSeminarman::quickiconButton($link, 'current-32.png', JText::_('COM_SEMINARMAN_LIVEUPDATE_ICON_CURRENT'));
		        }
		    } else {
		        SeminarmanViewSeminarman::quickiconButton($link, 'current-32.png', JText::_('COM_SEMINARMAN_LIVEUPDATE_ICON_CURRENT'));
		    }
		}

} else {
	
		$link = 'index.php?option=com_seminarman&amp;view=applications';
		SeminarmanViewSeminarman::quickiconButton($link, 'icon-48-applications.png', JText::_('COM_SEMINARMAN_APPLICATION'));
		
		$link = 'index.php?option=com_seminarman&amp;view=salesprospects';
		if ($params->get('tutor_access_sales_prospects')) SeminarmanViewSeminarman::quickiconButton($link, 'icon-48-inter.png', JText::_('COM_SEMINARMAN_LST_OF_SALES_PROSPECTS'));
		
		$link = 'index.php?option=com_seminarman&amp;view=courses';
		SeminarmanViewSeminarman::quickiconButton($link, 'icon-48-courses.png', JText::_('COM_SEMINARMAN_COURSES'));
		
		$link = 'index.php?option=com_seminarman&amp;view=course';
		SeminarmanViewSeminarman::quickiconButton($link, 'icon-48-courseedit.png', JText::_('COM_SEMINARMAN_NEW_COURSE'));	
		
		$link = 'index.php?option=com_seminarman&amp;view=tags';
		if ($params->get('tutor_access_tags')) SeminarmanViewSeminarman::quickiconButton($link, 'icon-48-tags.png', JText::_('COM_SEMINARMAN_TAGS'));
		
		if ($params->get('tutor_access_tutors')) {
			$tutor_id = JHTMLSeminarman::getUserTutorID();
		    $link = 'index.php?option=com_seminarman&controller=tutor&task=edit&cid[]='.$tutor_id;
		    SeminarmanViewSeminarman::quickiconButton($link, 'icon-48-tutors.png', JText::_('COM_SEMINARMAN_TUTORS'));
		}
}
?>
						</div>
						
<?php if (JHTMLSeminarman::UserIsCourseManager() || ($params->get('display_product_info'))): ?>						
						
						<div style="clear: both; margin-bottom: 20px;">
							<div style="background-color: #fff;border: 1px solid #ccc;border-radius: 5px;color: #565656;display: block;float: left;padding: 10px;margin-bottom: 15px;text-decoration: none;">
								<h3 style="margin-top: 0;"><?php echo JText::_('COM_SEMINARMAN_EXTENSIONS_INFO_LABEL'); ?></h3>
								<?php echo JText::sprintf('COM_SEMINARMAN_EXTENSIONS_INFO_DESC', '<a href="http://service.osg-gmbh.de/en/products/category/6-extensions-seminarman-premium" target=_blank>', '</a>'); ?>
							</div>
							<br /><br />
							<div style="clear: left;">
								<a href="https://service.osg-gmbh.de/en/documentation-osg-seminar-manager" target=_blank class="osg_support_button osg_support_white nowrap">Documentation</a><br /><br />
								<a href="http://service.osg-gmbh.de/en/forum/" target=_blank class="osg_support_button osg_support_white nowrap">Forum</a>
							</div>
						</div>

<div style="clear: both;">
<?php echo JText::sprintf('COM_SEMINARMAN_LICENSE', '<a href="http://www.gnu.org/licenses/gpl-3.0.de.html" target=_blank>', '</a>'); ?>
</div>

<?php endif; ?>					
                        </div>
                        
<?php 
    if (JHTMLSeminarman::UserIsCourseManager() || ($params->get('display_product_copyright')) || ($params->get('display_latest_feeds'))) {
    	$hide_right_pan = '';
    } else {
    	$hide_right_pan = ' style="display: none;"';
    }
?>                        
                        
                    <div class="cpanel-right" <?php echo $hide_right_pan; ?>>
<?php
if (JHTMLSeminarman::UserIsCourseManager() || ($params->get('display_latest_feeds'))):
$title = JText::_('COM_SEMINARMAN_LATEST_APPLICATIONS');
echo $this->pane->startPane('stat-pane');
echo $this->pane->startPanel($title, 'latestApplications');

?>
				<table class="adminlist">
				<?php

$k = 0;
$n = count($this->latestApplications);
for ($i = 0, $n; $i < $n; $i++)
{
    $row = $this->latestApplications[$i];
    $link = 'index.php?option=com_seminarman&amp;controller=application&amp;task=edit&amp;cid[]=' .
        $row->id;

?>
					<tr>
						<td>
								<a href="<?php

    echo $link;

?>">
									<?php

    echo htmlspecialchars($row->title . " - " . $row->first_name . " " . $row->
        last_name . " " . JHTML::date($row->date), ENT_QUOTES, 'UTF-8');

?>
								</a>
						</td>
					</tr>
					<?php

    $k = 1 - $k;
}

?>
				</table>
				<?php

$title = JText::_('COM_SEMINARMAN_ADDED_COURSES');
echo $this->pane->endPanel();
echo $this->pane->startPanel($title, 'latestJobs');

?>
				<table class="adminlist">
				<?php

$k = 0;
$n = count($this->latestJobs);
for ($i = 0, $n; $i < $n; $i++)
{
    $row = $this->latestJobs[$i];
    $link = 'index.php?option=com_seminarman&amp;controller=courses&amp;task=edit&amp;cid[]=' .
        $row->id;

?>
					<tr>
						<td>
								<a href="<?php

    echo $link;

?>">
									<?php

    echo htmlspecialchars($row->title, ENT_QUOTES, 'UTF-8');

?>
								</a>
						</td>
					</tr>
					<?php

    $k = 1 - $k;
}

?>
				</table>
				<?php
echo $this->pane->endPanel();
echo $this->pane->endPane();
endif;
?>
<?php if (JHTMLSeminarman::UserIsCourseManager() || ($params->get('display_product_copyright'))): ?>
<div style="text-align:center; margin-top: 100px;">
 <a href="http://www.osg-gmbh.de" target="_blank"><img src="components/com_seminarman/assets/images/logo.png" width=90 alt="Open Source Group Logo" title="Open Source Group Gmbh"></a>
<br><br><a href="http://www.osg-gmbh.de" target="_blank" title="Open Source Group Gmbh">Open Source Group GmbH</a><br><br><br>
<div align="center">Joomla! extension <b>OSG Seminar Manager</b> at<br>
<a href="http://sman.osg-gmbh.de" target="_blank">sman.osg-gmbh.de</a>
<br><br><?php echo JText::_('COM_SEMINARMAN_VOTE'); ?> <a href="http://extensions.joomla.org/extensions/living/education-a-culture/courses/18600" target="_blank">Joomla! Extensions Directory</a>.</div>
<br><br></div>
<?php endif; ?>

                    </div></div>                        
			</td>
		</tr>
<?php if (JHTMLSeminarman::UserIsCourseManager() || ($params->get('display_product_info'))): ?>		
		<tr><td colspan="2"><div style="text-align: center; margin-top:15px;">OSG Seminar Manager <?php 
		$current_product_xml =  JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_seminarman'.DS.'seminarman.xml';
		$current_sman_info = simplexml_load_file($current_product_xml);
		echo $current_sman_info->version;
		?></div></td></tr>
<?php endif; ?>
	</table>