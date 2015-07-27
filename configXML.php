<?php
/*
  Name: Contus HD FLV Player
  Plugin URI: http://www.apptha.com/category/extension/Wordpress/HD-FLV-Player-Plugin/
  Description: Video configxml file.
  Version: 2.6
  Author: Apptha
  Author URI: http://www.apptha.com
  License: GPL2
 */
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
$emailPath = $site_url . '/wp-admin/admin-ajax.php?action=email';
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
$playlist_autoplay = ($settingsRecord->playlistauto == 1) ? 'true' : 'false';
$hide_youtubelogo = ($settingsRecord->display_logo == 1) ? 'true' : 'false';
$ima_add = ($settingsRecord->ima_ads == 1) ? 'true' : 'false';
$ima_add_xml = $settingsRecord->ima_ads_xml;
$google_track = $settingsRecord->google_track;

/* Configuration Start */
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
      logo_target="' . $settingsRecord->logo_target . '"
      autoplay  ="' . $autoplay . '"
      playlist_autoplay ="' . $playlist_autoplay . '"
      Volume="' . $settingsRecord->volume . '"
      logoalign="' . $settingsRecord->logoalign . '"
      HD_default="' . $HD_default . '"
      Download="' . $download . '"
      logoalpha = "' . $settingsRecord->logoalpha . '"
      scaleToHideLogo = "' . $hide_youtubelogo . '"
      skin_autohide="' . $skin_autohide . '"
      stagecolor="' . $settingsRecord->stagecolor . '"
      skin="' . $skinpath . '"
      embed_visible="' . $embed_visible . '"
      playlistXML="' . $playXml . '"
      shareURL = "' . $emailPath . '"
      trackCode="' . $google_track . '"
      IMAAds="' . $ima_add . '"
      IMAadsXML="' . $ima_add_xml . '"
      showPlaylist ="' . $playlist . '"
      license = "' . $settingsRecord->license . '"
      debug="' . $debug . '">';
echo '<timer>' . $timer . '</timer>';
echo '<zoom>' . $zoom . '</zoom>';
echo '<email>' . $email . '</email>';
echo '<fullscreen>' . $fullscreen . '</fullscreen>';
echo '</config>';
exit;
/* Configuration ends */
exit;
?>