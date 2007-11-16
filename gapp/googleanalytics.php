<?php
/*
Plugin Name: Google Analytics for WordPress
Plugin URI: http://www.joostdevalk.nl/wordpress/analytics/
Description: This plugin makes it simple to add Google Analytics with extra search engines and automatic clickout and download tracking to your WordPress blog. 
Author: Joost de Valk
Version: 1.6
Author URI: http://www.joostdevalk.nl/
License: GPL

Based on Rich Boakes' Analytics plugin: http://boakes.org/analytics

*/

$uastring = "UA-00000-0";

/*
 * Admin User Interface
 */

if ( ! class_exists( 'GA_Admin' ) ) {

	class GA_Admin {

		function add_config_page() {
			global $wpdb;
			if ( function_exists('add_submenu_page') ) {
				add_submenu_page('plugins.php', 'Google Analytics for WordPress Configuration', 'Google Analytics', 1, basename(__FILE__), array('GA_Admin','config_page'));
			}
		} // end add_GA_config_page()

		function config_page() {
			global $dlextensions;
			if ( isset($_POST['submit']) ) {
				if (!current_user_can('manage_options')) die(__('You cannot edit the Google Analytics for WordPress options.'));
				check_admin_referer('analyticspp-config');
				$options['uastring'] = $_POST['uastring'];

				if (isset($_POST['dlextensions']) && $_POST['dlextensions'] != "") 
					$options['dlextensions'] 	= strtolower($_POST['dlextensions']);
				if (isset($_POST['dlprefix']) && $_POST['dlprefix'] != "") 
					$options['dlprefix'] 		= strtolower($_POST['dlprefix']);

				if (isset($_POST['artprefix']) && $_POST['artprefix'] != "") 
					$options['artprefix'] 		= strtolower($_POST['artprefix']);
				if (isset($_POST['comprefix']) && $_POST['comprefix'] != "") 
					$options['comprefix'] 		= strtolower($_POST['comprefix']);
				if (isset($_POST['comautprefix']) && $_POST['comautprefix'] != "") 
					$options['comautprefix'] 	= strtolower($_POST['comautprefix']);
				if (isset($_POST['blogrollprefix']) && $_POST['blogrollprefix'] != "") 
					$options['blogrollprefix'] 	= strtolower($_POST['blogrollprefix']);
				if (isset($_POST['domainorurl']) && $_POST['domainorurl'] != "") 
					$options['domainorurl'] 	= $_POST['domainorurl'];

				$options['position'] = $_POST['position'];
				
				if (isset($_POST['extrase'])) {
					$options['extrase'] = true;
				} else {
					$options['extrase'] = false;
				}

				if (isset($_POST['trackoutbound'])) {
					$options['trackoutbound'] = true;
					$options['position'] = 'header';
				} else {
					$options['trackoutbound'] = false;
				}

				if (isset($_POST['imagese'])) {
					$options['imagese'] = true;
					$options['extrase'] = true;
				} else {
					$options['imagese'] = false;
				}

				if (isset($_POST['yahooreffirst'])) {
					$options['yahooreffirst'] = true;
				} else {
					$options['yahooreffirst'] = false;
				}

				if (isset($_POST['admintracking'])) {
					$options['admintracking'] = true;
				} else {
					$options['admintracking'] = false;
				}

				if (isset($_POST['userv2'])) {
					$options['userv2'] = true;
				} else {
					$options['userv2'] = false;
				}

				$opt = serialize($options);
				update_option('GoogleAnalyticsPP', $opt);
			}
			$mulch = ($uastring=""?"##-#####-#":$uastring);

			$opt  = get_option('GoogleAnalyticsPP');
			$options = unserialize($opt);
			?>
			<div class="wrap">
				<script type="text/javascript">
					function toggle_help(ele, ele2) {
						var expl = document.getElementById(ele2);
						if (expl.style.display == "block") {
							expl.style.display = "none";
							ele.innerHTML = "What's this?";
						} else {
							expl.style.display = "block";
							ele.innerHTML = "Hide explanation";
						}
					}
				</script>
				<h2>Google Analytics for WordPress Configuration</h2>
				<fieldset>
					<form action="" method="post" id="analytics-conf" style="width: 35em; ">
						<?php
						if ( function_exists('wp_nonce_field') )
							wp_nonce_field('analyticspp-config');
						?>
						<p>
							<strong><label for="uastring">Analytics User Account</label></strong>
							<small><a href="#" onclick="javascript:toggle_help(this, 'expl');">What's this?</a></small><br/>
							<input id="uastring" name="uastring" type="text" size="20" maxlength="40" value="<?php echo $options['uastring']; ?>" style="font-family: 'Courier New', Courier, mono; font-size: 1.5em;" /><br/>
							<div id="expl" style="display:none;">
								<h3>Explanation</h3>
								<p>Google Analytics is a statistics service provided
									free of charge by Google.  This plugin simplifies
									the process of including the <em>basic</em> Google
									Analytics code in your blog, so you don't have to
									edit any PHP. If you don't have a Google Analytics
									account yet, you can get one at 
									<a href="https://www.google.com/analytics/home/">analytics.google.com</a>.</p>

								<p>In the Google interface, when you "Add Website 
									Profile" you are shown a piece of JavaScript that
									you are told to insert into the page, in that script is a 
									unique string that identifies the website you 
									just defined, that is your User Account string
									(it's shown in <strong>bold</strong> in the example below).</p>
								<tt>&lt;script src="http://www.google-analytics.com/urchin.js" type="text/javascript"&gt;<br />&lt;/script&gt;<br />&lt;script type="text/javascript"&gt;<br />_uacct = "<strong><?php echo($mulch);?></strong>";<br />urchinTracker();<br />&lt;/script&gt;<br /></tt>

								<p>Once you have entered your User Account String in
								   the box above your pages will be trackable by
									Google Analytics.</p>
							</div>
							<?php if ($options['trackoutbound']) { ?>
							<strong><label for="dlextensions">Extensions of files to track as downloads</label></strong><br/>
							(If the extension is only two chars, prefix it with a dot, like '.js')
							<input type="text" name="dlextensions" size="40" value="<?php echo $options['dlextensions']; ?>" style="font-family: 'Courier New', Courier, mono; font-size: 1.5em;"/><br/>
							<br/>
							<strong><label for="dlprefix">Prefix for tracked downloads</label></strong><br/>
							<input type="text" id="dlprefix" name="dlprefix" size="40" value="<?php echo $options['dlprefix']; ?>" style="font-family: 'Courier New', Courier, mono; font-size: 1.5em;"/><br/>
							<br/>
							<strong><label for="artprefix">Prefix for outbound clicks from articles</label></strong><br/>
							<input type="text" id="artprefix" name="artprefix" size="40" value="<?php echo $options['artprefix']; ?>" style="font-family: 'Courier New', Courier, mono; font-size: 1.5em;"/><br/>
							<br/>
							<strong><label for="comprefix">Prefix for outbound clicks from within comments</label></strong><br/>
							<input type="text" id="comprefix" name="comprefix" size="40" value="<?php echo $options['comprefix']; ?>" style="font-family: 'Courier New', Courier, mono; font-size: 1.5em;"/><br/>
							<br/>
							<strong><label for="comautprefix">Prefix for outbound clicks from comment author links</label></strong><br/>
							<input type="text" id="comautprefix" name="comautprefix" size="40" value="<?php echo $options['comautprefix']; ?>" style="font-family: 'Courier New', Courier, mono; font-size: 1.5em;"/><br/>
							<br/>
							<strong><label for="blogrollprefix">Prefix for outbound clicks from blogroll links</label></strong><br/>
							<input type="text" id="blogrollprefix" name="blogrollprefix" size="40" value="<?php echo $options['blogrollprefix']; ?>" style="font-family: 'Courier New', Courier, mono; font-size: 1.5em;"/><br/>
							<br/>
							<strong><label for="domainorurl">Track full URL of outbound clicks or just the domain?</label></strong><br/>
							<select name="domainorurl" id="domainorurl" style="font-family: 'Courier New', Courier, mono; font-size: 1.5em;">
								<option value="domain"<?php if ($options['domainorurl'] == 'domain') { echo ' selected="selected"';} ?>>Just the domain</option>
								<option value="url"<?php if ($options['domainorurl'] == 'url') { echo ' selected="selected"';} ?>>Track the complete URL</option>
							</select><br/>
							<br/>
							<?php } else { ?>
							<strong><label for="position">Place the script in the header or in the footer?</label></strong><br/>
							<select name="position" id="position" style="font-family: 'Courier New', Courier, mono; font-size: 1.5em;">
								<option value="header"<?php if ($options['position'] == 'header') { echo ' selected="selected"';} ?>>Header</option>
								<option value="footer"<?php if ($options['position'] == 'footer') { echo ' selected="selected"';} ?>>Footer</option>
							</select><br/>
							<br/>
							<?php } ?>
							<input type="checkbox" id="trackoutbound" name="trackoutbound" <?php if ($options['trackoutbound']) echo ' checked="checked" '; ?>/> 
							<label for="trackoutbound">Track outbound clicks &amp; downloads</label><br/>
							<br/>
							<input type="checkbox" id="extrase" name="extrase" <?php if ($options['extrase']) echo ' checked="checked" '; ?>/> 
							<label for="extrase">Track extra Search Engines</label><br/>
							<br/>
							<input type="checkbox" id="imagese" name="imagese" <?php if ($options['imagese']) echo ' checked="checked" '; ?>/> 
							<label for="imagese">Track Google Image search keywords (this needs the tracking of extra search engines too) (<a href="http://www.joostdevalk.nl/google-analytics-and-google-image-search-revisited/">more info</a>)</label><br/>
							<br/>
							<input type="checkbox" id="yahooreffirst" name="yahooreffirst" <?php if ($options['yahooreffirst']) echo ' checked="checked" '; ?>/> 
							<label for="yahooreffirst">Track the keyword people used to search before they refined their search queries in Yahoo! (<a href="http://www.joostdevalk.nl/yahoos-search-assist-and-tracking-keywords/">more info</a>)</label><br/>
							<br/>
							<input type="checkbox" id="admintracking" name="admintracking" <?php if ($options['admintracking']) echo ' checked="checked" '; ?>/> 
							<label for="admintracking">Track the administrator too (default is not to)</label>
							<br/>
							<input type="checkbox" id="userv2" name="userv2" <?php if ($options['userv2']) echo ' checked="checked" '; ?>/> 
							<label for="userv2">I use Urchin too, so add <code>_userv = 2;</code></label>
						</p>
						<p class="submit"><input type="submit" name="submit" value="Update Settings &raquo;" /></p>
					</form>
				</fieldset>
			</div>
			<?php
			if (isset($options['uastring'])) {
				if ($options['uastring'] == "") {
					add_action('admin_footer', array('GA_Admin','warning'));
				} else {
					if (isset($_POST['submit'])) {
						if ($_POST['uastring'] != $options['uastring'] ) {
							add_action('admin_footer', array('GA_Admin','success'));
						}
					}
				}
			} else {
				add_action('admin_footer', array('GA_Admin','warning'));
			}

		} // end config_page()

		function restore_defaults() {
			$options['dlextensions'] = 'doc,exe,.js,pdf,ppt,tgz,zip,xls';
			$options['dlprefix'] = '/downloads';
			$options['artprefix'] = '/outbound/article';
			$options['comprefix'] = '/outbound/comment';
			$options['comautprefix'] = '/outbound/commentauthor';
			$options['blogrollprefix'] = '/outbound/blogroll';
			$options['domainorurl'] = 'domain';
			$options['position'] = 'header';
			$options['userv2'] = false;
			$options['extrase'] = false;
			$options['imagese'] = false;
			$options['trackoutbound'] = true;
			$opt = serialize($options);
			update_option('GoogleAnalyticsPP',$opt);
		}
		
		function success() {
			echo "
			<div id='analytics-warning' class='updated fade-ff0000'><p><strong>Congratulations! You have just activated Google Analytics - take a look at the source of your blog pages and search for the word 'urchin' to see how your pages have been affected.</p></div>
			<style type='text/css'>
			#adminmenu { margin-bottom: 7em; }
			#analytics-warning { position: absolute; top: 7em; }
			</style>";
		} // end success()

		function warning() {
			echo "
			<div id='analytics-warning' class='updated fade-ff0000'><p><strong>Google Analytics is not active.</strong> You must <a href='plugins.php?page=googleanalytics.php'>enter your UA String</a> for it to work.</p></div>
			<style type='text/css'>
			#adminmenu { margin-bottom: 6em; }
			#analytics-warning { position: absolute; top: 7em; }
			</style>";
		} // end warning()

	} // end class GA_Admin

} //endif


/**
 * Code that actually inserts stuff into pages.
 */
if ( ! class_exists( 'GA_Filter' ) ) {
	class GA_Filter {

		/*
		 * Insert the tracking code into the page
		 */
		function spool_analytics() {
			$opt  = get_option('GoogleAnalyticsPP');
			$options = unserialize($opt);
			
			if ($options["uastring"] != "" && (!current_user_can('edit_users') || $options["admintracking"]) ) {
				echo("\n\t<!-- Google Analytics for WordPress | http://www.joostdevalk.nl/wordpress/google-analytics/ -->\n");
				echo("\t<script src=\"http://www.google-analytics.com/urchin.js\" type=\"text/javascript\"></script>\n");	
				if ( $options["extrase"] == true ) {
					echo("\t<script src=\"".get_bloginfo('wpurl')."/wp-content/plugins/gapp/custom_se.js\" type=\"text/javascript\"></script>\n");
				}
				if ( $options['imagese'] && $options['yahooreffirst']) {
					echo("\t<script src=\"".get_bloginfo('wpurl')."/wp-content/plugins/gapp/track-imagesearch-and-yahoo.js\" type=\"text/javascript\"></script>\n");	
				} else if ( $options['imagese'] && !$options['yahooreffirst'] ) {
					echo("\t<script src=\"".get_bloginfo('wpurl')."/wp-content/plugins/gapp/track-imagesearch.js\" type=\"text/javascript\"></script>\n");	
				} else if ( !$options['imagese'] && $options['yahooreffirst'] ) {
					echo("\t<script src=\"".get_bloginfo('wpurl')."/wp-content/plugins/gapp/track-yahoo.js\" type=\"text/javascript\"></script>\n");	
				}
				echo("\t<script type=\"text/javascript\">\n");
				echo("\t\t_uacct = \"".$options["uastring"]."\";\n");
				if ( $options['userv2'] ) {
					echo("\t\t_userv=2;\n");
				}
				echo("\t\turchinTracker();\n");
				echo("\t</script>\n");
				echo("\t<!-- End of Google Analytics code -->\n\n");
			}
		}

		/* Create an array which contians:
		 * "domain" e.g. boakes.org
		 * "host" e.g. store.boakes.org
		 */
		function ga_get_domain($uri){

			$hostPattern = "/^(http:\/\/)?([^\/]+)/i";
			$domainPattern = "/[^\.\/]+\.[^\.\/]+$/";

			preg_match($hostPattern, $uri, $matches);
			$host = $matches[2];
			preg_match($domainPattern, $host, $matches);
			return array("domain"=>$matches[0],"host"=>$host);    

		}

		/* Take the result of parsing an HTML anchor ($matches)
		 * and from that, extract the target domain.  If the 
		 * target is not local, then when the anchor is re-written
		 * then an urchinTracker call is added.
		 *
		 * the format of the outbound link is definedin the $leaf
		 * variable which must begin with a / and which may 
		 * contain multiple path levels:
		 * e.g. /outbound/x/y/z 
		 * or which may be just "/"
		 *
		 */
		function ga_parse_link($leaf, $matches){
			global $origin ;
			
			$opt  = get_option('GoogleAnalyticsPP');
			$options = unserialize($opt);
			
			$target = GA_Filter::ga_get_domain($matches[3]);
			$coolbit = "";
			$extension = substr($matches[3],-3);
			$dlextensions = split(",",$options['dlextensions']);
			if ( $target["domain"] != $origin["domain"] ){
				if ($options['domainorurl'] == "domain") {
					$coolBit .= "onclick=\"javascript:urchinTracker('".$leaf."/".$target["host"]."');\"";
				} else if ($options['domainorurl'] == "url") {
					$coolBit .= "onclick=\"javascript:urchinTracker('".$leaf."/".$matches[2]."//".$matches[3]."');\"";
				}
			} else if ( in_array($extension, $dlextensions) && $target["domain"] == $origin["domain"] ) {
				$file = str_replace($origin["domain"],"",$matches[3]);
				$file = str_replace('www.',"",$file);
				$coolBit .= "onclick=\"javascript:urchinTracker('".$options['dlprefix'].$file."');\"";
			}
			return '<a href="' . $matches[2] . '//' . $matches[3] . '"' . $matches[1] . $matches[4] . ' '.$coolBit.'>' . $matches[5] . '</a>';    
		}

		function ga_parse_article_link($matches){
			$opt  = get_option('GoogleAnalyticsPP');
			$options = unserialize($opt);
			return GA_Filter::ga_parse_link($options['artprefix'],$matches);
		}

		function ga_parse_comment_link($matches){
			$opt  = get_option('GoogleAnalyticsPP');
			$options = unserialize($opt);
			return GA_Filter::ga_parse_link($options['comprefix'],$matches);
		}

		function the_content($text) {
			if (!current_user_can('edit_users')|| $options['admintracking'] ) {
				static $anchorPattern = '/<a (.*?)href="(.*?)\/\/(.*?)"(.*?)>(.*?)<\/a>/i';
				$text = preg_replace_callback($anchorPattern,array('GA_Filter','ga_parse_article_link'),$text);
			}
			return $text;
		}

		function comment_text($text) {
			if (!current_user_can('edit_users')|| $options['admintracking'] ) {
				static $anchorPattern = '/<a (.*?)href="(.*?)\/\/(.*?)"(.*?)>(.*?)<\/a>/i';
				$text = preg_replace_callback($anchorPattern,array('GA_Filter','ga_parse_comment_link'),$text);
			}
			return $text;
		}

		function comment_author_link($text) {
			if (!current_user_can('edit_users')|| $options['admintracking'] ) {
				$opt  = get_option('GoogleAnalyticsPP');
				$options = unserialize($opt);
	
		        static $anchorPattern = '/(.*\s+.*?href\s*=\s*)["\'](.*?)["\'](.*)/';
				preg_match($anchorPattern, $text, $matches);
				if ($matches[2] == "") return $text;
	
				$target = GA_Filter::ga_get_domain($matches[2]);
				$coolbit = "";
				$origin = GA_Filter::ga_get_domain($_SERVER["HTTP_HOST"]);
				if ( $target["domain"] != $origin["domain"]  ){
					if ($options['domainorurl'] == "domain") {
						$coolBit .= "onclick=\"javascript:urchinTracker('".$options['comautprefix']."/".$target["host"]."');\"";
					} else if ($options['domainorurl'] == "url") {
						$coolBit .= "onclick=\"javascript:urchinTracker('".$options['comautprefix']."/".$matches[2]."');\"";
					}
				} 
				return $matches[1] . "\"" . $matches[2] . "\" " . $coolBit ." ". $matches[3];    
			} else {
				return $text;
			}
		}
		
		function bookmarks($bookmarks) {
			if (!is_admin() && (!current_user_can('edit_users') || $options['admintracking'] ) ) {
				$opt  = get_option('GoogleAnalyticsPP');
				$options = unserialize($opt);

				foreach ( (array) $bookmarks as $bookmark ) {
					if ($options['domainorurl'] == "domain") {
						$target = GA_Filter::ga_get_domain($bookmark->link_url);
						$bookmark->link_rel = $bookmark->link_rel."\" onclick=\"javascript:urchinTracker('".$options['blogrollprefix']."/".$target["host"]."');\"";
					} else if ($options['domainorurl'] == "url") {
						$bookmark->link_rel = $bookmark->link_rel."\" onclick=\"javascript:urchinTracker('".$options['blogrollprefix']."/".$bookmark->link_url."');\"";
					}
				}
			}
			return $bookmarks;
		}
	} // class GA_Filter
} // endif

$version = "0.61";
$uakey = "analytics";

if (function_exists("get_option")) {
	if ($wp_uastring_takes_precedence) {
		$opt  = get_option('GoogleAnalyticsPP');
		$options = unserialize($opt);
		$uastring = $options['uastring'];
	}
} 

$mulch = ($uastring=""?"##-#####-#":$uastring);
$gaf = new GA_Filter();
$origin = $gaf->ga_get_domain($_SERVER["HTTP_HOST"]);

$opt  = get_option('GoogleAnalyticsPP',"");

if ($opt == "") {
	$options['dlextensions'] = 'doc,exe,.js,pdf,ppt,tgz,zip,xls';
	$options['dlprefix'] = '/downloads';
	$options['artprefix'] = '/outbound/article';
	$options['comprefix'] = '/outbound/comment';
	$options['comautprefix'] = '/outbound/commentauthor';
	$options['blogrollprefix'] = '/outbound/blogroll';
	$options['domainorurl'] = 'domain';
	$options['position'] = 'header';
	$options['userv2'] = false;
	$options['extrase'] = false;
	$options['imagese'] = false;
	$options['trackoutbound'] = true;
	$opt = serialize($options);
	update_option('GoogleAnalyticsPP',$opt);
} else {
	$options = unserialize($opt);
}

// adds the menu item to the admin interface
add_action('admin_menu', array('GA_Admin','add_config_page'));

//get_currentuserinfo();

	// adds the footer so the javascript is loaded
	if ($options['position'] =='footer') {
		add_action('wp_footer', array('GA_Filter','spool_analytics'));	
	} else if ($options['position'] =='header') {
		add_action('wp_head', array('GA_Filter','spool_analytics'));	
	}

	if ($options['trackoutbound']) {
		// filters alter the existing content
		add_filter('the_content', array('GA_Filter','the_content'), 99);
		add_filter('the_excerpt', array('GA_Filter','the_content'), 99);
		add_filter('comment_text', array('GA_Filter','comment_text'), 99);
		add_filter('get_bookmarks', array('GA_Filter','bookmarks'), 99);
		add_filter('get_comment_author_link', array('GA_Filter','comment_author_link'), 99);
	}
//}
?>
