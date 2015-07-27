<?php
/*
  Name: Contus HD FLV Player
  Plugin URI: http://www.apptha.com/category/extension/Wordpress/HD-FLV-Player-Plugin/
  Description: Video playlist xml file.
  Version: 2.6
  Author: Apptha
  Author URI: http://www.apptha.com
  License: GPL2
 */

/* Used to import plugin configuration */
require_once( dirname(__FILE__) . '/hdflv-config.php');
global $wpdb, $i;
$title = 'hdflv Playlist';
$themediafiles = array();

// Fetching videos of the selected playlist
$playlist_id = filter_input(INPUT_GET, 'pid', FILTER_SANITIZE_STRING);
$playlist_id = intval($playlist_id);
$video_id = filter_input(INPUT_GET, 'vid', FILTER_SANITIZE_STRING);
$video_id = intval($video_id);

if ($playlist_id != '' && $video_id != '') {//Condition if both playlist id  && video id were set
    $playlist = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "hdflv_playlist WHERE pid = '$playlist_id' AND is_pactive = 1 ");

    if (count($playlist) > 0) {
    $selectVideo = " (SELECT * FROM " . $wpdb->prefix . "hdflv w WHERE w.vid = " . $video_id . "  AND is_active = 1)";
    $videoFiles = $wpdb->get_results($wpdb->prepare($selectVideo, NULL));
        $selectPlaylist .= " (SELECT * FROM " . $wpdb->prefix . "hdflv w";
        $selectPlaylist .= " INNER JOIN " . $wpdb->prefix . "hdflv_med2play m";
        $selectPlaylist .= " WHERE (m.playlist_id = '$playlist_id'";
        $selectPlaylist .= " AND m.media_id = w.vid AND w.vid != $video_id)  AND w.is_active = 1 GROUP BY w.vid ";
        $selectPlaylist .= " ORDER BY m.sorder ASC , m.porder " . $playlist->playlist_order . " ,w.vid " . $playlist->playlist_order . ")";
        $playFiles = $wpdb->get_results($wpdb->prepare($selectPlaylist, NULL));
        $themediafiles = array_merge($videoFiles, $playFiles);

        $title = $playlist->playlist_name;
    }
} elseif ($playlist_id != '') {//Condition if the playlist id set
    $playlist = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "hdflv_playlist WHERE pid = '$playlist_id' AND is_pactive = 1");
    if ($playlist) {
        $select = " SELECT * FROM " . $wpdb->prefix . "hdflv w";
        $select .= " INNER JOIN " . $wpdb->prefix . "hdflv_med2play m";
        $select .= " WHERE (m.playlist_id = '$playlist_id'";
        $select .= " AND m.media_id = w.vid)  AND w.is_active = 1 GROUP BY w.vid ";
        $select .= " ORDER BY m.sorder ASC , m.porder " . $playlist->playlist_order . " ,w.vid " . $playlist->playlist_order;
        $themediafiles = $wpdb->get_results($wpdb->prepare($select, NULL));
        $title = $playlist->playlist_name;
    }
} else {//Condition if both video id is set
    $selectVideo = " (SELECT * FROM " . $wpdb->prefix . "hdflv w WHERE w.vid = " . $video_id . " AND is_active = 1)";
    $videoFiles = $wpdb->get_results($wpdb->prepare($selectVideo, NULL));
    $themediafiles = $videoFiles;
}


$settingsRecord = $wpdb->get_row("SELECT autoplay , download FROM " . $wpdb->prefix . "hdflv_settings");
($settingsRecord->autoplay == 1) ? $ap = 'true' : $ap = 'false';

// Create XML output of playlist

header("content-type:text/xml;charset = utf-8"); //mime type
echo '<?xml version = "1.0" encoding = "utf-8"?>';
echo "<playlist autoplay = '$ap' random = 'false'>";
$defaultVideoImg = get_option('siteurl') . '/wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/images/hdflv.jpg';

if (count($themediafiles)) {  // if min(1) playlist or video is selected then it's play .
    $download = ($settingsRecord->download == 1) ? 'true' : 'false';
    foreach ($themediafiles as $media) {

        if ($media->image == '') {
            $image = $defaultVideoImg;
        } else {
            $image = $media->image;
        }
        $file = pathinfo($media->file);
        if ($media->hdfile != '') {
            $hd = 'true';
        } else {
            $hd = 'false';
        }
        echo '<mainvideo';
        echo ' id = "' . htmlspecialchars($media->vid) . '"';
        echo ' url = "' . htmlspecialchars($media->file) . '"'; //for play ['hdflv palylist = 1'] &amp; it store in db like so we get correctly
        echo ' thu_image = "' . htmlspecialchars($image) . '"';
        echo ' Preview = "' . htmlspecialchars($media->opimage) . '"';
        echo ' hd = "' . $hd . '"';
        if (($media->link) == ''):
            echo 'allow_download ="' . $download . '"';  //link col for download
        endif;
        echo ' hdpath   = "' . $media->hdfile . '">';
        echo '<title><![CDATA[' . htmlspecialchars($media->name) . ']]></title> ';
        echo '<tagline targeturl=""><![CDATA[]]></tagline>';
        echo '' . '</mainvideo>';
    }//for loop end hear
}//if end hear
else {                    // IF NO VIDEO IS FOUND THEN I PLAY DEFAULT VIDEO
    echo '<mainvideo url="http://www.hdflvplayer.net/hdflvplayer/videos/300.mp4"
             hdpath="http://www.hdflvplayer.net/hdflvplayer/videos/300.mp4"
             id="100"
             thu_image="http://hdflvplayer.net/hdflvplayer/images/300_p.jpg"                                                                                
             Preview="" 
             preroll="true" 
             midroll="true" 
             postroll="true" 
             allow_download="true"
             streamer=""
             isLive="false" > 
             <title><![CDATA[Welcome]]></title> 
             <!--Optional--> 
             <tagline targeturl="https://mydomain.com"><![CDATA[<span class="heading">Tagline - </span> <b>Your short description  goes here for Videos.</b> ]]></tagline>        
  </mainvideo>';
}
echo '</playlist>';
?>