<?php
/**
 * @name          : Player Configuration Settings
 * @version	  	  : 1.8
 * @package       : apptha
 * @subpackage    : contus-hd-flv-player
 * @author        : Apptha - http://www.apptha.com
 * @copyright     : Copyright (C) 2011 Powered by Apptha
 * @license	      : GNU General Public License version 2 or later; see LICENSE.txt
 * @Purpose       : Player Configuration Settings
 * @Creation Date : Dec 09, 2011
 * @Modified Date : Jul 23, 2012
 * */

header("content-type:text/xml;charset=utf-8");
require_once( dirname(__FILE__) . '/hdflv-config.php');
global $wpdb;
global $site_url;
//$site_url = get_option('siteurl');
$settingsRecord = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "hdflv_settings");

$skin = $site_url . '/wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/hdflvplayer/skin';
$skinpath = $skin . '/' . $settingsRecord->skin . '/' . $settingsRecord->skin . '.swf';
$logoPath = $site_url . '/wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/hdflvplayer/css/images/';
$xmlPath = $site_url . '/wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/';
$emailPath = $site_url . '/wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/hdflvplayer/email.php';
$playXml = $xmlPath . 'myextractXML.php';

$langXML = $site_url . '/wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/hdflvplayer/xml/language/language.xml';

$timer = $settingsRecord->timer == 1 ? 'true' : 'false';
$zoom = $settingsRecord->zoom == 1 ? 'true' : 'false';
$email = $settingsRecord->email ? 'true' : 'false';
$fullscreen = $settingsRecord->fullscreen == 1 ? 'true' : 'false';
$autoplay = ($settingsRecord->autoplay == 1) ? 'true' : 'false';
$HD_default = ($settingsRecord->HD_default == 1) ? 'true' : 'false';
$download = ($settingsRecord->download == 1) ? 'true' : 'false';
$skin_autohide = ($settingsRecord->skin_autohide == 1) ? 'true' : 'false';
$embed_visible = ($settingsRecord->embed_visible == 1) ? 'true' : 'false';
$playlist = ($settingsRecord->playlist == 1) ? 'true' : 'false';
$debug = ($settingsRecord->debug == 1) ? 'true' : 'false';
$autoplay = ($settingsRecord->autoplay == 1) ? 'true' : 'false';
$autoplay = ($settingsRecord->autoplay == 1) ? 'true' : 'false';
$autoplay = ($settingsRecord->autoplay == 1) ? 'true' : 'false';
$playlist_autoplay = ($settingsRecord->playlistauto == 1) ? 'true' : 'false';
$hide_youtubelogo = ($settingsRecord->display_logo == 1) ? 'true' : 'false';


/*Configuration Start*/
//ob_start(); ob_end_flush(); ob_end_clean();

echo '<?xml version="1.0" encoding="utf-8"?>';
echo '<config

      buffer="' . $settingsRecord->buffer . '"
      height="' . $settingsRecord->height . '"
      width="' . $settingsRecord->width . '"
      normalscale="' . $settingsRecord->normalscale . '"
      fullscreenscale="' . $settingsRecord->fullscreenscale . '"
      languageXML = "' . $langXML . '"
      logopath="' . $logoPath . $settingsRecord->logopath . '"
      logo_target="'.$settingsRecord->logo_target.'"
      autoplay  ="' . $autoplay . '"
      playlist_autoplay ="' . $playlist_autoplay . '"
      Volume="' . $settingsRecord->volume . '"
      logoalign="' . $settingsRecord->logoalign . '"
      HD_default="' . $HD_default . '"
      Download="' . $download . '"
      logoalpha = "' . $settingsRecord->logoalpha . '"
      scaleToHideLogo = "' . $hide_youtubelogo . '"
      skin_autohide="' . $skin_autohide . '"
      stagecolor1="' . $settingsRecord->stagecolor . '"
      skin="' . $skinpath . '"
      embed_visible="' . $embed_visible . '"
      playlistXML="'.$playXml.'"
      shareURL = "'.$emailPath.'"
      UseYouTubeApi="flash"
      showPlaylist ="'. $playlist.'"
      license = "'.$settingsRecord->license.'"
      debug="' . $debug . '">';

echo '<timer>' . $timer . '</timer>';

echo '<zoom>' . $zoom . '</zoom>';

echo '<email>' . $email . '</email>';

echo '<fullscreen>' . $fullscreen . '</fullscreen>';

echo '</config>';exit;


/*Configuration ends*/
exit;
?>