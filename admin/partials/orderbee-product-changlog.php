<?php

if (!current_user_can('manage_options')) {

    wp_die(__('You do not have sufficient permissions to access this page.'));

}

$obj_OrderBee_Settings = new OrderBee_Settings();

$obj_orderbee_log      	= $obj_OrderBee_Settings->get_orderbee_log();
$obj_orderbee_pushjobs 	= $obj_OrderBee_Settings->get_orderbee_pushjobs();

?>



<div class="wrap">

    <h1 style="display: none"></h1>

    <p id="obfrwc_H1"><?php echo  __('Change Log', 'orderbee'); ?></p>

<?php   if (!empty(get_option('obfrwc_server_auth_id'))) { ?>

    <hr class="wp-header-end">

    <p class="search-box">

        <a href="admin.php?page=obfrwc_change_log" class="button"><?php echo __('Refresh Data', 'orderbee'); ?></a>

        <br/>

    </p>

    <br class="clear">
<h2>Failed push jobs</h2>
    <p><?php echo __('This pushjobs will be tried again in the next 5 minutes.', 'orderbee'); ?></p>    
    <div style="max-height: 200px; overflow-y: auto;margin-top: 10px;">
        <?php 
        if(!empty($obj_orderbee_pushjobs['body'])){ 
            ?>

        <table class="wp-list-table widefat fixed striped">

            <thead>

                <tr>

                    <?php
					foreach ($obj_orderbee_pushjobs['size'] as $key=>$value){
						$obj_orderbee_pushjobs_size[$key] = $value;
					}
                    foreach ($obj_orderbee_pushjobs['header'] as $key=>$str_header) {

                        ?>

                        <th scope="col" class="manage-column" style="width:<?php echo $obj_orderbee_pushjobs_size[$key] ?>px"><?php echo $str_header; ?></th>

                        <?php

                    }

                    ?>

                </tr>

            </thead>



            <tbody id="the-list">

                <?php foreach ($obj_orderbee_pushjobs['body'] as $arr_log) { ?>

                    <tr>

                        <?php

                        foreach ($arr_log as $str_log) {

                            ?>

                            <td><?php echo $str_log; ?></td>

                            <?php

                        }

                        ?>

                    </tr>

                <?php } ?>

            </tbody>



            <tfoot>

                <tr>

                    <?php

                    foreach ($obj_orderbee_pushjobs['header'] as $str_header) {

                        ?>

                        <th scope="col" class="manage-column"><?php echo $str_header; ?></th>

                        <?php

                    }

                    ?>

                </tr>

            </tfoot>



        </table>  
        <?php 
        }
        else{
            echo __('No outstanding pushjobs, we\'re done.', 'orderbee');
        }
        ?>

    </div>
    <br class="clear">
<h2>Connection history</h2>
    <div style="max-height: 500px; overflow-y: auto;margin-top: 10px;">
        <?php 
        if(!empty($obj_orderbee_log->table->body)){ 
            ?>

        <table class="wp-list-table widefat fixed striped">

            <thead>

                <tr>

                    <?php
					foreach ($obj_orderbee_log->table->size as $key=>$value){
						$obj_orderbee_log_size[$key] = $value;
					}
                    foreach ($obj_orderbee_log->table->header as $key=>$str_header) {

                        ?>

                        <th scope="col" class="manage-column" style="width:<?php echo $obj_orderbee_log_size[$key] ?>px"><?php echo $str_header; ?></th>

                        <?php

                    }

                    ?>

                </tr>

            </thead>



            <tbody id="the-list">

                <?php foreach ($obj_orderbee_log->table->body as $arr_log) { ?>

                    <tr>

                        <?php

                        foreach ($arr_log as $str_log) {

                            ?>

                            <td><?php echo $str_log; ?></td>

                            <?php

                        }

                        ?>

                    </tr>

                <?php } ?>

            </tbody>



            <tfoot>

                <tr>

                    <?php

                    foreach ($obj_orderbee_log->table->header as $str_header) {

                        ?>

                        <th scope="col" class="manage-column"><?php echo $str_header; ?></th>

                        <?php

                    }

                    ?>

                </tr>

            </tfoot>



        </table>  
        <?php 
        }
        else{
            echo 'Data not found.';
        }
        ?>

    </div>
<?php }else{ echo __('You need to connect with OrderBee on the setup page first, to see this page.', 'orderbee'); } ?>
    <br class="clear">

</div>





