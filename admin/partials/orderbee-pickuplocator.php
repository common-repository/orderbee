<?php

if (!current_user_can('manage_options')) {

    wp_die(__('You do not have sufficient permissions to access this page.'));

}

//$obj_OrderBee_Settings = new OrderBee_Settings();

?>

<div class="wrap">

    <h1 style="display: none"></h1>

    <p id="obfrwc_H1"><?php echo __('Pickup Locator', 'orderbee'); ?></p>    

<?php   if (!empty(get_option('obfrwc_server_auth_id'))) { ?>
    
        <p class="description">

            <?php echo __('Make pickup points available at checkout', 'orderbee'); ?>

        </p>

        <br/>

     <i style="color:#B60508; font-weight:bold"><?php echo __('This function is only for beta-testers', 'orderbee'); ?></i>     

<?php }else{ echo __('You need to connect with OrderBee on the setup page first, to see this page.', 'orderbee'); } ?>

    <br class="clear">
</div>