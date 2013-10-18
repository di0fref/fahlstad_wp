<?php
/*
Plugin Name: Twitter
*/

include("twitter.class.php");
$twitter = new Twitter();

/* Put this call in your theme sidebar or wherever you like the tweets to appear */
function get_tweets(){
	global $twitter;
	$twitter->getTweets();
}

add_action("init", array(&$twitter, "init"));
add_action('admin_menu', array(&$twitter, "adminMenu"));
add_action('wp_head', array(&$twitter, "twitterHeader"))
?>