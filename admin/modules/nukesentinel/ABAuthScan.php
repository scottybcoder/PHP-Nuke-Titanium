<?php
/*=======================================================================
 PHP-Nuke Titanium v3.0.0 : Enhanced PHP-Nuke Web Portal System
 =======================================================================*/

/********************************************************/
/* NukeSentinel(tm)                                     */
/* By: NukeScripts(tm) (http://www.nukescripts.net)     */
/* Copyright (c) 2000-2008 by NukeScripts(tm)           */
/* See CREDITS.txt for ALL contributors                 */
/********************************************************/

if (!defined('NUKESENTINEL_ADMIN')) {
   die ('You can\'t access this file directly...');
}

if(is_god($admin)) {
  $importnamed = $exportnamed = '';
  $importadded = $exportadded = 0;
  $importad = $db->sql_query("SELECT `aid`, `name`, `pwd` FROM `".$prefix."_authors`");
  while(list($a_aid, $a_name, $a_pwd) = $db->sql_fetchrow($importad)) {
    $adminrow = $db->sql_numrows($db->sql_query("SELECT `aid` FROM `".$prefix."_nsnst_admins` WHERE `aid`='$a_aid'"));
    if($adminrow == 0) {
      $importadded++;
      if($importnamed == '') { $importnamed = $a_aid; } else { $importnamed = $importnamed.', '.$a_aid; }
      $makepass = "";
      $strs = "abc2def3ghj4kmn5opq6rst7uvw8xyz9";
      for($x=0; $x < 20; $x++) {
        mt_srand ((double) microtime() * 1000000);
        $str[$x] = substr($strs, mt_rand(0, strlen($strs)-1), 1);
        $makepass = $makepass.$str[$x];
      }
      $xpassword_md5 = md5($makepass);
      $xpassword_crypt = crypt($makepass);
      if(!get_magic_quotes_runtime()) { $makepass = addslashes($makepass); }
      if(strtolower($a_name) == "god") { $is_god = 1; } else { $is_god = 0; }
      $result = $db->sql_query("INSERT INTO `".$prefix."_nsnst_admins` (`aid`, `login`, `protected`, `password`, `password_md5`, `password_crypt`) VALUES ('$a_aid', '$a_aid', '$is_god', '$makepass', '$xpassword_md5', '$xpassword_crypt')");
      $db->sql_query("OPTIMIZE TABLE ".$prefix."_nsnst_admins");
      $aidrow = $db->sql_fetchrow($db->sql_query("SELECT * FROM `".$prefix."_nsnst_admins` WHERE `aid`='$a_aid' LIMIT 0,1"));
      $subject = _AB_ACCESSFOR." ".$nuke_config['sitename'];
      $message  = _AB_HTTPONLY."\n";
      $message .= _AB_LOGIN.': '.$aidrow['login']."\n";
      $message .= _AB_PASSWORD.': '.$aidrow['password']."\n";
      $message .= _AB_PROTECTED.': ';
      if($aidrow['protected']==0) { $message .= _AB_NO."\n"; } else { $message .= _AB_YES."\n"; }
      list($amail) = $db->sql_fetchrow($db->sql_query("SELECT `email` FROM `".$prefix."_authors` WHERE `aid`='$a_aid' LIMIT 0,1"));
      @evo_mail($amail, $subject, $message,"From: ".$nuke_config['adminmail']."\r\nX-Mailer: "._AB_NUKESENTINEL."\r\n");
    }
  }
  $exportad = $db->sql_query("SELECT `aid` FROM `".$prefix."_nsnst_admins`");
  while(list($a_aid) = $db->sql_fetchrow($exportad)) {
    $adminrow = $db->sql_numrows($db->sql_query("SELECT `aid` FROM `".$prefix."_authors` WHERE `aid`='$a_aid' LIMIT 0,1"));
    if($adminrow == 0) {
      $exportadded++;
      if($exportnamed == '') { $exportnamed = $a_aid; } else { $exportnamed = $exportnamed.', '.$a_aid; }
      $result = $db->sql_query("DELETE FROM `".$prefix."_nsnst_admins` WHERE `aid`='$a_aid' LIMIT 1");
      $db->sql_query("OPTIMIZE TABLE `".$prefix."_nsnst_admins`");
    }
  }
  include_once(NUKE_BASE_DIR.'header.php');
  OpenTable();
  OpenMenu(_AB_SCANADMINS);
  mastermenu();
  CarryMenu();
  authmenu();
  CloseMenu();
  CloseTable();
  echo '<br />'."\n";
  OpenTable();
  echo '<center><strong>'._AB_SCANADMINSDONE.'</strong></center><br />'."\n";
  echo '<center><strong>'._AB_ADMINSADDED.':</strong> '.$importadded;
  if($importnamed > '') { echo ' ('.$importnamed.')'; }
  echo '<br/>'."\n";
  echo '<strong>'._AB_ADMINSREMOVED.':</strong> '.$exportadded;
  if($exportnamed > '') { echo ' ('.$exportnamed.')'; }
  echo '</center><br />'."\n";
  echo '<center><a href="'.$admin_file.'.php?op=ABAuthList">'._AB_LISTHTTPAUTH.'</a></center>'."\n";
  CloseTable();
  include_once(NUKE_BASE_DIR.'footer.php');
}

?>