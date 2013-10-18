<?php
include("twitter_widget.php");
class Twitter{
	protected $url = "http://api.twitter.com/1/statuses/user_timeline.json?screen_name=%s&count=%s";
	protected $options;
	
	public function __construct(){
		$this->loadSettings();
	}
	
	public function init(){
		$this->loadSettings();
		wp_register_script("jquery_corner", plugins_url('/js/jquery.corner.js', __FILE__));
		wp_enqueue_script('jquery_corner');
		wp_register_script("twitter", plugins_url('/twitter.js', __FILE__));
		wp_enqueue_script('twitter');
	}
	
	public function getData(){
		return file_get_contents(sprintf($this->url, $this->options["username"], $this->options["count"]));
	}
	public function getTweets(){
		if($this->options["show_on_front_only"] == 1 and !is_front_page())
			return;
		
		echo "<div id='twitter_block'>
			<div id='twitter_logo'></div>
			<div id='twitter_entries'></div>
			<div id='twitter_follow'>
				<span id='twitter_follow_link'><a href='http://twitter.com/{$this->options["username"]}'>Follow me</a></span>
				<span id='twitter_refresh_link'><a href='#'>Refresh</a></span>
			</div>
		</div>";
	}
	
	protected function loadSettings($value=''){
		$this->options = array(
			'username'	=> get_option( "twitter_username" ),
			'count' 	=> get_option( "twitter_show_count" ),
			'show_on_front_only' => get_option( "show_on_front_only" ),
			'display_avatar' => get_option( "display_avatar" ),
		);
	}
	
	public function adminMenu($value=''){
		add_options_page('Twitter Options', 'Twitter', 'manage_options', 'twitter_options', array(&$this, "twitterOptions"));
	}
	
	public function twitterHeader(){
		?><link rel="stylesheet" href="<?php echo home_url();?>/wp-content/plugins/twitter/style.css" type="text/css" media="screen" title="no title" charset="utf-8"><?php
	}
	
	public function twitterOptions() {
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
	
		echo '<div class="wrap">';
		echo '<div id="icon-options-general" class="icon32"><br></div>';
		
		if(isset($_REQUEST["option_page"]) && $_REQUEST["option_page"] = "twitter_options"){
			$username = $_REQUEST["twitter_username"];
			$count = $_REQUEST["twitter_show_count"];
			$display = $_REQUEST["show_on_front_only"];
			$avatar = $_REQUEST["display_avatar"];
			
			update_option("twitter_show_count", $count);
			update_option("twitter_username", $username);
			update_option("show_on_front_only", $display);
			update_option("display_avatar", $avatar);
 
			echo '<div id="setting-error-settings_updated" class="updated settings-error"> 
			<p><strong>Settings saved.</strong></p></div>';
		}
		else{
			$count  = get_option( "twitter_show_count"); 
			$username  = get_option( "twitter_username" );
			$display = get_option("show_on_front_only");
			$avatar = get_option("display_avatar");
		}
		echo "<h2>Twitter Options</h2>";
		echo '<form method="post" action="">';

		settings_fields('twitter_options'); 
		if($display)
			$checked_checked  = "CHECKED";
		if($avatar)
			$avatar_checked  = "CHECKED";
		echo '<table class="form-table">';
		echo '<tr valign="top">
				<th scope="row"><label for="twitter_username">Twitter Username</label></th>
					<td>
						<input name="twitter_username" type="text" id="twitter_username" value="'.$username.'" class="small-text">
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="twitter_show_count">Tweets to display</label></th>
					<td>
						<input name="twitter_show_count" type="text" id="twitter_show_count" value="'.$count.'" class="small-text">
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="show_on_front_only">Display on font page only</label></th>
					<td>
						<input name="show_on_front_only" type="checkbox" id="show_on_front_only" '.$checked_checked.' value="1" class="small-text">
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="display_avatar">Display avatar</label></th>
					<td>
						<input name="display_avatar" type="checkbox" id="display_avatar" '.$avatar_checked.' value="1" class="small-text">
					</td>
				</tr>
				<tr>
					<td><input type="submit" name="twitter-submit" class="button-primary" value="' . __( 'Save Changes') . '" /></td>
					<td></td>
				</tr>';
	}
}
?>