<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.orderbee.be
 * @since             1.0.0
 * @package           OrderBee
 *
 * @wordpress-plugin
 * Plugin Name:       OrderBee
 * Plugin URI:        https://www.orderbee.be
 * Description:       This plugin makes a fast and safe connection between your Woocommerce and OrderBee.
 * Version:           1.3.3
 * Author:            OrderBee
 * Author URI:        https://www.orderbee.be
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       orderbee
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('ORDERBEE_VERSION', '1.3.3');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-orderbee-activator.php
 */
function activate_orderbee() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-orderbee-activator.php';
    OrderBee_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-orderbee-deactivator.php
 */
function deactivate_orderbee() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-orderbee-deactivator.php';
    OrderBee_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_orderbee');
register_deactivation_hook(__FILE__, 'deactivate_orderbee');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-orderbee.php';
require plugin_dir_path(__FILE__) . 'includes/class-orderbee-settings.php';/**
 /**
 * Adding plugin at admin bar
 * 
 * Extra helpfull
 *
 * @since    1.3.0
 */
require plugin_dir_path(__FILE__) . 'common/admin-bar.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
if (!function_exists('run_orderbee_manager')) {

    function run_orderbee_manager() {

        $plugin = new OrderBee();
        $plugin->run();
    }

    run_orderbee_manager();
}

if (!function_exists('obfrwc_rewrite_orderbee_products_url')) {

    function obfrwc_rewrite_orderbee_products_url() {
        add_rewrite_rule('^orderbee/products.txt/?', plugins_url('orderbee/products.txt'), 'top');
    }

    add_action('init', 'obfrwc_rewrite_orderbee_products_url');
}

if (!function_exists('obfrwc_rewrite_activation')) {

    function obfrwc_rewrite_activation() {
        obfrwc_rewrite_orderbee_products_url();
        flush_rewrite_rules();
    }

    register_activation_hook(__FILE__, 'obfrwc_rewrite_activation');
}

/**
 * Backup cronjob products and orders
 *
 * Failed push-jobs to OrderBee are saved in a db-table,
 * so now we can try again with this cronjob.
 * This cronjob runs every 5min.
 *
 * @since    1.3.0
 */

if (!function_exists('obfrwc_every_5minutes')) {

    function obfrwc_every_5minutes($schedules) {
        $schedules['every_5_minutes'] = array(
            'interval' => 300,
            'display'  => esc_html__('Every 5 minutes'),);
        return $schedules;
    }

    add_filter('cron_schedules', 'obfrwc_every_5minutes');
}


register_activation_hook(__FILE__, 'obfrwc_activation');
function obfrwc_activation() {
	wp_schedule_event(strtotime('00:01:00'), 'every_5_minutes', 'obfrwc_every_5minutes_products');
	wp_schedule_event(strtotime('00:03:00'), 'every_5_minutes', 'obfrwc_every_5minutes_orders');
}

add_action('obfrwc_every_5minutes_products', 'obfrwc_push_products_again');
function obfrwc_push_products_again() {
	require_once plugin_dir_path(__FILE__) . 'includes/class-orderbee-product-update.php'; 
	global $wpdb;
	$table_name = $wpdb->prefix."obfrwc_pushjobs"; 
	foreach($wpdb->get_results( "SELECT * FROM ".$table_name." WHERE type=1" , ARRAY_A) as $item){
		$product = new OrderBee_Product_Update();
		$product->generate_product_update_json($item['uid']);
	}
}

add_action('obfrwc_every_5minutes_orders', 'obfrwc_push_orders_again');
function obfrwc_push_orders_again() {
	require_once plugin_dir_path(__FILE__) . 'includes/class-orderbee-order-push.php'; 
	global $wpdb;
	$table_name = $wpdb->prefix."obfrwc_pushjobs"; 
	foreach($wpdb->get_results( "SELECT * FROM ".$table_name." WHERE type=2" , ARRAY_A) as $item){
		$order = new OrderBee_Order_Push();
		$order->push_order_to_OB($item['uid']);
	}
}


/**
 * End of Backup cronjob products and orders
 */

if (!function_exists('obfrwc_deactivation')) {

    register_deactivation_hook(__FILE__, 'obfrwc_deactivation');

    function obfrwc_deactivation() {

        wp_clear_scheduled_hook('obfrwc_every_60minutes'); // OLD BACKUP
        wp_clear_scheduled_hook('obfrwc_every_5minutes'); // OLD BACKUP
        wp_clear_scheduled_hook('obfrwc_every_5minutes_orders'); // NEW BACKUP
        wp_clear_scheduled_hook('obfrwc_every_5minutes_products'); // NEW BACKUP

    }

}

/**
 * Push orders to server
 *
 * @since    1.0.5
 */

if (!function_exists('obfrwc_push_order_to_server')) {

    function obfrwc_push_order_to_server($order_id) {
		global $wpdb;
		require_once plugin_dir_path(__FILE__) . 'includes/class-orderbee-order-push.php'; 
		$order = new OrderBee_Order_Push();
		$order->push_order_to_OB($order_id);
    }

    add_action('woocommerce_payment_complete', 'obfrwc_push_order_to_server');
}


/**
 * Pushing new or reissued products to OB
 *
 * @since    1.2.0
 */
if (!function_exists('obfrwc_push_product_to_server')) {
	function obfrwc_push_product_to_server( $product_id ) {
		
		if ( !is_front_page() && get_post_type(get_the_ID()) === 'product') {   
        	require_once plugin_dir_path(__FILE__) . 'includes/class-orderbee-product-update.php'; 
			$product = new OrderBee_Product_Update();
			$product->generate_product_update_json($product_id);
		}
	}
	add_action('wp_insert_post', 'obfrwc_push_product_to_server', 10, 1);
}
/**
 * Notice when orderbee needs an update
 *
 * @since    1.1.3
 */

$versionsOfPlugins = get_site_transient( 'update_plugins' );
$key = "orderbee/orderbee.php";
if(array_key_exists($key,$versionsOfPlugins->response)){
	function author_admin_notice(){
		echo '<div class="notice notice-error is-dismissible">
			  <p style="font-weight:bold; color:#FF0000">'.__('Warning! OrderBee-plugin is not up to date! Please update to stay connected.', 'orderbee').' <a href="update-core.php">'.__('UPDATE NOW', 'orderbee').'</a></p>
			 </div>';
	}
	add_action('admin_notices', 'author_admin_notice');
}

