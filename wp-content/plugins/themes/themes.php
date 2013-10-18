<?php
/*
Plugin Name: WP-Themes
*/

define('THEMES_UPLOAD_PATH', ABSPATH."wp-content/themes_lib/theme_images/");
define('THEMES_ZIP_PATH', ABSPATH."wp-content/downloads/");
define('FULL_URL', "/wp-content/themes_lib/theme_images/");
define("IMAGE_URL", plugins_url("/image.php", __FILE__)."?image=/wp-content/themes_lib/theme_images/");

//ini_set("display_errors", "1");
//error_reporting(E_ALL);

include("themes.class.php");
include("admin.class.php");

$themes = new fref_Themes();
$admin = new fref_ThemeAdmin();





register_activation_hook(__FILE__ , array(&$admin,'install'));
add_action("init", array(&$themes, "init"));
add_action("admin_init", array(&$admin, "init"));
add_action("the_content", array(&$themes, "display"));
add_action("wp_head", array(&$themes, "head"));
add_action('admin_menu', array(&$admin, "adminMenu"));
add_action('admin_head', array(&$admin, "adminHead"));

?>