<?php
/**
 * Collection definition 
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WPCP_Collection' ) ) {

class WPCP_Collection {
    
    private static $instance;  

    public $pointers;
    public $raw;

    public static function getInstance() {
        if ( !self::$instance ) {
            self::$instance = new WPCP_Collection();
        }

        return self::$instance;
    }

    /**
     * Get collection
     *
     * @param string $screen_id
     * @param string $page_name
     * @return obj collection of pointers for the current screen
     */
    public function get( $screen_id, $page_name ) {
        $pointer_obj = WPCP_Pointer::getInstance();

        // Assign for later use
        $this->pointers = $pointer_obj->get_pointers( $screen_id, $page_name );

        // For public access
        $this->raw = $this->pointers;

        return $this->prepare();
    }

    /**
     * Get collection in a raw form
     *
     * @return resource $this->raw
     */
    public function get_raw() {
        if ( !$this->raw )
            return;

        return $this->raw;
    }


    /**
     * Prepare pointers
     * 
     * @return array $pointers
     */
    public function prepare() {
        // Bail out if there are no pointers queried
        if ( !$this->pointers )
            return;

        $pointers = array();

        foreach( $this->pointers as $pointer ) {
               
                $pointers[$pointer->pointer_id] = array(
                    'pointer_id' => $pointer->pointer_id,
                    'screen' => $pointer->screen_id,
                    'target' => $pointer->target,
                    'options' => array(
                        'content' => sprintf( '<h3> %s </h3> <p> %s </p>',
                            __( $pointer->post_title , 'wpcp' ),
                            __( $pointer->post_content, 'wpcp' )
                        ),
                        'position' => array( 
                            'edge' => $pointer->edge, 
                            'align' => $pointer->align 
                        )
                    )
                );
                
        }

        return $this->screen( $pointers );
    }

    /**
     * Only include pointers that have not been dismissed
     *
     * @param array $pointers
     * @return array $valid_pointers
     */
    public function screen( $pointers = array() ) {

        if ( ! $pointers || ! is_array( $pointers ) )
            return;

        // Get dismissed pointers
        $dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );

        // Check pointers and remove dismissed ones.
        foreach ( $pointers as $pointer_id => $pointer ) {

            // Make sure we have pointers & check if they have been dismissed
            if ( in_array( $pointer_id, $dismissed ) )
                unset( $pointers[ $pointer_id ] );
        }

        return array_values( $pointers );
    }

    /**
     *  Add collection
     *
     * @param string $title
     * @param string description
     * @return boolean 
     */
    public function add( $title, $description = '' ) {
        $term = wp_insert_term( $title, 'wpcp_collection', array(
                'description'=> $description
        ));

        if ( is_wp_error( $term ) )
            return false;

        return $term['term_id'];
    }


    /**
     * Restart collection
     *
     * @param array $pointers
     * @return boolean true|false
     */
    public function restart( $pointers = array() ) {
        $user_id = get_current_user_id();

        // Get dismissed pointers
        $dismissed = $dismissed_bak = explode( ',', (string) get_user_meta( $user_id, 'dismissed_wp_pointers', true ) );

        // Remove dismissed pointers of the current user for the current screen
        for ( $i = 0; $i < sizeof( $pointers ); $i++ ) {
            foreach ( $dismissed as $key => $value ) {
                if ( $pointers[$i]['pointer_id'] == $value ) 
                    unset( $dismissed[$key] );
            }
        }

        // Convert back to comma separated strings
        $dismissed = implode( ',', $dismissed );

        // Save back
        update_user_meta( $user_id, 'dismissed_wp_pointers', $dismissed );

        return true; 
    }

} // end class

} // class_exists check