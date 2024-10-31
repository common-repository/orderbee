<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.orderbee.be
 * @since      1.0.0
 *
 * @package    OrderBee
 * @subpackage OrderBee/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    OrderBee
 * @subpackage OrderBee/includes
 * @author     OrderBee <info@orderbee.be>
 */
class OrderBee_Activator {

    /**
     * Activater.
     *
     * This function only runs once, when activating the plugin.
     *
     * @since    1.0.0
     */
    public static function activate() {
        
		$user = wp_get_current_user();
		$user = $user->data->user_login;
        $url = 'https://app.orderbee.be/api/Woocommerce/Activate';
		$obfrwc_server_auth_id = get_option('obfrwc_server_auth_id');
        $arr_auth_param = array(
			'consumer_id' => $obfrwc_server_auth_id,
            'user' => $user,
            'http'     => $_SERVER["HTTP_HOST"]
        );
        $response      = wp_remote_post($url, ['body' => $arr_auth_param]);
        $response_code = wp_remote_retrieve_response_code($response);
    /**
     * Create table.
     *
     * This function will create table, when not exist, to save the tasks.
     *
     * @since    1.2.6
     */
		global $wpdb;
		$table_name = $wpdb->prefix."obfrwc_pushjobs"; 
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		  id BIGINT(20) NOT NULL AUTO_INCREMENT,
		  uid BIGINT(20) NULL DEFAULT NULL,
		  type INT(2) NULL DEFAULT NULL,
		  error VARCHAR(6) NULL DEFAULT NULL,
		  PRIMARY KEY (id)
		) $charset_collate;";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$wpdb->query( $sql );
    }
	
}
