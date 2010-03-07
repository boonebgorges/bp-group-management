<?php
/*
Plugin Name: BP Group Management
Plugin URI: http://teleogistic.net/code/buddypress/bp-group-management
Description: Allows site administrators to manage BuddyPress group membership
Version: 0.2
Author: Boone Gorges
Author URI: http://teleogistic.net
*/

/* Only load the BuddyPress plugin functions if BuddyPress is loaded and initialized. */
function bp_group_management_init() {
	require( dirname( __FILE__ ) . '/bp-group-management-bp-functions.php' );
}
add_action( 'bp_init', 'bp_group_management_init' );

function bp_group_management_admin_init() {
	
	wp_register_style( 'bp-group-management-css', WP_PLUGIN_URL . '/bp-group-management/bp-group-management-css.css' );
}
add_action( 'admin_init', 'bp_group_management_admin_init' );

?>