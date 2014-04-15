<?php

/**
 * Helpers
 */

/**
 * Check if the serial entered is valid
 * 
 * @param string $serial_key
 * @return array
 */
function wpcp_is_valid_serial_key( $serial_key ) {
    // API URL
    $url = 'http://apps.theportlandcompany.com/sts/api/v1/item/'.$serial_key;

    // cURL Resource 
    $ch = curl_init();

    // Set URL 
    curl_setopt( $ch, CURLOPT_URL, $url );

    // Tell cURL to return the output 
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

    // Tell cURL NOT to return the headers 
    curl_setopt( $ch, CURLOPT_HEADER, false );

    // Execute cURL, Return Data
    $data = curl_exec( $ch );

    // Check HTTP Code
    $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

    // Close cURL Resource 
    curl_close( $ch );

    $res = json_decode( $data );

    // If Response is not 200
    if ( $status == 200 && !$res->error ) {
        return array( 'success' => true, 'item_name' => $res->item_name );
    }

    return array( 'success' => false, 'message' => $res->message );
}

/**
 * Check if this IP is registered in the API
 *
 * @return array
 */
function wpcp_is_ip_registered() {
    // API URL
    $url = 'http://apps.theportlandcompany.com/sts/api/v1/ip/'.$_SERVER['SERVER_ADDR'];

    // cURL Resource 
    $ch = curl_init();

    // Set URL 
    curl_setopt( $ch, CURLOPT_URL, $url );

    // Tell cURL to return the output 
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

    // Tell cURL NOT to return the headers 
    curl_setopt( $ch, CURLOPT_HEADER, false );

    // Execute cURL, Return Data
    $data = curl_exec( $ch );

    // Check HTTP Code
    $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

    // Close cURL Resource 
    curl_close( $ch );

    $res = json_decode( $data );

    // If Response is not 200
    if ( $status == 200 && !$res->error ) {
        return array( 'success' => true );
    }

    return array( 'success' => false, 'message' => $res->message );
}

/**
 * Register IP Address and Domain Name where this plugin is installed
 *
 * @param string $serial_key
 * @return array
 */
function wpcp_register_server_info( $serial_key ) {
    global $wpcp;

    // API URL
    $url = 'http://apps.theportlandcompany.com/sts/api/v1/ip';

    // $_GET Parameters to Send 
    $params = array( 
        'item_name' => $wpcp->plugin_name,
        'serial_key' => $serial_key, 
        'ip' => $_SERVER['SERVER_ADDR'], 
        'domain_name' => $_SERVER['SERVER_NAME'] 
    );

    // Resource
    $ch = curl_init();

    // Set URL
    curl_setopt($ch, CURLOPT_URL, $url);

    // Tell cURL that this is a POST
    curl_setopt($ch, CURLOPT_POST, 1);

    // Bind params
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));

    // Receive server reponse
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute cURL
    $data = curl_exec($ch);

    // Check HTTP Code
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Close cURL Resource 
    curl_close($ch);

    $res = json_decode( $data );

    // If Response is not 200
    if ( $status == 200 && !$res->error ) {
        return array( 'success' => true );
    }

    return array( 'success' => false, 'message' => $res->message );
}

/**
 * Deactivate this plugin
 *
 * @param string $serial_key
 * @return array
 */
function wpcp_deactivate( $serial_key ) {
    // API URL
    $url = 'http://apps.theportlandcompany.com/sts/api/v1/ip/'.$_SERVER['SERVER_ADDR'].'_'.$serial_key;

    // Resource
    $ch = curl_init();

    // Set URL
    curl_setopt($ch, CURLOPT_URL, $url);

    // Delete
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

    // Follow location
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

    // Do not return http headers
    curl_setopt($ch, CURLOPT_HEADER, 0);  

    // Receive server reponse
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  

    // Execute cURL
    $data = curl_exec($ch);

    // Check HTTP Code
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    $res = json_decode( $data );

    // If Response is not 200
    if ( $status == 200 && !$res->error ) {
        return array( 'success' => true );
    }

    return array( 'success' => false, 'message' => $res->message );
}

/**
 * Checks if plugin is active
 *
 * @return boolean
 */
function wpcp_is_active() {
    if ( get_option( '_wpcp_status' ) != 'active' )
        return false;

    return true;
}

/**
 * Verify
 *
 * @return array
 */
function wpcp_verify() {
    // Check if this IP is registered
    $ip_registered = wpcp_is_ip_registered();
    // Check if serial is valid
    $serial_key_valid = wpcp_is_valid_serial_key( get_option( '_wpcp_sk' ) );

    // If both are not true, deactivate
    if ( !$ip_registered['success'] || !$serial_key_valid['success'] )
        return false;

    return true;
}


// Social Media Sharing Utility
function social_media_sharing_utility() {

    echo '
    	<div class="updated social-media-sharing-utility">
            
            <a class="ptp-nag-close button-secondary" href="' . $_SERVER['REQUEST_URI'] . '&dismiss_sharing_reminder_wpcp=true">Dismiss</a>
    	
            <div id="fb-root"></div>
            <script>(function(d, s, id) {
              var js, fjs = d.getElementsByTagName(s)[0];
              if (d.getElementById(id)) return;
              js = d.createElement(s); js.id = id;
              js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
              fjs.parentNode.insertBefore(js, fjs);
            }(document, \'script\', \'facebook-jssdk\'));</script>
            <script type="text/javascript">
              reddit_url = "http://www.theportlandcompany.com/product/custom-pointers-plugin-for-wordpress/";
              reddit_title = "Custom Pointers Plugin for WordPress let\'s Adminstrator\'s create interactive tutorials to train Users. It\'s awesome!";
              reddit_newwindow = 1;
            </script>
    	
    		<ul>
    ';
    			if ( $args['mini'] ):
    echo '<li><img src="' . $this->plugin_uri . '/extensions/sm-share-buttons/images/share_icon.png' . '" alt="Share"/></li>';

    endif;
    
    echo '
    			<li>Sharing this Plugin helps fund it! </li>
    			
    			<li><script type="text/javascript" src="http://www.reddit.com/static/button/button1.js"></script></li>
    			<li><div class="g-plus" data-action="share" data-annotation="bubble" data-height="24"></div>
    			<li><div class="fb-share-button" data-href="http://www.theportlandcompany.com/product/custom-pointers-plugin-for-wordpress/" data-type="button_count"></div></li>
    </li>
    			
    		</ul>
    		
            <!-- Place this tag after the last share tag. -->
            <script type="text/javascript">
              (function() {
                var po = document.createElement(\'script\'); po.type = \'text/javascript\'; po.async = true;
                po.src = \'https://apis.google.com/js/platform.js\';
                var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(po, s);
              })();
            </script>
    		
    	</div>
    ';

} // End of social_media_sharing_utility()