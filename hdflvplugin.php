<?php
/*
  Plugin Name: Contus HD FLV Player
  Plugin URI: http://www.apptha.com/category/extension/Wordpress/HD-FLV-Player-Plugin/
  Description: Contus HD FLV Player simplifies the process of adding high definition videos to the Wordpress blog. The plugin efficiently plays your Videos with high quality video and audio output.
  Version: 2.6
  Author: Apptha
  Author URI: http://www.apptha.com
  License: GPL2
 */
session_start();
$videoid = 0;
$site_url = $siteUrl = get_option('siteurl');
define('APPTHA_HDFLV_BASEURL', plugin_dir_url(__FILE__));
$dir = dirname(plugin_basename(__FILE__));
$dirExp = explode('/', $dir);
$dirPage = $dirExp[0];

function HDFLV_Parse($content) {
    $content = preg_replace_callback('/\[hdpla ([^]]*)\]/i', 'hdflvPlayerReader', $content);
    return $content;
}

//Used for Rendering player with the configured informations and save configurations from admin
function hdflvPlayerReader($arguments= array()) {

    global $wpdb, $videoid, $siteUrl, $dirPage, $site_url;

    $configXML = $wpdb->get_row("SELECT width,height FROM " . $wpdb->prefix . "hdflv_settings");
    if ($arguments['width'] != '') {
        $width = $arguments['width'];
    } else {
        $width = $configXML->width;
    }
    if ($arguments['height'] != '') {
        $height = $arguments['height'];
    } else {
        $height = $configXML->height;
    }

    if (isset($arguments['id'])) {
        $videoid1=(int) $arguments['id'];
        $videofiles = $wpdb->get_row("SELECT vid,file,hdfile,image,name FROM " . $wpdb->prefix . "hdflv where vid = " . intval($arguments['id']));
        $file = $videofiles->file;

        $videofile = $videofiles->hdfile;
        $imagefile = $videofiles->image;
        $videoName = $videofiles->name;
    } elseif (isset($arguments['playlistid'])) {
        $playlist_id = intval($arguments['playlistid']);
        $playlist = $wpdb->get_row("SELECT w.* FROM " . $wpdb->prefix . "hdflv w  INNER JOIN " . $wpdb->prefix . "hdflv_med2play m  WHERE (m.playlist_id = '$playlist_id') AND m.media_id = w.vid");
        if ($playlist) {
            $select = " SELECT w.* FROM " . $wpdb->prefix . "hdflv w";
            $select .= " INNER JOIN " . $wpdb->prefix . "hdflv_med2play m";
            $select .= " WHERE (m.playlist_id = '$playlist_id'";
            $select .= " AND m.media_id = w.vid) GROUP BY w.vid ";
            $select .= " ORDER BY m.sorder ASC , m.porder " . $playlist->playlist_order . " ,w.vid " . $playlist->playlist_order . " limit 0,1";
            //echo $select;
            $videofiles = $wpdb->get_results($wpdb->prepare($select, NULL));
            $playlistName = $wpdb->get_row("SELECT w . name FROM wp_hdflv w INNER JOIN wp_hdflv_med2play m WHERE (m.playlist_id = '$playlist_id')  AND m.media_id = w.vid");
            $playName = $playlistName->name;
            $file = $videofiles->file;
            $videofile = $videofiles->hdfile;
            $imagefile = $videofiles->image;
//            $videoId = $videofiles->vid;
        }
    }
?>
    <script type="text/javascript">
        function current_video(video_id,d_title){

            if(d_title == undefined)
            {
                document.getElementById('default_title'+<?php echo $videoid .$videoid1; ?>).innerHTML='';
             }
            else {
                document.getElementById('default_title'+<?php echo $videoid .$videoid1; ?>).innerHTML=d_title;
            }
        }
    </script>

<?php
    if (!isset($arguments['id'])) {
        $output .='<h3 id="default_title' . $videoid .$videoid1. '" align="left" style="min-height:30px">' . $playName . '</h3>';
    } else {
        $output .='<h3 id="default_title' . $videoid .$videoid1. '" align="left" style="min-height:30px">' . $videofiles->name . '</h3>';
    }
    $output .= '<div id="video' . $videoid .$videoid1. '" class="HDFLV">';

    $play_url = get_option('siteurl');
    if (isset($arguments['playlistid']) && isset($arguments['id'])) {
        $play_url .= "&pid=" . (int) $arguments['playlistid'];
        $play_url .= "&vid=" . (int) $arguments['id'];
    } elseif (isset($arguments['playlistid'])) {
        $play_url .= "&pid=" . (int) $arguments['playlistid'];
    } else {
        $play_url .= "&vid=" . (int) $arguments['id'];
    }
if(isset($arguments['flashvars'])){
        $play_url .= "&" . $arguments['flashvars'];
    }
    $playerpath = $siteUrl . '/wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/hdflvplayer/hdplayer.swf';
    $output .= '<embed wmode="opaque" src="' . $playerpath . '"
               type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true"
               flashvars="baserefW=' . $play_url . '"
               width="' . $width . '" height="' . $height . '"></embed> </div>';
    $output .= '<div id="htmlplayer' . $videoid .$videoid1. '" style="display: none;">';
    if (preg_match('/www\.youtube\.com\/watch\?v=[^&]+/', $file, $vresult)) {

        $urlArray = split("=", $vresult[0]);
        $videourl = trim($urlArray[1]);
        $output .= '<iframe width="' . $width . '" height="' . $height . '"  src="http://www.youtube.com/embed/' . $videourl . '" frameborder="0" allowfullscreen>
            </iframe>';
    } else {
        $output .= ' <video id="video" src="' . $file . '" poster="' . $imagefile . '" width="' . $width . '" height="' . $height . '" autobuffer controls onerror="failed(event)">
     Html5 Not support This video Format.</video>';
    }

    $output .= ' </div><script>var txt =  navigator.platform ;if(txt =="iPod"|| txt =="iPad"|| txt =="iPhone" || txt =="Linux armv7I")
            {   document.getElementById("htmlplayer' . $videoid .$videoid1. '").style.display = "block";
                document.getElementById("video' . $videoid .$videoid1. '").style.display = "none";
            }else{
 	 document.getElementById("htmlplayer' . $videoid .$videoid1. '").style.display = "none";
            }
			 function failed(e) {
			  if(txt =="iPod"|| txt =="iPad"|| txt =="iPhone" || txt =="Linux armv7I")
            {
     alert("Player doesnot support this video.");
   }
}
        </script>';
$videoid++;
    return $output;
}

add_shortcode('hdplay', 'hdflvPlayerReader'); //Shortcode tag[hdplay]] to be searched in post content


/* Adding page & options */

function hdflvMenuCreate() {

    add_menu_page(__('hdflv', 'hdflv'), __('HD FLV Player', 'hdflv'), 2, "hdflv", "showMenu", get_bloginfo('url') . "/wp-content/plugins/" . dirname(plugin_basename(__FILE__)) . "/images/apptha.png");
    add_submenu_page("hdflv", __('HDFLV Videos', 'hdflv'), __('Videos', 'hdflv'), 4, "hdflv", "showMenu");
    add_submenu_page("hdflv", "HDFLV Options", "Playlist", 4, "hdflvplaylist", "showMenu");
    add_submenu_page("hdflv", "HDFLV Options", "Settings", 4, "hdflvplugin.php", "FlashOptions");
}

function showMenu() {      // HDFLV Videos submenu coding in manage.php file
    $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING);
    switch ($page) {
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

/**
 * Email function
 */
add_action( 'wp_ajax_email', 'email_function' );
add_action( 'wp_ajax_nopriv_email', 'email_function' );

function email_function() {
	require_once( dirname( __FILE__ ) . '/email.php' );
	die();
}

/*  function use to set  div is hide or show in settings tab */

function getDisplayValueOfDiv($divId) {
    if (!get_option($divId)) {
        $display = 'block';
        $viewStymolId = 'ui-icon ui-icon-minusthick';
    } else {
        $display = 'none';
        $viewStymolId = 'ui-icon ui-icon-plusthick';
    }
    return $display;
}

//function end hear

/* Function used to Edit player settings and generate settings form elements */

function FlashOptions() {
    global $wpdb;
    global $siteUrl;
    $message = '';
    $g = array(0 => 'Properties');

    $youtubelogshow = filter_input(INPUT_POST, 'logostatus', FILTER_SANITIZE_STRING);
    if (isset($youtubelogshow)) {

        update_option('youtubelogoshow', $youtubelogshow);
    }

    $options = get_option('HDFLVSettings');

    if ($_POST) {
        $settings = $wpdb->get_col("SELECT * FROM " . $wpdb->prefix . "hdflv_settings");
        $logoUpload = '';
        if (count($settings) > 0) {
            $autoplay = filter_input(INPUT_POST, 'autoplay', FILTER_SANITIZE_STRING);
            $playlist = filter_input(INPUT_POST, 'playlist', FILTER_SANITIZE_STRING);
            $playlistauto = filter_input(INPUT_POST, 'playlistauto', FILTER_SANITIZE_STRING);
            $buffer = filter_input(INPUT_POST, 'buffer', FILTER_SANITIZE_STRING);
            $normalscale = filter_input(INPUT_POST, 'normalscale', FILTER_SANITIZE_STRING);
            $fullscreenscale = filter_input(INPUT_POST, 'fullscreenscale', FILTER_SANITIZE_STRING);
            $volume = filter_input(INPUT_POST, 'volume', FILTER_SANITIZE_STRING);
            $logoalign = filter_input(INPUT_POST, 'logoalign', FILTER_SANITIZE_STRING);
            $logotarget = filter_input(INPUT_POST, 'logotarget', FILTER_SANITIZE_STRING);
            $hdflvplayer_ads = filter_input(INPUT_POST, 'hdflvplayer_ads', FILTER_SANITIZE_STRING);
            $HD_default = filter_input(INPUT_POST, 'HD_default', FILTER_SANITIZE_STRING);
            $download = filter_input(INPUT_POST, 'download', FILTER_SANITIZE_STRING);
            $logoalpha = filter_input(INPUT_POST, 'logoalpha', FILTER_SANITIZE_STRING);
            $skin_autohide = filter_input(INPUT_POST, 'skin_autohide', FILTER_SANITIZE_STRING);
            $stagecolor = filter_input(INPUT_POST, 'stagecolor', FILTER_SANITIZE_STRING);
            $skin = filter_input(INPUT_POST, 'skin', FILTER_SANITIZE_STRING);
            $embed_visible = filter_input(INPUT_POST, 'embed_visible', FILTER_SANITIZE_STRING);
            $shareURL = filter_input(INPUT_POST, 'shareURL', FILTER_SANITIZE_STRING);
            $playlistXML = filter_input(INPUT_POST, 'playlistXML', FILTER_SANITIZE_STRING);
            $debug = filter_input(INPUT_POST, 'debug', FILTER_SANITIZE_STRING);
            $timer = filter_input(INPUT_POST, 'timer', FILTER_SANITIZE_STRING);
            $zoom = filter_input(INPUT_POST, 'zoom', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_STRING);
            $fullscreen = filter_input(INPUT_POST, 'fullscreen', FILTER_SANITIZE_STRING);
            $width = (int) filter_input(INPUT_POST, 'width', FILTER_SANITIZE_STRING);
            $height = (int) filter_input(INPUT_POST, 'height', FILTER_SANITIZE_STRING);
            $display_logo = filter_input(INPUT_POST, 'display_logo', FILTER_SANITIZE_STRING);
            $license = filter_input(INPUT_POST, 'license', FILTER_SANITIZE_STRING);
            $ima_ads = filter_input(INPUT_POST, 'ima_ads', FILTER_SANITIZE_STRING);
            $ima_ads_xml = filter_input(INPUT_POST, 'ima_ads_xml');
            $google_tracker = filter_input(INPUT_POST, 'google_tracker');

            $query = " UPDATE " . $wpdb->prefix . "hdflv_settings SET
			autoplay= '" . $autoplay . "',playlist='" . $playlist . "',playlistauto='" . $playlistauto
                    . "',buffer='" . $buffer . "',normalscale='" . $normalscale . "',fullscreenscale='" . $fullscreenscale . "'";
            if ($_FILES['logopath']["name"] != '') {
                $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');
                $logoImage = strtolower($_FILES['logopath']["name"]);
                if (in_array(end(explode(".", $logoImage)), $allowedExtensions)) {
                    $logoUpload = true;
                    $query .= ",logopath='" . $_FILES['logopath']["name"] . "'";
                } else {
                    $logoUpload = false;
                    $message = "<div class='error' id='error'><p><strong>Invalid File Type Uploaded!</strong></p></div>";
                }
            }
            $query .= ",volume='" . $volume . "',logoalign='" . $logoalign . "',logo_target='" . $logotarget . "',hdflvplayer_ads='" . $hdflvplayer_ads
                    . "',HD_default='" . $HD_default . "',download='" . $download . "',logoalpha='" . $logoalpha . "',skin_autohide='" . $skin_autohide
                    . "',stagecolor='" . $stagecolor . "',skin='" . $skin . "',embed_visible='" . $embed_visible . "',shareURL='" . $shareURL
                    . "',playlistXML='" . $playlistXML . "',debug='" . $debug . "',timer='" . $timer . "',zoom='" . $zoom
                    . "',email='" . $email . "',fullscreen='" . $fullscreen . "',width='" . $width . "',height='" . $height
                    . "',display_logo='" . $display_logo . "',license='" . trim($license)
                    . "',ima_ads='" . $ima_ads . "',ima_ads_xml='" . $ima_ads_xml . "',google_tracker='" . $google_tracker
                    . "' WHERE settings_id = " . $settings[0]['settings_id'];
            $updateSettings = $wpdb->query($query);
        } else {
            require_once(dirname(__FILE__) . '/install.php');
            contusHdInstalling();
            $insertSettings = $wpdb->query(" INSERT INTO " . $wpdb->prefix . "hdflv_settings
						VALUES ('','" . $autoplay . "','" . $playlist . "','" . $playlistauto . "','" . $buffer
                            . "','" . $normalscale . "','" . $fullscreenscale . "','','http://www.hdflvplayer.net/','" . $volume . "','" . $logoalign . "','" . $hdflvplayer_ads . "','" . $HD_default
                            . "','" . $download . "','" . $logoalpha . "','" . $skin_autohide . "','" . $stagecolor
                            . "','" . $skin . "','" . $embed_visible . "','" . $shareURL . "','" . $playlistXML
                            . "','" . $debug . "','" . $timer . "','" . $zoom . "','" . $email
                            . "','" . $fullscreen . "','" . $width . "','" . $height . "','" . $display_logo . "','" . trim($license) . "','','wp-content/uploads','0','','')");
        }
        if ($logoUpload == '1' || $_FILES['logopath']["name"] == '') {
            if ($_FILES['logopath']["name"] != '') {
                move_uploaded_file($_FILES["logopath"]["tmp_name"], dirname(__FILE__) . "/hdflvplayer/css/images/" . $_FILES["logopath"]["name"]);
            }
            $message = '<div class="updated"><p><strong>Options saved</strong></p></div>';
        }
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
    <link rel="stylesheet" href="<?php echo $siteUrl ?>/wp-content/plugins/<?php echo dirname(plugin_basename(__FILE__)) ?>/hdflvplayer/css/jquery.ui.all.css">
    <script type="text/javascript" src="../wp-content/plugins/<?php echo $contus ?>/js/hdflvscript.js"></script>


    <h2 style="margin-bottom: 1%;" class="nav-tab-wrapper">
        <a id="hdflv" href="?page=hdflv" class="nav-tab "> Manage Videos</a> <a
            id="video" href="?page=hdflv&mode=video" class="nav-tab"> Add Video</a>
        <a id="playlist" href="?page=hdflvplaylist" class="nav-tab">Playlists</a>
        <a id="settings" href="?page=hdflvplugin.php" class="nav-tab">Settings</a>
    </h2>
    <script type="text/javascript">
        document.getElementById("settings").className = 'nav-tab nav-tab-active';
    </script>
    <div class="wrap">
        <h2>HD FLV Player Options</h2>

        <form method="post" enctype="multipart/form-data"	action="admin.php?page=hdflvplugin.php">

            <p class='submit'>
                <input type="hidden" name="plugin_name" id="plugin_name" value="<?php echo $contus; ?>" />
                <input type="hidden" name="app_wp_token" id="app_wp_token" value="<?php echo $_SESSION['app_wp_token']; ?>" />
                <input class='button-primary' type='submit' value='Update Options'>
            </p>
            <div class="column column1" style="float: left;">
            <?php
               $showOrHide = getDisplayValueOfDiv('LicenseContentHide');
               if ($showOrHide == 'block') {
                   $className = 'ui-icon ui-icon-minusthick';
               } else {
                   $className = 'ui-icon ui-icon-plusthick';
               }
            ?>
            <div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
               <div class="portlet-header ui-widget-header ui-corner-all">
                   <span id='LicenseSpan' onclick="hideContentDives('LicenseContentHide','LicenseSpan')" class="<?php echo $className; ?>"></span>
                   License Configuration
               </div>
               <div style="display: <?php echo $showOrHide; ?>;" class="portlet-content" id='LicenseContentHide' >
                   <table class="form-table">
                       <tr>
                           <th scope='row'>License Key</th>
                           <td>
                               <input type='text' name="license" value="<?php echo $fetchSettings->license ?>" size=45 />
                                <?php if ($fetchSettings->license == '' || $fetchSettings->license == '0') {?>
                                   <a href="http://www.apptha.com/shop/checkout/cart/add/product/20" target="_blank" style="margin-top: 10px;display: inline-block;">
                                       <img src="<?php  echo APPTHA_HDFLV_BASEURL; ?>/images/buy.gif" alt="Buy"/>
                                   </a>
                                <?php } ?>
                           </td>
                       </tr>
                   </table>
               </div>
            </div>
            <?php
                $showOrHide = getDisplayValueOfDiv('LogoContentHide');
                if ($showOrHide == 'block') {
                   $className = 'ui-icon ui-icon-minusthick';
                } else {
                   $className = 'ui-icon ui-icon-plusthick';
                }
            ?>
                                   <div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
                                           <div class="portlet-header ui-widget-header ui-corner-all">
                                               <span id='LogoSpan'
                                                     onclick="hideContentDives('LogoContentHide','LogoSpan')"
                                                     class="<?php echo $className; ?>"></span>Logo Configuration
                           					(Applicable Only For Licensed Player)
                                                    
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
                                                       <option <?php if ($fetchSettings->logoalign == 'BL') { ?>
                                                               selected="selected" <?php } ?> value="BL">Bottom Left</option>
                                                       <option <?php if ($fetchSettings->logoalign == 'BR') { ?>
                                                               selected="selected" <?php } ?> value="BR">Bottom Right</option>
                                                   </select></td>
                                           </tr>
                                           <tr>
                                               <th scope='row'>Logo Alpha</th>
                                               <td><input type='text' name="logoalpha"
                                                          value="<?php echo $fetchSettings->logoalpha ?>" size=45 /></td>
                                           </tr>
                                           <tr>
                                               <th scope='row'>Hide Youtube Logo</th>
                                               <td><input type='checkbox' class='check' name="display_logo"
                                                    <?php if ($fetchSettings->display_logo == 1) { ?> checked
                                                    <?php } ?> value="1" size=45 />
                                               </td>
                                           </tr>
                                       </table>
                                   </div>
                               </div>

<?php
    $showOrHide = getDisplayValueOfDiv('displayContentHide');
    if ($showOrHide == 'block') {
        $className = 'ui-icon ui-icon-minusthick';
    } else {
        $className = 'ui-icon ui-icon-plusthick';
    }
?>


            <div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
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
								Recommended width is 400. If you want use player width less than 400,
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
                                       style="font-size: 9px; padding-left: 5px; display: block;">Ex : 0xFFFFFF </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

                <?php
                                       $showOrHide = getDisplayValueOfDiv('PlaylistContentHide');
                                       if ($showOrHide == 'block') {
                                           $className = 'ui-icon ui-icon-minusthick';
                                       } else {
                                           $className = 'ui-icon ui-icon-plusthick';
                                       }
?>
                                       <div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
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
                                                                      value="1" size=45 />
                                                           </td>
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
                                                            <?php } ?> name="playlistauto" value="1" size=45 />
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                       
                                   </div>
                                   <!-- <div class="column" >lelf side div is end -->
                                   <div class="column column2" >





<?php
                                       $showOrHide = getDisplayValueOfDiv('VideoContentHide');
                                       if ($showOrHide == 'block') {
                                           $className = 'ui-icon ui-icon-minusthick';
                                       } else {
                                           $className = 'ui-icon ui-icon-plusthick';
                                       }
?>
                                       <div
                                           class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
                                           <div class="portlet-header ui-widget-header ui-corner-all">
                                               <span id='VideoSpan'
                                                     onclick="hideContentDives('VideoContentHide','VideoSpan')"
                                                     class="<?php echo $className; ?>"></span>Video Configuration
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
                                                                       <?php if ($fetchSettings->download == 1) {
                                 ?> checked <?php } ?>
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




                                <?php
                                       $showOrHide = getDisplayValueOfDiv('GeneralContentHide');
                                       if ($showOrHide == 'block') {
                                           $className = 'ui-icon ui-icon-minusthick';
                                       } else {
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
                               <th scope='row'>Debug</th>
                               <td><input type='checkbox' class='check'
<?php if ($fetchSettings->debug == 1) { ?> checked <?php } ?>
                                          name="debug" value="1" size=45 /></td>
                           </tr>


                           <tr>
                               <th scope='row'>IMA Ads</th>
                               <td><input type='checkbox' class='check'
                                       <?php if ($fetchSettings->ima_ads == 1) {
 ?> checked <?php } ?>
                                       name="ima_ads" value="1" size=45 /></td>
                        </tr>
                        <tr>
                            <th scope='row'>IMA Ads XML</th>
                            <td><input type='textbox' value="<?php echo $fetchSettings->ima_ads_xml; ?>" name="ima_ads_xml"  /></td>
                        </tr>

                        <tr>
                            <th scope='row'>Google Tracker</th>
                            <td><input type='textbox' value="<?php echo $fetchSettings->google_tracker; ?>" name="google_tracker"  /></td>
                        </tr>
                    </table>
                </div>
            </div>


<?php
            $showOrHide = getDisplayValueOfDiv('SkinContentHide');
            if ($showOrHide == 'block') {
                $className = 'ui-icon ui-icon-minusthick';
            } else {
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
                                       <?php if ($fetchSettings->skin_autohide == 1) {
 ?> checked
<?php } ?> name="skin_autohide" value="1" size=45 /></td>
                        </tr>
                        <tr>
                            <th scope='row'>Skin</th>
                            <td><select name="skin" style="width: 150px;">
<?php foreach ($skins as $skin) { ?>
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
    function hdflv_cssJs() {//function for adding css and javascript files starts
    wp_register_style('hdflv_css', plugins_url('css/hdflvsettings.css', __FILE__));
    wp_enqueue_style('hdflv_css');
}

add_action('admin_init', 'hdflv_cssJs');

                                   add_action('plugins_loaded', 'contusHdInstall'); //when the version is updating that time this hook will execute
                                   register_activation_hook(plugin_basename(dirname(__FILE__)) . '/hdflvplugin.php', 'contusHdInstall'); //activation
                                   register_uninstall_hook(__FILE__, 'contusHdDeinstall'); //delete plugin .
                                   register_deactivation_hook(__FILE__, 'contusHdDeactive'); //deactivation plugin
// CONTENT FILTER
                                   add_filter('the_content', 'HDFLV_Parse');

// OPTIONS MENU
                                   add_action('admin_menu', 'hdflvMenuCreate');
?>