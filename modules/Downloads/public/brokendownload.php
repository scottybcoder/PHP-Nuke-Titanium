<?php //fixed build upon
/**
 * TegoNuke(tm)/NSN GR Downloads (NSNGD): Downloads
 *
 * This module allows admins and end users (if so configured - see Submit Downloads
 * module) to add/submit downloads to their site.  This module is NSN Groups aware
 * (Note: NSN Groups is already built into RavenNuke(tm)) and carries more features
 * than the stock *nuke system Downloads module.  Check out the admin screens for a
 * multitude of configuration options.
 *
 * The original NSN GR Downloads was given to montego by Bob Marion back in 2006 to
 * take over on-going development and support.  It has undergone extensive bug
 * removal, including XHTML compliance and further security checking, among other
 * fine enhancements made over time.
 *
 * Original copyright statements are below these.
 *
 * PHP versions 5.2+ ONLY
 *
 * LICENSE: GNU/GPL 2 (provided with the download of this script)
 *
 * @category    Module
 * @package     TegoNuke(tm)/NSN
 * @subpackage  Downloads
 * @author      Rob Herder (aka: montego) <montego@montegoscripts.com>
 * @copyright   2006 - 2011 by Montego Scripts
 * @license     http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL 2
 * @version     1.1.3_47
 * @link        http://montegoscripts.com
 */
/********************************************************/
/* NSN GR Downloads                                     */
/* By: NukeScripts Network (webmasternukescripts.net)   */
/* http://www.nukescripts.net                           */
/* Copyright (c) 2000-2005 by NukeScripts Network       */
/********************************************************/
?>
<style>
.carbonfiber {
  background-color: #000000; /* Green */
  border: none;
  border-radius: 8px;
  color: white;
  padding: 15px 32px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 16px;
 -webkit-transition-duration: 0.4s; /* Safari */
  transition-duration: 0.4s;
}

.carbonfiber:hover {
  background-color: #c80101; /* Green */
  color: white;
}

}
</style>
<?
if (!defined('IN_NSN_GD')) { echo 'Access Denied'; die(); }
$lid = isset($lid) ? intval($lid) : 0;
$pagetitle = '- ' . _DL_REPORTBROKEN;
include_once 'header.php';
//menu(1); 
echo '<br />';
OpenTable();
//title(_DL_REPORTBROKEN);   
//OpenTable();
echo '<div align="center">';
echo '<form action="modules.php?name=', $module_name, '" method="post">';
echo '<input type="hidden" name="lid" value="', $lid, '" /><input type="hidden" name="op" value="brokendownloadS" />';

//echo '<p><h3>', _DL_THANKSBROKEN, '<br />', _DL_SECURITYBROKEN, '</h3></p>';
echo '<input class="carbonfiber" type="submit" value="', _DL_REPORTBROKEN, '" /></form>';

echo '</div>';

CloseTable();
include_once 'footer.php';

