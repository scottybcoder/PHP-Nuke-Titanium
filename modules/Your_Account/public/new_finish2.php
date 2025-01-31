<?php
/*=======================================================================
 Nuke-Evolution Basic: Enhanced PHP-Nuke Web Portal System
 =======================================================================*/

/*********************************************************************************/
/* CNB Your Account: An Advanced User Management System for phpnuke             */
/* ============================================                                 */
/*                                                                              */
/* Copyright (c) 2004 by Comunidade PHP Nuke Brasil                             */
/* http://dev.phpnuke.org.br & http://www.phpnuke.org.br                        */
/*                                                                              */
/* Contact author: escudero@phpnuke.org.br                                      */
/* International Support Forum: http://ravenphpscripts.com/forum76.html         */
/*                                                                              */
/* This program is free software. You can redistribute it and/or modify         */
/* it under the terms of the GNU General Public License as published by         */
/* the Free Software Foundation; either version 2 of the License.               */
/*                                                                              */
/*********************************************************************************/
/* CNB Your Account it the official successor of NSN Your Account by Bob Marion    */
/*********************************************************************************/

/*****[CHANGES]**********************************************************
-=[Base]=-
      Nuke Patched                             v3.1.0       06/26/2005
      NukeSentinel                             v2.5.00      07/11/2006
      Evolution Functions                      v1.5.0       12/20/2005
 ************************************************************************/

if (!defined('MODULE_FILE')) {
   die ("You can't access this file directly...");
}

if (!defined('CNBYA')) {
    die('CNBYA protection');
}

    include_once(NUKE_BASE_DIR.'header.php');
    $ya_user_email = strtolower($ya_user_email);
    ya_userCheck($ya_username);
    ya_mailCheck($ya_user_email);
    $user_regdate = date("M d, Y");
    if (!isset($stop)) {
        $datekey = date("F j");
        $rcode = hexdec(md5($_SERVER['HTTP_USER_AGENT'] . $sitekey . $random_num . $datekey));
        $code = substr($rcode, 2, $ya_config['codesize']);

        if (GDSUPPORT AND $code != $gfx_check AND ($ya_config['usegfxcheck'] == 3 OR $ya_config['usegfxcheck'] == 4 OR $ya_config['usegfxcheck'] == 6)) {

            redirect("modules.php?name=$module_name");
            exit;
        }
        mt_srand ((double)microtime()*1000000);
        $maxran = 1000000;
        $check_num = mt_rand(0, $maxran);
        $check_num = md5($check_num);
        $time = time();
        $finishlink = "$nukeurl/modules.php?name=Profile&mode=register&agreed=true&check_num=$check_num";
/*****[BEGIN]******************************************
 [ Base:     Evolution Functions               v1.5.0 ]
 ******************************************************/
        $new_password = md5($user_password);
/*****[END]********************************************
 [ Base:     Evolution Functions               v1.5.0 ]
 ******************************************************/
        $ya_username = check_html($ya_username, 'nohtml');
        $ya_realname = check_html($ya_realname, 'nohtml');
        $ya_user_email = check_html($ya_user_email, 'nohtml');
        list($newest_uid) = $db->sql_fetchrow($db->sql_query("SELECT max(user_id) AS newest_uid FROM ".$user_prefix."_users_temp"));
        if ($newest_uid == "-1") { $new_uid = 1; } else { $new_uid = $newest_uid+1; }
        $result = $db->sql_query("INSERT INTO ".$user_prefix."_users_temp (user_id, username, realname, user_email, user_password, user_regdate, check_num, time) VALUES ($new_uid, '$ya_username', '$ya_realname', '$ya_user_email', '$new_password', '$user_regdate', '$check_num', '$time')");

        if (is_array($nfield)):

            if ((count($nfield) > 0) AND ($result)) {
                foreach ($nfield as $key => $var) {
                    $db->sql_query("INSERT INTO ".$user_prefix."_cnbya_value_temp (uid, fid, value) VALUES ('$new_uid', '$key','$nfield[$key]')");
                }
            }

        endif;

        if(!$result) {
            OpenTable();
            echo ""._ADDERROR."<br />";
            CloseTable();
        } else {
            if ($ya_config['servermail'] == 0) {
                $message = _WELCOMETO." $sitename ($nukeurl)!<br /><br />";
                $message .= _YOUUSEDEMAIL." $ya_user_email "._TOREGISTER." $sitename.<br /><br />";
				$message .= _TOFINISHUSER."<br /><br /><a href=\"$finishlink\">$finishlink</a><br /><br />";
                $message .= _FOLLOWINGMEM."<br />"._UNICKNAME." $ya_username<br />"._UPASSWORD." $user_password";
                $subject = _ACTIVATIONSUB;
                $from  = "From: $adminmail\n";
                $from .= "Reply-To: $adminmail\n";
                $from .= "Return-Path: $adminmail\n";
                evo_mail($ya_user_email, $subject, $message, $from);
            }
            title(_USERREGLOGIN);
            OpenTable();
            echo "<center><strong>"._ACCOUNTCREATED."</strong><br /><br />";
            echo ""._YOUAREREGISTERED."<br /><br />";
            echo ""._FINISHUSERCONF."<br /><br />";
            echo ""._THANKSUSER." $sitename!</center>";
            CloseTable();
            if ($ya_config['sendaddmail'] == 1 AND $ya_config['servermail'] == 0) {
                $from  = "From: $ya_user_email\n";
                $from .= "Reply-To: $ya_user_email\n";
                $from .= "Return-Path: $ya_user_email\n";
                $subject = "$sitename - "._MEMACT;
/*****[BEGIN]******************************************
 [ Base:    NukeSentinel                      v2.5.00 ]
 ******************************************************/
                $from_ip = $nsnst_const['remote_ip'];
/*****[END]********************************************
 [ Base:    NukeSentinel                      v2.5.00 ]
 ******************************************************/
                $message  = "$ya_username "._YA_APLTO." $sitename "._YA_FROM." $from_ip<br />";
                $message .= "-----------------------------------------------------------<br />";
                $message .= _YA_NOREPLY;
                evo_mail($adminmail, $subject, $message, $from);
            }
        }
    } else {
        echo "$stop";
    }
    include_once(NUKE_BASE_DIR.'footer.php');

?>