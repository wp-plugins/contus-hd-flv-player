<?php
/*
 * version : 1.3
 * Edited by : John THomas
 * Email : johnthomas@contus.in
 * Purpose : Common functions needed throughout the plugin
 * Path:/wp-content/plugins/contus-hd-flv-player/function.php
 * Date:13/1/11
 *
 */
require_once( dirname(__FILE__) . '/hdflv-config.php');
if(isset($_GET['name'])){
    return hd_ajax_add_playlist($_GET['name'], $_GET['media']);
}

//Function used for rendering error message
function render_error($message) {
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
    $result = $wpdb->get_var("SELECT playlist_name FROM " . $wpdb->prefix . "hdflv_playlist WHERE pid = $pid ");

    return $result;
}

//Function used for get playlist order
function get_sortorder($mediaid = 0, $pid) {
    global $wpdb;

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
    $playids = $wpdb->get_col("SELECT pid FROM " . $wpdb->prefix . "hdflv_playlist");
    $mediaid = (int) $mediaid;
    $checked_playlist = $wpdb->get_col("
		SELECT playlist_id,sorder
		FROM " . $wpdb->prefix . "hdflv_playlist," . $wpdb->prefix . "hdflv_med2play
		WHERE " . $wpdb->prefix . "hdflv_med2play.playlist_id = pid AND " . $wpdb->prefix . "hdflv_med2play.media_id = '$mediaid'");

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
        . '"' . ($playlist['checked'] ? ' checked="checked"' : "") . '/> ' . wp_specialchars($playlist['name']) . "</label></td >&nbsp;<td style='font-size:11px;padding-left:13px'><input type=text size=3 id=sort-" . $playlist['playid'] . " name=sorder[] value=" . $playlist['sorder'] . ">Sort order</td></tr>
            ";
    }
    echo "</table>";
    $comma_separated = implode(",", $hiddenarray);
    echo "<input type=hidden name=hid value = $comma_separated >";
}

//calling via Ajax Function used for getting playlist output
function get_ajax_playlist_for_dbx() {

    global $wpdb;
    $playids = $wpdb->get_col("SELECT pid FROM " . $wpdb->prefix . "hdflv_playlist");
    $checked_playlist = $wpdb->get_col("
		SELECT playlist_id,sorder
		FROM " . $wpdb->prefix . "hdflv_playlist," . $wpdb->prefix . "hdflv_med2play
		WHERE " . $wpdb->prefix . "hdflv_med2play.playlist_id = pid AND " . $wpdb->prefix . "");

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
        . '"' . ($playlist['checked'] ? ' checked="checked"' : "") . '/> ' . wp_specialchars($playlist['name']) . "</label></td >&nbsp;<td style='font-size:11px;padding-left:13px'><input type=text size=3 id=sort-" . $playlist['playid'] . " name=sorder[] value=" . $playlist['sorder'] . ">Sort order</td></tr>
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
    $playids = $wpdb->get_col("SELECT pid FROM " . $wpdb->prefix . "hdflv_playlist");
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
        . '"' . ($playlist['checked'] ? ' checked="checked"' : "") . '/> ' . wp_specialchars($playlist['name']) . "</label></td >&nbsp;<td style='font-size:11px;padding-left:13px'><input type=text size=3 id=sort-" . $playlist['playid'] . " name=sorder[] value=" . $playlist['sorder'] . ">Sort order</td></tr>
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
    $pieces = explode(",", $_POST['hid']);
    $video1 = $_POST['normalvideoform-value'];
    $video2 = $_POST['hdvideoform-value'];
    $img1 = $_POST['thumbimageform-value'];
    $img2 = $_POST['previewimageform-value'];
    $img3 = $_POST['customimage'];
    $pre_image = $_POST['custompreimage'];
    if($uPath != ''){
        $wp_urlpath = $wp_urlpath.$uPath.'/';
    }else{
        $wp_urlpath = $wp_urlpath.'/';
    }
    

// Get input informations from POST
    $sorder = $_POST['sorder'];

    $act_name = trim($_POST['name']);

    if ($_POST['youtube-value'] != '') {

        $act_filepath = addslashes(trim($_POST['youtube-value']));
    } else {

        $act_filepath = addslashes(trim($_POST['customurl']));
    }

    $act_filepath2 = trim($_POST['customhd']);
    $act_image = addslashes(trim($_POST['urlimage']));
    $act_link = '';

    $act_playlist = $_POST['playlist'];

    $act_tags = addslashes(trim($_POST['act_tags']));

    if (!empty($act_filepath)) {
        $ytb_pattern = "@youtube.com\/watch\?v=([0-9a-zA-Z_-]*)@i";
        if (preg_match($ytb_pattern, stripslashes($act_filepath), $match)) {
            //print_r($match);
            $youtube_data = hd_GetSingleYoutubeVideo($match[1]);
            if ($youtube_data) {
                if ($act_name == '')
                    $act_name = addslashes($youtube_data['title']);
                if ($act_image == '')
                    $act_image = $youtube_data['thumbnail_url'];
                if ($act_link == '')
                    $act_link = $act_filepath;
                $act_filepath = preg_replace('/^(http)s?:\/+/i', '', $act_filepath);
            } else
                render_error(__('Could not retrieve Youtube video information', 'hdflv'));
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

    if ($insert_video != 0) {
        $video_aid = $wpdb->insert_id;  // get index_id
        $tags = explode(',', $act_tags);
        
        render_message(__('Media file', 'hdflv') . ' ' . $video_aid . __(' added successfully', 'hdflv'));
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

//Function used for retrieving YOUTUBE url
function youtubeurl() {
    $act_filepath = addslashes(trim($_POST['filepath']));
    if (!empty($act_filepath)) {
        $ytb_pattern = "@youtube.com\/watch\?v=([0-9a-zA-Z_-]*)@i";
        if (preg_match($ytb_pattern, stripslashes($act_filepath), $match)) {
            $youtube_data = hd_GetSingleYoutubeVideo($match[1]);
            if ($youtube_data) {
                $act[0] = addslashes($youtube_data['title']);
                $act[3] = $youtube_data['thumbnail_url'];
                $act[4] = $act_filepath;
            } else
                render_error(__('Could not retrieve Youtube video information', 'hdflv'));
        }else {
            $act[4] = $act_filepath;
            render_error(__('URL entered is not a valid Youtube Url', 'hdflv'));
        }
        return $act;
    }
}

//Function used for updating media data(File path,name,etc..)
function hd_update_media($media_id) {
    global $wpdb;
    $pieces = explode(",", $_POST['hid']);
    $sorder = $_POST['sorder'];
    $act_name = addslashes(trim($_POST['act_name']));
    $act_filepath = addslashes(trim($_POST['act_filepath']));
    $act_image = addslashes(trim($_POST['act_image']));
    $act_hdpath = addslashes(trim($_POST['act_hdpath']));
    $act_link = addslashes(trim($_POST['act_link']));
    $act_opimg = addslashes(trim($_POST['act_opimg']));

    $act_playlist = $_POST['playlist'];
    // Update tags
    $act_tags = addslashes(trim($_POST['act_tags']));
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
function hd_delete_media($act_vid, $deletefile) {
    global $wpdb;

    if ($deletefile) {

        $act_videoset = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "hdflv WHERE vid = $act_vid ");

        $act_filename = wpt_filename($act_videoset->file);
        $abs_filename = str_replace(trailingslashit(get_option('siteurl')), ABSPATH, trim($act_videoset->file));
        if (!empty($act_filename)) {

            $wpt_checkdel = @unlink($abs_filename);
            if (!$wpt_checkdel)
                render_error(__('Error in deleting file', 'hdflv'));
        }

        $act_filename = wpt_filename($act_videoset->image);
        $abs_filename = str_replace(trailingslashit(get_option('siteurl')), ABSPATH, trim($act_videoset->image));
        if (!empty($act_filename)) {

            $wpt_checkdel = @unlink($abs_filename);
            if (!$wpt_checkdel)
                render_error(__('Error in deleting file', 'hdflv'));
        }
    }

    //TODO: The problem of this routine : if somebody change the path, after he uploaded some files
    $wpdb->query("DELETE FROM " . $wpdb->prefix . "hdflv_med2play WHERE media_id = $act_vid");

    $delete_video = $wpdb->query("DELETE FROM " . $wpdb->prefix . "hdflv WHERE vid = $act_vid");
    if (!$delete_video)
        render_error(__('Error in deleting media file', 'hdflv'));

    if (empty($text))
        render_message(__('Media file', 'hdflv') . ' \'' . $act_vid . '\' ' . __('deleted successfully', 'hdflv'));

    return;
}


//calling via Ajax to add playlist
function hd_ajax_add_playlist($name,$media) {
   
    global $wpdb;

    // Get input informations from POST
    $p_name = addslashes(trim($name));
    $p_description = '';
    $p_playlistorder = 0;
    if (empty($p_playlistorder))
        $p_playlistorder = "ASC";

    $playlistname1 = "select playlist_name from " . $wpdb->prefix . "hdflv_playlist where playlist_name='" . $p_name . "'";
    $planame1 = mysql_query($playlistname1);
    if (mysql_fetch_array($planame1, MYSQL_NUM)) {
        render_error(__('Failed, Playlist name already exist', 'hdflv')).get_playlist_for_dbx($media);
        return ;
    }

    // Add playlist in db
    if (!empty($p_name)) {
        $insert_plist = mysql_query(" INSERT INTO " . $wpdb->prefix . "hdflv_playlist (playlist_name, playlist_desc, playlist_order) VALUES ('$p_name', '$p_description', '$p_playlistorder')");
        if ($insert_plist != 0) {
            $pid = $wpdb->insert_id;  // get index_id
            render_message(__('Playlist', 'hdflv') . ' ' . $name . __(' added successfully', 'hdflv')).get_playlist_for_dbx($media);
        }
    }

    return ;
}

//Function used to add playlist
function hd_add_playlist() {
    global $wpdb;

    // Get input informations from POST
    $p_name = addslashes(trim($_POST['p_name']));
    $p_description = addslashes(trim($_POST['p_description']));
    $p_playlistorder = $_POST['sortorder'];
    if (empty($p_playlistorder))
        $p_playlistorder = "ASC";

    $playlistname1 = "select playlist_name from " . $wpdb->prefix . "hdflv_playlist where playlist_name='" . $p_name . "'";
    $planame1 = mysql_query($playlistname1);
    if (mysql_fetch_array($planame1, MYSQL_NUM)) {
        render_error(__('Failed, Playlist name already exist', 'hdflv'));
        return;
    }

    // Add playlist in db
    if (!empty($p_name)) {
        $insert_plist = $wpdb->query(" INSERT INTO " . $wpdb->prefix . "hdflv_playlist (playlist_name, playlist_desc, playlist_order) VALUES ('$p_name', '$p_description', '$p_playlistorder')");
        if ($insert_plist != 0) {
            $pid = $wpdb->insert_id;  // get index_id
            render_message(__('Playlist', 'hdflv') . ' ' . $pid . __(' added successfully', 'hdflv'));
        }
    }

    return;
}

//Function used to update playlist
function hd_update_playlist() {
    global $wpdb;

    // Get input informations from POST
    $p_id = (int) ($_POST['p_id']);
    $p_name = addslashes(trim($_POST['p_name']));
    $p_description = addslashes(trim($_POST['p_description']));
    $p_playlistorder = $_POST['sortorder'];

    if (!empty($p_name)) {
        $wpdb->query(" UPDATE " . $wpdb->prefix . "hdflv_playlist SET playlist_name = '$p_name', playlist_desc = '$p_description', playlist_order = '$p_playlistorder' WHERE pid = '$p_id' ");
        render_message(__('Update Successfully', 'hdflv'));
    }

    return;
}

//Function used for rendering message
function render_message($message, $timeout = 0) {
?>
    <div class="wrap"><h2>&nbsp;</h2>
        <div class="fade updated" id="message" onclick="this.parentNode.removeChild (this)">
            <p><strong><?php echo $message ?></strong></p>
        </div></div>
<?php
}

//Function used to delete playlist
function hd_delete_playlist($act_pid) {
    global $wpdb;
    $delete_plist = $wpdb->query("DELETE FROM " . $wpdb->prefix . "hdflv_playlist WHERE pid = $act_pid");
    $delete_plist2 = $wpdb->query("DELETE FROM " . $wpdb->prefix . "hdflv_med2play WHERE playlist_id = $act_pid");
    if ($delete_plist && $delete_plist2) {
        render_message(__('Playlist', 'hdflv') . ' \'' . $act_pid . '\' ' . __('deleted successfully', 'hdflv'));
    }

    return;
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
