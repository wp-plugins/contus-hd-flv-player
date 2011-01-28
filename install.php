<?php
/*
 * Edited By : JOHn Thomas
 * Purpose : Used for installing player plugin (Creating tables)
 * Email : johnthomas@contus.in
*/
/****************************************************************/
/* Install routine for hdflvplayer
/****************************************************************/
function hdflv_install()
{
    global $wpdb;

    // set tablename
    $table_name 		= $wpdb->prefix . 'hdflv';
    $table_playlist		= $wpdb->prefix . 'hdflv_playlist';
    $table_med2play		= $wpdb->prefix . 'hdflv_med2play';
    $table_settings		= $wpdb->prefix . 'hdflv_settings';

    $wfound = false;
    $pfound = false;
    $mfound = false;
    $found = true;
    $settingsFound = false;

    foreach ($wpdb->get_results("SHOW TABLES;", ARRAY_N) as $row)
    {

        if ($row[0] == $table_name) 	$wfound = true;
        if ($row[0] == $table_playlist) $pfound = true;
        if ($row[0] == $table_med2play) $mfound = true;
        if ($row[0] == $table_settings) $settingsFound = true;
    }

    // add charset & collate like wp core
    $charset_collate = '';

    if ( version_compare(mysql_get_server_info(), '4.1.0', '>=') )
    {
        if ( ! empty($wpdb->charset) )
        $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if ( ! empty($wpdb->collate) )
        $charset_collate .= " COLLATE $wpdb->collate";
    }

    if (!$wfound)
    {

        $sql = "CREATE TABLE ".$table_name." (
                vid MEDIUMINT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    name MEDIUMTEXT NULL,
                    file MEDIUMTEXT NULL,
                    hdfile MEDIUMTEXT NULL,
                    image MEDIUMTEXT NULL,
                    opimage MEDIUMTEXT NULL,
                    link MEDIUMTEXT NULL
                    ) $charset_collate;";

        $res = $wpdb->get_results($sql);
    }

    if (!$pfound)
    {
        $sql = "CREATE TABLE ".$table_playlist." (
                pid BIGINT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                playlist_name VARCHAR(200) NOT NULL ,
                playlist_desc LONGTEXT NULL,
                playlist_order VARCHAR(50) NOT NULL DEFAULT 'ASC'
                ) $charset_collate;";

        $res = $wpdb->get_results($sql);
    }

    if (!$mfound)
    {
        $sql = "CREATE TABLE ".$table_med2play." (
                rel_id BIGINT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                media_id BIGINT(10) NOT NULL DEFAULT '0',
                playlist_id BIGINT(10) NOT NULL DEFAULT '0',
                porder MEDIUMINT(10) NOT NULL DEFAULT '0',
                sorder INT(3) NOT NULL DEFAULT '0'
                ) $charset_collate;";

        $res = $wpdb->get_results($sql);
    }
    
if (!$settingsFound)
    {
        $sql = "CREATE TABLE ".$table_settings." (
                settings_id BIGINT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                autoplay BIGINT(10) NOT NULL DEFAULT '0',
                playlist BIGINT(10) NOT NULL DEFAULT '0',
                playlistauto BIGINT(10) NOT NULL DEFAULT '0',
                buffer MEDIUMINT(10) NOT NULL DEFAULT '0',
                normalscale INT(3) NOT NULL DEFAULT '0',
                fullscreenscale INT(3) NOT NULL DEFAULT '0',
                logopath VARCHAR(200) NOT NULL DEFAULT '0',
                logo_target VARCHAR(200) NOT NULL,
                volume INT(3) NOT NULL DEFAULT '0',
                logoalign VARCHAR(10) NOT NULL DEFAULT '0',
                hdflvplayer_ads INT(3) NOT NULL DEFAULT '0',
                HD_default INT(3) NOT NULL DEFAULT '0',
                download INT(3) NOT NULL DEFAULT '0',
                logoalpha  INT(3) NOT NULL DEFAULT '0',
                skin_autohide INT(3) NOT NULL DEFAULT '0',
                stagecolor VARCHAR(45) NOT NULL,
                skin VARCHAR(200) NOT NULL,
                embed_visible INT(3) NOT NULL DEFAULT '0',
                shareURL VARCHAR(200) NOT NULL,
                playlistXML VARCHAR(200) NOT NULL,
                debug INT(3) NOT NULL DEFAULT '0',
                timer INT(3) NOT NULL DEFAULT '0',
                zoom INT(3) NOT NULL DEFAULT '0',
                email INT(3) NOT NULL DEFAULT '0',
                fullscreen INT(3) NOT NULL DEFAULT '0',
                width INT(5) NOT NULL DEFAULT '0',
                height INT(5) NOT NULL DEFAULT '0',
                display_logo INT(3) NOT NULL DEFAULT '0',
                configXML VARCHAR(200) NOT NULL,
                license VARCHAR(200) NOT NULL DEFAULT '0',
                upload_path VARCHAR(200) NOT NULL DEFAULT '0'
                ) $charset_collate;";

        $res = $wpdb->get_results($sql);
    }
}

// get the default options after reset or installation


?>