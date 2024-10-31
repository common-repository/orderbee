<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://www.orderbee.be
 * @since      1.0.0
 *
 * @package    OrderBee
 * @subpackage OrderBee/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    OrderBee
 * @subpackage OrderBee/includes
 * @author     OrderBee <info@orderbee.be>
 */
class OrderBee_Deactivator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
		$user = wp_get_current_user();
		$user = $user->data->user_login;
        $url = 'https://app.orderbee.be/api/Woocommerce/Deactivate';
		$obfrwc_server_auth_id = get_option('obfrwc_server_auth_id');
        $arr_auth_param = array(
			'consumer_id' => $obfrwc_server_auth_id,
            'user' => $user,
            'http'     => $_SERVER["HTTP_HOST"]
        );
        $response      = wp_remote_post($url, ['body' => $arr_auth_param]);
        $response_code = wp_remote_retrieve_response_code($response);
    /**
     * Drop table.
     *
     * This function will create table, when not exist, to save the tasks.
     *
     * @since    1.2.6
     */
		global $wpdb;
		$table_name = $wpdb->prefix."obfrwc_pushjobs"; 
		$sql = "DROP TABLE $table_name ;";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$wpdb->query( $sql );
    }

}
