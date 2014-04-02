;(function ($) {
 	// Manual
	WPCP_Create_Manual = {
		init: function (e) {
			// Donnot fetch overlay if not in manual mode
            if ( $.cookie('wpcp-manual') != 1 ) return;

			var data = {
				action: 'wpcp_overlay_manual',
				'_wpnonce': WPCP_Vars.nonce
			}

			// Fetch overlay
			$.post( WPCP_Vars.ajaxurl, data, function(res) {
                res = $.parseJSON(res);

				if( res.success ) {
                	WPCP_Vars.ovarlayManual = res.html;
                	// Donnot allow add on our own page
                	if ( WPCP_Vars.screen_id == 'edit-wpcp_pointer' ) return;

                	// Show splash
					if ( WPCP_Vars.splash_dismissed != 1 )
						WPCP_Create_Manual.splash();
					else 
						Mousetrap.bind( 'ctrl+alt+n', WPCP_Create_Manual.open ); 

				} else {
                	console.log(res.error);
                }
			});
		},

		open: function (e) {
			// Donnot append when overlay is still in view
			if ( $('.wpcp-overlay').length ) return;

			// Attach overlay to the DOM
			$('body').append( WPCP_Vars.ovarlayManual );

			// If there are pointers already added, set order to the next number
			if ( $( '.wpcp-status-overlay' ).length )
				$('.wpcp-overlay-form #wpcp-order option[value='+ ( $( '.wpcp-status-overlay' ).length + 1 ) +']').attr('selected', 'selected');

			WPCP_Create_Manual.positionOverlay();

			// Attach submit event
			$('.wpcp-overlay').on('submit', '.wpcp-overlay-form', WPCP_Create_Manual.add);
			// Bind event to close
            $( '.wpcp-overlay' ).on( 'click', '.wpcp-close', WPCP_Create_Manual.close );
		},

		add: function (e) {
			e.preventDefault();

			// Bail out if not all fields are filled
			if ( !WPCP_Create_Manual.isFormFilled( $(this) ) )
				return;

			// Donnot allow removal of overlay for now
			WPCP_Vars.allowClose = false;

			// Inject hidden input for screen ID into our form
			$('.wpcp-overlay-form').prepend('<input type="hidden" name="screen" value="'+ WPCP_Vars.screen_id +'" />');  

			// Inject hidden input for page name into our form
			$('.wpcp-overlay-form').prepend('<input type="hidden" name="page" value="'+ WPCP_Vars.page +'" />'); 

			var that = $(this),
                data = that.serialize();

            $('.wpcp-overlay-form .footer .button-primary').before('<div class="wpcp-loading">Saving...</div>');
            $('.wpcp-overlay-form .footer .button-primary').addClass( 'wpcp-doing-ajax' );
            $('.wpcp-overlay-form .footer .button-primary').val('Saving...');
            $.post(WPCP_Vars.ajaxurl, data, function(res) {
            	res = $.parseJSON(res);

                if( res.success ) {
                	var height = $('.wpcp-overlay table').outerHeight(),
						width = $('.wpcp-overlay table').outerHeight();

                	$('.wpcp-overlay table').html('<p class="wpcp-notify">Custom pointer '+ WPCP_Vars.actionDone +'!</p>');
					$('.wpcp-overlay table').css({
						'width': width,
						'height': height
					});

					$('.wpcp-overlay .button-primary').remove();
					$('.wpcp-overlay .footer').html('<input type="button" class="button-primary" value="Okay" />');
					$('.wpcp-overlay .button-primary').on('click', function(){
						WPCP_Create_Manual.close();
					});
				} else {
                	console.log(res.error);
                }
            });
		},

		positionOverlay: function(e) {
			// Add dim effect
			$( '.wpcp-manual' ).before( '<div class="wpcp-dim" />' );
			// Append dim div
			if ( !$( '.wpcp-dim' ).length ) $( 'body' ).append( $( '.wpcp-dim' ) );
			
			$( '.wpcp-manual' ).css({
				'top': ( ( $( window ).height() - $( '.wpcp-manual' ).outerHeight() ) / 2 ) + 'px',
				'left': ( ( $( window ).width() - $( '.wpcp-manual' ).outerWidth() ) / 2 ) + 'px'
			});
		},

		isFormFilled: function (data) {
			var blank = 0;

			if ( $(data[0]).find('#wpcp-selector').val() == '' ) {
				$(data[0]).find('#wpcp-selector').css('border-color', 'red');
				++blank;
			}

			if ( $(data[0]).find('#wpcp-title').val() == '' ) {
				$(data[0]).find('#wpcp-title').css('border-color', 'red');
				++blank;
			}

			if ( $(data[0]).find('#wpcp-content').val() == '' ) {
				$(data[0]).find('#wpcp-content').css('border-color', 'red');
				++blank;
			}

			if ( blank )
				return false;

			return true;
		},

		close: function (e) {
			$('.wpcp-overlay, .wpcp-dim').remove();
		},

		splash: function (e) {
			var data = {
				action: 'wpcp_splash',
				'_wpnonce': WPCP_Vars.nonce
			}

			// Fetch overlay
			$.post( WPCP_Vars.ajaxurl, data, function(res) {
                res = $.parseJSON(res);

				if( res.success ) {
                	// Show splash
                	$( 'body' ).append( res.html );
                	// Add dim effect
					$( '.wpcp-splash' ).before( '<div class="wpcp-dim" />' );
					// Append dim div
					if ( !$( '.wpcp-dim' ).length ) $( 'body' ).append( $( '.wpcp-dim' ) );
                	// Position splash
                	$( '.wpcp-splash' ).css({  
                		'top': ( ( $( window ).height() - $( '.wpcp-splash' ).outerHeight() ) / 2 ) + 'px',
						'left': ( ( $( window ).width() - $( '.wpcp-splash' ).outerWidth() ) / 2 ) + 'px'
                	});
                	// Bind close event
                	$( '.wpcp-splash' ).on( 'click', '.button-primary', function(){ 
                		$( '.wpcp-splash, .wpcp-dim' ).remove();
                		Mousetrap.bind( 'ctrl+alt+n', WPCP_Create_Manual.open );  
                	});
                	// Bind event to dissmiss
                	$( '.wpcp-splash' ).on( 'click', '#wpcp-dismiss-splash', WPCP_Create_Manual.dismissSplash );
				} else {
                	console.log(res.error);
                }
			});
		},

		dismissSplash: function (e) {
			var data = {
				action: 'wpcp_dismiss_splash',
				'_wpnonce': WPCP_Vars.nonce
			}

			// Dimiss splash
			$.post( WPCP_Vars.ajaxurl, data, function(res) {
                res = $.parseJSON(res);

				if( res.success ) {
                	console.log( 'splash dismissed.' );
				} else {
                	console.log(res.error);
                }
			});
		}
	} 
})(jQuery);