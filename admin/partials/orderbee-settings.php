<?php

if (!current_user_can('manage_options')) {

    wp_die(__('You do not have sufficient permissions to access this page.'));

}

$obj_OrderBee_Settings = new OrderBee_Settings();

?>

<div class="wrap">

    <h1 style="display: none"></h1>

    <p id="obfrwc_H1"><?php echo __('Settings', 'orderbee'); ?></p>    

    <?php

    if (!empty($_POST['btn-connect'])) {

        update_option('obfrwc_server_username', !empty($_POST['obfrwc_server_username']) ? sanitize_text_field($_POST['obfrwc_server_username']) : '');

        update_option('obfrwc_server_password', !empty($_POST['obfrwc_server_password']) ? sanitize_text_field($_POST['obfrwc_server_password']) : '');

        echo $obj_OrderBee_Settings->sync_wc_api_server();

    }



    if (!empty(get_option('obfrwc_server_auth_id'))) {

        ?>

        <p class="description">

            <?php echo __('You are connected with OrderBee', 'orderbee'); ?>

        </p>

        <br/>

        <form method="post">

            <input type="hidden" value="1" name="is_disconnect" />

            <?php

            submit_button(__('Disconnect', 'orderbee'), 'primary', 'btn-disconnect');

            ?>

        </form>        

        <?php
		
    }

    else {

        ?>

        <p class="description">

            <?php echo __('Please use your OrderBee credentials', 'orderbee'); ?>

        </p>

        <form method="post">

            <table class="form-table" role="presentation">

                <tbody>

                    <tr>

                        <th scope="row">

                            <label for="blogname"><?php echo __('Username', 'orderbee'); ?></label>

                        </th>

                        <td>

                            <input name="obfrwc_server_username" type="text" id="obfrwc_server_username" value="<?php echo get_option('obfrwc_server_username'); ?>" class="regular-text" required />

                        </td>

                    </tr>



                    <tr>

                        <th scope="row">

                            <label for="blogdescription"><?php echo __('Password', 'orderbee'); ?></label>

                        </th>

                        <td>

                            <input name="obfrwc_server_password" type="password" id="obfrwc_server_password" aria-describedby="taglineobfrwc_server_password" value="<?php echo get_option('obfrwc_server_password'); ?>" class="regular-text" required />

                        </td>

                    </tr>



                </tbody>

            </table>

            <?php

            submit_button(__('Connect', 'orderbee'), 'primary', 'btn-connect');

            ?>

        </form>



    </div>

<?php } 

	
?>



<div>

    <?php

    echo $obj_OrderBee_Settings->get_orderbee_ad_data();

    ?>

</div>