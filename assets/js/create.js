;(function ($) {
	// Global vars
	WPCP_Vars.ovarlayAuto = '',
	WPCP_Vars.ovarlayManual = '',
	WPCP_Vars.elName = '',
	WPCP_Vars.allowClose = true,
	WPCP_Vars.actionDone = 'created',
	WPCP_Vars.statusOverlayClass = 'wpcp-status-overlay',
	WPCP_Vars.statusOverlayDataAttr = 'idclass',
	WPCP_Vars.newCollection = {};

	// Control
	WPCP_Create = {
		init: function () {
			if ( $.cookie('wpcp-auto') == 1 || $.cookie('wpcp-manual') == 1 ) {
				// Delay it a little to allow elements added by javascripts are loaded first
           	 	setTimeout(function(){
					WPCP_Create.statusOverlay();
                    WPCP_Create_Auto.init();
                    WPCP_Create_Manual.init();
				}, 1000 );
           	}

            if ( WPCP_Vars.active == 'yes' )
                $( document ).on( 'keypress', '.wpcp-new-collection', WPCP_Create.quickAddCollection ); // Quick add collection

            // Toggle on/off collection dropdown
            $( document ).on( 'click', '.wpcp-add-collection', function(){
                $( this ).closest( 'p' ).fadeOut(function(){
                    $( this ).closest( 'p' ).next().fadeIn();
                });
            });
            $( document ).on( 'click', '.wpcp-cancel-add-collection', function(){
                $( this ).closest( 'p' ).fadeOut(function(){
                    $( this ).closest( 'p' ).prev().fadeIn();
                });
            });

            // Disable Align values based the selected Edge value
            $( document ).on( 'change', '#wpcp-edge', function(){
                if ( $( this ).val() == 'left' || $( this ).val() == 'right' ) {
                    $( '#wpcp-align' ).children( 'option:disabled' ).prop( 'disabled', false );
                    $( '#wpcp-align' ).children( 'option[value=left], option[value=right]' ).prop( 'disabled', 'disabled' );
                } else {
                    $( '#wpcp-align' ).children( 'option:disabled' ).prop( 'disabled', false );
                    $( '#wpcp-align' ).children( 'option[value=top], option[value=bottom]' ).prop( 'disabled', 'disabled' );
                }
            });

            // Tooltip
            $( document ).on( 'mouseover', '.wpcp-overlay .tool-tip-icon', WPCP_Create.toggleTooltip );
            $( document ).on( 'mouseout', '.wpcp-overlay .tool-tip-icon', WPCP_Create.toggleTooltip );

            // Toggles for auto/manual/stop modes.
            $('#wp-admin-bar-wpcp-auto').on('click', '> a', WPCP_Create.auto);
            $('#wp-admin-bar-wpcp-manual').on('click', '> a', WPCP_Create.manual);
            $('#wp-admin-bar-wpcp-stop').on('click', '> a', WPCP_Create.stop);

            // Indicate which mode is currently running
            if ( $.cookie( 'wpcp-auto' ) == 1 || $.cookie( 'wpcp-manual' ) == 1 ) {
                $('#wp-admin-bar-wpcp-parent > a').addClass( 'running' );

                // Change text of the admin bar menu depending on the state
                if ( $.cookie( 'wpcp-auto' ) == 1 )
                    $('#wp-admin-bar-wpcp-parent > a').text( 'Auto' );
                else if ( $.cookie( 'wpcp-manual' ) == 1 )
                    $('#wp-admin-bar-wpcp-parent > a').text( 'Manual' );
                    
            } else {
                $('#wp-admin-bar-wpcp-parent > a').removeClass( 'running' );
                $('#wp-admin-bar-wpcp-parent > a').text( '' );
            }
		},

		auto: function (e) {
			e.preventDefault();

			// Set to auto
			$.cookie( 'wpcp-auto', 1 );
			// Disable manual
			$.cookie( 'wpcp-manual', 0 );
            // Remove stopped indication
            $.cookie( 'wpcp-stopped', 0 );

			window.location.reload();
		},

		manual: function (e) {
			e.preventDefault();

			// Disable auto
			$.cookie( 'wpcp-auto', 0 );
			// Enable manual
			$.cookie( 'wpcp-manual', 1 );;
            // Remove stopped indication
            $.cookie( 'wpcp-stopped', 0 );

			window.location.reload();
		},

		stop: function (e) {
			e.preventDefault();

			// Disable auto
			$.cookie( 'wpcp-auto', 0 );
			// Disable manual
			$.cookie( 'wpcp-manual', 0 );
            // Indicate that add mode was stopped. This will be used to prevent newly added pointers to appear upon stopping add mode.
            $.cookie( 'wpcp-stopped', 1 );

			window.location.reload();
		},

		statusOverlay: function (e) {
			if ( !WPCP_Vars.pointers_raw ) return;

			// Donnot allow add on our own page
            if ( WPCP_Vars.screen_id == 'edit-wpcp_pointer' ) return;

			for ( var i = 0; i < WPCP_Vars.pointers_raw.length; i++ ) {
				// Add a border effect around element that has a pointer				
				$( 'body' ).append( '<div class="wpcp-status-overlay" id="wpcp-status-overlay-'+ i +'" data-idclass="'+ WPCP_Vars.pointers_raw[i]['target'] +'" />' );

				if ( !$( '#wpcp-status-overlay-'+ i ).length || !$( WPCP_Vars.pointers_raw[i]['target'] ).length ) return;

				var offset = $( WPCP_Vars.pointers_raw[i]['target'] ).offset();

	    		$( '#wpcp-status-overlay-'+ i ).css({
	    			'width': $( WPCP_Vars.pointers_raw[i]['target'] ).outerWidth() + 'px',
        			'height': $( WPCP_Vars.pointers_raw[i]['target'] ).outerHeight() + 'px',
        			'left': offset.left - 4 + 'px',
        			'top': offset.top - 4 + 'px',
	    		});
	    	}
		},

        quickAddCollection: function (e) {
            if ( WPCP_Vars.active != 'yes' ) return;

            var that = $( this ),
                code = e.keyCode || e.which; 

            // If Enter is not pressed
            if ( code != 13 ) return;

            e.preventDefault();

            var data = {
                        action: 'wpcp_add_collection',
                        title: that.val(),
                        '_wpnonce': WPCP_Vars.nonce
                    };

            $( '.wpcp-cancel-add-collection' ).hide();
            $( '.wpcp-new-collection' ).after( '<div class="wpcp-loading adding-collection">Saving...</div>' );

            $.post( WPCP_Vars.ajaxurl, data, function(res) {
                res = $.parseJSON(res);

                if( res.success ) {
                    // Assign for later use
                    WPCP_Vars.newCollection.id = res.term_id; 
                    WPCP_Vars.newCollection.name = that.val(); 

                    // Append newly added collection to the dropdown
                    $( '#wpcp-collection' ).append( '<option value="'+ res.term_id +'">'+ that.val() +'</option>' );
                    // Deselect pre-selected option
                    $( '#wpcp-collection' ).children( 'option:selected' ).prop( 'selected', false );
                    // Set newly added collection as selected
                    $( '#wpcp-collection' ).children( 'option[value='+ res.term_id +']' ).prop( 'selected', 'selected' );
                    // Remove spinner
                    $( '.adding-collection' ).remove();
                    // Unhide cancel button
                    $( '.wpcp-cancel-add-collection' ).show();
                    // Put the dropdown back in place
                    $( '.wpcp-cancel-add-collection' ).trigger( 'click' );
                } else {
                    console.log( res.error );
                }
            });
        },

        toggleTooltip: function (e) {
            var that = $( this );

            // Remove if present
            if ( $( '.wpcp-overlay .tool-tip' ).length ) {
                $( '.wpcp-overlay .tool-tip' ).remove();
                return;
            }

            // Append tooltip
            that.after( '<div class="tool-tip"></div>' );
            // Inject text
            $( '.wpcp-overlay .tool-tip' ).html( that.data( 'text' ) ).css({
                'left': that.position().left - ( ( $( '.wpcp-overlay .tool-tip' ).outerWidth() - that.outerWidth() ) / 2 ) + 'px',
                'top': that.position().top + that.outerHeight() + 6 + 'px' 
            });
        }
	}

    //dom ready
    $(function() {
    	// Initialize create
    	WPCP_Create.init();
    });

})(jQuery);