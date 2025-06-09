<?php
/*
Plugin Name: Custom Product Review Manager
Description: A Plugin that manages the custom reviews/ratings for WooCommerce Products.
Version: 1.0
Author: Anuj Maharjan
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 7.4
Requires Plugins: woocommerce
Text Domain: custom-product-review
*/

if ( ! defined( 'ABSPATH' ) ) { //exit if the plugin file is accessed directly
	exit;
}

add_action( 'plugins_loaded', function () {
	if ( ! class_exists( 'WooCommerce' ) ) { //check if WooCommerce plugin is active, since the WooCommerce plugin is required
		add_action( 'admin_notices', function () {
			echo '<div class="notice notice-error"><p><strong>Custom Product Review Manager: </strong>The WooCommerce is not active. Please install and activate WooCommerce to use this plugin.</p></div>';
		} ); //display notice in admin dashboard if WooCommerce plugin is not active

		return; //do not proceed further if WooCommerce is not active.
	}
} );

//define all plugin constants used with in plugin
define( 'CPRM_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'CPRM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Activation Hook
function cprm_activate_plugin() {
	// Run only on activation
	error_log( 'Custom Product Review Manager plugin is activated' ); // just for testing purpose
	flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'cprm_activate_plugin' );

// Deactivation Hook
function cprm_deactivate_plugin() {
	// Run only on deactivation
	error_log( 'Custom Product Review Manager plugin is deactivated' ); // just for testing purpose
	flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, 'cprm_deactivate_plugin' );

//Uninstall Hook - Remove all the plugin data (not needed since there is uninstall.php)

if ( file_exists( CPRM_PLUGIN_PATH . 'includes/class-custom-product-review-manager.php' ) ) {
	require_once CPRM_PLUGIN_PATH . 'includes/class-custom-product-review-manager.php'; // include the main plugin class file exist
}