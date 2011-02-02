<?php

/*
 * version : 1.3
 * Edited by : John THomas
 * Email : johnthomas@contus.in
 * Purpose : functions used to sort the playlist
 * Path:/wp-content/plugins/contus-hd-flv-player/process-sortable.php
 * Date:13/1/11
 *
 */

/* Used to import plugin configuration */
require_once( dirname(__FILE__) . '/hdflv-config.php');

function get_out_now() {
    exit;
}

add_action('shutdown', 'get_out_now', -1);

global $wpdb;

$title = 'hdflv Playlist';

$pid1 = $_GET['playid'];

foreach ($_GET['listItem'] as $position => $item) :
    mysql_query("UPDATE $wpdb->prefix" . "hdflv_med2play SET `sorder` = $position WHERE `media_id` = $item and playlist_id=$pid1 ");
endforeach;

$tables = $wpdb->get_results("SELECT vid FROM $wpdb->prefix" . "hdflv LEFT JOIN " . $wpdb->prefix . "hdflv_med2play ON (vid = media_id) WHERE (playlist_id = '$pid1') ORDER BY sorder ASC, vid ASC");


if ($tables) {
    foreach ($tables as $table) {
        $playstore1 .= $table->vid . ",";
    }
}
?>