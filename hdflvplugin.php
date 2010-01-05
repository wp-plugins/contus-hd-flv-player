<?php

/*
Plugin Name: Contus HDFLVPlayer Plugin
Version: 1.1
Plugin URI: http://www.hdflvplayer.net/wordpress/
Description: Simplifies the process of adding video to a WordPress blog. Powered by Contus Support HDFLVPlayer and SWFObject by Geoff Stearns.
Author: Contus Support.
Author URI: http://www.hdflvplayer.net/wordpress/

HDFLVPlayer for Wordpress

*/




 
$videoid = 0;
$site_url = get_option('siteurl');

function HDFLV_Parse($content) {
	$content = preg_replace_callback("/\[hdplay ([^]]*)\]/i", "HDFLV_Render", $content);
	return $content;
}

function HDFLV_Render($matches) {
    global $wpdb;

	global $videoid, $site_url;
	$output = '';
	$matches[1] = str_replace(array('&#8221;','&#8243;'), '', $matches[1]);
	preg_match_all('/([.\w]*)=(.*?) /i', $matches[1], $attributes);

	$arguments = array();
    
	foreach ( (array) $attributes[1] as $key => $value ) {
		// Strip out legacy quotes
		$arguments[$value] = str_replace('"', '', $attributes[2][$key]);

	}

	if ( !array_key_exists('id', $arguments) && !array_key_exists('playlistid', $arguments) ) {

		return '<div style="background-color:#ff9;padding:10px;"><p>Error: Required parameter "id" or "playlistid" is missing!</p></div>';
        exit;
	}

	


    if(array_key_exists('id', $arguments))
    {
        $sql1= "select file from ".$wpdb->prefix."hdflv where vid=".$arguments['id']."";
        $result = mysql_query($sql1);
        $row = mysql_fetch_array($result, MYSQL_NUM);
        $arguments['file']= $row[0];
    }

    if(array_key_exists('playlistid', $arguments))
    {
        $sql3= "select * from ".$wpdb->prefix."hdflv_med2play where playlist_id=".$arguments['playlistid']."";
        $result4 = mysql_query($sql3);
    }
    


	$options = get_option('HDFLVSettings');

	/* Override inline parameters */
	if ( array_key_exists('width', $arguments) ) {
		$options[0][17]['v'] = $arguments['width'];

	}
	if ( array_key_exists('height', $arguments) ) {
		$options[0][16]['v'] = $arguments['height'];
	}

	if(strpos($arguments['file'], 'http://') !== false || isset($arguments['streamer']) || strpos($arguments['file'], 'https://') !== false) {
		// This is a remote file, so leave it alone but clean it up a little
		$arguments['file'] = str_replace('&#038;','&',$arguments['file']);
	} else {
        $arguments['file'] = stripslashes($arguments['file']);
		//$arguments['file'] = $site_url . '/' . $arguments['file'];
	}
	$output .= "\n" . '<span id="video' . $videoid . '" class="HDFLV">' . "\n";
   	$output .= '<a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see this player.</span>' . "\n";
    $output .= '<script type="text/javascript">' . "\n";
	$output .= 'var s' . $videoid . ' = new SWFObject("' . $site_url . '/wp-content/plugins/'.dirname( plugin_basename(__FILE__) ).'/hdflvplayer/hdplayer.swf' . '","n' . $videoid . '","' . $options[0][17]['v'] . '","' . $options[0][16]['v'] . '","7");' . "\n";
	$output .= 's' . $videoid . '.addParam("allowfullscreen","true");' . "\n";
	$output .= 's' . $videoid . '.addParam("allowscriptaccess","always");' . "\n";
	$output .= 's' . $videoid . '.addParam("wmode","opaque");' . "\n";

	$output .= 's' . $videoid . '.addVariable("id","n' . $videoid . '");' . "\n";
	for ( $i=0; $i<count($options);$i++ ) {
		foreach ( (array) $options[$i] as $key=>$value ) {
			/* Allow for inline override of all parameters */
			if ( array_key_exists($value['on'], $arguments) && $value['on'] ) {
				$value['v'] = $arguments[$value['on']];
			}
			if ( $value['v'] != '' ) {
				// Check to see if we're processing a "skin". If so, make the filename absolute using the
				// fully qualified path. This will ensure the player displays correctly on category pages as well.
				if($value['on'] == 'skin') {
                    if($value['v'] != 'undefined')
					 {
						$output .= 's' . $videoid . '.addVariable("' . $value['on'] . '","' . $site_url . '/wp-content/plugins/'.dirname( plugin_basename(__FILE__) ).'/hdflvplayer/skin/' . $value['v'] . '/' . trim($value['v']) . '.swf");' . "\n";
					}
				} else {
					$output .= 's' . $videoid . '.addVariable("' . $value['on'] . '","' . trim($value['v']) . '");' . "\n";
				}
			}
		}
	}
    if($options[0][23]['v'] != 'true')
    {
        $options[0][10]['v']='';
    }
    if($options[0][25]['v'] == 'true')
    {
      if(array_key_exists('playlistid', $arguments))
        {
            if(mysql_num_rows($result4))
            {
                $output .= 's' . $videoid . '.addVariable("playlistXML","'. get_option('siteurl').'/wp-content/plugins/' . dirname( plugin_basename(__FILE__) ).'/myextractXML.php?id='.$arguments['playlistid'].'");' . "\n";
            }else    $output .= 's' . $videoid . '.addVariable("playlist","false");' . "\n";
        }else $output .= 's' . $videoid . '.addVariable("playlist","false");' . "\n";
    }

    $output .= 's' . $videoid . '.addVariable("logopath","' . $site_url . '/wp-content/plugins/'.dirname( plugin_basename(__FILE__) ).'/hdflvplayer/images/' . $options[0][10]['v'] . '");' . "\n";

    if(array_key_exists('id', $arguments))
    {
        $output .= 's' . $videoid . '.addVariable("file","' . $arguments['file'] . '");' . "\n";
    }

    $output .= 's' . $videoid . '.write("video' . $videoid . '");' . "\n";
	$output .= '</script>' . "\n";
    $videoid++;
    return $output;



}



















function HDFLVAddPage() {
    add_media_page  ( __('hdflv','hdflv'), __('HDFLVPlayer','hdflv'), 'edit_posts' , 'hdflv', 'show_menu' );
    
	add_options_page('HDFLVPlayer Options', 'HDFLVPlayer Options', '8', 'hdflvplugin.php', 'FlashOptions');
}

function show_menu()
{
    switch ($_GET["page"]){
			case "hdflv" :
                
				include_once (dirname (__FILE__). '/functions.php');	// admin functions
				include_once (dirname (__FILE__). '/manage.php');
                echo $wpdb;// admin functions
				$MediaCenter = new HDFLVManage();
				break;

                    }
}






function FlashOptions() {
    global $wpdb;
	global $site_url;
	$message = '';
	$g = array(0=>'Properties');

	$options = get_option('HDFLVSettings');



	// Process form submission
	if ($_POST) {
		for($i=0; $i<count($options);$i++) {
			foreach( (array) $options[$i] as $key=>$value) {
				// Handle Checkboxes that don't send a value in the POST
				if($value['t'] == 'cb' && !isset($_POST[$options[$i][$key]['on']])) {
					$options[$i][$key]['v'] = 'false';
				}
				if($value['t'] == 'cb' && isset($_POST[$options[$i][$key]['on']])) {
					$options[$i][$key]['v'] = 'true';
				}
				// Handle all other changed values
				if(isset($_POST[$options[$i][$key]['on']]) && $value['t'] != 'cb') {
					$options[$i][$key]['v'] = $_POST[$options[$i][$key]['on']];
				}

                if($_FILES['file']['name'] != '')
                {
                    $options[0][10]['v'] = $_FILES['file']['name'];
                }
                $options[0][28]['v'] = $_POST['uploadurl'];
			}
		}
         //$options['usewpupload'] = $_POST['usewpupload'];
         //echo $options['usewpupload'];
		update_option('HDFLVSettings', $options);

         move_uploaded_file($_FILES["file"]["tmp_name"],"../wp-content/plugins/".dirname( plugin_basename(__FILE__) )."/hdflvplayer/images/" . $_FILES["file"]["name"]);
		$message = '<div class="updated"><p><strong>Options saved.</strong></p></div>';
	}


	echo '<div class="wrap">';
	echo '<h2>HDFLVPlayer Options</h2>';
	echo $message;
	echo '<form method="post" enctype="multipart/form-data" action="options-general.php?page=hdflvplugin.php">';
	echo "<p>Welcome to the HDFLVPlayer plugin options menu! &nbsp;&nbsp; <a style=color:red; href=$site_url/wp-admin/upload.php?page=hdflv>Add Media</a></p>";
   // echo "<a href=$site_url/wp-admin/upload.php?page=hdflv>Add Media</a>";

	$ski =  str_replace('wp-admin', 'wp-content', dirname($_SERVER['SCRIPT_FILENAME'])) .'/plugins/'.dirname( plugin_basename(__FILE__) ).'/hdflvplayer/skin';

	$skins = array();

	// Pull the directories listed in the skins folder to generate the dropdown list with valid skin files
	chdir($ski);
	if ($handle = opendir($ski)) {
	    while (false !== ($file = readdir($handle))) {
	        if ($file != "." && $file != "..") {
				if(is_dir($file)) {
					$skins[] = $file;
				}
	        }
	    }
	    closedir($handle);
	}

	$options[0][18]['op'] = $skins;
    $options[0][18]['name'] = $skins;


	foreach( (array) $options as $key=>$value) {
		echo '<h3>' . $g[$key] . '</h3>' . "\n";
		echo '<table class="form-table">' . "\n";
		foreach( (array) $value as $setting) {
			echo '<tr><th scope="row">' . $setting['dn'] . '</th><td>' . "\n";
			switch ($setting['t']) {
                case 'file':
                    echo "<input type='file' name='file'>".$options[0][10]['v'];
                    break;
				case 'tx':
					echo '<input type="text" name="' . $setting['on'] . '" value="' . $setting['v'] . '" size=45  />';
					break;
				case 'dd':
					echo '<select name="' . $setting['on'] . '">';
                    $a=0;
					foreach( (array) $setting['op'] as $v) {
						$selected = '';
						if($v == $setting['v']) {
							$selected = ' selected';
						}
						echo '<option value="' . $v . '" ' . $selected . '>' . $setting['name'][$a] . '</option>';
                        $a++;
					}
					echo '</select>';
					break;
				case 'cb':
					echo '<input type="checkbox" class="check" name="' . $setting['on'] . '" ';
					if($setting['v'] == 'true') {
						echo 'checked="checked"';
					}
					echo ' />';
					break;
                case 'rb': ?>
                            <label><input name=<? echo $setting['on'] ?> type="radio" value="1" <? if( $setting['v'] == 1) echo 'checked'; ?> /> <?php _e('Standard upload folder : ','hdflv') ?></label><code><?php echo get_option('upload_path'); ?></code><br />
                            <label><input name=<? echo $setting['on'] ?> type="radio" value="0"  <? if( $setting['v'] == 0) echo 'checked'; ?> /> <?php _e('Store uploads in this folder : ','hdflv') ?></label>
                            <input type="text" size="50" maxlength="200" name="uploadurl" value="<?php echo $options[0][28]['v'] ?>" />
                    <? break;
				}
				echo '</td></tr>' . "\n";
			}
			echo '</table>' . "\n";
		}   
	echo '<p class="submit"><input class="button-primary" type="submit" method="post" value="Update Options"></p>';
	echo '</form>';
	echo '</div>';  
}




function HDFLV_head() {
	global $site_url;
	echo '<script type="text/javascript" src="' . $site_url . '/wp-content/plugins/'.dirname( plugin_basename(__FILE__) ).'/swfobject.js"></script>' . "\n";
}

add_action('wp_head', 'HDFLV_head');

function HDFLVLoadDefaults() {
	$f = array();

	/*
	  Array Legend:
	  on = Option Name
	  dn = Display Name
	  t = Type
	  v = Default Value
	*/

	//Properties

	$f[0][4]['on'] = 'link';
	$f[0][4]['dn'] = 'Link URL';
	$f[0][4]['t'] = 'tx';
	$f[0][4]['v'] = '';

    $f[0][4]['on'] = 'skin_autohide';
	$f[0][4]['dn'] = 'Skin Autohide';
	$f[0][4]['t'] = 'cb';
	$f[0][4]['v'] = 'true';


    $f[0][5]['on'] = 'logoalpha';
	$f[0][5]['dn'] = 'Logo Alpha';
	$f[0][5]['t'] = 'tx';
	$f[0][5]['v'] = '50';


    $f[0][8]['on'] = 'normalscale';
	$f[0][8]['dn'] = 'Normal Screen Scale';
	$f[0][8]['t'] = 'dd';
	$f[0][8]['v'] = '0';
	$f[0][8]['op'] = array('0','1','2');
    $f[0][8]['name'] = array('Aspect ratio','Orginal Size', 'Fit to screen');

    $f[0][9]['on'] = 'fullscreenscale';
	$f[0][9]['dn'] = 'Full Screen';
	$f[0][9]['t'] = 'dd';
	$f[0][9]['v'] = '0';
	$f[0][9]['op'] = array('0','1', '2');
    $f[0][9]['name'] = array('Aspect ratio','Orginal Size', 'Fit to screen');


    $f[0][10]['on'] = 'logopath';
	$f[0][10]['dn'] = 'Logo Path';
	$f[0][10]['t'] = 'file';
	$f[0][10]['v'] = "platoon.png";



    $f[0][12]['on'] = 'stagecolor';
	$f[0][12]['dn'] = 'Background Color';
	$f[0][12]['t'] = 'tx';
	$f[0][12]['v'] = '0x000000';

    $f[0][13]['on'] = 'autoplay';
	$f[0][13]['dn'] = 'Auto Play';
	$f[0][13]['t'] = 'cb';
	$f[0][13]['v'] = 'false';



    $f[0][14]['on'] = 'buffer';
	$f[0][14]['dn'] = 'Buffer Length';
	$f[0][14]['t'] = 'tx';
	$f[0][14]['v'] = '1';



    $f[0][15]['on'] = 'Volume';
	$f[0][15]['dn'] = 'Startup Volume';
	$f[0][15]['t'] = 'tx';
	$f[0][15]['v'] = '50';

    $f[0][16]['on'] = 'height';
	$f[0][16]['dn'] = 'Player Height';
	$f[0][16]['t'] = 'tx';
	$f[0][16]['v'] = '280';

    $f[0][17]['on'] = 'width';
	$f[0][17]['dn'] = 'Player Width';
	$f[0][17]['t'] = 'tx';
	$f[0][17]['v'] = '450';



    $f[0][18]['on'] = 'skin';
	$f[0][18]['dn'] = 'Skin';
	$f[0][18]['t'] = 'dd';
	$f[0][18]['v'] = 'skin_black';
	$f[0][18]['op'] = array();
    $f[0][18]['name'] = array();

    $f[0][19]['on'] = 'zoom';
	$f[0][19]['dn'] = 'Zoom';
	$f[0][19]['t'] = 'cb';
	$f[0][19]['v'] = 'true';

    $f[0][20]['on'] = 'timer';
	$f[0][20]['dn'] = 'Display Timer';
	$f[0][20]['t'] = 'cb';
	$f[0][20]['v'] = 'true';

    $f[0][22]['on'] = 'fullscreen';
	$f[0][22]['dn'] = 'Full Screen';
	$f[0][22]['t'] = 'cb';
	$f[0][22]['v'] = 'true';

    $f[0][23]['on'] = 'display_logo';
	$f[0][23]['dn'] = 'Display logo';
	$f[0][23]['t'] = 'cb';
	$f[0][23]['v'] = 'true';
    
    $f[0][24]['on'] = 'share';
	$f[0][24]['dn'] = 'Share';
	$f[0][24]['t'] = 'cb';
	$f[0][24]['v'] = 'true';

    $f[0][25]['on'] = 'playlist';
	$f[0][25]['dn'] = 'playlist';
	$f[0][25]['t'] = 'cb';
	$f[0][25]['v'] = 'true';


    /*$f[0][27]['on'] = 'usedefault';
	$f[0][27]['dn'] = 'Use default file upload';
	$f[0][27]['t'] = 'rb';
	$f[0][27]['v'] = '1';

    $f[0][28]['v'] = "wp-content/uploads";*/
    
    $f[0][29]['on'] = 'logo_target';
	$f[0][29]['dn'] = 'Logo Path';
	$f[0][29]['t'] = 'tx';
	$f[0][29]['v'] = 'http://hdflvplayer.net/wordpress/';



	return $f;
}

function hdflv_deinstall() {
	global $wpdb, $wp_version;

	$hd_table = $wpdb->prefix . 'hdflv';
	$hd_table_mp = $wpdb->prefix . 'hdflv_med2play';
	$hd_table_pl = $wpdb->prefix . 'hdflv_playlist';


	$wpdb->query("DROP TABLE IF EXISTS `". $hd_table . "`");
	$wpdb->query("DROP TABLE IF EXISTS `". $hd_table_mp . "`");
	$wpdb->query("DROP TABLE IF EXISTS `". $hd_table_pl . "`");


}



function hd_install() {

	require_once(dirname (__FILE__). '/install.php');
	hdflv_install();
}


function HDFLV_activate() {
	update_option('HDFLVSettings', HDFLVLoadDefaults());
}

register_activation_hook( plugin_basename( dirname(__FILE__) ) . '/hdflvplugin.php', 'hd_install' );


register_activation_hook(__FILE__,'HDFLV_activate');
register_uninstall_hook(__FILE__, 'hdflv_deinstall');




function HDFLV_deactivate() {
	delete_option('HDFLVSettings');
}

register_deactivation_hook(__FILE__,'HDFLV_deactivate');

// CONTENT FILTER

add_filter('the_content', 'HDFLV_Parse');


// OPTIONS MENU

add_action('admin_menu', 'HDFLVAddPage');

?>
