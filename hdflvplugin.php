<?php
/*
 * Plugin Name: Contus HDFLVPlayer Plugin
 * Version: 1.3
 * Author: Contus Support.
 * Plugin URI: http://www.hdflvplayer.net/wordpress/
 * Author URI: http://www.hdflvplayer.net/wordpress/
 * Description: Simplifies the process of adding video to a WordPress blog. Powered by Contus Support HDFLVPlayer and SWFObject.
 * Path :wp-content\plugins\contus-hd-flv-player\hdflvplugin.php
 * Edited by : john thomas
 * date:13/1/11
 * Purpose : Main plugin file to configure
 */
$videoid = 0;
$site_url = get_option('siteurl');

function HDFLV_Parse($content) {
    $content = preg_replace_callback('/\[hdpla ([^]]*)\]/i', 'HDFLV_Render', $content);
    return $content;
}

//Used for Rendering player with the configured informations and save configurations from admin
function HDFLV_Render($arguments= array()) {


    global $wpdb;

    global $videoid, $site_url;

    $configXML = $wpdb->get_row("SELECT configXML,width,height FROM " . $wpdb->prefix . "hdflv_settings");
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
	$videofiles = $wpdb->get_row("SELECT file,hdfile,image FROM " . $wpdb->prefix . "hdflv where vid = ".$arguments['id']);
    $file = $videofiles->file;

	$videofile = $videofiles->hdfile;
	$imagefile = $videofiles->image;
	}
	elseif (isset($arguments['playlistid'])) {
	 $playlist = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "hdflv_playlist WHERE pid = '$playlist_id'");
    if ($playlist) {
        $select = " SELECT w.* FROM " . $wpdb->prefix . "hdflv w";
        $select .= " INNER JOIN " . $wpdb->prefix . "hdflv_med2play m";
        $select .= " WHERE (m.playlist_id = '$playlist_id'";
        $select .= " AND m.media_id = w.vid) GROUP BY w.vid ";
        $select .= " ORDER BY m.sorder ASC , m.porder " . $playlist->playlist_order . " ,w.vid " . $playlist->playlist_order . "limit 0,1";
		echo $select;
        $videofiles = $wpdb->get_results($wpdb->prepare($select));

    $file = $videofiles->file;

	$videofile = $videofiles->hdfile;
	$imagefile = $videofiles->image;
	}
	}
    $output .= "\n" . '<span id="video' . $videoid . '" class="HDFLV">' . "\n";
    $output .= '<a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see this player.</span>' . "\n";
    $output .= '<script type="text/javascript">' . "\n";
    $output .= 'var s' . $videoid . ' = new SWFObject("' . $site_url . '/wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/hdflvplayer/hdplayer.swf' . '","n' . $videoid . '","' . $width . '","' . $height . '","7");' . "\n";
    $output .= 's' . $videoid . '.addParam("allowfullscreen","true");' . "\n";
    $output .= 's' . $videoid . '.addParam("allowscriptaccess","always");' . "\n";
    $output .= 's' . $videoid . '.addParam("wmode","opaque");' . "\n";

    $output .= 's' . $videoid . '.addVariable("baserefW","' . get_option('siteurl') . '");';
    if (isset($arguments['playlistid']) && isset($arguments['id'])) {
        $output .= 's' . $videoid . '.addVariable("pid","' . $arguments['playlistid'] . '");' . "\n";
        $output .= 's' . $videoid . '.addVariable("vid","' . $arguments['id'] . '");' . "\n";
    } elseif (isset($arguments['playlistid'])) {
        $output .= 's' . $videoid . '.addVariable("pid","' . $arguments['playlistid'] . '");' . "\n";
    } else {
        $output .= 's' . $videoid . '.addVariable("vid","' . $arguments['id'] . '");' . "\n";
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
$output .= ' <video id="video" src="'.$videofile.'" poster="'.$imagefile.'" width="'.$width.'" height="' . $height . '" autobuffer controls onerror="failed(event)">
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

add_shortcode('hdplay', 'HDFLV_Render');


/* Adding page & options */

function HDFLVAddPage() {
    add_media_page(__('hdflv', 'hdflv'), __('HDFLVPlayer', 'hdflv'), 'edit_posts', 'hdflv', 'show_menu');

    add_options_page('HDFLVPlayer Options', 'HDFLVPlayer Options', '8', 'hdflvplugin.php', 'FlashOptions');
}

function show_menu() {
    switch ($_GET['page']) {
        case 'hdflv' :

            include_once (dirname(__FILE__) . '/functions.php'); // admin functions
            include_once (dirname(__FILE__) . '/manage.php');
            $MediaCenter = new HDFLVManage();
            break;
    }
}

/* Function used to Edit player settings and generate settings form elements */

function FlashOptions() {
    global $wpdb;
    global $site_url;
    $message = '';
    $g = array(0 => 'Properties');

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
                            . "',display_logo='" . $_POST['display_logo']. "',license='" . $_POST['license']. "',upload_path='" . $_POST['uploadurl']
                            . "' WHERE settings_id = " . $settings[0]['settings_id'];
            $updateSettings = $wpdb->query($query);
        } else {
            require_once(dirname(__FILE__) . '/install.php');
            hdflv_install();
            HDFLVLoadDefaults();
            $insertSettings = $wpdb->query(" INSERT INTO " . $wpdb->prefix . "hdflv_settings
						VALUES ('','" . $_POST['autoplay'] . "','" . $_POST['playlist'] . "','" . $_POST['playlistauto'] . "','" . $_POST['buffer']
                            . "','" . $_POST['normalscale'] . "','" . $_POST['fullscreenscale'] . "','','http://www.hdflvplayer.net/','" . $_POST['volume'] . "','" . $_POST['logoalign'] . "','" . $_POST['hdflvplayer_ads'] . "','" . $_POST['HD_default']
                            . "','" . $_POST['download'] . "','" . $_POST['logoalpha'] . "','" . $_POST['skin_autohide'] . "','" . $_POST['stagecolor']
                            . "','" . $_POST['skin'] . "','" . $_POST['embed_visible'] . "','" . $_POST['shareURL'] . "','" . $_POST['playlistXML']
                            . "','" . $_POST['debug'] . "','" . $_POST['timer'] . "','" . $_POST['zoom'] . "','" . $_POST['email']
                            . "','" . $_POST['fullscreen'] . "','" . $_POST['width'] . "','" . $_POST['height'] . "','" . $_POST['display_logo'] ."','" . $_POST['license']."','','wp-content/uploads')");
        }
        move_uploaded_file($_FILES["logopath"]["tmp_name"], "../wp-content/plugins/" . dirname(plugin_basename(__FILE__)) . "/hdflvplayer/images/" . $_FILES["logopath"]["name"]);
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

    $fetchSettings = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "hdflv_settings");
?>

    <!--HTML design for admin settings -->
    <link rel="stylesheet" href="<?php echo $site_url ?>/wp-content/plugins/<?php echo dirname(plugin_basename(__FILE__)) ?>/hdflvplayer/css/jquery.ui.all.css">

    <script src="<?php echo $site_url ?>/wp-content/plugins/<?php echo dirname(plugin_basename(__FILE__)) ?>/js/jquery-1.4.4.js"></script>
    <script>
	var eff = jQuery.noConflict();
	</script>
    <script src="<?php echo $site_url ?>/wp-content/plugins/<?php echo dirname(plugin_basename(__FILE__)) ?>/js/jquery.ui.core.js"></script>
    <script src="<?php echo $site_url ?>/wp-content/plugins/<?php echo dirname(plugin_basename(__FILE__)) ?>/js/jquery.ui.widget.js"></script>
    <script src="<?php echo $site_url ?>/wp-content/plugins/<?php echo dirname(plugin_basename(__FILE__)) ?>/js/jquery.ui.mouse.js"></script>
    <script src="<?php echo $site_url ?>/wp-content/plugins/<?php echo dirname(plugin_basename(__FILE__)) ?>/js/jquery.ui.sortable.js"></script>
    <style>
        .column { width: 500px; float: left; padding-bottom: 20px; }
        .portlet { margin: 0 1em 1em 0; }
        .portlet-header { margin: 0.3em; padding-bottom: 4px; padding-left: 10px;padding-top: 4px;font-size:12px; }
        .portlet-header .ui-icon { float: right; }
        .portlet-content { padding: 0.4em; font-size:12px;}
        .ui-sortable-placeholder { border: 1px dotted black; visibility: visible !important; height: 50px !important; }
        .ui-sortable-placeholder * { visibility: hidden; }
    </style>
    <script>
	var eff = jQuery.noConflict();

        eff(function() {
            eff( ".column" ).sortable({
                connectWith: ".column"
            });

            eff( ".portlet" ).addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
            .find( ".portlet-header" )
            .addClass( "ui-widget-header ui-corner-all" )
            .prepend( "<span class='ui-icon ui-icon-minusthick'></span>")
            .end()
            .find( ".portlet-content" );

            eff( ".portlet-header .ui-icon" ).click(function() {
                eff( this ).toggleClass( "ui-icon-minusthick" ).toggleClass( "ui-icon-plusthick" );
                eff( this ).parents( ".portlet:first" ).find( ".portlet-content" ).toggle();
            });

            eff( ".column" ).disableSelection();
        });
    </script>
    <div class="wrap">
        <h2>HDFLVPlayer Options</h2>
        <form method="post" enctype="multipart/form-data" action="options-general.php?page=hdflvplugin.php">
            <p>Welcome to the HDFLVPlayer plugin options menu! &nbsp;&nbsp; <a style="color:red;" href='<?php echo $site_url; ?>/wp-admin/upload.php?page=hdflv'>Add Video</a></p>
            <div class="column">

                <div class="portlet">
                    <div class="portlet-header">Display Configuration</div>
                    <div class="portlet-content">
                        <table class="form-table">
                            <tr>
                                <th scope='row'>Player Width</th>
                                <td><input type='text' name="width" value="<?php echo $fetchSettings->width ?>" size=45  /></td>
                            </tr>
                            <tr>
                                <th scope='row'>Player Height</th>
                                <td><input type='text' name="height" value="<?php echo $fetchSettings->height ?>" size=45  /></td>
                            </tr>
                            <tr>
                                <th scope='row'>Stagecolor</th>
                                <td><input type='text' name="stagecolor" value="<?php echo $fetchSettings->stagecolor ?>" size=45  /></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="portlet">
                <div class="portlet-header">Skin Configuration</div>
                <div class="portlet-content">
                    <table class="form-table">
                        <tr>
                            <th scope='row'>Timer</th>
                            <td><input type='checkbox' class='check' <?php if ($fetchSettings->timer == 1) { ?> checked <?php } ?> name="timer" value="1" size=45  /></td>
                        </tr>
                        <tr>
                            <th scope='row'>Zoom</th>
                            <td><input type='checkbox' class='check' <?php if ($fetchSettings->zoom == 1) { ?> checked <?php } ?> name="zoom" value="1" size=45  /></td>
                        </tr>
                        <tr>
                            <th scope='row'>Share</th>
                            <td><input type='checkbox' class='check' <?php if ($fetchSettings->email == 1) { ?> checked <?php } ?> name="email" value="1" size=45  /></td>
                        </tr>
                        <tr>
                            <th scope='row'>Full Screen</th>
                            <td><input type='checkbox' class='check' <?php if ($fetchSettings->fullscreen == 1) { ?> checked <?php } ?> name="fullscreen" value="1" size=45  /></td>
                        </tr>
                        <tr>
                            <th scope='row'>Skin Autohide</th>
                            <td><input type='checkbox' class='check' <?php if ($fetchSettings->skin_autohide == 1) { ?> checked <?php } ?> name="skin_autohide" value="1" size=45  /></td>
                        </tr>
                        <tr>
                            <th scope='row'>Skin</th>
                            <td>
                                <select name="skin" style="width:150px;">
                                    <?php foreach ($skins as $skin) {
                                    ?>
                                                  <option <?php if ($fetchSettings->skin == $skin) { ?> selected="selected" <?php } ?> value="<?php echo $skin; ?>"><?php echo $skin; ?></option>
                                    <?php } ?>
                                          </select>
                                      </td>
                                  </tr>
                              </table>
                          </div>
                      </div>
               <div class="portlet">
                          <div class="portlet-header">Video Configuration</div>
                          <div class="portlet-content">
                              <table class="form-table">
                                  <tr>
                                      <th scope='row'>Auto Play</th>
                                      <td><input type='checkbox' class='check' name="autoplay" <?php if ($fetchSettings->autoplay == 1) { ?> checked <?php } ?> value="1" size=45  /></td>
                                  </tr>
                                  <tr>
                                      <th scope='row'>Download</th>
                                      <td><input type='checkbox' class='check' name="download" <?php if ($fetchSettings->download == 1) { ?> checked <?php } ?> value="1" size=45  /></td>
                                  </tr>
                                  <tr>
                                      <th scope='row'>Buffer</th>
                                      <td><input type='text' name="buffer" value="<?php echo $fetchSettings->buffer ?>" size=45  /></td>
                                  </tr>
                                  <tr>
                                      <th scope='row'>Volume</th>
                                      <td><input type='text' name="volume" value="<?php echo $fetchSettings->volume ?>" size=45  /></td>
                                  </tr>
                              </table>
                          </div>
                      </div>
                <div class="portlet">
                    <div class="portlet-header">License Configuration</div>
                    <div class="portlet-content">
                        <table class="form-table">
                            <tr>
                                <th scope='row'>License Key</th>
                                <td><input type='text' name="license" value="<?php echo $fetchSettings->license ?>" size=45  /></td>
                            </tr>
                        </table>
                    </div>
                </div>


        </div>
        <div class="column">

         <div class="portlet">
                    <div class="portlet-header">Playlist Configuration</div>
                    <div class="portlet-content">
                        <table class="form-table">
                            <tr>
                                <th scope='row'>Playlist Display</th>
                                <td><input type='checkbox' class='check' name="playlist" <?php if ($fetchSettings->playlist == 1) { ?> checked <?php } ?> value="1" size=45  /></td>
                            </tr>
                            <tr>
                                <th scope='row'>HD Default</th>
                                <td><input type='checkbox' class='check' name="HD_default" <?php if ($fetchSettings->HD_default == 1) { ?> checked <?php } ?> value="1" size=45  /></td>
                            </tr>
                            <tr>
                                <th scope='row'>Playlist Autoplay</th>
                                <td><input type='checkbox' class='check' <?php if ($fetchSettings->playlistauto == 1) { ?> checked <?php } ?> name="playlistauto" value="1" size=45  /></td>
                            </tr>
                        </table>
                    </div>
                </div>

            <div class="portlet">
                    <div class="portlet-header">General Settings</div>
                    <div class="portlet-content">
                        <table class="form-table">
                            <tr>
                                <th scope='row'>Normal Scale</th>
                                <td>
                                    <select name="normalscale" style="width:150px;">
                                        <option value="0" <?php if ($fetchSettings->normalscale == 0) { ?> selected="selected" <?php } ?> >Aspect Ratio</option>
                                        <option value="1" <?php if ($fetchSettings->normalscale == 1) { ?> selected="selected" <?php } ?>>Original Screen</option>
                                        <option value="2" <?php if ($fetchSettings->normalscale == 2) { ?> selected="selected" <?php } ?>>Fit To Screen</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope='row'>Full Screen Scale</th>
                                <td>
                                    <select name="fullscreenscale" style="width:150px;">
                                        <option value="0" <?php if ($fetchSettings->fullscreenscale == 0) { ?> selected="selected" <?php } ?>>Aspect Ratio</option>
                                        <option value="1" <?php if ($fetchSettings->fullscreenscale == 1) { ?> selected="selected" <?php } ?>>Original Screen</option>
                                        <option value="2" <?php if ($fetchSettings->fullscreenscale == 2) { ?> selected="selected" <?php } ?>>Fit To Screen</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope='row'>Uploads</th>
                                <td>
                <!--                    <label><input name="usedefault" type='radio' value="1" <?php if ($setting['v'] == 1)
        echo 'checked'; ?> /> <?php _e('Standard upload folder : ', 'hdflv') ?></label><code><?php echo get_option('upload_path'); ?></code><br />-->
                                <label><input name="usedefault" type='radio' value="0"  <?php if ($setting['v'] == 0)
                                    echo 'checked'; ?> /> <?php _e('Store uploads in this folder : ', 'hdflv') ?></label>
                                <input type="text" size="35" maxlength="200" name='uploadurl' value="<?php echo $fetchSettings->upload_path ?>" />
                            </td>
                        </tr>
                        <tr>
                            <th scope='row'>Embed Visible</th>
                            <td><input type='checkbox' class='check' <?php if ($fetchSettings->embed_visible == 1) { ?> checked <?php } ?> name="embed_visible" value="1" size=45  /></td>
                        </tr>
                        <tr>
                            <th scope='row'>Debug</th>
                            <td><input type='checkbox' class='check' <?php if ($fetchSettings->debug == 1) { ?> checked <?php } ?> name="debug" value="1" size=45  /></td>
                        </tr>

                    </table>
                </div>
            </div>
                      <div class="portlet">
                          <div class="portlet-header">Logo Configuration (Applicable Only For Licensed Player)
                            <?php if($fetchSettings->license == '' || $fetchSettings->license == '0'){?>
                              <a href="http://www.hdflvplayer.net/wordpress/" target="_blank" style="text-decoration: none;color:red;cursor:pointer;">Buy Now</a>
                              <?php }?>
                          </div>

                          <div class="portlet-content">
                              <table class="form-table">
                                  <tr>
                                      <th scope='row'>Logo Path</th>
                                      <td><input type='file' name="logopath" value="" size=35  /><?php echo $fetchSettings->logopath ?></td>
                                  </tr>
                                  <tr>
                                      <th scope='row'>Logo Target</th>
                                      <td><input type='text' name="logotarget" value="<?php echo $fetchSettings->logo_target ?>" size=45  /></td>
                                  </tr>
                                  <tr>
                                      <th scope='row'>Logo Align</th>
                                      <td> <select name="logoalign" style="width:150px;">
                                              <option <?php if ($fetchSettings->logoalign == 'TL') { ?> selected="selected" <?php } ?> value="TL">Top Left</option>
                                              <option <?php if ($fetchSettings->logoalign == 'TR') { ?> selected="selected" <?php } ?> value="TR">Top Right</option>
                                              <option <?php if ($fetchSettings->logoalign == 'LB') { ?> selected="selected" <?php } ?> value="LB">Left Bottom</option>
                                              <option <?php if ($fetchSettings->logoalign == 'RB') { ?> selected="selected" <?php } ?> value="RB">Right Bottom</option>
                                          </select></td>
                                  </tr>
                                  <tr>
                                      <th scope='row'>Logo Alpha</th>
                                      <td><input type='text' name="logoalpha" value="<?php echo $fetchSettings->logoalpha ?>" size=45  /></td>
                                  </tr>
                              </table>
                          </div>
                      </div>

                  </div>
                  <div class="clear"></div>
                      <p class='submit'><input class='button-primary' type='submit' value='Update Options'></p>

              </form>
          </div>

<!-- End of settings design-->

<?php
}

function HDFLV_head() {
  global $site_url;
  echo '<script type="text/javascript" src="' . $site_url . '/wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/swfobject.js"></script>' . "\n";
}

add_action('wp_head', 'HDFLV_head');

/* Loading default settings of player */
function HDFLVLoadDefaults() {
  global $wpdb;
  $insertSettings = $wpdb->query(" INSERT INTO " . $wpdb->prefix . "hdflv_settings
VALUES (1,1,1,1,1,0,1,'platoon.jpg','http://www.hdflvplayer.net/',50,'LR',1,1,0,20,1,'0x000000','skin_black',0,'hdflvplayer/videourl.php','playXml',1,1,1,1,1,500,400,1,0,0,'wp-content/uploads')");
}

/* Function to uninstall player plugin */
function hdflv_deinstall() {
  global $wpdb, $wp_version;

  $hd_table = $wpdb->prefix . 'hdflv';
  $hd_table_mp = $wpdb->prefix . 'hdflv_med2play';
  $hd_table_pl = $wpdb->prefix . 'hdflv_playlist';
  $hd_table_set = $wpdb->prefix . 'hdflv_settings';

//drop table start
//  $wpdb->query("DROP TABLE IF EXISTS `" . $hd_table . "`");
//  $wpdb->query("DROP TABLE IF EXISTS `" . $hd_table_mp . "`");
//  $wpdb->query("DROP TABLE IF EXISTS `" . $hd_table_pl . "`");
//  $wpdb->query("DROP TABLE IF EXISTS `" . $hd_table_set . "`");
}

/* Function to invoke install player plugin */
function hd_install() {
  require_once(dirname(__FILE__) . '/install.php');
  hdflv_install();
}

/* Function to activate player plugin */
function HDFLV_activate() {
  HDFLVLoadDefaults();
}

register_activation_hook(plugin_basename(dirname(__FILE__)) . '/hdflvplugin.php', 'hd_install');
register_activation_hook(__FILE__, 'HDFLV_activate');
register_uninstall_hook(__FILE__, 'hdflv_deinstall');


/* Function to deactivate player plugin */
function HDFLV_deactivate() {
  delete_option('HDFLVSettings');
}

register_deactivation_hook(__FILE__, 'HDFLV_deactivate');

// CONTENT FILTER
add_filter('the_content', 'HDFLV_Parse');

// OPTIONS MENU
add_action('admin_menu', 'HDFLVAddPage');
?>