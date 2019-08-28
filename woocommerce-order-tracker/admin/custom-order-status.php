<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

}
// die("saasda");
class MWB_MWB_Custom_Order_Status extends WP_List_Table {

	/** Class constructor */
	public function __construct() {
		parent::__construct( [
			'singular' => __( 'Custom Order Creation', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ), //singular name of the listed records
			'plural'   => __( 'Custom Order Creation', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ), //plural name of the listed records
			'ajax'     => false //does this table support ajax?
			] );
	}

	/**
	 * Retrieve feeds 
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public function get_feeds() {

		$customOrderStatus = array();
		$previousStatus = get_option( 'mwb_tyo_new_custom_order_status', false );
		if( is_array($previousStatus) && !empty($previousStatus) ) {
			$customOrderStatus = $previousStatus;
		}
		return $customOrderStatus;
	}

	public function get_count( ) {

		$customOrderStatus = array();
		$previousStatus = get_option( 'mwb_tyo_new_custom_order_status', false );
		if( is_array($previousStatus) && !empty($previousStatus) ) {
			$customOrderStatus = $previousStatus;
		}
		return count($customOrderStatus);
	}


	
	/** Text displayed when no customer data is available */
	public function no_items() {
		_e( 'No any Custom Order Created.', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN );
	}


	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
			}
		}


	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="mwb_mwb_custom_order[]" value="%s" />', $item['name']
			);
	}


	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_name( $item ) {
		$title = '<strong>' . $item['name'] . '</strong>';
		$actions = [
		'delete' => sprintf( '<a href="javascript:void(0);" data-action="%s" data-key="%s" class="mwb_delete_costom_order">'.__("Delete",MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN).'</a>','delete', $item['name'] )
		];
		return $title . $this->row_actions( $actions );
	}


	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = [
		'cb'      => '<input type="checkbox" />',
		'name'    => __( 'Custom Order Name', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ),
		];
		return $columns;
	}


	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return $sortable_columns = array();
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [
		'bulk-delete' => __('Delete',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN)
		];
		return $actions;
	}


	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {


		// $data = get_option( 'mwb_tyo_new_custom_order_status', array() );
// 
		// global $wpdb;

		$per_page = apply_filters( 'mwb_mwb_alter_custom_order_status_per_page', 10 );
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		// Column headers
		$this->_column_headers = array( $columns, $hidden, $sortable );

		
		$current_page = $this->get_pagenum();
		if ( 1 < $current_page ) {
			$offset = $per_page * ( $current_page - 1 );
		} else {
			$offset = 0;
		}

		if(!$this->current_action()) 
		{			
			if( is_array($_POST) && !empty( $_POST ) )
			{
				$redirectURL = get_admin_url()."admin.php?page=wc-settings&tab=mwb_tyo_settings&section=custom_status";
				wp_redirect($redirectURL);
			}
			$this->items = self::get_feeds();
			$this->renderHTML();
		}
		else {
			$this->process_bulk_action();
		}
		
	}

	public function renderHTML() {
		?>
		<div class="mwb_mwb_rows_wrap">
			<h3><?php _e('Create Custom Order Status',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN);?></h3>
			<input id="mwb_mwb_create_role_box" value="<?php _e('Create Custom Order Status',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN);?>" class="button-primary" type="button">
		</div>
		<!-- messages :: start -->
		<div class="mwb_notices_order_tracker">
			
		</div>
		<!-- messages :: end -->
		<div id="mwb_mwb_create_box">
			<h3 align="center"><?php _e( 'Create New Custom Order Status', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN);?></h3>
			<table class="wp-list-table widefat fixed striped">
				<tr>
					<th>
						<label for="mwb__new_role_name"><?php _e( 'Custom Order Status Name', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN);?><label>
						</th>
						<td>
							<input type="text" name="mwb_mwb_create_order_name" pattern = '[A-Za-z0-9]' id="mwb_mwb_create_order_name" placeholder="<?php _e( 'Type Custom Order Status Name Here', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN);?>">	
						</td>
					</tr>
				</table>
				<p class="save_section">
					<input type="button" id="mwb_mwb_create_custom_order_status" value="<?php _e( 'Create Order Status', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN);?>" class="button-primary">	
					<img id="mwb_mwb_send_loading" src="<?php echo MWB_TRACK_YOUR_ORDER_URL.'assets/images/clock-loading.gif';?>">
				</p>
			</div>
			<?php
			$this->display();
		}

		public function process_bulk_action() {
			if(!session_id()) {
				session_start();
			}
			if( 'bulk-delete' === $this->current_action() ) {
				$mwb_data=isset( $_POST['mwb_mwb_custom_order'] ) ? $_POST['mwb_mwb_custom_order'] : array() ;
				$mwb_data_exist_db=get_option('mwb_tyo_new_custom_order_status',array());
				if(is_array( $mwb_data ) && !empty( $mwb_data )) {

					if( is_array($mwb_data_exist_db) && !empty($mwb_data_exist_db) ) {
						foreach ($mwb_data_exist_db as $key1 => $value1) {
							foreach ($mwb_data as $key2 => $value2) {
								if( $value1['name'] == $value2 )
								{
									unset($mwb_data_exist_db[$key1]);
								}
							}
						}
					}
					update_option( 'mwb_tyo_new_custom_order_status', $mwb_data_exist_db );

					$redirectURL = get_admin_url()."admin.php?page=wc-settings&tab=mwb_tyo_settings&section=custom_status";
					wp_redirect($redirectURL);
				}
				else{
					$redirectURL = get_admin_url()."admin.php?page=wc-settings&tab=mwb_tyo_settings&section=custom_status";
					wp_redirect($redirectURL);
				}
			}
			else{
				$redirectURL = get_admin_url()."admin.php?page=wc-settings&tab=mwb_tyo_settings&section=custom_status";
				wp_redirect($redirectURL);
			}
		}
	}
	$mwb_mwb_user_role_table_list = new MWB_MWB_Custom_Order_Status();
	$mwb_mwb_user_role_table_list->prepare_items();

	