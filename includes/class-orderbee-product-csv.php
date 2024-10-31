<?php

class OrderBee_Product_Csv {

    public function generate_products_csv() {

        if (class_exists('WooCommerce')) {
            $arr_products = array_map('wc_get_product', get_posts(['post_type' => 'product', 'nopaging' => true]));
            if (empty($arr_products)) {
                echo json_encode(['message' => __('Products are not available.', 'orderbee')]);
                exit;
            }
        }
        else {
            if (empty($arr_products)) {
                echo json_encode(['message' => __('WooCommerce not found!', 'orderbee')]);
                exit;
            }
        }

        $arr_csv_data = array();
        foreach ($arr_products as $product_info) {

            /* VARIATION INFO */
            $obj_current_product = wc_get_product($product_info->id);            
            if ($obj_current_product->is_type('variable') == true) {
                $arr_variations     = $obj_current_product->get_available_variations();
                $temp_prod_var_list = array();
                foreach ($arr_variations as $prod_var_info) {
                    $temp_prod_var_list[] = array(
                        'id'             => $prod_var_info['variation_id'],
                        'sku'            => $prod_var_info['sku'],
                        'stock_quantity' => $prod_var_info['max_qty'],
                        'price'          => $prod_var_info['price'],
                        'regular_price'  => $prod_var_info['display_regular_price'],
                        'attributes'     => $prod_var_info['attributes']
                    );
                }
                if (!empty($temp_prod_var_list)) {
                    $arr_temp_product_info['variations'] = $temp_prod_var_list;
                }
            }else{
				$temp_prod_var_list = '';
			}
            $arr_temp_product_info = array($product_info->id, $product_info->status, $product_info->sku, wp_get_attachment_url($product_info->image_id), $product_info->name, $product_info->price, $product_info->regular_price, $product_info->stock_quantity, $temp_prod_var_list);
            array_push($arr_csv_data, $arr_temp_product_info);
        }
        return $arr_csv_data;
        exit;
    }

}
