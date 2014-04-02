<?php
/**
 * The following are array of pointers that'll be preloaded
 * when the plugin is activated
 */

$wpcp_self_term_id = get_option( '_wpcp_term_id_self' );

global $wpcp_preloads;

if ( !$wpcp_self_term_id ) $wpcp_preloads = array();

$wpcp_preloads = array(
	array(
        'order' => 1,
        'screen' => 'edit-wpcp_pointer', // this is the page hook we want our pointer to show on
        'page' => 'edit.php',
        'target' => '#wp-admin-bar-wpcp-parent > a', // the css selector for the pointer to be tied to, best to use ID's
        'title' => 'Introduction to Custom Pointers',
        'content' => 'Custom Pointers Plugin for WordPress enables Administrators to create interactive tutorials for Users by providing an interface for the Pointers API introduced into WordPress 3.3. Click "Next" to continue this interactive tutorial.',
        'edge' => 'top', //top, bottom, left, right
        'align' => 'left', //top, bottom, left, right, middle,
        'collection' => $wpcp_self_term_id,
	),
    array(
        'order' => 2,
        'screen' => 'edit-wpcp_pointer', // this is the page hook we want our pointer to show on
        'page' => 'edit.php',
        'target' => '#contextual-help-link', // the css selector for the pointer to be tied to, best to use ID's
        'title' => 'Restart a Tour',
        'content' => 'Tutorials begin automatically for first-time users. If you are using the Premium version of Custom Pointers than users can restart tutorials from this "Help" tab and then "Tours" > "Restart".',
        'edge' => 'top', //top, bottom, left, right
        'align' => 'right', //top, bottom, left, right, middle,
        'collection' => $wpcp_self_term_id,
    ),
    array(
        'order' => 3,
        'screen' => 'edit-wpcp_pointer', // this is the page hook we want our pointer to show on
        'page' => 'edit.php',
        'target' => '#menu-appearance', // the css selector for the pointer to be tied to, best to use ID's
        'title' => 'So You Are Ready?',
        'content' => 'To begin creating a Collection of Pointers (AKA tutorial), hover over the flag icon this is pointing to and select "Auto" (but don\'t do this yet!). The page will reload and you will see a box, like this, appear over most elements you hover over. If you want to go back to edit them simply refresh the page and you will see a light green border around elements that have Pointers already associated with them. And don\'t forget that you can <a href="http://www.theportlandcompany.com/product/custom-pointers-plugin-for-wordpress/" target="_blank">unlock more features by upgrading Premium!</a>',
        'edge' => 'left', //top, bottom, left, right
        'align' => 'middle', //top, bottom, left, right, middle,
        'collection' => $wpcp_self_term_id,
    ),      
);