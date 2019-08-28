<?php
/**
 * Plugin Name:  Woocommerce Order Tracker
 * Plugin URI: http://makewebbetter.com
 * Description: Woocommerce Order Tracker provides you the best way to track your orders.
 * Version: 1.0.0
 * Author: makewebbetter <webmaster@makewebbetter.com>
 * Author URI: http://makewebbetter.com
 * Requires at least: 3.5
 * Tested up to: 4.7.2
 * Text Domain: woocommerce-order-tracker
 * Domain Path: /languages
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

/**
 * Exit if accessed directly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$activated = true;
if (function_exists('is_multisite') && is_multisite())
{
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) )
	{
		$activated = false;
	}
}
else
{
	if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))))
	{
		$activated = false;
	}
}

/**
 * Check if WooCommerce is active
 **/
if ($activated)
{
	define('MWB_TRACK_YOUR_ORDER_PATH', plugin_dir_path( __FILE__ ));
	define('MWB_TRACK_YOUR_ORDER_URL', plugin_dir_url( __FILE__ ));
	define('MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN', 'woocommerce-order-tracker');
	define('MWB_TRACK_YOUR_ORDER_VERSION', '1.0.0');

	include_once MWB_TRACK_YOUR_ORDER_PATH.'includes/woocommerce-tyo-class.php';
	include_once MWB_TRACK_YOUR_ORDER_PATH.'admin/class-admin-setting.php';
	

	/**
	 * This function is used for formatting the price
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 * @param unknown $price
	 * @return string
	 */
	
	function mwb_tyo_format_price($price)
	{
		$price = number_format((float)$price, 2, '.', '');
		$currency_symbol = get_woocommerce_currency_symbol();
		$currency_pos = get_option( 'woocommerce_currency_pos' );
		switch ( $currency_pos ) {
			case 'left' :
				$uprice = $currency_symbol.'<span class="mwb_rnx_formatted_price">'.$price.'</span>';
				break;
			case 'right' :
				$uprice = '<span class="mwb_rnx_formatted_price">'.$price.'</span>'.$currency_symbol;
				break;
			case 'left_space' :
				$uprice = $currency_symbol.'&nbsp;<span class="mwb_rnx_formatted_price">'.$price.'</span>';
				break;
			case 'right_space' :
				$uprice = '<span class="mwb_rnx_formatted_price">'.$price.'</span>&nbsp;'.$currency_symbol;
				break;
		}
		
		return $uprice;
	}

	/**
	 * This function is to add track order page
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	function mwb_tyo_add_pages()
	{
		$email = get_option('admin_email', false);
		$admin = get_user_by('email', $email);
		$admin_id = $admin->ID;
		 
		$mwb_tyo_tracking = array(
				'post_author'    => $admin_id,
				'post_name'      => 'track-your-order',
				'post_title'     => __('Track Order', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN),
				'post_type'      => 'page',
				'post_status'    => 'publish',
					
		);
			
		$page_id = wp_insert_post($mwb_tyo_tracking);
			
		if($page_id) {
			$mwb_tyo_pages['pages']['mwb_track_order_page']=$page_id;
		}

		$mwb_tyo_guest_request_form = array(
				'post_author'    => $admin_id,
				'post_name'      => 'guest-track-order-form',
				'post_title'     => __('Track Your Order',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN),
				'post_type'      => 'page',
				'post_status'    => 'publish',
		
		);
		
		$page_id = wp_insert_post($mwb_tyo_guest_request_form);
		
		if($page_id) {
			$mwb_tyo_pages['pages']['mwb_guest_track_order_page']=$page_id;
		}
		
		update_option('mwb_tyo_tracking_page', $mwb_tyo_pages);
	}

	register_activation_hook( __FILE__, 'mwb_tyo_add_pages');

	/**
	 * This function is to remove track order page
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	function mwb_tyo_remove_pages()
	{
		$mwb_tyo_pages =  get_option('mwb_tyo_tracking_page');
		$page_id = $mwb_tyo_pages['pages']['mwb_track_order_page'];
		wp_delete_post($page_id);
		$page_id = $mwb_tyo_pages['pages']['mwb_guest_track_order_page'];
		wp_delete_post($page_id);
		delete_option('mwb_tyo_tracking_page');
	}

	register_deactivation_hook(__FILE__, 'mwb_tyo_remove_pages');

	/**
	 * This function checks session is set or not
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	function mwb_tyo_set_session()
	{
		if( !session_id() )
		{
			session_start();
		}
		if(isset($_POST['mwb_tyo_order_id_submit']))
		{
			$order_id = $_POST['order_id'];
			$billing_email = get_post_meta($order_id, '_billing_email', true);
			$req_email = $_POST['order_email'];
			if($req_email == $billing_email)
			{
				$_SESSION['mwb_tyo_email'] = $billing_email;
				$order = new WC_Order($order_id);
				$url = home_url().'/track-your-order/'.$order_id;
				wp_redirect($url);
				die;
			}
			else
			{
				$_SESSION['mwb_tyo_notification'] = __('OrderId or Email is Invalid', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN );
			}
		}
	}
	add_action('init', 'mwb_tyo_set_session');

	/**
	 * This function is used to load language'.
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	
	function mwb_tyo_load_plugin_textdomain()
	{
		$domain = "woocommerce-track-your-order";
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
		load_textdomain( $domain, MWB_TRACK_YOUR_ORDER_PATH .'languages/'.$domain.'-' . $locale . '.mo' );
		$var=load_plugin_textdomain( $domain, false, plugin_basename( dirname(__FILE__) ) . '/languages' );
	}
	add_action('plugins_loaded', 'mwb_tyo_load_plugin_textdomain');

	/**
	 * Add settings link on plugin page
	 * @name mwb_tyo_admin_settings()
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	
	function mwb_tyo_admin_settings($actions, $plugin_file) {
		static $plugin;
		if (! isset ( $plugin )) {
	
			$plugin = plugin_basename ( __FILE__ );
		}
		if ($plugin == $plugin_file) {
			$settings = array (
					'settings' => '<a href="' . home_url ( '/wp-admin/admin.php?page=wc-settings&tab=mwb_tyo_settings' ) . '">' . __ ( 'Settings', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ) . '</a>',
			);
			$actions = array_merge ( $settings, $actions );
		}
		return $actions;
	}
	
	//add link for settings
	add_filter ( 'plugin_action_links','mwb_tyo_admin_settings', 10, 5 );
}
else
{
	/**
	 * Show warning message if woocommerce is not install
	 * @name mwb_tyo_plugin_deactivate()
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	
	function mwb_tyo_plugin_deactivation()
 	{ ?>
 		 <div class="error notice is-dismissible">
 			<p><?php _e( 'Woocommerce is not activated, Please activate Woocommerce first to install Track Your Order.', 'woocommerce-order-tracker' ); ?></p>
   		</div>
   		
   	<?php 
 	} 
 	add_action( 'admin_init', 'mwb_tyo_plugin_deactivation' );  
 
 	
 	/**
 	 * Call Admin notices
 	 * @name mwb_tyo_plugin_deactivate()
 	 * @author makewebbetter<webmaster@makewebbetter.com>
 	 * @link http://www.makewebbetter.com/
 	 */
 	
  	function mwb_tyo_plugin_deactivate()
	{
	   deactivate_plugins( plugin_basename( __FILE__ ) );do_action( 'woocommerce_product_options_stock_fields' );
	   add_action( 'admin_notices', 'mwb_tyo_plugin_deactivate' );
	}
}
