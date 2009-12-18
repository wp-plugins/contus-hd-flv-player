<?php

/*
Plugin Name: Contus HDFLVPlayer Plugin
Version: 1.0
Plugin URI: http://www.contussupport.com
Description: Simplifies the process of adding video to a WordPress blog. Powered by Contus Support HDFLVPlayer and SWFObject by Geoff Stearns.
Author: Contus Support.
Author URI: http://www.contussupport.com

HDFLVPlayer for Wordpress

*/

$videoid = 0;
$site_url = get_option('siteurl');

function FlashVideo_Parse($content) {
	$content = preg_replace_callback("/\[hdplay ([^]]*)\/\]/i", "FlashVideo_Render", $content);
	return $content;
}

function FlashVideo_Render($matches) {

	global $videoid, $site_url;
	$output = '';
	$matches[1] = str_replace(array('&#8221;','&#8243;'), '', $matches[1]);
	preg_match_all('/([.\w]*)=(.*?) /i', $matches[1], $attributes);

	$arguments = array();

	foreach ( (array) $attributes[1] as $key => $value ) {
		// Strip out legacy quotes
		$arguments[$value] = str_replace('"', '', $attributes[2][$key]);

	}

	if ( !array_key_exists('filename', $arguments) && !array_key_exists('file', $arguments) ) {
		return '<div style="background-color:#ff9;padding:10px;"><p>Error: Required parameter "file" is missing!</p></div>';
		exit;
	}

	//Deprecate filename in favor of file.
	if(array_key_exists('filename', $arguments)) {
		$arguments['file'] = $arguments['filename'];
	}


	$options = get_option('FlashVideoSettings');

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
		$arguments['file'] = $site_url . '/' . $arguments['file'];
	}
	$output .= "\n" . '<span id="video' . $videoid . '" class="flashvideo">' . "\n";
   	$output .= '<a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see this player.</span>' . "\n";
    $output .= '<script type="text/javascript">' . "\n";
	$output .= 'var s' . $videoid . ' = new SWFObject("' . $site_url . '/wp-content/plugins/contus-hd-flv-player/hdflvplayer/hdplayer.swf' . '","n' . $videoid . '","' . $options[0][17]['v'] . '","' . $options[0][16]['v'] . '","7");' . "\n";
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
						$output .= 's' . $videoid . '.addVariable("' . $value['on'] . '","' . $site_url . '/wp-content/plugins/contus-hd-flv-player/hdflvplayer/skin/' . $value['v'] . '/' . trim($value['v']) . '.swf");' . "\n";
					}
				} else {
					$output .= 's' . $videoid . '.addVariable("' . $value['on'] . '","' . trim($value['v']) . '");' . "\n";
				}
			}
		}
	}
    $output .= 's' . $videoid . '.addVariable("logopath","' . $site_url . '/wp-content/plugins/contus-hd-flv-player/hdflvplayer/images/' . $options[0][10]['v'] . '");' . "\n";
	$output .= 's' . $videoid . '.addVariable("file","' . $arguments['file'] . '");' . "\n";
	$output .= 's' . $videoid . '.write("video' . $videoid . '");' . "\n";
	$output .= '</script>' . "\n";

	$videoid++;
    return $output;

}


function FlashVideoAddPage() {
	add_options_page('HDFLVPlayer Options', 'HDFLVPlayer Options', '8', 'hdflvplugin.php', 'FlashOptions');
}

function FlashOptions() {
	global $site_url;
	$message = '';
	$g = array(0=>'Properties');

	$options = get_option('FlashVideoSettings');



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
			}
		}

		update_option('FlashVideoSettings', $options);

         move_uploaded_file($_FILES["file"]["tmp_name"],"../wp-content/plugins/contus-hd-flv-player/hdflvplayer/images/" . $_FILES["file"]["name"]);
		$message = '<div class="updated"><p><strong>Options saved.</strong></p></div>';
	}


	echo '<div class="wrap">';
	echo '<h2>HDFLVPlayer Options</h2>';
	echo $message;
	echo '<form method="post" enctype="multipart/form-data" action="options-general.php?page=hdflvplugin.php">';
	echo "<p>Welcome to the HDFLVPlayer plugin options menu! <br><br>";
    echo "<li style='background:#D0D0D0;list-style:none;width:850px'><p style='padding-left:6px'>You can also set different width and height for the player in different posts irrespective of the values specified here.<br><br>
           <b>For example:</b>[hdplay file=http://www.yoursitename.com/videos/filename.flv width=400 height=400 /]<br><br><b>Example for YoutubeURL:</b>[hdplay file=http://www.youtube.com/watch?v=-galhgKDvNg width=400 height=400 /]</p></li>";

	$ski =  str_replace('wp-admin', 'wp-content', dirname($_SERVER['SCRIPT_FILENAME'])) .'/plugins/contus-hd-flv-player/hdflvplayer/skin';

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
					echo '<input type="text" name="' . $setting['on'] . '" value="' . $setting['v'] . '" />';
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
				}
				echo '</td></tr>' . "\n";
			}
			echo '</table>' . "\n";
		}
	echo '<p class="submit"><input class="button-primary" type="submit" method="post" value="Update Options"></p>';
	echo '</form>';
	echo '</div>';
}

function FlashVideo_head() {
	global $site_url;
	echo '<script type="text/javascript" src="' . $site_url . '/wp-content/plugins/contus-hd-flv-player/swfobject.js"></script>' . "\n";
}

add_action('wp_head', 'FlashVideo_head');

function FlashVideoLoadDefaults() {
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

    $f[0][11]['on'] = 'playlistXML';
	$f[0][11]['dn'] = 'Playlist Path';
	$f[0][11]['t'] = 'tx';
	$f[0][11]['v'] = '';

    $f[0][12]['on'] = 'stagecolor';
	$f[0][12]['dn'] = 'Background Color';
	$f[0][12]['t'] = 'tx';
	$f[0][12]['v'] = '0x000000';

    $f[0][13]['on'] = 'autoplay';
	$f[0][13]['dn'] = 'Auto Play';
	$f[0][13]['t'] = 'cb';
	$f[0][13]['v'] = 'false';



    $f[0][14]['on'] = 'bufferlength';
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




	return $f;
}

function FlashVideo_activate() {
	update_option('FlashVideoSettings', FlashVideoLoadDefaults());
}

register_activation_hook(__FILE__,'FlashVideo_activate');

function FlashVideo_deactivate() {
	delete_option('FlashVideoSettings');
}

register_deactivation_hook(__FILE__,'FlashVideo_deactivate');

// CONTENT FILTER

add_filter('the_content', 'FlashVideo_Parse');


// OPTIONS MENU

add_action('admin_menu', 'FlashVideoAddPage');

?>
