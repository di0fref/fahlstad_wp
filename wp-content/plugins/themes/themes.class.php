<?php
class fref_Themes{
	
	protected $thumbs_path = "wp-content/theme_pics/";
	protected $download_path = "wp-content/downloads/";
	protected $themes;
	protected $output = "Enjoy.";
	protected $option_prefix = "themes_";
	protected $demo = "demo?theme=";
	protected $url = "http://fahlstad.se/";
	protected $tbl = "themes";
	
	public function __construct()
	{
	
	}
	
	public function init()
	{
		wp_register_script("front_script", plugins_url('/js/front.js', __FILE__));
		wp_enqueue_script("front_script");
	}
	

	public function display($content)
	{
		if(!preg_match('|<!--THEMES-->|', $content))	
			return $content;

		$this->loadThemes();
		$this->output .= '<div id="themes_wrap">';
		

		
		foreach($this->themes as $theme){

			$download_link = "/wp-content/plugins/themes/download.php?dl_theme=$theme->id";

			//$download_link = add_query_arg(array("file" => "11"), "/wp-content/plugins/themes/download.php");

			
			$thumb = "<img src='".IMAGE_URL."$theme->thumb&amp;height=".get_option("themes_options_thumb_height")."&amp;width=".get_option("themes_options_thumb_width")."' alt='image' />";
			$full = FULL_URL.$theme->thumb;
			$th = "<img src='$full' width='125px' />";
			$this->output .= "
				<table width='100%' border=1 cellspacing=0 cellpadding=0>
					<tr>
						<td class='theme_thumb' valign='top'><div class='shadow'><a href='$full'>$thumb</a></div></td>
						<td class='theme_name' valign='top'><span>$theme->name</span><br /><i><small>Updated: ".date("F jS, Y", strtotime($theme->date_updated))."<br>Version: $theme->version</small></i></td>
						<td class='theme_desc' valign='top'>$theme->description</td>
						<td valign='top'><a class='download_link' id='$theme->id' href='$download_link'>Download</a>";
						//$this->output .=" | <a href='#'>Demo</a>(Soon)</td>";
						$this->output .= " | <a href='http://demo.fahlstad.se?wptheme=$theme->name'>Demo</a>";
					$this->output .= "</tr></table><hr />";
	
		}
		$this->output .= "</div>";
		return preg_replace("|<!--THEMES-->|", $this->output, $content);
	}
	
	protected function loadThemes()
	{
		global $table_prefix, $wpdb;
		$sql = "SELECT * FROM {$table_prefix}themes ORDER BY date_added DESC";
		$this->themes = $wpdb->get_results($sql);
	}

	public function head()
	{
		?>
		<style type="text/css" media="screen">
			#themes_wrap{
				__background:#f4f4f4;
			}
			#themes_wrap td{
				_border:1px solid #ccc;
				border-collapse:collapse;
				padding:5px;
			}
			#themes_wrap td{
				
			}
			#themes_wrap td.theme_thumb img{
				padding:5px;
				background:none;
				_border:none;
			}
			#themes_wrap td.theme_thumb{
				width:<?php echo get_option("themes_options_thumb_width");?>px;
			}
			#themes_wrap td.theme_name{
				width:150px;
			}
			#themes_wrap td.theme_name span{
				font-weight:bold;
			}
			#themes_wrap td.theme_desc{
				width:230px;
				font-size:small;
				font-style:italic;
			}
			#themes_wrap hr{
				border-bottom:1px solid #eee;
				margin:15px 0 15px 0;
			}
			
			#themes_wrap .shadow {
				-moz-box-shadow: 2px 2px 5px #bbb;
				-webkit-box-shadow: 2px 2px 5px #bbb;
				box-shadow: 2px 2px 5px #bbb;
				/* For IE 8 */
				-ms-filter: "progid:DXImageTransform.Microsoft.Shadow(Strength=4, Direction=135, Color='#999999')";
				/* For IE 5.5 - 7 */
				filter: progid:DXImageTransform.Microsoft.Shadow(Strength=4, Direction=135, Color='#99999');
			}
		</style>

		<?php
	}
}



?>