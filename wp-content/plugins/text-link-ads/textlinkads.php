<?php
/*
Plugin Name: Text Link Ads Advertiser Plugin 
Plugin URI: http://www.text-link-ads.com/?ref=267085
Description: Allows many xml keys per plugin install. Text Link Ads sell ads on specific pages. Join the Text Link Ads marketplace.  
Author: Text Link Ads
Version: 3.9.8
Author URI: http://www.text-link-ads.com/?ref=267085
*/
if (!function_exists('add_action')) {
    header('HTTP/1.0 404 Not Found');
    header('Location: ../../../404');
    exit;
}
global $wp_version;

//ensure that mysql_real_escape_string exists

if (!function_exists('mysql_real_escape_string')) {
    echo('You must be running PHP 4.3.0 or higher to use the Text Link Ads plugin. Please contact your web host about upgrading.');
    tla_disable_plugin();
    exit;
}
 
$wp_cache_shutdown_gc = 1;

$textlinkads_object = null;

add_action('init', 'tla_initialize');
// general/syncing hooks

if (!tla_widget_installed() && tla_between_posts()) {
    add_filter('the_content', 'tla_between_content_show');
} else {
    if ($wp_version < 2.8) {
        add_action('plugins_loaded', 'textlinkads_widget_init');
    } else {
        add_action('widgets_init', create_function('', 'return register_widget("textlinkads_widget");'));
    }
}

add_action('admin_init', 'tla_admin_init');
add_action('admin_menu', 'tla_admin_menu');
add_action('admin_notices', 'tla_admin_notices');
add_action('update_option_tla_site_keys', 'tla_refresh');

$tlaPluginName = plugin_basename(__FILE__);  

add_filter("plugin_action_links_$tlaPluginName", 'tla_settings_link');  

function tla_settings_link($links) 
{  
    $plugin = plugin_basename(__FILE__);  
    $settings_link = '<a href="options-general.php?page='.$plugin.'">Settings</a>';  
    array_unshift($links, $settings_link);  
    return $links;  
}  

function tla_admin_notices() 
{
    global $textlinkads_object;
    if ($textlinkads_object->websiteKeys) {
        return;
    }
    $pluginName = plugin_basename(__FILE__);
    echo "<div class='updated' style='background-color:#f66;'><p>" . sprintf(__('<a href="%s">Text Link Ads Plugin</a> needs attention: please enter a site key or disable the plugin.'), "options-general.php?page=$pluginName") . "</p></div>";
}

function tla_disable_plugin()
{
    $pluginName = basename(__FILE__);
    $plugins = get_option('active_plugins');
    $index = array_search($pluginName, $plugins);
    if ($index !== false) {
        array_splice($plugins, $index, 1);
        update_option('active_plugins', $plugins);
        do_action('deactivate_'.$pluginName);
    }
}

function tla_admin_init()
{
    global $textlinkads_object;
    if (!function_exists('register_setting')) return;
    register_setting('textlinkads', 'tla_between_posts'); 
    register_setting('textlinkads', 'tla_site_keys', 'tla_site_key_check'); 
    register_setting('textlinkads', 'tla_style_a'); 
    register_setting('textlinkads', 'tla_style_ul'); 
    register_setting('textlinkads', 'tla_style_li'); 
    register_setting('textlinkads', 'tla_style_span'); 
    register_setting('textlinkads', 'tla_fetch_method');     
}

function tla_site_key_check($setting)
{
    $tmpsetting = array();
    if (isset($setting[0]) && isset($setting[0]['mass']) && $setting[0]['mass']) {
        $setting[0]['mass'] = str_replace("\r", "\n", $setting[0]['mass']);
        $list = explode("\n", $setting[0]['mass']);
        foreach ($list as $item) {
            $item = str_replace("\t", " ", $item);
            list($xml_key, $url) = explode(" ", $item);
            $setting[] = array('key' => trim($xml_key), 'url' => trim($url));
        }
    }
    
    if ($setting) foreach ($setting as $data) {
        $badkey = false;
        $key = trim($data['key']);
        $url = trim($data['url']);
        $isurl = @parse_url($url);
        if (strlen($key) != 20) {
            $badkey = true;
        }
        if (!$isurl && $data['url'] != '/') {
            $badkey = true;
        }
        if (!$badkey) {
            $tmpsetting[] = $data;
        }
    }
    return $tmpsetting;
}

function tla_admin_menu()
{
    add_options_page('Text Link Ads Options', 'Text Link Ads', 'manage_options', __FILE__, 'tla_options_page');
}

function tla_options_page()
{
    global $textlinkads_object;
    ?>
    <div class="wrap">
        <h2>Text Link Ads</h2>
        <form method="post" action="options.php">
            <?php 
            if (function_exists('settings_fields')) {
                settings_fields('textlinkads'); 
            } else { 
                echo "<input type='hidden' name='option_page' value='textlinkads' />";
                echo '<input type="hidden" name="action" value="update" />';
                wp_nonce_field("textlinkads-options");
            }
            ?>
            <style>
            .tla_setting tr {
                border-bottom:5px solid #FFF;
            }
            .warning {
                color:red;
                border:1px solid #000;
                padding:5px;
            }
            </style>
            <table class="form-table tla_setting">
                <tr valign="top">
                    <td colspan=2>
                    <table><tr><th width=5%></th><th>Site Key</th><th>Ad Target Url</th></tr>
                
                <?php 
                $counter = 0;
                foreach ($textlinkads_object->websiteKeys as $url => $key) { 
                ?>
                <tr valign="top">
                    <td width=10%><?php echo ($url == get_option('siteurl')) ? 'Primary' : $counter;?></td>
                    <td><input type="text" name="tla_site_keys[<?php echo $counter;?>][key]" value="<?php echo $key;?>" /></td>
                    <td style="text-align:left;">
                        <input type="text" size="50" name="tla_site_keys[<?php echo $counter;?>][url]" value="<?php echo $url;?>" />
                    </td>
                    
                </tr>
                <?php
                    $counter++;
                }
                ?>
                 <tr>
                    <td colspan=2 valign="top">
                        This key can be obtained logging into <a href="http://www.text-link-ads.com/?ref=267085">Text Link Ads</a> and submitting your blog site. Delete a key by emptying the url and key fields.
                    </td>
                    <td valign="top">
                        The full url that your page was setup as i.e. <br><em>http://www.domain.com/mypage/</em>
                    </td>
                </tr>
                <tr valign="top">
                    <td width=10%>Add New</td><td style="text-align:left;"><input type="text" name="tla_site_keys[<?php echo $counter;?>][key]" value="" /></td><td><input type="text" name="tla_site_keys[<?php echo $counter;?>][url]" size="50"  value="" /></td>
                </tr>
                <tr>
                    <td valign=top> Or Bulk Add<br /></td><td colspan=3><textarea wrap=off rows=3 cols=77 name="tla_site_keys[0][mass]"></textarea><br />
                    <em>[site key] space or tab separated [url]</em>:<br/><br/>example:<br/>
                    <em>XXXXXXXXXXXXXXXXXXXX http://www.domain.com</em>
                    </td>
                </tr>
               
                </table></td></tr>
                <tr><td colspan=2>Adding multiple keys will remove the ability to do site wide via the widget or links between posts on the homepage</td></tr>
 
                <tr valign="top">
                    <th>Ad Display Method</th>
                    <td>
                        <?php if ($counter <= 1): ?>
                            <input type="radio" id="tla_between_posts_y" name="tla_between_posts" value="1" <?php echo get_option('tla_between_posts') ? 'checked="checked"' : '' ?>" />
                            <label for="tla_between_posts_y">Between Posts on Homepage</label>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="radio" id="tla_between_posts_n" name="tla_between_posts" value="0" <?php echo !get_option('tla_between_posts') ? 'checked="checked"' : '' ?>" />
                            <label for="tla_between_posts_n">Widget or Template Based</label>
                        <?php else: ?>
                            Ads will be displayed on the urls entered above. Make sure to activate the widget or add the <br /><?php echo '&lt;'.'?'.'php'.' tla_ads(); ?'.'&gt;';?> code to your template
                            <input type="hidden" name="tla_between_posts" value="0" />
                        <?php endif ?>
                    </td>
                </tr>
                <?php if ($counter <= 1): ?>
                <tr>
                    <td colspan="2">If you previously select Between Posts on Homepage option, the widget mode is disabled and your links will only appear on the homepage between posts. </td>
                </tr>
                <?php endif ?>
                <?php if (!function_exists('wp_remote_get')): ?>
                <tr valign="top">
                    <th>Ad Retrieval Method</th>
                    <td>
                        <?php if (function_exists('curl_init')) : ?>Curl <input type=radio name="tla_fetch_method" value="curl" <?php echo get_option('tla_fetch_method') == 'curl' ? 'checked' : '' ?>" /><?php endif; ?>
                        <?php if (function_exists('file_get_contents')) :?>Php (file_get_contents)<input type=radio name="tla_fetch_method" value="native" <?php echo get_option('tla_fetch_method') == 'native' ? 'checked' : '' ?>" /><?php endif; ?>
                        Default (sockets)<input type=radio name="tla_fetch_method" value="0" <?php echo !get_option('tla_fetch_method') ? 'checked' : '' ?>" />
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th>Styling Options</th><td><small><em>e.g. style="color:#CCC;" (use double quotes not single quotes)</em></small></em></td>
                </tr>
                 <tr valign="top">
                    <td scope="row">Style a</td>
                    <td><input type="text" name="tla_style_a" value='<?php echo get_option('tla_style_a') ? get_option('tla_style_a') : '' ?>' /><em>&lt;a ...&gt;</em></td>
                </tr>
                <tr valign="top">
                    <td scope="row">Style span</td>
                    <td><input type="text" name="tla_style_span" value='<?php echo get_option('tla_style_span') ? get_option('tla_style_span') : '' ?>' /><em>&lt;span ...&gt; </em></td>
                </tr>
                <tr valign="top">
                    <td scope="row">Style ul</td>
                    <td><input type="text" name="tla_style_ul" value='<?php echo get_option('tla_style_ul') ? get_option('tla_style_ul') : '' ?>' /><em>&lt;ul ...&gt; For Widget Mode only</em></td>
                </tr>
                <tr valign="top">
                    <td scope="row">Style li</td>
                    <td><input type="text" name="tla_style_li" value='<?php echo get_option('tla_style_li') ? get_option('tla_style_li') : '' ?>' /><em>&lt;li ...&gt; For Widget Mode only</em></td>
                </tr>
                <tr>
                    <td colspan=2>            
                        <p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
                    </td>
                </tr>
                <?php if (!is_file($textlinkads_object->htaccess_file)): ?>
                <tr>
                    <td valign="top"><p ><div class="warning">Optional Security Additions. We want to protect your privacy. Please make sure that your <strong>plugins directory is writable</strong> or add a file named <strong>.htaccess</strong> in your <strong>text-link-ads</strong> plugin directory with the code in the textbox to your right:</div> <strong><?php echo $textlinkads_object->htaccess_file; ?></strong></p></td>
                    <td><br /><textarea cols="30" rows="10"><?php echo $textlinkads_object->htaccess(); ?></textarea>
                </td>
                </tr>
                <?php endif; ?>
            </table>
        </form>
    </div>
    <?php 
}

function tla_widget_installed() 
{
    if (!function_exists('wp_get_sidebars_widgets')) return;
    $widgets = wp_get_sidebars_widgets(); 
    foreach ($widgets as $widget) {
        if (is_array($widget)) {
             foreach ($widget as $wid) {
                 if (stripos($wid, 'textlinkads-widget') !== false) {
                    return true;
                 }
             }
        } else {
            if (stripos($widget, 'textlinkads-widget') !== false) {
                return true;
            }
        }
    }
}

function tla_between_posts() 
{
    return get_option('tla_between_posts');
}

function tla_initialize()
{
    global $wpdb, $textlinkads_object;
    $textlinkads_object = new textlinkadsObject;
    $textlinkads_object->initialize();
    if (isset($_REQUEST['textlinkads_key']) && isset($_REQUEST['textlinkads_action'])) {
        if (in_array($_REQUEST['textlinkads_key'], array_values($textlinkads_object->websiteKeys))) {
            switch($_REQUEST['textlinkads_action']) {
                case 'debug_tla':
                case 'debug':
                    $textlinkads_object->debug(isset($_REQUEST['textlinkads_reset_index']) ? $_REQUEST['textlinkads_reset_index'] : '');
                    exit;
        
                case "refresh":
                case "refresh_tla":
                    echo "refreshing";
                    tla_refresh();
                    echo "refreshing complete";
                    break;
                    
            }
        }
    }
}

function tla_refresh()
{
    global $textlinkads_object;
    if (get_option('tla_last_update') < date('Y-m-d H:i:s', time() - 60)) {
        $textlinkads_object->cleanCache();
        $textlinkads_object->updateLocalAds();
    }            
}
                
function tla_check_installation()
{
    global $textlinkads_object;

    $textlinkads_object = new textlinkadsObject;
    $textlinkads_object->checkInstallation();
}

/** WP version less than 2.8 widget functions */
if (!tla_between_posts()) {
    if ($wp_version < 2.8) {
        function textlinkads_widget_init()
        {
            if (!function_exists('register_sidebar_widget') || !function_exists('register_widget_control')) return;
            register_sidebar_widget('textlinkads', 'textlinkads_widget');
            register_widget_control('textlinkads', 'textlinkads_widget_control');
        }
         
        function textlinkads_widget($args)
        {
            extract($args);
            global $textlinkads_object;
            if (!$textlinkads_object->ads) {
                return;
            } 
            $options = get_option('widget_textlinkads');
            $title = $options['title'];
            $before_widget = str_replace('textlinkads', '', $before_widget);
            echo $before_widget;
            echo $before_title . $title . $after_title;
            tla_ads();
            echo $after_widget;
        }
        
        function textlinkads_widget_control()
        {
            $options = $newoptions = get_option('widget_textlinkads');
            global $textlinkads_object;

            if (isset($_POST['textlinkads-title'])) {
                $newoptions['title'] = strip_tags(stripslashes($_POST['textlinkads-title']));
            }
        
            if ($options != $newoptions) {
                $options = $newoptions;
                update_option('widget_textlinkads', $options);
            }
        
            ?>
            <p><label for="textlinkads-title">Title: <input type="text" style="width: 250px;" id="textlinkads-title" name="textlinkads-title" value="<?php echo htmlspecialchars($options['title']); ?>" /></label></p>
            <input type="hidden" name="textlinkads-submit" id="textlinkads-submit" value="1" />
        <?php
        }
    // 2.8 + Api Additions    
    } else {
        class textlinkads_Widget extends WP_Widget
        {
            function textlinkads_Widget()
            {
                parent::WP_Widget(false, $name = 'Text Link Ads');
            }
        
            function widget($args, $instance)
            {
                global $textlinkads_object;
                if (!$textlinkads_object->ads) {
                    return;
                } 
                extract($args);
                $title = apply_filters('widget_title', empty($instance['title']) ? __('Links of Interest') : $instance['title']);
                $before_widget = str_replace('textlinkads', '', $before_widget);
                echo $before_widget;
                echo $before_title . $title . $after_title;
                tla_ads();
                echo $after_widget;
            }
            
            function form($instance) 
            {
                global $textlinkads_object;
                $instance = wp_parse_args((array)$instance, array('title' => ''));
                $title = esc_attr($instance['title']);
                ?>
                <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
                <?php 
            }
            
            function update($new_instance, $old_instance) 
            {
                $instance = $old_instance;
                if ($new_instance['title']) $instance['title'] = strip_tags(stripslashes($new_instance['title']));
                return $instance;
            }
        }
    }
} else if (tla_widget_installed()) {
    if (!function_exists('unregister_sidebar_widget') || !function_exists('unregister_widget_control')) return;
    unregister_sidebar_widget('textlinkads', 'textlinkads_widget');
    unregister_widget_control('textlinkads', 'textlinkads_widget_control');
}


function tla_ads()
{
    global $textlinkads_object;
    $textlinkads_object->outputHtmlAds();
}

function tla_between_content_show($content) 
{
    global $wpdb, $textlinkads_object;
    $adlink = '';

    if (!$textlinkads_object) {
        $textlinkads_object = new textlinkadsObject;
        $textlinkads_object->initialize();
    }

    if (is_home() || is_front_page()) {
        for ($z = 0; $z < $textlinkads_object->num_ads_per_post; $z++) {
            if ($ads = $textlinkads_object->ads[$textlinkads_object->nextAd++]) {
                if ($textlinkads_object->style_span) {
                    $adlink .= '<span ' . $textlinkads_object->style_span . '>';
                }
                $adlink .= $ads->before_text . ' <a';
                if ($textlinkads_object->style_a) {
                    $adlink .= ' ' . $textlinkads_object->style_a;
                }
                $adlink .= ' href="' . $ads->url . '">' . $ads->text.'</a> ' . $ads->after_text;
                if ($textlinkads_object->style_span) {
                    $adlink .= '</span>';
                }
            }
        }
    }
    return $content . $adlink;
}

class textlinkadsObject
{
    var $websiteKey = '';
    var $websiteKeys = array();
    var $xmlRefreshTime = 900;
    var $connectionTimeout = 10;
    var $DataTable = 'tla_data';
    var $version = '3.9.8';
    var $ads;

    function textlinkadsObject()
    {
        global $table_prefix;
        $this->DataTable = $table_prefix . $this->DataTable;
        
         //overwrite default key if set in options
        $this->siteKeys = maybe_unserialize(get_option('tla_site_keys'));
        if ($this->websiteKey && (!is_array($this->siteKeys) || count($this->siteKeys) == 0)) {
            add_option('tla_site_keys', serialize(array('0' => array('url' => get_option('siteurl'), 'key' => $this->websiteKey))));
            $this->siteKeys = maybe_unserialize(get_option('tla_site_keys'));
        } 
        
        if (is_array($this->siteKeys)) foreach ($this->siteKeys as $data) {
            $this->websiteKeys[trim($data['url'])] = trim($data['key']);
        } 
        
    }

    function debug()
    {
        global $wpdb, $wp_version;
        if ($wpdb->get_var("SHOW TABLES LIKE '" . $this->DataTable . "'") != $this->DataTable) {
            $installed = 'N';
        } else {
            $installed = 'Y';
            $data = print_r($wpdb->get_results("SELECT * FROM `" . $this->DataTable . "`"), true);
        }
        header('Content-type: application/xml');
        echo "<?xml version=\"1.0\" ?>\n";
        ?>
        <info>
        <lastRefresh><?php echo get_option('tla_last_update') ?></lastRefresh>
        <version><?php echo $this->version ?></version>
        <caching><?php echo defined('WP_CACHE') ? 'Y' : 'N' ?></caching>
        <phpVersion><?php echo phpversion() ?></phpVersion>
        <engineVersion><?php echo $wp_version ?></engineVersion>
        <installed><?php echo $installed ?></installed>
        <data><![CDATA[<?php echo $data ?>]]></data>
        </info> 
        <?php            
    }

    function installDatabase()
    {
        global $wpdb;

        require_once(ABSPATH . 'wp-admin/upgrade-functions.php');

        $sql = "DROP TABLE IF EXISTS `" . $this->DataTable . "`";
        $wpdb->query($sql);
        
        $sql = "CREATE TABLE `" . $this->DataTable . "` (
                  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `post_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                  `url` VARCHAR(255) NOT NULL,
                  `text` VARCHAR(255) NOT NULL,
                  `before_text` VARCHAR(255) NULL,
                  `after_text` VARCHAR(255) NULL,
                  `xml_key` VARCHAR(255) NULL,
                  PRIMARY KEY (`id`),
                  KEY `post_id` (`post_id`)
               ) AUTO_INCREMENT=1;";

        dbDelta($sql);
        $sql = "ALTER TABLE `" . $this->DataTable . "` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
        @$wpdb->query($sql);
        add_option('tla_last_update', '0000-00-00 00:00:00');

        if (!get_option('tla_between_posts')) {
            add_option('tla_between_posts', '');
        }

        if (!get_option('tla_site_keys') && $this->websiteKey) {
            add_option('tla_site_keys', serialize(array('0' => array('url' => get_option('siteurl'), 'key' => $this->websiteKey))));
        }
    }

    function checkInstallation()
    {
        global $wpdb;

        if ($wpdb->get_var("SHOW TABLES LIKE '" . $this->DataTable . "'") != $this->DataTable) {
            $this->installDatabase();
        }

        if (is_writable(dirname(__FILE__)) && !is_file($this->htaccess_file)) {
            $fh = fopen($this->htaccess_file, 'w+');
            fwrite($fh, $this->htaccess());
            fclose($fh); 
        }          

        if ($wpdb->get_var("SHOW COLUMNS FROM " . $this->DataTable . " LIKE 'xml_key'") != 'xml_key') {
            $wpdb->query("ALTER TABLE `" . $this->DataTable . "` ADD `xml_key` VARCHAR(20) NULL DEFAULT '' AFTER `after_text`;");
        }    
    }
    
    function htaccess()
    {
        return "<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule $ /index.php/404
</IfModule>";
    }
    
    function initialize()
    {
        global $wpdb;
        $where = '';
        $this->htaccess_file = dirname(__FILE__) . "/.htaccess";
        $this->checkInstallation();
        $this->ads = array();
        $this->between_posts = get_option('tla_between_posts') ? get_option('tla_between_posts') : '';
        $this->style_a = get_option('tla_style_a') ? get_option('tla_style_a') : '';;
        $this->style_ul = get_option('tla_style_ul') ? get_option('tla_style_ul') : '';
        $this->style_li = get_option('tla_style_li') ? get_option('tla_style_li') : '';
        $this->style_span = get_option('tla_style_span') ? get_option('tla_style_span') : '';
        $this->fetch_method = get_option('tla_fetch_method');

        if (get_option('tla_last_update') < date('Y-m-d H:i:s', time() - $this->xmlRefreshTime) || get_option('tla_last_update') > date('Y-m-d H:i:s')) {
            $this->updateLocalAds();
        }

        if ($this->websiteKeys) {
            $home = @parse_url(get_option('siteurl'));
            if ($home) {
                $home = $home['scheme'] . '://' . $home['host'];
            } else {
                $home = get_option('siteurl');
            }
            $urlBase = $home . $_SERVER['REQUEST_URI'];
            $altBase = (substr($urlBase, -1) == '/') ? substr($urlBase, 0, -1) : $urlBase . '/';
            $pageKey = isset($this->websiteKeys[$urlBase]) ? $this->websiteKeys[$urlBase] : '';
            if (!$pageKey) {
                $pageKey = isset($this->websiteKeys[$altBase]) ? $this->websiteKeys[$altBase] : '';
            }
            if ($pageKey) {
                $this->ads = $wpdb->get_results("SELECT * FROM " . $this->DataTable . " WHERE xml_key='" . mysql_real_escape_string($pageKey) . "'");
            } 
        } 
        if (!$this->ads) {
        	return;
        }
        define('DONOTCACHEPAGE', true);
        $this->adsCount = count($this->ads);
        $this->nextAd = 0;
        $this->posts_per_page = get_option('posts_per_page');
        if ($this->posts_per_page < $this->adsCount) {
            $this->num_ads_per_post = ceil($this->adsCount / $this->posts_per_page);
        } else {
            $this->num_ads_per_post = $this->adsCount;
        }
    }

    function updateLocalAds()
    {
        global $wpdb;
        foreach ($this->websiteKeys as $url => $key) {
            $ads = 0;
            $query = '';
            $url = 'http://www.text-link-ads.com/xml.php?k=' . $key . '&l=wordpress-tla-3.9.8';

            if (function_exists('json_decode') && is_array(json_decode('{"a":1}', true))) {
                $url .= '&f=json';
            }

            update_option('tla_last_update', date('Y-m-d H:i:s'));

            if ($xml = $this->fetchLive($url)) {
                $links = $this->decode($xml);
                $wpdb->show_errors();
                $wpdb->query("DELETE FROM `" . $this->DataTable . "` WHERE xml_key='" . mysql_real_escape_string($key) . "' OR xml_key = ''");
                if ($links && is_array($links)) {
                    foreach ($links as $link) {
                        $postId = isset($link['PostID']) ? $link['PostID'] : 0;
                        if ($postId) {
                            continue;
                        }
                        $query .= " (
                            '" . mysql_real_escape_string($link['URL']) . "',
                            '" . mysql_real_escape_string($postId) . "',
                            '" . mysql_real_escape_string($key) . "',
                            '" . mysql_real_escape_string(trim($link['Text'])) . "',
                            '" . mysql_real_escape_string(trim($link['BeforeText'])) . "',
                            '" . mysql_real_escape_string(trim($link['AfterText'])) . "'
                        ),";
                        $ads++;
                    }
                    if ($ads){
                        $wpdb->query("INSERT INTO `" . $this->DataTable . "` (`url`, `post_id`, `xml_key`, `text`, `before_text`, `after_text`) VALUES " . substr($query, 0, strlen($query) - 1));
                    }
                }
                
            }
        }
    }

    function fetchLive($url)
    {
        $results = '';
        if (!function_exists('wp_remote_get')) {
            switch ($this->fetch_method) {
                case 'curl':
                    if (function_exists('curl_init')) {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectionTimeout);
                        curl_setopt($ch, CURLOPT_TIMEOUT, $this->connectionTimeout);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                        $results = curl_exec($ch);
                        curl_close($ch);
                        break;
                    }
                case 'native':
                    if (function_exists('file_get_contents')) {
                        if (PHP_VERSION >= '5.2.1') {
                            $fgt_options = stream_context_create(
                                array(
                                    'http' => array(
                                        'timeout' => $this->connectionTimeout
                                        )
                                    )
                            ); 
                            $results = @file_get_contents($url, 0, $fgt_options);
                        } else {
                            ini_set('default_socket_timeout', $this->connectionTimeout);
                            $results = @file_get_contents($url);
                        }                    
                        break;
                    }
                default:
                    $url = parse_url($url);
                    if ($handle = @fsockopen($url["host"], 80)) {
                        if (function_exists("socket_set_timeout")) {
                            socket_set_timeout($handle, $this->connectionTimeout, 0);
                        } else if (function_exists("stream_set_timeout")) {
                            stream_set_timeout($handle, $this->connectionTimeout, 0);
                        }
            
                        fwrite($handle, "GET $url[path]?$url[query] HTTP/1.0\r\nHost: $url[host]\r\nConnection: Close\r\n\r\n");
                        while (!feof($handle)) {
                            $results .= @fread($handle, 40960);
                        }
                        fclose($handle);
                    }
                    break;
            }
        } else {
            $results = wp_remote_get($url);
            if (!is_wp_error($results)) {
                $results = substr($results['body'], strpos($results['body'], '<?'));
            } else {
                $results = '';
            }
        }

        $return = '';
        $capture = false;
        foreach (explode("\n", $results) as $line) {
            $char = substr(trim($line), 0, 1);
            if ($char == '[' || $char == '<') {
                $capture = true;
            }

            if ($capture) {
                $return .= $line . "\n";
            }
        }

        return $return;
    }

    function decode($str)
    {
        if (!function_exists('html_entity_decode')) {
            function html_entity_decode($string)
            {
               // replace numeric entities
               $str = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\1"))', $str);
               $str = preg_replace('~&#([0-9]+);~e', 'chr(\1)', $str);
               // replace literal entities
               $transTable = get_html_translation_table(HTML_ENTITIES);
               $transTable = array_flip($transTable);
               return strtr($str, $transTable);
            }
        }

        if (substr($str, 0, 1) == '[') {
            $arr = json_decode($str, true);
            foreach ($arr as $i => $a) {
                foreach ($a as $k => $v) {
                    $arr[$i][$k] = $this->decodeStr($v);
                }
            }

            return $arr;
        }

        $out = array();
        $returnData = array();

        preg_match_all("/<(.*?)>(.*?)</", $str, $out, PREG_SET_ORDER);
        $n = 0;
        while (isset($out[$n])) {
            $returnData[$out[$n][1]][] = $this->decodeStr($out[$n][0]);
            $n++;
        }

        if (!$returnData) {
            return false;
        }

        $arr = array();
        $count = count($returnData['URL']);
        for ($i = 0; $i < $count; $i++) {
            $arr[] = array(
                'BeforeText' => $returnData['BeforeText'][$i],
                'URL' => $returnData['URL'][$i],
                'Text' => $returnData['Text'][$i],
                'AfterText' => $returnData['AfterText'][$i],
            );
        }

        return $arr;
    }

    function decodeStr($str)
    {
        $search_ar = array('&#60;', '&#62;', '&#34;');
        $replace_ar = array('<', '>', '"');
        return str_replace($search_ar, $replace_ar, html_entity_decode(strip_tags($str)));
    }

    function outputHtmlAds()
    {
        foreach ($this->ads as $key => $ad) {
            if (trim($ad->text) == '' && trim($ad->before_text) == '' && trim($ad->after_text) == '') unset($this->ads[$key]);
        }

        if (count($this->ads) > 0) {
            echo "\n<ul";
            if ($this->style_ul) {
                echo ' '.$this->style_ul.'>'."\n";
            } else {
                echo '>';
            }
            foreach ($this->ads as $ads) {
                echo "<li";
                if ($this->style_li) {
                    echo ' '.$this->style_li.'>';
                } else {
                    echo ">";
                }
                if ($this->style_span) {
                    echo '<span '.$this->style_span.'>';
                }
                echo $ads->before_text.' <a';
                if ($this->style_a) {
                    echo ' '.$this->style_a;
                }
                echo ' href="'.$ads->url.'">'.$ads->text.'</a> '.$ads->after_text;
                if ($this->style_span) {
                 echo '</span>';
                }
                echo "</li>\n";
            }
            echo "</ul>";
        }
    }

    function cleanCache($posts=array())
    {
        if (!defined('WP_CACHE')) {
            return;   
        }

        if (count($posts) > 0) {
            //check wp-cache
            @include_once(ABSPATH . 'wp-content/plugins/wp-cache/wp-cache.php');
           
            if (function_exists('wp_cache_post_change')) {
                foreach ($posts as $post_id) {
                    wp_cache_post_change($post_id);
                }
            } else {
                //check wp-super-cache
                @include_once(ABSPATH . 'wp-content/plugins/wp-super-cache/wp-cache.php');  
                if (function_exists('wp_cache_post_change')) {
                    foreach ($posts as $post_id) {
                        wp_cache_post_change($post_id);
                    } 
                }
            }
        }
    } 
}
?>
