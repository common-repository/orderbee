<?php

class OrderBee_Order_Push {

    public function push_order_to_OB($order_id) {
		
		$order = new WC_Order($order_id);          
		$input = array();
		$input['ordernumber'] = $order->get_order_number();    
		$input['email'] = $order->get_billing_email();    
		$input['name'] = $order->get_formatted_billing_full_name();
		$input['company_name'] = $order->get_billing_company();    
		$input['contact_number'] = str_replace(' ', '', $order->get_billing_phone());
		$input['address_1'] = $order->get_billing_address_1();     
		$input['address_2'] = $order->get_billing_address_2();    
		$input['city'] = $order->get_billing_city();     
		$input['postal_code'] = $order->get_billing_postcode();    
		$input['country'] = $order->get_billing_country();    
		$input['del_name'] = $order->get_formatted_shipping_full_name();
		$input['del_company_name'] = $order->get_shipping_company();    
		$input['del_address_1'] = $order->get_shipping_address_1();     
		$input['del_address_2'] = $order->get_shipping_address_2();    
		$input['del_city'] = $order->get_shipping_city();     
		$input['del_postal_code'] = $order->get_shipping_postcode();    
		$input['del_country'] = $order->get_shipping_country();    
		$input['instructions'] = $order->get_customer_note();     
		$input['payment_method'] = $order->get_payment_method();     
		$input['payment_date'] = $order->get_date_paid();   
		$input['shipping_total'] = $order->get_shipping_total(); 
		$input['shipping_tax'] = $order->get_shipping_tax(); 
		$shipping_lines = $order->get_shipping_methods(); 
		foreach($shipping_lines as $id => $shipping_line){
			$shippingLineArray = $shipping_line->get_data();
			$taxesShipping = WC_Tax::get_rates($shipping_line->get_tax_class());
			foreach($taxesShipping as $taxShipping){
				$shippingLineArray["tax_rate"] = $taxShipping['rate'];
			}
			$input['shipping_lines'][] = $shippingLineArray;
		}
		$meta_data = $order->get_meta_data(); 
		foreach($meta_data as $meta){
			$meta = $meta->get_data();
			$input['meta_data'][] = $meta;
		}
		$input['order_total_inc'] = wc_format_decimal($order->get_total(), 2);
		$items = $order->get_items();
		foreach($items as $item){
			$itemIntel = $item->get_data();
			$itemArray = array();
    		
			
			if($item['variation_id']){
				$sku = get_post_meta( $item['variation_id'], '_sku', true );
			}else{
				$product = new WC_Product($itemIntel['product_id']);
				$sku = $product->get_sku();
			}
			
			
			$itemArray["sku"] = $sku;
			$itemArray["name"] = $itemIntel['name'];
			$itemArray["quantity"] = $itemIntel['quantity'];
			$itemArray["subtotal"] = $itemIntel['subtotal'];
			$itemArray["subtotal_tax"] = $itemIntel['subtotal_tax'];
			$taxes = WC_Tax::get_rates($item->get_tax_class());
			foreach($taxes as $tax){
				$itemArray["tax_rate"] = $tax['rate'];
			}
			$itemArray["meta_data"] = $itemIntel['meta_data'];
			$input['items'][] = $itemArray;
		}
		$fees = $order->get_items('fee');
		if($fees){
			$input['fee'] = array();
			foreach($fees as $fee){
				$feeArray = $fee->get_data();
				$taxesFee = WC_Tax::get_rates($fee->get_tax_class());
				foreach($taxesFee as $taxFee){
					$feeArray["tax_rate"] = $taxFee['rate'];
				}
				$input['fee'][] = $feeArray;
			}
		}
		
 		$url = 'https://app.orderbee.be/api/Woocommerce/NewOrder';
		$obfrwc_server_auth_id = get_option('obfrwc_server_auth_id');
        $arr_auth_param = array(
			'consumer_id' => $obfrwc_server_auth_id,
            'data' => json_encode($input),
            'http'     => $_SERVER["HTTP_HOST"]
        );
        $response      = wp_remote_post($url, ['body' => $arr_auth_param]);
		
		#### Keep backup table up to date
		global $wpdb;
		$table_name = $wpdb->prefix."obfrwc_pushjobs"; 
		$error = wp_remote_retrieve_response_code($response);
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		if($error!='201'){
			$wpdb->query("INSERT INTO $table_name (uid, type, error) 
    					SELECT $order_id, 2, $error WHERE (SELECT 1 
                           FROM $table_name 
                           WHERE uid=$order_id
                             AND type=2) IS NULL");
		}else{
					$wpdb->delete( $table_name, array( 'uid' => $order_id, 'type' => 2 ) );
		}
		
        
		
    }

}
