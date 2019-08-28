<?php

$current_user_id = get_current_user_id();
if($current_user_id > 0)
{
	$myaccount_page = get_option( 'woocommerce_myaccount_page_id' );
	$myaccount_page_url = get_permalink( $myaccount_page );
	wp_redirect($myaccount_page_url);
	exit;
}	

get_header( 'shop' );

/**
 * woocommerce_before_main_content hook.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
*/
do_action( 'woocommerce_before_main_content' );

/**
 * woocommerce_after_main_content hook.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */

$mwb_main_wrapper_class = get_option('mwb_tyo_track_order_class');
$mwb_child_wrapper_class = get_option('mwb_tyo_track_order_child_class');
$mwb_track_order_css = get_option('mwb_tyo_tracking_order_custom_css');
?>
<style>	<?php echo $mwb_track_order_css;?>	</style>
<div class="woocommerce woocommerce-account <?php echo $mwb_main_wrapper_class;?>">
	<div class="<?php echo $mwb_child_wrapper_class;?>">
		<div id="mwb_tyo_guest_request_form_wrapper">
			<h2><?php 
			$return_product_form = __( 'Track Your Order', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN );
			echo apply_filters('mwb_tyo_return_product_form', $return_product_form);
			?>
			</h2>
			<?php 
			if(isset($_SESSION['mwb_tyo_notification']) && !empty($_SESSION['mwb_tyo_notification']))
			{
				?>
				<ul class="woocommerce-error">
						<li><strong><?php __('ERROR',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN); ?></strong>: <?php echo $_SESSION['mwb_tyo_notification'];?></li>
				</ul>
				<?php 
				unset($_SESSION['mwb_tyo_notification']);
			}
			?>
			<form class="login" method="post">
				<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
					<label for="username"><?php _e('Enter Order Id',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN );?><span class="required"> *</span></label>
					<input type="text" id="order_id" name="order_id" class="woocommerce-Input woocommerce-Input--text input-text">
				</p>
				
				<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
					<label for="username"><?php _e('Enter Order Email',MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN );?><span class="required"> *</span></label>
					<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="order_email" id="order_email" value="">
				</p>
				
				<p class="form-row">
					<input type="submit" value="<?php _e( 'TRACK ORDER', MWB_TRACK_YOUR_ORDER_TEXT_DOMAIN ); ?>" name="mwb_tyo_order_id_submit" class="woocommerce-Button button">
				</p>
			</form>
		</div>
	</div>
</div>
<?php 
do_action( 'woocommerce_after_main_content' );

/**
 * woocommerce_sidebar hook.
 *
 * @hooked woocommerce_get_sidebar - 10
*/

get_footer( 'shop' );
