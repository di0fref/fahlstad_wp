<?php
class TwitterWidget {
	function control(){
		$data = get_option('twitter_widget_options');
		
		echo "<p><label>Title:<br /><input name='twitter_widget_title' type='text' value='{$data["twitter_widget_title"]}' /></label></p>";
		echo "<p><label>Screen Name:<br /><input name='twitter_widget_screen_name' type='text' value='{$data["twitter_widget_screen_name"]}' /></label></p>";
		echo "<p><label>Display number of Tweets:<br /><input name='twitter_widget_count' type='text' value='{$data["twitter_widget_count"]}' /></label></p>";
		
		if (isset($_POST['twitter_widget_title'])){
			$data['twitter_widget_title'] = attribute_escape($_POST['twitter_widget_title']);
			$data['twitter_widget_screen_name'] = attribute_escape($_POST['twitter_widget_screen_name']);
			$data['twitter_widget_count'] = attribute_escape($_POST['twitter_widget_count']);

			update_option('twitter_widget_options', $data);

		}	
	}
	function widget($args){
		$data = get_option('twitter_widget_options');
		echo $args['before_widget'];
		echo $args['before_title'] . $data["twitter_widget_title"] . $args['after_title'];
		echo 'I am your widget';
		echo $args['after_widget'];
	}
	function register(){
		register_sidebar_widget('Twitter', array('TwitterWidget', 'widget'));
		register_widget_control('Twitter', array('TwitterWidget', 'control'));
	}
}
add_action("widgets_init", array('TwitterWidget', 'register'));

?>