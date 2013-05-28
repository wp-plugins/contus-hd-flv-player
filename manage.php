<?php
/*
  Name: Contus HD FLV Player
  Plugin URI: http://www.apptha.com/category/extension/Wordpress/HD-FLV-Player-Plugin/
  Description: HD FLV Player manage file.
  Version: 2.5
  Author: Apptha
  Author URI: http://www.apptha.com
  License: GPL2
 */

$contus = dirname(plugin_basename(__FILE__));
$siteUrl = get_option('siteurl');
define('SITEURL', $siteUrl);
define('PLUGINNAME', $contus);
$jsDirPaht = $siteUrl . '/wp-content/plugins/' . $contus . '/js';
$pluginDir = $siteUrl . '/wp-content/plugins/' . $contus;
@session_start();
unset($_SESSION['app_wp_token']);
global $user_ID;
$_SESSION['app_wp_token'] = md5($user_ID);
?>
<style type="text/css">
    #poststuff h3, .metabox-holder h3 {cursor: default;}
</style>
<script type="text/javascript" src="<?php echo $jsDirPaht; ?>/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="<?php echo $jsDirPaht; ?>/jquery-ui-1.7.1.custom.min.js"></script>
<script type="text/javascript" src="<?php echo $jsDirPaht; ?>/hdflvscript.js"></script>
<script type="text/javascript">
    // When the document is ready set up our sortable with it's inherant function(s)
    $(document).ready(function() {
        $("#test-list").sortable({
            handle : '.handle',
            update : function () {
                var order = $('#test-list').sortable('serialize');
                var playid = document.getElementById('playlistid2').value;
                $("#info").load("<?php echo $pluginDir; ?>/process-sortable.php?"+order+"&playid="+playid+"&hdflv_token="+"<?php echo $_SESSION['app_wp_token']; ?>");
                showUser(playid,order);
            }
        });
    });
</script>
<script>

    var uploadqueue = [];
    var uploadmessage = '';
    var plugin_dir = '<?php echo $contus; ?>';
    function addQueue(whichForm,myfile)
    {
        var  extn = extension(myfile);
        if( whichForm == 'normalvideoform' || whichForm == 'hdvideoform' )
        {
            if(extn != 'flv' && extn != 'FLV' && extn != 'mp4' && extn != 'MP4' && extn != 'm4v' && extn != 'M4V' && extn != 'mp4v' && extn != 'Mp4v' && extn != 'm4a' && extn != 'M4A' && extn != 'mov' && extn != 'MOV' && extn != 'f4v' && extn != 'F4V' && extn != 'mp3' && extn != 'MP3')
            {
                alert(extn+" is not a valid Video Extension");
                return false;
            }
        }
        else
        {
            if(extn != 'jpg' && extn != 'png' )
            {
                alert(extn+" is not a valid Image Extension");
                return false;
            }
        }
        uploadqueue.push(whichForm);
        if (uploadqueue.length == 1)
        {

            processQueue();
        }
        else
        {
            holdQueue();
        }
    }
    function processQueue()
    {
        if (uploadqueue.length > 0)
        {
            form_handler = uploadqueue[0];
            setStatus(form_handler,'Uploading');
            submitUploadForm(form_handler);
        }
    }
    function holdQueue()
    {
        form_handler = uploadqueue[uploadqueue.length-1];
        setStatus(form_handler,'Queued');
    }
    function updateQueue(statuscode,statusmessage,outfile)
    {
        uploadmessage = statusmessage;
        form_handler = uploadqueue[0];
        if (statuscode == 0)
            document.getElementById(form_handler+"-value").value = outfile;
        setStatus(form_handler,statuscode);
        uploadqueue.shift();
        processQueue();
    }

    function submitUploadForm(form_handle)
    {
        var token = '<?php echo $_SESSION['app_wp_token']; ?>';
        document.forms[form_handle].target = "uploadvideo_target";
        document.forms[form_handle].action = "../wp-content/plugins/"+plugin_dir+"/uploadVideo.php?processing=1&hdflv_token="+token;
        document.forms[form_handle].submit();
    }
    function setStatus(form_handle,status)
    {
        switch(form_handle)
        {
            case "normalvideoform":
                divprefix = 'f1';
                break;
            case "hdvideoform":
                divprefix = 'f2';
                break;
            case "thumbimageform":
                divprefix = 'f3';
                break;
            case "previewimageform":
                divprefix = 'f4';
                break;
        }
        switch(status)
        {
            case "Queued":
                document.getElementById(divprefix + "-upload-form").style.display = "none";
                document.getElementById(divprefix + "-upload-progress").style.display = "";
                document.getElementById(divprefix + "-upload-status").innerHTML = "Queued";
                document.getElementById(divprefix + "-upload-message").style.display = "none";
                document.getElementById(divprefix + "-upload-filename").innerHTML = document.forms[form_handle].myfile.value;
                document.getElementById(divprefix + "-upload-image").src = '../wp-content/plugins/'+plugin_dir+'/images/empty.gif';
                document.getElementById(divprefix + "-upload-cancel").innerHTML = '<a style="float:right;padding-right:10px;" href=javascript:cancelUpload("'+form_handle+'") name="submitcancel">Cancel</a>';
                break;

            case "Uploading":
                document.getElementById(divprefix + "-upload-form").style.display = "none";
                document.getElementById(divprefix + "-upload-progress").style.display = "";
                document.getElementById(divprefix + "-upload-status").innerHTML = "Uploading";
                document.getElementById(divprefix + "-upload-message").style.display = "none";
                document.getElementById(divprefix + "-upload-filename").innerHTML = document.forms[form_handle].myfile.value;
                document.getElementById(divprefix + "-upload-image").src = '../wp-content/plugins/'+plugin_dir+'/images/loader.gif';
                document.getElementById(divprefix + "-upload-cancel").innerHTML = '<a style="float:right;padding-right:10px;" href=javascript:cancelUpload("'+form_handle+'") name="submitcancel">Cancel</a>';
                break;
            case "Retry":
            case "Cancelled":
                //uploadqueue = [];
                document.getElementById(divprefix + "-upload-form").style.display = "";
                document.getElementById(divprefix + "-upload-progress").style.display = "none";
                document.forms[form_handle].myfile.value = '';
                enableUpload(form_handle);
                break;
            case 0:
                document.getElementById(divprefix + "-upload-image").src = '../wp-content/plugins/'+plugin_dir+'/images/success.gif';
                document.getElementById(divprefix + "-upload-status").innerHTML = "";
                document.getElementById(divprefix + "-upload-message").style.display = "";
                document.getElementById(divprefix + "-upload-message").style.backgroundColor = "#CEEEB2";
                document.getElementById(divprefix + "-upload-message").innerHTML = uploadmessage;
                document.getElementById(divprefix + "-upload-cancel").innerHTML = '';
                break;


            default:
                document.getElementById(divprefix + "-upload-image").src = '../wp-content/plugins/'+plugin_dir+'/images/error.gif';
                document.getElementById(divprefix + "-upload-status").innerHTML = " ";
                document.getElementById(divprefix + "-upload-message").style.display = "";
                document.getElementById(divprefix + "-upload-message").innerHTML = uploadmessage + " <a href=javascript:setStatus('" + form_handle + "','Retry')>Retry</a>";
                document.getElementById(divprefix + "-upload-cancel").innerHTML = '';
                break;
        }
    }

    function enableUpload(whichForm,myfile)
    {
        if (document.forms[whichForm].myfile.value != '')
            document.forms[whichForm].uploadBtn.disabled = "";
        else
            document.forms[whichForm].uploadBtn.disabled = "disabled";
    }

    function cancelUpload(whichForm)
    {
        document.getElementById('uploadvideo_target').src = '';
        setStatus(whichForm,'Cancelled');
        pos = uploadqueue.lastIndexOf(whichForm);
        if (pos == 0)
        {
            if (uploadqueue.length >= 1)
            {
                uploadqueue.shift();
                processQueue();
            }
        }
        else
        {
            uploadqueue.splice(pos,1);
        }

    }
    function chkbut()
    {
        if(uploadqueue.length <= 0 )
        {
            if(document.getElementById('btn2').checked)
            {
                document.getElementById('youtube-value').value= document.getElementById('filepath1').value;
                document.getElementById('customurl1').value = document.getElementById('filepath2').value;
                document.getElementById('customhd1').value = document.getElementById('filepath3').value;
                return true;
            }
            if(document.getElementById('btn3').checked)
            {
                document.getElementById('customurl1').value = document.getElementById('filepath2').value;
                document.getElementById('customhd1').value = document.getElementById('filepath3').value;
                document.getElementById('customimage').value = document.getElementById('filepath4').value;
                document.getElementById('custompreimage').value = document.getElementById('filepath5').value;
                return true;
            }
        }else { alert("Wait for Uploading to Finish"); return false; }

    }
    function extension(fname)
    {
        var pos = fname.lastIndexOf(".");

        var strlen = fname.length;

        if(pos != -1 && strlen != pos+1)
        {
            var ext = fname.split(".");
            var len = ext.length;
            var extension = ext[len-1].toLowerCase();
        }
        else
        {

            extension = "No extension found";

        }

        return extension;

    }
</script>

<?php

class HDFLVManage {

    var $mode = 'main';
    var $wptfile_abspath;
    var $wp_urlpath;
    var $act_vid = false;
    var $act_pid = false;
    var $base_page = '?page=hdflv';
    var $PerPage = 10;

    function HDFLVManage() {
        global $hdflv;

        // get the options
        $this->options = get_option('HDFLVSettings');

        // Manage upload dir
        add_filter('upload_dir', array(&$this, 'upload_dir'));

        $wp_upload = wp_upload_dir();

        $this->wptfile_abspath = $wp_upload['path'] . '/';
        $this->wp_urlpath = $wp_upload['url'] . '';
        $this->editFile_abspath = $wp_upload['path'] . '';
        // output Manage screen
        $this->controller();    //it is main function in this we calling all tabs
    }

    /**
     * Renders an admin section of display code
     * @author     John Godley (http://urbangiraffe.com)
     *
     * @param string $ug_name Name of the admin file (without extension)
     * @param string $array Array of variable name=>value that is available to the display code (optional)
     * @return void
     * */
    function render_admin($ug_name, $ug_vars = array()) {
        //echo $ug_name."".$ug_vars;
        // exit();
        $function_name = array($this, 'show_' . $ug_name);  // show_playlist , show_[function]

        if (is_callable($function_name)) // return 1 or 0   $ug_vars is parameters to send function
            call_user_func_array($function_name, $ug_vars); // Call a user function given with an array of parameters http://php.net/manual/en/function.call-user-func-array.php
 else
            echo "<p>Rendering of admin function show_$ug_name failed</p>";
    }

    // Return custom upload dir/url
    function upload_dir($uploads) {

        if ($this->options[0][27]['v'] == 0) {
            $dir = ABSPATH . trim($this->options[0][28]['v']);
            $url = trailingslashit(get_option('siteurl')) . trim($this->options[0][28]['v']);


            // Make sure we have an uploads dir
            if (!wp_mkdir_p($dir)) {
                $message = sprintf(__('Unable to create directory %s. Is its parent directory writable by the server?', 'hdflv'), $dir);
                $uploads['error'] = $message;
                return $uploads;
            }
            $uploads = array('path' => $dir, 'url' => $url, 'error' => false);
        }
        return $uploads;
    }

    function render_message($message, $timeout = 0) {
?>
        <div style="margin-bottom: 0px;margin-top: -20px;" class="wrap">
            <div class="fade updated" id="message" onclick="this.parentNode.removeChild (this)">
                <p><strong><?php echo $message ?></strong></p>
            </div>
        </div>
<?php
    }

    function haflvMenuTab() {
        $modeType = trim(filter_input(INPUT_GET, 'mode'));
        $page = trim(filter_input(INPUT_GET, 'page')); // To Display menu tabs;
?>
        <h2 class="nav-tab-wrapper">
            <a  id="hdflv"   href="?page=hdflv" class="nav-tab <?php if ($page == 'hdflv' && empty($modeType)) {
            echo 'nav-tab-active';
        } ?>"> Manage Videos</a>
            <a  id="video"   href="?page=hdflv&mode=video" class="nav-tab <?php if ($page == 'hdflv' && !empty($modeType)) {
            echo 'nav-tab-active';
        } ?>"> Add Video</a>
            <a  id="playlist" href="?page=hdflvplaylist" class="nav-tab <?php if ($page == 'hdflvplaylist') {
            echo 'nav-tab-active';
        } ?>">Playlists</a>
            <a  id="settings" href="?page=hdflvplugin.php" class="nav-tab <?php if ($page == 'hdflvplugin.php') {
            echo 'nav-tab-active';
        } ?>">Settings</a>
        </h2>

<?php
    }

    function controller() {
        global $wpdb;
        filter_input(INPUT_GET, 'playid');
        $modeType = trim(filter_input(INPUT_GET, 'mode'));  //it show the menu tab;
        $page = trim(filter_input(INPUT_GET, 'page'));
        if (isset($modeType)) {
            switch ($modeType) {
                case 'playlist' : echo $this->mode = 'playlist';
                    break;
                case 'video' : $this->mode = 'add';
                    break;
                case 'edit' : $this->mode = 'edit';
                    break;
                case 'delete' : $this->mode = 'hdflv';

                    $video_name_in = filter_input(INPUT_GET, 'video_name');
                    $video_name = substr($video_name_in, 0, 35) . ' ...';
                    $id = filter_input(INPUT_GET, 'id');
                    hd_delete_media($id, 0, $video_name);
                    $this->mode = 'main';

                    break;
            }
        }
        if ($page == 'hdflvplaylist') {
            $this->mode = 'playlist';
        }
        $id = filter_input(INPUT_GET, 'id');
        $pid = filter_input(INPUT_GET, 'pid');

        $this->act_vid = (int) $id;
        $this->act_pid = (int) $pid;

//TODO:Include nonce !!!
        $add_media = filter_input(INPUT_POST, 'add_media');
        if (isset($add_media)) {
            hd_add_media($this->wptfile_abspath, $this->wp_urlpath);
            $this->mode = 'add';
        }

        $youtube_media = filter_input(INPUT_POST, 'youtube_media');
        if (isset($youtube_media)) {
            $act1 = youtubeurl();
            $act0_str = str_replace('"', '', $act1[0]);
?> 			<input type="hidden" name="act" id="act3" value="<?php echo $act1[3] ?>" />
            <input type="hidden" name="act" id="act0" value="<?php echo stripslashes($act0_str); ?>" />
            <input type="hidden" name="act" id="act4" value="<?php echo $act1[4] ?>" />
<?php
            $this->mode = 'add'; // hd_add_media($this->wptfile_abspath, $this->wp_urlpath);
        }
        $edit_update = filter_input(INPUT_POST, 'edit_update');

        $edit_cancel = filter_input(INPUT_POST, 'edit_cancel');
        if (isset($edit_update) || isset($edit_cancel)) {
            hd_update_media($this->act_vid);
            $this->mode = 'main';
            $paged = filter_input(INPUT_GET, 'paged');
            if ($paged) { //for pagination
                $siteUrl = '?page=hdflv&edit=succ&paged=' . $paged;
            } else {
                $siteUrl = '?page=hdflv&edit=succ';
            }

            echo '<script type="text/javascript">';
            echo "window.location.href = '$siteUrl'";
            echo '</script>';
        }
        $edit = filter_input(INPUT_GET, 'edit');
        if (isset($edit)) {
            render_message('Updated Successfully');
        }
        /* updating thumb image */
        $thumbupdate = filter_input(INPUT_POST, 'thumb-update');
        if (isset($thumbupdate)) {
            hd_update_thumb($this->editFile_abspath, $this->wp_urlpath, $this->act_vid);

            $this->mode = 'main';
        }
        /* updating preview image */
        $previewupdate = filter_input(INPUT_POST, 'preview-update');
        if (isset($previewupdate)) {
            hd_update_preview($this->editFile_abspath, $this->wp_urlpath, $this->act_vid);
            $this->mode = 'main';
        }
        $cancel = filter_input(INPUT_POST, 'cancel');
        $search = filter_input(INPUT_POST, 'search');
        if (isset($cancel) || isset($search))
            $this->mode = 'main';
        $show_add = filter_input(INPUT_POST, 'show_add');
        if (isset($show_add))
            $this->mode = 'add';
        $add_pl = filter_input(INPUT_POST, 'add_pl');
        if (isset($add_pl)) {
            hd_add_playlist();
            $this->mode = 'edit';
        }
        $add_pl1 = filter_input(INPUT_POST, 'add_pl1');
        if (isset($add_pl1)) {

            hd_add_playlist();
            $this->mode = 'add';
        }
        $add_playlist = filter_input(INPUT_POST, 'add_playlist');
        if (isset($add_playlist)) {
            hd_add_playlist();   // in function.php file
            $this->mode = 'playlist';
        }

        $update_playlist = filter_input(INPUT_POST, 'update_playlist');
        if (isset($update_playlist)) {
            hd_update_playlist();
            $this->mode = 'playlist';
        }
        $video_name = filter_input(INPUT_GET, 'video_name');
        if ($this->mode == 'delete') {
            $video_name = substr($video_name, 0, 35) . ' ...';
            hd_delete_media($this->act_vid, $this->options['deletefile'], $video_name);
            $this->mode = 'main';
        }

//Let's show the main screen if no one selected
        if (empty($this->mode))
            $this->mode = 'main';


// render the admin screen
        $this->render_admin($this->mode);
    }

    function show_main() {
        global $wpdb;

// init variables
        $pledit = true;
        $where = '';
        $join = '';

        $search_input = filter_input(INPUT_POST, 'search');
// check for page navigation
        $sort = 'ASC';
        $search = ( isset($search_input)) ? $search_input : '';
        $search_results = $search;
        $pl_id = filter_input(INPUT_POST, 'playid');
        $plfil = filter_input(INPUT_POST, 'plfilter');
        $plfilter = ( isset($plfil)) ? $plfil : (isset($pl_id) ? $pl_id : '0' );

        //print_r($_POST);
        if ($search != '') {
            if ($where != '')
                $where .= " AND ";
            $where .= " (name LIKE '%$search_results%')";
        }

        if ($plfilter != '0' && $plfilter != 'no') {
            $join = " LEFT JOIN " . $wpdb->prefix . "hdflv_med2play ON (vid = media_id) ";
            if ($where != '')
                $where .= " AND ";
            $where .= " (playlist_id = '" . $plfilter . "') ";
            $pledit = true;
        } elseif ($plfilter == 'no') {
            $join = "  WHERE `vid` NOT IN( SELECT media_id FROM " . $wpdb->prefix . "hdflv_med2play ) ";
            $pledit = false;
        }
        if ($search != '') {
            $join = " LEFT JOIN " . $wpdb->prefix . "hdflv_med2play ON (vid = media_id) ";
            $pledit = false;
        }
        else
            $pledit = false;

        if ($where != '')
            $where = " WHERE " . $where;
        //   echo "SELECT COUNT(*) FROM " . $wpdb->prefix . "hdflv" . $join . $where;

        $total = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->prefix . "hdflv" . $join . $where);

        $total_pages = ceil($total / $this->PerPage);

        if ($total_pages == 0)
            $total_pages = 1;
        if ($pledit)
            $orderby = " ORDER BY sorder " . $sort . ", vid " . $sort;
        else
            $orderby = " ORDER BY vid " . $sort;

        $paged = filter_input(INPUT_GET, 'paged');

        if (!$paged) {
            $limit = "LIMIT 0 , $this->PerPage";
        } else {
            $lowLimit = $paged * ($this->PerPage);
            $lowLimit = $lowLimit - ($this->PerPage );
            if ($lowLimit < 0) {
                $lowLimit = 0;
            }
            $hiLimit = $this->PerPage;
            $limit = "LIMIT $lowLimit , $hiLimit ";
        }

        $tables = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "hdflv" . $join . $where . $orderby . " $limit ");
        $pluginDirPath = get_option('siteurl') . '/wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/images';
?>
        <!-- Manage Video-->
        <div class="wrap hdflvwrap">

<?php $this->haflvMenuTab(); ?>
            <form name="filterType" method="post" id="posts-filter">
                <h2 ><?php _e('Manage Videos', 'hdflv'); ?></h2>
                <div class="admin_hdflv_video_info">
                    <span class="hint_head">How To Use?</span>
                    <p >You can also set different width and height for the player in different posts irrespective of the values specified here.<br /><br />
                        For example: <strong>[hdplay id=3 playlistid=2 width=400 height=400 ]</strong> or <strong>[hdplay playlistid=2 ]</strong> or <strong>[hdplay id=3 ]</strong> <br/><br/>
                        For Skin Configuration: <strong>[hdplay id=2 flashvars="autoplay=false&timer=false" width=250 height=250 ]</strong><br/><br/>
                            The "id" will be created when you add videos.</p>
                </div>
                <!-- for show select playlist and Add vidoes features.   -->

                <div style="margin: 23px 0px 10px 1px;" >
                    <div class="alignleft actions">
        	                Select Playlist<?php $this->playlist_filter($plfilter); ?>
                    <input title="To Filter Playlist" class="button-secondary" id="post-query-submit" type="submit" name="startfilter"  value="<?php _e('Filter', 'hdflv'); ?> &raquo;" class="button" />
                </div>

                <p class="search-box">
                    <input type="text" class="search-input" name="search" value="<?php echo $search; ?>" size="10" />
                <input type="submit" class="button-secondary" value="<?php _e('Search Video', 'hdflv'); ?>" />
                <input type="hidden" name="cancel" value="2"/>
            </p>

        </div>

        <!-- Table -->
        <table class="widefat" cellspacing="0" style="margin-top: 6%;" id="videostableid">
            <thead>
                <tr>
                    <th style="width:4%; " id="id" class="manage-column column-id" scope="col"><?php _e('ID', 'hdflv'); ?> </th>
                    <th style="width:38%;" id="title" class="manage-column column-title" scope="col"><?php _e('Title', 'hdflv'); ?> </th>
                    <th style="width:51%;" id="path" class="manage-column column-path"  scope="col"><?php _e('Path', 'hdflv'); ?> </th>
<?php
        $playid = filter_input(INPUT_GET, 'playid');
        $plfilter = filter_input(INPUT_GET, 'plfilter');
        if (isset($plfilter) && $plfilter != 'no' && $plfilter != '0' || isset($playid)) {
            $id1 = '1'; ?>
                    <th id="path" class="manage-column column-path"  scope="col"><?php _e('Sort Order', 'hdflv'); ?> </th><?php } ?>
                    <th id="Status" class="manage-column column-id" scope="col"><?php _e('Status', 'hdflv'); ?> </th>

                </tr>
            </thead>
            <tbody id="test-list" class="list:post">
            <input type=hidden id=playlistid2 name=playlistid2 value=<?php echo $plfilter ?> />
            <input type="hidden" id="imagepath" name="imagepath" value="<?php echo $pluginDirPath; ?>" />
            <div name=txtHint ></div>
<?php
                    if ($tables) {
                        $i = 0;
                        $paged = filter_input(INPUT_GET, 'paged');
                        if ($paged) {
                            $editPageid = 'paged=' . $paged;
                        } else {
                            $editPageid = '';
                        }
                        $showArrowImage = "<img src='../wp-content/plugins/" . dirname(plugin_basename(__FILE__)) . "/images/arrow.png' alt='move' width='16' height='16' class='handle' /></th>\n";
                        foreach ($tables as $table) {

                            $class = ( $class == 'class="alternate"' ) ? '' : 'class="alternate"';
                            echo "<tr $class id=\"listItem=$table->vid\" >\n";
                            echo "<th style='width:5%;' scope=\"row\" > $table->vid ";
                            echo "&nbsp";
                            if ($id1 == '1') {
                                echo $showArrowImage;
                            }

                            echo "<td  class='post-title column-title' style='text-align: left;'><strong><a title='" . __('Edit this media', 'hdflv') . "' href='$this->base_page&amp;mode=edit&amp;id=$table->vid'>" . stripslashes($table->name) . "</a></strong>\n";
                            echo "<input type='hidden' name='video_name' value=' .  $videoName . '>";
                            echo "<span class='edit'>
                                                                <a title='" . __('Edit this video', 'hdflv') . "' href='$this->base_page&amp;mode=edit&amp;id=$table->vid&amp;$editPageid'>" . __('Edit') . "</a>
                                                              </span> | ";
                            echo "<span class='delete'>
                                                                <a title='" . __('Delete this video', 'hdflv') . "' href='$this->base_page&amp;mode=delete&amp;id=$table->vid&amp;video_name=$table->name' onclick=\"javascript:check=confirm( '" . __("Delete this file ?", 'hdflv') . "');if(check==false) return false;\">" . __('Delete') . "</a>
                                                              </span>";
                            echo "</td>\n";
                            echo "<td>" . htmlspecialchars(stripslashes($table->file), ENT_QUOTES) . "</td>\n";
                            $playid = filter_input(INPUT_GET, 'playid');
                            $plfilter = filter_input(INPUT_POST, 'plfilter');
                            $tablevid = intval($table->vid);
                            if (isset($plfilter) && $plfilter != 'no' && $plfilter != '0') {
                                $a1 = mysql_query("SELECT sorder FROM " . $wpdb->prefix . "hdflv_med2play where playlist_id=" . $plfilter . " and media_id=$tablevid");
                                $playlist1 = mysql_fetch_array($a1);
                                echo "<td id=txtHint[$table->vid] >" . $playlist1[0] . "</td>\n";
                            } elseif (isset($playid)) {
                                $a1 = mysql_query("SELECT sorder FROM " . $wpdb->prefix . "hdflv_med2play where playlist_id=" . $playid . " and media_id=$tablevid");
                                $playlist1 = mysql_fetch_array($a1);
                                echo "<td id=txtHint[$table->vid]>" . $playlist1[0] . "</td>\n";
                            }
                            if ($table->is_active) {

                                echo "<td  style = 'padding-left: 2%;'id='status$table->vid'><img  title='deactive' style='cursor:pointer;' onclick='setVideoStatusOff($table->vid,0)'  src=$pluginDirPath/hdflv_active.png /></td>\n";
                            } else {

                                echo "<td style = 'padding-left: 2%;' id='status$table->vid'><img  title='active' style='cursor:pointer;' onclick='setVideoStatusOff($table->vid,1)' src=$pluginDirPath/hdflv_deactive.png /></td>\n";
                            }

                            echo '</tr>';
                            $i++;
                        }
                    } else {
                        echo '<tr><td colspan="7" align="center"><b>' . __('No entries found', 'hdflv') . '</b></td></tr>';
                    }
?>
                    </tbody>
                </table>
                <div style="float:right;margin-top: 0%;">
            <?php pagination($total_pages, 1); ?>
                </div>
            </form>
        </div>
<?php
                }

//function end for Manage Video files page

                function show_edit() {


                    global $wpdb;
                    $actvid = intval($this->act_vid);
                    $media = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "hdflv where vid = $actvid");
                    $act_name = htmlspecialchars(stripslashes($media->name));
                    $act_filepath = stripslashes($media->file);
                    $act_hdpath = stripslashes($media->hdfile);
                    $act_image = stripslashes($media->image);
                    $act_link = stripslashes($media->link);
                    $act_opimg = stripslashes($media->opimage);
?>
                    <!-- Edit Video -->

                    <div class="wrap">
                        <h2> <?php _e('Edit Video', 'hdflv') ?> </h2>
                        <form name="table_options" method="post" id="video_options"    enctype="multipart/form-data" onSubmit ="return edtValidate();">
                            <div id="poststuff" class="has-right-sidebar">
                                <div class="inner-sidebar">
                                    <div id="submitdiv" class="postbox">
                                        <h3 class="hndle"><span><?php _e('Playlist', 'hdflv') ?></span></h3>
                                        <div class="inside">
                                            <div id="submitpost" class="submitbox">
                                                <div class="misc-pub-section">
                                                    <p>
<?php _e('If you want to show this media file in your page, enter the tag :', 'hdflv') ?><br /><strong>[hdplay id=<?php echo $this->act_vid; ?> ]</strong></p>
                                                </div>
                                                <div class="misc-pub-section">


                                                    <h4> <?php _e('Playlist', 'hdflv'); ?> &nbsp;&nbsp;
                                    <a style="cursor:pointer"  onclick="playlistdisplay()"><?php _e('Create New', 'hdflv') ?></a></h4>
<?php _e("Note : If you don't select any playlists, the video will be added to virtual playlist named 'Default' and there is no playlist id for this 'Default' playlist.", 'hdflv') ?>
                                <div id="playlistcreate"><?php _e('Name', 'hdflv'); ?><input type="text" size="20" name="p_name" id="p_name" value="" />
                                    <input type="button" class="button-primary" name="add_pl1" value="<?php _e('Add'); ?>" onclick="return savePlaylist(document.getElementById('p_name') , <?php echo$this->act_vid ?>);" class="button button-highlighted" />
                                    <a style="cursor:pointer" onclick="playlistclose()"><b>Close</b></a></div>
                                <p id="jaxcat"></p>
                                <div id="playlistchecklist"><?php get_playlist_for_dbx(filter_input(INPUT_GET, 'id')); ?></div>
                            </div>

                            <div id="major-publishing-actions">
                                <input type="submit" class="button-primary" name="edit_update" value="<?php _e('Update'); ?>" class="button button-highlighted" />
                                <input type="button" onclick="window.location.href='admin.php?page=hdflv'"  class="button-secondary" name="cancel" value="<?php _e('Cancel'); ?>" class="button" />
                                <input type="hidden"  value="<?php $contus = dirname(plugin_basename(__FILE__));
                    echo SITEURL . '/wp-content/plugins/' . $contus; ?>" id="pluginUrl" name="pluginUrl" />
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div id="post-body" class="has-sidebar">
                <div id="post-body-content" class="has-sidebar-content">
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><?php _e('video title', 'hdflv') ?></th>
                            <td><input type="text" size="50"  name="act_name" value="<?php echo $act_name ?>" id="act_name"/><br /><?php if ($act_name != ''): ?> <span id="alert_title" style="color:red;font-size:12px;font-weight:bold;"></span><?php endif; ?></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php _e('video URL', 'hdflv') ?></th>
                            <td><input type="text" size="80"  name="act_filepath" value="<?php echo $act_filepath ?>" id="act_filepath"/>
                                <br /><?php _e('Here you need to enter the URL to the file ( MP4, M4V, M4A, MOV, Mp4v or F4V)', 'hdflv') ?>
                                <br /><?php echo _e('Example Youtube links: http://www.youtube.com/watch?v=tTGHCRUdlBs', 'hdflv') ?>
<?php if ($act_filepath != ''): ?><br /><span id="alert_VUrl" style="color:red;font-size:12px;font-weight:bold;"></span><?php endif; ?>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php _e('HD video URL', 'hdflv') ?></th>
                            <td><input type="text" size="80"  name="act_hdpath" value="<?php echo $act_hdpath ?>" id="act_hdpath"/>
                                <br /><?php _e('Enter the URL to HD video file', 'hdflv') ?><?php if ($act_hdpath != ''): ?><br /> <span id="alert_HDURL" style="color:red;font-size:12px;font-weight:bold;"></span><?php endif; ?></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php _e('Thumbnail URL', 'hdflv') ?></th>
                            <td><input type="text" size="80"  name="act_image" value="<?php echo $act_image ?>" id="act_image"/>
                                <br/><?php _e('Enter the URL to show a thumbnail of the video file or upload a image', 'hdflv') ?><?php if ($act_image != ''): ?><br /><span id="alert_IMGURL" style="color:red;font-size:12px;font-weight:bold;"></span><?php endif; ?>
                                    <br/>
                                    <form name="edit_thumb_form" method="post" enctype="multipart/form-data">
                                        <input type="file" name="edit_thumb" id="edit_thumb">
                                        <input type="submit" name="thumb-update" value="Upload" class="button-secondary" onclick ="return validateFileExt();">
                                    </form>
                                    <br />
                                    <span id="errmsg_thumbimg" style="color:red;font-size:12px;font-weight:bold;display: none;"></span>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><?php _e('Preview Image URL', 'hdflv') ?></th>
                                <td><input type="text" size="80"  name="act_opimg" value="<?php echo $act_opimg ?>" id="act_opimg"/>
                                    <br /><?php _e('Enter the URL to show a preview of the video file or upload a image', 'hdflv') ?><?php if ($act_opimg != ''): ?><br /> <span id="alert_prIMGURL" style="color:red;font-size:12px;font-weight:bold;"></span><?php endif; ?>
                                        <br />
                                        <form name="edit_preview_form" method="post" enctype="multipart/form-data" onSubmit ="return validateFileExt();">
                                            <input type="file" name="edit_preview" id="edit_preview">
                                            <input type="submit" name="preview-update" value="Upload" class="button-secondary">
                                        </form>
                                        <br />
                                        <span id="errmsg_previewimg" style="color:red;font-size:12px;font-weight:bold;display: none;"></span>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php _e('Link URL', 'hdflv') ?></th>
                                    <td><input type="text" size="80" name="act_link" value="<?php echo $act_link ?>" id="act_link"/>
                                        <br /><?php _e('Enter the URL to the page/file, if you click on the player', 'hdflv') ?><?php if ($act_link != ''): ?><br /> <span id="alert_linkURL" style="color:red;font-size:12px;font-weight:bold;"></span><?php endif; ?></td>
                                    </tr>
                                </table>
                            </div>
                            <p>
                                <input type="submit" class="button-primary" name="edit_update" value="<?php _e('Update'); ?>" class="button button-highlighted" />
                                <input type="button" onclick="window.location.href='admin.php?page=hdflv'" class="button-secondary" name="cancel" value="<?php _e('Cancel'); ?>" class="button" />
                            </p>
                        </div>
                    </div><!--END Poststuff -->

                </form>

            </div><script> document.getElementById('playlistcreate').style.display = "none";

                function playlistdisplay()
                {
                    document.getElementById('playlistcreate').style.display = "block";
                }
                function playlistclose()
                {
                    document.getElementById('playlistcreate').style.display = "none";
                }

            </script><!--END wrap -->
<?php
                                        }

//  function show_edit() end hear

                                        function show_add() {  // Add A Video
?>						               <div class="wrap">
                                                <script type="text/javascript">

                                                    function t1(t2)
                                                    {
                                                        if(t2.value == "y" ){
                                                            document.getElementById('message').style.display = "none";
                                                            document.getElementById('upload2').style.display = "block";
                                                            document.getElementById('youtube').style.display = "none";
                                                            document.getElementById('customurl').style.display = "none";
                                                        }
                                                        if(t2.value == "c" ){
                                                            document.getElementById('message').style.display = "none";
                                                            document.getElementById('youtube').style.display = "block";
                                                            document.getElementById('upload2').style.display = "none";
                                                            document.getElementById('customurl').style.display = "none";
                                                        }
                                                        if(t2.value == "url" ){
                                                            document.getElementById('message').style.display = "none";
                                                            document.getElementById('customurl').style.display = "block";
                                                            document.getElementById('youtube').style.display = "none";
                                                            document.getElementById('upload2').style.display = "none";
                                                        }
                                                    }


                                                </script>
<?php $this->haflvMenuTab(); ?>
                                                <div id="playlistResponse">

                                                </div>
                                                <h2> <?php _e('Add Video', 'hdflv'); ?> </h2>
                                            <div id="poststuff" class="has-right-sidebar">
                                                <div class="stuffbox" style="float:left;width: 74%;" name="youtube" >
                                                    <h3 class="hndle"><span><input type="radio" name="agree" id="btn1" value="y" onClick="t1(this)" /> Upload file
                                                            <input type="radio" name="agree" id="btn2" value="c" checked ="checked" onClick="t1(this)" /> YouTube URL
                                                            <input type="radio" name="agree" id="btn3" value="url" onClick="t1(this)" /> Custom URL</span></h3>
                                                    <span id="message" style="margin-top:100px;margin-left:300px;color:red;font-size:12px;font-weight:bold;"></span>
                                                    <form method=post>
                                                        <div id="youtube" class="inside" style="margin:15px;">
                                                            <table class="form-table">
                                                                <tr>
                                                                    <th scope="row"><?php _e('Youtube video URL', 'hdflv') ?></th>
                                                                    <td><input type="text" size="50" name="filepath" id="filepath1" onkeyup="generate12(this.value);" />&nbsp;&nbsp<input id="generate" type="submit" name="youtube_media" class="button-primary" value="<?php _e('Generate details', 'hdflv'); ?>" />
                                                                        <br /><?php _e('Enter the youtube video URL', 'hdflv') ?>
                                                                        <br /><?php _e('Example: http://www.youtube.com/watch?v=tTGHCRUdlBs', 'hdflv') ?></td>
                                                                </tr>
                                                            </table>
                                                        </div>

                                                        <div id="customurl" class="inside" style="margin:15px;">
<?php _e('<b>Supported video formats: </b>( MP4, M4V, M4A, MOV, Mp4v or F4V)', 'hdflv') ?>
                                                            <table class="form-table" >
                                                                <tr>
                                                                    <th scope="row"><?php _e('Video URL', 'hdflv') ?></th>
                                                    <td><input type="text" size="50" name="filepath2" id="filepath2" />
                                                        <br /><?php _e('Enter the URL of the video file', 'hdflv') ?>
                                                        <br /><?php echo _e('Example: http://www.domain.com/videopath/video.mp4', 'hdflv') ?>
                                                    </td></tr>
                                                <tr><th scope="row"><?php _e('HD video URL (Optional)', 'hdflv') ?></th>
                                                    <td><input type="text" size="50" name="filepath3" id="filepath3" />
                                                        <br /><?php _e('Enter the URL of the HD video file', 'hdflv') ?>
                                                        <br /><?php echo _e('Example: http://www.domain.com/videopath/hdvideo.mp4', 'hdflv') ?>
                                                    </td>
                                                </tr>
                                                <tr><th scope="row"><?php _e('Thumb image URL', 'hdflv') ?></th>
                                                    <td><input type="text" size="50" name="filepath4" id="filepath4" />
                                                        <br /><?php _e('Enter the URL to the image file', 'hdflv') ?>
                                                        <br /><?php echo _e('Example: http://www.domain.com/imagepath/thumb.png', 'hdflv') ?>
                                                    </td>
                                                </tr>
                                                <tr><th scope="row"><?php _e('Preview image URL (Optional)', 'hdflv') ?></th>
                                                    <td><input type="text" size="50" name="filepath5" id="filepath5" />
                                                        <br /><?php _e('Enter the URL to the Preview image file', 'hdflv') ?>
                                                        <br /><?php echo _e('Example: http://www.domain.com/imagepath/preview.png', 'hdflv') ?>

                                                    </td>
                                                </tr>
                                            </table>
                                        </div>

                                    </form>
                                    <!--   upload file coding -->
                                    <div id="upload2" class="inside" style="width: 72%;" >
<?php _e('<b>Supported video formats: </b>( MP4, M4V, M4A, MOV, Mp4v or F4V)', 'hdflv') ?>
                                        <table class="form-table" style="width:122%;">
                                            <tr id="ffmpeg_disable_new1" name="ffmpeg_disable_new1"><td>Upload Video</td>
                                                <td>
                                                    <div id="f1-upload-form" >
                                                        <form name="normalvideoform" method="post" enctype="multipart/form-data" >
                                                            <input type="file" name="myfile" onchange="enableUpload(this.form.name);" />
                                                            <input type="button" class="button" name="uploadBtn" value="Upload Video" disabled="disabled" onclick="return addQueue(this.form.name,this.form.myfile.value);" />
                                                            <input type="hidden" name="mode" value="video" />
                                                        </form>
                                                    </div>
                                                    <div id="f1-upload-progress" style="display:none">
                                                        <div style="float:left"><img id="f1-upload-image" src="<?php echo get_option('siteurl') . '/wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/images/empty.gif' ?>" alt="Uploading"  style="padding-top:2px"/>
                                                                <label style="padding-top:0px;padding-left:4px;font-size:14px;font-weight:bold;vertical-align:top"  id="f1-upload-filename">PostRoll.flv</label></div>
                                                            <div style="float:right"> <span id="f1-upload-cancel">
                                                                    <a style="float:right;padding-right:10px;" href="javascript:cancelUpload('normalvideoform');" name="submitcancel">Cancel</a>
                                                                </span>
                                                                <label id="f1-upload-status" style="float:right;padding-right:40px;padding-left:20px;">Uploading</label>
                                                                <span id="f1-upload-message" style="float:right;font-size:10px;background:#FFAFAE;">
                                                                    <b>Upload Failed:</b> User Cancelled the upload
                                                                </span></div>


                                                        </div>
                                                    </td></tr>

                                                <tr id="ffmpeg_disable_new2" name="ffmpeg_disable_new1"> <td>Upload HD Video(optional)</td>
                                                    <td>
                                                        <div id="f2-upload-form" >
                                                            <form name="hdvideoform" method="post" enctype="multipart/form-data" >
                                                                <input type="file" name="myfile" onchange="enableUpload(this.form.name);" />
                                                                <input type="button" class="button" name="uploadBtn" value="Upload Video" disabled="disabled" onclick="return addQueue(this.form.name,this.form.myfile.value);" />
                                                                <input type="hidden" name="mode" value="video" />
                                                            </form>
                                                        </div>
                                                        <div id="f2-upload-progress" style="display:none">
                                                            <div style="float:left"><img id="f2-upload-image" src="<?php echo get_option('siteurl') . '/wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/images/empty.gif' ?>" alt="Uploading"  style="padding-top:2px" />
                                                                <label style="padding-top:0px;padding-left:4px;font-size:14px;font-weight:bold;vertical-align:top"  id="f2-upload-filename">PostRoll.flv</label></div>
                                                            <div style="float:right"><span id="f2-upload-cancel">
                                                                    <a style="float:right;padding-right:10px;" href="javascript:cancelUpload('hdvideoform');" name="submitcancel">Cancel</a>

                                                                </span>
                                                                <label id="f2-upload-status" style="float:right;padding-right:40px;padding-left:20px;">Uploading</label>
                                                                <span id="f2-upload-message" style="float:right;font-size:10px;background:#FFAFAE;">
                                                                    <b>Upload Failed:</b> User Cancelled the upload
                                                                </span></div>

                                                        </div>

                                                    </td></tr>



                                                <tr id="ffmpeg_disable_new3" name="ffmpeg_disable_new1"><td>Upload Thumb Image</td><td>
                                                        <div id="f3-upload-form" >
                                                            <form name="thumbimageform" method="post" enctype="multipart/form-data" >
                                                                <input type="file" name="myfile"  onchange="enableUpload(this.form.name);" />
                                                                <input type="button" class="button" name="uploadBtn" value="Upload Image"  disabled="disabled" onclick="return addQueue(this.form.name,this.form.myfile.value);" />
                                                                <input type="hidden" name="mode" value="image" />
                                                            </form>
                                                        </div>
                                                        <div id="f3-upload-progress" style="display:none">
                                                            <div style="float:left"><img id="f3-upload-image" src="<?php echo get_option('siteurl') . '/wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/images/empty.gif' ?>" alt="Uploading" style="padding-top:2px" />
                                                                <label style="padding-top:0px;padding-left:4px;font-size:14px;font-weight:bold;vertical-align:top"  id="f3-upload-filename">PostRoll.flv</label></div>
                                                            <div style="float:right"> <span id="f3-upload-cancel">
                                                                    <a style="float:right;padding-right:10px;" href="javascript:cancelUpload('thumbimageform');" name="submitcancel">Cancel</a>
                                                                </span>
                                                                <label id="f3-upload-status" style="float:right;padding-right:40px;padding-left:20px;">Uploading</label>
                                                                <span id="f3-upload-message" style="float:right;font-size:10px;background:#FFAFAE;">
                                                                    <b>Upload Failed:</b> User Cancelled the upload
                                                                </span></div>

                                                        </div>

                                                    </td></tr>

                                                <tr id="ffmpeg_disable_new4" name="ffmpeg_disable_new1"><td>Upload Preview Image(optional)</td><td>
                                                        <div id="f4-upload-form" >
                                                            <form name="previewimageform" method="post" enctype="multipart/form-data" >
                                                                <input type="file" name="myfile" onchange="enableUpload(this.form.name);" />
                                                                <input type="button" class="button" name="uploadBtn" value="Upload Image" disabled="disabled" onclick="return addQueue(this.form.name,this.form.myfile.value);" />
                                                                <input type="hidden" name="mode" value="image" />
                                                            </form>
                                                        </div>
                                                        <div id="f4-upload-progress" style="display:none">
                                                            <div style="float:left"><img id="f4-upload-image" src="<?php echo get_option('siteurl') . '/wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/images/empty.gif' ?>" alt="Uploading" style="padding-top:2px" />
                                                                <label style="padding-top:0px;padding-left:4px;font-size:14px;font-weight:bold;vertical-align:top"  id="f4-upload-filename">PostRoll.flv</label></div>
                                                            <div style="float:right"><span id="f4-upload-cancel">
                                                                    <a style="float:right;padding-right:10px;" href="javascript:cancelUpload('previewimageform');" name="submitcancel">Cancel</a>
                                                                </span>
                                                                <label id="f4-upload-status" style="float:right;padding-right:40px;padding-left:20px;">Uploading</label>
                                                                <span id="f4-upload-message" style="float:right;font-size:10px;background:#FFAFAE;">
                                                                    <b>Upload Failed:</b> User Cancelled the upload
                                                                </span></div>


                                                        </div>
                                                        <div id="nor"><iframe id="uploadvideo_target" name="uploadvideo_target" src="#" style="width:0;height:0;border:0px solid #fff;"></iframe></div>
                                                    </td>
                                                </tr>

                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <form name="table_options" enctype="multipart/form-data" method="post" id="video_options" onsubmit="return chkbut()">
                                    <div id="poststuff" class="has-right-sidebar videourl">
                                        <input type="hidden" name="normalvideoform-value" id="normalvideoform-value" value=""  />
                                        <input type="hidden" name="hdvideoform-value" id="hdvideoform-value" value="" />
                                        <input type="hidden" name="thumbimageform-value" id="thumbimageform-value"  value="" />
                                        <input type="hidden" name="previewimageform-value" id="previewimageform-value"  value="" />
                                        <input type="hidden" name="youtube-value" id="youtube-value"  value="" />
                                        <input type="hidden" name="customurl" id="customurl1"  value="" />
                                        <input type="hidden" name="customhd" id="customhd1"  value="" />
                                        <input type="hidden" name="customimage" id="customimage"  value="" />
                                        <input type="hidden" name="custompreimage" id="custompreimage"  value="" />
                                        <input type="hidden"  value="<?php $contus = dirname(plugin_basename(__FILE__));
                                            echo SITEURL . '/wp-content/plugins/' . $contus; ?>" id="pluginUrl" name="pluginUrl" />
                                        <div class="inner-sidebar"  >
                                            <div id="submitdiv" class="postbox">
                                                <h3 class="hndle" ><span><?php _e('Playlist', 'hdflv') ?></span></h3>
                                                <div class="inside" >
                                                    <div id="submitpost" class="submitbox">
                                                         
                                    <div class="misc-pub-section"><?php //if(mysql_num_rows($playid1)) {    ?>
                                        <h4><?php _e('Playlist', 'hdflv'); ?>&nbsp;&nbsp;
                                            <a style="cursor:pointer"  onclick="playlistdisplay()"><?php _e('Create New', 'hdflv') ?></a></h4>

                                        <div id="playlistcreate1"><?php _e('Name', 'hdflv'); ?><input type="text" size="20" name="p_name" id="p_name" value="" />
                                            <input type="button" class="button-primary" name="add_pl1" value="<?php _e('Add'); ?>" onclick="return savePlaylist(document.getElementById('p_name') , <?php echo $this->act_vid ?>);" class="button button-highlighted" />
                                            <a style="cursor:pointer" onclick="playlistclose()"><b>Close</b></a></div>

                                        <p id="jaxcat"></p>

                                        <div id="playlistchecklist"><?php get_playlist(); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="post-body" class="has-sidebar"><br>
                        <div id="post-body-content" class="has-sidebar-content">

                            <div class="stuffbox">
                                <h3 class="hndle"><span><?php _e('Enter Video Title / Name', 'hdflv'); ?></span></h3>
                                <div class="inside" style="margin:15px;">
                                    <table class="form-table">
                                        <tr>
                                            <th scope="row"><?php _e('Video Title / Name', 'hdflv') ?></th>
                                            <td><input type="text" size="50" maxlength="200" name="name" id="name" />
                                                <br/><span style="color: red;font-size: 12px;font-weight: bold" id = "Errormsgname"> </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                        </div>
                        <p><input type="submit" name="add_media" class="button-primary" onclick="return validateInput();" value="<?php _e('Add Video', 'hdflv'); ?>" class="button" />
                            <input type="button" onclick="window.location.href='admin.php?page=hdflv'" class="button-secondary" name="cancel" value="<?php _e('Cancel'); ?>" class="button" />

                        </p>

                    </div>
                </div><!--END Poststuff -->

            </form>
            <script type="text/javascript">

                document.getElementById('upload2').style.display = "none";
                document.getElementById('customurl').style.display = "none";
                document.getElementById('name').value = document.getElementById('act0').value;
                document.getElementById('filepath1').value = document.getElementById('act4').value;

            </script>
            <script> document.getElementById('playlistcreate1').style.display = "none";
                document.getElementById('generate').style.visibility  = "hidden";
                function playlistdisplay()
                {
                    document.getElementById('playlistcreate1').style.display = "block";
                }
                function playlistclose()
                {
                    document.getElementById('playlistcreate1').style.display = "none";
                }
                function generate12(str1)
                {
                    var re= /http:\/\/www\.youtube[^"]+/;
                    if(re.test(str1))
                        document.getElementById('generate').style.visibility = "visible";
                    else document.getElementById('generate').style.visibility  = "hidden";
                }
            </script>

        </div><!--END wrap -->
<?php
                                        }

                                        function show_plydel() {
                                            $playlist_name = filter_input(INPUT_GET, 'pname');
                                            $message = hd_delete_playlist($this->act_pid, $playlist_name);
                                            //$this->render_message($message);
                                            $this->mode = 'playlist';
                                            // show playlist
                                            $this->render_admin($this->mode);
                                        }

                                        function show_plyedit() {
                                            // use the same output as playlist
                                            $this->render_admin('playlist');
                                        }

                                        // Edit or update playlst
                                        function show_playlist() {

                                            global $wpdb;

                                            // get the tables
                                            $did = filter_input(INPUT_GET, 'did');
                                            $pid = filter_input(INPUT_GET, 'pid');
                                            $pname = filter_input(INPUT_GET, 'pname');
                                            if (isset($pid)) {
                                                $id = $wpdb->escape($pid);
                                                $update = $wpdb->get_row("SELECT pid , playlist_name FROM " . $wpdb->prefix . "hdflv_playlist WHERE pid = $id ");
                                            } else if (isset($did)) {
                                                hd_delete_playlist($did, $pname);
                                            }
                                            $pluginDirPath = get_option('siteurl') . '/wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/images';

                                            $tables = $wpdb->get_results("SELECT pid , playlist_name , is_pactive FROM " . $wpdb->prefix . "hdflv_playlist ");
?><!-- Manage Playlist --><div class="wrap">
<?php
                                            $this->haflvMenuTab();
                                            $tablehdflv = $wpdb->prefix . 'hdflv';
                                            $tablePlaylist = $wpdb->prefix . 'hdflv_playlist';
                                            $sql = "SHOW TABLES LIKE '$tablehdflv'"; //is table in DB or not
                                            $isTable = $wpdb->query($sql);  //if TRUE THEN 1 ELSE 0
                                            if ($isTable) { //yes already in DB so add fields only
                                                $sql = "SHOW COLUMNS FROM $tablehdflv";
                                                $numOfCol = $wpdb->query($sql);
                                                if ($numOfCol < 8) { // add one col like is_active
                                                }
                                            } else {         // create table
                                            }
                                            $sql = "SHOW TABLES LIKE '$tablePlaylist'"; //is table in DB or not
                                            $isTable = $wpdb->query($sql);  //if TRUE THEN 1 ELSE 0

                                            if ($isTable) { //yes already in DB so add fields only
                                                $sql = "SHOW COLUMNS FROM $tablePlaylist";
                                                $numOfCol = $wpdb->query($sql);
                                                if ($numOfCol < 5) { // add one col like is_pactive
                                                }
                                            }
?>
                                                <h2><?php _e('Manage Playlists', 'hdflv'); ?></h2>
                                                <div class="admin_hdflv_video_info">
                                                    <span class="hint_head">How To Use?</span>
                                                    <p >You can use the following plugin code to display the player with the videos from particular playlist.<br/><br/><strong> [hdplay playlistid=id ]</strong><br/><br/><strong>id</strong> - It is playlist id which will be generated automatically when you create playlist.
                                                </div>
                                                <form id="editplist" name="editplist"  method="post">
                                                    <input type="hidden" id="imagepath" name="imagepath" value="<?php echo $pluginDirPath; ?>" />
                                                    <table class="widefat" cellspacing="0">
                                                        <thead>
                                                            <tr>
                                                                <th style="padding-left: 2%;" scope="col"><?php _e('ID', 'hdflv'); ?></th>
                                                                <th scope="col"><?php _e('Name', 'hdflv'); ?></th>
                                                                <th scope="col" colspan="2"><?php _e('Action'); ?></th>
                                                                <th scope="col" ><?php _e('Status'); ?></th>
                                                            </tr>
                                                        </thead>
<?php
                                            if ($tables) {
                                                $i = 0;

                                                foreach ($tables as $table) {
                                                    if ($i % 2 == 0) {
                                                        echo "<tr class='alternate'>\n";
                                                    } else {
                                                        echo "<tr>\n";
                                                    }
                                                    echo "<th style='padding-left: 2%;' scope=\"row\">$table->pid</th>\n";
                                                    echo "<td><a title='" . stripslashes($table->playlist_name) . "' onclick=\"submitplay($table->pid)\" href=\"?page=hdflvplaylist&pid=$table->pid#playlist_edit\" >" . stripslashes($table->playlist_name) . "</td>\n";
                                                    echo "<td><a title='Edit' href=?page=hdflvplaylist&pid=$table->pid#playlist_edit class=\"edit\">" . __('Edit') . "</a> |
                                                      <a href='?page=hdflvplaylist&pname=$table->playlist_name&did=$table->pid' class=\"delete\" onclick=\"javascript:check=confirm( '" . __("Delete this file ?", 'hdflv') . "');if(check==false) return false;\">" . __('Delete') . "</a></td>\n";
                                                    echo "<td></td>";
                                                    if ($table->is_pactive) {

                                                        echo "<td  style = 'padding-left: 2%;' id='status$table->pid'><img  title='deactive' style='cursor:pointer;' onclick='setVideoStatusOff($table->pid,0,1)'  src=$pluginDirPath/hdflv_active.png /></td>\n";
                                                    } else {

                                                        echo "<td  style = 'padding-left: 2%;' id='status$table->pid'><img  title='active' style='cursor:pointer;' onclick='setVideoStatusOff($table->pid,1,1)' src=$pluginDirPath/hdflv_deactive.png /></td>\n";
                                                    }
                                                    echo '</tr>';
                                                    $i++;
                                                }//foreach end
                                            }//if end hear
                                            else {
                                                echo '<tr><td colspan="7" align="center"><b>' . __('No entries found', 'hdflv') . '</b></td></tr>';
                                            } ?>
                                        </table>
                                        <input type="hidden" name="playid" id="playid" value="" />
                                    </form>
                                </div>
                                <div class="wrap">
                                    <div id="poststuff" class="metabox-holder">
                                        <div id="playlist_edit" class="stuffbox">
                                            <h3 style="cursor: none;"><?php
                                            $pid = filter_input(INPUT_GET, 'pid');
                                            if (!isset($pid))
                                                echo _e('Add Playlist', 'hdflv');
                                            else
                                                echo _e('Update Playlist', 'hdflv');
?></h3>
                                        <div class="inside">
                                            <form id="addplist"  action = "<?php echo $_SERVER['PHP_SELF'] . '?page=hdflvplaylist'; ?>"  method="post" >
                                                <input type="hidden" value="<?php echo $this->act_pid ?>" name="p_id"/>
                                                <p><?php _e('Name:', 'hdflv'); ?><br/>
                                                    <input type="text"  value="<?php echo $update->playlist_name ?>" name="p_name" id="p_name"/></p>
                                                <span style="color: red;" id = "bind_playlist_error"></span>
                                                <div class="submit" style="margin: 0px;padding: 4px;">
<?php
                                            if (!isset($pid))
                                                echo '<input type="submit"  name="add_playlist" value="' . __('Add Playlist', 'hdflv') . '" class="button-primary" onClick="return validatePlaylist();"/>';
                                            else
                                                echo '<input type="submit" name="update_playlist" value="' . __('Update Playlist', 'hdflv') . '" class="button-primary" />';
?>
                                            <input type="button" onclick="window.location.href='?page=hdflvplaylist'" name="cancel" value="<?php _e('Cancel', 'hdflv'); ?>"  />
                                        </div>
                                    </form>
                                    <script type="text/javascript">
                                        function validatePlaylist()
                                        {
                                            var testPlaylist;
                                            testPlaylist = document.getElementById('p_name').value;
                                            if(testPlaylist.trim()==''){
                                                document.getElementById("bind_playlist_error").innerHTML='Please enter playlist name';
                                                return false;
                                            }
                                        }
                                    </script>
                                </div>
                            </div>
                        </div>
                    </div>
<?php
                                        }

// Display sort form filter
// Display playlist form filter
                                        function playlist_filter($plfilter) {
                                            global $wpdb;
?>
                                            <select name="plfilter">
                                                <option value='0'>All</option>
<?php
                                            $dbresults = $wpdb->get_results(" SELECT * FROM " . $wpdb->prefix . "hdflv_playlist ");
                                            if ($dbresults) {
                                                foreach ($dbresults as $dbresult) :
                                                    echo '<option value="' . $dbresult->pid . '"';
                                                    if ($plfilter == $dbresult->pid)
                                                        echo 'selected="selected"';
                                                    echo '>' . $dbresult->playlist_name . '</option>';
                                                endforeach;
                                            }
?>
                                </select>
            <?php
                                        }

                                    }
            ?>
                                    <input type="hidden" name="app_wp_token" id="app_wp_token" value="<?php echo $_SESSION['app_wp_token']; ?>" />
                                    <input type="hidden" name="plugin_name" id="plugin_name" value="<?php echo $contus; ?>" />