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