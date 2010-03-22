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

			add_action('admin_footer', array(&$this,'warning'));
			add_action('admin_footer', array(&$this,'theme_switch_warning'));

			add_action('admin_init', array(&$this,'save_settings'));

			add_action('switch_theme', array(&$this,'switch_theme'));
		}
		
		function config_page_head() {
			if ($_GET['page'] == $this->hook) {
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
								jQuery('#position_footer').css("display","none");
								jQuery('#position_manual').css("display","none");
							} else if (jQuery('#position').val() == 'footer') {
								jQuery('#position_header').css("display","none");
								jQuery('#position_footer').css("display","block");
								jQuery('#position_manual').css("display","none");
							} else {
								jQuery('#position_header').css("display","none");
								jQuery('#position_footer').css("display","none");
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
						jQuery('#advancedsettings').change(function(){
							if ((jQuery('#advancedsettings').attr('checked')) == true)  {
								jQuery('#advancedgasettings').css("display","block");
								jQuery('#customvarsettings').css("display","block");
							} else {
								jQuery('#advancedgasettings').css("display","none");
								jQuery('#customvarsettings').css("display","none");
							}
						}).change();
						jQuery('#extrase').change(function(){
							if ((jQuery('#extrase').attr('checked')) == true)  {
								jQuery('#extrasebox').css("display","block");
							} else {
								jQuery('#extrasebox').css("display","none");
							}
						}).change();
					});
				 </script>
			<?php
			}
		}
				
		function checkbox($id) {
			$options = get_option( $this->optionname );
			return '<input type="checkbox" id="'.$id.'" name="'.$id.'"'. checked($options[$id],true,false).'/>';
		}

		function textinput($id) {
			$options = get_option( $this->optionname );
			return '<input type="text" id="'.$id.'" name="'.$id.'" size="30" value="'.$options[$id].'"/>';
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
			$options['position']		= 'footer';
			update_option( $this->optionname, $options );
		}
		
		function is_integrated_theme( $theme = '' ) {
			if ( empty( $theme ) )
				$theme = get_current_theme();
			if ( in_array( $theme, array( 'Thesis' ) ) )
				return $theme;
			if ( defined( 'THEMATICVERSION' ) )
				return 'Thematic';
			if ( defined( 'HEADWAYVERSION' ) )
				return 'Headway';
			if ( defined( 'PARENT_THEME_NAME' ) && PARENT_THEME_NAME == 'Genesis' )
				return PARENT_THEME_NAME;
			return false;
		}
		
		function save_settings() {
			$options = get_option( $this->optionname );
			
			if ( isset($_REQUEST['reset']) && $_REQUEST['reset'] == "true" && isset($_REQUEST['plugin']) && $_REQUEST['plugin'] == 'google-analytics-for-wordpress') {
				$options = $this->set_defaults();
				$options['msg'] = "<div class=\"updated\"><p>Google Analytics settings reset.</p></div>\n";
			} elseif ( isset($_POST['submit']) && isset($_POST['plugin']) && $_POST['plugin'] == 'google-analytics-for-wordpress') {
				if (!current_user_can('manage_options')) die(__('You cannot edit the Google Analytics for WordPress options.'));
				check_admin_referer('analyticspp-config');
				
				foreach (array('uastring', 'dlextensions', 'domainorurl','position','domain', 'ga_token', 'extraseurl') as $option_name) {
					if (isset($_POST[$option_name]))
						$options[$option_name] = $_POST[$option_name];
					else
						$options[$option_name] = '';
				}
				
				foreach (array('extrase', 'imagese', 'trackoutbound', 'admintracking', 'trackadsense', 'allowanchor', 'rsslinktagging', 'advancedsettings', 'trackregistration', 'theme_updated', 'cv_loggedin', 'cv_authorname', 'cv_category', 'cv_year', 'outboundpageview', 'downloadspageview', 'manual_uastring') as $option_name) {
					if (isset($_POST[$option_name]) && $_POST[$option_name] != 'off')
						$options[$option_name] = true;
					else
						$options[$option_name] = false;
				}
				
				$options['msg'] = "<div id=\"updatemessage\" class=\"updated fade\"><p>Google Analytics settings updated.</p></div>\n";
				$options['msg'] .= "<script type=\"text/javascript\">setTimeout(function(){jQuery('#updatemessage').hide('slow');}, 3000);</script>";	
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
				<a href="http://yoast.com/"><div id="yoast-icon" style="background: url(http://netdna.yoast.com/wp-content/themes/yoast-v2/images/yoast-32x32.png) no-repeat;" class="icon32"><br /></div></a>
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
										$line = 'Please authenticate with Google Analytics to retrieve your tracking code: <a class="button-primary" href="'.$query.'">Click here to authenticate with Google</a>';
									} else if(isset($_GET['token']) || (isset($options['ga_token']) && !empty($options['ga_token']))) {
										if (isset($_GET['token']))
											$token = $_GET['token'];
										else
											$token = $options['ga_token'];
										
										require_once('xmlparser.php');

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
												'timeout'		=> 10,
											);
											$result = $request->request( $api_url , $args );
											if (is_array($result) && $result['response']['code'] == 200) {
												$options['ga_api_responses'][$token] = $result;
												$options['ga_token'] = $token;
												update_option('GoogleAnalyticsPP', $options);												
											}
										}

										if (is_array($options['ga_api_responses'][$token]) && $options['ga_api_responses'][$token]['response']['code'] == 200) {
											$arr = yoast_xml2array($options['ga_api_responses'][$token]['body']);
										
											$ga_accounts = array();
											foreach ($arr['feed']['entry'] as $site) {
												$ua = $site['dxp:property']['3_attr']['value'];
												$account = $site['dxp:property']['1_attr']['value'];
												if (!isset($ga_accounts[$account]) || !is_array($ga_accounts[$account]))
													$ga_accounts[$account] = array();
												$ga_accounts[$account][$site['title']] = $ua;
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
														// $select1 = str_replace('value="'.$i.'"','value="'.$i.'" '.$sel,$select1);
													}
													$select2 .= "\t".'<option class="sub_'.$i.'" '.$sel.' value="'.$ua.'">'.$title.'</option>'."\n";
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
									$line = '<div id="uastring_automatic">'.$line.'</div><div style="display:none;" id="uastring_manual">Manually enter your UA code: <input id="uastring" name="uastring" type="text" size="20" maxlength="40" value="'.$options['uastring'].'"/></div>';
									$rows = array();
									$content = '';
									$rows[] = array(
										'id' => 'uastring',
										'label' => 'Analytics Profile',
										'desc' => '<input type="checkbox" name="manual_uastring" '.checked($options['manual_uastring'], true, false).' id="switchtomanual"/> <label for="switchtomanual">Manually enter your UA code</label>',
										'content' => $line
									);
									$integrated_theme = $this->is_integrated_theme();
									if ( !$integrated_theme ) {
										$temp_content = '<select name="position" id="position">
											<option value="footer" '.selected($options['position'],'footer',false).'>In the footer (default)</option>
											<option value="header" '.selected($options['position'],'header',false).'>In the header</option>
											<option value="manual" '.selected($options['position'],'manual',false).'>Insert manually</option>
										</select>';
										if ($options['theme_updated']) {
											$temp_content .= '<input type="hidden" name="theme_updated" value="off"/>';
											echo '<div id="message" class="updated" style="background-color:lightgreen;border-color:green;"><p><strong>Notice:</strong> Your Google Analytics can be adjusted: save your settings to make sure Google Analytics gets loaded properly.</p></div>';
											remove_action('admin_footer', array(&$this,'theme_switch_warning'));
										}
										
										$desc = '<div id="position_header">While the header is by far the best spot to place the tracking code, it does sometimes cause issues. You should make very sure that all the tags in your head section are properly closed. For more info <a href="http://yoast.com/wordpress/google-analytics/manual-placement/">read this page</a>.</div>';
										
										$desc .= '<div id="position_manual"><a href="http://yoast.com/wordpress/google-analytics/manual-placement/">Follow the instructions here</a> to choose the location for your tracking code manually.</div>';
										$desc .= '<div id="position_footer">Placing the tracking code in the header gives the best results, but might leed to issues with IE6 &amp; 7 when the HTML in the &lt;head&gt; area is not valid, because of that this plugin defaults to footer. If you\'re certain your HTML is valid, please do set the position to header for better tracking. You could also insert the tracking code manually, be sure to follow <a href="http://yoast.com/wordpress/google-analytics/manual-placement/">the instructions on how to do that</a>.</div> ';

										$rows[] = array(
											'id' => 'position',
											'label' => 'Where should the tracking code be placed?',
											'desc' => $desc,
											'content' => $temp_content,
										);
									} else {
										$temp_content = 'Your current theme framework ('.$integrated_theme.') allows for automatic integration.<input type="hidden" name="position" value="'.$integrated_theme.'"/>';
										if ($options['theme_updated']) {
											echo '<div id="message" class="updated" style="background-color:lightgreen;border-color:green;"><p><strong>Notice:</strong> Your Google Analytics can be adjusted: save your settings to allow for automatic integration.</p></div>';
											$temp_content .= '<input type="hidden" name="theme_updated" value="off"/>';
											remove_action('admin_footer', array(&$this,'theme_switch_warning'));
										}
											
										$rows[] = array(
											'id' => 'position',
											'label' => 'Tracking code location',
											'content' => $temp_content,
										);
									}
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
									$this->postbox('gasettings','Google Analytics Settings',$this->form_table($rows).'<div class="alignright"><input type="submit" class="button-primary" name="submit" value="Update Google Analytics Settings &raquo;" /></div><br class="clear"/>');
								
								
									$rows = array();
									$pre_content = '<p>Google Analytics allows you to save up to 5 custom variables on each page, and this plugin helps you make the most use of these! Check which custom variables you\'d like the plugin to save for you below. Please note that these will only be saved when they are actually available.</p><p>If you want to start using these custom variables, go to Visitors &raquo; Custom Variables in your Analytics reports.</p>';
									$rows[] = array(
										'id' => 'cv_loggedin',
										'label' => 'Logged in Users',
										'desc' => 'Allows you to easily remove logged in users from your reports.',
										'content' =>  $this->checkbox('cv_loggedin'),
									);
									$rows[] = array(
										'id' => 'cv_authorname',
										'label' => 'Author Name',
										'desc' => 'Allows you to see pageviews per author.',
										'content' =>  $this->checkbox('cv_authorname'),
									);
									$rows[] = array(
										'id' => 'cv_category',
										'label' => 'Category',
										'desc' => 'Allows you to see pageviews per category, works best when each post is in only one category.',
										'content' =>  $this->checkbox('cv_category'),
									);
									$rows[] = array(
										'id' => 'cv_year',
										'label' => 'Publication year',
										'desc' => 'Allows you to see pageviews per year of publication, showing you if your old posts still get traffic.',
										'content' =>  $this->checkbox('cv_year'),
									);
									$this->postbox('customvarsettings','Custom Variables Settings',$pre_content.$this->form_table($rows).'<div class="alignright"><input type="submit" class="button-primary" name="submit" value="Update Google Analytics Settings &raquo;" /></div><br class="clear"/>');
									
									$rows = array();
									$rows[] = array(
										'id' => 'admintracking',
										'label' => 'Track the administrator too',
										'desc' => 'Not recommended, as this would schew your statistics.',
										'content' =>  $this->checkbox('admintracking'),
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
										'content' => $this->checkbox('extrase').'<div id="extrasebox">
											You can provide a custom URL to the extra search engines file if you want:
											<input type="text" name="extraseurl" size="30" value="'.$options['extraseurl'].'"/>
										</div>',
									);
									$rows[] = array(
										'id' => 'imagese',
										'label' => 'Track Google Image Search as a Search Engine',
										'content' => $this->checkbox('imagese'),
									);
									$rows[] = array(
										'id' => 'rsslinktagging',
										'label' => 'Tag links in RSS feed with campaign variables?',
										'desc' => 'Do not use this feature if you use FeedBurner, as FeedBurner can do this automatically, and better than this plugin can. Check <a href="http://www.google.com/support/feedburner/bin/answer.py?hl=en&amp;answer=165769">this help page</a> for info on how to enable this feature in FeedBurner.',
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
										'desc' => 'This adds a <a href="http://code.google.com/apis/analytics/docs/gaJSApiCampaignTracking.html#_gat.GA_Tracker_._setAllowAnchor">setAllowAnchor</a> call to your tracking code, and makes RSS link tagging use a # as well.',
										'content' => $this->checkbox('allowanchor'),
									);
									$this->postbox('advancedgasettings','Advanced Settings',$content.$this->form_table($rows).'<div class="alignright"><input type="submit" class="button-primary" name="submit" value="Update Google Analytics Settings &raquo;" /></div><br class="clear"/>');

									$content = "<p><a href='http://www.google.com/analytics/authorized_consultants.html'><img src='".plugins_url('google-analytics-for-wordpress')."/images/GAAC-logo.gif' class='alignright' style='margin-left:10px;' alt='Google Analytics Authorized Consultant'/></a>If you're serious about making money with your site, you're probably serious about your analytics too (and if you're not, you should be!). If you think you're not getting the best out of your Google Analytics, you might want to hire serious help too. OrangeValley is a <a href='http://www.google.com/analytics/authorized_consultants.html'>Google Analytics Authorized Consultant</a> and can help you get the most out of your site and marketing.</p><p><a href='http://yoast.com/hire-me/'>Contact us today to start a conversation about how we can help you!</a></p>";

									$this->postbox('gagaac',__('Google Analytics Support', 'ywawp'), $content);
								
								?>
					</form>
					<form action="<?php echo $this->plugin_options_url(); ?>" method="post">
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
						$this->plugin_like();
						$this->postbox('donate','Donate $5, $10 or $20 now!','<form style="margin-left:50px;" action="https://www.paypal.com/cgi-bin/webscr" method="post">
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
				'admintracking' 		=> true,
				'advancedsettings' 		=> false,
				'allowanchor' 			=> false,
				'cv_loggedin'			=> false,
				'cv_authorname'			=> false,
				'cv_category'			=> false,
				'cv_year'				=> false,
				'dlextensions' 			=> 'doc,exe,js,pdf,ppt,tgz,zip,xls',
				'domainorurl' 			=> 'domain',
				'ga_token' 				=> '',
				'ga_api_responses'		=> array(),
				'extrase' 				=> false,
				'extraseurl'			=> '',
				'imagese' 				=> false,
				'outboundpageview'		=> false,
				'downloadspageview'		=> false,
				'position' 				=> 'footer',
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

		/*
		 * Insert the tracking code into the page
		 */
		function spool_analytics() {	
			global $wp_query;
			// echo '<!--'.print_r($wp_query,1).'-->';
			$options  = get_option('GoogleAnalyticsPP');
			
			$customvarslot = 1;
			if ( $options["uastring"] != "" && (!current_user_can('edit_users') || $options["admintracking"]) && !is_preview() ) { 
				$push = array();
				$push[] = "'_setAccount','".$options["uastring"]."'";

				if ( $options['allowanchor'] )
					$push[] = "'_setAllowAnchor','true'";
				
				if ( isset($options['domain']) && $options['domain'] != "" ) {
					if (substr($options['domain'],0,1) != ".")
						$options['domain'] = ".".$options['domain'];
					$push[] = "'_setDomainName','".$options['domain']."'";
				}

				if ( $options['cv_loggedin'] && is_user_logged_in() ) {
					$push[] = "'_setCustomVar',".$customvarslot.",'logged-in','1',1";
					$customvarslot++;
				}
				
				if ( is_singular() ) {
					if ( $options['cv_authorname'] ) {
						$push[] = "'_setCustomVar',".$customvarslot.",'author','".str_replace(" ","-",strtolower(html_entity_decode(get_the_author_meta('display_name',$wp_query->post->post_author))))."'";
						$customvarslot++;
					}
				}
				if ( is_single() ) {
					if ( $options['cv_category'] ) {
						$cats = get_the_category();
						$push[] = "'_setCustomVar',".$customvarslot.",'category','".str_replace(" ","-",strtolower(html_entity_decode($cats[0]->name)))."'";
						$customvarslot++;
					}
					if ( $options['cv_year'] ) {
						$push[] = "'_setCustomVar',".$customvarslot.",'year','".get_the_time('Y')."'";
						$customvarslot++;
					}
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
<?php if ( $options["imagese"] ) { ?>
	regex = new RegExp("images.google.([^\/]+).*&prev=([^&]+)");
	var match = regex.exec(document.referrer);
	if (match != null) {
		_gaq.push(
			['_addOrganic', 'images.google.'+match[1], 'q', true],
			['_setReferrerOverride','http://images.google.'+match[1] +unescape(match[2])]
		);
	}
<?php } ?>
	_gaq.push( 
<?php echo $pushstr; ?> 
	);
	</script>
<?php
	if ( $options["extrase"] ) {
		if ( !empty($options["extraseurl"]) ) {
			$url = $options["extraseurl"];
		} else {
			$url = gapp_plugin_path().'custom_se_async.js';
		}
		echo '<script src="'.$url.'" type="text/javascript"></script>'."\n"; 
	}
		
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

		function ga_get_tracking_link($prefix, $target, $jsprefix = 'javascript:') {
			$options  = get_option('GoogleAnalyticsPP');
			if ( 
				( $prefix != 'download' && $options['outboundpageview'] ) || 
				( $prefix == 'download' && $options['downloadspageview'] ) ) 
			{
				$prefix = '/yoast-ga/'.$prefix;
				$pushstr = "['_trackPageview','".$prefix."/".$target."']";
			} else {
				$pushstr = "['_trackEvent','".$prefix."','".$target."']";
			}
			return $jsprefix."_gaq.push(".$pushstr.");";
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
			if (preg_match("/.*\..*\..*\..*$/",$host))
				preg_match($domainPatternUK, $host, $matches);
			else
				preg_match($domainPatternUS, $host, $matches);

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
			$extension = substr(strrchr($matches[3], '.'), 1);
			$dlextensions = split(",",str_replace('.','',$options['dlextensions']));
			if ( $target ) {
				if ( in_array($extension, $dlextensions) ) {
					$file = $matches[3];
					$trackBit = GA_Filter::ga_get_tracking_link('download', $file,'');
				} else if ( $target["domain"] != $origin["domain"] ){
					if ($options['domainorurl'] == "domain") {
						$url = $target["host"];
					} else if ($options['domainorurl'] == "url") {
						$url = $matches[3]; 
					}
					$trackBit = GA_Filter::ga_get_tracking_link($category, $url,'');
				} 				
			} 
			if ($trackBit != "") {
				if (preg_match('/onclick=[\'\"](.*?)[\'\"]/i', $matches[4]) > 0) {
					// Check for manually tagged outbound clicks, and replace them with the tracking of choice.
					if (preg_match('/.*_track(Pageview|Event).*/i', $matches[4]) > 0) {
						$matches[4] = preg_replace('/onclick=[\'\"](javascript:)?(.*;)?[a-zA-Z0-9]+\._track(Pageview|Event)\([^\)]+\)(;)?(.*)?[\'\"]/i', 'onclick="javascript:' . $trackBit .'$2$5"', $matches[4]);
					} else {
						$matches[4] = preg_replace('/onclick=[\'\"](javascript:)?(.*?)[\'\"]/i', 'onclick="javascript:' . $trackBit .'$2"', $matches[4]);
					}
				} else {
					$matches[4] = 'onclick="javascript:' . $trackBit . '"' . $matches[4];
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
			$options  = get_option('GoogleAnalyticsPP');
			
			if (!is_admin() && (!current_user_can('edit_users') || $options['admintracking'] ) ) {
				$i = 0;
				while ( $i < count($bookmarks) ) {
					$target = GA_Filter::ga_get_domain($bookmarks[$i]->link_url);
					$sitedomain = GA_Filter::ga_get_domain(get_bloginfo('url'));
					if ($target['host'] == $sitedomain['host'])
						continue;					
					if ($options['domainorurl'] == "domain")
						$url = $target["host"];
					else
						$url = $bookmarks[$i]->link_url;
					$trackBit = '" onclick="'.GA_Filter::ga_get_tracking_link('outbound-blogroll', $url).'"';
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

switch ($options['position']) {
	case 'manual':
		// No need to insert here, bail NOW.
		break;
	case 'Genesis':
		add_action('genesis_before', array('GA_Filter','spool_analytics'));
		break;
	case 'Headway':
		add_action('headway_before_everything', array('GA_Filter','spool_analytics'));
		break;
	case 'Thematic':
		add_action('thematic_before', array('GA_Filter','spool_analytics'));
		break;
	case 'Thesis':
		add_action('thesis_hook_before_html', array('GA_Filter','spool_analytics'));
		break;
	case 'header':
		add_action('wp_head', array('GA_Filter','spool_analytics'));
		break;
	case 'footer':
	default:
		add_action('wp_footer', array('GA_Filter','spool_analytics'));
		break;
}

if ($options['trackregistration'])
	add_action('login_head', array('GA_Filter','spool_analytics'),20);	
	
if ($options['rsslinktagging'])
	add_filter ( 'the_permalink_rss', array('GA_Filter','rsslinktagger'), 99 );	

?>