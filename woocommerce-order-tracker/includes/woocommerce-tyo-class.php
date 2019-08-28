<?php
/**
 * Exit if accessed directly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists( 'MWB_Track_Your_Order' ) )
{
	/**
	 * This is class for tracking order and other functionalities .
	 *
	 * @name    Mwb_Track_Your_Order
	 * @category Class
	 * @author   makewebbetter <webmaster@makewebbetter.com>
	 */
	
	class MWB_Track_Your_Order{

		/**
		 * This is construct of class
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		function __construct() 
		{
			add_filter( 'admin_enqueue_scripts', array($this, 'mwb_tyo_admin_scripts'));
			add_filter( 'woocommerce_my_account_my_orders_actions', array( $this,'mwb_tyo_add_track_order_button_on_orderpage' ), 10, 2 );
			add_filter( 'template_include', array($this, 'mwb_tyo_include_track_order_page'),10);
			add_filter( 'template_include', array($this, 'mwb_tyo_include_guest_track_order_page'),10);
			add_action( 'init', array($this, 'mwb_tyo_register_custom_order_status'));
			add_filter( 'wc_order_statuses', array($this, 'mwb_tyo_add_custom_order_status'));
			add_action('admin_menu',  array($this, 'mwb_tyo_tracking_order_meta_box'));
			add_action( 'save_post', array($this, 'mwb_tyo_save_delivery_date_meta'));
			add_action( 'wp_enqueue_scripts', array($this, 'mwb_tyo_scripts'));
			add_action( 'woocommerce_order_status_changed' , array( $this, 'mwb_tyo_track_order_status' ), 10 , 3 );
			add_action( 'woocommerce_order_details_after_order_table',array($this, 'mwb_tyo_track_order_button'));
			add_action('wp_ajax_mwb_mwb_create_custom_order_status',array($this,'mwb_mwb_create_custom_order_status'));
			add_action('wp_ajax_mwb_mwb_delete_custom_order_status',array($this,'mwb_mwb_delete_custom_order_status'));
		}

		/**
		 * This function is to include CSS and js
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		public function mwb_tyo_scripts()
		{
			$url = plugins_url();
			$ajax_nonce = wp_create_nonce( "mwb-tyo-ajax-seurity-string" );
			$upload_url = home_url();
			
			$user_id = get_current_user_id();
			
			$redirect_uri = $_SERVER['REQUEST_URI'];
			
			$track_page_id = get_option( 'mwb_tyo_tracking_page', array() );
			if( is_array($track_page_id) && !empty( $track_page_id ) )
			{
				$track_page_id = $track_page_id['pages']['mwb_track_order_page'] ;

				if ( $track_page_id == get_the_ID() ) 
				{
					wp_enqueue_style( 'mwb-tyo-style-front', MWB_TRACK_YOUR_ORDER_URL.'/assets/css/mwb-tyo-style-front.css' );
				}
			}
			wp_register_script('mwb-tyo-script', MWB_TRACK_YOUR_ORDER_URL.'assets/js/mwb-tyo-script.js', array('jquery'), MWB_TRACK_YOUR_ORDER_VERSION , true);
			$ajax_nonce = wp_create_nonce( "mwb-tyo-ajax-seurity-string" );
			$statuses = wc_get_order_statuses();
			$translation_array = array(
				'ajaxurl' => admin_url('admin-ajax.php'),
				'mwb_tyo_nonce'	=>	$ajax_nonce,
				'order_statuses'	=>	$statuses,
				);
			wp_localize_script( 'mwb-tyo-script', 'global_tyo', $translation_array );
			wp_enqueue_script( 'mwb-tyo-script' );
			
		}

		/**
		 * This function is add cs and js to order meta
		 * 
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 * @param unknown $order
		 */
		public function mwb_tyo_admin_scripts()
		{
			$screen = get_current_screen();

			if(isset($screen->id))
			{	
				if($screen->id == 'shop_order')
				{
					wp_enqueue_style( 'mwb-tyo-style-jqueru-ui', MWB_TRACK_YOUR_ORDER_URL.'assets/css/jquery-ui.css' );
					wp_enqueue_style( 'mwb-tyo-style-timepicker', MWB_TRACK_YOUR_ORDER_URL.'assets/css/jquery.ui.timepicker.css' );
					wp_enqueue_script( 'mwb-tyo-script-timepicker', MWB_TRACK_YOUR_ORDER_URL.'assets/js/jquery.ui.timepicker.js', array('jquery'), MWB_TRACK_YOUR_ORDER_VERSION, true );
					wp_enqueue_script( 'jquery-ui-datepicker' );
				}
			}
			wp_register_script('mwb-tyo-script-admin', MWB_TRACK_YOUR_ORDER_URL.'assets/js/mwb-tyo-admin-script.js', array('jquery'), MWB_TRACK_YOUR_ORDER_VERSION);
			if( strpos($_SERVER['REQUEST_URI'], '&tab=mwb_tyo_settings') > 0 )
			{
				wp_enqueue_script( 'select2' );
				wp_enqueue_style( 'select2' );
			}
			$ajax_nonce = wp_create_nonce( "mwb-tyo-ajax-seurity-string" );
			$statuses = wc_get_order_statuses();
			$order_status = array( 'wc-dispatched'=>__('Order Dispatched',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN), 'wc-packed'=>__('Order Packed',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN) , 'wc-shipped'=>__('Order Shipped',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN) );
			$custom_order_status=get_option('mwb_tyo_new_custom_order_status',array());
			if(is_array($custom_order_status) && !empty($custom_order_status)){
				foreach ($custom_order_status as $key => $value) {
					$order_status['wc-'.$value['name']] = $value['name'];
				}
			}
			foreach($order_status as $key => $val)
			{
				$statuses[$key] = $val;
			}
			$translation_array = array(
				'ajaxurl' => admin_url('admin-ajax.php'),
				'mwb_tyo_nonce'	=>	$ajax_nonce,
				'order_statuses'	=>	$statuses,
				'message_success'  => __( 'Order Status successfully saved.', 'woocommerce-order-tracker'  ),
				'message_invalid_input'  => __( 'Please enter a Valid Status Name.', 'woocommerce-order-tracker'  ),
				'message_error_save'  => __( 'Unable to save Order Status.', 'woocommerce-order-tracker'  ),
				'message_empty_data'  => __( 'Please enter the status name .', 'woocommerce-order-tracker'  ),
				);
			wp_enqueue_style( 'mwb-tyo-style', MWB_TRACK_YOUR_ORDER_URL.'/assets/css/mwb-tyo-style.css' );
			wp_localize_script( 'mwb-tyo-script-admin', 'global_tyo', $translation_array );
			wp_enqueue_script( 'mwb-tyo-script-admin' );
		}

		/**
		 * This function adds a Custom order status on the backend
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 * @param unknown $template
		 */
		public function mwb_mwb_create_custom_order_status()
		{
			$create_custom_order_status = array();
			$value = array();
			$value=get_option('mwb_tyo_new_custom_order_status',false);
			if(is_array($value) && !empty($value))
			{
				$create_custom_order_status=$_POST['mwb_mwb_new_role_name'];
				$value[] = array( 'name'=> $create_custom_order_status );
				update_option('mwb_tyo_new_custom_order_status',$value);

			}else{
				$create_custom_order_status[] = array( 'name' => $_POST['mwb_mwb_new_role_name'] );
				update_option('mwb_tyo_new_custom_order_status',$create_custom_order_status);
			}
			
			echo "success";
			wp_die();
		}

		/**
		 * This function delete the Custom order status on the backend
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 * @param unknown $template
		 */
		public function mwb_mwb_delete_custom_order_status()
		{
			$mwb_custom_action =$_POST['mwb_custom_action'];
			$mwb_custom_key =$_POST['mwb_custom_key'];
			if(isset($mwb_custom_key) && !empty($mwb_custom_key)){
				$custom_order_status_exist = get_option( 'mwb_tyo_new_custom_order_status', false );
				if( is_array($custom_order_status_exist) && !empty($custom_order_status_exist) ) {
					foreach($custom_order_status_exist as $key => $value){
						if($value['name']===$mwb_custom_key)
						{
							unset($custom_order_status_exist[$key]);

						}
					}
					update_option( 'mwb_tyo_new_custom_order_status', $custom_order_status_exist );
					echo "success";
				}
				else
				{
					echo "failed";
				}
				
				wp_die();
			}
		}
		


		/**
		 * This function adds a track order button on order page on the frontend
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 * @param unknown $template
		 * @return string
		 */
		public function mwb_tyo_add_track_order_button_on_orderpage( $actions, $order )
		{
			$mwb_tyo_enable_track_order_feature = get_option( 'mwb_tyo_enable_track_order_feature', 'no' );
			if ($mwb_tyo_enable_track_order_feature != 'yes') {
				return $actions;
			}
			$mwb_tyo_pages= get_option('mwb_tyo_tracking_page');
			$page_id = $mwb_tyo_pages['pages']['mwb_track_order_page'];
			if(WC()->version < "3.0.0")
			{
				$order_id=$order->id;
				$track_order_url = get_permalink($page_id);
				$actions['track_order']['url'] 	= $track_order_url."/".$order_id;
				$actions['track_order']['name'] 	= __( 'Track Order', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN );
			}
			else
			{
				$order_id=$order->get_id();
				$track_order_url = get_permalink($page_id);
				$actions['track_order']['url'] 	= $track_order_url.$order_id;
				$actions['track_order']['name'] 	= __( 'Track Order', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN );
			}			
			return $actions;
		}

		/**
		 * This function is to create template for track order
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 * @param unknown $template
		 * @return string
		 */
		public function mwb_tyo_include_track_order_page($template)
		{
			$mwb_tyo_enable_track_order_feature = get_option( 'mwb_tyo_enable_track_order_feature', 'no' );
			if ($mwb_tyo_enable_track_order_feature != 'yes') {
				return $template;
			}
			$mwb_tyo_pages= get_option('mwb_tyo_tracking_page');
			$page_id = $mwb_tyo_pages['pages']['mwb_track_order_page'];
			if(is_page($page_id))
			{
				$new_template = MWB_TRACK_YOUR_ORDER_PATH. 'template/mwb-track-order-myaccount-page.php';
				$template =  $new_template;
			}

			return $template;
		}

		/**
		 * This function is to create template for track order
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 * @param unknown $template
		 * @return string
		 */
		public function mwb_tyo_include_guest_track_order_page($template)
		{
			$mwb_tyo_enable_track_order_feature = get_option( 'mwb_tyo_enable_track_order_feature', 'no' );
			if ($mwb_tyo_enable_track_order_feature != 'yes') {
				return $template;
			}
			$mwb_tyo_pages= get_option('mwb_tyo_tracking_page');
			$page_id = $mwb_tyo_pages['pages']['mwb_guest_track_order_page'];
			if(is_page($page_id))
			{
				$new_template = MWB_TRACK_YOUR_ORDER_PATH. 'template/mwb-guest-track-order-page.php';
				$template =  $new_template;
			}

			return $template;
		}
		

		/**
		 * This function is to add custom order status for return and exchange
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		public function mwb_tyo_register_custom_order_status()
		{
			$mwb_tyo_enable_track_order_feature = get_option( 'mwb_tyo_enable_track_order_feature', 'no' );
			$mwb_tyo_enable_custom_order_feature = get_option( 'mwb_tyo_enable_custom_order_feature', 'no' );
			if ($mwb_tyo_enable_track_order_feature != 'yes' || $mwb_tyo_enable_custom_order_feature !='yes') {
				return ;
			}
			
			register_post_status( 'wc-packed', array(
				'label'                     => __('Order Packed',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( __('Order Packed', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ).'<span class="count">(%s)</span>', __('Order Packed', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN).'<span class="count">(%s)</span>' )
				) );

			register_post_status( 'wc-dispatched', array(
				'label'                     => __('Order Dispatched',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( __('Order Dispatched', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN).' <span class="count">(%s)</span>', __('Order Dispatched', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN).' <span class="count">(%s)</span>' )
				) );

			register_post_status( 'wc-shipped', array(
				'label'                     => __('Order Shipped',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( __('Order Shipped', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN).' <span class="count">(%s)</span>', __('Order Shipped', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN).' <span class="count">(%s)</span>' )
				) );
			
		}

		/**
		 * This function is to register custom order status
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 * @param unknown $order_statuses
		 * @return multitype:string unknown
		 */
		public function mwb_tyo_add_custom_order_status($order_statuses)
		{
			$mwb_tyo_enable_track_order_feature = get_option( 'mwb_tyo_enable_track_order_feature', 'no' );
			$mwb_tyo_enable_custom_order_feature = get_option( 'mwb_tyo_enable_custom_order_feature', 'no' );
			if ($mwb_tyo_enable_track_order_feature != 'yes' || $mwb_tyo_enable_custom_order_feature !='yes') {
				return $order_statuses ;
			}
			$statuses = get_option( 'mwb_tyo_new_custom_statuses_for_order_tracking' , array() );
			if (is_array($statuses) && !empty( $statuses )) 
			{
				foreach ($statuses as $key => $value) 
				{
					$order_statuses[$value] = str_replace('wc-', '', $value);
				}
			}
			return $order_statuses;
		}
		/**
		 * This function is add Meta box for adding estimated date of delivery
		 * 
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		public function mwb_tyo_tracking_order_meta_box()
		{
			$mwb_tyo_enable_track_order_feature = get_option( 'mwb_tyo_enable_track_order_feature', 'no' );
			if ($mwb_tyo_enable_track_order_feature != 'yes') {
				return ;
			}
			add_meta_box('mwb_tyo_track_order', __('Enter Estimated Delivery Date',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN), array($this, 'mwb_tyo_track_order_metabox'), 'shop_order', 'side');
		}

		/**
		 * This function is for estimated delivery date html 
		 * 
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 * @param unknown $order
		 */
		public function mwb_tyo_track_order_metabox()
		{
			global $post, $thepostid, $theorder;
			$mwb_tyo_enable_track_order_feature = get_option( 'mwb_tyo_enable_track_order_feature', 'no' );
			if ($mwb_tyo_enable_track_order_feature != 'yes') {
				return ;
			}
			if(WC()->version < "3.0.0")
			{
				$order_id = $theorder->id;
			}
			else
			{
				$order_id = $theorder->get_id();
			}
			$expected_delivery_date = get_post_meta( $order_id, 'mwb_tyo_estimated_delivery_date', true );
			$expected_delivery_time = get_post_meta( $order_id, 'mwb_tyo_estimated_delivery_time', true );

			?>
			<div class="mwb_tyo_estimated_delivery_datails_wrapper">
				<label for="mwb_tyo_est_delivery_date"><?php _e( 'Delivery Date',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN  ) ?></label>
				<input type="text" class="mwb_tyo_est_delivery_date" id="mwb_tyo_est_delivery_date" name="mwb_tyo_est_delivery_date" value="<?php echo $expected_delivery_date; ?>" placeholder="<?php _e( 'Enter Delivery Date', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ); ?>"></input>
				<label for="mwb_tyo_est_delivery_time"><?php _e( 'Delivery Time',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN  ) ?></label>
				<input type="text" class="mwb_tyo_est_delivery_time" name="mwb_tyo_est_delivery_time" id="mwb_tyo_est_delivery_time" value="<?php echo $expected_delivery_time; ?>" placeholder="<?php _e( 'Enter Delivery time', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ); ?>"></input>
			</div>
			<?php
		}


		public function mwb_tyo_save_delivery_date_meta()
		{
			global $post;
			if(isset($post->ID))
			{	
				$mwb_track_order_status = array();
				$post_id = $post->ID;
				if ( isset( $_POST['mwb_tyo_est_delivery_date'] ) && $_POST['mwb_tyo_est_delivery_date'] != '' ) 
				{
					update_post_meta( $post_id, 'mwb_tyo_estimated_delivery_date', $_POST['mwb_tyo_est_delivery_date'] );
				}
				else 
				{
					update_post_meta($post_id, 'mwb_tyo_estimated_delivery_date', false);
				}

				if ( isset( $_POST['mwb_tyo_est_delivery_time'] ) && $_POST['mwb_tyo_est_delivery_time'] != '' ) 
				{
					update_post_meta( $post_id, 'mwb_tyo_estimated_delivery_time', $_POST['mwb_tyo_est_delivery_time'] );
				}
				else 
				{
					update_post_meta($post_id, 'mwb_tyo_estimated_delivery_time', false);
				}

			}
		}

		public function mwb_tyo_track_order_status( $order_id , $old_status, $new_status )
		{
			$old_status = 'wc-'.$old_status;
			$new_status = 'wc-'.$new_status;
			$order = new WC_Order( $order_id );
			$mwb_track_order_status = get_post_meta( $order_id, 'mwb_track_order_status',true );
			if ( is_array($mwb_track_order_status) && !empty($mwb_track_order_status) ) {
				$c = count($mwb_track_order_status);
				if ($mwb_track_order_status[$c-1] == $old_status) {
					if (in_array( $new_status , $mwb_track_order_status )) {
						$key = array_search ( $new_status, $mwb_track_order_status );
						unset( $mwb_track_order_status[$key] );
						$mwb_track_order_status = array_values($mwb_track_order_status);
					}
					$mwb_track_order_status[] = $new_status;
					update_post_meta( $order_id, 'mwb_track_order_status', $mwb_track_order_status );
				}else{

					$mwb_track_order_status[] = $old_status;
					$mwb_track_order_status[] = $new_status;
					update_post_meta( $order_id, 'mwb_track_order_status', $mwb_track_order_status );
				}
			}
			else{
				$mwb_track_order_status[] = $old_status;
				$mwb_track_order_status[] = $new_status;
				update_post_meta( $order_id, 'mwb_track_order_status', $mwb_track_order_status );
			}
		}

		public function mwb_tyo_track_order_button( $order )
		{
			if(WC()->version < "3.0.0")
			{
				$order_id = $order->id;
			}
			else
			{
				$order_id = $order->get_id();
			}
			$mwb_tyo_enable_track_order_feature = get_option( 'mwb_tyo_enable_track_order_feature', 'no' );
			if ($mwb_tyo_enable_track_order_feature != 'yes') {
				return ;
			}
			$mwb_tyo_pages= get_option('mwb_tyo_tracking_page');
			$page_id = $mwb_tyo_pages['pages']['mwb_track_order_page'];
			$track_order_url = get_permalink($page_id);
			?>
			<a href="<?php echo $track_order_url.'/'.$order_id ?>" class="button button-primary"><?php _e( 'TRACK ORDER', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ); ?></a>
			<?php
		}
		
	}
	new MWB_Track_Your_Order();
}
