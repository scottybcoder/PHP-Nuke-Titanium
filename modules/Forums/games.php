<?php
/*=======================================================================
 Nuke-Evolution Basic: Enhanced PHP-Nuke Web Portal System
 =======================================================================*/

/***************************************************************************
 *                                games.php
 *                            -------------------
 *
 *   PHPNuke Ported Arcade - http://www.nukearcade.com
 *   Original Arcade Mod phpBB by giefca - http://www.gf-phpbb.com
 *
 ***************************************************************************/

/*****[CHANGES]**********************************************************
-=[Base]=-
      Nuke Patched                             v3.1.0       09/20/2005
-=[Mod]=-
      Advanced Username Color                  v1.0.5       09/20/2005
 ************************************************************************/

if (!defined('MODULE_FILE')) {
    die('You can\'t access this file directly...');
}

if ($popup != "1"){
    $module_name = basename(dirname(__FILE__));
    require("modules/".$module_name."/nukebb.php");
}
else
{
    $phpbb_root_path = NUKE_FORUMS_DIR;
}

define('IN_PHPBB', true);
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);
require_once('includes/bbcode.'. $phpEx);

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_GAME, $nukeuser);
init_userprefs($userdata);
//
// End session management
//
include('includes/functions_arcade.' . $phpEx);
//
// Start auth check
//
if (!$userdata['session_logged_in']) {
        $header_location = (@preg_match("/Microsoft|WebSTAR|Xitami/", getenv("SERVER_SOFTWARE"))) ? "Refresh: 0; URL=" : "Location: ";
        header($header_location . "modules.php?name=Your_Account");
        exit;
}
//
// End of auth check
//

$arcade_config = array();
$arcade_config = read_arcade_config();

if($arcade_config['limit_by_posts'] && $userdata['user_level'] != ADMIN){
$secs = 86400;
$uid = $userdata['user_id'];

$days = $arcade_config['days_limit'];
$posts = $arcade_config['posts_needed'];

$current_time = time();
$old_time = $current_time - ($secs * $days);

//Begin Limit Play mod
if($arcade_config['limit_type']=='posts')
{
$sql = "SELECT * FROM " . POSTS_TABLE . " WHERE poster_id = $uid";
}
else
{
$sql = "SELECT * FROM " . POSTS_TABLE . " WHERE poster_id = $uid and post_time BETWEEN $old_time AND $current_time";
}
if ( !($result = $db->sql_query($sql)) )
    {
        message_die(GENERAL_ERROR, 'Could not obtain forums information', '', __LINE__, __FILE__, $sql);
    }

    $Amount_Of_Posts = $db->sql_numrows( $result );


    if($Amount_Of_Posts < $posts)
    {
    $diff_posts = $posts - $Amount_Of_Posts;

    if($arcade_config['limit_type']=='posts')
        {
            $message = "You need $posts posts to play the arcade.<br />You need $diff_posts more posts.";
            }
    else  {
            $message = "You need $posts posts in the last $days days to play the arcade.<br />You need $diff_posts more posts.";

        }
        message_die(GENERAL_MESSAGE, $message);
    }
}
//End Limit Play mod

if (!empty($HTTP_POST_VARS['gid']) || !empty($HTTP_GET_VARS['gid']))
        {
        $gid = (!empty($HTTP_POST_VARS['gid'])) ? intval($HTTP_POST_VARS['gid']) : intval($HTTP_GET_VARS['gid']);
        }
else    {
        message_die(GENERAL_ERROR, "No game is specified");
        }

$sql = "SELECT g.* , MAX(s.score_game) AS highscore FROM " . GAMES_TABLE . " g LEFT JOIN " . SCORES_TABLE . " s ON g.game_id = s.game_id WHERE g.game_id = $gid GROUP BY g.game_id, g.game_highscore";

if (!($result = $db->sql_query($sql)))
{
        message_die(GENERAL_ERROR, "Could not read games table", '', __LINE__, __FILE__, $sql);
}

if (!($row = $db->sql_fetchrow($result)) )
{
        message_die(GENERAL_ERROR, "This game does not exist", '', __LINE__, __FILE__, $sql);
}

$liste_cat_auth_play = get_arcade_categories($userdata['user_id'], $userdata['user_level'],'play');
$tbauth_play = array();
$tbauth_play = explode(',',$liste_cat_auth_play);

if (!in_array($row['arcade_catid'],$tbauth_play))
{
        message_die(GENERAL_MESSAGE, $lang['game_forbidden']);
}


$template->set_filenames(array(
        'body' => 'games_body.tpl')
);

$sql = "DELETE FROM " . GAMEHASH_TABLE . " WHERE hash_date < " . (time() - 72000);

if (!$db->sql_query($sql))
{
        message_die(GENERAL_ERROR, "Could not delete from game hash table", '', __LINE__, __FILE__, $sql);
}

// Type V2 Game Else Type V1
if ($row['game_type'] == 3) {
        $type_v2 = true;
        $template->assign_block_vars('game_type_V2',array());
        $gamehash_id = md5(uniqid($user_ip));
        $sql = "INSERT INTO " . GAMEHASH_TABLE . " (gamehash_id , game_id , user_id , hash_date) VALUES ('$gamehash_id' , '$gid' , '" . $userdata['user_id'] . "' , '" . time() . "')";

        if (!($result = $db->sql_query($sql)))
                {
                message_die(GENERAL_ERROR, "Could not delete from game hash table", '', __LINE__, __FILE__, $sql);
        }
                    }
elseif ($row['game_type'] == 4 or $row['game_type'] == 5)
        {
        if ($row['game_type'] == 5)
                {
                            $template->assign_block_vars('game_type_V5',array());
            }
            else
            {
                        $template->assign_block_vars('game_type_V2',array());
            }
        setcookie('gidstarted', '', time() - 3600);
        setcookie('gidstarted',$gid);
        setcookie('timestarted', '', time() - 3600);
        setcookie('timestarted', time());

        $gamehash_id = md5($user_ip);
        $sql = "INSERT INTO " . GAMEHASH_TABLE . " (gamehash_id , game_id , user_id , hash_date) VALUES ('$gamehash_id' , '$gid' , '" . $userdata['user_id'] . "' , '" . time() . "')";

        if (!($result = $db->sql_query($sql)))
                {
                message_die(GENERAL_ERROR, "Couldn't update hashtable", '', __LINE__, __FILE__, $sql);
        }

        }
else
                {
                        message_die(GENERAL_ERROR, "Game Type no longer supported, please contact the admin and have him/her delete it.");

                }

setcookie('arcadepopup', '', time() - 3600);
setcookie('arcadepopup', '0');
global $prefix;
$sql = "SELECT arcade_cattitle FROM `".$prefix."_bbarcade_categories` WHERE arcade_catid = " . $row['arcade_catid'];
$result = $db->sql_query($sql);
$ourrow = $db->sql_fetchrow($result);
$cat_title = $ourrow['arcade_cattitle'];

$template->assign_vars(array(
        'MAXSIZE_AVATAR' => intval($arcade_config['maxsize_avatar']),
        'CAT_TITLE' => '<a class="nav" href="' . append_sid("arcade.$phpEx&amp;cid=") . $row['arcade_catid'] .'">' . $cat_title . '</a> ' ,
        'NAV_DESC' => '<a class="nav" href="' . append_sid("arcade.$phpEx") . '">' . $lang['arcade'] . '</a> ' ,
        'SWF_GAME' => $row['game_swf'] ,
        'GAME_WIDTH' => $row['game_width'] ,
        'GAME_HEIGHT' => $row['game_height'] ,
        'L_GAME' => $row['game_name'] ,
        'GAMEHASH' => $gamehash_id,
        'L_TOP' => $lang['best_scores_game'] ,
        'HIGHSCORE' => number_format($row['highscore']),
        'URL_ARCADE' => '<nobr><a class="cattitle" href="' . append_sid("arcade.$phpEx") . '">' . $lang['lib_arcade'] . '</a></nobr> ',
        'MANAGE_COMMENTS' => '<nobr><a class="cattitle" href="' . append_sid("comments_list.$phpEx") . '">' . $lang['comments'] . '</a></nobr> ',
        'URL_BESTSCORES' => '<nobr><a class="cattitle" href="' . append_sid("toparcade.$phpEx") . '">' . $lang['best_scores'] . '</a></nobr> ',
        'URL_SCOREBOARD' => '<nobr><a class="cattitle" href="' . append_sid("scoreboard.$phpEx?gid=$gid") . '">' . $lang['scoreboard'] . '</a></nobr> ')
);

$sql = "SELECT s.* , u.username, u.user_avatar_type, u.user_allowavatar, u.user_avatar FROM " . SCORES_TABLE . " s LEFT JOIN " . USERS_TABLE . " u ON s.user_id = u.user_id WHERE game_id = $gid ORDER BY s.score_game DESC, s.score_date ASC LIMIT 0,15 ";

if (!($result = $db->sql_query($sql)))
{
        message_die(GENERAL_ERROR, "Could not read from scores table", '', __LINE__, __FILE__, $sql);
}

$sql = "SELECT comments_value FROM " . COMMENTS_TABLE . " WHERE game_id = $gid";

if( !($result_comment = $db->sql_query($sql)) )
{
    message_die(GENERAL_ERROR, "Error retrieving comment from comment table", '', __LINE__, __FILE__, $sql);
}

$row_comment = $db->sql_fetchrow($result_comment);

//
// Define censored word matches
//
$orig_word = array();
$replacement_word = array();
obtain_word_list($orig_word, $replacement_word);

if(!empty($row_comment['comments_value']))
{
            if ( count($orig_word) )
                        {
                             $row_comment['comments_value'] = preg_replace($orig_word, $replacement_word, $row_comment['comments_value']);
                        }
                        $comment = '<marquee scrollamount="3">' .$row_comment['comments_value'] .'</marquee>';
}
else
{
$comment='';
}

$pos = 0;
$posreelle = 0;
$lastscore = 0;
while ($row = $db->sql_fetchrow($result)) {
        $posreelle++;

        if ($posreelle == 1) {
                $user_avatar_type = $row['user_avatar_type'];
                $user_allowavatar = $row['user_allowavatar'];
                $user_avatar = $row['user_avatar'];
/*****[BEGIN]******************************************
 [ Mod:    Advanced Username Color             v1.0.5 ]
 ******************************************************/
                $best_user = UsernameColor($row['username']);
/*****[END]********************************************
 [ Mod:    Advanced Username Color             v1.0.5 ]
 ******************************************************/
                $best_date = create_date( $board_config['default_dateformat'] , $row['score_date'] , $board_config['board_timezone'] );
                $games_played = $row['score_set'];
                $best_time = sec2hms($row['score_time']);

        }

        if ($lastscore!=$row['score_game']) {
                $pos = $posreelle;
        }

        $lastscore = $row['score_game'];
        $template->assign_block_vars('scorerow', array(
                'POS' => $pos,
/*****[BEGIN]******************************************
 [ Mod:    Advanced Username Color             v1.0.5 ]
 ******************************************************/
                'USERNAME' => UsernameColor($row['username']),
/*****[END]********************************************
 [ Mod:    Advanced Username Color             v1.0.5 ]
 ******************************************************/
                'URL_STATS' => '<nobr><a class="cattitle" href="' . append_sid("statarcade.$phpEx?uid=" . $row['user_id']) . '">' . "<img src='modules/Forums/templates/" . $theme['template_name'] . "/images/loupe.gif' align='absmiddle' border='0' alt='" . $lang['statuser'] . " " . $row['username'] . "'>" . '</a></nobr> ',
                'GAMEDESC' => $row['game_desc'],
                'SCORE' => number_format($row['score_game']),
                'DATEHIGH' => create_date($board_config['default_dateformat'] , $row['score_date'] , $board_config['board_timezone']))
        );
}

$avatar_img = '';
if ($user_avatar_type && $user_allowavatar) {
        switch($user_avatar_type) {
                case USER_AVATAR_UPLOAD:
                        $avatar_img = ($board_config['allow_avatar_upload']) ? '<img src="' . $board_config['avatar_path'] . '/' . $user_avatar . '" alt="" border="0" hspace="20" align="center" valign="center" onload="resize_avatar(this)"/>' : '';
                        break;

                case USER_AVATAR_REMOTE:
                        $avatar_img = ($board_config['allow_avatar_remote']) ? '<img src="' . $user_avatar . '" alt="" border="0"  hspace="20" align="center" valign="center"  onload="resize_avatar(this)"/>' : '';
                        break;

                case USER_AVATAR_GALLERY:
                        $avatar_img = ($board_config['allow_avatar_local']) ? '<img src="' . $board_config['avatar_gallery_path'] . '/' . $user_avatar . '" alt="" border="0"  hspace="20" align="center" valign="center"  onload="resize_avatar(this)"/>' : '';
                        break;
        }
}

if ($arcade_config['display_winner_avatar']) {
        if ($arcade_config['winner_avatar_position']=='right') {
                $template->assign_block_vars('avatar_best_player_right',array());
        } else {
                $template->assign_block_vars('avatar_best_player_left',array());
        }

        $template->assign_vars(array(
           'L_ACTUAL_WINNER' => $lang['Actual_winner'],
           'BEST_USER_NAME' => $best_user,
           'BEST_USER_DATE' => sprintf($lang['hi_score_on'], $best_date),
           'BEST_TIME' => sprintf($lang['played_time_total'], $best_time),
           'COMMENTS' => smilies_pass($comment),
           'GAMES_PLAYED' => sprintf($lang['played_times'], $games_played),
           'FIRST_AVATAR' => $avatar_img)
       );

}

include($phpbb_root_path . 'whoisplaying.'.$phpEx);

//
// Output page header
$page_title = $lang['arcade_game'];
include('includes/page_header.'.$phpEx);
$template->pparse('body');
include('includes/page_tail.'.$phpEx);

?>