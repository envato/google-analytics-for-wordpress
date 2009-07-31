<?php
/*
Plugin Name: Google Analytics for WordPress
Plugin URI: http://yoast.com/wordpress/analytics/
Description: This plugin makes it simple to add Google Analytics with extra search engines and automatic clickout and download tracking to your WordPress blog. 
Author: Joost de Valk
Version: 3.0.1
Requires at least: 2.7
Author URI: http://yoast.com/
License: GPL

*/

// Determine the location
$gapppluginpath = plugins_url('', __FILE__).'/';

/*
 * Admin User Interface
 */

if ( ! class_exists( 'GA_Admin' ) ) {

	require_once('yst_plugin_tools.php');
	
	class GA_Admin extends Yoast_Plugin_Admin {

		var $hook 		= 'google-analytics';
		var $filename	= 'google-analytics-for-wordpress/googleanalytics.php';
		var $longname	= 'Google Analytics Configuration';
		var $shortname	= 'Google Analytics';
		var $ozhicon	= 'chart_curve.png';
		var $optionname = 'GoogleAnalyticsPP';
		var $homepage	= 'http://yoast.com/wordpress/analytics/';
		

		function GA_Admin() {
			add_action( 'admin_menu', array(&$this, 'register_settings_page') );
			add_filter( 'plugin_action_links', array(&$this, 'add_action_link'), 10, 2 );
			add_filter( 'ozh_adminmenu_icon', array(&$this, 'add_ozh_adminmenu_icon' ) );				
			
			add_action('admin_print_scripts', array(&$this,'config_page_scripts'));
			add_action('admin_print_styles', array(&$this,'config_page_styles'));	
			
			add_action('wp_dashboard_setup', array(&$this,'widget_setup'));	
			add_action('admin_head', array(&$this,'config_page_head'));
		}
		
		function config_page_head() {
			if ($_GET['page'] == $this->hook) {
				wp_enqueue_script('jquery');
			?>
				 <script type="text/javascript" charset="utf-8">
				 	jQuery(document).ready(function(){
						jQuery('#explanation td').css("display","none");
						jQuery('#advancedsettings').change(function(){
							if ((jQuery('#advancedsettings').attr('checked')) == true)  {
								jQuery('#advancedgasettings').css("display","block");
							} else {
								jQuery('#advancedgasettings').css("display","none");
							}
						}).change();
						jQuery('#explain').click(function(){
							if ((jQuery('#explanation').css("display")) == "block")  {
								jQuery('#explanation').css("display","none");
							} else {
								jQuery('#explanation').css("display","block");
							}
						});
					});
				 </script>
			<?php
			}
		}
				
		function checkbox($id) {
			$options = get_option($this->optionname);
			return '<input type="checkbox" id="'.$id.'" name="'.$id.'"'. checked($options[$id],true,false).'/>';
		}

		function textinput($id) {
			$options = get_option($this->optionname);
			return '<input type="text" id="'.$id.'" name="'.$id.'" size="30" value="'.$options[$id].'"/>';
		}
		
		function config_page() {
			global $dlextensions, $gapppluginpath;
			if ( isset($_POST['reset']) && $_POST['reset'] == "true") {
				$options['dlextensions'] = 'doc,exe,.js,pdf,ppt,tgz,zip,xls';
				$options['dlprefix'] = '/downloads';
				$options['artprefix'] = '/outbound/article';
				$options['comprefix'] = '/outbound/comment';
				$options['comautprefix'] = '/outbound/commentauthor';
				$options['blogrollprefix'] = '/outbound/blogroll';
				$options['domainorurl'] = 'domain';
				$options['userv2'] = false;
				$options['extrase'] = false;
				$options['imagese'] = false;
				$options['admintracking'] = true;
				$options['trackoutbound'] = true;
				$options['advancedsettings'] = false;
				$options['allowanchor'] = false;
				update_option('GoogleAnalyticsPP',$options);
				echo "<div class=\"updated\"><p>Google Analytics settings reset to default.</p></div>\n";
			}
			if ( isset($_POST['submit']) ) {
				if (!current_user_can('manage_options')) die(__('You cannot edit the Google Analytics for WordPress options.'));
				check_admin_referer('analyticspp-config');
				$options['uastring'] = $_POST['uastring'];
				
				foreach (array('dlextensions', 'dlprefix', 'artprefix', 'comprefix', 'comautprefix', 'blogrollprefix', 'domainorurl','position','domain') as $option_name) {
					if (isset($_POST[$option_name])) {
						$options[$option_name] = strtolower($_POST[$option_name]);
					}
				}
				
				foreach (array('extrase', 'imagese', 'trackoutbound', 'trackloggedin', 'admintracking', 'trackadsense', 'userv2', 'allowanchor', 'rsslinktagging', 'advancedsettings') as $option_name) {
					if (isset($_POST[$option_name])) {
						$options[$option_name] = true;
					} else {
						$options[$option_name] = false;
					}
				}

				if ($options['imagese']) {
					$options['extrase'] = true;
				} 

				update_option('GoogleAnalyticsPP', $options);
				echo "<div id=\"updatemessage\" class=\"updated fade\"><p>Google Analytics settings updated.</p></div>\n";
				echo "<script type=\"text/javascript\">setTimeout(function(){jQuery('#updatemessage').hide('slow');}, 3000);</script>";
				
			}

			$options  = get_option('GoogleAnalyticsPP');
			?>
			<div class="wrap">
				<a href="http://yoast.com/"><div id="yoast-icon" style="background: url(http://cdn.yoast.com/theme/yoast-32x32.png) no-repeat;" class="icon32"><br /></div></a>
				<h2>Google Analytics for WordPress Configuration</h2>
				<div class="postbox-container" style="width:70%;">
					<div class="metabox-holder">	
						<div class="meta-box-sortables">
							<form action="" method="post" id="analytics-conf">
								<?php
									wp_nonce_field('analyticspp-config');
									$rows = array();
									$rows[] = array(
										'id' => 'uastring',
										'label' => 'Analytics Account ID',
										'desc' => '<a href="#" id="explain">What\'s this?</a>',
										'content' => '<input id="uastring" name="uastring" type="text" size="20" maxlength="40" value="'.$options['uastring'].'"/><br/><div id="explanation" style="background: #fff; border: 1px solid #ccc; padding: 5px; display:none;">
											<strong>Explanation</strong><br/>
											Find the Account ID, starting with UA- in your account overview, as marked below:<br/>
											<br/>
											<img src="'.$gapppluginpath.'/account-id.png" alt="Account ID"/><br/>
											<br/>
											Once you have entered your Account ID in the box above your pages will be trackable by Google Analytics.<br/>
											Still can\'t find it? Watch <a href="http://yoast.com/wordpress/google-analytics/#accountid">this video</a>!
										</div>'
									);
									$rows[] = array(
										'id' => 'position',
										'label' => 'Where should the tracking script be placed?',
										'content' => '<select name="position" id="position">
											<option value="footer" '.checked($options['position'],true,false).'>In the footer (default)</option>
											<option value="header" '.checked($options['position'],true,false).'>In the header</option>
										</select>'
									);
									$rows[] = array(
										'id' => 'trackoutbound',
										'label' => 'Track outbound clicks &amp; downloads',
										'desc' => '',
										'content' => $this->checkbox('trackoutbound'),
									);
									$rows[] = array(
										'id' => 'advancedsettings',
										'label' => 'Show advanced settings',
										'desc' => 'Only adviced for advanced users who know their way around Google Analytics',
										'content' => $this->checkbox('advancedsettings'),
									);
									$this->postbox('gasettings','Google Analytics Settings',$this->form_table($rows));
								
									$rows = array();
									$rows[] = array(
										'id' => 'admintracking',
										'label' => 'Track the administrator too',
										'desc' => 'Not recommended, as this would schew your statistics.',
										'content' =>  $this->checkbox('admintracking'),
									);
									$rows[] = array(
										'id' => 'trackloggedin',
										'label' => 'Segment logged in users',
										'content' =>  $this->checkbox('trackloggedin'),
									);
									$rows[] = array(
										'id' => 'dlextensions',
										'label' => 'Extensions of files to track as downloads',
										'content' => $this->textinput('dlextensions'),
									);
									$rows[] = array(
										'id' => 'dlprefix',
										'label' => 'Prefix for tracked downloads',
										'content' => $this->textinput('dlprefix'),
									);
									$rows[] = array(
										'id' => 'artprefix',
										'label' => 'Prefix for outbound clicks from articles',
										'content' => $this->textinput('artprefix'),
									);
									$rows[] = array(
										'id' => 'comprefix',
										'label' => 'Prefix for outbound clicks from links in comments',
										'content' => $this->textinput('comprefix'),
									);
									$rows[] = array(
										'id' => 'comautprefix',
										'label' => 'Prefix for outbound clicks from comment author links',
										'content' => $this->textinput('comautprefix'),
									);
									$rows[] = array(
										'id' => 'blogrollprefix',
										'label' => 'Prefix for outbound clicks from blogroll links',
										'content' => $this->textinput('blogrollprefix'),
									);
									$rows[] = array(
										'id' => 'domainorurl',
										'label' => 'Track full URL of outbound clicks or just the domain',
										'content' => '<select name="domainorurl" id="domainorurl">
											<option value="domain"'.selected($options['domainorurl'],'domain',false).'>Just the domain</option>
											<option value="url"'.selected($options['domainorurl'],'url',false).'>Track the complete URL</option>
										</select>',
									);
									$rows[] = array(
										'id' => 'domain',
										'label' => 'Domain Tracking',
										'desc' => 'This allows you to set the domain that\'s set by <a href="http://code.google.com/apis/analytics/docs/gaJSApiDomainDirectory.html#_gat.GA_Tracker_._setDomainName"><code>setDomainName</code></a> for tracking subdomains, if empty this will not be set.',
										'content' => $this->textinput('domain'),
									);
									$rows[] = array(
										'id' => 'trackadsense',
										'label' => 'Track AdSense',
										'desc' => 'This requires integration of your Analytics and AdSense account, for help, <a href="https://www.google.com/adsense/support/bin/topic.py?topic=15007">look here</a>.',
										'content' => $this->checkbox('trackadsense'),
									);
									$rows[] = array(
										'id' => 'extrase',
										'label' => 'Track extra Search Engines',
										'content' => $this->checkbox('extrase'),
									);
									$rows[] = array(
										'id' => 'userv2',
										'label' => 'I use Urchin',
										'content' => $this->checkbox('userv2'),
									);
									$rows[] = array(
										'id' => 'rsslinktagging',
										'label' => 'Tag the links in your RSS feed with campaign variables.',
										'content' => $this->checkbox('rsslinktagging'),
									);
									$rows[] = array(
										'id' => 'allowanchor',
										'label' => 'Use # instead of ? for Campaign tracking?',
										'desc' => 'This adds a <a href="http://code.google.com/apis/analytics/docs/gaJSApiCampaignTracking.html#_gat.GA_Tracker_._setAllowAnchor">setAllowAnchor</a> call to your tracking script, and makes RSS link tagging use a # as well.',
										'content' => $this->checkbox('allowanchor'),
									);
									$this->postbox('advancedgasettings','Advanced Settings',$this->form_table($rows));
								
								?>
						<div class="submit"><input type="submit" class="button-primary" name="submit" value="Update Google Analytics Settings &raquo;" /></div>
					</form>
					<form action="" method="post">
						<input type="hidden" name="reset" value="true"/>
						<div class="submit"><input type="submit" value="Reset Settings &raquo;" /></div>
					</form>
				</div>
			</div>
		</div>
		<div class="postbox-container" style="width:20%;">
			<div class="metabox-holder">	
				<div class="meta-box-sortables">
					<?php
						$this->plugin_like('blog-icons');
						$this->plugin_support('blog-icons');
						$this->news(); 
					?>
				</div>
				<br/><br/><br/>
			</div>
		</div>
	</div>
			<?php
			if (isset($options['uastring'])) {
				if ($options['uastring'] == "") {
					add_action('admin_footer', array(&$this,'warning'));
				} else {
					if (isset($_POST['submit'])) {
						if ($_POST['uastring'] != $options['uastring'] ) {
							add_action('admin_footer', array(&$this,'success'));
						}
					}
				}
			} else {
				add_action('admin_footer', array(&$this,'warning'));
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
			$options['userv2'] = false;
			$options['extrase'] = false;
			$options['imagese'] = false;
			$options['trackoutbound'] = true;
			$options['admintracking'] = true;
			update_option('GoogleAnalyticsPP',$options);
		}
		
		function warning() {
			echo "<div id='message' class='error'><p><strong>Google Analytics is not active.</strong> You must <a href='plugins.php?page=googleanalytics.php'>enter your UA String</a> for it to work.</p></div>";
		} // end warning()

	} // end class GA_Admin

	$ga_admin = new GA_Admin();
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
			global $gapppluginpath;
			
			$options  = get_option('GoogleAnalyticsPP');
			
			if ($options["uastring"] != "" && (!current_user_can('edit_users') || $options["admintracking"]) && !is_preview() ) { ?>
	<!-- Google Analytics for WordPress | http://yoast.com/wordpress/google-analytics/ -->
	<script type="text/javascript">
		var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
		document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
	</script>
	<script type="text/javascript">
		try {
			var pageTracker = _gat._getTracker("<?php echo $options["uastring"]; ?>");
		} catch(err) {}
	</script>
<?php if ( $options["extrase"] == true ) {
		echo("\t<script src=\"".$gapppluginpath."custom_se.js\" type=\"text/javascript\"></script>\n"); 
} ?>
	<script type="text/javascript">
		try {
<?php if ( $options['userv2'] ) {
	echo("\t\t\tpageTracker._setLocalRemoteServerMode();\n");
} 
if ( $options['allowanchor'] ) {
	echo("\t\t\tpageTracker._setAllowAnchor(true);\n");
} 
if ($options['trackloggedin'] && !isset($_COOKIE['__utmv']) && is_user_logged_in() ) {
	echo("\t\t\tpageTracker._setVar('logged-in');\n");
} else {
	echo("\t\t\t// Cookied already: ".$_COOKIE['__utmv']."\n");
}
if ( isset($options['domain']) && $options['domain'] != "" ) {
	if (substr($options['domain'],0,1) != ".") {
		$options['domain'] = ".".$options['domain'];
	}
	echo("\t\t\tpageTracker._setDomainName(\"".$options['domain']."\");\n");
}
if (strpos($_SERVER['HTTP_REFERER'],"images.google") && strpos($_SERVER['HTTP_REFERER'],"&prev") && $options["imagese"]) { ?>
			regex = new RegExp("images.google.([^\/]+).*&prev=([^&]+)");
			var match = regex.exec(pageTracker.qa);
			pageTracker.qa = "http://images.google." + match[1] + unescape(match[2]); <?php } 
?>			pageTracker._trackPageview();
		} catch(err) {}
	</script>
	<!-- End of Google Analytics code -->
	<?php
			} else if ((current_user_can('edit_users') && !$options["admintracking"])) {
				echo "<!-- Google Analytics tracking code not shown because admin tracking is disabled -->";
			}
		}

		/*
		 * Insert the AdSense parameter code into the page. This'll go into the header per Google's instructions.
		 */
		function spool_adsense() {
			$options  = get_option('GoogleAnalyticsPP');
			if ($options["uastring"] != "" && (!current_user_can('edit_users') || $options["admintracking"]) && !is_preview() ) { ?>
				
	<script type="text/javascript">
		window.google_analytics_uacct = "<?php echo $options["uastring"]; ?>";
	</script>
	<?php
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
			if (isset($matches[0]))
				return array("domain"=>$matches[0],"host"=>$host);    
			else
				return array("domain"=>"","host"=>"");
		}

		function ga_parse_link($leaf, $matches){
			global $origin ;
			
			$options  = get_option('GoogleAnalyticsPP');
			
			// Break out immediately if the link is not an http or https link.
			if (strpos($matches[2],"http") !== 0)
				$target = false;
			else
				$target = GA_Filter::ga_get_domain($matches[3]);
				
			$coolBit = "";
			$extension = substr($matches[3],-3);
			$dlextensions = split(",",$options['dlextensions']);
			if ( $target ) {
				if ( $target["domain"] != $origin["domain"] ){
					if ($options['domainorurl'] == "domain") {
						$coolBit .= "javascript:pageTracker._trackPageview('".$leaf."/".$target["host"]."');";
					} else if ($options['domainorurl'] == "url") {
						$coolBit .= "javascript:pageTracker._trackPageview('".$leaf."/".$matches[2]."//".$matches[3]."');";
					}
				} else if ( in_array($extension, $dlextensions) && $target["domain"] == $origin["domain"] ) {
					$file = str_replace($origin["domain"],"",$matches[3]);
					$file = str_replace('www.',"",$file);
					$coolBit .= "javascript:pageTracker._trackPageview('".$options['dlprefix'].$file."');";
				}				
			} 
			if ($coolBit != "") {
				if (preg_match('/onclick=[\'\"](.*?)[\'\"]/i', $matches[4]) > 0) {
					$matches[4] = preg_replace('/onclick=[\'\"](.*?)[\'\"]/i', 'onclick="' . $coolBit .' $1"', $matches[4]);
				} else {
					$matches[4] = 'onclick="' . $coolBit . '"' . $matches[4];
				}				
			}
			return '<a ' . $matches[1] . 'href="' . $matches[2] . '//' . $matches[3] . '"' . ' ' . $matches[4] . '>' . $matches[5] . '</a>';
		}

		function ga_parse_article_link($matches){
			$options  = get_option('GoogleAnalyticsPP');
			return GA_Filter::ga_parse_link($options['artprefix'],$matches);
		}

		function ga_parse_comment_link($matches){
			$options  = get_option('GoogleAnalyticsPP');
			return GA_Filter::ga_parse_link($options['comprefix'],$matches);
		}

		function the_content($text) {
			if (!is_feed()) {
				static $anchorPattern = '/<a (.*?)href=[\'\"](.*?)\/\/([^\'\"]+?)[\'\"](.*?)>(.*?)<\/a>/i';
				$text = preg_replace_callback($anchorPattern,array('GA_Filter','ga_parse_article_link'),$text);				
			}
			return $text;
		}

		function comment_text($text) {
			if (!is_feed()) {
				static $anchorPattern = '/<a (.*?)href="(.*?)\/\/(.*?)"(.*?)>(.*?)<\/a>/i';
				$text = preg_replace_callback($anchorPattern,array('GA_Filter','ga_parse_comment_link'),$text);
			}
			return $text;
		}

		function comment_author_link($text) {
			$options  = get_option('GoogleAnalyticsPP');
			
			if (current_user_can('edit_users') && !$options["admintracking"]) {
				return $text;
			}
	        static $anchorPattern = '/(.*\s+.*?href\s*=\s*)["\'](.*?)["\'](.*)/';
			preg_match($anchorPattern, $text, $matches);
			if ($matches[2] == "") return $text;

			$target = GA_Filter::ga_get_domain($matches[2]);
			$coolBit = "";
			$origin = GA_Filter::ga_get_domain($_SERVER["HTTP_HOST"]);
			if ( $target["domain"] != $origin["domain"]  ){
				if ($options['domainorurl'] == "domain") {
					$coolBit .= "onclick=\"javascript:pageTracker._trackPageview('".$options['comautprefix']."/".$target["host"]."');\"";
				} else if ($options['domainorurl'] == "url") {
					$coolBit .= "onclick=\"javascript:pageTracker._trackPageview('".$options['comautprefix']."/".$matches[2]."');\"";
				}
			} 
			return $matches[1] . "\"" . $matches[2] . "\" " . $coolBit ." ". $matches[3];    
		}
		
		function bookmarks($bookmarks) {
			$options  = get_option('GoogleAnalyticsPP');
			
			if (!is_admin() && (!current_user_can('edit_users') || $options['admintracking'] ) ) {
				$options  = get_option('GoogleAnalyticsPP');

				foreach ( (array) $bookmarks as $bookmark ) {
					if ($options['domainorurl'] == "domain") {
						$target = GA_Filter::ga_get_domain($bookmark->link_url);
						$bookmark->link_rel = $bookmark->link_rel."\" onclick=\"javascript:pageTracker._trackPageview('".$options['blogrollprefix']."/".$target["host"]."');";
					} else if ($options['domainorurl'] == "url") {
						$bookmark->link_rel = $bookmark->link_rel."\" onclick=\"javascript:pageTracker._trackPageview('".$options['blogrollprefix']."/".$bookmark->link_url."');";
					}
				}
			}
			return $bookmarks;
		}
		
		function rsslinktagger($guid) {
			$options  = get_option('GoogleAnalyticsPP');
			global $wp;
			if ($wp->request == 'feed') {
				if ( $options['allowanchor'] ) {
					$delimiter = '#';
				} else {
					$delimiter = '?';
				}
				if (strpos ( $guid, $delimiter ) > 0)
					$delimiter = '&amp;';
				return $guid . $delimiter . 'utm_source=rss&amp;utm_medium=rss&amp;utm_campaign=rss';
			}
		}
		
	} // class GA_Filter
} // endif

/**
 * If setAllowAnchor is set to true, GA ignores all links tagged "normally", so we redirect all "normally" tagged URL's
 * to one tagged with a hash. Needs some work as it also needs to do that when the first utm_ var is actually not the
 * first GET variable in the URL.
 */
if ( $options['allowanchor'] ) {
	function ga_utm_hastag_redirect() {
		if (isset($_SERVER['REQUEST_URI'])) {
			if (strpos($_SERVER['REQUEST_URI'], "utm_") !== false) {			
				$url = 'http://';
				if ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "") {
					$url = 'https://';
				}
				$url .= $_SERVER['SERVER_NAME'];
				if ( strpos($_SERVER['REQUEST_URI'], "?utm_") !== false ) {
					$url .= str_replace("?utm_","#utm_",$_SERVER['REQUEST_URI']);
				} 
				else if ( strpos($_SERVER['REQUEST_URI'], "&utm_") !== false ) {
					$url .= substr_replace($_SERVER['REQUEST_URI'], "#utm_", strpos($_SERVER['REQUEST_URI'], "&utm_"), 5); 
				}
				wp_redirect($url, 301);
				exit;
			}
		}
	}
	add_action('init','ga_utm_hastag_redirect',1);
}

$gaf = new GA_Filter();
$origin = $gaf->ga_get_domain($_SERVER["HTTP_HOST"]);

$options  = get_option('GoogleAnalyticsPP',"");

if ($options == "") {
	$options['dlextensions'] = 'doc,exe,js,pdf,ppt,tgz,zip,xls';
	$options['dlprefix'] = '/downloads';
	$options['artprefix'] = '/outbound/article';
	$options['comprefix'] = '/outbound/comment';
	$options['comautprefix'] = '/outbound/commentauthor';
	$options['blogrollprefix'] = '/outbound/blogroll';
	$options['domainorurl'] = 'domain';
	$options['position'] = 'footer';
	$options['userv2'] = false;
	$options['extrase'] = false;
	$options['imagese'] = false;
	$options['trackoutbound'] = true;
	update_option('GoogleAnalyticsPP',$options);
} 

if ($options['trackoutbound']) {
	// filters alter the existing content
	add_filter('the_content', array('GA_Filter','the_content'), 99);
	add_filter('the_excerpt', array('GA_Filter','the_content'), 99);
	add_filter('comment_text', array('GA_Filter','comment_text'), 99);
	add_filter('get_bookmarks', array('GA_Filter','bookmarks'), 99);
	add_filter('get_comment_author_link', array('GA_Filter','comment_author_link'), 99);
}

if ($options['trackadsense']) {
	add_action('wp_head', array('GA_Filter','spool_adsense'),10);	
}

if ($options['position'] == 'footer' || $options['position'] == "") {
	add_action('wp_footer', array('GA_Filter','spool_analytics'));	
} else {
	add_action('wp_head', array('GA_Filter','spool_analytics'),20);	
}

if ($options['rsslinktagging']) {
	add_filter ( 'the_permalink_rss', array('GA_Filter','rsslinktagger'), 99 );	
}
?>