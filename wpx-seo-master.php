<?php
/*
Plugin Name: WPX SEO Manager
Plugin URI: http://wpxprt.com/wpx-seo-manager
Description: Everything SEO bundled into one great package.
Version: 0.2.1
Author: Wordpress Expert
Author URI: http://wpxprt.com

------------------------------------------------------------------------
Copyright 2012 Wordpress Expert (wpxprt.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

define('WPXSEO_VERSION', '0.2.1');

if (!defined('WPXSEO_PLUGIN_BASENAME')) {
    define('WPXSEO_PLUGIN_BASENAME', plugin_basename(__FILE__));
}
if (!defined('WPXSEO_PLUGIN_NAME')) {
    define('WPXSEO_PLUGIN_NAME', trim(dirname(WPXSEO_PLUGIN_BASENAME), '/'));
}
if (!defined('WPXSEO_PLUGIN_URL')) {
    define('WPXSEO_PLUGIN_URL', WP_PLUGIN_URL . '/' . WPXSEO_PLUGIN_NAME);
}

add_option('wpx_seo_alexa_webmaster', '');
add_option('wpx_seo_google_webmaster', '');
add_option('wpx_seo_bing_webmaster', '');

add_option('wpx_seo_google_analytics', '');
add_option('wpx_seo_quantcast_analytics', '');

add_option('sitemap_URL', '');

function wpx_seo_RegisterAdminScripts() {
	//wp_register_script('wpxseoBackend', WPXSEO_PLUGIN_URL .'/js/backend.js', array('jquery'));	
}

function wpx_seo_RegisterAdminStyles() {
	//wp_enqueue_style('thickbox');
}

function wpx_seo_GetReturnLocation(){
	$currentLocation = "http";
	$currentLocation .= ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') ? "s" : "")."://";
	$currentLocation .= $_SERVER['SERVER_NAME'];
	if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') {
		if($_SERVER['SERVER_PORT']!='443') {
			$currentLocation .= ":".$_SERVER['SERVER_PORT'];
		}
	}
	else {
		if($_SERVER['SERVER_PORT']!='80') {
			$currentLocation .= ":".$_SERVER['SERVER_PORT'];
		}
	}
	$currentLocation .= $_SERVER['REQUEST_URI'];
	echo $currentLocation;
}

add_action('admin_menu', 'wpx_seo_menu');

function wpx_seo_menu() {
	add_menu_page('WPX SEO Manager', 'SEO Manager', 'manage_options', 'wpx_seo_manager_dashboard', 'wpx_seo_manager');
	add_submenu_page('wpx_seo_manager_dashboard', 'Webmaster', 'Webmaster', 'manage_options', 'wpx-seo-webmaster', 'wpx_seo_webmaster');
	add_submenu_page('wpx_seo_manager_dashboard', 'Tracking', 'Tracking', 'manage_options', 'wpx-seo-tracking', 'wpx_seo_tracking');
	//add_submenu_page('wpx_seo_master_dashboard', 'Sitemap', 'Sitemap', 'manage_options', 'wpx-seo-sitemap', 'wpx_seo_sitemap');
	add_action('admin_print_scripts', 'wpx_seo_RegisterAdminScripts');
    add_action('admin_print_styles', 'wpx_seo_RegisterAdminStyles');
}
//Pages//
//Sitemap
function wpx_seo_sitemap() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}

	if (isset($_POST['sitemap_create']))
	{
		
	}
}

// Main Page Submit Sitemap //
function wpx_seo_manager() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	
	if (isset($_POST['sitemap_update']))
	{
		update_option('sitemap_URL', (string)$_POST['sitemap_URL']);
		$sitemap_URL1 = get_option('sitemap_URL');

		$show_sitemap = '';
		$last3 = substr($sitemap_URL1, -1, 3);
		$last5 = substr($sitemap_URL1, -1, 5);
		$check1 = "xml";
		$icon_url = get_bloginfo( 'wpurl' );

		if($sitemap_URL1 == "")
		{
			$show_sitemap .= '<div id="message" class="updated fade"><p>'."Oops!! Blank field. Please provide sitemap URL" . '<br /><br /> Sitemap must ends with .xml or .xml.gz';
			$show_sitemap .= '</p></div>';
		}

		else
		{
			$webmasterlink = array(

				'goo' => array (
					'webmaster_engine' => 'Google',
					'search_engine' => 'http://www.google.com/webmasters/sitemaps/ping?sitemap=',
					'OKmessage' => 'Sitemap Notification Received',
					'NOmessage' => 'Bad Request'
				),

				'bin' => array (
						'webmaster_engine' => 'Bing',
						'search_engine' => 'http://www.bing.com/webmaster/ping.aspx?siteMap=',
						'OKmessage' => 'Thanks for submitting your sitemap',
						'NOmessage' => 'Bad Request'
				),

				'ask' => array (
					'webmaster_engine' => 'Ask.com',
					'search_engine' => 'http://submissions.ask.com/ping?sitemap=',
					'OKmessage' => 'Your Sitemap submission was successful',
					'NOmessage' => 'Your Sitemap submission was not successful'
				),

			);

			$show_sitemap .= '<div id="message" class="updated fade"><p>';

			foreach ($webmasterlink as $siln => $myArray1 )
			{
				$webmaster_engine	= $myArray1['webmaster_engine'];
				$search_engine	= $myArray1['search_engine'];
				$OKmessage	= $myArray1['OKmessage'];
				$NOmessage	= $myArray1['NOmessage'];

				list ($source, $finalMessage) =  wpx_seo_manager_sitemap_submit($sitemap_URL1,$search_engine,$OKmessage,$NOmessage);

				$statusTag = substr($finalMessage,0,4);
				if ($statusTag == 'DONE') {
					$icon = '<img border="0" src="'.WPXSEO_PLUGIN_URL.'/yes.jpg" /> ';
					$alter_link = '<br />';
					}
				else if ($statusTag == 'NOPE') {
					$icon = '<img border="0" src="'.WPXSEO_PLUGIN_URL.'/fail.jpg" /> ';
					$submission_URL1 = $search_engine.$sitemap_URL1;
					$alter_link = '<a href="'.$submission_URL1.'" target="_blank"> (Try manually)</a><br /><br />';
					}
				else {
					$icon = '';
					$alter_link = '';
					}
				$finalMessage = substr($finalMessage,4);
				$insert_sitemap = "\n".$icon."<b>".$webmaster_engine.":  </b><i>".$finalMessage."</i><br />".$alter_link ;
				$show_sitemap .= $insert_sitemap;
			}
			$show_sitemap .= '</p></div>';
		}
	}
	$icon_url = get_bloginfo( 'wpurl' );
	?>
	<div class="wrap">
		<?php screen_icon(); ?>
        <h2>WPX SEO Manager Settings</h2>
        <br class="clear"/>
		
		<div class="postbox-container" style="width: 69%;	;float:left;">
			<div id="poststuff">
				<div id="wpx-seo-submit" class="postbox">
                    <h3 id="sitesubmission">Automatic sitemap submission</h3>
					
					<form method="post" mame="sitemap_update">
					<input type="hidden" name="sitemap_update" id="sitemap_update" value="true" />
                    
					<div class="inside">
                        <table class="form-table">
                            <tr valign="top" class="alternate">
                                <th scope="row" style="width:29%;"><label for="sitemap_URL">Please provide existing Sitemap URL</label></th>
                                <td>
                                    <input name="sitemap_URL" type="text" size="75" value="<?php echo get_option('sitemap_URL'); ?>" />
                                </td>
                            </tr>
                        </table>
                    </div>
				</div>
			</div>
			
			<div class="submit">
                <input type="submit" name="sitemap_update" class="button-primary" value="Submit to Google, Bing, Ask &raquo;" />
            </div>

            <?=$show_sitemap?>
            </form>
    
            <div id="poststuff">
                <div id="wpx-seo-tips" class="postbox">
                <h3 id="MoreInfo">More Information</h3>
    
                	<div class="inside">
                    	<table class="form-table">
                        	<tr>
                                <th scope="row">
                                    <label for="PluginSite">Plugin Site:</label>
                                </th>
                                <td id="PluginSite">
                                    <a href="http://wpxprt.com/" target="_blank">Official Site</a>.
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="PluginForums">Plugin Social:</label>
                                </th>
                                <td id="PluginForums">
                                    <a href="http://facebook.com/wpxprt/" target="_blank">Facebook Page</a>.
                                    <a href="http://twitter.com/wpxprt/" target="_blank">Twitter Page</a>
                                </td>
                            </tr>
                      </table>
                	</div>
            	</div>
        	</div>
    	</div>

		<div class="clear">
        <p>
            <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="EWM7CSB98D4M2">
            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
            <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
            </form>
        </p>
			<p><br/>&copy; Copyright 2012 - <?php echo date("Y"); ?> <a href="http://wpxprt.com">Wordpress Expert</a></p>
		</div>
	</div>
	<?php
}

// Webmaster Page //
function wpx_seo_webmaster() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	
	if (isset($_POST['webmaster_update']))
	{
		update_option('wpx_seo_google_webmaster', (string)$_POST["wpx_seo_google_webmaster"]);
		update_option('wpx_seo_alexa_webmaster', (string)$_POST["wpx_seo_alexa_webmaster"]);
		update_option('wpx_seo_bing_webmaster', (string)$_POST["wpx_seo_bing_webmaster"]);
		
		echo '<div id="message" class="updated fade"><p><strong>Settings updated.</strong></p></div>';
	}
	
	?>    
	<div class="wrap">
		<?php screen_icon(); ?>
        <h2>WPX SEO Manager Webmaster</h2>
        <br class="clear"/>
		
		<div class="postbox-container" style="width: 69%;;float:left;">
			<div id="poststuff">
				<div id="wpx-seo-verification" class="postbox">
					<h3 id="verification">Site Verification</h3>
                    <form name="webmaster_update" method="post">
                    <input type="hidden" name="webmaster_update" value="1" />
                        
                    <div class="inside">
                        <table class="form-table">
                            <tr valign="top" class="alternate">
                                <th scope="row" style="width:32%;"><label>Google WebMaster Central</label></th>
                                <td>
                                    <input name="wpx_seo_google_webmaster" type="text" size="55" value="<?php echo get_option('wpx_seo_google_webmaster'); ?>" />
                                </td>
                            </tr>
							
                            <tr valign="top">
                                <th scope="row" style="width:32%;"><label>Bing WebMaster Center</label></th>
                                <td>
                                    <input name="wpx_seo_bing_webmaster" type="text" size="55" value="<?php echo get_option('wpx_seo_bing_webmaster'); ?>" />
                                </td>
                            </tr>
							
                            <tr valign="top">
                                <th scope="row" style="width:32%;"><label>Alexa Code</label></th>
                                <td>
									<input name="wpx_seo_alexa_webmaster" type="text" size="55" value="<?php echo get_option('wpx_seo_alexa_webmaster'); ?>" />
                                </td>
                            </tr>                
                        </table>
                    </div>
                </div>
            </div>
			
			<div class="submit">
                <input type="submit" name="webmaster_update" class="button-primary" value="Update options &raquo;" />
            </div>
            </form>
			<div id="poststuff">
                <div id="wpx-seo-tips" class="postbox">
                <h3 id="MoreInfo">More Information</h3>
    
                	<div class="inside">
                    	<table class="form-table">
                        	<tr>
                                <th scope="row">
                                    <label for="PluginSite">Plugin Site:</label>
                                </th>
                                <td id="PluginSite">
                                    <a href="http://wpxprt.com/" target="_blank">Official Site</a>.
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="PluginForums">Plugin Social:</label>
                                </th>
                                <td id="PluginForums">
                                    <a href="http://facebook.com/wpxprt/" target="_blank">Facebook Page</a>.
                                    <a href="http://twitter.com/wpxprt/" target="_blank">Twitter Page</a>
                                </td>
                            </tr>
                      </table>
                	</div>
            	</div>
        	</div>
    	</div>
		<div class="clear">
        <p>
            <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="EWM7CSB98D4M2">
            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
            <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
            </form>
        </p>
			<p><br/>&copy; Copyright 2012 - <?php echo date("Y"); ?> <a href="http://wpxprt.com">Wordpress Expert</a></p>
		</div>
	</div>
	<?php
}

function wpx_seo_tracking() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	
	if (isset($_POST['tracking_update']))
	{		
		update_option('wpx_seo_google_analytics', (string)$_POST['wpx_seo_google_analytics']);
		update_option('wpx_seo_quantcast_analytics', (string)$_POST['wpx_seo_quantcast_analytics']);
		
		echo '<div id="message" class="updated fade"><p><strong>Settings updated.</strong></p></div>';
	}
	?>
	<div class="wrap">
		<?php screen_icon(); ?>
        <h2>WPX SEO Manager Tracking</h2>
        <br class="clear"/>
		
		<div class="postbox-container" style="width: 69%;;float:left;">
            <div id="poststuff">
                <div id="wpx-seo-tracking" class="postbox">
                    <h3 id="tracking">Site Tracking</h3>
					<form name="tracking_update" method="post">
                    <input type="hidden" name="tracking_update" value="1" />
                    <div class="inside">
                        <table class="form-table">
                            <tr valign="top" class="alternate">
                                <th scope="row" style="width:32%;"><label>Google Analytics</label></th>
                                <td>
                                    <input name="wpx_seo_google_analytics" type="text" size="55" value="<?php echo get_option('wpx_seo_google_analytics'); ?>" />
                                </td>
                            </tr>              
                            <tr valign="top">
                                <th scope="row" style="width:32%;"><label>Quantcast Analytics</label></th>
                                <td>
                                    <input name="wpx_seo_quantcast_analytics" type="text" size="55" value="<?php echo get_option('wpx_seo_quantcast_analytics'); ?>" />
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div> 
			
            <div class="submit">
                <input type="submit" name="tracking_update" class="button-primary" value="Update options &raquo;" />
            </div>
            </form>
			
            <div id="poststuff">
                <div id="wpx-seo-tips" class="postbox">
                <h3 id="MoreInfo">More Information</h3>
    
                	<div class="inside">
                    	<table class="form-table">
                        	<tr>
                                <th scope="row">
                                    <label for="PluginSite">Plugin Site:</label>
                                </th>
                                <td id="PluginSite">
                                    <a href="http://wpxprt.com/" target="_blank">Official Site</a>.
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="PluginForums">Plugin Social:</label>
                                </th>
                                <td id="PluginForums">
                                    <a href="http://facebook.com/wpxprt/" target="_blank">Facebook Page</a>.
                                    <a href="http://twitter.com/wpxprt/" target="_blank">Twitter Page</a>
                                </td>
                            </tr>
                      </table>
                	</div>
            	</div>
        	</div>
    	</div>
		<div class="clear">
        <p>
            <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="EWM7CSB98D4M2">
            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
            <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
            </form>
        </p>
			<p><br/>&copy; Copyright 2012 - <?php echo date("Y"); ?> <a href="http://wpxprt.com">Wordpress Expert</a></p>
		</div>
	</div>
	<?php
}

function wpx_seo_head()
{
	$google_wm = get_option('wpx_seo_google_webmaster');
	$alexa_wm = get_option('wpx_seo_alexa_webmaster');

	$bing_wm = get_option('wpx_seo_bing_webmaster');
	$google_an = get_option('wpx_seo_google_analytics');
	$quantcast_an = get_option('wpx_seo_quantcast_analytics');

	if (!($google_wm == ""))
	{
		$google_wm_meta = '<meta name="google-site-verification" content="' . $google_wm . '" /> ';
		echo "\n" . $google_wm_meta . "\n";
	}

	if (!($bing_wm == ""))
	{
		$bing_wm_meta = '<meta name="msvalidate.01" content="' . $bing_wm . '" />';
		echo $bing_wm_meta . "\n";
	}

	if (!($alexa_wm == ""))
	{
		$alexa_wm_meta = '<meta name="alexaVerifyID" content="' . $alexa_wm . '" />';
		echo $alexa_wm_meta . "\n";
	}

	if (!($google_an == ""))
	{
		echo "\n".'<script type="text/javascript">'."\n";
		echo 'var _gaq = _gaq || [];'."\n";
		echo '_gaq.push([\'_setAccount\', \'' . $google_an . '\']);'."\n";
		echo '_gaq.push([\'_trackPageview\']);'."\n";
		echo '_gaq.push([\'_trackPageLoadTime\']);'."\n";
		echo '(function() {'."\n";
		echo 'var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;'."\n";
		echo 'ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\';'."\n";
		echo 'var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ga, s);'."\n";
		echo ' })();'."\n";
		echo '</script>'."\n";
	}

	if (!($quantcast_an == ""))
	{
		echo '<script type="text/javascript">'."\n";
		echo '_qoptions={qacct:"' . $quantcast_an . '"};'."\n";
		echo '</script>'."\n";
		echo '<script type="text/javascript" src="http://edge.quantserve.com/quant.js"></script>'."\n";
	}
}

function wpx_seo_footer()
{

}

function wpx_seo_manager_sitemap_submit($sitemap_URL1,$search_engine,$OKmessage,$NOmessage)
{
	$DONE_MSG = 'DONE';
	$NOPE_MSG = 'NOPE';

	$pingurl = $search_engine.$sitemap_URL1;
	$source = @file_get_contents($pingurl);

	if ($source != false) {

		$source = strip_tags($source);
		$source = "WEBMASTER".$source;

		$isOKmessage = stripos($source,$OKmessage);
		$isNOmessage = stripos($source,$NOmessage);

		if (($isOKmessage != false)&&($isNOmessage == false))
		{
			$finalMessage = $DONE_MSG.$OKmessage;
		}
		if (($isOKmessage == false)&&($isNOmessage != false))
		{
			$finalMessage = $NOPE_MSG.$NOmessage;
		}
		if (($isOKmessage == false)&&($isNOmessage == false))
		{
			$finalMessage = $NOPE_MSG.'Submission error';
		}
	}
	else if ($source == false) {$finalMessage = $NOPE_MSG.'search_engine error';}
	return array($source, $finalMessage);
}

// do the work of this plugin!
add_action('wp_head', 'wpx_seo_head');
add_action('wp_footer', 'wpx_seo_footer');

?>