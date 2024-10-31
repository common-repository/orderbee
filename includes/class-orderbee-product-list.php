<?php

class OrderBee_Product_List {

    public function generate_products_list_json() {

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
            /* PRODUCT INFO */
            $arr_temp_product_info = array(
                'id'             => $product_info->id,
                'status'         => $product_info->status,
                'type'           => $product_info->get_type(),
                'SKU'            => $product_info->sku,
				'manage_stock'	 => $product_info->manage_stock,
                'images_links'   => wp_get_attachment_url($product_info->image_id),
                'name'           => $product_info->name,
                'price'          => $product_info->price,
                'regular_price'  => $product_info->regular_price,
                'tax_status' 	 => $product_info->tax_status,
                'tax_rate' 		 => WC_Tax::get_rates( $product_info->get_tax_class() ),
                'stock_quantity' => $product_info->stock_quantity,
				'meta_data'		 => $product_info->get_meta_data(),
                'variations'     => array()
            );
			if($product_info->has_weight()){
				$arr_temp_product_info['WD']['weight'] = $product_info->get_weight();
				$arr_temp_product_info['WD']['weight_unit'] = get_option('woocommerce_weight_unit');
			}
			if($product_info->has_dimensions()){
				$arr_temp_product_info['WD']['length'] = $product_info->get_length();
				$arr_temp_product_info['WD']['width'] = $product_info->get_width();
				$arr_temp_product_info['WD']['height'] = $product_info->get_height();
				$arr_temp_product_info['WD']['dimension_unit'] = get_option('woocommerce_dimension_unit');
			}

            /* VARIATION INFO */
            $obj_current_product = wc_get_product($product_info->id);            
            if ($obj_current_product->is_type('variable') == true) {
                $arr_variations     = $obj_current_product->get_available_variations();
                $temp_prod_var_list = array();
                foreach ($arr_variations as $prod_var_info) {
					$variation_o = new WC_Product_Variation( $prod_var_info['variation_id'] );
					$manageStock = $variation_o->get_manage_stock();
					if($prod_var_info['max_qty']){
						$varQty = $prod_var_info['max_qty'];
					}else{
						$varQty = $variation_o->get_stock_quantity();
					}
					$dm = 0;
					if(!empty($variation_o->weight)) $WeightDimensions['weight'] = $variation_o->weight;
					if(!empty($variation_o->weight)) $WeightDimensions['weight_unit'] = get_option('woocommerce_weight_unit');
					if(!empty($variation_o->length)) $WeightDimensions['length'] = $variation_o->length; $dm = 1;
					if(!empty($variation_o->width)) $WeightDimensions['width'] = $variation_o->width; $dm = 1;
					if(!empty($variation_o->height)) $WeightDimensions['height'] = $variation_o->height; $dm = 1;
					if(!empty($variation_o->length) or !empty($variation_o->width) or !empty($variation_o->height)) $WeightDimensions['dimension_unit'] = get_option('woocommerce_dimension_unit');
                    $temp_prod_var_list[] = array(
                        'id'            => $prod_var_info['variation_id'],
                        'sku'           => $prod_var_info['sku'],
                        'stock_quantity'=> $varQty,
						'manage_stock'	=> $manageStock,
                        'price'         => $prod_var_info['display_price'],
                        'regular_price' => $prod_var_info['display_regular_price'],
                        'attributes'    => $prod_var_info['attributes'],
                        'meta_data'     => $variation_o->get_meta_data(),
						'WD'			=> $WeightDimensions
                    );
					unset($WeightDimensions);
                }
                if (!empty($temp_prod_var_list)) {
                    $arr_temp_product_info['variations'] = $temp_prod_var_list;
                }
            }
            array_push($arr_csv_data, $arr_temp_product_info);
			unset($arr_temp_product_info);
        }
        ob_start('ob_gzhandler');
        header("Content-type: application/json; charset=utf-8");
        return json_encode($arr_csv_data, 1);
        exit;
    }
}
