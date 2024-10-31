<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://www.orderbee.be
 * @since      1.0.0
 *
 * @package    OrderBee
 */
// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/* REMOVE KEY FROM DB */
if (class_exists('WooCommerce')) {
    global $wpdb;
    $wpdb->delete("{$wpdb->prefix}woocommerce_api_keys",
            array(
                'user_id'     => get_current_user_id(),
                'description' => 'Key for OrderBee'
    ));
}

/* DISCONNECT REQUEST TO SERVER */
require_once __DIR__ . '/includes/class-orderbee-settings.php';
$obj_OrderBee_Settings = new OrderBee_Settings();
$obj_OrderBee_Settings->disconnect_server();

delete_option('obfrwc_server_auth_id');
delete_option('obfrwc_server_username');
delete_option('obfrwc_server_password');
