<div class="wrap op-conten-wrap">
    <h1 style="text-align: center;margin-bottom: 15px;"><?php echo __( 'Google Cloud Print Setting', 'woocommerce-openpos-star-cloudprnt' ); ?></h1>
    <div class="row">
        <div class="col-sm-2"></div>
        <div class="col-sm-10">
            <div class="bs-example" data-example-id="simple-ol">
                <dt>Google OAuth Prerequisites</dt>
                <ol>
                    <li><?php echo __( 'Create Google API project and get OAuth credentials.', 'woocommerce-openpos-star-cloudprnt' ); ?></li>
                    <li><?php echo __( 'Create Google OAuth Credentials', 'woocommerce-openpos-star-cloudprnt' ); ?></li>
                    <li><?php echo __( 'Create new project and get the corresponding OAuth credentials using Google developer console https://console.developers.google.com/' ); ?></li>
                    <li><?php echo __( 'Select APIS & AUTH –> credentials from the left menu.', 'woocommerce-openpos-star-cloudprnt' ); ?></li>
                    <li><?php echo __( 'Click Create new Client ID button. A popup will appear. In Authorized redirect URIs text area enter url at field "Authorized redirect URIs".', 'woocommerce-openpos-star-cloudprnt' ); ?></li>
                    <li><?php echo __( 'After submitting this form, we can get the client Id, secret key etc.', 'woocommerce-openpos-star-cloudprnt' ); ?></li>
                </ol>

            </div>

        </div>
    </div>
    <div class="row">
        <div class="col-md-offset-2 col-sm-10" id="setting-notification">

        </div>
    </div>

    <form class="form-horizontal" id="_op_cloud_prnt_form">
        <input type="hidden" name="action" value="op_google_print_setting">

        <div class="form-group">
            <label for="inputEmail3" class="col-sm-2 control-label"><?php echo __( 'Authorized redirect URIs', 'woocommerce-openpos-star-cloudprnt' ); ?></label>
            <div class="col-sm-10">
                <input type="text" disabled class="form-control"  value="<?php echo esc_url(OPENPOS_GOOGLE_PRINT_URL.'/google-cloud-print/oAuthRedirect.php')?>">
            </div>
        </div>
        <div class="form-group">
            <label for="inputEmail3" class="col-sm-2 control-label"><?php echo __( 'Google Client Id', 'woocommerce-openpos-star-cloudprnt' ); ?></label>
            <div class="col-sm-10">
                <input type="text"  class="form-control" name="_op_google_cloud_print[client_id]"  value="<?php echo isset($setting['client_id']) ? $setting['client_id'] : ''; ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="inputEmail3" class="col-sm-2 control-label"><?php echo __( 'Google Client Secret', 'woocommerce-openpos-star-cloudprnt' ); ?></label>
            <div class="col-sm-10">
                <input type="text"  class="form-control" name="_op_google_cloud_print[client_secret]"  value="<?php echo isset($setting['client_id']) ? $setting['client_secret'] : ''; ?>">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __( 'Enable for OpenPOS', 'woocommerce-openpos-start-cloudprnt' ); ?></label>
            <div class="col-sm-4">
                <select class="form-control" name="_op_google_cloud_print[active]">
                    <option value="0" <?php echo $setting['active'] == 0 ? 'selected': ''; ?>><?php echo __( 'No', 'woocommerce-openpos-star-cloudprnt' ); ?></option>
                    <option value="1" <?php echo $setting['active'] == 1 ? 'selected': ''; ?> ><?php echo __( 'Yes', 'woocommerce-openpos-star-cloudprnt' ); ?></option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __( 'Access Token', 'woocommerce-openpos-start-cloudprnt' ); ?></label>
            <div class="col-sm-4">
                <input type="text" disabled class="form-control"  value="<?php echo isset($setting['accessToken']) ? $setting['accessToken'] : ''; ?>">
            </div>
            <div class="col-sm-6"><p><a href="<?php echo esc_url(OPENPOS_GOOGLE_PRINT_URL.'/google-cloud-print/index.php')?>">Click here to get new access token</a></p></div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __( 'Default Printer', 'woocommerce-openpos-start-cloudprnt' ); ?></label>
            <div class="col-sm-4">
                <select class="form-control" name="_op_google_cloud_print[default_printer]">
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