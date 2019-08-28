/**
* Script for the FRONTEND 
* @author makewebbetter<webmaster@makewebbetter.com>
* @link http://www.makewebbetter.com/
*/

jQuery( document ).ready( function(){
	jQuery( document ).on( 'mouseover', '.mwb-circle', function(){

		var status = jQuery(this).attr( 'data-status' );
		console.log(status);
		if (status != '' ) 
		{
			var status_msg = '<h4>'+status+'</h4>';
			jQuery( '.mwb-tyo-mwb-delivery-msg' ).html( status_msg );
			jQuery( '.mwb-tyo-mwb-delivery-msg' ).show();
		}
	} );
	jQuery( document ).on( 'mouseout', '.mwb-circle', function(){

		jQuery( '.mwb-tyo-mwb-delivery-msg' ).hide();

	} );
} );
