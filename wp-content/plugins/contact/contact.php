<?php
/*
Plugin Name: ContactForm
Plugin Author: Fredrik Fahlstad
*/
include("Akismet.class.php");
class ContactForm{
	
	protected $akismet, $name, $email, $url, $message;
	public function __construct()
	{	
		$this->akismet = new Akismet(get_bloginfo("url"), "7219625ea7ef");
	}
	
	public function display($content)
	{
		if(!preg_match("|<!--CONTACTFORM-->|", $content))
			return $content;
			
		$data = $this->getForm();
		
		if(isset($_REQUEST["contact_submit"])){
			$this->name = stripslashes(trim($_REQUEST["_name"]));
			$this->email = stripslashes(trim($_REQUEST["email"])); 
			$this->url = stripslashes(trim($_REQUEST["url"]));
			$this->message = stripslashes(trim($_REQUEST["message"]));
			$message = $this->processMessage();
			$data = "<h3>Thank you</h3><p>I'll get back to at my earliest convince.</p>";
		}
		
		return preg_replace("|<!--CONTACTFORM-->|", $data, $content);
	}
	protected function processMessage()
	{
		if($this->isSpam()){
			return;
		}
		$headers = "MIME-Version: 1.0\n";
		$headers .= "From: $this->name <$this->email>\n";
		$headers .= "Content-Type: text/plain; charset=\"" . get_settings('blog_charset') . "\"\n";
		
		$recipient = "fredrik@fahlstad.se";
		$subject = "WordPress Contact Form";
		$fullmsg = "$this->name wrote:\n";
		$fullmsg .= wordwrap($this->message, 80, "\n") . "\n\n";
		$fullmsg .= "Website: " . $this->url . "\n";
		$fullmsg .= "IP: " . $this->getip();
				
		mail($recipient, $subject, $fullmsg, $headers);
	}
	
	public function init()
	{
		wp_dequeue_script("jquery");
		wp_enqueue_script("jquery");
		wp_register_script("contact_validation_script", plugins_url('/jquery.validate.min.js', __FILE__));
		wp_register_script("contact_script", plugins_url('/script.js', __FILE__));
		wp_enqueue_script("contact_validation_script");
		wp_enqueue_script("contact_script");
	}
	
	/* Using akismet */
	protected function isSpam()
	{
		$this->akismet->setCommentAuthor($this->name);
		$this->akismet->setCommentAuthorEmail($this->email);
		$this->akismet->setCommentAuthorURL($this->url);
		$this->akismet->setCommentContent($this->message);
		$this->akismet->setPermalink(get_permalink());

		if($this->akismet->isCommentSpam()){
			return true;
		}
		else{
			return false;
		}
	}
	
	protected function getForm()
	{
		return '<div id="contact_block"><p>To contact me, please fill out this form.</p>
		<form id="contact_form" name="contact_form" method="post" action="'.get_permalink().'">
			<div class="field">
				<label for="name" class="form_label">Name: <span class="required">*</span></label>
				<input type="text" name="_name" value="" id="name" />
			</div>
			
			<div class="field">
				<label for="email" class="form_label">Email: <span class="required">*</span></label>
				<input type="text" name="email" value="" id="email" />
			</div>

			<div class="field">
				<label for="url" class="form_label">Website:</label>
				<input type="text" name="url" value="" id="url" />
			</div>
			
			<div class="field">
				<label for="message" class="form_label">Message: <span class="required">*</span></label>
				<textarea name="message" rows="8" cols="40"></textarea>
			</div>
			
			<div class="field">
				<input type="submit" name="contact_submit" value="Send" id="contact_submit" />
			</div>
		</form></div>';
	}
	function header(){
		?>
		<style type="text/css">
			#contact_block form .field label.form_label {
				display: inline;
				float: left;
				width: 100px;
			}
			#contact_block form input{
				margin: 0;
				vertical-align: baseline;
			}
			#contact_block form .field {
				clear: both;
				margin-bottom:10px;
			}
			#contact_block #contact_submit{
				margin-left:100px;
			}
			#contact_block .required{
				color:red;
			}
			label.error { float: none; color: red; padding-left: .5em; vertical-align: top; }
			label.valid { float: none; color: green; padding-left: .5em; vertical-align: top; }
			.yellow{
				background:#fcfce3;
			}
			.is_valid{
				background:url(wp-content/plugins/contact/images/ok.png) 0 0 no-repeat;
				width:20px;
				height:20px;
				display:inline-block;
				margin-left:5px;
			}
		</style>
		<?php
	}
	protected function getip()
	{
		if (isset($_SERVER))
		{
	 		if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
	 		{
	  			$ip_addr = $_SERVER["HTTP_X_FORWARDED_FOR"];
	 		}
	 		elseif (isset($_SERVER["HTTP_CLIENT_IP"]))
	 		{
	  			$ip_addr = $_SERVER["HTTP_CLIENT_IP"];
	 		}
	 		else
	 		{
	 			$ip_addr = $_SERVER["REMOTE_ADDR"];
	 		}
		}
		else
		{
	 		if ( getenv( 'HTTP_X_FORWARDED_FOR' ) )
	 		{
	  			$ip_addr = getenv( 'HTTP_X_FORWARDED_FOR' );
	 		}
	 		elseif ( getenv( 'HTTP_CLIENT_IP' ) )
	 		{
	  			$ip_addr = getenv( 'HTTP_CLIENT_IP' );
	 		}
	 		else
	 		{
	  			$ip_addr = getenv( 'REMOTE_ADDR' );
	 		}
		}
	return $ip_addr;
	}

}
function save_error(){
    update_option('plugin_error',  ob_get_contents());
}
register_activation_hook(__FILE__, "save_error");

$cf = new ContactForm();
add_action("init", array(&$cf, "init"));
add_action("wp_head", array(&$cf, "header"));
add_action("the_content", array(&$cf, "display"));
add_action('activated_plugin','save_error');
?>