/**
 * Rating Manager Lite
 *
 * https://www.elementous.com
 * 
 */
jQuery(document).ready(function($) {

	var is_feedback_active = jQuery('.feedback-wrapper').length;

	if (is_feedback_active) {
		jQuery('.feedback-wrapper').hide();
		jQuery('.elm-feedback').hide();

		jQuery('.elm-leave-your-feedback').click(function() {
			jQuery(this).parent().find('.feedback-wrapper').toggle();
		});
	}

	// Process feedback form
	jQuery('.send-feedback').click(function() {
		var name = jQuery(this).parent().find('.feedback-name').val();
		var email = jQuery(this).parent().find('.feedback-email').val();
		var message = jQuery(this).parent().find('.feedback-message').val();
		var post_id = jQuery(this).parent().find('.feedback-post-id').val();
		var rating = jQuery(this).parent().find('.feedback-rating-value').val();
		var nonce = jQuery(this).parent().find('.feedback-nonce').val();
		var captcha = jQuery(this).parent().find('.feedback-captcha').val();

		var errors = new Array();
		var elm_this = this;

		if (!message) {
			errors.push(feedback_texts.feedback_required);
		}

		if (!name) {
			errors.push(feedback_texts.name_required);
		}

		if (!email) {
			errors.push(feedback_texts.email_required);
		}

		if (email && !elm_isValidEmailAddress(email)) {
			errors.push(feedback_texts.wrong_email);
		}

		if (errors.length !== 0) {
			var errors_string = errors.toString();
			var errors_string = errors_string.replace(/,/g, '<br />');

			jQuery(this).parent().parent().find('.feedback-errors').html(errors_string);
			jQuery(this).parent().parent().find('.feedback-errors').css('float', 'left');
		} else {
			jQuery(this).parent().parent().find('.feedback-errors').hide();

			var data = {
				action: 'elm_process_feedback',
				post_id: post_id,
				message: message,
				name: name,
				email: email,
				rating: rating,
				nonce: nonce,
				captcha: captcha
			};

			jQuery.post(ajaxurl, data, function(response) {
				jQuery(elm_this).parent().parent().parent().parent().find('.elm-leave-your-feedback').hide();
				jQuery(elm_this).parent().parent().parent().parent().find('.feedback-wrapper').html(response.message).fadeOut(3000);
				
			}, "json");
		}
	});
});

// Process rating form
function elm_rml_process(svg_source, options, nonce) {
	options.svg_source = svg_source;
		
	jQuery('.elm-rating').UR(options).one('ultimateratings.set', function(e, data) {
		var elm_this = this;
		var elm_data = data;
		var post_id = jQuery(elm_this).attr('class').split(' ')[1];

		var data = {
			action: 'elm_process_rating',
			post_id: post_id,
			value: data.rating,
			nonce: nonce
		};

		jQuery(elm_this).parent().parent().find('.elm-feedback').show();

		jQuery(elm_this).parent().parent().parent().find('.feedback-form').append('<input type=\"hidden\" name=\"feedback_rating_value\" class=\"feedback-rating-value\" value=\"' + elm_data.rating + '\" />');

		jQuery(elm_this).parent().parent().find('.elm-rating').html('<div class=\"elm-loading\"></div>');

		jQuery.post(ajaxurl, data, function(response) {
			jQuery(elm_this).parent().parent().find('.elm-rating').html('<div class=\"elm-rating-readonly\" data-elm-value=\"' + response.avg + '\" data-elm-readonly=\"true\"></div>');
			jQuery(elm_this).parent().parent().find('.elm-rating-readonly').UR(options);

			jQuery(elm_this).parent().find('.jq-ry-container').css('cursor', 'default');

			jQuery(elm_this).parent().parent().find('.elm-rating-stats').html(response.avg);
			
			if ( response.thankyou_msg ) {
				jQuery(elm_this).parent().parent().find('.elm-thankyou-msg').html(response.thankyou_msg).show().fadeOut(3000);
			}
		}, 'json');

		jQuery(this).unbind();
	});
}

// load SVG file
function elm_ultimate_ratings( file, options, ur_nonce ) {
	var xhr = new XMLHttpRequest();
	
	xhr.open("GET", file, true);
	xhr.onload = function (e) {
	  if (xhr.readyState === 4) {
		if (xhr.status === 200) {
			svg_source = xhr.responseText;
			
			elm_ultimate_ratings_callback( svg_source, options, ur_nonce );
		} else {
		  console.error(xhr.statusText);
		}
	  }
	};
	xhr.onerror = function (e) {
	  console.error(xhr.statusText);
	};
	xhr.send(null);
}

// Display read only ratings
function elm_rml_display( svg_source, options ) {
	options.svg_source = svg_source;
	
	jQuery('.elm-rating-readonly').each(function() {
		jQuery(this).UR(options);
	});
}

// AJAX callback after loading SVG file
// Setup rating form
function elm_ultimate_ratings_callback( svg_source, options, ur_nonce ) {
	elm_rml_display( svg_source, options );
	elm_rml_process( svg_source, options, ur_nonce ); 
}

function elm_isValidEmailAddress(emailAddress) {
	var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
	return pattern.test(emailAddress);
};
