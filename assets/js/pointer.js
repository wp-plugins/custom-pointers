;(function ($) {
    // Global vars
    WPCP_Vars.currentPointer = 0;
    WPCP_Vars.wickedPointers = [];

    WPCP_Pointer = {
        init: function (e) {
            if ( WPCP_Vars.active == 'yes' )
                $( document ).on( 'click', '.wpcp-restart-collection', WPCP_Pointer.restart ); // Restart collection

            // Hide pointers when help pane is shown
            $( '#contextual-help-link' ).on( 'click', WPCP_Pointer.togglePointers );

            // If no pointers for this page, bail out
            if ( !WPCP_Vars.pointers ) return;

            if ( WPCP_Vars.active == 'yes' ) {
                // Turn off Create Mode OR Restart Collection first 
                if ( $.cookie( 'wpcp-auto' ) == 1 || $.cookie( 'wpcp-manual' ) == 1 || $.cookie( 'wpcp-stopped' ) == 1 || WPCP_Vars.pointers.length < WPCP_Vars.pointers_raw.length ) return;
            }

            // Delay it a little to allow elements added by javascripts are loaded first
            setTimeout(function(){
                WPCP_Pointer.start(); // Render poiners
            
                if ( $( '.wp-pointer' ).length > 1 ) {
                    // Hide all except the first 
                    $( '.wp-pointer:not(:first)' ).hide();
                    if ( WPCP_Vars.active == 'yes' )
                        $( '.wp-pointer:not(:first)' ).find( '.wp-pointer-buttons' ).prepend( '<a href="#" class="button-primary back" >Back</a>' ); // Add back button

                    // Change all action to Next except the last
                    $( '.wp-pointer:not(:last)' ).find( '.close' ).addClass( 'button-primary next' ).removeClass( 'close' ).text( 'Next' );
                }

                $( '.wp-pointer:first' ).addClass( 'wp-current-pointer' );
                // Change last button text to "Done"
                $( '.wp-pointer:last' ).find( '.close' ).removeClass( 'next' ).addClass( 'button-primary' ).text( 'Done' );
                // Trigger next when next button is clicked
                $( '.wp-pointer .next' ).on( 'click', WPCP_Pointer.next );

                if ( WPCP_Vars.active == 'yes' )
                    $( '.wp-pointer .back' ).on( 'click', WPCP_Pointer.back ); // Trigger back when back button is clicked

                // If there are pointers displayed, change button text to 'Restart'
                if ( $( '.wp-pointer' ).length )
                    $( '.wpcp-restart-collection' ).val( 'Restart Tour!' );

                // Position pointer
                WPCP_Pointer.position();

            }, 1000 );
        },

        start: function (e) {
            var counter = 0;

            $.each(WPCP_Vars.pointers, function(i) {
                options = $.extend( WPCP_Vars.pointers[i].options, {
                    close: function() {
                        $.post( WPCP_Vars.ajaxurl, {
                            pointer: WPCP_Vars.pointers[i].pointer_id,
                            action: 'dismiss-wp-pointer'
                        });
                    }
                });
            
                $(WPCP_Vars.pointers[i].target).pointer( options ).pointer('open');

                // Collect rendered pointers
                WPCP_Vars.wickedPointers[counter++] = { 
                    index: i, 
                    edge: WPCP_Vars.pointers_raw[i].edge,
                    align: WPCP_Vars.pointers_raw[i].align, 
                    target: WPCP_Vars.pointers_raw[i].target 
                }; 
            });

            // Fix wicked pointers
            WPCP_Pointer.fix();
        },

        fix: function (e) {
            // This fixes pointers with arrow alignment issues. NOTE: This is a Wordpress issue. We're just fixing it.
           $.each( WPCP_Vars.wickedPointers, function(i){

                if ( WPCP_Vars.wickedPointers[i].edge == 'top' || WPCP_Vars.wickedPointers[i].edge == 'bottom' ) {
                    if ( WPCP_Vars.wickedPointers[i].align == 'left' ) {
                        if ( $( '.wp-pointer' ).eq( WPCP_Vars.wickedPointers[i].index ).find( '.wp-pointer-arrow' ).outerWidth() < $( WPCP_Vars.wickedPointers[i].target ).outerWidth() &&  
                            $( WPCP_Vars.wickedPointers[i].target ).outerWidth() <= $( '.wp-pointer' ).eq( WPCP_Vars.wickedPointers[i].index ).outerWidth() ) {

                            $( '.wp-pointer' ).eq( WPCP_Vars.wickedPointers[i].index ).find( '.wp-pointer-arrow' ).css({
                                'left': ( $( WPCP_Vars.wickedPointers[i].target ).outerWidth() - $( '.wp-pointer' ).eq( WPCP_Vars.wickedPointers[i].index ).find( '.wp-pointer-arrow' ).outerWidth() ) / 2 + 'px'
                            });
                        } else {
                            $( '.wp-pointer' ).eq( WPCP_Vars.wickedPointers[i].index ).find( '.wp-pointer-arrow' ).css({
                                'left': '3px'
                            });
                        }
                    } else if ( WPCP_Vars.wickedPointers[i].align == 'right' ) {
                        if ( $( '.wp-pointer' ).eq( WPCP_Vars.wickedPointers[i].index ).find( '.wp-pointer-arrow' ).outerWidth() < $( WPCP_Vars.wickedPointers[i].target ).outerWidth() &&
                            $( WPCP_Vars.wickedPointers[i].target ).outerWidth() <= $( '.wp-pointer' ).eq( WPCP_Vars.wickedPointers[i].index ).outerWidth() ) {
                            $( '.wp-pointer' ).eq( WPCP_Vars.wickedPointers[i].index ).find( '.wp-pointer-arrow' ).css({
                                'left': 'auto',
                                'right': ( $( WPCP_Vars.wickedPointers[i].target ).outerWidth() - $( '.wp-pointer' ).eq( WPCP_Vars.wickedPointers[i].index ).find( '.wp-pointer-arrow' ).outerWidth() ) / 2 + 'px'
                            });
                        } else {
                            $( '.wp-pointer' ).eq( WPCP_Vars.wickedPointers[i].index ).find( '.wp-pointer-arrow' ).css({
                                'left': 'auto',
                                'right': '3px'
                            });
                        }
                    } else if ( WPCP_Vars.wickedPointers[i].align == 'middle' ) {
                        $( '.wp-pointer' ).eq( WPCP_Vars.wickedPointers[i].index ).find( '.wp-pointer-arrow' ).css({
                            'left': ( $( '.wp-pointer' ).eq( WPCP_Vars.wickedPointers[i].index ).outerWidth() - $( '.wp-pointer' ).eq( WPCP_Vars.wickedPointers[i].index ).find( '.wp-pointer-arrow' ).outerWidth() ) / 2 + 'px'
                        });
                    }

                    // Fix for Edge Bottom. Pointer overlaps with the element. Reduce top value by 20px.
                    if ( WPCP_Vars.wickedPointers[i].edge == 'bottom' ) {
                        $( '.wp-pointer' ).eq( WPCP_Vars.wickedPointers[i].index ).css({ 'top': $( '.wp-pointer' ).eq( WPCP_Vars.wickedPointers[i].index ).css('top').replace( /[^-\d\.]/g, '' ) - 20 + 'px' });
                    }
                } else {
                    if ( WPCP_Vars.wickedPointers[i].align == 'top' ) {
                        if ( $( '.wp-pointer' ).eq( WPCP_Vars.wickedPointers[i].index ).find( '.wp-pointer-arrow' ).outerHeight() < $( WPCP_Vars.wickedPointers[i].target ).outerHeight() ) {
                            $( '.wp-pointer' ).eq( WPCP_Vars.wickedPointers[i].index ).find( '.wp-pointer-arrow' ).css({
                                'top': ( $( WPCP_Vars.wickedPointers[i].target ).outerHeight() - $( '.wp-pointer' ).eq( WPCP_Vars.wickedPointers[i].index ).find( '.wp-pointer-arrow' ).outerHeight() ) / 2 + 'px'
                            });
                        } else {
                            $( '.wp-pointer' ).eq( WPCP_Vars.wickedPointers[i].index ).find( '.wp-pointer-arrow' ).css({
                                'top': '20px'
                            });
                        }
                    } else if ( WPCP_Vars.wickedPointers[i].align == 'bottom' ) {
                        if ( $( '.wp-pointer' ).eq( WPCP_Vars.wickedPointers[i].index ).find( '.wp-pointer-arrow' ).outerHeight() < $( WPCP_Vars.wickedPointers[i].target ).outerHeight() ) {
                            $( '.wp-pointer' ).eq( WPCP_Vars.wickedPointers[i].index ).find( '.wp-pointer-arrow' ).css({
                                'top': 'auto',
                                'bottom': ( $( WPCP_Vars.wickedPointers[i].target ).outerHeight() - $( '.wp-pointer' ).eq( WPCP_Vars.wickedPointers[i].index ).find( '.wp-pointer-arrow' ).outerHeight() ) / 2 + 'px'
                            });
                        } else {
                            $( '.wp-pointer' ).eq( WPCP_Vars.wickedPointers[i].index ).find( '.wp-pointer-arrow' ).css({
                                'top': 'auto',
                                'bottom': '3px'
                            });
                        }
                    } 

                    // Fix for Edge Right. Pointer overlaps with the element. Reduce left value by 13px.
                    if ( WPCP_Vars.wickedPointers[i].edge == 'right' ) {
                        $( '.wp-pointer' ).eq( WPCP_Vars.wickedPointers[i].index ).css({ 'left': $( '.wp-pointer' ).eq( WPCP_Vars.wickedPointers[i].index ).css('left').replace( /[^-\d\.]/g, '' ) - 13 + 'px' });
                    }
                }
           });
        },

        restart: function (e) {
            e.preventDefault();

            if ( WPCP_Vars.active != 'yes' ) return;

            var data = {
                        action: 'wpcp_restart_collection',
                        pointers: WPCP_Vars.pointers_raw,
                        '_wpnonce': WPCP_Vars.nonce
                    },
                buttonText = $( '.wp-pointer' ).length ? 'Restarting...' : 'Starting...';

            $('.wpcp-help-content .button-primary').after('<div class="wpcp-loading restarting">Restarting...</div>');
            $('.wpcp-help-content .button-primary').addClass( 'wpcp-doing-ajax' );
            $('.wpcp-help-content .button-primary').val( buttonText );

            $.post( WPCP_Vars.ajaxurl, data, function(res) {
                res = $.parseJSON(res);

                if( res.success ) {
                    // Set this to 0 so pointers will be shown
                    $.cookie( 'wpcp-auto', 0 );
                    // Set this to 0 so pointers will be shown
                    $.cookie( 'wpcp-manual', 0 );
                    // Set this to 0 so pointers will be shown
+                    $.cookie( 'wpcp-stopped', 0 );
                    // Reload to restart collection
                    window.location.reload();
                } else {
                    console.log(res.error);
                }
            });
        },

        next: function (e) {
            $( '.wp-current-pointer').removeClass( 'wp-current-pointer' ).hide();
            $( '.wp-pointer').eq( ++WPCP_Vars.currentPointer ).addClass('wp-current-pointer').fadeIn();

            // Position pointer
            WPCP_Pointer.position();
        },

        back: function (e) {
            e.preventDefault();
            
            $( '.wp-current-pointer').removeClass( 'wp-current-pointer' ).hide();
            $( '.wp-pointer').eq( --WPCP_Vars.currentPointer ).addClass('wp-current-pointer').fadeIn();

            // Position pointer
            WPCP_Pointer.position();
        },

        togglePointers: function(e) {
            if ( $(this).hasClass( 'hidden' ) || !$(this).hasClass( 'shown' ) ) {
                $(this).removeClass( 'hidden' ).addClass( 'shown' );
                if ( $( '.wp-pointer' ).length )
                    $( '.wp-pointer' ).hide();
            } else if ( $(this).hasClass( 'shown' ) ) {
                $(this).removeClass( 'shown' ).addClass( 'hidden' );
                if ( $( '.wp-pointer' ).length > 1 ) {
                    $( '.wp-current-pointer' ).fadeIn();
                } else {
                    $( '.wp-pointer' ).fadeIn();
                }
            }
        },

        position: function(e) {
            if ( $( '#wpadminbar' ).length )
                $( 'body, html' ).animate({ scrollTop: $( '.wp-current-pointer').offset().top -48 }, 800 );
            else 
                $( 'body, html' ).animate({ scrollTop: $( '.wp-current-pointer').offset().top - 20 }, 800 );
        }
    }

    //dom ready
    $(function() {
        WPCP_Pointer.init();
    });

})(jQuery);