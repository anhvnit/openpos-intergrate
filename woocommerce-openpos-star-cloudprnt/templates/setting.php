<div class="wrap op-conten-wrap">
    <h1 style="text-align: center;margin-bottom: 15px;"><?php echo __( 'Star CloudPRNT Setting', 'woocommerce-openpos-star-cloudprnt' ); ?></h1>
    <div class="row">
        <div class="col-sm-2"></div>
        <div class="col-sm-10">
            <div class="bs-example" data-example-id="simple-ol">
                <ol>
                    <li><?php echo __( 'Setup your Star CloudPRNT printer onto your network, and run a self-test print to get the IP address.', 'woocommerce-openpos-star-cloudprnt' ); ?></li>
                    <li><?php echo __( 'Type the printer IP address into a web browser to access the web interface. Default details are username "root" and password "public".', 'woocommerce-openpos-star-cloudprnt' ); ?></li>
                    <li><?php echo __( 'Navigate to Configuration > CloudPRNT.', 'woocommerce-openpos-star-cloudprnt' ); ?></li>
                    <li><?php echo __( 'Enter the "<b>Your Cloud Server URL</b>" below into the Server URL field.', 'woocommerce-openpos-star-cloudprnt' ); ?></li>
                    <li><?php echo __( 'Click Submit and then click Save.', 'woocommerce-openpos-star-cloudprnt' ); ?></li>
                    <li><?php echo __( 'Choose Save > Configuration Printing > Restart Device.', 'woocommerce-openpos-star-cloudprnt' ); ?></li>
                    <li><?php echo __( 'Click Execute and wait for 2 to 3 minutes.', 'woocommerce-openpos-star-cloudprnt' ); ?></li>
                    <li><?php echo __( 'Once the printer has rebooted, go back to this page and refresh it. You will notice the printer has been populated in the printer list.', 'woocommerce-openpos-star-cloudprnt' ); ?></li>
                </ol>
                <dl>
                    <dt> Requirements</dt>
                    <dd>PHP 5.6 or greater.</dd>
                    <dd>Star TSP650II, TSP700II, TSP800II or SP700 series printer with a IFBD-HI01X/HI02X interface.</dd>
                    <dd>Recommended printer interface firmware 1.4 or greater.</dd>

                </dl>
            </div>

        </div>
    </div>
    <div class="row">
        <div class="col-md-offset-2 col-sm-10" id="setting-notification">

        </div>
    </div>

    <form class="form-horizontal" id="_op_cloud_prnt_form">
        <input type="hidden" name="action" value="op_print_setting">
        <div class="form-group">
            <label for="inputEmail3" class="col-sm-2 control-label"><?php echo __( 'Your Cloud Server URL', 'woocommerce-openpos-star-cloudprnt' ); ?></label>
            <div class="col-sm-10">
                <input type="text" disabled class="form-control"  value="<?php echo esc_url(OPENPOS_CLOUDPRNT_URL.'/cloudprnt/cloudprnt.php')?>">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __( 'Enable for OpenPOS', 'woocommerce-openpos-start-cloudprnt' ); ?></label>
            <div class="col-sm-4">
                <select class="form-control" name="_op_star_cloud_prnt[active]">
                    <option value="0" <?php echo $setting['active'] == 0 ? 'selected': ''; ?>><?php echo __( 'No', 'woocommerce-openpos-star-cloudprnt' ); ?></option>
                    <option value="1" <?php echo $setting['active'] == 1 ? 'selected': ''; ?> ><?php echo __( 'Yes', 'woocommerce-openpos-star-cloudprnt' ); ?></option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __( 'Default Printer', 'woocommerce-openpos-start-cloudprnt' ); ?></label>
            <div class="col-sm-4">
                <select class="form-control" name="_op_star_cloud_prnt[default_printer]">
                    <option value=""><?php echo __( 'Please choose', 'woocommerce-openpos-star-cloudprnt' ); ?></option>
                    <?php foreach($printers as $key => $printer):  ?>
                        <option value="<?php echo $key; ?>"  <?php echo $setting['default_printer'] == $key ? 'selected': ''; ?>><?php echo $printer['name']; ?><?php echo isset($printer['ClientType']) ? ' - '.$printer['ClientType'] : ''; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="button" id="save-setting" class="btn btn-default"><?php echo __( 'Save', 'woocommerce-openpos-star-cloudprnt' ); ?></button>
            </div>
        </div>
    </form>
    <br class="clear">
</div>

<script type="text/javascript">

    (function($) {
        "use strict";

        $(document).on('click','#save-setting',function(){
            $.ajax({
                url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
                type: 'post',
                data: $('#_op_cloud_prnt_form').serialize(),
                dataType: 'json',
                beforeSend:function(){

                },
                success: function(response){
                    if(response['status'] == 1)
                    {
                        $('#setting-notification').html('<p class="bg-success">'+response['message']+'</p>');
                        var timeOutVar = setTimeout(function(){
                            $('#setting-notification').empty();
                        }, 5000);
                    }
                }
            });
        });



    })( jQuery );
</script>