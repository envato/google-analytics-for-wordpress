<?php
/*
Plugin Name: Google Analytics for WordPress
Plugin URI: http://yoast.com/wordpress/analytics/#utm_source=wordpress&utm_medium=plugin&utm_campaign=google-analytics-for-wordpress&utm_content=v406
Description: This plugin makes it simple to add Google Analytics to your WordPress blog, adding lots of features, eg. custom variables and automatic clickout and download tracking. 
Author: Joost de Valk
Version: 4.0.6
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

	require_once plugin_dir_path(__FILE__).'yst_plugin_tools.php';
	
	class GA_Admin extends Yoast_GA_Plugin_Admin {

		var $hook 		= 'google-analytics-for-wordpress';
		var $filename	= 'google-analytics-for-wordpress/googleanalytics.php';
		var $longname	= 'Google Analytics Configuration';
		var $shortname	= 'Google Analytics';
		var $ozhicon	= 'images/chart_curve.png';
		var $optionname = 'Yoast_Google_Analytics';
		var $homepage	= 'http://yoast.com/wordpress/google-analytics/';
		var $toc		= '';

		function GA_Admin() {
			$this->upgrade();
			
			add_action( 'admin_menu', array(&$this, 'register_settings_page') );
			add_filter( 'plugin_action_links', array(&$this, 'add_action_link'), 10, 2 );
			add_filter( 'ozh_adminmenu_icon', array(&$this, 'add_ozh_adminmenu_icon' ) );				
			
			add_action('admin_print_scripts', array(&$this,'config_page_scripts'));
			add_action('admin_print_styles', array(&$this,'config_page_styles'));	
			
			add_action('wp_dashboard_setup', array(&$this,'widget_setup'));	

			add_action('admin_head', array(&$this,'config_page_head'));

			add_action('admin_footer', array(&$this,'warning'));
			add_action('admin_footer', array(&$this,'theme_switch_warning'));

			add_action('admin_init', array(&$this,'save_settings'));

			add_action('switch_theme', array(&$this,'switch_theme'));
		}
		
		function config_page_head() {
			if (isset($_GET['page']) && $_GET['page'] == $this->hook) {
				$options = get_option($this->optionname);
				if (!empty($options['uastring'])) { 
					$uastring = $options['uastring'];
				} else { 
					$uastring = ''; 
				}
				wp_enqueue_script('jquery');
				
			?>
				 <script type="text/javascript" charset="utf-8">				
					function makeSublist(parent,child,childVal)
					{
						jQuery("body").append("<select style='display:none' id='"+parent+child+"'></select>");
						jQuery('#'+parent+child).html(jQuery("#"+child+" option"));

						var parentValue = jQuery('#'+parent).attr('value');
						jQuery('#'+child).html(jQuery("#"+parent+child+" .sub_"+parentValue).clone());

						childVal = (typeof childVal == "undefined")? "" : childVal ;
						jQuery("#"+child).val(childVal).attr('selected','selected');

						jQuery('#'+parent).change(function(){
							var parentValue = jQuery('#'+parent).attr('value');
							jQuery('#'+child).html(jQuery("#"+parent+child+" .sub_"+parentValue).clone());
							jQuery('#'+child).trigger("change");
							jQuery('#'+child).focus();
						});
					}
				 	jQuery(document).ready(function(){
						makeSublist('ga_account', 'uastring_sel', '<?php echo $uastring; ?>');
						jQuery('#position').change(function(){
							if (jQuery('#position').val() == 'header')  {
								jQuery('#position_header').css("display","block");
								jQuery('#position_manual').css("display","none");
							} else {
								jQuery('#position_header').css("display","none");
								jQuery('#position_manual').css("display","block");								
							}
						}).change();
						jQuery('#switchtomanual').change(function() {
							if ((jQuery('#switchtomanual').attr('checked')) == true)  {
								jQuery('#uastring_manual').css('display','block');
								jQuery('#uastring_automatic').css('display','none');
							} else {
								jQuery('#uastring_manual').css('display','none');
								jQuery('#uastring_automatic').css('display','block');								
							}
						}).change();
						jQuery('#trackoutbound').change(function(){
							if ((jQuery('#trackoutbound').attr('checked')) == true)  {
								jQuery('#internallinktracking').css("display","block");
							} else {
								jQuery('#internallinktracking').css("display","none");
							}
						}).change();
						jQuery('#advancedsettings').change(function(){
							if ((jQuery('#advancedsettings').attr('checked')) == true)  {
								jQuery('#advancedgasettings').css("display","block");
								jQuery('#customvarsettings').css("display","block");
								jQuery('#toc').css("display","block");
							} else {
								jQuery('#advancedgasettings').css("display","none");
								jQuery('#customvarsettings').css("display","none");
								jQuery('#toc').css("display","none");
							}
						}).change();
						jQuery('#extrase').change(function(){
							if ((jQuery('#extrase').attr('checked')) == true)  {
								jQuery('#extrasebox').css("display","block");
							} else {
								jQuery('#extrasebox').css("display","none");
							}
						}).change();
						jQuery('#gajslocalhosting').change(function(){
							if ((jQuery('#gajslocalhosting').attr('checked')) == true)  {
								jQuery('#localhostingbox').css("display","block");
							} else {
								jQuery('#localhostingbox').css("display","none");
							}
						}).change();
						jQuery('#customvarsettings :input').change(function() {
							if (jQuery("#customvarsettings :input:checked").size() > 5) {
								alert('The maximum number of allowed custom variables in Google Analytics is 5, please unselect one of the other custom variables before selecting this one.')
								jQuery(this).attr('checked', false);
							};
						});
						jQuery('#uastring').change(function(){
							if ((jQuery('#switchtomanual').attr('checked')) == true)  {
								if (!jQuery(this).val().match(/^UA-[\d-]+$/)) {
									alert("That's not a valid UA ID, please make sure it matches the expected pattern of: UA-XXXXXX-X, and that there are no spaces or other characters in the input field.");
									jQuery(this).focus();
								}
							}
						});
					});
				 </script>
			<?php
			}
		}
				
		function theme_switch_warning() {
			$options = get_option( $this->optionname );
			if ($options['theme_updated']) {
				echo "<div id='message' class='error'><p>You have updated your theme, please check your <a href='".$this->plugin_options_url()."'><strong>Google Analytics settings</strong></a> to make sure Google Analytics can still function correctly.</p></div>";
			}
		} 

		function switch_theme( $theme ) {
			$options 					= get_option( $this->optionname );
			$options['theme_updated'] 	= 1;
			$options['position']		= 'header';
			update_option( $this->optionname, $options );
		}
		
		function toc( $modules ) {
			$output = '<ul>';
			foreach ($modules as $module => $key) {
				$output .= '<li><a href="#'.$key.'">'.$module.'</a></li>';
			}
			$output .= '</ul>';
			return $output;
		}
		
		function save_settings() {
			$options = get_option( $this->optionname );
			
			if ( isset($_REQUEST['reset']) && $_REQUEST['reset'] == "true" && isset($_REQUEST['plugin']) && $_REQUEST['plugin'] == 'google-analytics-for-wordpress') {
				$options = $this->set_defaults();
				$options['msg'] = "<div class=\"updated\"><p>Google Analytics settings reset.</p></div>\n";
			} elseif ( isset($_POST['submit']) && isset($_POST['plugin']) && $_POST['plugin'] == 'google-analytics-for-wordpress') {
				if (!current_user_can('manage_options')) die(__('You cannot edit the Google Analytics for WordPress options.'));
				check_admin_referer('analyticspp-config');
				
				foreach (array('uastring', 'dlextensions', 'domainorurl','position','domain', 'ga_token', 'extraseurl', 'gajsurl', 'gfsubmiteventpv', 'trackprefix', 'ignore_userlevel', 'internallink', 'internallinklabel') as $option_name) {
					if (isset($_POST[$option_name]))
						$options[$option_name] = $_POST[$option_name];
					else
						$options[$option_name] = '';
				}
				
				foreach (array('extrase', 'trackoutbound', 'admintracking', 'trackadsense', 'allowanchor', 'allowlinker', 'rsslinktagging', 'advancedsettings', 'trackregistration', 'theme_updated', 'cv_loggedin', 'cv_authorname', 'cv_category', 'cv_all_categories', 'cv_tags', 'cv_year', 'cv_post_type', 'outboundpageview', 'downloadspageview', 'gajslocalhosting', 'manual_uastring', 'taggfsubmit', 'wpec_tracking', 'shopp_tracking', 'anonymizeip', 'trackcommentform') as $option_name) {
					if (isset($_POST[$option_name]) && $_POST[$option_name] != 'off')
						$options[$option_name] = true;
					else
						$options[$option_name] = false;
				}

				if (isset($_POST['manual_uastring']) && isset($_POST['uastring_man'])) {
					$options['uastring'] = $_POST['uastring_man'];
				}
				
				$cache = '';
				if ( function_exists('w3tc_pgcache_flush') ) {
					w3tc_pgcache_flush();
					$cache = ' and <strong>W3TC Page Cache cleared</strong>';
				} else if ( function_exists('wp_cache_clear_cache') ) {
					wp_cache_clear_cache();
					$cache = ' and <strong>WP Super Cache cleared</strong>';
				}
										
				$options['msg'] = "<div id=\"updatemessage\" class=\"updated fade\"><p>Google Analytics <strong>settings updated</strong>$cache.</p></div>\n";
				$options['msg'] .= "<script type=\"text/javascript\">setTimeout(function(){jQuery('#updatemessage').hide('slow');}, 3000);</script>";
			}
			update_option($this->optionname, $options);
		}
		
		function save_button() {
			return '<div class="alignright"><input type="submit" class="button-primary" name="submit" value="Update Google Analytics Settings &raquo;" /></div><br class="clear"/>';
		}
		
		function upgrade() {
			$options = get_option($this->optionname);
			if ($options['version'] < '4.04') {
				if ( !isset($options['trackcommentform']) || $options['trackcommentform'] == '')
					$options['trackcommentform'] = true;
				if ( !isset($options['ignore_userlevel']) || $options['ignore_userlevel'] == '')
					$options['ignore_userlevel'] = 11;
					
				$options['version'] = '4.04';
			}
			if ($options['version'] < '4.06') {
				$options['version'] = '4.06';
			}
			update_option($this->optionname, $options);
		}

		function config_page() {
			$options = get_option($this->optionname);
			echo $options['msg'];
			$options['msg'] = '';
			update_option($this->optionname, $options);
			
			?>
			<div class="wrap">
				<a href="http://yoast.com/"><div id="yoast-icon" style="background: url(http://cdn.yoast.com/wp-content/themes/yoast-v2/images/yoast-32x32.png) no-repeat;" class="icon32"><br /></div></a>
				<h2>Google Analytics for WordPress Configuration</h2>
				<div class="postbox-container" style="width:65%;">
					<div class="metabox-holder">	
						<div class="meta-box-sortables">
							<form action="<?php echo $this->plugin_options_url(); ?>" method="post" id="analytics-conf">
								<input type="hidden" name="plugin" value="google-analytics-for-wordpress"/>
								<?php
									wp_nonce_field('analyticspp-config');
									if ( empty($options['uastring']) && empty($options['ga_token']) && !isset($_GET['token']) ) {
										$url = $this->plugin_options_url();
										if (isset($_GET['switchua']))
											$url .= '&switchua=1';
										$query = 'https://www.google.com/accounts/AuthSubRequest?';
										$query .= http_build_query(
											array(		
												'next' => $url,
												'scope' => 'https://www.google.com/analytics/feeds/',
												'secure' => 0,
												'session' => 1,
												'hd' => 'default'
											)
										);
										$line = 'Please authenticate with Google Analytics to retrieve your tracking code:<br/><br/> <a class="button-primary" href="'.$query.'">Click here to authenticate with Google</a><br/><br/><strong>Note</strong>: if you have multiple Google accounts, you\'ll want to switch to the right account first, since Google doesn\'t let you switch accounts on the authentication screen.';
									} else if(isset($_GET['token']) || (isset($options['ga_token']) && !empty($options['ga_token']))) {
										if (isset($_GET['token']))
											$token = $_GET['token'];
										else
											$token = $options['ga_token'];
										
										require_once plugin_dir_path(__FILE__).'xmlparser.php';
										if (file_exists(ABSPATH.'wp-includes/class-http.php'))
											require_once(ABSPATH.'wp-includes/class-http.php');

										if (!isset($options['ga_api_responses'][$token])) {
											$options['ga_api_responses'] = array();
											$request = new WP_Http;
											$api_url = 'https://www.google.com/analytics/feeds/accounts/default';
											$headers = array( 
												'Content-Type' 	=> 'application/x-www-form-urlencoded',
												'Authorization' => 'AuthSub token="'.$token.'"',
											);
											$args = array(
												'method' 		=> 'GET', 
												'body' 			=> '', 
												'headers' 		=> $headers,
												'timeout'		=> 20,
											);
											$result = $request->request( $api_url , $args );
											if (is_array($result) && $result['response']['code'] == 200) {
												$options['ga_api_responses'][$token] = $result;
												$options['ga_token'] = $token;
												update_option('Yoast_Google_Analytics', $options);												
											}
										}

										if (is_array($options['ga_api_responses'][$token]) && $options['ga_api_responses'][$token]['response']['code'] == 200) {
											$arr = yoast_xml2array($options['ga_api_responses'][$token]['body']);
										
											$ga_accounts = array();
											if (isset($arr['feed']['entry'][0])) {
												foreach ($arr['feed']['entry'] as $site) {
													$ua = $site['dxp:property']['3_attr']['value'];
													$account = $site['dxp:property']['1_attr']['value'];
													if (!isset($ga_accounts[$account]) || !is_array($ga_accounts[$account]))
														$ga_accounts[$account] = array();
													$ga_accounts[$account][$site['title']] = $ua;
												}
											} else {
												$ua = $arr['feed']['entry']['dxp:property']['3_attr']['value'];
												$account = $arr['feed']['entry']['dxp:property']['1_attr']['value'];
												$title = $arr['feed']['entry']['title'];
												if (!isset($ga_accounts[$account]) || !is_array($ga_accounts[$account]))
													$ga_accounts[$account] = array();
												$ga_accounts[$account][$title] = $ua;
											}

											$select1 = '<select style="width:150px;" name="ga_account" id="ga_account">';
											$select1 .= "\t<option></option>\n";
											$select2 = '<select style="width:150px;" name="uastring" id="uastring_sel">';
											$i = 1;
											$currentua = '';
											if (!empty($options['uastring']))
												$currentua = $options['uastring'];
										
											foreach($ga_accounts as $account => $val) {
												$accountsel = false;
												foreach ($val as $title => $ua) {
													$sel = selected($ua, $currentua, false);
													if (!empty($sel)) {
														$accountsel = true;
													}
													$select2 .= "\t".'<option class="sub_'.$i.'" '.$sel.' value="'.$ua.'">'.$title.' - '.$ua.'</option>'."\n";
												}
												$select1 .= "\t".'<option '.selected($accountsel,true,false).' value="'.$i.'">'.$account.'</option>'."\n";
												$i++;
											}
											$select1 .= '</select>';
											$select2 .= '</select>';
																														
											$line = '<input type="hidden" name="ga_token" value="'.$token.'"/>';
											$line .= 'Please select the correct Analytics profile to track:<br/>';
											$line .= '<table class="form_table">';
											$line .= '<tr><th width="15%">Account:</th><td width="85%">'.$select1.'</td></tr>';
											$line .= '<tr><th>Profile:</th><td>'.$select2.'</td></tr>';
											$line .= '</table>';

											$try = 1;
											if (isset($_GET['try']))
												$try = $_GET['try'] + 1;

											if ($i == 1 && $try < 4 && isset($_GET['token'])) {
												$line .= '<script type="text/javascript" charset="utf-8">
													window.location="'.$this->plugin_options_url().'&switchua=1&token='.$token.'&try='.$try.'";
												</script>';
											}
											$line .= 'Please note that if you have several profiles of the same website, it doesn\'t matter which profile you select, and in fact another profile might show as selected later. You can check whether they\'re profiles for the same site by checking if they have the same UA code. If that\'s true, tracking will be correct.<br/>';
											$line .= '<br/>Refresh this listing or switch to another account: ';
										} else {
											$line = 'Unfortunately, an error occurred while connecting to Google, please try again:';
										}
										
										$url = $this->plugin_options_url();
										if (isset($_GET['switchua']))
											$url .= '&switchua=1';
										$query = 'https://www.google.com/accounts/AuthSubRequest?';
										$query .= http_build_query(
											array(		
												'next' => $url,
												'scope' => 'https://www.google.com/analytics/feeds/',
												'secure' => 0,
												'session' => 1,
												'hd' => 'default'
											)
										);
										$line .= '<a class="button" href="'.$query.'">Re-authenticate with Google</a>';
									} else {
										$line = '<input id="uastring" name="uastring" type="text" size="20" maxlength="40" value="'.$options['uastring'].'"/><br/><a href="'.$this->plugin_options_url().'&amp;switchua=1">Select another Analytics Profile &raquo;</a>';
									}
									$line = '<div id="uastring_automatic">'.$line.'</div><div style="display:none;" id="uastring_manual">Manually enter your UA code: <input id="uastring" name="uastring_man" type="text" size="20" maxlength="40" value="'.$options['uastring'].'"/></div>';
									$rows = array();
									$content = '';
									$rows[] = array(
										'id' => 'uastring',
										'label' => 'Analytics Profile',
										'desc' => '<input type="checkbox" name="manual_uastring" '.checked($options['manual_uastring'], true, false).' id="switchtomanual"/> <label for="switchtomanual">Manually enter your UA code</label>',
										'content' => $line
									);
									$temp_content = $this->select('position', array('header' => 'In the header (default)', 'manual' => 'Insert manually'));
									if ($options['theme_updated'] && $options['position'] == 'manual') {
											$temp_content .= '<input type="hidden" name="theme_updated" value="off"/>';
											echo '<div id="message" class="updated" style="background-color:lightgreen;border-color:green;"><p><strong>Notice:</strong> You switched your theme, please make sure your Google Analytics tracking is still ok. Save your settings to make sure Google Analytics gets loaded properly.</p></div>';
											remove_action('admin_footer', array(&$this,'theme_switch_warning'));
									}
									$desc = '<div id="position_header">The header is by far the best spot to place the tracking code. If you\'d rather place the code manually, switch to manual placement. For more info <a href="http://yoast.com/wordpress/google-analytics/manual-placement/">read this page</a>.</div>';
									$desc .= '<div id="position_manual"><a href="http://yoast.com/wordpress/google-analytics/manual-placement/">Follow the instructions here</a> to choose the location for your tracking code manually.</div>';

									$rows[] = array(
										'id' => 'position',
										'label' => 'Where should the tracking code be placed',
										'desc' => $desc,
										'content' => $temp_content,
									);
									$rows[] = array(
										'id' => 'trackoutbound',
										'label' => 'Track outbound clicks &amp; downloads',
										'desc' => 'Clicks &amp; downloads will be tracked as events, you can find these under Content &raquo; Event Tracking in your Google Analytics reports.',
										'content' => $this->checkbox('trackoutbound'),
									);
									$rows[] = array(
										'id' => 'advancedsettings',
										'label' => 'Show advanced settings',
										'desc' => 'Only adviced for advanced users who know their way around Google Analytics',
										'content' => $this->checkbox('advancedsettings'),
									);
									$this->postbox('gasettings','Google Analytics Settings',$this->form_table($rows).$this->save_button());
								
									$rows = array();
									$pre_content = '<p>Google Analytics allows you to save up to 5 custom variables on each page, and this plugin helps you make the most use of these! Check which custom variables you\'d like the plugin to save for you below. Please note that these will only be saved when they are actually available.</p><p>If you want to start using these custom variables, go to Visitors &raquo; Custom Variables in your Analytics reports.</p>';
									$rows[] = array(
										'id' => 'cv_loggedin',
										'label' => 'Logged in Users',
										'desc' => 'Allows you to easily remove logged in users from your reports, or to segment by different user roles. The users primary role will be logged.',
										'content' =>  $this->checkbox('cv_loggedin'),
									);
									$rows[] = array(
										'id' => 'cv_post_type',
										'label' => 'Post type',
										'desc' => 'Allows you to see pageviews per post type, especially useful if you use multiple custom post types.',
										'content' =>  $this->checkbox('cv_post_type'),
									);
									$rows[] = array(
										'id' => 'cv_authorname',
										'label' => 'Author Name',
										'desc' => 'Allows you to see pageviews per author.',
										'content' =>  $this->checkbox('cv_authorname'),
									);
									$rows[] = array(
										'id' => 'cv_tags',
										'label' => 'Tags',
										'desc' => 'Allows you to see pageviews per tags using advanced segments.',
										'content' =>  $this->checkbox('cv_tags'),
									);
									$rows[] = array(
										'id' => 'cv_year',
										'label' => 'Publication year',
										'desc' => 'Allows you to see pageviews per year of publication, showing you if your old posts still get traffic.',
										'content' =>  $this->checkbox('cv_year'),
									);
									$rows[] = array(
										'id' => 'cv_category',
										'label' => 'Single Category',
										'desc' => 'Allows you to see pageviews per category, works best when each post is in only one category.',
										'content' =>  $this->checkbox('cv_category'),
									);
									$rows[] = array(
										'id' => 'cv_all_categories',
										'label' => 'All Categories',
										'desc' => 'Allows you to see pageviews per category using advanced segments, should be used when you use multiple categories per post.',
										'content' =>  $this->checkbox('cv_all_categories'),
									);

									$this->postbox('customvarsettings','Custom Variables Settings',$pre_content.$this->form_table($rows).$this->save_button());
									
									$rows = array();
									$rows[] = array(
										'id' => 'ignore_userlevel',
										'label' => 'Ignore users',
										'desc' => 'Users of the role you select and higher will be ignored, so if you select Editor, all Editors and Administrators will be ignored.',
										'content' => $this->select('ignore_userlevel', array(
											'11' => 'Ignore no-one',
											'8' => 'Administrator',
											'5' => 'Editor',
											'2' => 'Author', 
											'1' => 'Contributor', 
											'0' => 'Subscriber (ignores all logged in users)', 
										)),
									);
									$rows[] = array(
										'id' => 'outboundpageview',
										'label' => 'Track outbound clicks as pageviews',
										'desc' => 'You do not need to enable this to enable outbound click tracking, this changes the default behavior of tracking clicks as events to tracking them as pageviews. This is therefore not recommended, as this would schew your statistics, but <em>is</em> sometimes necessary when you need to set outbound clicks as goals.',
										'content' =>  $this->checkbox('outboundpageview'),
									);
									$rows[] = array(
										'id' => 'downloadspageview',
										'label' => 'Track downloads as pageviews',
										'desc' => 'Not recommended, as this would schew your statistics, but it does make it possible to track downloads as goals.',
										'content' =>  $this->checkbox('downloadspageview'),
									);
									$rows[] = array(
										'id' => 'dlextensions',
										'label' => 'Extensions of files to track as downloads',
										'content' => $this->textinput('dlextensions'),
									);
									$rows[] = array(
										'id' => 'trackprefix',
										'label' => 'Prefix to use in Analytics before the tracked pageviews',
										'desc' => 'This prefix is used before all pageviews, they are then segmented automatically after that. If nothing is entered here, <code>/yoast-ga/</code> is used.',
										'content' => $this->textinput('trackprefix'),
									);
									$rows[] = array(
										'id' => 'domainorurl',
										'label' => 'Track full URL of outbound clicks or just the domain',
										'content' => $this->select('domainorurl', array(
														'domain' => 'Just the domain',
														'url' => 'Track the complete URL',
													)
												),
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
										'id' => 'gajslocalhosting',
										'label' => 'Host ga.js locally',
										'content' => $this->checkbox('gajslocalhosting').'<div id="localhostingbox">
											You have to provide a URL to your ga.js file:
											<input type="text" name="gajsurl" size="30" value="'.$options['gajsurl'].'"/>
										</div>',
										'desc' => 'For some reasons you might want to use a locally hosted ga.js file, or another ga.js file, check the box and then please enter the full URL including http here.'
									);
									$rows[] = array(
										'id' => 'extrase',
										'label' => 'Track extra Search Engines',
										'content' => $this->checkbox('extrase').'<div id="extrasebox">
											You can provide a custom URL to the extra search engines file if you want:
											<input type="text" name="extraseurl" size="30" value="'.$options['extraseurl'].'"/>
										</div>',
									);
									$rows[] = array(
										'id' => 'rsslinktagging',
										'label' => 'Tag links in RSS feed with campaign variables',
										'desc' => 'Do not use this feature if you use FeedBurner, as FeedBurner can do this automatically, and better than this plugin can. Check <a href="http://www.google.com/support/feedburner/bin/answer.py?hl=en&amp;answer=165769">this help page</a> for info on how to enable this feature in FeedBurner.',
										'content' => $this->checkbox('rsslinktagging'),
									);
									$rows[] = array(
										'id' => 'trackregistration',
										'label' => 'Add tracking to the login and registration forms',
										'content' => $this->checkbox('trackregistration'),
									);
									$rows[] = array(
										'id' => 'trackcommentform',
										'label' => 'Add tracking to the comment forms',
										'content' => $this->checkbox('trackcommentform'),
									);
									$rows[] = array(
										'id' => 'allowanchor',
										'label' => 'Use # instead of ? for Campaign tracking',
										'desc' => 'This adds a <code><a href="http://code.google.com/apis/analytics/docs/gaJSApiCampaignTracking.html#_gat.GA_Tracker_._setAllowAnchor">_setAllowAnchor</a></code> call to your tracking code, and makes RSS link tagging use a # as well.',
										'content' => $this->checkbox('allowanchor'),
									);
									$rows[] = array(
										'id' => 'allowlinker',
										'label' => 'Add <code>_setAllowLinker</code>',
										'desc' => 'This adds a <code><a href="http://code.google.com/apis/analytics/docs/gaJS/gaJSApiDomainDirectory.html#_gat.GA_Tracker_._setAllowLinker">_setAllowLinker</a></code> call to your tracking code,  allowing you to use <code>_link</code> and related functions.',
										'content' => $this->checkbox('allowlinker'),
									);
									$rows[] = array(
										'id' => 'anonymizeip',
										'label' => 'Anonymize IP\'s',
										'desc' => 'This adds <code><a href="http://code.google.com/apis/analytics/docs/gaJS/gaJSApi_gat.html#_gat._anonymizeIp">_anonymizeIp</a></code>, telling Google Analytics to anonymize the information sent by the tracker objects by removing the last octet of the IP address prior to its storage.',
										'content' => $this->checkbox('anonymizeip'),
									);
									
									$this->postbox('advancedgasettings','Advanced Settings',$this->form_table($rows).$this->save_button());

									$rows = array();
									$rows[] = array(
										'id' => 'internallink',
										'label' => 'Internal links to track as outbound',
										'desc' => 'If you want to track all internal links that begin with <code>/out/</code>, enter <code>/out/</code> in the box above. If you have multiple prefixes you can separate them with comma\'s: <code>/out/,/recommends/</code>',
										'content' => $this->textinput('internallink'),
									);
									$rows[] = array(
										'id' => 'internallinklabel',
										'label' => 'Label to use',
										'desc' => 'The label to use for these links, this will be added to where the click came from, so if the label is "aff", the label for a click from the content of an article becomes "outbound-article-aff".',
										'content' => $this->textinput('internallinklabel'),
									);

									$this->postbox('internallinktracking','Internal Links to Track as Outbound',$this->form_table($rows).$this->save_button());
									
									$modules = array();
									
/*									if (class_exists('RGForms') && GFCommon::$version >= '1.3.11') {
										$pre_content = 'This plugin can automatically tag your Gravity Forms to track form submissions as either events or pageviews';
										$rows = array();
										$rows[] = array(
											'id' => 'taggfsubmit',
											'label' => 'Tag Gravity Forms',
											'content' => $this->checkbox('taggfsubmit'),
										);
										$rows[] = array(
											'id' => 'gfsubmiteventpv',
											'label' => 'Tag Gravity Forms as',
											'content' => '<select name="gfsubmiteventpv">
											<option value="events" '.selected($options['gfsubmiteventpv'],'events',false).'>Events</option>
											<option value="pageviews" '.selected($options['gfsubmiteventpv'],'pageviews',false).'>Pageviews</option>
											</select>',
										);
										$this->postbox('gravityforms','Gravity Forms Settings',$pre_content.$this->form_table($rows).$this->save_button());
										$modules['Gravity Forms'] = 'gravityforms';
									}
									*/
									if ( defined('WPSC_VERSION') ) {
										$pre_content = 'The WordPress e-Commerce plugin has been detected. This plugin can automatically add transaction tracking for you. To do that, <a href="http://yoast.com/wordpress/google-analytics/enable-ecommerce/">enable e-commerce for your reports in Google Analytics</a> and then check the box below.';
										$rows = array();
										$rows[] = array(
											'id' => 'wpec_tracking',
											'label' => 'Enable transaction tracking',
											'content' => $this->checkbox('wpec_tracking'),
										);
										$this->postbox('wpecommerce','WordPress e-Commerce Settings',$pre_content.$this->form_table($rows).$this->save_button());
										$modules['WordPress e-Commerce'] = 'wpecommerce';
									}

									global $Shopp;
									if ( isset($Shopp) ) {
										$pre_content = 'The Shopp e-Commerce plugin has been detected. This plugin can automatically add transaction tracking for you. To do that, <a href="http://www.google.com/support/googleanalytics/bin/answer.py?hl=en&amp;answer=55528">enable e-commerce for your reports in Google Analytics</a> and then check the box below.';
										$rows = array();
										$rows[] = array(
											'id' => 'shopp_tracking',
											'label' => 'Enable transaction tracking',
											'content' => $this->checkbox('shopp_tracking'),
										);
										$this->postbox('shoppecommerce','Shopp e-Commerce Settings',$pre_content.$this->form_table($rows).$this->save_button());
										$modules['Shopp'] = 'shoppecommerce';
									}
								?>
					</form>
					<form action="<?php echo $this->plugin_options_url(); ?>" method="post" onsubmit="javascript:return(confirm('Do you really want to reset all settings?'));">
						<input type="hidden" name="reset" value="true"/>
						<input type="hidden" name="plugin" value="google-analytics-for-wordpress"/>
						<div class="submit"><input type="submit" value="Reset All Settings &raquo;" /></div>
					</form>
				</div>
			</div>
		</div>
		<div class="postbox-container side" style="width:20%;">
			<div class="metabox-holder">	
				<div class="meta-box-sortables">
					<?php
						if ( count($modules) > 0 )
							$this->postbox('toc','List of Available Modules',$this->toc($modules));
						$this->plugin_like();
						$this->postbox('donate','<strong class="red">Donate $5, $10, $20 or $50!</strong>','<p>This plugin has cost me countless hours of work, if you use it, please donate a token of your appreciation!</p><br/><form style="margin-left:50px;" action="https://www.paypal.com/cgi-bin/webscr" method="post">
						<input type="hidden" name="cmd" value="_s-xclick">
						<input type="hidden" name="hosted_button_id" value="FW9FK4EBZ9FVJ">
						<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
						<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
						</form>');
						$this->plugin_support();
						$this->news(); 
					?>
				</div>
				<br/><br/><br/>
			</div>
		</div>
	</div>
			<?php
		} 
		
		function set_defaults() {
			$options = array(
				'advancedsettings' 		=> false,
				'allowanchor' 			=> false,
				'allowlinker' 			=> false,
				'cv_loggedin'			=> false,
				'cv_authorname'			=> false,
				'cv_category'			=> false,
				'cv_all_categories'		=> false,
				'cv_tags'				=> false,
				'cv_year'				=> false,
				'cv_post_type'			=> false,
				'dlextensions' 			=> 'doc,exe,js,pdf,ppt,tgz,zip,xls',
				'domainorurl' 			=> 'domain',
				'ga_token' 				=> '',
				'ga_api_responses'		=> array(),
				'extrase' 				=> false,
				'extraseurl'			=> '',
				'ignore_userlevel'		=> '11',
				'outboundpageview'		=> false,
				'downloadspageview'		=> false,
				'position' 				=> 'footer',
				'trackcommentform'		=> true,
				'trackadsense'			=> false,
				'trackoutbound' 		=> true,
				'trackregistration' 	=> false,
				'rsslinktagging'		=> true,
				'domain' 				=> '',
			);
			update_option($this->optionname,$options);
			return $options;
		}
		
		function warning() {
			$options = get_option($this->optionname);
			if (!isset($options['uastring']) || empty($options['uastring'])) {
				echo "<div id='message' class='error'><p><strong>Google Analytics is not active.</strong> You must <a href='".$this->plugin_options_url()."'>select which Analytics Profile to track</a> before it can work.</p></div>";
			}
		} // end warning()

	} // end class GA_Admin

	$ga_admin = new GA_Admin();
} //endif


/**
 * Code that actually inserts stuff into pages.
 */
if ( ! class_exists( 'GA_Filter' ) ) {
	class GA_Filter {

		/**
		 * Cleans the variable to make it ready for storing in Google Analytics
		 */
		function ga_str_clean($val) {
			return remove_accents(str_replace('---','-',str_replace(' ','-',strtolower(html_entity_decode($val)))));
		}
		/*
		 * Insert the tracking code into the page
		 */
		function spool_analytics() {	
			global $wp_query;
			// echo '<!--'.print_r($wp_query,1).'-->';

			$options  = get_option('Yoast_Google_Analytics');
			
			/**
			 * The order of custom variables is very, very important: custom vars should always take up the same slot to make analysis easy.
			 */
			$customvarslot = 1;
			if ( $options["uastring"] != "" && yoast_ga_do_tracking() && !is_preview() ) { 
				$push = array();

				if ( $options['allowanchor'] )
					$push[] = "'_setAllowAnchor',true";

				if ( $options['allowlinker'] )
					$push[] = "'_setAllowLinker',true";
				
				if ( $options['anonymizeip'] )
					$push[] = "'_gat._anonymizeIp'";
					
				if ( isset($options['domain']) && $options['domain'] != "" ) {
					// should allow for a 'none' domain too!
					if (substr($options['domain'],0,1) != "." && $options['domain'] != 'none')
						$options['domain'] = ".".$options['domain'];
					$push[] = "'_setDomainName','".$options['domain']."'";
				}

				if ( $options['cv_loggedin'] ) {
					$current_user = wp_get_current_user();
					if ( $current_user && $current_user->ID != 0 )
						$push[] = "'_setCustomVar',$customvarslot,'logged-in','".$current_user->roles[0]."',1";
					// Customvar slot needs to be upped even when the user is not logged in, to make sure the variables below are always in the same slot.
					$customvarslot++;
				} 

				if ( is_singular() && !is_home() ) {
					if ( $options['cv_post_type'] ) {
						$post_type = get_post_type();
						if ( $post_type ) {
							$push[] = "'_setCustomVar',".$customvarslot.",'post_type','".$post_type."',3";
							$customvarslot++;						
						}
					}
					if ( $options['cv_authorname'] ) {
						$push[] = "'_setCustomVar',$customvarslot,'author','".GA_Filter::ga_str_clean(get_the_author_meta('display_name',$wp_query->post->post_author))."',3";
						$customvarslot++;
					}
					if ( $options['cv_tags'] ) {
						$i = 0;
						$tagsstr = '';
						foreach ( (array) get_the_tags() as $tag ) {
							if ($i > 0)
								$tagsstr .= ' ';
							$tagsstr .= $tag->slug;
							$i++;
						}
						// Max 64 chars for value and label combined, hence 64 - 4
						$tagsstr = substr($tagsstr, 0, 60);
						$push[] = "'_setCustomVar',$customvarslot,'tags','".$tagsstr."',3";
						$customvarslot++;
					}
					if ( is_single() ) {
						if ( $options['cv_year'] ) {
							$push[] = "'_setCustomVar',$customvarslot,'year','".get_the_time('Y')."',3";
							$customvarslot++;
						}
						if ( $options['cv_category'] ) {
							$cats = get_the_category();
							$push[] = "'_setCustomVar',$customvarslot,'category','".$cats[0]->slug."',3";
							$customvarslot++;
						}
						if ( $options['cv_all_categories'] ) {
							$i = 0;
							$catsstr = '';
							foreach ( (array) get_the_category() as $cat ) {
								if ($i > 0)
									$catsstr .= ' ';
								$catsstr .= $cat->slug;
								$i++;
							}
							// Max 64 chars for value and label combined, hence 64 - 10 
							$catsstr = substr($catsstr, 0, 54);
							$push[] = "'_setCustomVar',$customvarslot,'categories','".$catsstr."',3";
							$customvarslot++;
						}
					}
				} 

				$push = apply_filters('yoast-ga-custom-vars',$push, $customvarslot);

				$push = apply_filters('yoast-ga-push-before-pageview',$push);
				
				if ( is_404() ) {
					$push[] = "'_trackPageview','/404.html?page=' + document.location.pathname + document.location.search + '&from=' + document.referrer";
				} else if ($wp_query->is_search) {
					$pushstr = "'_trackPageview','".get_bloginfo('url')."/?s=";
					if ($wp_query->found_posts == 0) {
						$push[] = $pushstr."no-results:".rawurlencode($wp_query->query_vars['s'])."&cat=no-results'";
					} else if ($wp_query->found_posts == 1) {
						$push[] = $pushstr.rawurlencode($wp_query->query_vars['s'])."&cat=1-result'";
					} else if ($wp_query->found_posts > 1 && $wp_query->found_posts < 6) {
						$push[] = $pushstr.rawurlencode($wp_query->query_vars['s'])."&cat=2-5-results'";
					} else {
						$push[] = $pushstr.rawurlencode($wp_query->query_vars['s'])."&cat=plus-5-results'";
					}
				} else {
					$push[] = "'_trackPageview'";
				}

				$push = apply_filters('yoast-ga-push-after-pageview',$push);

				if ( defined('WPSC_VERSION') && $options['wpec_tracking'] )
					$push = GA_Filter::wpec_transaction_tracking($push);
				
				if ($options['shopp_tracking']) {
					global $Shopp;
					if ( isset($Shopp) )
						$push = GA_Filter::shopp_transaction_tracking($push);					
				}
				
				$pushstr = "";
				foreach ($push as $key) {
					if (!empty($pushstr))
						$pushstr .= ",";

					$pushstr .= "[".$key."]";
				}

				?>
				
	<script type="text/javascript">//<![CDATA[
	// Google Analytics for WordPress by Yoast v<?php echo $options['version'];  ?> | http://yoast.com/wordpress/google-analytics/
	var _gaq = _gaq || [];
	_gaq.push(['_setAccount','<?php echo trim($options["uastring"]); ?>']);
<?php
	if ( $options["extrase"] ) {
		if ( !empty($options["extraseurl"]) ) {
			$url = $options["extraseurl"];
		} else {
			$url = gapp_plugin_path().'custom_se_async.js';
		}
		echo '</script><script src="'.$url.'" type="text/javascript"></script>'."\n".'<script type="text/javascript">'; 
	}

?>
	_gaq.push(<?php echo $pushstr; ?>);
	(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = <?php 
if ( $options['gajslocalhosting'] && !empty($options['gajsurl']) ) {
	echo "'".$options['gajsurl']."';";
} else {
	echo "('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';";
}
?>

		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();
	// End of Google Analytics for WordPress by Yoast v4.0
	//]]></script>
	
<?php
			} else if ( $options["uastring"] != "" ) {
				echo "<!-- Google Analytics tracking code not shown because users over level ".$options["ignore_userlevel"]." are ignored -->\n";
			} else if ( $options["uastring"] == "" && current_user_can('manage_options') ) {
				echo "<!-- Google Analytics tracking code not shown because yo haven't chosen a Google Analytics account yet. -->\n";
			}
		}

		/*
		 * Insert the AdSense parameter code into the page. This'll go into the header per Google's instructions.
		 */
		function spool_adsense() {
			$options  = get_option('Yoast_Google_Analytics');
			if ( $options["uastring"] != "" && yoast_ga_do_tracking() && !is_preview() ) {
				echo '<script type="text/javascript">'."\n";
				echo "\t".'window.google_analytics_uacct = "'.$options["uastring"].'";'."\n"; 
				echo '</script>'."\n";
			}
		}		

		function ga_get_tracking_prefix() {
			$options  = get_option('Yoast_Google_Analytics');
			return (empty($options['trackprefix'])) ? '/yoast-ga/' : $options['trackprefix'];
		}
		
		function ga_get_tracking_link($prefix, $target, $jsprefix = 'javascript:') {
			$options  = get_option('Yoast_Google_Analytics');
			if ( 
				( $prefix != 'download' && $options['outboundpageview'] ) || 
				( $prefix == 'download' && $options['downloadspageview'] ) ) 
			{
				$prefix = GA_Filter::ga_get_tracking_prefix().$prefix;
				$pushstr = "['_trackPageview','".$prefix."/".$target."']";
			} else {
				$pushstr = "['_trackEvent','".$prefix."','".$target."']";
			}
			return $jsprefix."_gaq.push(".$pushstr.");";
		}
		
		function ga_get_domain($uri){
			$hostPattern = "/^(http:\/\/)?([^\/]+)/i";
			$domainPatternUS = "/[^\.\/]+\.[^\.\/]+$/";
			$domainPatternUK = "/[^\.\/]+\.[^\.\/]+\.[^\.\/]+$/";

			preg_match($hostPattern, $uri, $matches);
			$host = $matches[2];
			if (preg_match("/.*\..*\..*\..*$/",$host))
				preg_match($domainPatternUK, $host, $matches);
			else
				preg_match($domainPatternUS, $host, $matches);

			return array("domain"=>$matches[0],"host"=>$host);
		}

		function ga_parse_link($category, $matches){
			$origin = GA_Filter::ga_get_domain($_SERVER["HTTP_HOST"]);
			$options  = get_option('Yoast_Google_Analytics');
			
			// Break out immediately if the link is not an http or https link.
			if (strpos($matches[2],"http") !== 0) {
				$target = false;
			} else if ((strpos($matches[2],"mailto") === 0)) {
				$target = 'email';
			} else {
				$target = GA_Filter::ga_get_domain($matches[3]);
			}
			$trackBit = "";
			$extension = substr(strrchr($matches[3], '.'), 1);
			$dlextensions = split(",",str_replace('.','',$options['dlextensions']));
			if ( $target ) {
				if ( $target == 'email' ) {
					$trackBit = GA_Filter::ga_get_tracking_link('mailto', str_replace('mailto:','',$matches[3]),'');
				} else if ( in_array($extension, $dlextensions) ) {
					$trackBit = GA_Filter::ga_get_tracking_link('download', $matches[3],'');
				} else if ( $target["domain"] != $origin["domain"] ){
					if ($options['domainorurl'] == "domain") {
						$url = $target["host"];
					} else if ($options['domainorurl'] == "url") {
						$url = $matches[3]; 
					}
					$trackBit = GA_Filter::ga_get_tracking_link($category, $url,'');
				} else if ( $target["domain"] == $origin["domain"] && isset($options['internallink']) && $options['internallink'] != '') {
					$url = preg_replace('|'.$origin["host"].'|','',$matches[3]);
					$extintlinks = explode(',',$options['internallink']);
					foreach ($extintlinks as $link) {
						if (preg_match('|^'.trim($link).'|', $url, $match)) {
							$label = $options['internallinklabel'];
							if ($label == '')
								$label = 'int';
							$trackBit = GA_Filter::ga_get_tracking_link($category.'-'.$label, $url,'');
						}						
					}
				}
			} 
			if ($trackBit != "") {
				if (preg_match('/onclick=[\'\"](.*?)[\'\"]/i', $matches[4]) > 0) {
					// Check for manually tagged outbound clicks, and replace them with the tracking of choice.
					if (preg_match('/.*_track(Pageview|Event).*/i', $matches[4]) > 0) {
						$matches[4] = preg_replace('/onclick=[\'\"](javascript:)?(.*;)?[a-zA-Z0-9]+\._track(Pageview|Event)\([^\)]+\)(;)?(.*)?[\'\"]/i', 'onclick=\'javascript:' . $trackBit .'$2$5\'', $matches[4]);
					} else {
						$matches[4] = preg_replace('/onclick=[\'\"](javascript:)?(.*?)[\'\"]/i', 'onclick=\'javascript:' . $trackBit .'$2\'', $matches[4]);
					}
				} else {
					$matches[4] = 'onclick=\'javascript:' . $trackBit . '\'' . $matches[4];
				}				
			}
			return '<a ' . $matches[1] . 'href=\'' . $matches[2] . '//' . $matches[3] . '\'' . ' ' . $matches[4] . '>' . $matches[5] . '</a>';
		}

		function ga_parse_article_link($matches){
			return GA_Filter::ga_parse_link('outbound-article',$matches);
		}

		function ga_parse_comment_link($matches){
			return GA_Filter::ga_parse_link('outbound-comment',$matches);
		}

		function ga_parse_widget_link($matches){
			return GA_Filter::ga_parse_link('outbound-widget',$matches);
		}

		function widget_content($text) {
			if ( !yoast_ga_do_tracking() )
				return $text;
			static $anchorPattern = '/<a (.*?)href=[\'\"](.*?)\/\/([^\'\"]+?)[\'\"](.*?)>(.*?)<\/a>/i';
			$text = preg_replace_callback($anchorPattern,array('GA_Filter','ga_parse_widget_link'),$text);
			return $text;
		}
		
		function the_content($text) {
			if ( !yoast_ga_do_tracking() )
				return $text;

			if (!is_feed()) {
				static $anchorPattern = '/<a (.*?)href=[\'\"](.*?)\/\/([^\'\"]+?)[\'\"](.*?)>(.*?)<\/a>/i';
				$text = preg_replace_callback($anchorPattern,array('GA_Filter','ga_parse_article_link'),$text);				
			}
			return $text;
		}

		function comment_text($text) {
			if ( !yoast_ga_do_tracking() )
				return $text;

			if (!is_feed()) {
				static $anchorPattern = '/<a (.*?)href="(.*?)\/\/(.*?)"(.*?)>(.*?)<\/a>/i';
				$text = preg_replace_callback($anchorPattern,array('GA_Filter','ga_parse_comment_link'),$text);
			}
			return $text;
		}

		function comment_author_link($text) {
			if ( !yoast_ga_do_tracking() )
				return $text;

	        static $anchorPattern = '/(.*\s+.*?href\s*=\s*)["\'](.*?)["\'](.*)/';
			preg_match($anchorPattern, $text, $matches);
			if ($matches[2] == "") return $text;

			$target = GA_Filter::ga_get_domain($matches[2]);
			$origin = GA_Filter::ga_get_domain($_SERVER["HTTP_HOST"]);
			if ( $target["domain"] != $origin["domain"]  ){
				if ($options['domainorurl'] == "domain")
					$url = $target["host"];
				else
					$url = $matches[2];
				$trackBit = 'onclick="'.GA_Filter::ga_get_tracking_link('outbound-commentauthor', $url).'"';
			} 
			return $matches[1] . "\"" . $matches[2] . "\" " . $trackBit ." ". $matches[3];    
		}
		
		function bookmarks($bookmarks) {
			if ( !yoast_ga_do_tracking() )
				return $bookmarks;
			
			$i = 0;
			while ( $i < count($bookmarks) ) {
				$target = GA_Filter::ga_get_domain($bookmarks[$i]->link_url);
				$sitedomain = GA_Filter::ga_get_domain(get_bloginfo('url'));
				if ($target['host'] == $sitedomain['host']) {
					$i++;
					continue;
				}
				if ($options['domainorurl'] == "domain")
					$url = $target["host"];
				else
					$url = $bookmarks[$i]->link_url;
				$trackBit = '" onclick="'.GA_Filter::ga_get_tracking_link('outbound-blogroll', $url);
				$bookmarks[$i]->link_target .= $trackBit;
				$i++;
			}
			return $bookmarks;
		}
		
		function rsslinktagger($guid) {
			$options  = get_option('Yoast_Google_Analytics');
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
		
		function wpec_transaction_tracking( $push ) {
			global $wpdb, $purchlogs, $cart_log_id;
			if( !isset( $cart_log_id ) || empty($cart_log_id) )
				return $push;

			$city = $wpdb->get_var ("SELECT tf.value
		                               FROM ".WPSC_TABLE_SUBMITED_FORM_DATA." tf
		                          LEFT JOIN ".WPSC_TABLE_CHECKOUT_FORMS." cf
		                                 ON cf.id = tf.form_id
		                              WHERE cf.type = 'city'
		                                AND log_id = ".$cart_log_id );

			$country = $wpdb->get_var ("SELECT tf.value
		                                  FROM ".WPSC_TABLE_SUBMITED_FORM_DATA." tf
		                             LEFT JOIN ".WPSC_TABLE_CHECKOUT_FORMS." cf
		                                    ON cf.id = tf.form_id
		                                 WHERE cf.type = 'country'
		                                   AND log_id = ".$cart_log_id );

			$cart_items = $wpdb->get_results ("SELECT * FROM ".WPSC_TABLE_CART_CONTENTS." WHERE purchaseid = ".$cart_log_id, ARRAY_A);

			$total_shipping = $purchlogs->allpurchaselogs[0]->base_shipping;	
			$total_tax 		= 0;
			foreach ( $cart_items as $item ) {
				$total_shipping += $item['pnp'];
				$total_tax		+= $item['tax_charged'];
			}

			$push[] = "'_addTrans','".$cart_log_id."',"															// Order ID
			."'".GA_Filter::ga_str_clean(get_bloginfo('name'))."',"											// Store name
			."'".nzshpcrt_currency_display($purchlogs->allpurchaselogs[0]->totalprice,1,true,false,true)."',"	// Total price
			."'".nzshpcrt_currency_display($total_tax,1,true,false,true)."',"									// Tax
			."'".nzshpcrt_currency_display($total_shipping,1,true,false,true)."',"								// Shipping
			."'".$city."',"																						// City
			."'',"																								// State
			."'".$country."'";																					// Country

			foreach( $cart_items as $item ) {
				$item['sku'] = $wpdb->get_var( "SELECT meta_value FROM ".WPSC_TABLE_PRODUCTMETA." WHERE meta_key = 'sku' AND product_id = '".$item['prodid']."' LIMIT 1" );

				$item['category'] = $wpdb->get_var( "SELECT pc.name FROM ".WPSC_TABLE_PRODUCT_CATEGORIES." pc LEFT JOIN ".WPSC_TABLE_ITEM_CATEGORY_ASSOC." ca ON pc.id = ca.category_id WHERE pc.group_id = '1' AND ca.product_id = '".$item['prodid']."'" );	
				$push[] = "'_addItem',"
				."'".$cart_log_id."',"			// Order ID
				."'".$item['sku']."',"			// Item SKU
				."'".$item['name']."',"			// Item Name
				."'".$item['category']."',"		// Item Category
				."'".$item['price']."',"		// Item Price
				."'".$item['quantity']."'";		// Item Quantity
			}
			$push[] = "'_trackTrans'";

			return $push;
		}
		
		function shopp_transaction_tracking( $push ) {
			global $Shopp;
			// Only process if we're in the checkout process (receipt page)
			if (function_exists('is_shopp_page') && !is_shopp_page('checkout')) 
				return $push;
			// Only process if we have valid order data
			if (!isset($Shopp->Cart->data->Purchase) || empty($Shopp->Cart->data->Purchase->id)) 
				return $push;

			$Purchase = $Shopp->Cart->data->Purchase;
			$push[] = "'_addTrans',"
						."'".$Purchase->id."',"										// Order ID
						."'".GA_Filter::ga_str_clean(get_bloginfo('name'))."'," 	// Store
						."'".number_format($Purchase->total,2)."'," 				// Total price
						."'".number_format($Purchase->tax,2)."',"					// Tax
						."'".number_format($Purchase->shipping,2)."',"				// Shipping
						."'".$Purchase->city."',"									// City
						."'".$Purchase->state."',"									// State
						."'.$Purchase->country.'";									// Country

			foreach ($Purchase->purchased as $item) {
				$sku = empty($item->sku) ? 'PID-'.$item->product.str_pad($item->price,4,'0',STR_PAD_LEFT) : $item->sku;
				$push[] = 	"'_addItem',"
							."'".$Purchase->id."',"
							."'".$sku."',"
							."'".$item->name."',"
							."'".$item->optionlabel."',"
							."'".number_format($item->unitprice,2)."',"
							."'".$item->quantity."'";
			}
			$push[] = "'_trackTrans'";
			return $push;
		}
		
	} // class GA_Filter
} // endif

/**
 * If setAllowAnchor is set to true, GA ignores all links tagged "normally", so we redirect all "normally" tagged URL's
 * to one tagged with a hash. 
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

function yoast_ga_do_tracking() {
	$current_user = wp_get_current_user();
	
	if (!$current_user)
		return true;
	
	$yoast_ga_options = get_option('Yoast_Google_Analytics');
	
	if ( ($current_user->user_level >= $yoast_ga_options["ignore_userlevel"]) )
		return false;
	else
		return true;
}

function track_comment_form_head() {
    if (is_singular()) {
        global $post;
        $yoast_ga_options = get_option('Yoast_Google_Analytics');
        if ( yoast_ga_do_tracking() && $yoast_ga_options["trackcommentform"] && ('open' == $post->comment_status) )
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
    $yoast_ga_options = get_option('Yoast_Google_Analytics');
    if ( yoast_ga_do_tracking() && $yoast_ga_options["trackcommentform"] ) {
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
}
add_action('comment_form_after','yoast_track_comment_form');
/*
function gfform_tag() {
	$options = get_option('Yoast_Google_Analytics');
	if ( isset( $options['taggfsubmit'] ) && $options['taggfsubmit'] ) {
		$title = GA_Filter::ga_str_clean( $form['title'] );
		if ($options['gfsubmiteventpv'] == 'events') {
			$pv = "['_trackEvent','gf_form_submit','".$title."']";
		} else {
			$pv = "['_trackPageview','".GA_Filter::ga_get_tracking_prefix()."gf-form-submit/".$title."']";
		}
	}
	wp_enqueue_script('jquery');
	?>
	<script type="text/javascript" charset="utf-8">
		jQuery(document).ready(function() {
			jQuery('.gform_wrapper form').submit(function() {
				_gaq.push(<?php echo $pv; ?>);
			});
		});
	</script>
	<?php
}
add_action('wp_footer','gfform_tag',10);
*/

function yoast_sanitize_relative_links($content) {
	preg_match("|^http(s)?://([^/]+)|i", get_bloginfo('url'), $match);
    $content = preg_replace("/<a([^>]*) href=('|\")\/([^\"']*)('|\")/", "<a\${1} href=\"" .$match[0] ."/" ."\${3}\"", $content);

	if (is_singular()) {
		$content = preg_replace("/<a([^>]*) href=('|\")#([^\"']*)('|\")/", "<a\${1} href=\"" .get_permalink()."#" ."\${3}\"", $content);
    }
    return $content;
}
add_filter('the_content', 'yoast_sanitize_relative_links', 98);
add_filter('widget_text', 'yoast_sanitize_relative_links', 98);

function yoast_analytics() {
	$options	= get_option('Yoast_Google_Analytics');
	if ($options['position'] == 'manual')
		GA_Filter::spool_analytics();
	else
		echo '<!-- Please set Google Analytics position to "manual" in the settings, or remove this call to yoast_analytics(); -->';
}

$gaf 		= new GA_Filter();
$options	= get_option('Yoast_Google_Analytics');

if (!is_array($options)) {
	$options = get_option('GoogleAnalyticsPP');
	if (!is_array($options)) {
		$ga_admin->set_defaults();
	} else {
		delete_option('GoogleAnalyticsPP');
		if ($options['admintracking']) {
			$options["ignore_userlevel"] = '8';
			unset($options['admintracking']);
		} else {
			$options["ignore_userlevel"] = '11';
		}
		update_option('Yoast_Google_Analytics', $options);
	}
}

if ( $options['allowanchor'] ) {
	add_action('init','ga_utm_hashtag_redirect',1);
}

if ($options['trackoutbound']) {
	// filters alter the existing content
	add_filter('the_content', array('GA_Filter','the_content'), 99);
	add_filter('widget_text', array('GA_Filter','widget_content'), 99);
	add_filter('the_excerpt', array('GA_Filter','the_content'), 99);
	add_filter('comment_text', array('GA_Filter','comment_text'), 99);
	add_filter('get_bookmarks', array('GA_Filter','bookmarks'), 99);
	add_filter('get_comment_author_link', array('GA_Filter','comment_author_link'), 99);
}

if ($options['trackadsense'])
	add_action('wp_head', array('GA_Filter','spool_adsense'),1);	

switch ($options['position']) {
	case 'manual':
		// No need to insert here, bail NOW.
		break;
	case 'header':
	default:
		add_action('wp_head', array('GA_Filter','spool_analytics'),2);
		break;
}

if ($options['trackregistration'])
	add_action('login_head', array('GA_Filter','spool_analytics'),20);	
	
if ($options['rsslinktagging'])
	add_filter ( 'the_permalink_rss', array('GA_Filter','rsslinktagger'), 99 );	

?>