/**
* Script for the ADMIN END
* @author makewebbetter<webmaster@makewebbetter.com>
* @link http://www.makewebbetter.com/
*/

jQuery( document ).ready( function(){

	var current_url = window.location.href;
	if( current_url.indexOf( 'tab=mwb_tyo_settings&section=custom_status' ) > 0 )
	{
		jQuery( document ).find( '.woocommerce-save-button' ).hide();
	}
	jQuery("div#mwb_mwb_mail_success").hide();
	jQuery("div#mwb_mwb_mail_failure").hide();
	jQuery("div#mwb_mwb_mail_empty").hide();
	jQuery("div#mwb_mwb_invalid_input").hide();
	jQuery("div#mwb_mwb_select_for_delete").hide();

	/*SETTING A TIMEPICKER TO THE METABOX ON ORDER EDIT PAGE*/
	if( jQuery( '.mwb_tyo_est_delivery_time' ).length > 0 ){

		jQuery( '.mwb_tyo_est_delivery_time' ).timepicker();
	}

	/*SETTING A DATEPICKER TO THE METABOX ON ORDER EDIT PAGE*/
	if( jQuery( '.mwb_tyo_est_delivery_date' ).length > 0 ){

		jQuery( '.mwb_tyo_est_delivery_date' ).datepicker();
	}

	/*SHOW / HIDE FOR SELECTING THE USE OF CUSTOM ORDER STATUS */
	jQuery( '#mwb_tyo_enable_custom_order_feature' ).on( 'change', function(){
		if ( jQuery( '#mwb_tyo_enable_custom_order_feature' ).is( ':checked' ) ) {
			jQuery('#mwb_tyo_new_custom_statuses_for_order_tracking').closest('tr').show();
		}else{
			jQuery('#mwb_tyo_new_custom_statuses_for_order_tracking').closest('tr').hide();
		}
	} );

	if ( jQuery( '#mwb_tyo_enable_custom_order_feature' ).is( ':checked' ) ) {
		jQuery('#mwb_tyo_new_custom_statuses_for_order_tracking').closest('tr').show();
	}else{
		jQuery('#mwb_tyo_new_custom_statuses_for_order_tracking').closest('tr').hide();
	}

	jQuery( '#mwb_tyo_order_status_in_hidden' ).closest('tr').hide();


	var selected_order_status_approval = jQuery( '#mwb_tyo_order_status_in_approval' ).val();
	var selected_order_status_processing = jQuery( '#mwb_tyo_order_status_in_processing' ).val();
	var selected_order_status_shipping = jQuery( '#mwb_tyo_order_status_in_shipping' ).val();

	jQuery.each( selected_order_status_processing , function( key , value ){
		jQuery("#mwb_tyo_order_status_in_approval option[value="+value+"]").remove();
		jQuery("#mwb_tyo_order_status_in_shipping option[value="+value+"]").remove();
	} );

	jQuery.each( selected_order_status_approval , function( key , value ){
		jQuery("#mwb_tyo_order_status_in_processing option[value="+value+"]").remove();
		jQuery("#mwb_tyo_order_status_in_shipping option[value="+value+"]").remove();
	} );

	jQuery.each( selected_order_status_shipping , function( key , value ){
		jQuery("#mwb_tyo_order_status_in_processing option[value="+value+"]").remove();
		jQuery("#mwb_tyo_order_status_in_approval option[value="+value+"]").remove();
	} );

	jQuery( document ).on( 'change' , '#mwb_tyo_new_custom_statuses_for_order_tracking', function(){
		var selected_order_status_approval = jQuery( '#mwb_tyo_order_status_in_approval' ).val();
		// console.log(selected_order_status_approval);
		var selected_order_status_processing = jQuery( '#mwb_tyo_order_status_in_processing' ).val();
		var selected_order_status_shipping = jQuery( '#mwb_tyo_order_status_in_shipping' ).val();
		var notSelected = jQuery("#mwb_tyo_new_custom_statuses_for_order_tracking").find('option').not(':selected');
		var array1 = notSelected.map(function () {
			return this.value;
		}).get();
		
		jQuery.each( array1 , function( key, val ){
			jQuery("#mwb_tyo_order_status_in_processing option[value='"+val+"']").remove();
			jQuery("#mwb_tyo_order_status_in_approval option[value='"+val+"']").remove();
			jQuery("#mwb_tyo_order_status_in_shipping option[value='"+val+"']").remove();
			var value = val.replace( 'wc-' , '' );
			if( jQuery(document).find( '.select2-selection__choice' ).attr( 'title' ) == value )
			{
				jQuery(document).find( '.select2-selection__choice' ).attr( 'title', value ).remove();
			}
		} );
		var order_statuses = global_tyo.order_statuses;
		var val = jQuery(this).val();


		jQuery.each( val , function(key , value){
			console.log(order_statuses);
			var status_name = order_statuses[value];

			if( status_name == '' || status_name == null ){
				status_name = value.replace( 'wc-' , '' );
			}
			var  l = '<option value='+value+'>'+status_name+'</option>';
			if((jQuery.inArray(value, selected_order_status_approval)==-1) && (jQuery.inArray(value,selected_order_status_processing)==-1) && (jQuery.inArray(value,selected_order_status_shipping)==-1) )
			{
				// console.log(value);
				if( jQuery("#mwb_tyo_order_status_in_processing option[value="+value+"]").length <= 0 ){
					jQuery('#mwb_tyo_order_status_in_processing').append( l );
				}
				if( jQuery("#mwb_tyo_order_status_in_approval option[value="+value+"]").length <= 0 ){
					jQuery('#mwb_tyo_order_status_in_approval').append( l );
				}
				if( jQuery("#mwb_tyo_order_status_in_shipping option[value="+value+"]").length <= 0 ){
					jQuery('#mwb_tyo_order_status_in_shipping').append( l );
				}
			}
			
		} );

	} );


jQuery( document ).on( 'change', '#mwb_tyo_order_status_in_approval', function()
{
	var order_statuses = global_tyo.order_statuses;
	var existing_value =jQuery('#mwb_tyo_new_custom_statuses_for_order_tracking').val();
	var status = [];
	var selected_order_status_approval = jQuery( '#mwb_tyo_order_status_in_approval' ).val();
	console.log(selected_order_status_approval);
	var selected_order_status_processing = jQuery( '#mwb_tyo_order_status_in_processing' ).val();
	var selected_order_status_shipping = jQuery( '#mwb_tyo_order_status_in_shipping' ).val();
	var hidden_value = []; 
	var previously_selected_value = []; 
	var previously_selected_value = jQuery( '#mwb_tyo_order_status_in_hidden' ).val();
	jQuery.each( selected_order_status_approval, function( key, value ){
		hidden_value.push( value );
	} );
	// console.log(selected_order_status_processing);
	jQuery.each( selected_order_status_processing, function( key, value ){
		hidden_value.push( value );
	} );
	jQuery.each( selected_order_status_shipping, function( key, value ){
		hidden_value.push( value );
	} );
	// console.log(hidden_value);
	
	jQuery( '#mwb_tyo_order_status_in_hidden' ).val( hidden_value );
	var pre_length = 0 ;
	if(previously_selected_value != null && previously_selected_value.length != null && previously_selected_value.length != 0)
	{
		var pre_length = previously_selected_value.length;
	}
	var hidden_length = hidden_value.length;
	if( pre_length >= hidden_length )
	{
		var i = 0;
		jQuery.grep(previously_selected_value, function(el) {

			if (jQuery.inArray(el, hidden_value) == -1) 
			{
				var status_name = order_statuses[el];
				var  l = '<option value='+el+'>'+status_name+'</option>';
				// console.log( l );
				jQuery('#mwb_tyo_order_status_in_processing').append( l );
				jQuery('#mwb_tyo_order_status_in_shipping').append( l );
			}


			i++;

		});
	}
	else if( pre_length <= hidden_length )
	{
		var i = 0;
		jQuery.grep(hidden_value, function(el) {

			if (jQuery.inArray(el, previously_selected_value) == -1) 
			{
				jQuery("#mwb_tyo_order_status_in_processing option[value="+el+"]").remove();
				jQuery("#mwb_tyo_order_status_in_shipping option[value="+el+"]").remove();

			}


			i++;

		});
	}

} );

jQuery( document ).on( 'change', '#mwb_tyo_order_status_in_processing', function()
{
	var order_statuses = global_tyo.order_statuses;
	var existing_value =jQuery('#mwb_tyo_new_custom_statuses_for_order_tracking').val();
	var status = [];
	var selected_order_status_approval = jQuery( '#mwb_tyo_order_status_in_approval' ).val();
	var selected_order_status_processing = jQuery( '#mwb_tyo_order_status_in_processing' ).val();
	var selected_order_status_shipping = jQuery( '#mwb_tyo_order_status_in_shipping' ).val();
	var hidden_value = [] ; 
	var previously_selected_value = []; 
	var previously_selected_value = jQuery( '#mwb_tyo_order_status_in_hidden' ).val();
	jQuery.each( selected_order_status_approval, function( key, value ){
		hidden_value.push( value );
	} );
	jQuery.each( selected_order_status_processing, function( key, value ){
		hidden_value.push( value );
	} );
	jQuery.each( selected_order_status_shipping, function( key, value ){
		hidden_value.push( value );
	} );
	jQuery( '#mwb_tyo_order_status_in_hidden' ).val( hidden_value );

	var pre_length = 0 ;
	if(previously_selected_value != null && previously_selected_value.length != null && previously_selected_value.length != 0)
	{
		var pre_length = previously_selected_value.length;
	}
	var hidden_length = hidden_value.length;

	if( pre_length >= hidden_length )
	{

		var i = 0;
		jQuery.grep(previously_selected_value, function(el) {

			if (jQuery.inArray(el, hidden_value) == -1) 
			{
				var status_name = order_statuses[el];
				var  l = '<option value='+el+'>'+status_name+'</option>';
				jQuery('#mwb_tyo_order_status_in_approval').append( l );
				jQuery('#mwb_tyo_order_status_in_shipping').append( l );
			}


			i++;

		});
	}
	else if( pre_length <= hidden_length )
	{
		var i = 0;
		jQuery.grep(hidden_value, function(el) {

			if (jQuery.inArray(el, previously_selected_value) == -1) 
			{

				jQuery("#mwb_tyo_order_status_in_approval option[value="+el+"]").remove();
				jQuery("#mwb_tyo_order_status_in_shipping option[value="+el+"]").remove();

			}


			i++;

		});
	}
} );

jQuery( document ).on( 'change', '#mwb_tyo_order_status_in_shipping', function()
{
	var order_statuses = global_tyo.order_statuses;
	// var existing_value =jQuery('#mwb_tyo_new_custom_statuses_for_order_tracking').val();
	var status = [];
	var selected_order_status_approval = jQuery( '#mwb_tyo_order_status_in_approval' ).val();
	var selected_order_status_processing = jQuery( '#mwb_tyo_order_status_in_processing' ).val();
	var selected_order_status_shipping = jQuery( '#mwb_tyo_order_status_in_shipping' ).val();
	// console.log(selected_order_status_processing);
	var hidden_value = []; 
	var previously_selected_value = []; 
	var previously_selected_value = jQuery( '#mwb_tyo_order_status_in_hidden' ).val();
	jQuery.each( selected_order_status_approval, function( key, value ){
		hidden_value.push( value );
	} );
	jQuery.each( selected_order_status_processing, function( key, value ){
		hidden_value.push( value );
	} );
	jQuery.each( selected_order_status_shipping, function( key, value ){
		hidden_value.push( value );
	} );

	jQuery( '#mwb_tyo_order_status_in_hidden' ).val( hidden_value );

	var pre_length = 0 ;
	if(previously_selected_value != null && previously_selected_value.length != null && previously_selected_value.length != 0)
	{
		var pre_length = previously_selected_value.length;
	}
	var hidden_length = hidden_value.length;

	if( pre_length >= hidden_length )
	{
		var i = 0;
		jQuery.grep(previously_selected_value, function(el) {

			if (jQuery.inArray(el, hidden_value) == -1) 
			{
				var status_name = order_statuses[el];
				var  l = '<option value='+el+'>'+status_name+'</option>';
				jQuery('#mwb_tyo_order_status_in_processing').append( l );
				jQuery('#mwb_tyo_order_status_in_approval').append( l );
			}
			i++;

		});
	}
	else if( pre_length <= hidden_length )
	{
		var i = 0;
		jQuery.grep(hidden_value, function(el) {

			if (jQuery.inArray(el, previously_selected_value) == -1) 
			{

				jQuery("#mwb_tyo_order_status_in_processing option[value="+el+"]").remove();
				jQuery("#mwb_tyo_order_status_in_approval option[value="+el+"]").remove();

			}

			i++;
		});
	}
} );
jQuery(document.body).on('click','.mwb_delete_costom_order',function(){
	var mwb_action=jQuery(this).data('action');
	var mwb_key=jQuery(this).data('key');
	jQuery.ajax({
		url: global_tyo.ajaxurl,
		type : 'post',
		data:{
			action : 'mwb_mwb_delete_custom_order_status',
			mwb_custom_action : mwb_action,
			mwb_custom_key	: mwb_key
		},
		success: function(response){
			if(response=='success')
			{
				location.reload();
			}

		}

	});
});

} );


jQuery(document).on('click','input#mwb_mwb_create_role_box',function(){
	jQuery(this).toggleClass('role_box_open');
	jQuery("div#mwb_mwb_create_box").slideToggle();
	if(jQuery(this).hasClass('role_box_open')) {
		jQuery(this).val('Close');
	}
	else {
		jQuery(this).val('Create Custom Order Status');
	}
});


jQuery(document).on('click','input#mwb_mwb_create_custom_order_status',function(){
	jQuery('#mwb_mwb_send_loading').show();
	var mwb_mwb_create_order_status = jQuery('#mwb_mwb_create_order_name').val().trim();
	if(mwb_mwb_create_order_status != "" && mwb_mwb_create_order_status != null) 
	{
		if( /^[a-zA-Z0-9- ]*$/.test(mwb_mwb_create_order_status) )
		{
			mwb_mwb_create_order_status = mwb_mwb_create_order_status.replace(/\s+/g, '');
			
			jQuery.ajax({
				url : global_tyo.ajaxurl,
				type : 'post',
				data : {
					action : 'mwb_mwb_create_custom_order_status',
					mwb_mwb_new_role_name : mwb_mwb_create_order_status
				},
				success : function( response ) {
					jQuery('#mwb_mwb_send_loading').hide();

					if(response == "success") {
						jQuery('input#mwb_mwb_create_role_box').trigger('click');
						jQuery("div.mwb_notices_order_tracker").html('<div id="message" class="notice notice-success"><p><strong>'+global_tyo.message_success+'</strong></p></div>');
						jQuery('#mwb_mwb_create_order_name').val('');
						location.reload();
					}
					else {
						jQuery("div.mwb_notices_order_tracker").html('<div id="message" class="notice notice-error"><p><strong>'+global_tyo.message_error_save+'</strong></p></div>').delay(2000).fadeOut(function(){});
					}	
				}
			});
		}
		else{
			jQuery('#mwb_mwb_send_loading').hide();
			jQuery("div.mwb_notices_order_tracker").html( '<div id="message" class="notice notice-error"><p><strong>'+global_tyo.message_invalid_input+'</strong></p></div>' ).delay(4000).fadeOut(function(){});
			return;
		}	
	}else{
		jQuery('#mwb_mwb_send_loading').hide();
		jQuery("div.mwb_notices_order_tracker").html( '<div id="message" class="notice notice-error"><p><strong>'+global_tyo.message_empty_data+'</strong></p></div>' ).delay(4000).fadeOut(function(){});
		return;
	}
	jQuery('#mwb_mwb_send_loading').hide();
});

