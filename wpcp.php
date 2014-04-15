<?php
/*
Plugin Name: Custom Pointers for WordPress (Free)
Plugin URI: http://www.theportlandcompany.com/product/custom-pointers-plugin-for-wordpress/
Description: The Custom Pointers Plugin for WordPress introduces an interface that enables Administrators to create a group of custom "Pointers" quickly, easily and in an organized fashion. Fundamentally; it's a way to create interactive tutorials for your WordPress Users in the back end. This is built atop the "Feature Pointers" feature that was introduced in WordPress 3.3.
Author: The Portland Company, Designed by Spencer Hill, Coded by Redeye Adaya
Author URI: http://www.theportlandcompany.com
Version: 0.9.18
Copyright: 2014 The Portland Company 
License: GPL v3
License URI: http://www.gnu.org/licenses/quick-guide-gplv3.html
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
    public $version = '0.9.0';

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

    /**
     * @var string
     */
    public $plugin_name = 'Custom Pointers Plugin for WordPress';

    function __construct() {
        // Define WP_Custom_Pointers constant
        define( 'WPCP_VERSION', $this->version );

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
        register_deactivation_hook( __FILE__, array( $this, 'deactivate_cron') );

        // Include required files
        add_action( 'plugins_loaded', array( $this, 'includes' ));
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
        $options = array(  
                '_wpcp_version',
                '_wpcp_status',
                '_wpcp_preloads_ran',
                '_wpcp_term_id_self',
                '_wpcp_status',
                '_wpcp_sk'
            );

        foreach ( $options as $option ) {
            delete_option( $option );
        }

        global $wpdb;

        $sql = "SELECT `ID` FROM {$wpdb->posts} WHERE `post_type` = '%s'";
        $pointers = $wpdb->get_results( $wpdb->prepare( $sql, 'wpcp_pointer' ) );

        foreach( $pointers as $pointer ) {
            wp_delete_post( $pointer->ID );
        }

        $sql = "SELECT {$wpdb->terms}.term_id FROM {$wpdb->terms}";
        $sql .= " JOIN {$wpdb->term_taxonomy} ON {$wpdb->terms}.term_id = {$wpdb->term_taxonomy}.term_id";
        $sql .= " WHERE {$wpdb->term_taxonomy}.taxonomy = '%s'";
        $collections = $wpdb->get_results( $wpdb->prepare( $sql, 'wpcp_collection' ) );

        foreach( $collections as $collection ) {
            wp_delete_term( $collection->term_id, 'wpcp_collection' );
        }
        
        $user_ID = get_current_user_id();
        
        delete_user_option( $user_ID, 'dismiss_coupon_reminder_wpcp' );
        delete_user_option( $user_ID, 'dismiss_sharing_reminder_wpcp' );
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
        wp_enqueue_script( 'wpcp_validate', plugins_url( 'assets/js/jquery.validate.min.js', __FILE__ ) );
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
            'splash_dismissed' => get_user_meta( get_current_user_id(), '_wpcp_splash_dismissed' ),
            'active' => wpcp_is_active() ? 'yes' : 'no'
        ) );

        // Enqueue styles
        wp_enqueue_style( 'wp-pointer' );
        wp_enqueue_style( 'jquery-ui', plugins_url( 'assets/css/jquery-ui-1.9.1.custom.css', __FILE__ ) );
        wp_enqueue_style( 'wpcp-admin', plugins_url( 'assets/css/admin.css', __FILE__ ) );
        wp_enqueue_style( 'wpcp-create', plugins_url( 'assets/css/create.css', __FILE__ ) );
        wp_enqueue_style( 'wpcp-pointer', plugins_url( 'assets/css/pointer.css', __FILE__ ) );

        if ( wpcp_is_active() ) {
            // Add help tab
            $this->contextual_help();
        }
    }

    /**
     * Helper functions
     *
     * @return void
     */
    public function includes() {
        require_once dirname( __FILE__ ) . '/includes/html.php';
        require_once dirname( __FILE__ ) . '/includes/functions.php';
        require_once dirname( __FILE__ ) . '/includes/preloads.php';
    }

    /**
     * Register the plugin menu
     *
     * @return void
     */
    public function admin_menu() {
        $capability = 'edit_posts'; //minimum level: editor
        
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
            'name' => 'Custom Pointers',
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
            'menu_name' => 'Custom Pointers'
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
        
		$dismiss_coupon_reminder_wpcp = get_user_option( 'dismiss_coupon_reminder_wpcp' );
		$dismiss_sharing_reminder_wpcp = get_user_option( 'dismiss_sharing_reminder_wpcp' );
		
		if ( $_GET['dismiss_coupon_reminder_wpcp'] == true ) {
		    update_user_meta( get_current_user_id(), 'dismiss_coupon_reminder_wpcp', true );
		    return;
		}
		
		if ( $_GET['dismiss_sharing_reminder_wpcp'] == true ) {
		    update_user_meta( get_current_user_id(), 'dismiss_sharing_reminder_wpcp', true );
		    return;
		}
		
		
		if ( get_current_screen()->parent_file == 'edit.php?post_type=wpcp_pointer' ) {
    
            if ( get_bloginfo( 'version' ) < '3.3' ) { ?>
                <div class="error">
                    <p><?php _e( 'You must be using WordPress 3.3 or later to utilize the Custom Pointers Plugin for WordPress.', 'wpcp' ); ?></p>
                </div>
            <?php
            }
            ?>
        
        <?php if ( $dismiss_coupon_reminder_wpcp == false ) : ?>
            <div class="updated">
            
                <table>
                    <tr valign="top">
                        <td style="width: 33%;">
                            <h3>Premium Includes:</h3>
                            <h4>Back End</h4>
                            <ol>
                                <li>Quick Add Collection</li>
                            </ol>
                            <h4>Front End</h4>
                            <ol>
                                <li>Restart Button</li>
                                <li>Back Button</li>
                            </ol>
                        </td>
                        <td style="width: 43%; ">
                            <h3>Get a Coupon to Upgrade for $29!</h3>
                            <ol>
                                <li><a href='https://plus.google.com/109726560580019725502/about?hl=en&gl=us' target='_blank'>Leave a Review on Google &#187;</a></li>
                                <li><a href='http://www.theportlandcompany.com/contact-and-support/' target='_blank'>Send an Message to Us for a Coupon &#187;</a></li>
                                <li><a href='http://www.theportlandcompany.com/product/custom-pointers-plugin-for-wordpress/' target='_blank'>Get Your Coupon to Purchase for $29 &#187;</li>
                            </ol>
                            <a class='button-primary' target='_blank' href='http://www.theportlandcompany.com/product/custom-pointers-plugin-for-wordpress/'>Upgrade to Premium »</a>
                        </td>
                        <td>
                            <h3>Coming Soon to Premium</h3>
                            <ol>
                                <li>Import/Export</li>
                                <li>Visual Editor to embed Media</li>
                                <li>Quick Delete</li>
                                <li>Quick Re-Organize</li>
                            </ol>
                            
                            <a class="ptp-nag-close button-secondary" href="<?php echo $_SERVER['REQUEST_URI']; ?>&dismiss_coupon_reminder_wpcp=true"><?php _e( 'Dismiss', 'ptp' ); ?></a>
                        </td>
                    </tr>
                    
                </table>
                
            </div>
            
    		<link rel="canonical" href="http://www.theportlandcompany.com/product/custom-pointers-plugin-for-wordpress/">
        <?php 
        
        endif;
        
        if ( $dismiss_sharing_reminder_wpcp == false ) : 
            social_media_sharing_utility();
        endif;
        
        } // End of get_current_screen()->parent_base == 'ptp_bulk_import'
    
    }

    /**
     * Activate Cron
     *
     * @return void
     */
    public function activate_cron() {
        if ( !wp_next_scheduled( 'wpcp_cron' ) ) {
            wp_schedule_event( time(), 'twicedaily', 'wpcp_cron' );
        }
    }

    /**
     * Deactivate Cron
     *
     * @return void
     */
    public function deactivate_cron() {
        if( false !== ( $time = wp_next_scheduled( 'wpcp_cron' ) ) ) {
            wp_unschedule_event( $time, 'wpcp_cron' );
        }
    }

    /**
     * Verify
     *
     * @return void
     */
    public function verify() {
        $res = wpcp_verify();

        if ( !$res ) {
            delete_option( '_wpcp_status' );
            wpcp_deactivate();
        }
    }

}

$GLOBALS['wpcp'] = new WP_Custom_Pointers();

} // class_exists check