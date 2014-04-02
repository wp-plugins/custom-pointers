;(function ($) {
	// Auto
	WPCP_Create_Auto = {
		init: function () {
			// Donnot fetch overlay if not in auto mode
            if ( $.cookie('wpcp-auto') != 1 ) return;

			var data = {
				action: 'wpcp_overlay_auto',
				'_wpnonce': WPCP_Vars.nonce
			}

			// Fetch overlay
			$.post( WPCP_Vars.ajaxurl, data, function(res) {
                res = $.parseJSON(res);

				if( res.success ) {
                	WPCP_Vars.ovarlayAuto = res.html;
                	// Donnot allow add on our own page
                	if ( WPCP_Vars.screen_id == 'edit-wpcp_pointer' ) return;
                	// Attach hover event to all elements in the DOM
					$(document).on('mouseover', WPCP_Create_Auto.overlay);
				} else {
                	console.log(res.error);
                }
			});
		},

		overlay: function (e) {
			// Bail out if overlay can't be closed
			if ( !WPCP_Vars.allowClose ) return;

			// Remove previous overlay
			WPCP_Create_Auto.close(e);

			if ( e.target.id ) { // If hovered element has ID
				// Set target element
				var target = $( '#' + e.target.id );

				// Set Class/ID with the Class/ID of the element underneath the status overlay
				if ( $( e.target ).hasClass( WPCP_Vars.statusOverlayClass ) )
					target = $( $( e.target ).data( WPCP_Vars.statusOverlayDataAttr ) );

				// Only detect element that fits criteria
				if ( !WPCP_Create_Auto.isValid( target ) )
					return;

				// Set WPCP_Vars.elName
				if ( $( e.target ).hasClass( WPCP_Vars.statusOverlayClass ) )
					WPCP_Vars.elName = $( e.target ).data( WPCP_Vars.statusOverlayDataAttr );
				else
					WPCP_Vars.elName = '#' + e.target.id;

				// Append overlay
				WPCP_Create_Auto.open();
        		$( '.wpcp-overlay' ).css({
        			'width': target.outerWidth() + 'px',
        			'height': target.outerHeight() + 'px',
        			'left': target.offset().left - 4 + 'px',
        			'top': target.offset().top - 4 + 'px',
        		});

        		// Position overlay
        		WPCP_Create_Auto.position( target );

        		return;

        	} else if ( $( '.' + $( e.target).attr( 'class' ) ).length == 1 ) { // If hovered element has no ID but with Class and only one element has that Class
        		// Set target element
        		var target = $( '.' + $( e.target ).attr( 'class' ) );

        		// Only detect element that fits criteria
				if ( !WPCP_Create_Auto.isValid( target ) )
					return;

				// Set WPCP_Vars.elName
				WPCP_Vars.elName = '.' + target.attr( 'class' );
        		
        		// Append overlay
        		WPCP_Create_Auto.open();

        		$( '.wpcp-overlay' ).css({
        			'width': target.outerWidth() + 'px',
        			'height': target.outerHeight() + 'px',
        			'left': target.offset().left - 4 + 'px',
        			'top': target.offset().top - 4 + 'px',
        		});

        		// Position overlay
        		WPCP_Create_Auto.position( target );

        		return;

        	} else { // If hovered element has no ID and Class

        		var selector = WPCP_Create_Auto.formSelector( $( e.target ) ),
        			target = $( selector );

        		if ( !target.length ) return;

        		// Only detect element that fits criteria
				if ( !WPCP_Create_Auto.isValid( target ) )
					return;

				// Set WPCP_Vars.elName
				WPCP_Vars.elName = selector;
        		
        		// Append overlay
        		WPCP_Create_Auto.open();

        		$( '.wpcp-overlay' ).css({
        			'width': target.outerWidth() + 'px',
        			'height': target.outerHeight() + 'px',
        			'left': target.offset().left - 4 + 'px',
        			'top': target.offset().top - 4 + 'px',
        		});

        		// Position overlay
        		WPCP_Create_Auto.position( target );

        		return;
        	} 
		},

		open: function () {
			// Attach overlay to the DOM
			$('body').append( WPCP_Vars.ovarlayAuto );

			// If user started typing, don't allow close
			$('.wpcp-overlay').on( 'click', function(e){
				// Donnot set to false when the element being clicked the the close button itself
				if ( $( e.target ).attr( 'class' ) != 'wpcp-close' )
					WPCP_Vars.allowClose = false;
			});

			// Inject WPCP_Vars.elName in the header of the overlay
			$('.wpcp-overlay .wpcp-el-name').empty().html( WPCP_Vars.elName );

			// Check if target has a pointer already
			var pointer = WPCP_Create_Auto.hasPointer();

			// Modify form so it'll update instead of add
			if ( pointer ) {
				// Inject hidden input for post ID into our form
				$('.wpcp-overlay-form').prepend('<input type="hidden" name="post_id" value="'+ pointer.post_id +'" />');

				// Populate title
				$('.wpcp-overlay-form #wpcp-title').val( pointer.post_title );

				// Populate content
				$('.wpcp-overlay-form #wpcp-content').val( pointer.post_content );

				// Set edge
				$('.wpcp-overlay-form #wpcp-edge option[value='+ pointer.edge +']').attr('selected', 'selected');

				// Set align
				$('.wpcp-overlay-form #wpcp-align option[value='+ pointer.align +']').attr('selected', 'selected');

				// Set collection
				$('.wpcp-overlay-form #wpcp-collection option[value='+ pointer.collection +']').attr('selected', 'selected');

				// Set order
				$('.wpcp-overlay-form #wpcp-order option[value='+ pointer.order +']').attr('selected', 'selected');

				// Inject delete link 
				$('.wpcp-overlay-form .footer').prepend( '<a class="wpcp-delete" data-id="'+ pointer.post_id +'" href="#">Delete</a>' ); 

				// Change button text to Update				
				$('.wpcp-overlay-form .button-primary').val( 'Update' ); 

				// Modify action done
				WPCP_Vars.actionDone = 'updated';

				// Change action to wpcp_update_pointer
				$('.wpcp-overlay-form #action').val( 'wpcp_update_pointer' ); 

				// Bind delete event to our delete anchor
				$('.wpcp-overlay-form').on('click', '.wpcp-delete', WPCP_Create_Auto.delete);

				// Disable Align values based the selected Edge value
				if ( $( '#wpcp-edge' ).val() == 'left' || $( '#wpcp-edge' ).val() == 'right' ) {
                    $( '#wpcp-align' ).children( 'option:disabled' ).prop( 'disabled', false );
                    $( '#wpcp-align' ).children( 'option[value=left], option[value=right]' ).prop( 'disabled', 'disabled' );
                } else {
                    $( '#wpcp-align' ).children( 'option:disabled' ).prop( 'disabled', false );
                    $( '#wpcp-align' ).children( 'option[value=top], option[value=bottom]' ).prop( 'disabled', 'disabled' );
                }
			} else {
				// Set order to next number
				$('.wpcp-overlay-form #wpcp-order option[value='+ ( $( '.wpcp-status-overlay' ).length + 1 ) +']').attr('selected', 'selected');
				// Set collection to last used collection
				if ( WPCP_Vars.pointers_raw )
					$('.wpcp-overlay-form #wpcp-collection option[value='+ WPCP_Vars.pointers_raw[WPCP_Vars.pointers_raw.length - 1]['collection'] +']').attr('selected', 'selected');
			}

			// Always append newly added collection to the dropdown
			if ( WPCP_Vars.newCollection.id ) {
            	$( '#wpcp-collection' ).append( '<option value="'+ WPCP_Vars.newCollection.id +' " selected>'+ WPCP_Vars.newCollection.name +'</option>' );
			}

			// Attach submit event
			$('.wpcp-overlay').on('submit', '.wpcp-overlay-form', WPCP_Create_Auto.add);
			// Bind event to close
            $( '.wpcp-overlay' ).on( 'click', '.wpcp-close', WPCP_Create_Auto.close );
		},

		close: function (e) {
			// Donnot close overlay if element being hovered is the overlay itself
			if ( $(e.target).attr('class') == 'wpcp-overlay' || $(e.target).parents('.wpcp-overlay').length && e.type == 'mouseover' )
				return;

			// Allow closing of overlay. Closing by clicking [x] sets this to false which will cause auto mode to stop working.
			WPCP_Vars.allowClose = true;


			if ( $('.wpcp-overlay').length && e.target.id != 'wpadminbar') // e.target.id != 'wpadminbar' is needed due to browser bug
				$('.wpcp-overlay').remove();
		},

		add: function (e) {
			e.preventDefault();

			// Bail out if not all fields are filled
			if ( !WPCP_Create_Auto.isFormFilled( $(this) ) )
				return;

			// Donnot allow removal of overlay for now
			WPCP_Vars.allowClose = false;

			// Inject hidden input for screen ID into our form
			$('.wpcp-overlay-form').prepend('<input type="hidden" name="screen" value="'+ WPCP_Vars.screen_id +'" />'); 

			// Inject hidden input for page name into our form
			$('.wpcp-overlay-form').prepend('<input type="hidden" name="page" value="'+ WPCP_Vars.page +'" />'); 

			// Inject hidden input for target element into our form
			$('.wpcp-overlay-form').prepend('<input type="hidden" name="target" value="'+ WPCP_Vars.elName +'" />'); 

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
						$('.wpcp-overlay').remove();
						// Allow removal of overlay
						WPCP_Vars.allowClose = true;
					});
				} else {
                	console.log(res.error);
                }
            });
		},

		delete: function (e) {
			// Donnot allow removal of overlay for now
			WPCP_Vars.allowClose = false;
			
			WPCP_Vars.actionDone = 'deleted',
				data = {
						post_id: $(this).data('id'),
						action: 'wpcp_delete_pointer',
						'_wpnonce': WPCP_Vars.nonce
					};

			$('.wpcp-overlay-form .footer .button-primary').before('<div class="wpcp-loading deleting">Deleting...</div>');
			$('.wpcp-overlay-form .footer .wpcp-delete').text( 'Deleting...' ).css( { 'text-decoration': 'none', 'color': '#6B6B6B' } );
			$.post( WPCP_Vars.ajaxurl, data, function(res) {
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
						$('.wpcp-overlay').remove();
						// Allow removal of overlay
						WPCP_Vars.allowClose = true;
					});
				} else {
                	console.log(res.error);
                }
			});
		},

		isFormFilled: function (data) {
			var blank = 0;

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

		isValid: function (el) {
			if ( ( el.children().length && $.inArray( el.prop( 'tagName' ).toLowerCase(), WPCP_Create_Auto.exceptions() ) === -1 ) || el.attr('id') == 'wp-admin-bar-wpcp-parent' ||  
				el.parents('#wp-admin-bar-wpcp-parent').length || el.attr('class') == 'wpcp-overlay' || el.parents('.wpcp-overlay').length ) { 
				
				return false;
			} else
				return true;
		},

		hasPointer: function () {
			if ( !WPCP_Vars.pointers_raw ) return false;

			for ( var i = 0; i < WPCP_Vars.pointers_raw.length; i++ ) {
	    		if ( WPCP_Vars.pointers_raw[i]['target'] == WPCP_Vars.elName && WPCP_Vars.pointers_raw[i]['screen_id'] == WPCP_Vars.screen_id ) {
	    			var pointer = { 
	    				post_id: WPCP_Vars.pointers_raw[i]['post_id'], 
	    				post_title: WPCP_Vars.pointers_raw[i]['post_title'],
	    				post_content: WPCP_Vars.pointers_raw[i]['post_content'],
	    				edge: WPCP_Vars.pointers_raw[i]['edge'],
	    				align: WPCP_Vars.pointers_raw[i]['align'],
	    				collection: WPCP_Vars.pointers_raw[i]['collection'],
	    				order: WPCP_Vars.pointers_raw[i]['order']
	    			};

	    			return pointer;
	    		}
	    	}

	    	return false;
		},

		formSelector: function(el, append) {

			var selector = '';

			if ( !el.siblings( el.prop( 'tagName' ).toLowerCase() ).length ) { // If this element has no siblings of the same tagname
				if ( el.parent().attr( 'id' ) || $( '.' + el.parent().attr( 'class' ) ).length == 1 ) { // If direct parent has ID or Class and nothing else is assigned with that class, use it as the end parent
					if ( el.parent().attr( 'id' ) )
						selector = '#' + el.parent().attr( 'id' ) + ' ' + el.prop( 'tagName' ).toLowerCase();
					else
						selector = '.' + el.parent().attr( 'class' ) + ' ' + el.prop( 'tagName' ).toLowerCase();

					return $.trim( append ? selector + ' ' + append : selector );
				} else {
					selector = el.prop( 'tagName' ).toLowerCase();

					return WPCP_Create_Auto.formSelector( el.parent(), $.trim( append ? selector + ' ' + append : selector ) );
				}
				
			} else if ( el.siblings( el.prop( 'tagName' ).toLowerCase() ).length &&  el.siblings( el.prop( 'tagName' ).toLowerCase() ).length == el.siblings().length ) { // If this element has siblings of the same tagname, possible list-like markup
				if ( el.parent().attr( 'id' ) || $( '.' + el.parent().attr( 'class' ) ).length == 1 ) { // If direct parent has ID or Class and nothing else is assigned with that class, use it as the end parent
					var nth = el.parent().children( el.prop( 'tagName' ).toLowerCase() ).index( el ) + 1;

					if ( el.parent().attr( 'id' ) )
						selector = '#' + el.parent().attr( 'id' ) + ' ' + el.prop( 'tagName' ).toLowerCase() + ':nth-child('+ nth +')';
					else
						selector = '.' + el.parent().attr( 'class' ) + ' ' + el.prop( 'tagName' ).toLowerCase() + ':nth-child('+ nth +')';

					return $.trim( append ? selector + ' ' + append : selector );
				} else {
					var nth = el.parent().children( el.prop( 'tagName' ).toLowerCase() ).index( el ) + 1;
					selector = el.prop( 'tagName' ).toLowerCase() + ':nth-child('+ nth +')';

					return WPCP_Create_Auto.formSelector( el.parent(), $.trim( append ? selector + ' ' + append : selector ) );
				}
			}
		},

		position: function (el) {
			// Right
			if ( ($(window).width() - (el.outerWidth() + el.offset().left)) <  $( '.wpcp-overlay-wrap' ).outerWidth() ) {
				$( '.wpcp-overlay-wrap' ).removeClass( 'wpcp-overlay-wrap-left' ).addClass( 'wpcp-overlay-wrap-right' );
			}

			// Top
			if ( el.offset().top <  5 ) {
				$( '.wpcp-overlay-wrap' ).removeClass().addClass( 'wpcp-overlay-wrap wpcp-overlay-wrap-top' );
				$( '.wpcp-overlay-wrap' ).css({
					'top': el.outerHeight() + 'px',
					'left': - ( ( $('.wpcp-overlay-wrap-top').outerWidth() - el.outerWidth() ) / 2 ) + 'px'
				});
				$('.wpcp-overlay-wrap span.arrow').css({
					'left': ( ( $( '.wpcp-overlay-wrap' ).outerWidth() - $('.wpcp-overlay-wrap span.arrow').outerWidth() ) / 2 ) + 'px'
				});
			}

			// Top Left Corner
			if ( el.offset().top <  5 && ( el.offset().left <  ( $( '.wpcp-overlay-wrap' ).outerWidth() - el.outerWidth() ) / 2 ) ) {
				$( '.wpcp-overlay-wrap' ).removeClass().addClass( 'wpcp-overlay-wrap wpcp-overlay-wrap-top-left' );
				$( '.wpcp-overlay-wrap' ).css({
					'left': - el.offset().left + 10 + 'px'
				});
				$('.wpcp-overlay-wrap span.arrow').css({
					'left': ( ( el.offset().left + ( el.outerWidth() / 2 ) ) - ( $('.wpcp-overlay-wrap span.arrow').outerWidth() ) ) + 'px'
				});
			}

			// Top Right Corner
			if ( el.offset().top <  5 && ( ($(window).width() - (el.outerWidth() + el.offset().left)) <  ( ( $( '.wpcp-overlay-wrap' ).outerWidth() - el.outerWidth() ) / 2 ) ) ) {
				$( '.wpcp-overlay-wrap' ).removeClass().addClass( 'wpcp-overlay-wrap wpcp-overlay-wrap-top-right' );
				$('.wpcp-overlay-wrap').css({
					'left': - ( $('.wpcp-overlay-wrap').outerWidth() - ( el.outerWidth() - 10 ) ) + 'px'
				});
				$('.wpcp-overlay-wrap span.arrow').css({
					'left': ( el.offset().left - $('.wpcp-overlay-wrap').offset().left ) + ( el.outerWidth() / 2 ) + 'px'
				});
			}

			// Bottom
			if ( $(window).height() - (el.offset().top + el.outerHeight()) < $( '.wpcp-overlay-wrap' ).outerHeight() && el.offset().top > $( '.wpcp-overlay-wrap' ).outerHeight() - el.outerHeight() ) {
				$( '.wpcp-overlay-wrap' ).removeClass().addClass( 'wpcp-overlay-wrap wpcp-overlay-wrap-not-fit-left-and-right-bottom' );

				$( '.wpcp-overlay-wrap' ).css({
					'top': - ( $( '.wpcp-overlay-wrap' ).outerHeight() ) + 'px',
					'left': ( el.outerWidth() - $( '.wpcp-overlay-wrap' ).outerWidth() ) / 2 + 'px'
				});
				$('.wpcp-overlay-wrap span.arrow').css({
					'top': $( '.wpcp-overlay-wrap' ).outerHeight() - 16 + 'px',
					'left': ( ( $( '.wpcp-overlay-wrap' ).outerWidth() - $('.wpcp-overlay-wrap span.arrow').outerWidth() ) / 2 ) + 'px'
				});
			}
			
			// Bottom Left
			if ( $(window).height() - (el.offset().top + el.outerHeight()) < $( '.wpcp-overlay-wrap' ).outerHeight() && el.offset().left <  $( '.wpcp-overlay-wrap' ).outerWidth() ) {
				$( '.wpcp-overlay-wrap' ).removeClass().addClass( 'wpcp-overlay-wrap wpcp-overlay-wrap-bottom-left' );

				$( '.wpcp-overlay-wrap' ).css({
					'left': 'auto',
					'top': - ( $( '.wpcp-overlay-wrap' ).outerHeight() - el.outerHeight() - 8 ) + 'px'
				});
				$('.wpcp-overlay-wrap span.arrow').css({
					'top': $( '.wpcp-overlay-wrap' ).outerHeight() - 20 + 'px',
					'left': '7px'
				});

				// If it overflows to the top
				if ( el.offset().top < $( '.wpcp-overlay-wrap' ).outerHeight() - el.outerHeight() - 8 ) {
					$( '.wpcp-overlay-wrap' ).css({
						'top': - ( Math.abs( $( '.wpcp-overlay-wrap' ).css('top').replace( /[^-\d\.]/g, '' ) ) - ( $( '.wpcp-overlay-wrap' ).outerHeight() - ( el.outerHeight() + 8 ) - el.offset().top ) ) + 10 + 'px'
					});
					$('.wpcp-overlay-wrap span.arrow').css({
						'top': ( el.offset().top - $('.wpcp-overlay-wrap').offset().top ) + ( ( el.outerHeight() / 2 ) - ( $('.wpcp-overlay-wrap span.arrow').outerHeight() / 2 ) ) + 'px'
					});
				}
			}	
			
			// Bottom Right
			if ( $(window).height() - (el.offset().top + el.outerHeight()) < $( '.wpcp-overlay-wrap' ).outerHeight() && ($(window).width() - (el.outerWidth() + el.offset().left)) <  $( '.wpcp-overlay-wrap' ).outerWidth() ) {
				$( '.wpcp-overlay-wrap' ).removeClass().addClass( 'wpcp-overlay-wrap wpcp-overlay-wrap-bottom-right' );

				$( '.wpcp-overlay-wrap' ).css({
					'left': '-364px',
					'top': - ( $( '.wpcp-overlay-wrap' ).outerHeight() - el.outerHeight() - 8 ) + 'px'
				});
				$('.wpcp-overlay-wrap span.arrow').css({
					'top': $( '.wpcp-overlay-wrap' ).outerHeight() - 20 + 'px',
					'left': 'auto',
					'right': '6px'
				});

				// If it overflows to the top
				if ( el.offset().top < $( '.wpcp-overlay-wrap' ).outerHeight() - el.outerHeight() - 8 ) {
					$( '.wpcp-overlay-wrap' ).css({
						'top': - ( Math.abs( $( '.wpcp-overlay-wrap' ).css('top').replace( /[^-\d\.]/g, '' ) ) - ( $( '.wpcp-overlay-wrap' ).outerHeight() - ( el.outerHeight() + 8 ) - el.offset().top ) ) + 10 + 'px'
					});
					$('.wpcp-overlay-wrap span.arrow').css({
						'top': ( el.offset().top - $('.wpcp-overlay-wrap').offset().top ) + ( ( el.outerHeight() / 2 ) - ( $('.wpcp-overlay-wrap span.arrow').outerHeight() / 2 ) ) + 'px'
					});
				}
			}

			// Bottom Left Corner
			// Bottom Right Corner

			// If the target element is too large for the overlay fit in either left and right sides, position it below or to the top
			if ( el.offset().left <  $( '.wpcp-overlay-wrap' ).outerWidth() && ($(window).width() - (el.outerWidth() + el.offset().left)) <  $( '.wpcp-overlay-wrap' ).outerWidth() ) {
				
				if ( el.offset().top <  $( '.wpcp-overlay-wrap' ).outerHeight() ) { // If no space at the top
					$( '.wpcp-overlay-wrap' ).removeClass().addClass( 'wpcp-overlay-wrap wpcp-overlay-wrap-not-fit-left-and-right-top' );

					$( '.wpcp-overlay-wrap' ).css({
						'top': el.outerHeight() + 'px',
						'left': ( el.outerWidth() - $( '.wpcp-overlay-wrap' ).outerWidth() ) / 2 + 'px'
					});
					$('.wpcp-overlay-wrap span.arrow').css({
						'left': ( ( $( '.wpcp-overlay-wrap' ).outerWidth() - $('.wpcp-overlay-wrap span.arrow').outerWidth() ) / 2 ) + 'px'
					});
				} else if ( ($(window).height() - (el.outerHeight() + el.offset().top)) <  $( '.wpcp-overlay-wrap' ).outerHeight() ) { // If no space at the bottom
					$( '.wpcp-overlay-wrap' ).removeClass().addClass( 'wpcp-overlay-wrap wpcp-overlay-wrap-not-fit-left-and-right-bottom' );

					$( '.wpcp-overlay-wrap' ).css({
						'top': - ( $( '.wpcp-overlay-wrap' ).outerHeight() ) + 'px',
						'left': ( el.outerWidth() - $( '.wpcp-overlay-wrap' ).outerWidth() ) / 2 + 'px'
					});
					$('.wpcp-overlay-wrap span.arrow').css({
						'top': $( '.wpcp-overlay-wrap' ).outerHeight() - 16 + 'px',
						'left': ( ( $( '.wpcp-overlay-wrap' ).outerWidth() - $('.wpcp-overlay-wrap span.arrow').outerWidth() ) / 2 ) + 'px'
					});
				}
			}

			// If the target element is too large for the overlay to fit in any side, center it 
			if ( el.offset().left <  $( '.wpcp-overlay-wrap' ).outerWidth() && ($(window).width() - (el.outerWidth() + el.offset().left)) <  $( '.wpcp-overlay-wrap' ).outerWidth() && 
				 el.offset().top <  $( '.wpcp-overlay-wrap' ).outerHeight() && ($(window).height() - (el.outerHeight() + el.offset().top)) <  $( '.wpcp-overlay-wrap' ).outerHeight() ) {
				$( '.wpcp-overlay-wrap' ).removeClass().addClass( 'wpcp-overlay-wrap wpcp-overlay-wrap-not-fit-any-side' );

				if ( el.outerHeight() ) { // If element's height is greater than the overlay
					$( '.wpcp-overlay-wrap' ).css({
						'top': ( el.outerHeight() - $( '.wpcp-overlay-wrap' ).outerHeight() ) / 2 + 'px',
						'left': ( el.outerWidth() - $( '.wpcp-overlay-wrap' ).outerWidth() ) / 2 + 'px'
					});
				} else {
					$( '.wpcp-overlay-wrap' ).css({
						'top': - (( $( '.wpcp-overlay-wrap' ).outerHeight() - el.outerHeight() ) / 2) + 'px',
						'left': ( el.outerWidth() - $( '.wpcp-overlay-wrap' ).outerWidth() ) / 2 + 'px'
					});
				}
			}

			return;
		},

		exceptions: function () {
			// Arrray els stores all elements that are excluded from target elements that should not be detected because they have children
			var els = [ 'select', 'a' ]; // Add some more exceptions in the future

			return els;
		}

	}
})(jQuery);