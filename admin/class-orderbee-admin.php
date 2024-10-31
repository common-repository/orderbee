<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.orderbee.be
 * @since      1.0.0
 *
 * @package    OrderBee
 * @subpackage OrderBee/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    OrderBee
 * @subpackage OrderBee/admin
 * @author     OrderBee <info@orderbee.be>
 */
class OrderBee_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version     = $version;

        add_action('admin_menu', array($this, 'obfrwc_menu'));
        add_action('admin_menu', array($this, 'obfrwc_menu_override'));

        flush_rewrite_rules();
    }

    function obfrwc_menu() {
        add_menu_page('OrderBee', 'OrderBee', 'manage_options', 'obfrwc_page_settings', array($this, 'obfrwc_page_settings'), plugins_url('orderbee/favicon.png?v='.ORDERBEE_VERSION));
        add_submenu_page('obfrwc_page_settings', __('Change Log', 'orderbee'), __('Change Log', 'orderbee'), 'manage_options', 'obfrwc_change_log', array($this, 'obfrwc_page_change_log'));
        add_submenu_page('obfrwc_page_settings', __('Pickup Locator', 'orderbee'), __('Pickup Locator', 'orderbee'), 'manage_options', 'obfrwc_pickup_locator', array($this, 'obfrwc_page_pickup_locator'));
    }

    function obfrwc_menu_override() {
        global $submenu;
        $submenu['obfrwc_page_settings'][0][0] = __('Settings', 'orderbee');
        $submenu['obfrwc_page_settings'][0][3] = __('Settings', 'orderbee');
        return $submenu;
    }

    function obfrwc_page_settings() {
        if (!empty($_POST['is_disconnect'])) {
            /* REMOVE KEY FROM DB */
            global $wpdb;
            $wpdb->delete("{$wpdb->prefix}woocommerce_api_keys",
                    array(
                        'user_id'     => get_current_user_id(),
                        'description' => 'Key for OrderBee'
            ));

            /* DISCONNECT REQUEST TO SERVER */
            $obj_OrderBee_Settings = new OrderBee_Settings();
            $obj_status            = $obj_OrderBee_Settings->disconnect_server();
            if ($obj_status->code == 200) {
                update_option('obfrwc_server_auth_id', null);
            }
            echo '<div class="updated notice is-dismissible"><p>' . __($obj_status->message, 'orderbee') . '</p></div>';
        }
        require_once __DIR__ . '/partials/orderbee-settings.php';
    }

    function obfrwc_page_change_log() {
        require_once __DIR__ . '/partials/orderbee-product-changlog.php';
    }
    function obfrwc_page_pickup_locator() {
        require_once __DIR__ . '/partials/orderbee-pickuplocator.php';
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/orderbee-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/orderbee-admin.js', array('jquery'), $this->version, false);
    }

}
