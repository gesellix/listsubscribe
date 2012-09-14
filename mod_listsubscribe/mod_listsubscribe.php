<?php

/**
 * ListSubscribe - A Mailman Subscriber module
 * @by Tobias Gesellchen, www.gesellix.de
 * @copyright (C) 2005-2012 Tobias Gesellchen http://www.gesellix.de/
 * @version 2.6
 * @licence GPL, but please send me your modifications to tobias@gesellix.de
 */

# Don't allow direct access to the file
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

$snoopy_instance;
$login_cookies_instance;

# Setup the module standard parameter settings
if (!isset($_POST['sendoption'])) {
//    $url          = sefRelToAbs($_SERVER['REQUEST_URI']);
   $url = $_SERVER['REQUEST_URI'];
   $introduction = $params->get('introduction');
   $option_add = $params->get('option_add');
   $option_del = $params->get('option_del');
   $defaulttext = $params->get('defaulttext');
   $defaulttextsize = $params->get('defaulttextsize');   
}
else {
   include 'modules/mod_listsubscribe/Snoopy.class.php';

   $defaulttext = $params->get('defaulttext');
   $defaulttextsize = $params->get('defaulttextsize');
   $requestto = $params->get('requestto');
   $mailsubject = $params->get('mailsubject');
   $mailfrom = $params->get('mailfrom');
   $defaultpass = $params->get('defaultpass');
   $mailmanurl = $params->get('mailmanurl');
   $mailmanadminpass = $params->get('mailmanadminpass');

   $maction = $_POST['maction'];
   $email = trim($_POST['email']);
}
?>

<!-- ListSubscribe - (C) 2005-2012 by Tobias Gesellchen, www.gesellix.de -->
<script language="JavaScript1.2" type="text/javascript">
   <!--
   function emailvalidate() {
      if (validateAddress(document.listsubscribeform.email.value)) {
         return true;
      }
      else {
         alert('Please enter a valid email address');
         return false;
      }
   }

   function validateAddress(incoming) {
      var emailstring = incoming;
      if (emailstring.length < 6) {
         return false
      }

      var ampIndex = emailstring.indexOf("@");
      if (ampIndex == -1) {
         return false
      }

      var afterAmp = emailstring.substring((ampIndex + 1), emailstring.length);
      if (afterAmp.length = 0) {
         return false
      }

      // find a dot in the portion of the string after the ampersand only
      var dotIndex = afterAmp.indexOf(".");

      // determine dot position in entire string (not just after amp portion)
      dotIndex = dotIndex + ampIndex + 1;

      // afterAmp will be portion of string from ampersand to dot
      afterAmp = emailstring.substring((ampIndex + 1), dotIndex);
      if (afterAmp.length = 0) {
         return false
      }

      // afterDot will be portion of string from dot to end of string
      var afterDot = emailstring.substring((dotIndex + 1), emailstring.length);
      if (afterDot.length = 0) {
         return false
      }

      var beforeAmp = emailstring.substring(0, (ampIndex));
      if (beforeAmp.length = 0) {
         return false
      }

      //old regex did not allow subdomains and dots in names
      //var email_regex = /^[\w\d\!\#\$\%\&\'\*\+\-\/\=\?\^\_\`\{\|\}\~]+(\.[\w\d\!\#\$\%\&\'\*\+\-\/\=\?\^\_\`\{\|\}\~])*\@(((\w+[\w\d\-]*[\w\d]\.)+(\w+[\w\d\-]*[\w\d]))|((\d{1,3}\.){3}\d{1,3}))$/;
      var email_regex = /^\w(?:\w|-|\.(?!\.|@))*@\w(?:\w|-|\.(?!\.))*\.\w{2,3}/;

      // index of -1 means "not found"
      return email_regex.test(emailstring);
   }
   //-->
</script>

<?php

switch ($_POST['sendoption']) {
   case "send":
      if (notValidEmail($email, $defaulttext)) {
         echo "<script type=\"text/javascript\"> alert('Please enter a valid email address'); window.history.back(); </script>\n";
         exit;
      }

      $nl = Chr(13) . Chr(10);
      $text = "";
      switch ($maction) {
         case "add":
            $text = "subscribe " . $defaultpass . " address=$email" . $nl . $nl;
            break;
         case "del":
            $text = "unsubscribe " . $defaultpass . " address=$email" . $nl . $nl;
            break;
      }

      if ($text == "") {
         echo "<script type=\"text/javascript\"> alert('Error encountered, please try again after reloading the site'); window.history.back(); </script>\n";
         exit;
      }

      if (trim($mailmanadminpass) == '') {
         if (!sendmessage($requestto, $mailfrom, $mailsubject, $text)) {
            ?>
         <div style="text-align: center;">
            <?php echo "The (un-)subscription mail could not be sent!"; ?><br/><br/>
            [ <a href="javascript:history.back()"><?php echo "Back"; ?></a> ]
         </div>
         <?php
         }
         else {
            $mactionText = (($maction == "add") ? "subscribed" : "unsubscribed");
            ?>
         <center>
            <?php echo "$email was $mactionText - You will receive a confirmation email shortly."; ?><br/><br/>
            [ <a href="javascript:history.back()"><?php echo "Back"; ?></a> ]
         </center>
         <?php
         }
      }
      else {
         switch ($maction) {
            case "add":
               Subscribe($email, $mailmanurl, $mailmanadminpass);
               ?>
               <div style="text-align: center;">
                  <?php echo "$email was subscribed - You will receive a welcome email shortly."; ?><br/><br/>
                  [ <a href="javascript:history.back()"><?php echo "Back"; ?></a> ]
               </div>
               <?php
               break;
            case "del":
               unSubscribe($email, $mailmanurl, $mailmanadminpass);
               ?>
               <div style="text-align: center;">
                  <?php echo "$email was unsubscribed."; ?><br/><br/>
                  [ <a href="javascript:history.back()"><?php echo "Back"; ?></a> ]
               </div>
               <?php
               break;
         }
      }

      break;

   default:

      #Bring it all to the screen
      ?>

      <?php if ($introduction) echo "<p>$introduction</p>"; ?>
      <form name="listsubscribeform" action="<?php echo $url; ?>" id="listsubscribeForm" method="post" onSubmit="return emailvalidate()">
         <input
	    size="<?php echo $defaulttextsize; ?>"
            alt="list subscribe input"
            type="text"
            name="email"
            value="<?php echo $defaulttext; ?>"
            class="inputbox"
            accesskey="n"
            id="listsubscribeInput"
            onblur="if(this.value=='') this.value='<?php echo $defaulttext; ?>';"
            onfocus="if(this.value=='<?php echo $defaulttext; ?>') this.value='';"
            />

         <select name="maction">
            <option value="add"><?php echo $option_add; ?></option>
            <option value="del"><?php echo $option_del; ?></option>
         </select>

         <input type="submit" name="Submit" class="button" value="GO"/>
         <input type="hidden" name="sendoption" id="sendoption" value="send"/>
      </form>

      <?php
      break;
} // end of switch(sendoption)


function sendmessage($requestto, $mailfrom, $mailsubject, $text) {
   $header = "From: $mailfrom\n";
   $header .= "Content-Type: text/plain\n";
   $header .= "Content-Transfer-Encoding: 8bit\n";
   $header .= "X-Mailer: mod_listSubscribe (c) 2005 Tobias Gesellchen\n";

   return @mail($requestto, $mailsubject, $text, $header);
}

function notValidEmail($emailtext, $defaulttext) {
   if (eregi("^([._a-z0-9-]+[._a-z0-9-]*)@(([a-z0-9-]+\.)*([a-z0-9-]+)(\.[a-z]{2,4}))$", $emailtext)) {
      return ($emailtext == $defaulttext);
   }
   else {
      return true;
   }
}

function getSnoopy() {
   global $snoopy_instance;

   if (!isset($snoopy_instance)) {
      $snoopy_instance = new Snoopy();
   }
   return $snoopy_instance;
}

function mailman_login($mailmanurl, $adminpasswd) {
   global $login_cookies_instance;

   if (!isset($login_cookies)) {
      $submit['adminpw'] = $adminpasswd;
      $s = getSnoopy();
      $s->submit($mailmanurl, $submit);

      while (list($key, $val) = each($s->headers)) {
         list ($title, $value) = split(":", $val);

         if ($title == 'Set-Cookie') {
            $cookieList = split(';', $value);
            while (list($k, $v) = each($cookieList)) {
               list($cookieKey, $cookieVal) = split('=', trim($v));
               $login_cookies_instance[trim($cookieKey)] = trim($cookieVal);
            }
         }
      }
   }
   return $login_cookies_instance;
}

function Subscribe($email, $mailmanurl, $adminpasswd) {
   $login_cookies = mailman_login($mailmanurl, $adminpasswd);
   $url = $mailmanurl . '/members/add';
   $postdata['subscribe_or_invite'] = '0'; //$params->get('new_invite_only', '0');
   $postdata['send_welcome_msg_to_this_batch'] = '1'; //$params->get('new_welcome_msg', '0');
   $postdata['send_notifications_to_list_owner'] = '1'; //$params->get('new_send_notifications_to_list_owner', '0');
   $postdata['subscribees'] = $email . "\n";
   $postdata['invitation'] = "";

   $s = getSnoopy();
   $s->cookies = $login_cookies;
   $s->submittext($url, $postdata);
}

function unSubscribe($email, $mailmanurl, $adminpasswd) {
   $login_cookies = mailman_login($mailmanurl, $adminpasswd);
   $url = $mailmanurl . '/members/remove';
   $postdata['send_unsub_ack_to_this_batch'] = '0'; //$params->get('delete_send_ack', '0');
   $postdata['send_unsub_notifications_to_list_owner'] = '0'; //$params->get('delete_send_notifications_to_list_owner', '0');
   $postdata['unsubscribees'] = $email . "\n";

   $s = getSnoopy();
   $s->cookies = $login_cookies;
   $s->submittext($url, $postdata);
}

?>
