<?php
/*
  Name: Contus HD FLV Player
  Plugin URI: http://www.apptha.com/category/extension/Wordpress/HD-FLV-Player-Plugin/
  Description: Player main function file.
  Version: 2.6
  Author: Apptha
  Author URI: http://www.apptha.com
  License: GPL2
 */

ob_start();
require_once( dirname(__FILE__) . '/hdflv-config.php');
$name = strip_tags(filter_input(INPUT_GET, 'name'));
$media = filter_input(INPUT_GET, 'media');
if (isset($name)) {
    return hd_ajax_add_playlist($name, $media);
}
//Function used for rendering error message
function hdflv_render_error($message) {
?>
    <div class="wrap"><h2>&nbsp;</h2>
        <div class="error" id="error">
            <p><strong><?php echo $message ?></strong></p>
        </div></div>
<?php
}
//Function used for getting playlist by ID
function get_playlistname_by_ID($pid = 0) {
    global $wpdb;
    $pid = (int) $pid;
    $result = $wpdb->get_var("SELECT playlist_name FROM " . $wpdb->prefix . "hdflv_playlist WHERE pid = $pid AND is_pactive = 1 ");
    return $result;
}

//Function used for get playlist order
function get_sortorder($mediaid = 0, $pid) {
    global $wpdb;
    $pid = intval($pid);
    $mediaid = (int) $mediaid;
    $result = $wpdb->get_var("SELECT sorder FROM " . $wpdb->prefix . "hdflv_med2play WHERE media_id = $mediaid and playlist_id= $pid");
    return $result;
}

//Function used for get complete URL of file name
function wpt_filename($urlpath) {
    $filename = substr(($t = strrchr($urlpath, '/')) !== false ? $t : '', 1);
    return $filename;
}

//Function used for getting playlist output
function get_playlist_for_dbx($mediaid) {

    global $wpdb;
    $playids = $wpdb->get_col("SELECT pid FROM " . $wpdb->prefix . "hdflv_playlist WHERE is_pactive = 1");
    $mediaid = (int) $mediaid;
    $checked_playlist = $wpdb->get_col("
		SELECT playlist_id,sorder
		FROM " . $wpdb->prefix . "hdflv_playlist," . $wpdb->prefix . "hdflv_med2play
		WHERE " . $wpdb->prefix . "hdflv_med2play.playlist_id = pid AND " . $wpdb->prefix . "hdflv_med2play.media_id = '$mediaid' AND is_pactive = 1");

    if (count($checked_playlist) == 0)
        $checked_playlist[] = 0;

    $result = array();
    // create an array with playid, checked status and name
    if (is_array($playids)) {
        foreach ($playids as $playid) {
            $result[$playid]['playid'] = $playid;
            $result[$playid]['checked'] = in_array($playid, $checked_playlist);
            $result[$playid]['name'] = get_playlistname_by_ID($playid);
            $result[$playid]['sorder'] = get_sortorder($mediaid, $playid);
        }
    }

    $hiddenarray = array();
    echo "<table>";
    foreach ($result as $playlist) {

        $hiddenarray[] = $playlist['playid'];
        echo '<tr><td style="font-size:11px"><label for="playlist-' . $playlist['playid']
        . '" class="selectit"><input value="' . $playlist['playid']
        . '" type="checkbox" name="playlist[]" id="playlist-' . $playlist['playid']
        . '"' . ($playlist['checked'] ? ' checked="checked"' : "") . '/> ' . wp_specialchars($playlist['name']) . "</label></td ></tr>
            ";
    }
    echo "</table>";
    $comma_separated = implode(",", $hiddenarray);
    echo "<input type=hidden name=hid value = $comma_separated >";
}

//calling via Ajax Function used for getting playlist output
function get_ajax_playlist_for_dbx() {

    global $wpdb;
    $playids = $wpdb->get_col("SELECT pid FROM " . $wpdb->prefix . "hdflv_playlist AND is_pactive = 1 ");
    $checked_playlist = $wpdb->get_col("
		SELECT playlist_id,sorder
		FROM " . $wpdb->prefix . "hdflv_playlist," . $wpdb->prefix . "hdflv_med2play
		WHERE " . $wpdb->prefix . "hdflv_med2play.playlist_id = pid AND AND is_pactive = 1");

    if (count($checked_playlist) == 0)
        $checked_playlist[] = 0;

    $result = array();
    // create an array with playid, checked status and name
    if (is_array($playids)) {
        foreach ($playids as $playid) {
            $result[$playid]['playid'] = $playid;
            $result[$playid]['checked'] = in_array($playid, $checked_playlist);
            $result[$playid]['name'] = get_playlistname_by_ID($playid);
            $result[$playid]['sorder'] = get_sortorder($mediaid, $playid);
        }
    }

    $hiddenarray = array();
    echo "<table>";
    foreach ($result as $playlist) {

        $hiddenarray[] = $playlist['playid'];
        echo '<tr><td style="font-size:11px"><label for="playlist-' . $playlist['playid']
        . '" class="selectit"><input value="' . $playlist['playid']
        . '" type="checkbox" name="playlist[]" id="playlist-' . $playlist['playid']
        . '"' . ($playlist['checked'] ? ' checked="checked"' : "") . '/> ' . wp_specialchars($playlist['name']) . "</label></td ></tr>
            ";
    }
    echo "</table>";
    $comma_separated = implode(",", $hiddenarray);
    echo "<input type=hidden name=hid value = $comma_separated >";
}

//Function used to get playlist
function get_playlist() {

    global $wpdb;

// get playlist ID's
    $playids = $wpdb->get_col("SELECT pid FROM " . $wpdb->prefix . "hdflv_playlist WHERE is_pactive = 1  ");
    $mediaid = (int) $mediaid;

    if (count($checked_playlist) == 0)
        $checked_playlist[] = 0;

    $result = array();

// create an array with playid, checked status and name
    if (is_array($playids)) {
        foreach ($playids as $playid) {
            $result[$playid]['playid'] = $playid;
            $result[$playid]['checked'] = in_array($playid, $checked_playlist);
            $result[$playid]['name'] = get_playlistname_by_ID($playid);
            $result[$playid]['sorder'] = get_sortorder($mediaid, $playid);
        }
    }

    $hiddenarray = array();
    echo "<table>";
    foreach ($result as $playlist) {

        $hiddenarray[] = $playlist['playid'];
        echo '<tr><td style="font-size:11px"><label for="playlist-' . $playlist['playid']
        . '" class="selectit"><input value="' . $playlist['playid']
        . '" type="checkbox" name="playlist[]" id="playlist-' . $playlist['playid']
        . '"' . ($playlist['checked'] ? ' checked="checked"' : "") . '/> ' . wp_specialchars($playlist['name']) . "</label></td ></tr>
            ";
    }
    echo "</table>";
    $comma_separated = implode(",", $hiddenarray);
    echo "<input type=hidden name=hid value = $comma_separated >";
}

//Function used for adding videos
function hd_add_media($wptfile_abspath, $wp_urlpath) {

    global $wpdb;
    $uploadPath = $wpdb->get_col("SELECT upload_path FROM " . $wpdb->prefix . "hdflv_settings");
    $uPath = $uploadPath[0];
    $imageUrl = filter_input(INPUT_POST, 'youtube-value');
    if ($imageUrl) {
        $imageUrl = explode('v=', $imageUrl);

        $imageUrl = explode('&', $imageUrl[1]);
        $imageUrl = $imageUrl[0];
    }
    $hid = filter_input(INPUT_POST, 'hid');
    $pieces = explode(",", $hid);
    $video1 = filter_input(INPUT_POST, 'normalvideoform-value');
    $video2 = filter_input(INPUT_POST, 'hdvideoform-value');
    $img1 = filter_input(INPUT_POST, 'thumbimageform-value');
    $img2 = filter_input(INPUT_POST, 'previewimageform-value');
    $img3 = filter_input(INPUT_POST, 'customimage');
    $pre_image = filter_input(INPUT_POST, 'custompreimage');

    if ($uPath != '') {
        $wp_urlpath = $wp_urlpath . $uPath . '/';
    } else {
        $wp_urlpath = $wp_urlpath . '/';
    }


// Get input informations from POST
    $sorder = filter_input(INPUT_POST, 'sorder', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY);
    $act_name = trim(filter_input(INPUT_POST, 'name'));
    $act_name = preg_replace("/[^a-zA-Z0-9\/-_\\s]/", '', $act_name);
    $youtubevalue = filter_input(INPUT_POST, 'youtube-value');
    if ($youtubevalue != '') {

        $act_filepath = addslashes(trim(filter_input(INPUT_POST, 'youtube-value')));
    } else {

        $act_filepath = addslashes(trim(filter_input(INPUT_POST, 'customurl')));
    }

    $act_filepath2 = trim(filter_input(INPUT_POST, 'customhd'));
    $act_image = addslashes(trim(filter_input(INPUT_POST, 'urlimage')));
    $act_link = '';
    $act_playlist = filter_input(INPUT_POST, 'playlist', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY);

    $act_tags = addslashes(trim(filter_input(INPUT_POST, 'act_tags')));

    if (!empty($act_filepath)) {
        $ytb_pattern = "@youtube.com\/watch\?v=([0-9a-zA-Z_-]*)@i";
        if (preg_match($ytb_pattern, stripslashes($act_filepath), $match)) {
            $youtube_data = hd_GetSingleYoutubeVideo($match[1]);
            $act_image = "http://img.youtube.com/vi/" . $imageUrl . "/0.jpg";
            $act_opimage = "http://img.youtube.com/vi/" . $imageUrl . "/2.jpg";
            if ($youtube_data) {
                if ($act_name == '')
                    $act_name = addslashes($youtube_data['title']);
                if ($act_image == '')
                    $act_image = $youtube_data['thumbnail_url'];
                if ($act_link == '')
                    $act_link = $act_filepath;
                $act_filepath = preg_replace('/^(http)(http)s?:\/+/i', '', $act_filepath);
            } else
                hdflv_render_error(__('Could not retrieve Youtube video information', 'hdflv'));
        }else {
            $act_hdpath = $act_filepath2;
            $act_image = $img3;
            $act_opimage = $pre_image;
        }
    } else {
        if ($video1 != '')
            $act_filepath = $wp_urlpath . "$video1";
        if ($video2 != '')
            $act_hdpath = $wp_urlpath . "$video2";
        if ($img1 != '')
            $act_image = $wp_urlpath . "$img1";
        if ($img2 != '')
            $act_opimage = $wp_urlpath . "$img2";
    }

    $insert_video = $wpdb->query(" INSERT INTO " . $wpdb->prefix . "hdflv ( name, file, hdfile , image, opimage , link )
	VALUES ( '$act_name',  '$act_filepath','$act_hdpath', '$act_image', '$act_opimage', '$act_link' )");
    $videoName = substr($act_name, 0, 30) . ' ...';
    if ($insert_video != 0) {
        $video_aid = $wpdb->insert_id;  // get index_id
        $tags = explode(',', $act_tags);

        render_message(__('Video file', 'hdflv') . ' ' . ' \'' . $videoName . '\' ' . __(' added successfully', 'hdflv'));
    }

    // Add any link to playlist?
    if ($video_aid && is_array($act_playlist)) {
        $add_list = array_diff($act_playlist, array());

        if ($add_list) {
            foreach ($add_list as $new_list) {
                $new_list1 = $new_list - 1;
                if ($sorder[$new_list1] == '')
                    $sorder[$new_list1] = '0';
                $wpdb->query(" INSERT INTO " . $wpdb->prefix . "hdflv_med2play (media_id,playlist_id,sorder) VALUES ($video_aid, $new_list, $sorder[$new_list1])");
            }
        }

        $i = 0;
        foreach ($pieces as $new_list) {
            $wpdb->query(" UPDATE " . $wpdb->prefix . "hdflv_med2play SET sorder= '$sorder[$i]' WHERE media_id = '$video_aid' and playlist_id = '$new_list'");
            $i++;
        }
    }
    return;
}

//Function for updating thumb image
function hd_update_thumb($wptfile_abspath, $showPath, $updateID) {

    global $wpdb;
    $uploadPath = $wpdb->get_col("SELECT upload_path FROM " . $wpdb->prefix . "hdflv_settings");
    $uPath = $uploadPath[0];
    $uploadStatus = '';

    if ($uPath != '') {
        $wp_urlpath = $wptfile_abspath . $uPath . '/';
    } else {
        $wp_urlpath = $wptfile_abspath . '/';
    }
    if ($_FILES ["edit_thumb"]["error"] == 0 && $_FILES ["edit_thumb"]["type"] == 'image/jpeg' || $_FILES ["edit_thumb"]["type"] == 'image/gif' || $_FILES ["edit_thumb"]["type"] == 'image/pjpeg' || $_FILES ["edit_thumb"]["type"] == 'image/png') {
        $cname = $_FILES["edit_thumb"]["name"];
        $tname = $_FILES["edit_thumb"]["tmp_name"];
        $random_digit = rand(0000, 9999);
        $new_file_name = $random_digit . '_' . $cname;
        if (move_uploaded_file($tname, $wp_urlpath . $new_file_name)) {
            $uploadStatus = true;
            $updated_thumb = $new_file_name;
        }
    } else {
        $uploadStatus = false;
        hdflv_render_error(__('Invalid File Format Uploaded', 'hdflv'));
    }
    if ($uPath != '') {
        $wp_showPath = $showPath . $uPath . '/';
    } else {
        $wp_showPath = $showPath . '/';
    }
    if ($uploadStatus == '1') {
        $updated_thumb_value = $wp_showPath . $updated_thumb;
        $updateID = intval($updateID);
        $wpdb->query(" UPDATE " . $wpdb->prefix . "hdflv SET image= '$updated_thumb_value' WHERE vid = '$updateID'");
        render_message(__('Image Update Successfully', 'hdflv'));
        return;
    }
}

//Function for updating preview image
function hd_update_preview($wptfile_abspath, $showPath, $updateID) {

    global $wpdb;
    $uploadPath = $wpdb->get_col("SELECT upload_path FROM " . $wpdb->prefix . "hdflv_settings");
    $uPath = $uploadPath[0];
    $uploadStatus = '';

    if ($uPath != '') {
        $wp_urlpath = $wptfile_abspath . $uPath . '/';
    } else {
        $wp_urlpath = $wptfile_abspath . '/';
    }

    if ($_FILES ["edit_preview"]["error"] == 0 && $_FILES ["edit_preview"]["type"] == 'image/jpeg' || $_FILES ["edit_preview"]["type"] == 'image/gif' || $_FILES ["edit_preview"]["type"] == 'image/pjpeg' || $_FILES ["edit_preview"]["type"] == 'image/png') {
        $cname = $_FILES["edit_preview"]["name"];
        $tname = $_FILES["edit_preview"]["tmp_name"];
        $random_digit = rand(0000, 9999);
        $new_file_name = $random_digit . '_' . $cname;
        if (move_uploaded_file($tname, $wp_urlpath . $new_file_name)) {
            $uploadStatus = true;
            $updated_preview = $new_file_name;
        }
    } else {
        $uploadStatus = false;
        hdflv_render_error(__('Invalid File Format Uploaded', 'hdflv'));
    }
    if ($uPath != '') {
        $wp_showPath = $showPath . $uPath . '/';
    } else {
        $wp_showPath = $showPath . '/';
    }
    $updateID = intval($updateID);
    if ($uploadStatus == '1') {
        $updated_preview_value = $wp_showPath . $updated_preview;
        $wpdb->query(" UPDATE " . $wpdb->prefix . "hdflv SET opimage= '$updated_preview_value' WHERE vid = '$updateID'");
        render_message(__('Image Update Successfully', 'hdflv'));
        return;
    }
}

//Function used for retrieving YOUTUBE url
function youtubeurl() {
    $act_filepath = addslashes(trim(filter_input(INPUT_POST, 'filepath')));
    if (!empty($act_filepath)) {
        $ytb_pattern = "@youtube.com\/watch\?v=([0-9a-zA-Z_-]*)@i";
        if (preg_match($ytb_pattern, stripslashes($act_filepath), $match)) {
            $youtube_data = hd_GetSingleYoutubeVideo($match[1]);
            if ($youtube_data) {
                $act[0] = addslashes($youtube_data['title']);
                $act[3] = $youtube_data['thumbnail_url'];
                $act[4] = $act_filepath;
            } else
                hdflv_render_error(__('Could not retrieve Youtube video information', 'hdflv'));
        }else {
            $act[4] = $act_filepath;
            hdflv_render_error(__('URL entered is not a valid Youtube Url', 'hdflv'));
        }
        return $act;
    }
}

//Function used for updating media data(File path,name,etc..)
function hd_update_media($media_id) {
    global $wpdb;
    $pieces = explode(",", filter_input(INPUT_POST, 'hid'));
    $sorder = filter_input(INPUT_POST, 'sorder', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY);
    $act_name = strip_tags(trim(filter_input(INPUT_POST, 'act_name')));
    $act_name = preg_replace("/[^a-zA-Z0-9\/-_\\s]/", '', $act_name);
    $act_filepath = addslashes(trim(filter_input(INPUT_POST, 'act_filepath')));
    $act_image = addslashes(trim(filter_input(INPUT_POST, 'act_image')));
    $act_hdpath = addslashes(trim(filter_input(INPUT_POST, 'act_hdpath')));
    $act_link = addslashes(trim(filter_input(INPUT_POST, 'act_link')));
    $act_opimg = addslashes(trim(filter_input(INPUT_POST, 'act_opimg')));
    $media_id = intval($media_id);
    $act_playlist = filter_input(INPUT_POST, 'playlist', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY);
    // Update tags
    $act_tags = addslashes(trim(filter_input(INPUT_POST, 'act_tags')));
    $tags = explode(',', $act_tags);
    if (!$act_playlist)
        $act_playlist = array();
    if (empty($act_autostart))
        $act_autostart = 0;
    // Read the old playlist status
    $old_playlist = $wpdb->get_col(" SELECT playlist_id FROM " . $wpdb->prefix . "hdflv_med2play WHERE media_id = $media_id");
    if (!$old_playlist) {
        $old_playlist = array();
    } else {
        $old_playlist = array_unique($old_playlist);
    }

    $delete_list = array_diff($old_playlist, $act_playlist);
    if ($delete_list) {
        foreach ($delete_list as $del) {
            $wpdb->query(" DELETE FROM " . $wpdb->prefix . "hdflv_med2play WHERE playlist_id = $del AND media_id = $media_id ");
        }
    }
    $add_list = array_diff($act_playlist, $old_playlist);
    if ($add_list) {
        foreach ($add_list as $new_list) {
            $new_list1 = $new_list - 1;
            if ($sorder[$new_list1] == '')
                $sorder[$new_list1] = '0';
            $wpdb->query(" INSERT INTO " . $wpdb->prefix . "hdflv_med2play (media_id, playlist_id,sorder) VALUES ($media_id, $new_list, $sorder[$new_list1])");
        }
    }
    $i = 0;
    foreach ($pieces as $new_list) {
        $wpdb->query(" UPDATE " . $wpdb->prefix . "hdflv_med2play SET sorder= '$sorder[$i]' WHERE media_id = '$media_id' and playlist_id = '$new_list'");
        $i++;
    }
    if (!empty($act_filepath)) {
        $result = $wpdb->query("UPDATE " . $wpdb->prefix . "hdflv SET name = '$act_name',  file='$act_filepath' ,hdfile='$act_hdpath' , image='$act_image' , opimage='$act_opimg' , link='$act_link'  WHERE vid = '$media_id' ");
    }
    render_message(__('Update Successfully', 'hdflv'));
    return;
}

//Function used for deleting media(video)
function hd_delete_media($act_vid, $deletefile, $video_name) {
    global $wpdb;
    $act_vid = intval($act_vid);
    if ($deletefile) {

        $act_videoset = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "hdflv WHERE vid = $act_vid ");

        $act_filename = wpt_filename($act_videoset->file);
        $abs_filename = str_replace(trailingslashit(get_option('siteurl')), ABSPATH, trim($act_videoset->file));
        if (!empty($act_filename)) {

            $wpt_checkdel = @unlink($abs_filename);
            if (!$wpt_checkdel)
                hdflv_render_error(__('Error in deleting file', 'hdflv'));
        }

        $act_filename = wpt_filename($act_videoset->image);
        $abs_filename = str_replace(trailingslashit(get_option('siteurl')), ABSPATH, trim($act_videoset->image));
        if (!empty($act_filename)) {

            $wpt_checkdel = @unlink($abs_filename);
            if (!$wpt_checkdel)
                hdflv_render_error(__('Error in deleting file', 'hdflv'));
        }
    }

    //TODO: The problem of this routine : if somebody change the path, after he uploaded some files
    $wpdb->query("DELETE FROM " . $wpdb->prefix . "hdflv_med2play WHERE media_id = $act_vid");

    $delete_video = $wpdb->query("DELETE FROM " . $wpdb->prefix . "hdflv WHERE vid = $act_vid");
    if (!$delete_video)
        hdflv_render_error(__('Error in deleting media file', 'hdflv'));

    if (empty($text))
        render_message(__('Video file', 'hdflv') . ' \'' . $video_name . '\' ' . __('deleted successfully', 'hdflv'));

    return;
}

//calling via Ajax to add playlist    hdflvscript.js file this is calling
function hd_ajax_add_playlist($name, $media) {

    global $wpdb;

    // Get input informations from POST
    $p_name = strip_tags(addslashes(trim($name)));
    $p_description = '';
    $p_playlistorder = 0;
    if (empty($p_playlistorder))
        $p_playlistorder = "ASC";

    $playlistname1 = "select playlist_name from " . $wpdb->prefix . "hdflv_playlist where playlist_name='" . $p_name . "'";
    $planame1 = $wpdb->get_var($playlistname1);
    if (!empty($planame1)) {
        hdflv_render_error(__('Failed, Playlist name already exist', 'hdflv')) . get_playlist_for_dbx($media);
        return;
    }

    // Add playlist in db
    if (!empty($p_name)) {
        $videoData = array ('playlist_name' => $p_name, 'playlist_desc' => $p_description, 'playlist_order' => $p_playlistorder );   
        if ( $wpdb->insert( $wpdb->prefix . "hdflv_playlist", $videoData )) {
            $pid = $wpdb->insert_id;  // get index_id
            render_message(__('Playlist', 'hdflv') . ' ' . $name . __(' added successfully', 'hdflv')) . get_playlist_for_dbx($media);
        }
    }

    return;
}

//Function used to add playlist
function hd_add_playlist() {
    global $wpdb;

    // Get input informations from POST
    $p_name = strip_tags(trim(filter_input(INPUT_POST, 'p_name')));
    $p_name = preg_replace("/[^a-zA-Z0-9\/-_\\s]/", '', $p_name);
    $p_description = strip_tags(trim(filter_input(INPUT_POST, 'p_description')));
    $p_playlistorder = filter_input(INPUT_POST, 'sortorder');
    if (empty($p_playlistorder))
        $p_playlistorder = "ASC";

    $playlistname1 = "select playlist_name from " . $wpdb->prefix . "hdflv_playlist where playlist_name='" . $p_name . "'";
    $planame1 = $wpdb->get_var($playlistname1);
    if (!empty($planame1)) {
        hdflv_render_error(__('Failed, Playlist name already exist', 'hdflv'));
        return;
    }

    // Add playlist in db
    if (!empty($p_name)) {
      $insert_plist = $wpdb->query(" INSERT INTO " . $wpdb->prefix . "hdflv_playlist (playlist_name, playlist_desc, playlist_order) VALUES ('$p_name', '$p_description', '$p_playlistorder')");
        if ($insert_plist != 0) {
            $pid = $wpdb->insert_id;  // get index_id
            render_message(__('Playlist', 'hdflv') . ' ' . $p_name . __(' added successfully', 'hdflv'));
        }
    }

    return;
}

//Function used to update playlist
function hd_update_playlist() {
    global $wpdb;

    // Get input informations from POST
    $p_id = (int) (filter_input(INPUT_POST, 'p_id'));
    $p_name = strip_tags(trim(filter_input(INPUT_POST, 'p_name')));
    $p_name = preg_replace("/[^a-zA-Z0-9\/_-\s]/", '', $p_name);
    $p_description = strip_tags(trim(filter_input(INPUT_POST, 'p_description')));
    $p_playlistorder = filter_input(INPUT_POST, 'sortorder');
    $pager = filter_input(INPUT_POST, 'page');
    $mode = filter_input(INPUT_POST, 'mode');

    $siteUrl = 'admin.php?page=hdflvplaylist' . $pager . '&mode=' . $mode . '&sus=1';
    if (!empty($p_name)) {
        $wpdb->query(" UPDATE " . $wpdb->prefix . "hdflv_playlist SET playlist_name = '$p_name', playlist_desc = '$p_description', playlist_order = '$p_playlistorder' WHERE pid = '$p_id' ");

        render_message(__('Playlist', 'hdflv') . ' ' . $p_name . ' ' . __('Update Successfully', 'hdflv'));
    }
    return;
}

//Function used to delete playlist
function hd_delete_playlist($act_pid, $playlist_name) {
    global $wpdb;
    $act_pid = $wpdb->escape($act_pid);
    $act_pid = intval($act_pid);
    $delete_plist = $wpdb->query("DELETE FROM " . $wpdb->prefix . "hdflv_playlist WHERE pid = $act_pid");

    $delete_plist2 = $wpdb->query("DELETE FROM " . $wpdb->prefix . "hdflv_med2play WHERE playlist_id = $act_pid");

    render_message(__('Playlist', 'hdflv') . ' \'' . $playlist_name . '\' ' . __('deleted successfully', 'hdflv'));

    return;
}

//Function used for rendering message
function render_message($message, $timeout = 0) {
?>

    <div  style="margin:0px ;" class="fade updated" id="message" onclick="this.parentNode.removeChild (this)">
        <p><strong><?php echo $message ?></strong></p>
    </div>
<?php
}

//Function used to return YOUTUBE single video
function hd_GetSingleYoutubeVideo($youtube_media) {
    if ($youtube_media == '')
        return;
    $url = 'http://gdata.youtube.com/feeds/api/videos/' . $youtube_media;
    $ytb = hd_ParseYoutubeDetails(hd_GetYoutubePage($url));
    return $ytb[0];
}

//Function used for parsing xml fron youtube
function hd_ParseYoutubeDetails($ytVideoXML) {

    // Create parser, fill it with xml then delete it
    $yt_xml_parser = xml_parser_create();
    xml_parse_into_struct($yt_xml_parser, $ytVideoXML, $yt_vals);
    xml_parser_free($yt_xml_parser);
    // Init individual entry array and list array
    $yt_video = array();
    $yt_vidlist = array();

    // is_entry tests if an entry is processing
    $is_entry = true;
    // is_author tests if an author tag is processing
    $is_author = false;
    foreach ($yt_vals as $yt_elem) {

        // If no entry is being processed and tag is not start of entry, skip tag
        if (!$is_entry && $yt_elem['tag'] != 'ENTRY')
            continue;
        // Processed tag
        switch ($yt_elem['tag']) {
            case 'ENTRY' :
                if ($yt_elem['type'] == 'open') {
                    $is_entry = true;
                    $yt_video = array();
                } else {
                    $yt_vidlist[] = $yt_video;
                    $is_entry = false;
                }
                break;
            case 'ID' :
                $yt_video['id'] = substr($yt_elem['value'], -11);
                $yt_video['link'] = $yt_elem['value'];
                break;
            case 'PUBLISHED' :
                $yt_video['published'] = substr($yt_elem['value'], 0, 10) . ' ' . substr($yt_elem['value'], 11, 8);
                break;
            case 'UPDATED' :
                $yt_video['updated'] = substr($yt_elem['value'], 0, 10) . ' ' . substr($yt_elem['value'], 11, 8);
                break;
            case 'MEDIA:TITLE' :
                $yt_video['title'] = $yt_elem['value'];
                break;
            case 'MEDIA:KEYWORDS' :
                $yt_video['tags'] = $yt_elem['value'];
                break;
            case 'MEDIA:DESCRIPTION' :
                $yt_video['description'] = $yt_elem['value'];
                break;
            case 'MEDIA:CATEGORY' :
                $yt_video['category'] = $yt_elem['value'];
                break;
            case 'YT:DURATION' :
                $yt_video['duration'] = $yt_elem['attributes'];
                break;
            case 'MEDIA:THUMBNAIL' :
                if ($yt_elem['attributes']['HEIGHT'] == 240) {
                    $yt_video['thumbnail'] = $yt_elem['attributes'];
                    $yt_video['thumbnail_url'] = $yt_elem['attributes']['URL'];
                }
                break;
            case 'YT:STATISTICS' :
                $yt_video['viewed'] = $yt_elem['attributes']['VIEWCOUNT'];
                break;
            case 'GD:RATING' :
                $yt_video['rating'] = $yt_elem['attributes'];
                break;
            case 'AUTHOR' :
                $is_author = ($yt_elem['type'] == 'open');
                break;
            case 'NAME' :
                if ($is_author)
                    $yt_video['author_name'] = $yt_elem['value'];
                break;
            case 'URI' :
                if ($is_author)
                    $yt_video['author_uri'] = $yt_elem['value'];
                break;
            default :
        }
    }

    unset($yt_vals);

    return $yt_vidlist;
}

//Function used get content of url given using curl
function hd_GetYoutubePage($url) {

    // Try to use curl first
    if (function_exists('curl_init')) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $xml = curl_exec($ch);
        curl_close($ch);
    }
    // If not found, try to use file_get_contents (requires php > 4.3.0 and allow_url_fopen)
    else {
        $xml = @file_get_contents($url);
    }

    return $xml;
}
?>
<style type="text/css">
    /*             =============================================================================*/
    .pagination {
        clear:both;
        padding:3px 0;
        position:relative;
        font-size:11px;
        line-height:13px;
    }

    .pagination span, .pagination a {
        display:block;
        float:left;
        margin: 2px 2px 2px 0;
        padding:6px 9px 5px 9px;
        text-decoration:none;
        width:auto;
        color: #333;

        background-color: #EEE;
        border: 1px solid #B1B1B1;
        font-family: verdana;
        font-size: 11px;
    }

    .pagination a:hover{
        color:#fff;
        background: #3279BB;
    }

    .pagination .current{
        padding:6px 9px 5px 9px;
        background: #3279BB;
        color:#fff;
    }

</style> 
<?php

function pagination($pages = '', $range = '') {
    $showitems = ($range * 1) + 1;
    $nextvalue = filter_input(INPUT_GET, 'paged');
    if (!$nextvalue
        )$nextvalue = 1;
    global $paged;
    if (empty($paged))
        $paged = 0;
    if ($pages == '') {
        global $wp_query;
        $pages = $wp_query->max_num_pages;
        if (!$pages) {
            $pages = 1;
        }
    }
    if (1 != $pages) {
        echo "<div class=\"pagination\"><span>Page " . $nextvalue . " of " . intval($pages) . "</span>";
        $lastVal = intval($pages);
        $diffis = intval($pages) - $nextvalue;
        if ($diffis <= 2 && $nextvalue != 1) {
            echo "<a href='" . get_pagenum_link(1) . "'>&laquo; First</a>";
        }
        if ($nextvalue > 1) {
            $previous = filter_input(INPUT_GET, 'paged') - 1;

            echo "<a href='" . get_pagenum_link() . "&paged=$previous#videostableid'>&lsaquo; Previous</a>";
        }
        $paged = filter_input(INPUT_GET, 'paged');
        if (!$paged || $paged == 1) {
            $i = 1;
            $pages = 2;
            $showitems = 0;
        } else {
            $i = $paged;
        }
        echo ($paged == $i) ? "<span class=\"current\">" . $i . "</span>" : "<a href='" . get_pagenum_link($i) . "#videostableid' class=\"inactive\">" . $i . "</a>";


        if ($diffis > 1) {

            echo "<a href=\"" . get_pagenum_link(++$nextvalue) . "#videostableid\">Next &rsaquo;</a>";
        }
        if ($diffis > 0) {
            echo "<a href='" . get_pagenum_link($lastVal) . "#videostableid'>Last &raquo;</a>";
        }
        echo "</div>\n";
    }
}
?>