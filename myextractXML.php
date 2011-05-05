<?php

/*
 * version : 1.3
 * Edited by : John THomas
 * Email : johnthomas@contus.in
 * Purpose : Create playlist for player
 * Path:/wp-content/plugins/contus-hd-flv-player/myextractXML.php
 * Date:13/1/11
 *
 */


/* Used to import plugin configuration */
require_once( dirname(__FILE__) . '/hdflv-config.php');

// get the path url from querystring
$playlist_id = $_GET['pid'];

function get_out_now() {
    exit;
}

add_action('shutdown', 'get_out_now', -1);

global $wpdb;

$title = 'hdflv Playlist';

$themediafiles = array();
$limit = '';


// Fetching videos of the selected playlist
$playlist_id = intval($_GET['pid']);
$video_id = intval($_GET['vid']);
if ($playlist_id != '' && $video_id != '') {//Condition if both playlist id  && video id were set
    $playlist = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "hdflv_playlist WHERE pid = '$playlist_id'");
    $selectVideo = " (SELECT * FROM " . $wpdb->prefix . "hdflv w WHERE w.vid = " . $video_id . ")";
    $videoFiles = $wpdb->get_results($wpdb->prepare($selectVideo));
    if (count($playlist) > 0) {        
        
            $selectPlaylist .= " (SELECT * FROM " . $wpdb->prefix . "hdflv w";
            $selectPlaylist .= " INNER JOIN " . $wpdb->prefix . "hdflv_med2play m";
            $selectPlaylist .= " WHERE (m.playlist_id = '$playlist_id'";
            $selectPlaylist .= " AND m.media_id = w.vid AND w.vid != $video_id) GROUP BY w.vid ";
            $selectPlaylist .= " ORDER BY m.sorder ASC , m.porder " . $playlist->playlist_order . " ,w.vid " . $playlist->playlist_order . ")";
            $playFiles = $wpdb->get_results($wpdb->prepare($selectPlaylist));
            $themediafiles = array_merge($videoFiles, $playFiles);       

        $title = $playlist->playlist_name;
    }else{
            $themediafiles = $videoFiles;
    }
} elseif ($playlist_id != '') {//Condition if the playlist id set
    $playlist = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "hdflv_playlist WHERE pid = '$playlist_id'");
    if ($playlist) {
        $select = " SELECT * FROM " . $wpdb->prefix . "hdflv w";
        $select .= " INNER JOIN " . $wpdb->prefix . "hdflv_med2play m";
        $select .= " WHERE (m.playlist_id = '$playlist_id'";
        $select .= " AND m.media_id = w.vid) GROUP BY w.vid ";
        $select .= " ORDER BY m.sorder ASC , m.porder " . $playlist->playlist_order . " ,w.vid " . $playlist->playlist_order;
        $themediafiles = $wpdb->get_results($wpdb->prepare($select));
        $title = $playlist->playlist_name;
    }
} else {//Condition if both video id is set
    $selectVideo = " (SELECT * FROM " . $wpdb->prefix . "hdflv w WHERE w.vid = " . $video_id . ")";
    $videoFiles = $wpdb->get_results($wpdb->prepare($selectVideo));
    $themediafiles = $videoFiles;
}
session_start();
$_SESSION['videoCount'] = count($themediafiles);

$options1 = get_option('HDFLVSettings');
$autoPlay = $wpdb->get_col("SELECT autoplay FROM " . $wpdb->prefix . "hdflv_settings");
if ($autoPlay[0] == 1) {
    $ap = 'true';
} else {
    $ap = 'false';
}

// Create XML output of playlist
ob_start();
ob_clean();
header("content-type:text/xml;charset = utf-8");
echo '<?xml version = "1.0" encoding = "utf-8"?>';
echo "<playlist autoplay = '$ap' random = 'false'>";


if (is_array($themediafiles)) {

    foreach ($themediafiles as $media) {

        if ($media->image == '') {
            $image = get_option('siteurl') . '/wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/images/hdflv.jpg';
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

        echo ' url = "' . htmlspecialchars($media->file) . '"';
        echo ' thu_image = "' . htmlspecialchars($image) . '"';
        echo ' Preview = "' . htmlspecialchars($media->opimage) . '"';
        echo ' hd = "' . $hd . '"';
         echo ' download = "true"';
        echo ' hdpath = "' . $media->hdfile . '">';
        echo '<title><![CDATA[' . htmlspecialchars($media->name) . ']]></title> ';
        // echo '<tagline targeturl=""><![CDATA[' . htmlspecialchars($media->name) . ']]></tagline> ';
        echo '' . '</mainvideo>';
    }
}

echo '</playlist>';
?>