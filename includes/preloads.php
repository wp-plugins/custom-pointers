<?php
/**
 * The following are array of pointers that'll be preloaded
 * when the plugin is activated
 */

$wpcp_self_term_id = get_option( '_wpcp_term_id_self' );

global $wpcp_preloads;

if ( !$wpcp_self_term_id ) $wpcp_preloads = array();

$wpcp_preloads = array(
	// Plugin menu item
	array(
        'order' => 1,
        'screen' => 'edit-wpcp_pointer', // this is the page hook we want our pointer to show on
        'page' => 'edit.php',
        'target' => '#wp-admin-bar-wpcp-parent > a', // the css selector for the pointer to be tied to, best to use ID's
        'title' => 'Create Pointers',
        'content' => 'To start creating, go to a page where you want to create Pointers for and hover this menu and select "Auto" for auto-dection or "Manual" for manual entry of CSS selector for elements that you want to associate a pointer with. But before that, click Next button to learn about the other parts of this page.',
        'edge' => 'top', //top, bottom, left, right
        'align' => 'left', //top, bottom, left, right, middle,
        'collection' => $wpcp_self_term_id,
	),
    // Pointers List
    array(
        'order' => 2,
        'screen' => 'edit-wpcp_pointer', // this is the page hook we want our pointer to show on
        'page' => 'edit.php',
        'target' => '.wp-list-table', // the css selector for the pointer to be tied to, best to use ID's
        'title' => 'Pointers List',
        'content' => 'The Pointers you created are listed in this table. Just like posts or pages, Pointers are also a type of Wordpress post so you can manage them like how you would manage posts or pages. If you are in Auto mode, you can also edit or delete a pointer by just hovering on an element with green border. The green border indicates that the element has already a pointer.',
        'edge' => 'top', //top, bottom, left, right
        'align' => 'left', //top, bottom, left, right, middle,
        'collection' => $wpcp_self_term_id,
    ),   
    // Help toggle
    array(
        'order' => 3,
        'screen' => 'edit-wpcp_pointer', // this is the page hook we want our pointer to show on
        'page' => 'edit.php',
        'target' => '#contextual-help-link', // the css selector for the pointer to be tied to, best to use ID's
        'title' => 'Tour Access',
        'content' => 'To run Pointers, unhide Help pane by clicking this button. Once shown, click Tour tab and click Start or Restart button to run the Tour. The Tour is just a collection of Pointers linked together for the page you are on.',
        'edge' => 'top', //top, bottom, left, right
        'align' => 'right', //top, bottom, left, right, middle,
        'collection' => $wpcp_self_term_id,
    ),
    // So You Are Ready?
    array(
        'order' => 4,
        'screen' => 'edit-wpcp_pointer', // this is the page hook we want our pointer to show on
        'page' => 'edit.php',
        'target' => '#menu-appearance', // the css selector for the pointer to be tied to, best to use ID's
        'title' => 'So You Are Ready?',
        'content' => 'Now try to reate your first Pointer. Click this menu(or any other menu) and toggle Auto mode and start hovering on any element. An overlay form will appear into which you can enter the information of the Pointer that will be created for that element.',
        'edge' => 'left', //top, bottom, left, right
        'align' => 'middle', //top, bottom, left, right, middle,
        'collection' => $wpcp_self_term_id,
    ),      
);