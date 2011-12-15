<?php
/*
 * Plugin Name: Contus HDFLVPlayer Plugin
 * Version: 1.7
 * Author: Apptha
 * Plugin URI: http://www.apptha.com/category/extension/Wordpress/HD-FLV-Player-Plugin/
 * Author URI: http://www.apptha.com/
 * Description: Contus HD FLV Player simplifies the process of adding high definition videos to the Wordpress blog. The plugin efficiently plays your Videos with high quality video and audio output.  
 * Path :wp-content\plugins\contus-hd-flv-player\hdflvplugin.php
 * Edited by : kranthi kumar
 * Email : kranthikumar@contus.in
 * date:09/12/11
 * Purpose : Main plugin file to configure
 */
session_start();
$videoid = 0;
$site_url = $siteUrl = get_option('siteurl');
$dir = dirname(plugin_basename(__FILE__));
$dirExp = explode('/', $dir);
$dirPage = $dirExp[0];
function HDFLV_Parse($content) {
	$content = preg_replace_callback('/\[hdpla ([^]]*)\]/i', 'hdflvPlayerReader', $content);
	return $content;
}

//Used for Rendering player with the configured informations and save configurations from admin
function hdflvPlayerReader($arguments= array()) {

	global $wpdb,$videoid, $siteUrl ,$dirPage , $site_url;

	$configXML = $wpdb->get_row("SELECT width,height FROM " . $wpdb->prefix . "hdflv_settings");
	if($arguments['width'] != ''){
		$width = $arguments['width'];
	}else{
		$width = $configXML->width;
	}
	if($arguments['height'] != ''){
		$height = $arguments['height'];
	}else{
		$height = $configXML->height;
	}

	if (isset($arguments['id'])) {
		$videofiles = $wpdb->get_row("SELECT vid,file,hdfile,image,name FROM " . $wpdb->prefix . "hdflv where vid = ".$arguments['id']);
		$file = $videofiles->file;

		$videofile = $videofiles->hdfile;
		$imagefile = $videofiles->image;
		$videoName = $videofiles->name;
	}
	elseif (isset($arguments['playlistid'])) {
	 $playlist_id = $arguments['playlistid'];
	 $playlist = $wpdb->get_row("SELECT w.* FROM " . $wpdb->prefix . "hdflv w  INNER JOIN " . $wpdb->prefix . "hdflv_med2play m  WHERE (m.playlist_id = '$playlist_id') AND m.media_id = w.vid");
	 if ($playlist) {
	 	$select = " SELECT w.* FROM " . $wpdb->prefix . "hdflv w";
	 	$select .= " INNER JOIN " . $wpdb->prefix . "hdflv_med2play m";
	 	$select .= " WHERE (m.playlist_id = '$playlist_id'";
	 	$select .= " AND m.media_id = w.vid) GROUP BY w.vid ";
	 	$select .= " ORDER BY m.sorder ASC , m.porder " . $playlist->playlist_order . " ,w.vid " . $playlist->playlist_order . " limit 0,1";
	 	//echo $select;
	 	$videofiles = $wpdb->get_results($wpdb->prepare($select));
	 	$playlistName = $wpdb->get_row("SELECT w . name FROM wp_hdflv w INNER JOIN wp_hdflv_med2play m WHERE (m.playlist_id = '$playlist_id')  AND m.media_id = w.vid");
	 	$playName = $playlistName->name;

	 	$file = $videofiles->file;

	 	$videofile = $videofiles->hdfile;
	 	$imagefile = $videofiles->image;
	 	$videoId = $videofiles->vid;

	 }
	}?>
<script type="text/javascript">
      
        function currentvideo(video_id,title,tag,default_id){
        	if(title == undefined)
     			{  
     		  		document.getElementById(default_id).innerHTML='';
     		  		//alert('empty if');
         		}
     		else {
     				document.getElementById(default_id).innerHTML=title;
     				//alert('title'+title);
         	 	}
        	}
        	
    </script>

	<?php
	if (!isset($arguments['id'])){
		$output .='<h3 id="'. $videoid .'" align="left" style="min-height:30px">'	. $playName .'</h3>';} else {
			$output .='<h3 id="'. $videoid .'" align="left" style="min-height:30px">'	. $videofiles->name .'</h3>'; }
			$output .= "\n" . '<span id="video' . $videoid . '" class="HDFLV">' . "\n";
			$output .= '<a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see this player.</span>' . "\n";
			$output .= '<script type="text/javascript">' . "\n";
			$output .= 'var s' . $videoid . ' = new SWFObject("' . $siteUrl . '/wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/hdflvplayer/hdplayer.swf' . '","n' . $videoid . '","' . $width . '","' . $height . '","7");' . "\n";
			$output .= 's' . $videoid . '.addParam("allowfullscreen","true");' . "\n";
			$output .= 's' . $videoid . '.addParam("allowscriptaccess","always");' . "\n";
			$output .= 's' . $videoid . '.addParam("wmode","opaque");' . "\n";

			$output .= 's' . $videoid . '.addVariable("baserefW","' . get_option('siteurl') . '");';
			if (isset($arguments['playlistid']) && isset($arguments['id'])) {
				$output .= 's' . $videoid . '.addVariable("pid","' . $arguments['playlistid'] . '");' . "\n";
				$output .= 's' . $videoid . '.addVariable("vid","' . $arguments['id'] . '");' . "\n";
				$output .= 's' . $videoid . '.addVariable("player","' . $videoid . '");' . "\n";
			} elseif (isset($arguments['playlistid'])) {
				$output .= 's' . $videoid . '.addVariable("pid","' . $arguments['playlistid'] . '");' . "\n";
				$output .= 's' . $videoid . '.addVariable("player","' . $videoid . '");' . "\n";
			} else {
				$output .= 's' . $videoid . '.addVariable("vid","' . $arguments['id'] . '");' . "\n";
				$output .= 's' . $videoid . '.addVariable("player","' . $videoid . '");' . "\n";
			}

			$output .= 's' . $videoid . '.write("video' . $videoid . '");' . "\n";
			$output .= '</script>' . "\n";
			$videoid++;
			$output .= '<div id="htmlplayer' . $videoid . '">';
			if(preg_match('/www\.youtube\.com\/watch\?v=[^&]+/', $file, $vresult)) {

				$urlArray = split("=", $vresult[0]);
				$videourl = trim($urlArray[1]);
				$output .= '<iframe  type="text/html" width="'.$width.'" height="' . $height . '"  src="http://www.youtube.com/embed/'.$videourl.'" frameborder="0">
</iframe>';
			}
			else
			{
				$output .= ' <video id="video" src="'. $file .'" poster="'.$imagefile.'" width="'.$width.'" height="' . $height . '" autobuffer controls onerror="failed(event)">
     Html5 Not support This video Format.</video>';
			}

			$output .= ' </div><script>var txt =  navigator.platform ;if(txt =="iPod"|| txt =="iPad"|| txt =="iPhone" || txt =="Linux armv7I")
            {   document.getElementById("htmlplayer' . $videoid . '").style.display = "block";
                document.getElementById("video' . $videoid . '").style.display = "none";
            }else{
 	 document.getElementById("htmlplayer' . $videoid . '").style.display = "none";
            }
			 function failed(e) {
			  if(txt =="iPod"|| txt =="iPad"|| txt =="iPhone" || txt =="Linux armv7I")
            {
     alert("Player doesnot support this video.");
   }
}
        </script>';

			return $output;
}

add_shortcode('hdplay', 'hdflvPlayerReader'); //Shortcode tag[hdplay]] to be searched in post content


/* Adding page & options */

function hdflvMenuCreate() {

	add_menu_page(__('hdflv', 'hdflv'), __('HDFLVPlayer', 'hdflv'), 2, "hdflv", "showMenu", get_bloginfo('url') . "/wp-content/plugins/contus-hd-flv-player/images/apptha.png");

	add_submenu_page( "hdflv", __('HDFLV Videos', 'hdflv'), __('Videos', 'hdflv'), 4, "hdflv", "showMenu");
    add_submenu_page( "hdflv", "HDFLV Options", "Playlist", 4, "hdflvplaylist", "showMenu");
	add_submenu_page( "hdflv", "HDFLV Options", "Settings", 4, "hdflvplugin.php", "FlashOptions");
}

function showMenu() {      // HDFLV Videos submenu coding in manage.php file
	switch ($_GET['page']) {
		case 'hdflv' :

			include_once (dirname(__FILE__) . '/functions.php'); // admin functions support to manage.php
			include_once (dirname(__FILE__) . '/manage.php');
			$MediaCenter = new HDFLVManage();
			break;
		case 'hdflvplaylist' :

			include_once (dirname(__FILE__) . '/functions.php'); // admin functions support to manage.php
			include_once (dirname(__FILE__) . '/manage.php');
			$MediaCenter = new HDFLVManage();
			$MediaCenter->mode = 'playlist';
			break;	

	}
}
/*  function use to set  div is hide or show in settings tab */

function getDisplayValueOfDiv($divId)
{
	if(!get_option($divId))
	{
		$display = 'block';
		$viewStymolId = 'ui-icon ui-icon-minusthick' ;
	}
	else{
		$display = 'none';
		$viewStymolId = 'ui-icon ui-icon-plusthick' ;
	}
	return $display;
}//function end hear

/* Function used to Edit player settings and generate settings form elements */
function FlashOptions() {
	global $wpdb;
	global $siteUrl;
	$message = '';
	$g = array(0 => 'Properties');

	$youtubelogshow =  filter_input(INPUT_POST  , 'logostatus');
	if(isset($youtubelogshow)){
		 
		update_option('youtubelogoshow', $youtubelogshow );
	}

	$options = get_option('HDFLVSettings');

	if ($_POST) {
		$settings = $wpdb->get_col("SELECT * FROM " . $wpdb->prefix . "hdflv_settings");
		if (count($settings) > 0) {
			$query = " UPDATE " . $wpdb->prefix . "hdflv_settings SET
			autoplay= '" . $_POST['autoplay'] . "',playlist='" . $_POST['playlist'] . "',playlistauto='" . $_POST['playlistauto']
			. "',buffer='" . $_POST['buffer'] . "',normalscale='" . $_POST['normalscale'] . "',fullscreenscale='" . $_POST['fullscreenscale'] . "'";
			if($_FILES['logopath']["name"] != ''){
				$query .= ",logopath='" . $_FILES['logopath']["name"]."'";
			}
			$query .= ",volume='" . $_POST['volume'] . "',logoalign='" . $_POST['logoalign']. "',logo_target='" . $_POST['logotarget'] . "',hdflvplayer_ads='" . $_POST['hdflvplayer_ads']
			. "',HD_default='" . $_POST['HD_default'] . "',download='" . $_POST['download'] . "',logoalpha='" . $_POST['logoalpha'] . "',skin_autohide='" . $_POST['skin_autohide']
			. "',stagecolor='" . $_POST['stagecolor'] . "',skin='" . $_POST['skin'] . "',embed_visible='" . $_POST['embed_visible'] . "',shareURL='" . $_POST['shareURL']
			. "',playlistXML='" . $_POST['playlistXML'] . "',debug='" . $_POST['debug'] . "',timer='" . $_POST['timer'] . "',zoom='" . $_POST['zoom']
			. "',email='" . $_POST['email'] . "',fullscreen='" . $_POST['fullscreen'] . "',width='" . $_POST['width'] . "',height='" . $_POST['height']
			. "',display_logo='" . $_POST['display_logo']. "',license='" .trim ( $_POST['license']). "',upload_path='" . $_POST['uploadurl']
			. "' WHERE settings_id = " . $settings[0]['settings_id'];
			$updateSettings = $wpdb->query($query);
		} else {
			require_once(dirname(__FILE__) . '/install.php');
			contusHdInstalling();
			HDFLVLoadDefaults();
			$insertSettings = $wpdb->query(" INSERT INTO " . $wpdb->prefix . "hdflv_settings
						VALUES ('','" . $_POST['autoplay'] . "','" . $_POST['playlist'] . "','" . $_POST['playlistauto'] . "','" . $_POST['buffer']
			. "','" . $_POST['normalscale'] . "','" . $_POST['fullscreenscale'] . "','','http://www.hdflvplayer.net/','" . $_POST['volume'] . "','" . $_POST['logoalign'] . "','" . $_POST['hdflvplayer_ads'] . "','" . $_POST['HD_default']
			. "','" . $_POST['download'] . "','" . $_POST['logoalpha'] . "','" . $_POST['skin_autohide'] . "','" . $_POST['stagecolor']
			. "','" . $_POST['skin'] . "','" . $_POST['embed_visible'] . "','" . $_POST['shareURL'] . "','" . $_POST['playlistXML']
			. "','" . $_POST['debug'] . "','" . $_POST['timer'] . "','" . $_POST['zoom'] . "','" . $_POST['email']
			. "','" . $_POST['fullscreen'] . "','" . $_POST['width'] . "','" . $_POST['height'] . "','" . $_POST['display_logo'] ."','" . $_POST['license']."','','wp-content/uploads')");
		}
	
		move_uploaded_file($_FILES["logopath"]["tmp_name"], dirname(__FILE__) . "/hdflvplayer/css/images/" . $_FILES["logopath"]["name"]);
		$message = '<div class="updated"><p><strong>Options saved.</strong></p></div>';
		
	}

	echo $message;

	$ski = str_replace('wp-admin', 'wp-content', dirname($_SERVER['SCRIPT_FILENAME'])) . '/plugins/' . dirname(plugin_basename(__FILE__)) . '/hdflvplayer/skin';

	$skins = array();

	// Pull the directories listed in the skins folder to generate the dropdown list with valid skin files
	chdir($ski);
	if ($handle = opendir($ski)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				if (is_dir($file)) {
					$skins[] = $file;
				}
			}
		}
		closedir($handle);
	}
	$contus = dirname(plugin_basename(__FILE__));
	$fetchSettings = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "hdflv_settings");
	?>

<!--HTML design for admin settings -->
<link
	rel="stylesheet"
	href="<?php echo $siteUrl ?>/wp-content/plugins/<?php echo dirname(plugin_basename(__FILE__)) ?>/hdflvplayer/css/jquery.ui.all.css">
<script
	type="text/javascript"
	src="../wp-content/plugins/<?php echo $contus ?>/js/hdflvscript.js"></script>

	<h2 style="margin-bottom: 1%;" class="nav-tab-wrapper">
	<a  id="hdflv"   href="?page=hdflv" class="nav-tab "> Video Files</a> 
	<a  id="video"   href="?page=hdflv&mode=video" class="nav-tab"> Add Videos</a> 
	<a  id="playlist" href="?page=hdflvplaylist" class="nav-tab">Playlist</a>
	<a  id="settings" href="?page=hdflvplugin.php" class="nav-tab">Settings</a>
	</h2>
	<script type="text/javascript">
		
	 document.getElementById("settings").className = 'nav-tab nav-tab-active';
 	  
	</script>
<div class="wrap">
	<h2>HDFLVPlayer Options</h2>
	<form method="post" enctype="multipart/form-data"
		action="admin.php?page=hdflv&mode=video">
		<p>
			Welcome to the HDFLVPlayer plugin options menu! &nbsp;&nbsp;<input
				class="button-primary" type="submit"
				value="<?php _e('Add Video', 'hdflv') ?> &raquo;" name="show_add" />
		</p>
	</form>
	<form method="post" enctype="multipart/form-data"
		action="admin.php?page=hdflvplugin.php">

		<div class="column" style="float: left;">
		<?php $showOrHide =  getDisplayValueOfDiv('displayContentHide');
		if($showOrHide == 'block')
		{
			$className = 'ui-icon ui-icon-minusthick';
		}
		else{
			$className = 'ui-icon ui-icon-plusthick';
		}

		?>
			<div
				class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
				<div class="portlet-header ui-widget-header ui-corner-all">
					<span id='displaySpan'
						onclick="hideContentDives('displayContentHide','displaySpan')"
						class="<?php echo $className; ?>"></span>Display Configuration
				</div>

				<div style="display:<?php echo $showOrHide; ?> ;" class="portlet-content" id='displayContentHide' >
					<table class="form-table">
						<tr>
							<th scope='row'>Player Width</th>
							<td><input type='text' name="width"
								value="<?php echo $fetchSettings->width ?>" size=45 />Note:
								Recommended width is 400. If you want your width less than 400,
								please disable few buttons in "Skin Configuration"</td>

						</tr>
						<tr>
							<th scope='row'>Player Height</th>
							<td><input type='text' name="height"
								value="<?php echo $fetchSettings->height ?>" size=45 /></td>
						</tr>
						<tr>
							<th scope='row'>Stagecolor</th>
							<td><input type='text' name="stagecolor"
								value="<?php echo $fetchSettings->stagecolor ?>" size=45 /> <span
								style="font-size: 9px; padding-left: 5px;">Ex : 0xFFFFFF </span>
							</td>
						</tr>
						<!--    <tr>
                                <th scope='row'><?php _e('Youtube Logo','digi'); ?></th>
                                <td>
                                <?php $logoflag =  get_option('youtubelogoshow'); ?>
                                	<input type="radio" name="logostatus" value="1" <?php ($logoflag == 1 )? printf("checked=\"checked\" ") : printf("");  ?>     title="Show logo"  /> <?php _e('Enable','digi'); ?>
                                	<input type="radio" name="logostatus" value="2" <?php ($logoflag == 2 )? printf("checked=\"checked\" ") : printf("");  ?>  title="Don't show logo" /> <?php _e('Disable','digi'); ?> 
                                </td>
                            </tr>
                           -->
					</table>
				</div>
			</div>
			<?php $showOrHide =  getDisplayValueOfDiv('SkinContentHide');
			if($showOrHide == 'block')
			{
				$className = 'ui-icon ui-icon-minusthick';
			}
			else{
				$className = 'ui-icon ui-icon-plusthick';
			}

			?>
			<div
				class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
				<div class="portlet-header ui-widget-header ui-corner-all">
					<span id='SkinSpan'
						onclick="hideContentDives('SkinContentHide','SkinSpan')"
						class="<?php echo $className; ?>"></span>Skin Configuration
				</div>
				<div style="display: <?php echo $showOrHide; ?>;" class="portlet-content" id='SkinContentHide' >
					<table class="form-table">
						<tr>
							<th scope='row'>Timer</th>
							<td><input type='checkbox' class='check'
							<?php if ($fetchSettings->timer == 1) { ?> checked <?php } ?>
								name="timer" value="1" size=45 /></td>
						</tr>
						<tr>
							<th scope='row'>Zoom</th>
							<td><input type='checkbox' class='check'
							<?php if ($fetchSettings->zoom == 1) { ?> checked <?php } ?>
								name="zoom" value="1" size=45 /></td>
						</tr>
						<tr>
							<th scope='row'>Share</th>
							<td><input type='checkbox' class='check'
							<?php if ($fetchSettings->email == 1) { ?> checked <?php } ?>
								name="email" value="1" size=45 /></td>
						</tr>
						<tr>
							<th scope='row'>Full Screen</th>
							<td><input type='checkbox' class='check'
							<?php if ($fetchSettings->fullscreen == 1) { ?> checked
							<?php } ?> name="fullscreen" value="1" size=45 /></td>
						</tr>
						<tr>
							<th scope='row'>Skin Autohide</th>
							<td><input type='checkbox' class='check'
							<?php if ($fetchSettings->skin_autohide == 1) { ?> checked
							<?php } ?> name="skin_autohide" value="1" size=45 /></td>
						</tr>
						<tr>
							<th scope='row'>Skin</th>
							<td><select name="skin" style="width: 150px;">
							<?php foreach ($skins as $skin) {
								?>
									<option <?php if ($fetchSettings->skin == $skin) { ?>
										selected="selected" <?php } ?> value="<?php echo $skin; ?>">
										<?php echo $skin; ?>
									</option>
									<?php } ?>
							</select>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<?php $showOrHide =  getDisplayValueOfDiv('VideoContentHide');
			if($showOrHide == 'block')
			{
				$className = 'ui-icon ui-icon-minusthick';
			}
			else{
				$className = 'ui-icon ui-icon-plusthick';
			}
			?>
			<div
				class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
				<div class="portlet-header ui-widget-header ui-corner-all">
					<span id='VideoSpan'
						onclick="hideContentDives('VideoContentHide','VideoSpan')"
						class="<?php  echo $className; ?>"></span>Video Configuration
				</div>
				<div style="display: <?php echo $showOrHide; ?>;" class="portlet-content" id='VideoContentHide' >


					<table class="form-table">
						<tr>
							<th scope='row'>Auto Play</th>
							<td><input type='checkbox' class='check' name="autoplay"
							<?php if ($fetchSettings->autoplay == 1) { ?> checked <?php } ?>
								value="1" size=45 /></td>
						</tr>
						<tr>
							<th scope='row'>Download</th>
							<td><input type='checkbox' class='check' name="download"
							<?php if ($fetchSettings->download == 1) { ?> checked <?php } ?>
								value="1" size=45 />&nbsp Note: Not supported for YouTube videos</td>
						</tr>
						<tr>
							<th scope='row'>Buffer</th>
							<td><input type='text' name="buffer"
								value="<?php echo $fetchSettings->buffer ?>" size=45 /></td>
						</tr>
						<tr>
							<th scope='row'>Volume</th>
							<td><input type='text' name="volume"
								value="<?php echo $fetchSettings->volume ?>" size=45 /></td>
						</tr>
						<tr>
							<th scope='row'>Embed visible</th>
							<td><input type='checkbox' class='check' name="embed_visible"
							<?php if ($fetchSettings->embed_visible == 1) { ?> checked
							<?php } ?> value="1" size=45 /></td>
						</tr>

					</table>
				</div>
			</div>
			<?php $showOrHide =  getDisplayValueOfDiv('LicenseContentHide');
			if($showOrHide == 'block')
			{
				$className = 'ui-icon ui-icon-minusthick';
			}
			else{
				$className = 'ui-icon ui-icon-plusthick';
			}
			?>
			<div
				class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
				<div class="portlet-header ui-widget-header ui-corner-all">
					<span id='LicenseSpan'
						onclick="hideContentDives('LicenseContentHide','LicenseSpan')"
						class="<?php echo $className; ?>"></span>License Configuration
				</div>
				<div style="display: <?php echo $showOrHide; ?>;" class="portlet-content" id='LicenseContentHide' >
					<table class="form-table">
						<tr>
							<th scope='row'>License Key</th>
							<td><input type='text' name="license"
								value="<?php echo $fetchSettings->license ?>" size=45 /></td>
						</tr>
					</table>
				</div>
			</div>


		</div>
		<!-- <div class="column" >lelf side div is end -->
		<div class="column">
		<?php $showOrHide =  getDisplayValueOfDiv('PlaylistContentHide');
		if($showOrHide == 'block')
		{
			$className = 'ui-icon ui-icon-minusthick';
		}
		else{
			$className = 'ui-icon ui-icon-plusthick';
		}
		?>
			<div
				class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
				<div class="portlet-header ui-widget-header ui-corner-all">
					<span id='PlaylistSpan'
						onclick="hideContentDives('PlaylistContentHide','PlaylistSpan')"
						class="<?php echo $className; ?>"></span>Playlist Configuration
				</div>
				<div style="display: <?php echo $showOrHide; ?>;" class="portlet-content" id='PlaylistContentHide' >

					<table class="form-table">
						<tr>
							<th scope='row'>Playlist Display</th>
							<td><input type='checkbox' class='check' name="playlist"
							<?php if ($fetchSettings->playlist == 1) { ?> checked <?php } ?>
								value="1" size=45 /></td>
						</tr>
						<tr>
							<th scope='row'>HD Default</th>
							<td><input type='checkbox' class='check' name="HD_default"
							<?php if ($fetchSettings->HD_default == 1) { ?> checked
							<?php } ?> value="1" size=45 /></td>
						</tr>
						<tr>
							<th scope='row'>Playlist Autoplay</th>
							<td><input type='checkbox' class='check'
							<?php if ($fetchSettings->playlistauto == 1) { ?> checked
							<?php } ?> name="playlistauto" value="1" size=45 /></td>
						</tr>
					</table>
				</div>
			</div>
			<?php $showOrHide =  getDisplayValueOfDiv('GeneralContentHide');
			if($showOrHide == 'block')
			{
				$className = 'ui-icon ui-icon-minusthick';
			}
			else{
				$className = 'ui-icon ui-icon-plusthick';
			}
			?>
			<div
				class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
				<div class="portlet-header ui-widget-header ui-corner-all">
					<span id='GeneralSpan'
						onclick="hideContentDives('GeneralContentHide','GeneralSpan')"
						class="<?php echo $className; ?>"></span>General Configuration
				</div>
				<div style="display: <?php echo $showOrHide; ?>;" class="portlet-content" id='GeneralContentHide' >

					<table class="form-table">
						<tr>
							<th scope='row'>Normal Scale</th>
							<td><select name="normalscale" style="width: 150px;">
									<option value="0"
									<?php if ($fetchSettings->normalscale == 0) { ?>
										selected="selected" <?php } ?>>Aspect Ratio</option>
									<option value="1"
									<?php if ($fetchSettings->normalscale == 1) { ?>
										selected="selected" <?php } ?>>Original Screen</option>
									<option value="2"
									<?php if ($fetchSettings->normalscale == 2) { ?>
										selected="selected" <?php } ?>>Fit To Screen</option>
							</select>
							</td>
						</tr>
						<tr>
							<th scope='row'>Full Screen Scale</th>
							<td><select name="fullscreenscale" style="width: 150px;">
									<option value="0"
									<?php if ($fetchSettings->fullscreenscale == 0) { ?>
										selected="selected" <?php } ?>>Aspect Ratio</option>
									<option value="1"
									<?php if ($fetchSettings->fullscreenscale == 1) { ?>
										selected="selected" <?php } ?>>Original Screen</option>
									<option value="2"
									<?php if ($fetchSettings->fullscreenscale == 2) { ?>
										selected="selected" <?php } ?>>Fit To Screen</option>
							</select>
							</td>
						</tr>
						<tr>
							<th scope='row'>Uploads</th>
							<td>
								<!--                    <label><input name="usedefault" type='radio' value="1" <?php if ($setting['v'] == 1)
        echo 'checked'; ?> /> <?php _e('Standard upload folder : ', 'hdflv') ?></label><code><?php echo get_option('upload_path'); ?></code><br />-->
								<label><input name="usedefault" type='radio' value="0"
								<?php if ($setting['v'] == 0)
								echo 'checked'; ?> /> <?php _e('Store uploads in this folder : ', 'hdflv') ?>
							</label> <input type="text" size="35" maxlength="200"
								name='uploadurl'
								value="<?php echo $fetchSettings->upload_path ?>" />
							</td>
						</tr>

						<tr>
							<th scope='row'>Debug</th>
							<td><input type='checkbox' class='check'
							<?php if ($fetchSettings->debug == 1) { ?> checked <?php } ?>
								name="debug" value="1" size=45 /></td>
						</tr>


					</table>
				</div>
			</div>

			<?php $showOrHide =  getDisplayValueOfDiv('LogoContentHide');
			if($showOrHide == 'block')
			{
				$className = 'ui-icon ui-icon-minusthick';
			}
			else{
				$className = 'ui-icon ui-icon-plusthick';
			}
			?>
			<div
				class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
				<div class="portlet-header ui-widget-header ui-corner-all">
					<span id='LogoSpan'
						onclick="hideContentDives('LogoContentHide','LogoSpan')"
						class="<?php echo $className; ?>"></span>Logo Configuration
					(Applicable Only For Licensed Player)
					<?php if($fetchSettings->license == '' || $fetchSettings->license == '0'){?>
					<a href="http://www.apptha.com/shop/checkout/cart/add/product/20"
						target="_blank"
						style="text-decoration: none; color: red; cursor: pointer;">Buy
						Now</a>
						<?php }?>
				</div>
				<div style="display: <?php echo $showOrHide; ?>;" class="portlet-content" id='LogoContentHide' >


					<table class="form-table">
						<tr>
							<th scope='row'>Logo Path</th>
							<td><input type='file' name="logopath" value="" size=35 /> <?php echo $fetchSettings->logopath ?>
							</td>
						</tr>
						<tr>
							<th scope='row'>Logo Target</th>
							<td><input type='text' name="logotarget"
								value="<?php echo $fetchSettings->logo_target ?>" size=45 /></td>
						</tr>
						<tr>
							<th scope='row'>Logo Align</th>
							<td><select name="logoalign" style="width: 150px;">
									<option <?php if ($fetchSettings->logoalign == 'TL') { ?>
										selected="selected" <?php } ?> value="TL">Top Left</option>
									<option <?php if ($fetchSettings->logoalign == 'TR') { ?>
										selected="selected" <?php } ?> value="TR">Top Right</option>
									<option <?php if ($fetchSettings->logoalign == 'LB') { ?>
										selected="selected" <?php } ?> value="LB">Left Bottom</option>
									<option <?php if ($fetchSettings->logoalign == 'RB') { ?>
										selected="selected" <?php } ?> value="RB">Right Bottom</option>
							</select></td>
						</tr>
						<tr>
							<th scope='row'>Logo Alpha</th>
							<td><input type='text' name="logoalpha"
								value="<?php echo $fetchSettings->logoalpha ?>" size=45 /></td>
						</tr>
						<tr>
							<th scope='row'>Hide You Tube Logo</th>
							<td><input type='checkbox' class='check' name="display_logo"
							<?php if ($fetchSettings->display_logo == 1) { ?> checked
							<?php } ?> value="1" size=45 /></td>
						</tr>
					</table>
				</div>
			</div>

		</div>
		<div class="clear"></div>
		<p class='submit'>
			<input class='button-primary' type='submit' value='Update Options'>
		</p>

	</form>
</div>

<!-- End of settings design-->
<?php
}

function HDFLV_head() {
	global $siteUrl;
	echo '<script type="text/javascript" src="' . $siteUrl . '/wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/js/swfobject.js"></script>' . "\n";
}

add_action('wp_head', 'HDFLV_head');

/* Loading default settings of player */


/* Function to uninstall player plugin */
function contusHdDeinstall() {

	global $wpdb, $wp_version;
	require_once(dirname(__FILE__) . '/install.php');
	hdflvDropTables();

}

/* Function to invoke install player plugin */
function contusHdInstall() {
	require_once(dirname(__FILE__) . '/install.php');
	contusHdInstalling();
}
/* Function to deactivate player plugin */
function contusHdDeactive() {
	delete_option('HDFLVSettings');
}

add_action('plugins_loaded', 'contusHdInstall'); //when the version is updating that time this hook will execute
register_activation_hook(plugin_basename(dirname(__FILE__)) . '/hdflvplugin.php', 'contusHdInstall'); //activation
register_uninstall_hook(__FILE__, 'contusHdDeinstall'); //delete plugin .
register_deactivation_hook(__FILE__, 'contusHdDeactive'); //deactivation plugin

// CONTENT FILTER
add_filter('the_content', 'HDFLV_Parse');

// OPTIONS MENU
add_action('admin_menu', 'hdflvMenuCreate');
?>