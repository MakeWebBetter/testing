<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Plugin class for managing admin interfaces.
 *
 * @class    MWB_TYO_Admin_Settings
 *
 * @version  1.0.0
 * @package  track-your-order/admin
 * @category Class
 * @author   makewebbetter <webmaster@makewebbetter.com>
 */

if( !class_exists( 'MWB_TYO_Admin_Settings' ) ){

	class MWB_TYO_Admin_Settings{

		/**
		 * This is construct of class
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		
		public function __construct(){
			$this->id = 'mwb_tyo_settings';
			add_filter( 'woocommerce_settings_tabs_array', array($this,'mwb_tyo_add_settings_tab'), 50 );
			add_action( 'woocommerce_settings_tabs_' . $this->id, array($this,'mwb_tyo_settings_tab') );
			add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
			add_action( 'woocommerce_sections_' . $this->id, array( $this, 'mwb_tyo_output_sections' ) );
		}

		/**
		 * Add new tab to woocommerce setting
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		public static function mwb_tyo_add_settings_tab( $settings_tabs ) {
			$settings_tabs['mwb_tyo_settings'] = __( 'Track Your Order', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN );
			return $settings_tabs;
		}
		
		/**
		 * Save section setting 
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		function mwb_tyo_settings_tab() {
			global $current_section;
			woocommerce_admin_fields( self::mwb_tyo_get_settings($current_section) );
		}

		/**
		 * Output of section setting 
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		public function mwb_tyo_output_sections() {

			global $current_section;
			$sections = $this->mwb_tyo_get_sections();
			if ( empty( $sections ) || 1 === sizeof( $sections ) ) {
				return;
			}

			echo '<ul class="subsubsub">';

			$array_keys = array_keys( $sections );

			foreach ( $sections as $id => $label ) {
				echo '<li><a href="' . admin_url( 'admin.php?page=wc-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
			}
			echo '</ul><br class="clear" />';
		}

		/**
		 * Create section setting 
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		public function mwb_tyo_get_sections() {

			$sections = array(
				''             	=>  __( 'Track Order', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ),
				'custom_status'		=>  __('Build Custom Order Status',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN),
				'other'     	=>  __( 'Common Setting', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ),
				);

			return apply_filters( 'mwb_tyo_get_sections' . $this->id, $sections );
		}

		/**
		 * Section setting
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		function mwb_tyo_get_settings($current_section) {
			$custom_order_status=get_option('mwb_tyo_new_custom_order_status',array());
			$order_status = array( 'wc-dispatched'=>__('Order Dispatched',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN), 'wc-packed'=>__('Order Packed',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN) , 'wc-shipped'=>__('Order Shipped',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN) );
			if(is_array($custom_order_status) && !empty($custom_order_status)){
				foreach ($custom_order_status as $key => $value) {
					$order_status['wc-'.$value['name']] = $value['name'];
				}
			}
			$statuses = wc_get_order_statuses();
			if(isset($statuses['wc-cancelled']))
			{
				unset($statuses['wc-cancelled']);
			}
			if ( 'other' == $current_section ) 
			{
				$settings = array(
					array(
						'title' => __( 'Basic Settings', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ),
						'type' 	=> 'title',
						),
					array(
						'title'         => __( 'Main Wrapper Class of Theme', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ),
						'desc'          => __( 'Write the main wrapper class of your theme if some design issue arises.', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN),
						'type'          => 'text',
						'id' 		=> 'mwb_tyo_track_order_class'
						),
					array(
						'title'         => __( 'Child Wrapper Class of Theme', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ),
						'desc'          => __( 'Write the child wrapper class of your theme if some design issue arises.',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN),
						'type'          => 'text',
						'id' 		=> 'mwb_tyo_track_order_child_class'
						),
					array(
						'title'         => __( 'Tracking Order Page Custom CSS', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ),
						'desc'          => __( 'Write the custom css for Tracking Order page.', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN),
						'type'          => 'textarea',
						'id' 		=> 'mwb_tyo_tracking_order_custom_css'
						),
					);
				foreach ($statuses as $key => $value) {
					$key = str_replace('-', '_', $key);
					$text_arr = array( 
						'title'         => __( 'Text for ', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN).$value.__(' status on tracking page', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ),
						'desc'          => __( 'Write the text for ',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN).$value.__(' to be shown on frontend during order tracking.',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN),
						'type'          => 'text',
						'id' 		=> 'mwb_tyo_'.$key.'_text'
						);
					$settings[] = $text_arr; 
				}
				$settings[] = array(
					'type' 	=> 'sectionend',
					);

				return apply_filters( 'mwb_tyo_get_common_settings' . $this->id, $settings );
			}
			else if ('custom_status' == $current_section ){
				include_once MWB_TRACK_YOUR_ORDER_PATH.'admin/custom-order-status.php';
				$settings = array();
				return apply_filters( 'mwb_tyo_get_common_settings' . $this->id, $settings );
			}
			else{
				$total_hidden_status = $statuses;
				foreach($order_status as $key => $val)
				{
					$total_hidden_status[$key] = $val;
				}
				$settings = array(
					array(
						'title' => __( 'Track Your Order', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ),
						'type' 	=> 'title',
						),
					array(
						'title'         => __( 'Enable', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ),
						'desc'          => __( 'Enable Track Your Order Feature', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ),
						'default'       => 'no',
						'type'          => 'checkbox',
						'id' 		=> 'mwb_tyo_enable_track_order_feature'
						),
					array(
						'title'         => __( 'Enable', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ),
						'desc'          => __( 'Enable use of Custom Order Status', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ),
						'default'       => 'no',
						'type'          => 'checkbox',
						'id' 		=> 'mwb_tyo_enable_custom_order_feature'
						),
					array(
						'title'    => __( 'Custom Order Statuses', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ),
						'desc'     => __( 'Select new Order Status to be created for enhanced order tracking', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ),
						'class'    => 'wc-enhanced-select',
						'css'      => 'min-width:300px;',
						'default'  => '',
						'type'     => 'multiselect',
						'options'  => $order_status,
						'desc_tip' =>  true,
						'id' 		=> 'mwb_tyo_new_custom_statuses_for_order_tracking'
						),
					array(
						'title'    => __( 'hidden_status', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ),
						'desc'     => __( 'Select Order Status to be shown in the Approval section while order tracking', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ),
						'class'    => 'wc-enhanced-select',
						'css'      => 'min-width:300px;',
						'default'  => '',
						'type'     => 'multiselect',
						'options'  => $total_hidden_status,
						'desc_tip' =>  true,
						'id' 		=> 'mwb_tyo_order_status_in_hidden'
						),
					array(
						'title'    => __( 'Approval', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ),
						'desc'     => __( 'Select Order Status to be shown in the Approval section while order tracking', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ),
						'class'    => 'wc-enhanced-select',
						'css'      => 'min-width:300px;',
						'default'  => '',
						'type'     => 'multiselect',
						'options'  => $statuses,
						'desc_tip' =>  true,
						'id' 		=> 'mwb_tyo_order_status_in_approval'
						),
					array(
						'title'    => __( 'Processing', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ),
						'desc'     => __( 'Select Order Status to be shown in the Processing section while order tracking', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ),
						'class'    => 'wc-enhanced-select',
						'css'      => 'min-width:300px;',
						'default'  => '',
						'type'     => 'multiselect',
						'options'  => $statuses,
						'desc_tip' =>  true,
						'id' 		=> 'mwb_tyo_order_status_in_processing'
						),
					array(
						'title'    => __( 'Shipping', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ),
						'desc'     => __( 'Select Order Status to be shown in the Shipping section while order tracking', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ),
						'class'    => 'wc-enhanced-select',
						'css'      => 'min-width:300px;',
						'default'  => '',
						'type'     => 'multiselect',
						'options'  => $statuses,
						'desc_tip' =>  true,
						'id' 		=> 'mwb_tyo_order_status_in_shipping'
						),
					array(
						'type' 	=> 'sectionend',
						),
					);
				return apply_filters( 'mwb_tyo_get_track_order_settings' . $this->id, $settings );
			}
		}

		 /**
	     * Save setting
	     * @author makewebbetter<webmaster@makewebbetter.com>
	     * @link http://www.makewebbetter.com/
	     */
		 public function save() {
		 	global $current_section;
		 	$settings = $this->mwb_tyo_get_settings( $current_section );
		 	WC_Admin_Settings::save_fields( $settings );
		 }
		}
		new MWB_TYO_Admin_Settings();
	}