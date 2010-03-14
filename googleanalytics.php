<?php
/*
Plugin Name: Google Analytics for WordPress
Plugin URI: http://yoast.com/wordpress/analytics/#utm_source=wordpress&utm_medium=plugin&utm_campaign=google-analytics-for-wordpress
Description: This plugin makes it simple to add Google Analytics with extra search engines and automatic clickout and download tracking to your WordPress blog. 
Author: Joost de Valk
Version: 4.0
Requires at least: 2.8
Author URI: http://yoast.com/
License: GPL

*/
	
// Determine the location
function gapp_plugin_path() {
	return plugins_url('', __FILE__).'/';
}

/*
 * Admin User Interface
 */

if ( ! class_exists( 'GA_Admin' ) ) {

	require_once('yst_plugin_tools.php');
	
	class GA_Admin extends Yoast_Plugin_Admin {

		var $hook 		= 'google-analytics-for-wordpress';
		var $filename	= 'google-analytics-for-wordpress/googleanalytics.php';
		var $longname	= 'Google Analytics Configuration';
		var $shortname	= 'Google Analytics';
		var $ozhicon	= 'images/chart_curve.png';
		var $optionname = 'GoogleAnalyticsPP';
		var $homepage	= 'http://yoast.com/wordpress/google-analytics/';
		

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
			$options = get_option('GoogleAnalyticsPP');

			if ( (isset($_POST['reset']) && $_POST['reset'] == "true") || !is_array($options) ) {
				$this->set_defaults();
				echo "<div class=\"updated\"><p>Google Analytics settings reset to default.</p></div>\n";
			}

			if ( isset($_POST['submit']) ) {
				if (!current_user_can('manage_options')) die(__('You cannot edit the Google Analytics for WordPress options.'));
				check_admin_referer('analyticspp-config');
				
				foreach (array('uastring', 'dlextensions', 'domainorurl','position','domain') as $option_name) {
					if (isset($_POST[$option_name]))
						$options[$option_name] = $_POST[$option_name];
					else
						$options[$option_name] = '';
				}
				
				foreach (array('extrase', 'imagese', 'trackoutbound', 'trackloggedin', 'admintracking', 'trackadsense', 'allowanchor', 'rsslinktagging', 'advancedsettings', 'trackregistration') as $option_name) {
					if (isset($_POST[$option_name]))
						$options[$option_name] = true;
					else
						$options[$option_name] = false;
				}

				update_option('GoogleAnalyticsPP', $options);
				echo "<div id=\"updatemessage\" class=\"updated fade\"><p>Google Analytics settings updated.</p></div>\n";
				echo "<script type=\"text/javascript\">setTimeout(function(){jQuery('#updatemessage').hide('slow');}, 3000);</script>";	
			}

			
			?>
			<div class="wrap">
				<a href="http://yoast.com/"><div id="yoast-icon" style="background: url(http://netdna.yoast.com/wp-content/themes/yoast-v2/images/yoast-32x32.png) no-repeat;" class="icon32"><br /></div></a>
				<h2>Google Analytics for WordPress Configuration</h2>
				<div class="postbox-container" style="width:65%;">
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
											<img src="'.gapp_plugin_path().'/images/account-id.png" alt="Account ID"/><br/>
											<br/>
											Once you have entered your Account ID in the box above your pages will be trackable by Google Analytics.<br/>
											Still can\'t find it? Watch <a href="http://yoast.com/wordpress/google-analytics/#accountid">this video</a>!
										</div>'
									);
									$rows[] = array(
										'id' => 'position',
										'label' => 'Where should the tracking script be placed?',
										'content' => '<select name="position" id="position">
											<option value="footer" '.selected($options['position'],'footer',false).'>In the footer (default)</option>
											<option value="manual" '.selected($options['position'],'manual',false).'>Insert manually</option>
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
								

									$content = "<p><a href='http://www.google.com/analytics/authorized_consultants.html'><img src='".plugins_url('google-analytics-for-wordpress')."/images/GAAC-logo.gif' class='alignright' style='margin-left:10px;' alt='Google Analytics Authorized Consultant'/></a>If you're serious about making money with your site, you're probably serious about your analytics too (and if you're not, you should be!). If you think you're not getting the best out of your Google Analytics, you might want to hire serious help too. OrangeValley is a <a href='http://www.google.com/analytics/authorized_consultants.html'>Google Analytics Authorized Consultant</a> and can help you get the most out of your site and marketing.</p><p><a href='http://yoast.com/hire-me/'>Contact us today to start a conversation about how we can help you!</a></p>";

									$this->postbox('gagaac',__('Google Analytics Support', 'ywawp'), $content);

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
										'desc' => 'This requires integration of your Analytics and AdSense account, for help, <a href="http://google.com/support/analytics/bin/answer.py?answer=92625">look here</a>.',
										'content' => $this->checkbox('trackadsense'),
									);
									$rows[] = array(
										'id' => 'extrase',
										'label' => 'Track extra Search Engines',
										'content' => $this->checkbox('extrase'),
									);
									$rows[] = array(
										'id' => 'imagese',
										'label' => 'Track Google Image Search as a Search Engine',
										'content' => $this->checkbox('imagese'),
									);
									$rows[] = array(
										'id' => 'rsslinktagging',
										'label' => 'Tag links in RSS feed with campaign variables',
										'content' => $this->checkbox('rsslinktagging'),
									);
									$rows[] = array(
										'id' => 'trackregistration',
										'label' => 'Add tracking to the login and registration forms',
										'content' => $this->checkbox('trackregistration'),
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
						<div class="submit"><input type="submit" value="Reset Default Settings &raquo;" /></div>
					</form>
				</div>
			</div>
		</div>
		<div class="postbox-container side" style="width:20%;">
			<div class="metabox-holder">	
				<div class="meta-box-sortables">
					<?php
						$this->plugin_like();
						$this->plugin_support();
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
		} 
		
		function set_defaults() {
			$options = get_option('GoogleAnalyticsPP');
			$options['dlextensions'] = 'doc,exe,.js,pdf,ppt,tgz,zip,xls';
			$options['domainorurl'] = 'domain';
			$options['async'] = false;
			$options['extrase'] = false;
			$options['imagese'] = false;
			$options['admintracking'] = true;
			$options['trackoutbound'] = true;
			$options['advancedsettings'] = false;
			$options['allowanchor'] = false;				
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
			global $wp_query;					
			$options  = get_option('GoogleAnalyticsPP');
			
			$customvarslot = 1;
			if ( $options["uastring"] != "" && (!current_user_can('edit_users') || $options["admintracking"]) && !is_preview() ) { 
				$push = array();
				$push[] = "'_setAccount','".$options["uastring"]."'";

				if ( $options['allowanchor'] )
					$push[] = "'_setAllowAnchor','true'";

				if ( $options['trackloggedin'] && is_user_logged_in() ) {
					$push[] = "'_setCustomVar',".$customvarslot.",'logged-in','1',1";
					$customvarslot++;
				}
				
				if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],'tbs=frim:1') !== false) {
					$push[] = "'_setCustomVar',".$customvarslot.",'social-search','1',2";
					$customvarslot++;
				}
				
				if ( isset($options['domain']) && $options['domain'] != "" ) {
					if (substr($options['domain'],0,1) != ".")
						$options['domain'] = ".".$options['domain'];
					$push[] = "'_setDomainName','".$options['domain']."'";
				}
				
				if ( is_single() ) {
					$push[] = "'_setCustomVar',".$customvarslot.",'author','".str_replace(" ","-",strtolower(html_entity_decode(get_the_author())))."'";
					$customvarslot++;
					$cats = get_the_category();
					$push[] = "'_setCustomVar',".$customvarslot.",'category','".str_replace(" ","-",strtolower(html_entity_decode($cats[0]->name)))."'";
					$customvarslot++;
				} else if ( is_page() ) {
					$push[] = "'_setCustomVar',".$customvarslot.",'author','".str_replace(" ","-",strtolower(get_the_author()))."'";
					$customvarslot++;
					$push[] = "'_setCustomVar',".$customvarslot.",'page','1'";
					$customvarslot++;
				}

				if ( is_404() ) {
					$push[] = "'_trackPageview','/404.html?page=' + document.location.pathname + document.location.search + '&from=' + document.referrer'";
				} else if ($wp_query->is_search && $wp_query->found_posts == 0) {
					$push[] = "'_trackPageview','".get_bloginfo('url')."/?s=no-results:".rawurlencode($wp_query->query_vars['s'])."&cat=no-results'";
				} else {
					$push[] = "'_trackPageview'";
				}

				$pushstr = "";
				foreach ($push as $key) {
					if (!empty($pushstr))
						$pushstr .= ",\n";

					$pushstr .= "\t\t[".$key."]";
				}

				?>
	<!-- Google Analytics for WordPress - async tracking beta | http://yoast.com/wordpress/google-analytics/ -->
	<script type="text/javascript">
	var _gaq = _gaq || [];
<?php
	if ( $options["imagese"] ) { ?>
	regex = new RegExp("images.google.([^\/]+).*&prev=([^&]+)");
	var match = regex.exec(document.referrer);
	if (match != null) {
		_gaq.push(
			['_addOrganic', 'images.google.'+match[1], 'q', true],
			['_setReferrerOverride','http://images.google.'+match[1] +unescape(match[2])]
		);
	}
	_gaq.push(
<?php echo $pushstr; ?>

	);
<?php } ?>
	</script>
<?php
	if ( $options["extrase"] )
		echo '<script src="'.gapp_plugin_path().'custom_se_async.js" type="text/javascript"></script>'."\n"; 
?>
	<script type="text/javascript">
	(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(ga);
	})();
	</script>
	<!-- End of Google Analytics async tracking beta code -->
<?php
			} else if ( $options["uastring"] != "" && current_user_can('edit_users') && !$options["admintracking"] ) {
				echo "<!-- Google Analytics tracking code not shown because admin tracking is disabled -->";
			} else if ( $options["uastring"] == "" && current_user_can('edit_users') ) {
				echo "<!-- Google Analytics tracking code not shown because yo haven't entered your UA string yet. -->";
			}
		}

		/*
		 * Insert the AdSense parameter code into the page. This'll go into the header per Google's instructions.
		 */
		function spool_adsense() {
			$options  = get_option('GoogleAnalyticsPP');
			if ( $options["uastring"] != "" && (!current_user_can('edit_users') || $options["admintracking"]) && !is_preview() ) {
				echo '<script type="text/javascript">'."\n";
				echo "\t".'window.google_analytics_uacct = "'.$options["uastring"].'";'."\n"; 
				echo '</script>'."\n";
			}
		}		

		/* Create an array which contians:
		 * "domain" e.g. boakes.org
		 * "host" e.g. store.boakes.org
		 */
		function ga_get_domain($uri){
			$hostPattern = "/^(http:\/\/)?([^\/]+)/i";
			$domainPatternUS = "/[^\.\/]+\.[^\.\/]+$/";
			$domainPatternUK = "/[^\.\/]+\.[^\.\/]+\.[^\.\/]+$/";

			preg_match($hostPattern, $uri, $matches);
			$host = $matches[2];
			if (preg_match("/.*\..*\..*\..*$/",$host)) {
			        preg_match($domainPatternUK, $host, $matches);
			} else {
			        preg_match($domainPatternUS, $host, $matches);
			}

			return array("domain"=>$matches[0],"host"=>$host);
		}

		function ga_parse_link($category, $matches){
			$origin = GA_Filter::ga_get_domain($_SERVER["HTTP_HOST"]);
			$options  = get_option('GoogleAnalyticsPP');
			
			// Break out immediately if the link is not an http or https link.
			if (strpos($matches[2],"http") !== 0)
				$target = false;
			else
				$target = GA_Filter::ga_get_domain($matches[3]);
				
			$trackBit = "";
			$extension = substr($matches[3],-3);
			$dlextensions = split(",",$options['dlextensions']);
			if ( $target ) {
				if ( $target["domain"] != $origin["domain"] ){
					if ($options['domainorurl'] == "domain") {
						$trackBit .= "javascript:_gaq.push(['_trackEvent','".$category."','".$target["host"]."']);";
					} else if ($options['domainorurl'] == "url") {
						$trackBit .= "javascript:_gaq.push(['_trackEvent','".$category."','".$matches[2]."//".$matches[3]."']);";
					}
				} else if ( in_array($extension, $dlextensions) && $target["domain"] == $origin["domain"] ) {
					$file = str_replace($origin["domain"],"",$matches[3]);
					$file = str_replace('www.',"",$file);
					$trackBit .= "javascript:_gaq.push(['_trackEvent','download','".$file."']);";
				}				
			} 
			if ($trackBit != "") {
				if (preg_match('/onclick=[\'\"](.*?)[\'\"]/i', $matches[4]) > 0) {
					$matches[4] = preg_replace('/onclick=[\'\"](.*?)[\'\"]/i', 'onclick="' . $trackBit .' $1"', $matches[4]);
				} else {
					$matches[4] = 'onclick="' . $trackBit . '"' . $matches[4];
				}				
			}
			return '<a ' . $matches[1] . 'href="' . $matches[2] . '//' . $matches[3] . '"' . ' ' . $matches[4] . '>' . $matches[5] . '</a>';
		}

		function ga_parse_article_link($matches){
			return GA_Filter::ga_parse_link('outbound-article',$matches);
		}

		function ga_parse_comment_link($matches){
			return GA_Filter::ga_parse_link('outbound-comment',$matches);
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
			$trackBit = "";
			$origin = GA_Filter::ga_get_domain($_SERVER["HTTP_HOST"]);
			if ( $target["domain"] != $origin["domain"]  ){
				$trackBit .= "onclick=\"javascript:_gaq.push(['_trackEvent','outbound-commentauthor','";
				if ($options['domainorurl'] == "domain") {
					$trackBit .= $target["host"];
				} else if ($options['domainorurl'] == "url") {
					$trackBit .= $matches[2];
				}
				$trackBit .= "']);\"";
			} 
			return $matches[1] . "\"" . $matches[2] . "\" " . $trackBit ." ". $matches[3];    
		}
		
		function bookmarks($bookmarks) {
			$options  = get_option('GoogleAnalyticsPP');
			
			if (!is_admin() && (!current_user_can('edit_users') || $options['admintracking'] ) ) {
				$i = 0;
				while ( $i < count($bookmarks) ) {
					$target = GA_Filter::ga_get_domain($bookmarks[$i]->link_url);
					$sitedomain = GA_Filter::ga_get_domain(get_bloginfo('url'));
					if ($target['host'] == $sitedomain['host'])
						continue;					
					$trackBit = "\" onclick=\"javascript:_gaq.push(['_trackEvent','outbound-blogroll','";
					if ($options['domainorurl'] == "domain")
						$trackBit .= $target["host"];
					else if ($options['domainorurl'] == "url")
						$trackBit .= $bookmarks[$i]->link_url;
					$trackBit .= "']);";

					$bookmarks[$i]->link_target .= $trackBit;
					$i++;
				}
			}
			return $bookmarks;
		}
		
		function rsslinktagger($guid) {
			$options  = get_option('GoogleAnalyticsPP');
			global $wp, $post;
			if ( is_feed() ) {
				if ( $options['allowanchor'] ) {
					$delimiter = '#';
				} else {
					$delimiter = '?';
					if (strpos ( $guid, $delimiter ) > 0)
						$delimiter = '&amp;';
				}
				return $guid . $delimiter . 'utm_source=rss&amp;utm_medium=rss&amp;utm_campaign='.urlencode($post->post_name);
			}
		}
		
	} // class GA_Filter
} // endif

/**
 * If setAllowAnchor is set to true, GA ignores all links tagged "normally", so we redirect all "normally" tagged URL's
 * to one tagged with a hash. Needs some work as it also needs to do that when the first utm_ var is actually not the
 * first GET variable in the URL.
 */
function ga_utm_hashtag_redirect() {
	if (isset($_SERVER['REQUEST_URI'])) {
		if (strpos($_SERVER['REQUEST_URI'], "utm_") !== false) {			
			$url = 'http://';
			if ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "") {
				$url = 'https://';
			}
			$url .= $_SERVER['SERVER_NAME'];
			if ( strpos($_SERVER['REQUEST_URI'], "?utm_") !== false ) {
				$url .= str_replace("?utm_","#utm_",$_SERVER['REQUEST_URI']);
			} else if ( strpos($_SERVER['REQUEST_URI'], "&utm_") !== false ) {
				$url .= substr_replace($_SERVER['REQUEST_URI'], "#utm_", strpos($_SERVER['REQUEST_URI'], "&utm_"), 5); 
			}
			wp_redirect($url, 301);
			exit;
		}
	}
}

function track_comment_form_head() {
	if (is_single()) {
		global $post;
		if ('open' == $post->comment_status)
			wp_enqueue_script('jquery');	
	}
}
add_action('wp_print_scripts','track_comment_form_head');

$comment_form_id = '';
function yoast_get_comment_form_id($args) {
	global $comment_form_id;
	$comment_form_id = $args['id_form'];
	return $args;
}
add_filter('comment_form_defaults', 'yoast_get_comment_form_id',99,1);

function yoast_track_comment_form() {
	global $comment_form_id, $post;
	$yoast_ga_options = get_option('GoogleAnalyticsPP');
?>
<script type="text/javascript" charset="utf-8">
	jQuery(document).ready(function() {
		jQuery('#<?php echo $comment_form_id; ?>').submit(function() {
			_gaq.push(
				['_setAccount','<?php echo $yoast_ga_options["uastring"]; ?>'],
				['_trackEvent','comment']
			);
		});
	});	
</script>
<?php
}
add_action('comment_form_after','yoast_track_comment_form');

function yoast_analytics() {
	$options	= get_option('GoogleAnalyticsPP');
	if ($options['position'] == 'manual')
		GA_Filter::spool_analytics();
	else
		echo '<!-- Please set Google Analytics position to "manual" in the settings -->';
}

$gaf 		= new GA_Filter();
$options	= get_option('GoogleAnalyticsPP');

if (!is_array($options))
	$ga_admin->set_defaults();

if ( $options['allowanchor'] ) {
	add_action('init','ga_utm_hashtag_redirect',1);
}

if ($options['trackoutbound']) {
	// filters alter the existing content
	add_filter('the_content', array('GA_Filter','the_content'), 99);
	add_filter('the_excerpt', array('GA_Filter','the_content'), 99);
	add_filter('comment_text', array('GA_Filter','comment_text'), 99);
	add_filter('get_bookmarks', array('GA_Filter','bookmarks'), 99);
	add_filter('get_comment_author_link', array('GA_Filter','comment_author_link'), 99);
}

if ($options['trackadsense'])
	add_action('wp_head', array('GA_Filter','spool_adsense'),10);	

if ($options['position'] == 'footer' || $options['position'] == "")
	add_action('wp_footer', array('GA_Filter','spool_analytics'));	
	
if ($options['trackregistration'])
	add_action('login_head', array('GA_Filter','spool_analytics'),20);	
	
if ($options['rsslinktagging'])
	add_filter ( 'the_permalink_rss', array('GA_Filter','rsslinktagger'), 99 );	

?>