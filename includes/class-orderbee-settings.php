<?php

class OrderBee_Settings {
    /* AUTHENTICATE AND SHARE WC API INFO WITH SERVER */

    protected function authenticate_ob_server() {

        if (empty(get_option('obfrwc_server_username')) || empty(get_option('obfrwc_server_password'))) {
            return (object) array('message' => __('API credentials are missing.', 'orderbee'));
        }

        $obfrwc_server_authenticate_url = 'https://app.orderbee.be/api/Woocommerce/Validate';
        $arr_auth_param                 = array(
            'username' => get_option('obfrwc_server_username'),
            'password' => get_option('obfrwc_server_password'),
            'http'     => $_SERVER["HTTP_HOST"]
        );

        /* AUTHENTICATE WITH SERVER */
        $arr_auth_status = $this->call_server($obfrwc_server_authenticate_url, $arr_auth_param);
        if (!empty($arr_auth_status->id)) {
            update_option('obfrwc_server_auth_id', $arr_auth_status->id);
            update_option('obfrwc_server_password', '');
        }
        return $arr_auth_status;
    }

    /* SHARE WC API INFO WITH SERVER */

    public function sync_wc_api_server() {
        $arr_auth_status = $this->authenticate_ob_server();
        if (!empty($arr_auth_status->id)) {

            if (!class_exists('WooCommerce')) {
                return '<div class="error notice is-dismissible"><p>' . __('WooCommerce not found!', 'orderbee') . '</p></div>';
            }

            /* CREATE OR GET EXIST WC API KEY FOR CURRENT USER */
            global $wpdb;
            $wc_key_info = $wpdb->get_row($wpdb->prepare("SELECT consumer_key, consumer_secret "
                            . "FROM {$wpdb->prefix}woocommerce_api_keys 
                        WHERE user_id = %d AND description = 'Key for OrderBee'", get_current_user_id()), ARRAY_A);

            if (empty($wc_key_info['consumer_key'])) {
                $wc_key_info = $this->ceate_wc_api_credential('OrderBee Auth', get_current_user_id(), 'read_write');
            }

            /* SEND WC API KEY SERVER */
            $obfrwc_server_credential_url = 'https://app.orderbee.be/api/Woocommerce/SaveAPI';
            $wc_key_info['consumer_id']   = $arr_auth_status->id;
            $wc_key_info['http']          = $_SERVER["HTTP_HOST"];
            $api_responce                 = $this->call_server($obfrwc_server_credential_url, $wc_key_info);
            if ($api_responce->code != '200') {
                return '<div class="error notice is-dismissible"><p>' . $api_responce->message . '</p></div>';
            }
            else {
                return '<div class="updated notice is-dismissible"><p>' . $arr_auth_status->message . '</p></div>';
            }
        }
        else {
            return '<div class="error notice is-dismissible"><p>' . __($arr_auth_status->message, 'orderbee') . '</p></div>';
        }
    }

    /* CREATE WC API KEY */

    protected function ceate_wc_api_credential($app_name, $app_user_id, $scope) {
        global $wpdb;

        $user = wp_get_current_user();

        // Created API keys.
        $permissions     = in_array($scope, array('read', 'write', 'read_write'), true) ? sanitize_text_field($scope) : 'read';
        $consumer_key    = 'ck_' . wc_rand_hash();
        $consumer_secret = 'cs_' . wc_rand_hash();

        $wpdb->insert(
                $wpdb->prefix . 'woocommerce_api_keys',
                array(
                    'user_id'         => $user->ID,
                    'description'     => 'Key for OrderBee',
                    'permissions'     => $permissions,
                    'consumer_key'    => wc_api_hash($consumer_key),
                    'consumer_secret' => $consumer_secret,
                    'truncated_key'   => substr($consumer_key, -7),
                ),
                array(
                    '%d',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                )
        );

        return array(
            'consumer_key'    => $consumer_key,
            'consumer_secret' => $consumer_secret
        );
    }

    public function generate_products_list_json() {

        $arr_products = array_map('wc_get_product', get_posts(['post_type' => 'product', 'nopaging' => true]));

        $arr_csv_data = array();
        foreach ($arr_products as $product_info) {
            array_push($arr_csv_data, array(
                'id'             => $product_info->id,
                'status'         => $product_info->status,
                'SKU'            => $product_info->sku,
                'images-links'   => wp_get_attachment_url($product_info->image_id),
                'name'           => $product_info->name,
                'price'          => $product_info->price,
                'regular_price'  => $product_info->regular_price,
                'stock_quantity' => $product_info->stock_quantity,
            ));
        }
        ob_start('ob_gzhandler');
        header("Content-type: application/json; charset=utf-8");
        echo wp_json_encode($arr_csv_data, 1);
        exit;
    }

    public function get_orderbee_log() {
        $obfrwc_server_auth_id = get_option('obfrwc_server_auth_id');
        if (!empty($obfrwc_server_auth_id)) {
            /* SEND WC API KEY SERVER */
            $obfrwc_server_log_url = 'https://app.orderbee.be/api/Woocommerce/ChangeLog';
            $api_responce          = $this->call_server($obfrwc_server_log_url, ['language'=>get_locale(), 'consumer_id' => $obfrwc_server_auth_id, 'http' => $_SERVER["HTTP_HOST"]]);
            if ($api_responce->code != '200') {
                echo '<div class="error notice is-dismissible"><p>' . $api_responce->message . '</p></div>';
            }
            else {
                return $api_responce;
            }
        }
        else {
            echo '<div class="error notice is-dismissible"><p>' . __('API credentials are missing.', 'orderbee') . '</p></div>';
        }
    }

    public function get_orderbee_pushjobs() {
		$table = array('header'=>array('id', 'type', 'item', 'errorcode'));
		$table['size'] = array('150','120','','120');
		global $wpdb;
		$table_name = $wpdb->prefix."obfrwc_pushjobs"; 
		$query = $wpdb->get_results("SELECT * FROM ".$table_name, ARRAY_A);
		foreach($query as $row){
			if($row['type'] == 1){
				$type = 'Product';
				$product_info = new WC_Product($row['uid']);
				$description = $product_info->name;
			}elseif($row['type'] == 2){
				$type = 'Order';
				$description = 'Order #'.$row['uid'];
			}
			$table['body'][] = array($row['id'],$type,'<a href="'.get_edit_post_link($row['uid']).'">'.$description.'</a>',$row['error']);
		}
		return $table;
    }

    public function get_orderbee_ad_data() {
        $obfrwc_server_log_url = 'https://app.orderbee.be/api/Woocommerce/ProductInfo';
        $api_responce          = $this->call_server($obfrwc_server_log_url, ['language'=>get_locale()]);
        if ($api_responce->code == '200') {
            return $api_responce->innerHTML;
        }
        else {
            return null;
        }
    }

    public function disconnect_server() {
        $obfrwc_server_log_url = 'https://app.orderbee.be/api/Woocommerce/Disconnect';
        $api_responce          = $this->call_server($obfrwc_server_log_url, ['consumer_id' => get_option('obfrwc_server_auth_id'), 'http' => $_SERVER["HTTP_HOST"]]);
        return $api_responce;
    }

    /* MAKE SERVER API CALL */

    protected function call_server($str_url = null, $arr_fields = null) {
        $response      = wp_remote_post($str_url, ['body' => $arr_fields]);
        $response_code = wp_remote_retrieve_response_code($response);
        if (in_array($response_code, array(200, 301, 302, 403))) {
            $str_result = wp_remote_retrieve_body($response);
            return json_decode($str_result);
        }
        else {
            return (object) array('code' => $response_code, 'message' => __('API Access point not accessible.', 'orderbee'));
        }
    }

    function for_lang_scan() {
        __("The username you've entered is unknown, please try again.", 'orderbee');
        __("You've entered a wrong password, please try again.", 'orderbee');
        __("Not all credentials are given, please try again.", 'orderbee');
        __("API Access point not accessible.", 'orderbee');
        __("Data not found.", 'orderbee');
    }

}
