jQuery(document).ready(function($) {

    /***** Uploading images *****/

    var file_frame;

    jQuery.fn.uploadMediaFile = function( button, preview_media ) {
        var button_id = button.attr('id');
        var field_id = button_id.replace( '_button', '' );
        var preview_id = button_id.replace( '_button', '_preview' );

        // If the media frame already exists, reopen it.
        if ( file_frame ) {
          file_frame.open();
          return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
          title: button.data( 'uploader_title' ),
          button: {
            text: button.data( 'uploader_button_text' ),
          },
          multiple: true
        });

        // When an image is selected, run a callback.
        file_frame.on( 'select', function() {
          var attachments = file_frame.state().get('selection').toJSON();
          var attachments_ids = [];
          for ( i in attachments ) {
          	attachments_ids.push( attachments[i].id );
          }
          jQuery( '#' + field_id ).val( attachments_ids.join() );
          if( preview_media ) {
          	for ( i in attachments ) {
          		if ( ! jQuery( "#" + preview_id + '-' + i ).length ) {
          			var lastPreview = jQuery('[id^="' + preview_id + '"]').last();
          			var newPreviewID = lastPreview.clone();
          			var newID = newPreviewID.attr( 'id' ).match( /(.+-)(\d+)/ );
          			newID = newID[1] + ( parseInt( newID[2] ) + 1 );
          			newPreviewID.attr( 'id', newID );
          			lastPreview.after( newPreviewID );
          		}
            	jQuery( '#' + preview_id + '-' + i ).attr( 'src', attachments[i].sizes.thumbnail.url );
            }
          }
          file_frame = false;
        });

        // Finally, open the modal
        file_frame.open();
    }

    jQuery('.image_upload_button').click(function() {
        jQuery.fn.uploadMediaFile( jQuery(this), true );
    });

    jQuery('.image_delete_button').click(function() {
        jQuery(this).siblings( '.image_data_field' ).val( '' );
        jQuery(this).siblings( '.image_preview' ).attr( 'src', '' );
        return false;
    });


	jQuery( '#add_slide' ).click( function( e ) {
		var newSlide = jQuery( this ).siblings( '#slides_wrapper' ).find( 'fieldset:last' ).clone();
		newSlide.find( 'input[type="text"]' ).val('').attr( 'name', function( i, str ) {
			str = str.match( /(slides\[)(\d+)(\]\[.+\])/ );
			str = str[1] + ( parseInt( str[2] ) + 1 ) + str[3];
			return str;
		});
		newSlide.find( 'img' ).attr( 'src', '' );
		newSlide.appendTo( '#slides_wrapper' );
	});

	jQuery( document.body ).on( 'click', '.remove_slide', function( e ) {
		jQuery( this ).closest( 'fieldset' ).remove();
	});

});