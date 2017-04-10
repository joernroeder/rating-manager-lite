jQuery( document ).ready(function($) {

	jQuery( '#toplevel_page_rating-manager-lite-admin-panels-settings-general .wp-submenu li:last-child a').css( 'color', 'lightgreen' );

	// Buttons
	// Reset style HTML template to default
	jQuery( '#reset-style-html-template' ).on( 'click', function() {
		jQuery( '#html-template' ).val('%THANK_YOU_MESSAGE% <div class="elm-rating-wrapper">%RATING%</div>');
	});

	jQuery( '#rating-svg-icon' ).on( 'change', function() {
		var svg_file_name = jQuery(this).val();
		
		elm_change_ur_svg_icon( svg_file_name );
	});

	// Stats buttons
	// Reset post type stats
	jQuery( '#reset-post-type-stats' ).on( 'click', function() {
		var r = confirm('Are you sure you want to reset statistics for post types?');
		
		if ( r == true ) {
			var nonce = jQuery( 'input[name="reset_post_type_stats_nonce"]' ).val();

		    var data = {
				action: 'elm_reset_post_type_stats',
				nonce: nonce
			};
			
			jQuery.post(ajaxurl, data, function(response) {
				jQuery( '.reset-post-type-stats-message' ).show();
				jQuery( '.reset-post-type-stats-message' ).html( response.message ).fadeOut(2500);
			}, "json");
		}
	});
	
	// Reset post ratings
	jQuery( '#reset-post-stats' ).on( 'click', function() {
		var r = confirm('Are you sure you want to reset ratings for this post?');
		
		if ( r == true ) {
			var post_id = jQuery( 'input[name="reset_ratings_post_id"]' ).val();
			var nonce = jQuery( 'input[name="reset_ratings_nonce"]' ).val();

		    var data = {
				action: 'elm_reset_post_stats',
				post_id: post_id,
				nonce: nonce
			};
			
			jQuery.post(ajaxurl, data, function(response) {
				jQuery('.ur-average-rating').html('0');
				jQuery('.ur-rated-by-users').html('0');
				jQuery('.ur-feedback').html('0');
				
				jQuery( '.reset-post-stats-message' ).show();
				jQuery( '.reset-post-stats-message' ).html( response.message ).css('color', '#7ad03a').fadeOut(2500);
			}, "json");
		}
	});
	
	if ( jQuery().ColorPicker ) {
 			jQuery( '.elm-ur-color' ).each( function () {
 				var option_id = jQuery( this ).find( '.elm-color' ).attr( 'id' );
				var color = jQuery( this ).find( '.elm-color' ).val();
				var picker_id = option_id += '_picker';

	 			jQuery( '#' + picker_id ).children( 'div' ).css( 'backgroundColor', color );
				jQuery( '#' + picker_id ).ColorPicker({
				
					color: color,
					onShow: function ( colpkr ) {
						jQuery( colpkr ).fadeIn( 200 );
						return false;
					},
					onHide: function ( colpkr ) {
						jQuery( colpkr ).fadeOut( 200 );
						return false;
					},
					onChange: function ( hsb, hex, rgb ) {
						jQuery( '#' + picker_id ).children( 'div' ).css( 'backgroundColor', '#' + hex );
						jQuery( '#' + picker_id ).next( 'input' ).attr( 'value', '#' + hex );
					
					}
				});
 			});
 		}
});

function elm_change_ur_svg_icon( svg_file_name ) {
	var file = svg_folder_url + svg_file_name + '.svg';
		
	var svg_source = elm_rml_admin_load_svg( file );
		
	jQuery('#rating-svg-icon-preview').html( svg_source );
	jQuery('#rating-svg-icon-preview').find('svg').attr('width', '24px');
	jQuery('#rating-svg-icon-preview').find('svg').attr('height', '24px');
}

// send a request to a server
// load SVG file source
function elm_rml_admin_load_svg(file) {
	// Check file type?
	// Check host?

    var request;
    if (window.XMLHttpRequest) {
        // IE7+, Firefox, Chrome, Opera, Safari
        request = new XMLHttpRequest();
    } else {
        // code for IE6, IE5
        request = new ActiveXObject('Microsoft.XMLHTTP');
    }

    // load
    request.open('GET', file, false);
    request.send();
    return request.responseText;
}
