jQuery(document).ready(function(){

	var Plugin = {

		init:function(){

			// Cache elements
			Plugin.formAddTag = jQuery('#addtag');
			Plugin.formAddTagSubmit = Plugin.formAddTag.find('#submit');

			// Bindings
			Plugin.bindings();

		},

		bindings:function(){

			jQuery('form').on( 'click', '.add-term-thumbnail', function(e){
				e.preventDefault();
				var ajax = ( 1 == jQuery(this).data('ajax') ) ? true : false;
				Plugin.selectImage( jQuery(this), ajax );
			});

			jQuery('form').on( 'click', '.remove-term-thumbnail', function(e){
				e.preventDefault();
				var ajax = ( 1 == jQuery(this).data('ajax') ) ? true : false;
				Plugin.removeImage( jQuery(this), ajax );
			});

			Plugin.formAddTagSubmit.click(function(e){
				Plugin.clearAddTagForm();
			});

		},

		clearAddTagForm:function(){

			setTimeout(function(){
				if ( '' == Plugin.formAddTag.find('#tag-name' ).val() )
					Plugin.formAddTag.find('.remove-term-thumbnail').trigger('click');
			}, 1000 )

		},

		removeImage:function( button, ajax ){

			button.parent().find('.term-thumbnail').remove();
			button.parent().find('.add-term-thumbnail').show();
			button.parent().find('.remove-term-thumbnail').hide();

			if ( true === ajax ) {

				jQuery.post( ajaxurl, {action: 'delete-term-thumbnail', term_id: button.data('id') }, function(response){

				});

			} else {

				button.parent().find('input[name="term-thumbnail-id"]').val('');

			}

		},

		selectImage:function( button, ajax ){

			var fileFrame = wp.media({
				title: button.data('title'),
				library : {
					type : 'image'
				},
				button: {
					text: button.text()
				}
			});

			fileFrame.on( 'select', function() {

				selection = fileFrame.state().get('selection');

				if ( ! selection )
					return;

				var attachment_ids = [];
				selection.each( function( attachment ) {
					attachment_ids.push(attachment.attributes.id);
				});

				button.parent().find('.add-term-thumbnail').hide();
				button.parent().find('.remove-term-thumbnail').show();

				if ( true === ajax ) {

					jQuery.post( ajaxurl, {
						action: 'set-term-thumbnail',
						term_id: button.data('id'),
						attachment_id: attachment_ids.join(', ')
					}, function(response){
						button.parent().prepend( response );
					} );

				} else {

					jQuery( button.data('id-field') ).val(attachment_ids.join(', '));

					jQuery.post( ajaxurl, {
						action: 'get-term-thumbnail',
						attachment_id: attachment_ids.join(', ')
					}, function(response){
						button.parent().prepend( response );
					});

				}

			});

			fileFrame.open();

		}

	}

	Plugin.init();

});
