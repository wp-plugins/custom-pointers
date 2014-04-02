<?php
/**
 * Pointer definition 
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WPCP_Pointer' ) ) {

class WPCP_Pointer {
    
    private static $instance;  

    public static function getInstance() {
        if ( !self::$instance ) {
            self::$instance = new WPCP_Pointer();
        }

        return self::$instance;
    }

    /**
     * Get all pointers based on screen id
     *
     * @param string $screen_id
     * @param string $page_name
     * @return array $pointers
     */
    public function get_pointers( $screen_id, $page_name ) {
        global $wpdb;

        $sql = "SELECT PointerID.meta_value AS 'pointer_id', Target.meta_value AS 'target', Edge.meta_value AS 'edge',";
        $sql .= " Align.meta_value AS 'align', Screen.meta_value AS 'screen_id',";
        $sql .= " OrderX.meta_value AS 'order', `ID` AS 'post_id', `post_content`, `post_title`, `term_taxonomy_id` AS 'collection'";
        $sql .= " FROM {$wpdb->posts}";
        $sql .= " JOIN {$wpdb->postmeta} PointerID ON {$wpdb->posts}.ID = PointerID.post_id"; 
        $sql .= " JOIN {$wpdb->postmeta} Target ON {$wpdb->posts}.ID = Target.post_id"; 
        $sql .= " JOIN {$wpdb->postmeta} Edge ON {$wpdb->posts}.ID = Edge.post_id"; 
        $sql .= " JOIN {$wpdb->postmeta} Align ON {$wpdb->posts}.ID = Align.post_id"; 
        $sql .= " JOIN {$wpdb->postmeta} Screen ON {$wpdb->posts}.ID = Screen.post_id"; 
        $sql .= " JOIN {$wpdb->postmeta} Page ON {$wpdb->posts}.ID = Page.post_id"; 
        $sql .= " JOIN {$wpdb->postmeta} OrderX ON {$wpdb->posts}.ID = OrderX.post_id"; 
        $sql .= " JOIN {$wpdb->term_relationships} ON {$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id";
        $sql .= " WHERE {$wpdb->posts}.post_type = '%s'";
        $sql .= " AND PointerID.meta_key = '%s'";
        $sql .= " AND Target.meta_key = '%s'";
        $sql .= " AND Edge.meta_key = '%s'";
        $sql .= " AND Align.meta_key = '%s'";
        $sql .= " AND Screen.meta_key = '%s'";
        $sql .= " AND Screen.meta_value = '%s'";
        $sql .= " AND Page.meta_key = '%s'";
        $sql .= " AND Page.meta_value = '%s'";
        $sql .= " AND OrderX.meta_key = '%s'";
        $sql .= " AND {$wpdb->posts}.post_status = '%s'";
        $sql .= " ORDER BY CAST( OrderX.meta_value AS UNSIGNED ) ASC LIMIT 999";

        $pointers = $wpdb->get_results( 
            $wpdb->prepare( 
                $sql,  
                'wpcp_pointer',
                '_wpcp_id', 
                '_wpcp_target', 
                '_wpcp_edge', 
                '_wpcp_align', 
                '_wpcp_screen', 
                $screen_id,
                '_wpcp_page',
                $page_name, 
                '_wpcp_order', 
                'publish' 
            ) 
        );

        return $pointers;
    }

    /**
     * Add pointer
     *
     * @param array $pointer 
     * @return int $post_id
     */
    public function add( $pointer = array() ) {
        $args = array(
            'post_title'    => $pointer['title'],
            'post_content'  => $pointer['content'],
            'post_status'   => 'publish', 
            'post_type'     => 'wpcp_pointer',
            'post_author'   => get_current_user_id(),
            'tax_input'     => array( 'wpcp_collection' => array( intval( $pointer['collection'] ) ) )
        );

        $post_id = wp_insert_post( $args );

        if ( !$post_id ) 
            return false;

        $metadata = array( 
            '_wpcp_id' => uniqid(), 
            '_wpcp_screen' => $pointer['screen'], 
            '_wpcp_page' => $pointer['page'], 
            '_wpcp_target'=> $pointer['target'], 
            '_wpcp_edge' => $pointer['edge'], 
            '_wpcp_align' => $pointer['align'], 
            '_wpcp_order' => $pointer['order'], 
        );

        $meta_ids = array();
        foreach ( $metadata as $key => $value ) {
            $meta_ids[] = add_post_meta( $post_id, $key, $value );
        }

        if ( sizeof( $meta_ids ) != sizeof( $metadata ) ) 
            return false;

        return $post_id;
    }

    /**
     * Update a pointer
     *
     * @param array $pointer
     * @return int $post_id
     */
    public function update( $pointer = array() ) {
        $args = array(
            'ID'            => $pointer['post_id'],
            'post_title'    => $pointer['title'],
            'post_content'  => $pointer['content'],
            'tax_input'     => array( 'wpcp_collection' => array( intval( $pointer['collection'] ) ) )
        );

        $post_id = wp_update_post( $args );

        if ( !$post_id ) 
            return false;

        $metadata = array( 
            '_wpcp_edge' => $pointer['edge'], 
            '_wpcp_align' => $pointer['align'], 
            '_wpcp_order' => $pointer['order'], 
        );

        $meta_ids = array();
        foreach ( $metadata as $key => $value ) {
            $meta_ids[] = update_post_meta( $post_id, $key, $value );
        }

        if ( sizeof( $meta_ids ) != sizeof( $metadata ) ) 
            return false;

        return $post_id;
    }

    /**
     * Delete a pointer
     *
     * @param int $id
     * @return boolean true|false
     */
    public function delete( $id ) {
        $result = wp_delete_post( $id );

        if ( !$result )
            return false;

        return true;
    }

} // end class

} // class_exists check