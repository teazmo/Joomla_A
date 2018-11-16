    <!--application form-->
<script type="text/javascript">
function setVisibility() {
    document.getElementById('course_appform').style.display = 'block';
}
function unsetVisibility() {
        document.getElementById('course_appform').style.display = 'none';
}
</script>

<h3 class="booking componentheading<?php echo $this->params->get('pageclass_sfx'); ?> underline"><a onclick="setVisibility();" href="<?php echo JURI::getInstance()->toString(); ?>#appform" id="appform">
<?php 
  if ( $this->show_application_form == 1 )   {
    echo JText::_('COM_SEMINARMAN_BOOK_COURSE') . ": " . $this->course->title; 
  } elseif ( $this->show_application_form == 2 ) {
  	echo JText::_('COM_SEMINARMAN_APPLY_WL_COURSE') . ": " . $this->course->title;
  }
?>
</a></h3>

    <div class="course_applicationform" id="course_appform">
        <?php if ($params->get('enable_loginform') == 1) echo '<h3 class="underline">' . JText::_('COM_SEMINARMAN_LOGIN_PLEASE') . '</h3>'; ?>
    <?php
        $module = JModuleHelper::getModule('mod_login','OSG Login');
        if ((!(is_null($module))) && ($params->get('enable_loginform') == 1)) echo JModuleHelper::renderModule($module);
        switch ($params->get('enable_bookings')) {
                case 3:
                        echo $this->loadTemplate('applicationform');
                        break;
                case 2:
                        echo $this->loadTemplate('applicationform');
                        break;
                case 1:
                        if ($this->user->get('guest')) {
                            echo JText::_('COM_SEMINARMAN_PLEASE_LOGIN_FIRST') .'.';
                        } else {
                            if ($this->params->get('user_booking_rules')==1){
                                $course_booking_permission = JHTMLSeminarman::check_booking_permission($this->course->id, $this->user->id);
                            } else {
                                $course_booking_permission = true;
                            }
                            if ($course_booking_permission) {    
                        	    echo  $this->loadTemplate('applicationform');
                        	} else {
                                echo JText::_('COM_SEMINARMAN_BOOKING_NOT_ALLOWED');
                            }
                        }
                        break;
                default:
                        echo JText::_('COM_SEMINARMAN_BOOKINGS_DISABLED') .'.';
        }

        // load mailplus plugin if available
        $dispatcher=JDispatcher::getInstance();
        JPluginHelper::importPlugin('seminarman');
        $html_tmpl=$dispatcher->trigger('onDisplayMailplusForCourse',array($this->course));  // we need the course attribs
        if (isset($html_tmpl) && !empty($html_tmpl)) echo $html_tmpl[0];

    ?>        
    </div>
<script type="text/javascript">
//Create Base64 Object for different browsers
var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/\r\n/g,"\n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}
var login_return_field = document.querySelector("#course_appform > #login-form input[name=return]");
if (login_return_field){
  var login_return_url = Base64.decode(login_return_field.value);
  if (login_return_url.indexOf("?buchung=1") > 0) {
	login_return_url = login_return_url + "#appform";
  } else {
	login_return_url = login_return_url + "?buchung=1#appform";
  }
login_return_field.value = Base64.encode(login_return_url);
}
</script>
<?php
if (!( isset($_GET['buchung']) && $_GET['buchung'] == 1 )) {
  echo '<script type="text/javascript">unsetVisibility();</script>';
}
?>