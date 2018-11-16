<br>
<div class="centered">
<table style="margin: 0 auto;">
  <tr>
    <td align="center"><br>
    <a href="http://www.osg-gmbh.de" target="_blank"><img src="components/com_seminarman/assets/images/smlogo.png" width=90 height=90 alt="OSG Seminar Manager" title="OSG Seminar Manager"></a>
    </td>
    <td><b><br><br><br> OSG Seminar Manager <?php 
        $current_product_xml =  JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_seminarman'.DS.'seminarman.xml';
        $current_sman_info = simplexml_load_file($current_product_xml);
        echo $current_sman_info->version;
		?></b><br><br>
	</td>
 </tr>
</table>
<br><br>
<a href="http://www.osg-gmbh.de" target="_blank"><img src="components/com_seminarman/assets/images/logo.png" width=90 alt="Open Source Group Logo" title="Open Source Group Gmbh"></a>
<br><br><a href="http://www.osg-gmbh.de" target="_blank" title="Open Source Group Gmbh">Open Source Group GmbH</a><br><br><br>
<div align="center">Joomla! extension <b>OSG Seminar Manager</b> at<br>
<a href="http://sman.osg-gmbh.de" target="_blank">sman.osg-gmbh.de</a>
<br><br><?php echo JText::_('COM_SEMINARMAN_VOTE'); ?> <a href="http://extensions.joomla.org/extensions/living/education-a-culture/courses/18600" target="_blank">Joomla! Extensions Directory</a>.<br><br>
</div>
<br><br>
</div>