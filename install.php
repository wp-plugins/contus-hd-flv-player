<?php
/* 
 * Purpose : Used for installing player plugin (Creating tables)
 * Edited By : kranthi kumar
 * Version: 1.7
 * Email : kranthikumar@contus.in
*/
/****************************************************************/
/* Install routine for hdflvplayer
/****************************************************************/
function contusHdInstalling()
{
    global $wpdb;

    // set tablename
    $tablehdflv 		= $wpdb->prefix . 'hdflv';
    $tablePlaylist		= $wpdb->prefix . 'hdflv_playlist';
    $tableMed2play		= $wpdb->prefix . 'hdflv_med2play';
    $tableSettings		= $wpdb->prefix . 'hdflv_settings';
   
    update_option('youtubelogoshow', 1);
 
    // add charset & collate like wp core
    $charset_collate = '';

    if ( version_compare(mysql_get_server_info(), '4.1.0', '>=') )
    {
        if ( ! empty($wpdb->charset) )
        $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if ( ! empty($wpdb->collate) )
        $charset_collate .= " COLLATE $wpdb->collate";
    }
           $sqlTableHdflv = "CREATE TABLE IF NOT EXISTS ".$tablehdflv." (
                vid MEDIUMINT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    name MEDIUMTEXT NULL,
                    file MEDIUMTEXT NULL,
                    hdfile MEDIUMTEXT NULL,
                    image MEDIUMTEXT NULL,
                    opimage MEDIUMTEXT NULL,
                    link MEDIUMTEXT NULL ,
                    `is_active` INT(1) NOT NULL DEFAULT '1'
                    ) $charset_collate;";
    
            $sqlPlayList = "CREATE TABLE IF NOT EXISTS ".$tablePlaylist." (
                pid INT(2) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                playlist_name VARCHAR(200) NOT NULL ,
                playlist_desc VARCHAR(200) NULL,
                playlist_order VARCHAR(50) NOT NULL DEFAULT 'ASC',
               `is_pactive` INT(1) NOT NULL DEFAULT '1'
                ) $charset_collate;";
            
            
    
				$sql = "SHOW TABLES LIKE '$tablehdflv'"; //is table in DB or not
				$isTable =  $wpdb->query($sql);  //if TRUE THEN 1 ELSE 0
				if($isTable){ //yes already in DB so add fields only
					$sql = "SHOW COLUMNS FROM $tablehdflv";
				    $numOfCol =  $wpdb->query($sql);
				    if($numOfCol < 8){ // add one col like is_active
				    	$sql = "ALTER  TABLE  $tablehdflv ADD is_active INT(1) DEFAULT 1";
				    	$wpdb->query($sql);
				    }
				}
				else{         // create table 
						 $wpdb->query($sqlTableHdflv);
				}
				$sql = "SHOW TABLES LIKE '$tablePlaylist'"; //is table in DB or not
				$isTable =  $wpdb->query($sql);  //if TRUE THEN 1 ELSE 0
				
				if($isTable){ //yes already in DB so add fields only
					$sql = "SHOW COLUMNS FROM $tablePlaylist";
				    $numOfCol =  $wpdb->query($sql);
				    if($numOfCol < 5){ // add one col like is_pactive
				    	
				    	$sql = "ALTER  TABLE  $tablePlaylist ADD is_pactive INT(1) DEFAULT 1";
				    	$wpdb->query($sql);
				    }
				}
				else{         // create table 
					$wpdb->query($sqlPlayList);
				}
    
        $sql = "CREATE TABLE IF NOT EXISTS ".$tableMed2play." (
                rel_id BIGINT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                media_id BIGINT(10) NOT NULL DEFAULT '0',
                playlist_id BIGINT(10) NOT NULL DEFAULT '0',
                porder MEDIUMINT(10) NOT NULL DEFAULT '0',
                sorder INT(3) NOT NULL DEFAULT '0'
                ) $charset_collate;";

       $wpdb->query($sql);
    
        $sql = "CREATE TABLE  IF NOT EXISTS ".$tableSettings." (
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

        $wpdb->query($sql);
        $wpdb->query(" INSERT INTO " . $wpdb->prefix . "hdflv_settings
					   VALUES (1,1,1,1,1,0,1,'platoon.jpg','http://www.hdflvplayer.net/',50,'LR',1,1,0,20,1,'0x000000','skin_black',0,'hdflvplayer/url.php','playXml',1,1,1,1,1,500,400,1,0,0,'wp-content/uploads')");
        
}

function hdflvDropTables(){
	global $wpdb;
	$tablehdflv 		= $wpdb->prefix . 'hdflv';
    $tablePlaylist		= $wpdb->prefix . 'hdflv_playlist';
    $tableMed2play		= $wpdb->prefix . 'hdflv_med2play';
    $tableSettings		= $wpdb->prefix . 'hdflv_settings';
	$sql =  "DROP TABLE $tablehdflv , $tablePlaylist , $tableMed2play ,  $tableSettings ";
	$wpdb->query($sql);
 	//drop table start
 	delete_option('SkinContentHide');
 	delete_option('displayContentHide');
 	delete_option('GeneralContentHide');
 	delete_option('PlaylistContentHide');
 	delete_option('LogoContentHide');
 	delete_option('VideoContentHide');
 	delete_option('LicenseContentHide');
 	 
}
?>