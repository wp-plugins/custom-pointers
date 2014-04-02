<?php

/**
 * Description of ajax
 */
class WPCP_Ajax {

    public function __construct() {
        add_action( 'wp_ajax_wpcp_overlay_auto', array( $this, 'overlay_auto') );
        add_action( 'wp_ajax_wpcp_overlay_manual', array( $this, 'overlay_manual') );

        add_action( 'wp_ajax_wpcp_add_pointer', array( $this, 'add_pointer') );
        add_action( 'wp_ajax_wpcp_update_pointer', array( $this, 'update_pointer') );
        add_action( 'wp_ajax_wpcp_delete_pointer', array( $this, 'delete_pointer') );

        add_action( 'wp_ajax_wpcp_restart_collection', array( $this, 'restart_collection') );

        add_action( 'wp_ajax_wpcp_add_collection', array( $this, 'add_collection') );

        add_action( 'wp_ajax_wpcp_splash', array( $this, 'splash') );
        add_action( 'wp_ajax_wpcp_dismiss_splash', array( $this, 'dismiss_splash') );

        add_action( 'wp_ajax_wpcp_activate', array( $this, 'activate' ) );
        add_action( 'wp_ajax_wpcp_deactivate', array( $this, 'deactivate' ) );
    }

    /**
     * Returns form overlay
     * @return json object
     */
    public function overlay_auto() {
        check_ajax_referer( 'wpcp_nonce' );

        echo json_encode(array(
            'success' => true,
            'html' => wpcp_overlay_auto()
        ));

        exit;
    }

    /**
     * Returns form overlay
     * @return json object
     */
    public function overlay_manual() {
        check_ajax_referer( 'wpcp_nonce' );

        echo json_encode(array(
            'success' => true,
            'html' => wpcp_overlay_manual()
        ));

        exit;
    }

    /**
     * Creates pointer
     * @return json object
     */
    public function add_pointer() {
        check_ajax_referer( 'wpcp_add_pointer', 'wpcp_nonce' );

        $pointer_obj = WPCP_Pointer::getInstance();
        $result = $pointer_obj->add( $_POST );

        if ( !$result ) {
            echo json_encode(array(
                'success' => false,
                'error' => $result
            ));

            exit;
        }

        echo json_encode(array(
            'success' => true,
        ));

        exit;
    }

    /**
     * Updates a pointer
     * @return json object
     */
    public function update_pointer() {
        check_ajax_referer( 'wpcp_add_pointer', 'wpcp_nonce' );

        $pointer_obj = WPCP_Pointer::getInstance();
        $result = $pointer_obj->update( $_POST );

        if ( !$result ) {
            echo json_encode(array(
                'success' => false,
                'error' => $result
            ));

            exit;
        }

        echo json_encode(array(
            'success' => true,
        ));

        exit;
    }

    /**
     * Deletes a pointer
     * @return json object
     */
    public function delete_pointer() {
        check_ajax_referer( 'wpcp_nonce' );

        $pointer_obj = WPCP_Pointer::getInstance();
        $result = $pointer_obj->delete( $_POST['post_id'] );

        if ( !$result ) {
            echo json_encode(array(
                'success' => false,
                'error' => $result
            ));

            exit;
        }

        echo json_encode(array(
            'success' => true,
        ));

        exit;
    }

    /**
     * Resets collection for the current screen
     * @return json object
     */
    public function restart_collection() {
        check_ajax_referer( 'wpcp_nonce' );

        $collection_obj = WPCP_Collection::getInstance();
        $result = $collection_obj->restart( $_POST['pointers'] );

        if ( !$result ) {
            echo json_encode(array(
                'success' => false,
                'error' => $result
            ));

            exit;
        }

        echo json_encode(array(
            'success' => true
        ));

        exit;
    }

    /**
     * Add collection
     * @return json object
     */
    public function add_collection() {
        check_ajax_referer( 'wpcp_nonce' );

        $collection_obj = WPCP_Collection::getInstance();
        $result = $collection_obj->add( $_POST['title'] );

        if ( !$result ) {
            echo json_encode(array(
                'success' => false,
                'error' => $result
            ));

            exit;
        }

        echo json_encode(array(
            'success' => true,
            'term_id' => $result
        ));

        exit;
    }

     /**
     * Returns splash
     * @return json object
     */
    public function splash() {
        check_ajax_referer( 'wpcp_nonce' );

        echo json_encode(array(
            'success' => true,
            'html' => wpcp_splash()
        ));

        exit;
    }

     /**
     * Returns splash
     * @return json object
     */
    public function dismiss_splash() {
        check_ajax_referer( 'wpcp_nonce' );

        update_user_meta( get_current_user_id(), '_wpcp_splash_dismissed', 1 );

        echo json_encode(array(
            'success' => true
        ));

        exit;
    }

    /**
     * Activate this plugin
     *
     * @return json object
     */
    public function activate() {
        check_ajax_referer( 'wpcp_nonce' );

        global $wpcp;

        // Check if serial key is valid
        $res = wpcp_is_valid_serial_key( $_POST['serial_key'] );
        if ( !$res['success'] ) {
            echo json_encode(array(
                'success' => false,
                'message' => $res['message']
            ));

            exit;
        }

        // Check if item names match
        if ( $res['item_name'] != $wpcp->plugin_name ) {
            echo json_encode(array(
                'success' => false,
                'message' => 'The serial key your using is not for '.$wpcp->plugin_name
            ));

            exit;
        }

        // Register this IP
        $res = wpcp_register_server_info( $_POST['serial_key'] );
        if ( !$res['success'] ) {
            echo json_encode(array(
                'success' => false,
                'message' => $res['message']
            ));

            exit;
        }

        update_option( '_wpcp_status', 'active' );
        update_option( '_wpcp_sk', $_POST['serial_key'] );

        echo json_encode(array(
            'success' => true,
        ));

        exit;
    }

    /**
     * Dectivate this plugin
     *
     * @return json object
     */
    public function deactivate() {
        check_ajax_referer( 'wpcp_nonce' );

        $res = wpcp_deactivate( $_POST['serial_key'] );
        if ( !$res['success'] ) {
            echo json_encode(array(
                'success' => false,
                'message' => $res['message']
            ));

            exit;
        }

        delete_option( '_wpcp_status' );

        echo json_encode(array(
            'success' => true,
        ));

        exit;
    }

}