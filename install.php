<?php
/*
+----------------------------------------------------------------+
+	hdflvplayer-install
+	
+   required for hdflvplayer
+----------------------------------------------------------------+
*/
/****************************************************************/
/* Install routine for hdflvplayer
/****************************************************************/
function hdflv_install() {

	global $wpdb;

		// set tablename
		$table_name 		= $wpdb->prefix . 'hdflv';
		$table_playlist		= $wpdb->prefix . 'hdflv_playlist';
		$table_med2play		= $wpdb->prefix . 'hdflv_med2play';

		$wfound = false;
		$pfound = false;
		$mfound = false;
		$found = true;
	
	    foreach ($wpdb->get_results("SHOW TABLES;", ARRAY_N) as $row) {
	        	
			if ($row[0] == $table_name) 	$wfound = true;
			if ($row[0] == $table_playlist) $pfound = true;
			if ($row[0] == $table_med2play) $mfound = true;
		}
	        
    	// add charset & collate like wp core
		$charset_collate = '';
	
		if ( version_compare(mysql_get_server_info(), '4.1.0', '>=') ) {
			if ( ! empty($wpdb->charset) )
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			if ( ! empty($wpdb->collate) )
				$charset_collate .= " COLLATE $wpdb->collate";
		}
	        
		if (!$wfound) {
		 
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
		
		if (!$pfound) {
		 
		 	$sql = "CREATE TABLE ".$table_playlist." (
				pid BIGINT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				playlist_name VARCHAR(200) NOT NULL ,
				playlist_desc LONGTEXT NULL,
				playlist_order VARCHAR(50) NOT NULL DEFAULT 'ASC'
				) $charset_collate;";
	     
			$res = $wpdb->get_results($sql);
		}

		if (!$mfound) {
		 
		 	$sql = "CREATE TABLE ".$table_med2play." (
				rel_id BIGINT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				media_id BIGINT(10) NOT NULL DEFAULT '0',
				playlist_id BIGINT(10) NOT NULL DEFAULT '0',
				porder MEDIUMINT(10) NOT NULL DEFAULT '0',
                sorder INT(3) NOT NULL DEFAULT '0'
				) $charset_collate;";
	     
			$res = $wpdb->get_results($sql);
		}


}

// get the default options after reset or installation


?>