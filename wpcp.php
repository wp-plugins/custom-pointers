<?php
/*
Plugin Name: Custom Pointers for WordPress (Free)
Plugin URI: http://www.theportlandcompany.com/product/custom-pointers-plugin-for-wordpress/
Description: The Custom Pointers Plugin for WordPress introduces an administrative interface that enables Administrators to create a "Collection" custom "Pointers" quickly, easily and in an organized fashion. Fundamentally; it's a way to create interactive tutorials for your WordPress Users in the back end. This is built atop the "Feature Pointers" feature that was introduced in WordPress 3.3.
Author: The Portland Company, Designed by Spencer Hill, Coded by Redeye Adaya
Author URI: http://www.theportlandcompany.com
Version: 0.9.5
Copyright: 2014 The Portland Company 
License: GPL 2
*/


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WP_Custom_Pointers' ) ) {

// Bootstrap
class WP_Custom_Pointers {

    /**
     * @var object
     */
    private $pointer_obj;

    /**
     * @var object
     */
    private $collection_obj;

    /**
     * @var string
     */
    public $version = '0.9.1';

    /**
     * @var string
     */
    public $remote_version;

    /**
     * @var string
     */
    public $plugin_path;

    /**
     * @var string
     */
    public $plugin_uri;

    /**
     * @var string
     */
    public $current_screen;

    function __construct() {
        // Define WP_Custom_Pointers constant
        define( 'WPCP_VERSION', $this->version );

        // Get remote version
        $this->remote_version = $this->get_remote_version();

        // Admin notices
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );

        // Auto-load classes on demand
        spl_autoload_register( array( $this, 'autoload' ) );

        add_action( 'admin_init', array( $this, 'init' ) );
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_bar_menu', array( $this, 'admin_bar_node'), 999 );

        add_action( 'admin_enqueue_scripts', array($this, 'admin_scripts'), 2000 );

        add_action( 'init', array( $this, 'register_post_type' ) );
        add_action( 'init', array( $this, 'register_taxonomy' ) );

        add_action( 'admin_footer', array( $this, 'hide_add_new_link' ) );

        register_activation_hook( __FILE__, array( $this, 'install') );
        register_deactivation_hook( __FILE__, array( $this, 'uninstall') );
    }

    /**
     * Auto-load WP_Custom_Pointers classes on demand to reduce memory consumption.
     *
     * @access public
     * @param mixed $class
     * @return void
     */
    public function autoload( $class ) {

        $name = explode( '_', $class );

        if ( isset( $name[1] ) ) {
            $class_name = strtolower( $name[1] );

            $filename = dirname( __FILE__ ) . '/classes/' . $class_name . '.php';

            if ( file_exists( $filename ) ) {
                require_once $filename;
            }
        }
    }
    
    /**
     * Init WP_Custom_Pointers when WordPress Initialises.
     *
     * @access public
     * @return void
     */
    public function init() {
        $this->plugin_path = dirname( __FILE__ );
        $this->plugin_uri = plugins_url( '', __FILE__ );

        $ajax = new WPCP_Ajax();
        $this->pointer_obj = WPCP_Pointer::getInstance();
        $this->collection_obj = WPCP_Collection::getInstance();

        // Include required files
        $this->includes();
    }

    /**
     * Runs the setup when the plugin is installed
     */
    public function install() {
        update_option( '_wpcp_version', $this->version );
    }

    /**
     * Uninstall
     */
    public function uninstall() {
        delete_option( '_wpcp_version' );
        delete_option( '_wpcp_preloads_ran' );

        // Delete our pointers
        $pointers = $this->pointer_obj->get_pointers( 'edit-wpcp_pointer', 'edit.php' );
        foreach ( $pointers as $pointer ) {
            wp_delete_post( $pointer->post_id, true );
        }
    }

    /**
     * Load all the plugin scripts and styles
     *
     * @return void
     */
    public function admin_scripts() {
        if ( get_option( '_wpcp_preloads_ran' ) != 1 ) {
            // Load our preloads
            $this->preload();
            // Make sure we don't run this block again
            update_option( '_wpcp_preloads_ran', 1 );
        }

        // Set screen
        $this->current_screen = get_current_screen();

        // Enqueue scripts
        wp_enqueue_script( 'wp-pointer' );
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'wpcp-mousetrap', plugins_url( 'assets/js/mousetrap.min.js', __FILE__ ) );
        wp_enqueue_script( 'wpcp-cookie', plugins_url( 'assets/js/jquery.cookie.min.js', __FILE__ ) );
        wp_enqueue_script( 'wpcp-admin', plugins_url( 'assets/js/admin.js', __FILE__ ), '', '', true );
        wp_enqueue_script( 'wpcp-create-auto-status', plugins_url( 'assets/js/create.auto.status.js', __FILE__ ), '', '', true );
        wp_enqueue_script( 'wpcp-create-auto', plugins_url( 'assets/js/create.auto.js', __FILE__ ), '', '', true );
        wp_enqueue_script( 'wpcp-create-manual', plugins_url( 'assets/js/create.manual.js', __FILE__ ), '', '', true );
        wp_enqueue_script( 'wpcp-create', plugins_url( 'assets/js/create.js', __FILE__ ), '', '', true );
        wp_enqueue_script( 'wpcp-pointer', plugins_url( 'assets/js/pointer.js', __FILE__ ), '', '', true );

        // Localize some values
        wp_localize_script( 'wpcp-admin', 'WPCP_Vars', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'wpcp_nonce' ),
            'pointers' => $this->collection_obj->get( $this->current_screen->id, $this->get_page() ),
            'pointers_raw' => $this->collection_obj->get_raw(),
            'screen_id' => $this->current_screen->id,
            'page' => $this->get_page(),
            'splash_dismissed' => get_user_meta( get_current_user_id(), '_wpcp_splash_dismissed' )
        ) );

        // Enqueue styles
        wp_enqueue_style( 'wp-pointer' );
        wp_enqueue_style( 'jquery-ui', plugins_url( 'assets/css/jquery-ui-1.9.1.custom.css', __FILE__ ) );
        wp_enqueue_style( 'wpcp-admin', plugins_url( 'assets/css/admin.css', __FILE__ ) );
        wp_enqueue_style( 'wpcp-create', plugins_url( 'assets/css/create.css', __FILE__ ) );
        wp_enqueue_style( 'wpcp-pointer', plugins_url( 'assets/css/pointer.css', __FILE__ ) );

        // Add help tab
        $this->contextual_help();
    }

    /**
     * Helper functions
     *
     * @return void
     */
    public function includes() {
        require_once dirname( __FILE__ ) . '/includes/html.php';
        require_once dirname( __FILE__ ) . '/includes/preloads.php';
    }

    /**
     * Register the plugin menu
     *
     * @return void
     */
    public function admin_menu() {
        $capability = 'edit_posts'; //minimum level: editor

        add_submenu_page( 'edit.php?post_type=wpcp_pointer', __( 'Settings', 'wpcp' ), __( 'Settings', 'wpcp' ), $capability, 'wpcp_settings', array($this, 'admin_page_handler') );

        global $submenu;
        // Disable Add New tab
        unset( $submenu['edit.php?post_type=wpcp_pointer'][10] );
    }

    /**
     * Hide link
     *
     * @return string
     */
    public function hide_add_new_link() {
        if ( isset($_GET['post_type']) && $_GET['post_type'] == 'wpcp_pointer' ) {
            ?> <style type="text/css"> #icon-edit + h2 .add-new-h2 { display:none; } </style> <?php
        }
    }

    /**
     * Render admin pages
     *
     * @return string
     */
    public function admin_page_handler() {
        $get = $_GET;

        echo '<div class="wrap wpcp">';

        if ( $get['page'] == 'wpcp' ) {
            include_once dirname( __FILE__ ) . '/views/settings.php';
        } 
        echo '</div>';
    }

    /**
     * Add admin bar menu
     *
     * @return void
     */
    public function admin_bar_node( $wp_admin_bar ) {

        $args = array(
            'id'    => 'wpcp-parent',
            'title' => '',
            'href'  => '#',
        );
 
        $wp_admin_bar->add_node( $args );

        $args = array(
            'id'    => 'wpcp-auto',
            'parent' => 'wpcp-parent',
            'title' => 'Auto',
            'href'  => '#',
        );

        $wp_admin_bar->add_node( $args );

        $args = array(
            'id'    => 'wpcp-manual',
            'parent' => 'wpcp-parent',
            'title' => 'Manual',
            'href'  => '#',
        );

        $wp_admin_bar->add_node( $args );

        $args = array(
            'id'    => 'wpcp-stop',
            'parent' => 'wpcp-parent',
            'title' => 'Stop',
            'href'  => '#',
        );

        $wp_admin_bar->add_node( $args );
    }

    /**
     * Register custom post type
     * @return void
     */
    public function register_post_type() {
        $labels = array(
            'name' => 'Pointers',
            'singular_name' => 'Pointer',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Pointer',
            'edit_item' => 'Edit Pointer',
            'new_item' => 'New Pointer',
            'all_items' => 'All Pointers',
            'view_item' => 'View Pointer',
            'search_items' => 'Search Pointers',
            'not_found' =>  'No pointers found',
            'not_found_in_trash' => 'No pointers found in Trash', 
            'parent_item_colon' => '',
            'menu_name' => 'Pointers'
        );

          $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true, 
            'show_in_menu' => true, 
            'query_var' => true,
            'rewrite' => array( 'slug' => 'wpcp_pointer' ),
            'capability_type' => 'post',
            'has_archive' => true, 
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array(),
            'menu_icon' => plugins_url( '', __FILE__ ) . '/assets/images/logo.png'
        ); 

        register_post_type( 'wpcp_pointer', $args );
    }

    /**
     * Register custom taxonomy
     * @return void
     */
    public function register_taxonomy() {
        $labels = array(
            'name'                => _x( 'Collections', 'wpcp' ),
            'singular_name'       => _x( 'Collection', 'wpcp' ),
            'search_items'        => __( 'Search Collections' ),
            'all_items'           => __( 'All Collections' ),
            'parent_item'         => __( 'Parent Collection' ),
            'parent_item_colon'   => __( 'Parent Collection:' ),
            'edit_item'           => __( 'Edit Collection' ), 
            'update_item'         => __( 'Update Collection' ),
            'add_new_item'        => __( 'Add New Collection' ),
            'new_item_name'       => __( 'New Collection Name' ),
            'menu_name'           => __( 'Collections' )
        );    

        $args = array(
            'hierarchical'        => true,
            'labels'              => $labels,
            'show_ui'             => true,
            'show_admin_column'   => true,
            'query_var'           => true,
            'rewrite'             => array( 'slug' => 'wpcp_collection' )
        );

        register_taxonomy( 'wpcp_collection', array( 'wpcp_pointer' ), $args );

        // Create our own term for our own collections
        if ( !get_option( '_wpcp_term_id_self' ) ) {
            $term = wp_insert_term(
                'WP Custom Pointers', 
                'wpcp_collection', 
                array(
                    'description'=> 'Collection of pointers for this plugin.'
                )
            );

            if ( !is_wp_error( $term ) )
                update_option( '_wpcp_term_id_self', $term['term_id'] ); // Save our term id
        }
    }

    /**
     * Contextual help
     *
     * @return void
     */
    public function contextual_help() {
        if ( !$this->collection_obj->get_raw() )
            $content = wpcp_contextual_help_content( false );
        else
            $content = wpcp_contextual_help_content( true );

        $this->current_screen->add_help_tab( array( 
           'id' => 'wpcp-help-tab',            
           'title' => __( 'Tour', 'wpcp' ),      
           'content' => $content, 
        ) );
    }

    /**
     * Preload
     *
     * @return void
     */
    public function preload() {
        global $wpcp_preloads;

        foreach ( $wpcp_preloads as $preload ) {
            $this->pointer_obj->add( $preload );
        }
    }

    /**
     * Identify page
     *
     * @return $page
     */
    public function get_page() {
        global $pagenow, $post;

        // Get page name
        if ( $post->ID && is_string( $post->ID ) ) // If page is a post entry. Ex: Pages -> All Pages -> Frontpage
            $page = $post->ID;
        else if ( $_GET['page'] ) // If page is a submenu of the menu and is a custom page. Ex: Custom Post Type Menu -> Settings(Settings is usually a custom page)
            $page = $_GET['page'];
        else
            $page = $pagenow; // If page is a submenu of the menu. Ex: Pages -> All Pages

        return $page;
    }

    /**
     * Admin notices
     *
     * @return void
     */
    public function admin_notices() { 
        ?>

        <?php if ( get_bloginfo( 'version' ) < '3.3' ) : ?>
            <div class="error">
                <p><?php _e( 'This plugin only works for version 3.3 and up. Please upgrade to the latest version of Wordpress first!', 'wpcp' ); ?></p>
            </div>
        <?php endif; ?>
        
        <?php if ( $this->remote_version && version_compare( $this->remote_version, WPCP_VERSION, '>' ) ) : ?>
            <div class="updated">
                <p><?php _e( "There is a new version of WP Custom Pointers. We advise you to upgrade to the new version. Please go to this <a href='". admin_url() . 'plugins.php' ."'>page</a> to do so.", 'wpcp' ); ?></p>
            </div>
        <?php endif; ?>

        <?php
    }

    /** 
     * Get our latet plugin version from the repo
     *
     * @return string The version number of the stable build in WP Plugins Directory
     */
    public function get_remote_version() {
        $args = array(
            'slug' => 'wp-custom-pointers',
            'fields' => array(
                'version' => true
            )
        );

        $response = wp_remote_post(
            'http://api.wordpress.org/plugins/info/1.0/',
            array(
                'body' => array(
                    'action' => 'plugin_information',
                    'request' => serialize((object)$args)
                )
            )
        );

        if ( !is_wp_error( $response ) ) {
            $returned_object = unserialize( wp_remote_retrieve_body( $response ) );
            return $returned_object->version;
        } else {
            return false;
        }
    }

}

$GLOBALS['wpcp'] = new WP_Custom_Pointers();

} // class_exists check