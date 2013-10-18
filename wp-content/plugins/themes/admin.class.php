<?php
class fref_ThemeAdmin{
	public function adminMenu()
	{
		add_options_page('Theme Options', 'WP-Themes', 'manage_options', 'theme_options', array(&$this, "themeOptions"));
	}
	public function init($value='')
	{
		wp_dequeue_script("jquery");
		wp_enqueue_script("jquery");
		wp_register_script("themes_validation_script", plugins_url('/js/jquery.validate.min.js', __FILE__));
		wp_register_script("themes_script", plugins_url('/js/script.js', __FILE__));
		wp_enqueue_script("themes_validation_script");
		wp_enqueue_script("themes_script");
	}
	public function themeOptions($value='')
	{
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		if(isset($_REQUEST["themes_submit"])){
			$this->addTheme();
			echo '<div id="setting-error-settings_updated" class="updated settings-error"> 
			<p><strong>Theme added.</strong></p></div>';
		}
		if(isset($_REQUEST["themes_edit_submit"])){
			$this->updateTheme();
			echo '<div id="setting-error-settings_updated" class="updated settings-error"> 
			<p><strong>Theme saved.</strong></p></div>';
		}		
		if(isset($_REQUEST["theme_action"]) && $_REQUEST["theme_action"] == "delete"){
			$this->deleteTheme();
			echo '<div id="setting-error-settings_updated" class="updated settings-error"> 
			<p><strong>Theme deleted.</strong></p></div>';
		}
		if(isset($_REQUEST["themes_options_submit"])){
			$this->updateOptions();
			echo '<div id="setting-error-settings_updated" class="updated settings-error"> 
			<p><strong>Options saved.</strong></p></div>';
		}
		$themes = $this->getThemes();
	
		echo '<div class="wrap">';
		echo '<div id="icon-options-general" class="icon32"><br></div>';
		echo "<h2>Themes<a href='#' id='add_new_theme' class='add-new-h2'>Add New</a></h2>";	
		echo '<form method="post" action="" enctype="multipart/form-data" id="themes_new_form">';
		
		echo '<table class="form-table" id="add_new_table">';
		echo '<tr valign="top">
				<th scope="row"><label for="themes_name">Name: <span class="required">*</span></label></th>
					<td>
						<input name="themes_name" type="text" id="themes_name" value="" class="regular-text code">
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="themes_version">Version: <span class="required">*</span></label>
					</th>
						<td>
							<input name="themes_version" type="text" value="" class="small-text code">
						</td>
					</tr>
				<tr valign="top">
					<th scope="row"><label for="themes_zip">Zipfile: <span class="required">*</span></label></th>
					<td>
						<input name="themes_zip" type="file" id="themes_zip">
					</td>
				</tr>
				<tr valign="top">
				<th scope="row"><label for="themes_thumb">Screenshot: <span class="required">*</span></label></th>
					<td>
						<input name="themes_thumb" type="file" id="themes_thumb">
					</td>
				</tr>
				<tr valign="top">
				<th scope="row"><label for="themes_description">Description:</label></th>
					<td>
						<textarea style="width:300px" name="themes_description" id="themes_description" cols="45" rows="4"></textarea>
					</td>
				</tr>
				<tr valign="top">
				<th scope="row"></th>
					<td>
					<input type="submit" name="themes_submit" id="themes_submit" class="button-primary" value="Add Theme">
					</td>
				</tr>';
				
			echo "</table></form><br />";
			echo "<h2>Options<a href='#' id='edit_theme_options' class='add-new-h2'>Edit</a></h2>";
			echo '<form method="post" action="" enctype="multipart/form-data">';
			echo '<table class="form-table" id="edit_options_table">';
			echo '
					<tr valign="top">
						<th scope="row">
							<label for="themes_options_thumb_width">Thumb width:</label>
						</th>
						<td>
							<input name="themes_options_thumb_width" type="text" value="'.get_option("themes_options_thumb_width").'" class="small-text code"> px
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="themes_options_thumb_height">Thumb height:</label>
						</th>
						<td>
							<input name="themes_options_thumb_height" type="text" value="'.get_option("themes_options_thumb_height").'" class="small-text code"> px
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"></th>
						<td>
							<input type="submit" name="themes_options_submit" class="button-primary" value="Save Options">
						</td>
					</tr>';
					
			echo "</table></form><br />";
		
		$list = "
			<table class='wp-list-table widefat fixed pages'>
				<thead>
					<tr>
						<th>Name (Click to edit)</th>
						<th>Version</th>
						<th>Description</th>
						<th>Downloads</th>
						<th>Screenshot</th>
						<th>File</th>
					</tr>
				</thead>";
				foreach($themes as $theme){
					$url = FULL_URL.$theme["thumb"];
					$thumb = "<img src='".IMAGE_URL."{$theme["thumb"]}&amp;height=".get_option("themes_options_thumb_height")."&amp;width=".get_option("themes_options_thumb_width")."' alt='image' />";
					
					$list .= "<tr>
								<td><a class='theme_name_link' id='{$theme["id"]}' href='#'>{$theme["name"]}</a></td>
								<td>{$theme["version"]}</td>
								<td>{$theme["description"]}</td>
								<td>{$theme["downloads"]}</td>
								<td>$thumb</td>
								
								<td>{$theme["zip"]}</a></td>
							</tr>
							<tr class='themes_edit' id='theme_{$theme["id"]}'>
								<td colspan='6'></td>
							</tr>";
				}	
		$list .= "</table>";
		echo $list;
	}
	protected function addTheme()
	{
		global $table_prefix, $wpdb;
		move_uploaded_file($_FILES["themes_thumb"]["tmp_name"], THEMES_UPLOAD_PATH.$_FILES["themes_thumb"]["name"]);
		move_uploaded_file($_FILES["themes_zip"]["tmp_name"], THEMES_ZIP_PATH.$_FILES["themes_zip"]["name"]);
		
		$date = date("Y-m-d H:i:s");
		
		$sql = "INSERT INTO {$table_prefix}themes (`version`, `zip`, `name`, `description`, `thumb`, `date_added`, `date_updated`) 
			VALUES('{$_POST["themes_version"]}', '{$_FILES["themes_zip"]["name"]}', '{$_POST["themes_name"]}', '{$_POST["themes_description"]}','{$_FILES["themes_thumb"]["name"]}', '$date', '$date')";
			
		$wpdb->query($sql);
	}
	protected function updateOptions()
	{
		update_option("themes_options_thumb_width", $_POST["themes_options_thumb_width"]);
		update_option("themes_options_thumb_height", $_POST["themes_options_thumb_height"]);
	}
	protected function updateTheme()
	{
		global $table_prefix, $wpdb;
		$date = date("Y-m-d H:i:s");
		$additional_sql = array();
		$base_sql = "UPDATE {$table_prefix}themes SET 
			name = '{$_REQUEST["themes_name"]}',
			description = '{$_REQUEST["themes_description"]}',
			version = '{$_REQUEST["themes_version"]}',
			date_updated = '$date'";
			
		if($_FILES["themes_thumb"]["name"] != ""){
			move_uploaded_file($_FILES["themes_thumb"]["tmp_name"], THEMES_UPLOAD_PATH.$_FILES["themes_thumb"]["name"]);
			$additional_sql[] =  "thumb = '{$_FILES["themes_thumb"]["name"]}'";
		}
		if($_FILES["themes_zip"]["name"] != ""){
			move_uploaded_file($_FILES["themes_zip"]["tmp_name"], THEMES_ZIP_PATH.$_FILES["themes_zip"]["name"]);
			$additional_sql[] =  "zip = '{$_FILES["themes_zip"]["name"]}'";
		}
		$del = "";
		if(!empty( $additional_sql))
			$del = " , ";
		$sql = $base_sql . $del . implode(" , ", $additional_sql) . " WHERE id = '{$_REQUEST["theme_id"]}'";
		
		$wpdb->query($sql);
	}
	
	protected function deleteTheme()
	{
		global $table_prefix, $wpdb;
		$sql = "DELETE FROM {$table_prefix}themes WHERE id = '{$_REQUEST["theme_id"]}'";
		$wpdb->query($sql);
	}
	
	protected function getThemes()
	{
		global $table_prefix, $wpdb;
		$sql = "SELECT * FROM {$table_prefix}themes ORDER BY date_added DESC";
		return $wpdb->get_results($sql, ARRAY_A);
	}
	public function install()
	{
		if(!file_exists(THEMES_UPLOAD_PATH))
			mkdir(THEMES_UPLOAD_PATH, 0755, true);
			
		if(!file_exists(THEMES_ZIP_PATH))
			mkdir(THEMES_ZIP_PATH,  0755, true);	
			
		global $table_prefix;
		$sql = "CREATE TABLE IF NOT EXISTS  {$table_prefix}themes(
			`id` int(11) NOT NULL auto_increment,
			`name` varchar(255) NOT NULL default '',
			`downloads` int(11)  NULL default '0',
			`description` text  NULL default '',
			`thumb` varchar(255) NOT NULL default '',
			`zip` varchar(255) NOT NULL default '',
			`version` varchar(50) NOT NULL default '',
			`date_added` datetime NOT NULL default '0000-00-00 00:00:00',
			`date_updated` datetime NOT NULL default '0000-00-00 00:00:00',
			
			PRIMARY KEY  (`id`)
			)";

		require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
		dbDelta($sql);
	}
	public function adminHead()
	{
		?>
		<style type="text/css" media="screen">
			#add_new_table, #edit_options_table, .themes_edit{
				display:none;
			}
			.themes_edit{
				background:#eee !important;
			}
			#add_new_table .required{
				color:red;
			}
		</style>

		<?php
	}
	
}
?>