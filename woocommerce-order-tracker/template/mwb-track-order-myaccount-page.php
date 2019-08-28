<?php
/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$allowed = true;

$current_user_id = get_current_user_id();
if( $current_user_id == '' || $current_user_id == null )
{
	$allowed = false;
}

if( $allowed == true )
{
	if(isset($_POST['order_id']))
	{
		$order_id = $_POST['order_id'];
	}
	else 
	{
		$link_array = explode('/',$_SERVER['REQUEST_URI']);
		if(empty($link_array[count($link_array)-1]))
		{
			$order_id = $link_array[count($link_array)-2];
		}	
		else
		{
			$order_id = $link_array[count($link_array)-1];
		}	
		
	}

	//check order id is valid
	
	if(!is_numeric($order_id))
	{
		
		if(get_current_user_id() > 0)
		{
			$myaccount_page = get_option( 'woocommerce_myaccount_page_id' );
			$myaccount_page_url = get_permalink( $myaccount_page);
		}
		else
		{
			$mwb_tyo_pages= get_option('mwb_tyo_tracking_page');
			$page_id = $mwb_tyo_pages['pages']['mwb_track_order_page'];
			$myaccount_page_url = get_permalink( $page_id );
		}
		$allowed = false;
		$reason = __('Please choose an Order.',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN).'<a href="'.$myaccount_page_url.'">'.__('Click Here',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN).'</a>';
		$reason = apply_filters('mwb_tyo_track_choose_order', $reason);
	}
	else 
	{
		$order_customer_id = get_post_meta($order_id, '_customer_user', true);
		
		if($current_user_id > 0)    // check order associated to customer account or not for registered user
		{
			if($order_customer_id != $current_user_id)
			{
				$myaccount_page = get_option( 'woocommerce_myaccount_page_id' );
				$myaccount_page_url = get_permalink( $myaccount_page );
				$allowed = false;
				$reason = __("This order #", MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN).$order_id.__( "is not associated to your account.", MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN). "<a href='$myaccount_page_url'>".__( 'Click Here ', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN )."</a>";
				$reason = apply_filters('mwb_tyo_track_choose_order', $reason);
			}
		}
		else						// check order associated to customer account or not for guest user
		{
			if(isset($_SESSION['mwb_tyo_email']))
			{
				$user_email = $_SESSION['mwb_tyo_email'];
				$order_email = get_post_meta($order_id, '_billing_email', true);
				if($user_email != $order_email)
				{
					$allowed = false;
					$mwb_tyo_pages= get_option('mwb_tyo_tracking_page');
					$page_id = $mwb_tyo_pages['pages']['mwb_track_order_page'];
					$myaccount_page_url = get_permalink( $page_id );
					$reason = __("This order #",MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN).$order_id.__( "is not associated to your account.",MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN). "<a href='$myaccount_page_url'>".__( 'Click Here ', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN )."</a>";
					$reason = apply_filters('mwb_tyo_track_choose_order', $reason);
				}	
			}	
			else
			{
				$allowed = false;
			}	 
		}	
	}
}else{
	$mwb_tyo_pages= get_option('mwb_tyo_tracking_page');
	$page_id = $mwb_tyo_pages['pages']['mwb_guest_track_order_page'];
	$track_order_url = get_permalink($page_id);
	header( 'Location: '.$track_order_url );
}
get_header( 'shop' );
	/**
	 * woocommerce_before_main_content hook.
	 *
	 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
	 * @hooked woocommerce_breadcrumb - 20
	 */
	do_action( 'woocommerce_before_main_content' );
	$mwb_main_wrapper_class = get_option('mwb_tyo_track_order_class');
	$mwb_child_wrapper_class = get_option('mwb_tyo_track_order_child_class');
	$mwb_track_order_css = get_option('mwb_tyo_tracking_order_custom_css');
	?>
	<style>	<?php echo $mwb_track_order_css;?>	</style>
	<div class="mwb-tyo-order-tracking-section <?php echo $mwb_main_wrapper_class;?>">
		<?php
		if ($allowed == true) 
		{
			$order = new WC_Order( $order_id );
			$expected_delivery_date = get_post_meta( $order_id, 'mwb_tyo_estimated_delivery_date' , true );
			$expected_delivery_time = get_post_meta( $order_id, 'mwb_tyo_estimated_delivery_time' , true );
			$order_delivered_date = get_post_meta( $order_id, '_completed_date', true );
			if(WC()->version < "3.0.0")
			{
				$order_status = $order->post_status;
				$ordered_by = $order->post->post_author;
				$ordered_by = get_user_by( 'ID', $ordered_by );
				$ordered_by = $ordered_by->data->display_name;
			}
			else
			{
				$order_status='wc-'.$order->get_status();
				$ordered_by = $order->get_customer_id();
				$ordered_by = get_user_by( 'ID', $ordered_by );
				$ordered_by = $ordered_by->data->display_name;
			}
			$billing_first_name = get_post_meta( $order_id, '_billing_first_name', true );
			$billing_last_name = get_post_meta( $order_id, '_billing_last_name', true );
			$billing_address = get_post_meta( $order_id, '_billing_address_1', true )." ".get_post_meta( $order_id, '_billing_address_2', true );
			$billing_city = get_post_meta( $order_id, '_billing_city', true );
			$billing_state = get_post_meta( $order_id, '_billing_state', true );
			$billing_country = get_post_meta( $order_id, '_billing_country', true );
			$billing_postcode = get_post_meta( $order_id, '_billing_postcode', true );
			$mwb_track_order_status = get_post_meta( $order_id, 'mwb_track_order_status' , true );
			$order_status_key = str_replace('-', '_', $order_status);
			$order_status_key = 'mwb_tyo_'.$order_status_key.'_text';
			?>
			<section class="mwb-order-section about-section details-section <?php echo $mwb_child_wrapper_class;?>">
				<div class="mwb-order-details-header">
					<h2><?php _e('Order Details',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN); ?></h2>
				</div>
				<div class="mwb-order-details-div">
					<ul class="mwb-order-listing">
						<li>
							<span><?php _e( 'Order Id:' , MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ); ?></span>
							<span><?php echo $order_id; ?><?php _e( '('.count( $order->get_items() ) . ' items)', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ); ?></span>
						</li>
						<li>
							<span><?php _e( 'Order Date:' , MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ); ?></span>
							<span><?php if(WC()->version < "3.0.0"){ echo date_i18n( 'F d, Y h:i', strtotime($order->post->post_date));}else{ $mwb_date=$order->get_date_created(); echo date_i18n( 'F d, Y h:i', strtotime( $mwb_date ) );} ?></span>
						</li>
						<li>
							<span><?php _e( 'Amount Paid:' , MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ); ?></span>
							<span><strong class="amt-paid"><?php echo mwb_tyo_format_price( $order->get_total() ); ?></strong></span>
						</li>
					</ul>
				</div>
				<div class="mwb-order-details-div">
					<h3><?php echo $billing_first_name." ".$billing_last_name." ".get_post_meta( $order_id, '_billing_phone', true ); ?></h3>
					<div class="address-block">
						<ul>
							<li><?php echo $billing_address; ?></li>
							<li><?php echo $billing_city.", ".$billing_state." -".$billing_postcode; ?></li>
							<li><?php echo WC()->countries->countries[ $billing_country ]; ?></li>
						</ul>
					</div>
				</div>
			</section>

			<section class="section product-details-section <?php echo $mwb_child_wrapper_class;?>">
				<table class="shop_table order_details mwb-product-details-table mwb-tyo-product-detail-table"> 
					<thead>
						<tr>
							<th><?php _e( 'Product Details', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ); ?></th>
							<th><?php _e( 'Quantity', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ); ?></th>
							<th><?php _e( 'Sub Total', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php $total = 0; ?>
						<?php if(WC()->version < "3.0.0"){ 
							foreach( $order->get_items() as $item_id => $item ) {
								if ($item['qty'] > 0) {
									$product = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
									$thumbnail     = $product ? apply_filters( 'woocommerce_admin_order_item_thumbnail', $product->get_image( 'thumbnail', array( 'title' => '' ), false ), $item_id, $item ) : '';
									$productdata = new WC_Product($product->id);
									$is_visible        = $product && $product->is_visible();
									$product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );
									?>
									<?php $total += $product->get_price(); ?>
									<tr>
										<td>
											<div class="mwb-product-wrapper mwb-product-img">
												<?php if(isset($thumbnail) && !empty($thumbnail))
												{	
													echo  wp_kses_post( $thumbnail );
												}
												else
												{
													?>
													<img alt="<?php _e( 'Placeholder', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ); ?>" width="150" height="150" class="attachment-thumbnail size-thumbnail wp-post-image mwb-img-responsive" src="<?php echo home_url();?>/wp-content/plugins/woocommerce/assets/images/placeholder.png">
													<?php 
												} ?>
											</div>
											<div class="mwb-product-wrapper mwb-product-desc">
												<h4><a href=""><?php echo $productdata->post->post_title ; ?></a></h4>
											</div>
										</td>
										<td>
											<?php echo $item['qty']; ?>
										</td>
										<td>
											<span><b><?php echo mwb_tyo_format_price( $product->get_price() ); ?></b></span>
										</td>
									</tr>
									<?php
								}
							} 
						}
						else
						{
							$total = 0;
							foreach( $order->get_items() as $item_id => $item ) {
								if ($item->get_quantity() > 0) {
									$product = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
									$thumbnail     = $product ? apply_filters( 'woocommerce_admin_order_item_thumbnail', $product->get_image( 'thumbnail', array( 'title' => '' ), false ), $item_id, $item ) : '';
									$productdata = wc_get_product($product->get_id());
									$is_visible        = $product && $product->is_visible();
									$product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );
									?>
									<?php $total += $product->get_price(); ?>
									<tr>
										<td>
											<div class="mwb-product-wrapper mwb-product-img">
												<?php if(isset($thumbnail) && !empty($thumbnail))
												{	
													echo  wp_kses_post( $thumbnail );
												}
												else
												{
													?>
													<img alt="<?php _e( "Placeholder", MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ); ?>" width="150" height="150" class="attachment-thumbnail size-thumbnail wp-post-image mwb-img-responsive" src="<?php echo home_url();?>/wp-content/plugins/woocommerce/assets/images/placeholder.png">
													<?php 
												} ?>
											</div>
											<div class="mwb-product-wrapper mwb-product-desc">
												<h4><a href=""><?php echo $productdata->get_title() ; ?></a></h4>
											</div>
										</td>
										<td>
											<?php echo $item->get_quantity(); ?>
										</td>
										<td>
											<span><b><?php echo mwb_tyo_format_price( $product->get_price() ); ?></b></span>
										</td>
									</tr>
									<?php
								}
							} 
						}?>
						<tr>
							<td>
								<div>
									<span><b><?php _e( 'Total', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ) ?></b></span>
								</div>
							</td>
							<td>

							</td>
							<td>
								<div>
									<span><b><?php echo mwb_tyo_format_price( $total ); ?></b></span>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</section>
			<?php $get_status_approval = get_option( 'mwb_tyo_order_status_in_approval', array() );
			$get_status_processing = get_option( 'mwb_tyo_order_status_in_processing', array() );
			$get_status_shipping = get_option( 'mwb_tyo_order_status_in_shipping', array() ); 
			$mwb_track_order_status = array();
			$mwb_track_order_status = get_post_meta( $order_id, 'mwb_track_order_status' , true);
			$woo_statuses = wc_get_order_statuses();
			$status_process = 0 ;
			$status_shipped = 0 ;
			if(is_array($get_status_processing) && !empty($get_status_processing)){
				foreach ($get_status_processing as $key => $value) {
					if( !empty($mwb_track_order_status) && in_array($value, $mwb_track_order_status) )
					{
						$status_process = 1;
					}
				}
			}

			if(is_array($get_status_shipping) && !empty($get_status_shipping)){
				foreach ($get_status_shipping as $key1 => $value1) {
					if( !empty($mwb_track_order_status) && in_array($value, $mwb_track_order_status) )
					{
						$status_shipped = 1;
					}
				}
			}

			?>
			<section class="<?php echo $mwb_child_wrapper_class;?> section product-details-section">
				<table class="shop_table order_details mwb-product-details-table mwb-tyo-track-order-table">
					<thead>
						<tr>
							<th><?php _e( 'APPROVAL', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ); ?></th>
							<th><?php _e( 'PROCESSING', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ); ?></th>
							<th><?php _e( 'SHIPPING', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ); ?></th>
							<?php if ( $expected_delivery_date != '' || $expected_delivery_time != '' || $order_delivered_date != ''  ) { ?>
							<th><?php _e( 'DELIVERY', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ); ?></th>
							<?php } ?>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="3">
								<div class="mwb-design-division">
									<div class="mwb-controller">
										<span class="track-approval">
											<span class="mwb-circle mwb-tyo-hover <?php if( empty($mwb_track_order_status) ){ echo 'active'; } ?>" data-status = "<?php _e( 'Your Order is Successfully Placed', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ); ?>"></span> 

											<?php 
											$class = '';
											$active = 0 ;
											$f = 0;
											$cancelled = 0;
											if( is_array($mwb_track_order_status) && empty($mwb_track_order_status) && $order_status != '' && in_array( $order_status, $get_status_approval ) ) 
												{ ?>
											<?php $current_status = get_option( $order_status_key, __('Your Order status is ',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN).$woo_statuses[$order_status] );?>
											<span class="mwb-circle active" data-status = '<?php echo $current_status; ?>'></span>	
											
											<?php }
											else if( is_array($mwb_track_order_status) && !empty( $mwb_track_order_status ) ){
												$f = 0;
												foreach ($mwb_track_order_status as $key => $value) {
													if ( in_array($value, $get_status_approval) ) {
														$f = 1;
														$value_key = str_replace('-', '_', $value);
														$value_key = 'mwb_tyo_'.$value_key.'_text';
														$message = __('Your Order status is ',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN).$woo_statuses[$value];
														?>
														<?php $current_status = get_option( $value_key, '' );?>
														<?php if ($current_status == '') {
															$current_status = $message;
														} ?>
														<span class="mwb-circle mwb-tyo-hover <?php if( !isset($mwb_track_order_status[$key+1]) ){ $active = 1; echo 'active' ; } ?>"  data-status = '<?php echo $current_status; ?>'></span>	

														<?php
													}
													if( isset($mwb_track_order_status[$key+1]) && $mwb_track_order_status[$key+1] == 'wc-cancelled' && in_array( $value , $get_status_approval ) && $order_status == 'wc-cancelled' ){
														$cancelled = 1;
														$current_status = get_option( 'mwb_tyo_wc_cancelled_text' , '' );
														if ($current_status == '') {
															$current_status =__( 'Your Order is Cancelled', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN );
														}
														?>
														<span class="mwb-circle order-cancelled"  data-status = '<?php echo $current_status; ?>'></span>	

														<?php
													}
												}
											} ?>

										</span>
										<span class="track-processing">
											<?php if($cancelled != 1){ ?>

											<?php if( $active == 1 ){
												if(is_array($get_status_processing) && !empty($get_status_processing)){
													foreach ($get_status_processing as $key => $value) {
														if(  in_array($value, $mwb_track_order_status) ){
															$class = 'revert';
														}
													}	
												} 
											}?>
											<?php $f = 0; ?>
											<?php if( is_array($mwb_track_order_status) && empty($mwb_track_order_status) && $order_status != '' )
											{ ?>
											<?php $current_status = get_option( $order_status_key, __('Your Order status is ',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN).$woo_statuses[$order_status] );?>
											<span class="mwb-circle active" data-status = '<?php echo $current_status; ?>'></span>	

											<?php }
											else if( is_array($mwb_track_order_status) && !empty( $mwb_track_order_status ) ){
												$f = 0;
												foreach ($mwb_track_order_status as $key => $value) {
													if ( in_array($value, $get_status_processing) ) {
														$f = 1;
														$value_key = str_replace('-', '_', $value);
														$value_key = 'mwb_tyo_'.$value_key.'_text';
														$message = __('Your Order status is ',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN).$woo_statuses[$value];
														?>
														<?php $current_status = get_option( $value_key, '' );?>
														<?php if ($current_status == '') {
															$current_status = $message;
														} ?>
														<span class="mwb-circle mwb-tyo-hover <?php echo $class ?> <?php if( !isset($mwb_track_order_status[$key+1]) ){ $active = 1; echo 'active' ; } ?>" data-status = '<?php if($class == "revert") { _e( "Your Order is Sent back", MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ); }else { echo $current_status; } ?>'  ></span>	
														
														<?php
													}
													if( isset($mwb_track_order_status[$key+1]) && $mwb_track_order_status[$key+1] == 'wc-cancelled' && in_array( $value , $get_status_processing ) &&  $order_status == 'wc-cancelled' ){
														$cancelled = 1;
														$current_status = get_option( 'mwb_tyo_wc_cancelled_text' , '' );
														if ($current_status == '') {
															$current_status = __( 'Your Order is Cancelled', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN );
														}
														?>
														<span class="mwb-circle order-cancelled"  data-status = '<?php echo $current_status; ?>'></span>	
														
														<?php
													}
												}
												if($f != 1 && $status_process == 0 && $status_shipped == 0){
													?>
													<span class="mwb-circle hollow" data-status=""></span> 
													<?php
												}else if( $f != 1 && $status_process == 0 && $status_shipped == 1 ){
													?>
													<span class="mwb-circle" data-status="<?php _e( 'Your Order Is Processed', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ); ?>"></span> 
													<?php
												}
											}
											else{
												?>
												<span class="mwb-circle hollow" data-status=""></span> 
												<?php
											} ?>
											<?php }else{
												$current_status = get_option( 'mwb_tyo_wc_cancelled_text' , __('Your Order is cancelled',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN) );
												?>
												<span class="mwb-circle red" data-status="<?php echo $current_status; ?>"></span> 
												<?php
											} ?>
										</span>
										<span class="track-shipping">
											<?php if($cancelled != 1){ ?>
											<?php if( $active == 1 ){
												if(is_array($get_status_shipping) && !empty($get_status_shipping)){
													foreach ($get_status_shipping as $key => $value) {
														if(  in_array($value, $mwb_track_order_status) ){
															$class = 'revert';
														}
													}	
												} 
											}?>
											<?php 
											$f = 0;
											if( is_array($mwb_track_order_status) && empty($mwb_track_order_status) && $order_status != '' )
												{ ?>
											<?php $current_status = get_option( $order_status_key, __('Your Order status is ',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN).$woo_statuses[$order_status] );?>
											<span class="mwb-circle active" data-status = '<?php echo $current_status; ?>'></span>	

											<?php }
											else if( is_array($mwb_track_order_status) && !empty( $mwb_track_order_status ) ){
												$f = 0;
												foreach ($mwb_track_order_status as $key => $value) {
													if ( in_array($value, $get_status_shipping) ) {
														$f = 1 ;
														$value_key = str_replace('-', '_', $value);
														$value_key = 'mwb_tyo_'.$value_key.'_text';
														$message = __('Your Order status is ',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN).$woo_statuses[$value];
														?>
														<?php $current_status = get_option( $value_key, '' );?>
														<?php if ($current_status == '') {
															$current_status = $message;
														} ?>
														<span class="mwb-circle mwb-tyo-hover <?php echo $class; ?> <?php if( !isset($mwb_track_order_status[$key+1]) ){ $active = 1; echo 'active' ; } ?>" data-status = '<?php if($class == "revert") { _e( "Your Order is Sent back", MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ); } else { echo $current_status; } ?>'  ></span>	

														<?php
													}
													if( isset($mwb_track_order_status[$key+1]) && $mwb_track_order_status[$key+1] == 'wc-cancelled' && in_array( $value , $get_status_shipping ) &&  $order_status == 'wc-cancelled' ){
														$cancelled = 1;
														$current_status = get_option( 'mwb_tyo_wc_cancelled_text' , '' );
														if ($current_status == '') {
															$current_status = __( 'Your Order is Cancelled', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN );
														}
														?>
														<span class="mwb-circle order-cancelled"  data-status = '<?php echo $current_status; ?>'></span>	
														
														<?php
													}
												}
												if($f != 1){
													?>
													<span class="mwb-circle hollow" data-status=""></span> 
													<?php
												}
											}
											else{
												?>
												<span class="mwb-circle hollow" data-status=""></span> 
												<?php
											} ?>
											<?php }else{
												$current_status = get_option( 'mwb_tyo_wc_cancelled_text' , __('Your Order is cancelled',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN) );
												?>
												<span class="mwb-circle red" data-status="<?php echo $current_status; ?>"></span> 
												<?php
											} ?>
										</span>
										<div class="mwb-deliver-msg mwb-tyo-mwb-delivery-msg"></div>
									</div>
								</div>
							</td>
							<?php if ( $expected_delivery_date != '' || $expected_delivery_time != '' || $order_delivered_date != ''  ) { ?>
							<td>
								<div class="mwb-delivery-div">
									<span><?php if( $order_status == 'wc-cancelled' ) { _e( 'Order Cancelled', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ); }else if( $order_delivered_date == '' && $order_status != 'wc-cancelled' ) { _e( 'Not Delivered', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ); }else{ 
										echo __('on ',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN).date_i18n( 'F d, Y h:i', strtotime( $order_delivered_date ) );
									} ?></span>
									<?php  if( $expected_delivery_date != '' ) {  ?><span><?php if( ($order_delivered_date != '') || ($order_status == 'wc-cancelled') ){ ?><del><?php echo __('by ',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN).$expected_delivery_date . " " .$expected_delivery_time; ?>
								</del><?php } else{ echo __('by ',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN).$expected_delivery_date . " " .$expected_delivery_time;} ?></span><?php } ?>
							</div>
						</td>
						<?php } ?>
					</tr>
				</tbody>
			</table>
		</section>
		<?php
	}
	else 
	{
		$return_request_not_send = __('Tracking Request can\'t be send. ', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN );
		echo apply_filters('mwb_tyo_tracking_request_not_send', $return_request_not_send);
		echo $reason;
	}?>
</div>
<?php
/**
 * woocommerce_after_main_content hook.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'woocommerce_after_main_content' );

get_footer( 'shop' );