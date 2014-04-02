;(function ($) {

	WPCP_Admin = {
		init: function(e) {
			// Validate license form
			this.License.validate();
		},
		License : {
			validate: function (e) {
                $('#license-form').validate({
                    rules: {
                        serial_key: {  // <-- name of actual text input
                            required: true
                        }
                    },
                    submitHandler: function (form) {
                        WPCP_Admin.License.activate.call(form);

                        return false;
                    }
                });
            },
			activate: function(e) {
				var that = $(this),
                    data = that.serialize() + '&_wpnonce=' + WPCP_Vars.nonce,
                    action = $( 'input[name=action]' ).val(),
                    buttonActiveLabel = action == 'wpcp_activate' ? 'Activating...' : 'Deactivating...';

                $( '#wpcp-activate' ).after( '<div class="wpcp-loading">'+ buttonActiveLabel +'</div>' );
                $( '#wpcp-activate' ).addClass( 'wpcp-button-active' ).val( buttonActiveLabel ).prop( 'disabled', true ).css({ 'cursor': 'default' });
                $( '.checked, .failed' ).remove();
                $.post(WPCP_Vars.ajaxurl, data, function(res) {
                    res = $.parseJSON(res);

                    if( res.success ) {
                        if ( action == 'wpcp_activate' ) {
                            $( '#wpcp-activate' ).val( 'Deactivate' );
                            $( 'input[name=action]' ).val( 'wpcp_deactivate' );
                            $( '#serial-key' ).prop( 'readonly', true ).addClass( 'serial-key-disabled' );
                            $( '#serial-key' ).after( '<span class="checked">Checked</span>' );
                        } else {
                            $( '#wpcp-activate' ).val( 'Activate' );
                            $( 'input[name=action]' ).val( 'wpcp_activate' );
                            $( '#serial-key' ).prop( 'readonly', false ).removeClass( 'serial-key-disabled' );
                        }
                    } else {
                        console.log(res.message);

                        $( '#serial-key' ).after( '<span class="failed">Failed</span>' );

                        if ( action == 'wpcp_activate' ) {
                            $( '#wpcp-activate' ).val( 'Activate' );
                            $( 'input[name=action]' ).val( 'wpcp_activate' );
                        } else {
                            $( '#wpcp-activate' ).val( 'Deactivate' );
                            $( 'input[name=action]' ).val( 'wpcp_deactivate' );
                        }
                    }

                    $( '#wpcp-activate' ).removeClass( 'wpcp-button-active' ).prop( 'disabled', false ).css({ 'cursor': 'pointer' });
                    $('.wpcp-loading').remove();
                });

                return false;
			}
		}
	}

    //dom ready
    $(function() {
    	WPCP_Admin.init();
    });

})(jQuery);