<?php
function orderbee_admin_bar( $wp_admin_bar ) {
	global $wp_admin_bar, $wpdb;
	$countPushes = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."obfrwc_pushjobs");
	if($countPushes==0){
		$notePushJobs = __( 'Synched!', 'orderbee' );
	}else{
		$notePushJobs = __( 'Sync in progress!', 'orderbee' );
	}
	if ( current_user_can( 'manage_options' ) && !empty(get_option('obfrwc_server_auth_id'))) {
	
		$wp_admin_bar->add_menu(
			[
				'id'    => 'orderbee',
        		'title' => '<img src="'.plugins_url('orderbee/favicon.png?v='.ORDERBEE_VERSION).'" class="ab-icon dashicons" style="height:24px">'.__( 'OrderBee', 'orderbee' ).' || '.$notePushJobs,
			]
		);
		$wp_admin_bar->add_menu(
			[
				'parent'    => 'orderbee',
				'id'    => 'orderbee-app',
        		'title' => '<span style="font-weight:bold; color:#00FF00">'.__( 'Go to OrderBee panel', 'orderbee' ).'</span>',
				'href'   => 'https://app.orderbee.be',
				'meta'  => array(
					'target'=> '_blank'
					)
			]
		);
		$wp_admin_bar->add_menu(
			[
				'parent'    => 'orderbee',
				'id'    => 'orderbee-settings',
        		'title' => __( 'Settings', 'orderbee' ),
				'href'   => wp_nonce_url( admin_url( 'admin.php?page=obfrwc_page_settings' ), $action . '_all' ),
			]
		);
		$wp_admin_bar->add_menu(
			[
				'parent'    => 'orderbee',
				'id'    => 'orderbee-changelog',
        		'title' => __( 'Changelog', 'orderbee' ),
				'href'   => wp_nonce_url( admin_url( 'admin.php?page=obfrwc_page_changelog' ), $action . '_all' ),
			]
		);
		$wp_admin_bar->add_menu(
			[
				'parent'    => 'orderbee',
				'id'    => 'orderbee-pickuplocator',
        		'title' => __( 'Pickup Locator', 'orderbee' ),
				'href'   => wp_nonce_url( admin_url( 'admin.php?page=obfrwc_page_pickup_locator' ), $action . '_all' ),
			]
		);
	}
}
add_action( 'admin_bar_menu', 'orderbee_admin_bar', PHP_INT_MAX -9  );