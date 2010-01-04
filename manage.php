
<?php
/*
+----------------------------------------------------------------+
+	hdflv-admin
+	
+   required for hdflv
+----------------------------------------------------------------+
*/
class HDFLVManage   {

    var $mode = 'main';
    var $wptfile_abspath;
    var $wp_urlpath;
    var $act_vid = false;
    var $act_pid = false;
    var $base_page = '?page=hdflv';
    var $PerPage = 10;

    function HDFLVManage() {
        global $hdflv;
        global $act1;

        // get the options
        $this->options = get_option('HDFLVSettings');
        //print_r($this->options);
        //echo "<br><br>".$this->options[0][27]['v'];
        //echo $this->options[0][26]['v'];

        // $this->options = hd_get_DefaultOption();

        // same as $_SERVER['REQUEST_URI'], but should work under IIS 6.0
        //$this->base_page   = admin_url() . 'admin.php' . $this->base_page ;

        // Create taxonomy
        //register_taxonomy( WORDTUBE_TAXONOMY, 'wordtube', array('update_count_callback' => '_update_media_term_count') );

        // check for player


        // Manage upload dir
        add_filter('upload_dir', array(&$this, 'upload_dir'));

        $wp_upload = wp_upload_dir();
        //print_r(wp_upload_dir()) ;
        $this->wptfile_abspath = $wp_upload['path'].'/';
        $this->wp_urlpath = $wp_upload['url'].'/';

        // output Manage screen
        $this->controller();
    }

    /**
     * Renders an admin section of display code
     *@author     John Godley (http://urbangiraffe.com)
     *
     * @param string $ug_name Name of the admin file (without extension)
     * @param string $array Array of variable name=>value that is available to the display code (optional)
     * @return void
     **/

    function render_admin ($ug_name, $ug_vars = array ())
    {
       // echo $ug_name."".$ug_vars;
       // exit();
        $function_name = array($this, 'show_'.$ug_name);

        if ( is_callable($function_name) )
        call_user_func_array($function_name, $ug_vars);
        else
        echo "<p>Rendering of admin function show_$ug_name failed</p>";
    }

    // Return custom upload dir/url
    function upload_dir($uploads) {

        if ($this->options[0][27]['v'] == 0 ) {
            $dir = ABSPATH.trim( $this->options[0][28]['v'] ).'/';
            $url = trailingslashit( get_option('siteurl') ).trim( $this->options[0][28]['v']).'/';


            // Make sure we have an uploads dir
            if ( ! wp_mkdir_p( $dir ) ) {
                $message = sprintf(__('Unable to create directory %s. Is its parent directory writable by the server?','hdflv'), $dir);
                $uploads['error'] = $message;
                return $uploads;
            }
            $uploads = array('path' => $dir, 'url' => $url, 'error' => false);
        }
        return $uploads;

    }

    function render_message($message, $timeout = 0)
    {
        ?>
            <div class="wrap"><h2>&nbsp;</h2>
                <div class="fade updated" id="message" onclick="this.parentNode.removeChild (this)">
                    <p><strong><?php echo $message ?></strong></p>
                </div>
            </div>
        <?php
    }

function controller() {
global $wpdb;

$this->mode = trim($_GET['mode']);

$this->act_vid = (int) $_GET['id'];
$this->act_pid = (int) $_GET['pid'];

//TODO:Include nonce !!!

if (isset($_POST['add_media']))
{
    hd_add_media($this->wptfile_abspath, $this->wp_urlpath);
    $this->mode = 'main';  
}

if (isset($_POST['youtube_media']))
{
    $act1 = youtubeurl();
   ?> <input type="hidden" name="act" id="act3" value="<? echo $act1[3] ?>" />
    <input type="hidden" name="act" id="act0" value="<? echo $act1[0] ?>" />
    <input type="hidden" name="act" id="act1" value="<? echo $act1[1] ?>" />
    <input type="hidden" name="act" id="act2" value="<? echo $act1[2] ?>" />
    <input type="hidden" name="act" id="act4" value="<? echo $act1[4] ?>" /><?
   $this->mode = 'add';// hd_add_media($this->wptfile_abspath, $this->wp_urlpath);
}


if (isset($_POST['edit_update']))
{
    hd_update_media( $this->act_vid );
    $this->mode = 'main';
}


if (isset($_POST['cancel']) || isset($_POST['search']))
$this->mode = 'main';

if (isset($_POST['show_add']))
$this->mode = 'add';

if (isset($_POST['add_pl'])) {
    hd_add_playlist();
    $this->mode = 'edit';
}

if (isset($_POST['add_pl1'])) {
    hd_add_playlist();
    $this->mode = 'add';
}

if (isset($_POST['add_playlist'])) {
    hd_add_playlist();
    $this->mode = 'playlist';
}

if (isset($_POST['update_playlist'])) {
    hd_update_playlist();
    $this->mode = 'playlist';
}

if ( $this->mode =='delete') {
    hd_delete_media($this->act_vid, $this->options['deletefile']);
    $this->mode = 'main';
}

//Let's show the main screen if no one selected
if ( empty($this->mode) )
$this->mode = 'main';


// render the admin screen
$this->render_admin($this->mode);
}

function show_main()
{
            global $wpdb;
            
            // init variables
            $pledit = true;
            $where = '';
            $join = '';
            
            //if(isset($_REQUEST['plfilter'])) echo $_REQUEST['plfilter']."nirmal";
            // check for page navigation
            $page     = ( isset($_REQUEST['apage']))    ? (int) $_REQUEST['apage'] : 1;
            $sort     = ( isset($_REQUEST['sort']))     ? $_REQUEST['sort'] :'DESC';
            $search   = ( isset($_REQUEST['search']))   ? $_REQUEST['search'] : '';
            $plfilter = ( isset($_REQUEST['plfilter'])) ? $_REQUEST['plfilter'] : (isset($_REQUEST['playid']) ? $_REQUEST['playid'] : '0' );

           

            if ($search != '') {
                if ($where != '') $where .= " AND ";
                $where .= " ((name LIKE '%$search%') OR (creator LIKE '%$search%')) ";
            }

            if ($plfilter != '0' && $plfilter != 'no') {
                $join = " LEFT JOIN ".$wpdb->prefix."hdflv_med2play ON (vid = media_id) ";
                if ($where != '') $where .= " AND ";
                $where .= " (playlist_id = '".$plfilter."') ";
                $pledit = true;
            } elseif ($plfilter == 'no') {
                $join = " LEFT JOIN ".$wpdb->prefix."hdflv_med2play ON (vid = media_id) ";
                if ($where != '') $where .= " AND ";
                $where .= " (media_id IS NULL) ";
                $pledit = false;
            } else
            $pledit = false;

            if ($where != '') $where = " WHERE ".$where;

            $total = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."hdflv".$join.$where );
            //echo $total;
            $total_pages = ceil( $total / $this->PerPage );
            if ($total_pages == 0) $total_pages = 1;

            if ($page > $total_pages) $page = $total_pages;
            $start = $offset = ( $page - 1 ) * $this->PerPage;

            if ($pledit)
            $orderby = " ORDER BY porder ".$sort.", vid ".$sort;
            else
            $orderby = " ORDER BY vid ".$sort;

           
                $tables = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."hdflv".$join.$where.$orderby." LIMIT $start, 10");
            

?>



<!-- Manage Video-->

<div class="wrap">

    <form name="filterType" method="post" id="posts-filter">
        <h2><?php _e('Manage Media files','hdflv'); ?></h2>
        <div style='background:#D0D0D0;list-style:none;width:850px;float:left'><p style='padding-left:6px'>You can also set different width and height for the player in different posts irrespective of the values specified here.<br><br>

           <b>For example:</b>[hdplay id=3 playlistid=2 width=400 height=400 /] or [hdplay playlistid=2 /] or [hdplay id=3 /] <br><br>* id will be created when u add videos in the manage media section</p></div><div style=float:left;color:red;>&nbsp;&nbsp;&nbsp;<a style=color:red; href="options-general.php?page=hdflvplugin"><?php _e('Options->HDFLVPlayer Settings', 'hdflv')?></a></div><div style=clear:both></div>
        <ul class="subsubsub">
            <li>&nbsp;</li>
        </ul>
        <p class="search-box">
            <input type="text" class="search-input" name="search" value="<?php echo $search; ?>" size="10" />
            <input type="submit" class="button-primary" value="<?php _e('Search Media','hdflv'); ?>" />
            <input type="hidden" name="cancel" value="2"/>
        </p>
        <div class="tablenav">
            <?php $this->navigation($this->PerPage, $page, $total, $search, $sort, $plfilter); ?>
            <div class="alignleft actions">
                <?php $this->sort_filter($sort); ?>
                <?php $this->playlist_filter($plfilter); ?>
                <input class="button-secondary" id="post-query-submit" type="submit" name="startfilter"  value="<?php _e('Filter','hdflv'); ?> &raquo;" class="button" />
            </div>
        </div>
        <!-- Table -->
        <table class="widefat" cellspacing="0">
            <thead>
                <tr>
                    <th id="id" class="manage-column column-id" scope="col"><?php _e('ID','hdflv'); ?> </th>
                    <th id="title" class="manage-column column-title" scope="col"><?php _e('Title','hdflv'); ?> </th>
                    <th id="path" class="manage-column column-path"  scope="col"><?php _e('Path','hdflv'); ?> </th>
                    <? if(isset($_REQUEST['plfilter']) && $_REQUEST['plfilter'] != 'no' && $_REQUEST['plfilter'] != '0' || isset($_REQUEST['playid']) ) { ?><th id="path" class="manage-column column-path"  scope="col"><?php _e('Sort Order','hdflv'); ?> </th><?}?>



                </tr>
            </thead>
            <tbody id="the-list" class="list:post">
                <?php
                if($tables) {
                    $i = 0;
                    foreach($tables as $table) {
                        $class = ( $class == 'class="alternate"' ) ? '' : 'class="alternate"';
                        echo "<tr $class>\n";
                        echo "<th scope=\"row\">$table->vid</th>\n";
                        echo "<td class='post-title column-title''><strong><a title='" . __('Edit this media','hdflv') . "' href='$this->base_page&amp;mode=edit&amp;id=$table->vid'>" . stripslashes($table->name) . "</a></strong>\n";
                        echo "<span class='edit'>
                                                        <a title='" . __('Edit this media','hdflv') . "' href='$this->base_page&amp;mode=edit&amp;id=$table->vid'>" . __('Edit') . "</a>
                                                      </span> | ";
                        echo "<span class='delete'>
                                                        <a title='" . __('Delete this media','hdflv') . "' href='$this->base_page&amp;mode=delete&amp;id=$table->vid' onclick=\"javascript:check=confirm( '".__("Delete this file ?",'hdflv')."');if(check==false) return false;\">" . __('Delete') . "</a>
                                                      </span>";
                        echo "</td>\n";
                        echo "<td>".htmlspecialchars(stripslashes($table->file), ENT_QUOTES)."</td>\n";
                        if(isset($_REQUEST['plfilter']) && $_REQUEST['plfilter'] != 'no' && $_REQUEST['plfilter'] != '0') {
                            $a1=mysql_query("SELECT sorder FROM ".$wpdb->prefix."hdflv_med2play where playlist_id=".$_REQUEST['plfilter']." and media_id=$table->vid");
                            $playlist1 = mysql_fetch_array($a1);
                            echo "<td>".$playlist1[0]."</td>\n";
                        }elseif (isset($_REQUEST['playid']))
                        {
                            $a1=mysql_query("SELECT sorder FROM ".$wpdb->prefix."hdflv_med2play where playlist_id=".$_REQUEST['playid']." and media_id=$table->vid");
                            $playlist1 = mysql_fetch_array($a1);
                            echo "<td>".$playlist1[0]."</td>\n";
                        }

                        echo '</tr>';
                        $i++;
                    }
                } else {
                    echo '<tr><td colspan="7" align="center"><b>'.__('No entries found','hdflv').'</b></td></tr>';
                }
                ?>
            </tbody>
        </table>
        <div class="tablenav">
            <?php $this->navigation($this->PerPage, $page, $total, $search, $sort, $plfilter); ?>
            <div class="alignleft actions">
                <input class="button-secondary" type="submit" value="<?php _e('Insert new media file','hdflv') ?> &raquo;" name="show_add"/>
            </div>
            <br class="clear"/>
        </div>
    </form>
</div>

<!-- Manage Playlist-->
<div class="wrap">
    <h2><?php _e('Playlist', 'hdflv') ?> (<a href="<?php echo $this->base_page; ?>&mode=playlist"><?php _e('Add or Edit','hdflv') ?></a>)</h2>
    <p><?php _e('You can show all videos/media files in a playlist. Show this playlist with the tag', 'hdflv') ?> <strong> [hdplay playlistid=id /]</strong></p>
    <form name="selectlist" method="post">
        <input type="hidden" name="apage" value="<?php echo $page; ?>" />
        <input type="hidden" name="search" value="<?php echo $search; ?>" />
        <input type="hidden" name="sort" value="<?php echo $sort; ?>" />
        <input type="hidden" name="plfilter" value="<?php echo $plfilter; ?>" />

    </form>

</div>

        <?php
    }

    function show_edit() {
        

        global $wpdb;
      
        $media = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."hdflv where vid = $this->act_vid");
        //$playid1 = mysql_query("SELECT pid FROM ".$wpdb->prefix."hdflv_playlist");
        $act_name = htmlspecialchars(stripslashes($media->name));
        $act_creator = htmlspecialchars(stripslashes($media->creator));
        $act_desc = htmlspecialchars(stripslashes($media->description));
        $act_filepath = stripslashes($media->file);
        $act_image = stripslashes($media->image);
        $act_link = stripslashes($media->link);
        // Retrieve tags to display
        //$act_width = stripslashes($media->width);
        //$act_height = stripslashes($media->height);

        ?>
<!-- Edit Video -->
<div class="wrap">
    <h2> <?php _e('Edit media file', 'hdflv') ?> </h2>
    <form name="table_options" method="post" id="video_options">
        <div id="poststuff" class="has-right-sidebar">
            <div class="inner-sidebar">
                <div id="submitdiv" class="postbox">
                    <h3 class="hndle"><span><?php _e('Playlist','hdflv') ?></span></h3>
                    <div class="inside">
                        <div id="submitpost" class="submitbox">
                            <div class="misc-pub-section">
                                <p><?php _e('See global settings for the HDFLV Player under', 'hdflv') ?>&nbsp;<a href="options-general.php?page=hdflvplugin"><?php _e('Options->HDFLVPlayer', 'hdflv')?></a> <br /><br />
                                <?php _e('If you want to show this media file in your page, enter the tag :', 'hdflv') ?><br /><strong>[hdplay id=<?php echo $this->act_vid; ?> /]</strong></p>
                            </div>
                            <div class="misc-pub-section">
                            
                            <? //if(mysql_num_rows($playid1)) { ?>
                                <h4> <?php _e('Playlist','hdflv');// } ?> &nbsp;&nbsp;
                                <a style="cursor:pointer"  onclick="playlistdisplay()"><?php _e('Create New', 'hdflv')?></a></h4>
                                <div id="playlistcreate">
                                            <?php _e('Name','hdflv'); ?><input type="text" size="20" name="p_name" value="" />
                                            <input type="submit" class="button-primary" name="add_pl" value="<?php _e('Add'); ?>" class="button button-highlighted" />
                                            <a style="cursor:pointer" onclick="playlistclose()"><b>Close</b></a>
                                </div>
                                <p id="jaxcat"></p>
                                <div id="playlistchecklist"><?php get_playlist_for_dbx($this->act_vid); ?></div>
                            </div>

                            <div id="major-publishing-actions">
                                <input type="submit" class="button-primary" name="edit_update" value="<?php _e('Update'); ?>" class="button button-highlighted" />
                                <input type="submit" class="button-secondary" name="cancel" value="<?php _e('Cancel'); ?>" class="button" />
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div id="post-body" class="has-sidebar">
                <div id="post-body-content" class="has-sidebar-content">
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><?php _e('Media title','hdflv') ?></th>
                            <td><input type="text" size="50"  name="act_name" value="<?php echo $act_name ?>" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php _e('Media URL','hdflv') ?></th>
                            <td><input type="text" size="80"  name="act_filepath" value="<?php echo $act_filepath ?>" />
                                <br /><?php _e('Here you need to enter the URL to the file ( MP4, M4V, M4A, MOV, Mp4v or F4V)','hdflv') ?>
                                <br /><?php echo _e('It also accept Youtube links. Example: http://www.youtube.com/watch?v=tTGHCRUdlBs','hdflv') ?>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php _e('Thumbnail URL','hdflv') ?></th>
                            <td><input type="text" size="80"  name="act_image" value="<?php echo $act_image ?>" />
                            <br /><?php _e('Enter the URL to show a preview of the media file','hdflv') ?></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php _e('Link URL','hdflv') ?></th>
                            <td><input type="text" size="80" name="act_link" value="<?php echo $act_link ?>" />
                            <br /><?php _e('Enter the URL to the page/file, if you click on the player','hdflv') ?></td>
                        </tr>
                    </table>
                </div>
                <p>
                    <input type="submit" class="button-primary" name="edit_update" value="<?php _e('Update'); ?>" class="button button-highlighted" />
                    <input type="submit" class="button-secondary" name="cancel" value="<?php _e('Cancel'); ?>" class="button" />
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

    function show_add() {

        ?>
            
<!-- Add A Video -->
<div class="wrap">
             

    <h2><?php _e('Add a new media file','hdflv'); global $wpdb;  ?></h2>
                    
    <form name="table_options" enctype="multipart/form-data" method="post" id="video_options">
        <div id="poststuff" class="has-right-sidebar" >

            <div class="inner-sidebar" >
                <div id="submitdiv" class="postbox">
                    <h3 class="hndle" style="color:white;background:none;background-color:black"><span><?php _e('Playlist','hdflv') ?></span></h3>
                    <div class="inside" style="color:blue" >
                        <div id="submitpost" class="submitbox">
                            <div class="misc-pub-section">
                                <p>
                                    <?php _e('See global settings for the HDFLV Player under', 'hdflv') ?> <a href="options-general.php?page=hdflvplugin"><?php _e('Options->HDFLVPlayer', 'hdflv')?></a>
                                </p>
                            </div><? //$playid1 = mysql_query("SELECT pid FROM ".$wpdb->prefix."hdflv_playlist"); ?>
                            <div class="misc-pub-section"><? //if(mysql_num_rows($playid1)) { ?>
                                <h4><?php _e('Playlist','hdflv'); ?>&nbsp;&nbsp;
                                <a style="cursor:pointer"  onclick="playlistdisplay()"><?php _e('Create New', 'hdflv')?></a></h4>
                                <div id="playlistcreate1"><?php _e('Name','hdflv'); ?><input type="text" size="20" name="p_name" value="" />
                                        <input type="submit" class="button-primary" name="add_pl1" value="<?php _e('Add'); ?>" class="button button-highlighted" />
                                        <a style="cursor:pointer" onclick="playlistclose()"><b>Close</b></a></div>
                                <p id="jaxcat"></p>
                                <div id="playlistchecklist"><?php get_playlist_for_dbx($this->act_vid); ?></div>
                            </div> 
                        </div>
                    </div>
                </div>
            </div>

            <div id="post-body" class="has-sidebar"><br>
                <div id="post-body-content" class="has-sidebar-content">

                
                    <div class="stuffbox" name="youtube" >
                        <h3 class="hndle"><span>Enter YouTube URL</span></h3>
                        <div id="youtube" class="inside" style="margin:15px;">
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('URL to media file','hdflv') ?></th>
                                    <td><input type="text" size="50" id="filepath" name="filepath" />&nbsp;&nbsp<input type="submit" name="youtube_media" class="button-primary" value="<?php _e('Generate details','hdflv'); ?>" class="button" />
                                        <br /><?php _e('Here you need to enter the URL to the media file','hdflv') ?>
                                    <br /><?php _e('It accept also a Youtube link: http://www.youtube.com/watch?v=tTGHCRUdlBs','hdflv') ?></td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('URL to thumbnail file','hdflv') ?></th>
                                    <td><input type="text" size="50" id="urlimage" name="urlimage" value="" />
                                    <br /><?php _e('Enter the URL to show a preview of the media file (optional)','hdflv') ?></td>
                                </tr>
                            </table>
                        </div>
                       
                    </div>

                     
                        
                   

               

                    <div class="stuffbox">
                        <h3 class="hndle"><span><?php _e('Enter Title / Name','hdflv'); ?></span></h3>
                        <div class="inside" style="margin:15px;">
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Title / Name','hdflv') ?></th>
                                    <td><input type="text" size="50" maxlength="200" name="name" id="name" /></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                     

                   

                    

                </div>
                <input type="submit" name="add_media" class="button-primary" value="<?php _e('Add media file','hdflv'); ?>" class="button" />
            </div>
        </div><!--END Poststuff -->

    </form><script>
        document.getElementById('urlimage').value = document.getElementById('act3').value;
        document.getElementById('name').value = document.getElementById('act0').value;
         document.getElementById('filepath').value = document.getElementById('act4').value;
        </script>
        <script> document.getElementById('playlistcreate1').style.display = "none";

   function playlistdisplay()
   {
      document.getElementById('playlistcreate1').style.display = "block";
   }
   function playlistclose()
   {
      document.getElementById('playlistcreate1').style.display = "none";
   }
</script>

</div><!--END wrap -->
        <?php
    }

    function show_plydel() {
        $message = hd_delete_playlist($this->act_pid);
        $this->render_message($message);
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
        $tables = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix."hdflv_playlist ");
        if ($this->mode == 'plyedit')
        $update = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix."hdflv_playlist WHERE pid = {$this->act_pid} ");
        ?>

<!-- Edit Playlist -->
<div class="wrap">
    <h2><?php _e('Manage Playlist','hdflv'); ?></h2>
    <br class="clear"/>
    <form id="editplist" name="editplist" action="<?php echo $this->base_page; ?>" method="post">
        <table class="widefat" cellspacing="0">
            <thead>
                <tr>
                    <th scope="col"><?php _e('ID','hdflv'); ?></th>
                    <th scope="col"><?php _e('Name','hdflv'); ?></th>
                    <th scope="col" colspan="2"><?php _e('Action'); ?></th>
                </tr>
            </thead>
            <?php
            if($tables) {
                $i = 0;
                foreach($tables as $table) {
                    if($i%2 == 0) {
                        echo "<tr class='alternate'>\n";
                    }  else {
                        echo "<tr>\n";
                    }
                    echo "<th scope=\"row\">$table->pid</th>\n";
                    echo "<td><a onclick=\"submitplay($table->pid)\" href=\"#\" >".stripslashes($table->playlist_name)."</td>\n";
                    echo "<td><a href=\"$this->base_page&amp;mode=plyedit&amp;pid=$table->pid#addplist\" class=\"edit\">".__('Edit')."</a></td>\n";
                    echo "<td><a href=\"$this->base_page&amp;mode=plydel&amp;pid=$table->pid\" class=\"delete\" onclick=\"javascript:check=confirm( '".__("Delete this file ?",'hdflv')."');if(check==false) return false;\">".__('Delete')."</a></td>\n";
                    echo '</tr>';
                    $i++;
                }
            } else {
                echo '<tr><td colspan="7" align="center"><b>'.__('No entries found','hdflv').'</b></td></tr>';
            }
            ?>
        </table>
        <input type="hidden" name="playid" id="playid" value="" />
    </form>
    <script type="text/javascript">
        function submitplay(playid)
        {
            document.getElementById('playid').value = playid;
            document.editplist.action = "?page=hdflv";
            document.editplist.submit();
        }
    </script>
</div>

<div class="wrap">
    <div id="poststuff" class="metabox-holder">
        <div id="playlist_edit" class="stuffbox">
            <h3><?php
                if ($this->mode == 'playlist') echo _e('Add Playlist','hdflv');
                if ($this->mode == 'plyedit') echo _e('Update Playlist','hdflv');
                ?></h3>
            <div class="inside">
                <form id="addplist" action="<?php echo $this->base_page; ?>" method="post">
                    <input type="hidden" value="<?php echo $this->act_pid ?>" name="p_id" />
                    <p><?php _e('Name:','hdflv'); ?><br/><input type="text" value="<?php echo $update->playlist_name ?>" name="p_name"/></p>
                    <div class="submit">
                        <?php
                        if ($this->mode == 'playlist') echo '<input type="submit" name="add_playlist" value="' . __('Add Playlist','hdflv') . '" class="button-primary" />';
                        if ($this->mode == 'plyedit') echo '<input type="submit" name="update_playlist" value="' . __('Update Playlist','hdflv') . '" class="button-primary" />';
                        ?>
                        <input type="submit" name="cancel" value="<?php _e('Cancel','hdflv'); ?>" class="button-secondary" />
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
}

// Display sort form filter
function sort_filter($sort) {
?>
<select name="sort">
    <option value="ASC" <?php if ($sort == 'ASC') echo 'selected="selected"'; ?>><?php _e('Sort Ascending', 'hdflv'); ?></option>
    <option value="DESC" <?php if ($sort == 'DESC') echo 'selected="selected"'; ?>><?php _e('Sort Descending', 'hdflv'); ?></option>
</select>
<?php
}


// Display playlist form filter
function playlist_filter($plfilter) {
global $wpdb;
?>
<select name="plfilter">
    <option value="0" <?php if ($plfilter == '0') echo 'selected="selected"'; ?>><?php _e('--Select--', 'hdflv'); ?></option>
    <option value="no" <?php if ($plfilter == 'no') echo 'selected="selected"'; ?>><?php _e('No playlist', 'hdflv'); ?></option>
    <?php $dbresults = $wpdb->get_results(" SELECT * FROM ".$wpdb->prefix."hdflv_playlist ");
    if ($dbresults) {
        foreach ($dbresults as $dbresult) :
        echo '<option value="'.$dbresult->pid.'"';
        if ($plfilter == $dbresult->pid) echo 'selected="selected"';
        echo '>'.$dbresult->playlist_name.'</option>';
        endforeach;
    }
    ?>
</select>
<?php
}

// add a navigation
function navigation($PerPage, $page, $total, $search, $sort, $plfilter) {

$sdiv2 = "<div class='tablenav-pages'>";
$ediv2 = "</div>\n";

if ( $total > $PerPage ) {
    $total_pages = ceil( $total / $PerPage );
    if ($page > $total_pages) $page = $total_pages;
    $r = '';
    if ( 1 < $page ) {
        $args['apage'] = ( 1 == $page - 1 ) ? FALSE : $page - 1;
        if ($search != '') $args['search'] = $search;
        if ($sort != '') $args['sort'] = $sort;
        if ($plfilter != '') $args['plfilter'] = $plfilter;
        $r .=  '<a class="prev page-numbers" href="'. add_query_arg( $args, $this->base_page  ) . '">&laquo; '. __('Previous Page', 'hdflv') .'</a>' . "\n";
    }

    if ( ( $total_pages = ceil( $total / $PerPage ) ) > 1 ) {
        for ( $page_num = 1; $page_num <= $total_pages; $page_num++ ) :
        if ( $page == $page_num ) {
            $r .=  '<span class="page-numbers current">'.$page_num.'</span>'."\n";
        } else {
            $p = false;
            if ( $page_num < 3 || ( $page_num >= $page - 3 && $page_num <= $page + 3 ) || $page_num > $total_pages - 3 ) {
                $args['apage'] = ( 1 == $page_num ) ? FALSE : $page_num;
                if ($search != '') $args['search'] = $search;
                if ($sort != '') $args['sort'] = $sort;
                
                if ($plfilter != '') $args['plfilter'] = $plfilter;
                $r .= '<a class="page-numbers" href="' . add_query_arg($args, $this->base_page ) . '">' . ( $page_num ) . "</a>\n";
                $in = true;
            } elseif ( $in == true ) {
                $r .= '<span class="dots">...</span>'."\n";
                $in = false;
            }
        }
        endfor;
    }

    if ( ( $page ) * $PerPage < $total || -1 == $total ) {
        $args['apage'] = $page + 1;
        if ($search != '') $args['search'] = $search;
        if ($sort != '') $args['sort'] = $sort;
        if ($plfilter != '') $args['plfilter'] = $plfilter;
        $r .=  '<a class="next page-numbers" href="' . add_query_arg($args, $this->base_page ) . '">'. __('Next Page', 'hdflv') .' &raquo;</a>' . "\n";
    }
    $r = $sdiv2.$r.$ediv2;
} else
$r = '';

echo $r;
}
}
?>
