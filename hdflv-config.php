<?php
/**
 * @name          : configuration of plugin
 * @version	  	  : 1.8
 * @package       : apptha
 * @subpackage    : contus-hd-flv-player
 * @author        : Apptha - http://www.apptha.com
 * @copyright     : Copyright (C) 2011 Powered by Apptha
 * @license	      : GNU General Public License version 2 or later; see LICENSE.txt
 * @Purpose       : configuration of plugin
 * @Creation Date : Dec 09, 2011
 * @Modified Date : Jul 23, 2012
 * */

/**
 * Bootstrap file for getting the ABSPATH constant to wp-load.php
 * This is requried when a plugin requires access not via the admin screen.
 *
 * If the wp-load.php file is not found, then an error will be displayed 
 */
/** Define the server path to the file wp-config here, if you placed WP-CONTENT outside the classic file structure */
$path = ''; // It should be end with a trailing slash

/** That's all, stop editing from here * */
if (!defined('WP_LOAD_PATH')) {

    /** classic root path if wp-content and plugins is below wp-config.php */
    $classic_root = dirname(dirname(dirname(dirname(__FILE__)))) . '/';

    if (file_exists($classic_root . 'wp-load.php'))
        define('WP_LOAD_PATH', $classic_root);
    else
    if (file_exists($path . 'wp-load.php'))
        define('WP_LOAD_PATH', $path);
    else
        exit("Could not find wp-load.php");
}

// let's load WordPress
require_once( WP_LOAD_PATH . 'wp-load.php');
?>