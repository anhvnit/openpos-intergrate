<div class="wrap op-conten-wrap">
    <h1><?php echo __( 'Edit Transfer', 'woo-book-price' ); ?></h1>

    <div class="form-container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <form class="form-horizontal" id="book-form">
                    <input type="hidden" name="transfer_id" value="<?php echo $current_transfer['transfer_id']; ?>">
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label"><?php echo __( 'Title', 'woo-book-price' ); ?></label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" value="<?php echo $current_transfer['title']; ?>" id="book_title" name="title" placeholder="Title">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label"><?php echo __( 'From Outlet', 'woo-book-price' ); ?></label>
                        <div class="col-sm-10">
                            <select class="form-control" name="from_warehouse_id">
                                <option value="-1"><?php echo __('Please choose Outlet','openpos'); ?></option>
                                <?php foreach($warehouses as $warehouse): ?>
                                    <option value="<?php echo $warehouse['id']; ?>" <?php echo $warehouse['id'] == $current_transfer['from_warehouse_id'] ? 'selected':''; ?>><?php echo $warehouse['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label"><?php echo __( 'To Outlet', 'woo-book-price' ); ?></label>
                        <div class="col-sm-10">
                            <select class="form-control" name="to_warehouse_id">
                                <option value="-1"><?php echo __('Please choose Outlet','openpos'); ?></option>
                                <?php foreach($warehouses as $warehouse): ?>
                                    <option value="<?php echo $warehouse['id']; ?>" <?php echo $warehouse['id'] == $current_transfer['to_warehouse_id'] ? 'selected':''; ?> ><?php echo $warehouse['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label"><?php echo __( 'Staffs Allow Receive', 'woo-book-price' ); ?></label>
                        <div class="col-sm-10">
                            <select class="form-control" name="staffs[]" multiple>
                                <?php foreach($cashiers as $cashier):   ?>
                                    <option value="<?php echo $cashier->ID; ?>" <?php echo in_array($cashier->ID,$current_transfer['staffs']) ? 'selected':''; ?> ><?php echo $cashier->display_name; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="to_date" class="col-sm-2 control-label"><?php echo __( 'Note', 'woo-book-price' ); ?></label>
                        <div class="col-sm-10">
                            <textarea name="note" style="width: 100%;"><?php echo $current_transfer['note']; ?></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label"><?php echo __( 'Import Product List CSV', 'woo-book-price' ); ?></label>
                        <div class="col-sm-5">
                            <input type="file" id="csv-file-import">
                            <p class="help-block"><?php echo __( 'Upload file follow format of outlet csv after updated', 'woo-book-price' ); ?></p>
                        </div>
                        <div class="col-sm-5">
                            <button type="button" id="import-csv-btn" class="btn btn-info pull-right"><?php echo __( 'Import', 'woo-book-price' ); ?></button>
                            <button type="button" id="download-csv-btn" class="btn btn-default pull-left"><?php echo __( 'Download Template CSV', 'woo-book-price' ); ?></button>
                        </div>

                    </div>

                    <button type="button" id="book-save-btn" class="btn btn-primary pull-right"><?php echo __( 'Save', 'woo-book-price' ); ?></button>
                    <?php if($current_transfer['transfer_status']  == 1 ): ?>
                    <button type="button"  data-id="<?php echo $current_transfer['transfer_id']; ?>" class="btn btn-primary pull-right transfer-now" style="margin-right: 5px;"><?php echo __( 'Transfer Now', 'woo-book-price' ); ?></button>
                    <?php endif; ?>
                </form>
            </div>
        </div>

    </div>

    <br class="clear">
    <div class="product-list-container">
        <table id="op-transfer-grid" class="table table-condensed table-hover table-striped op-transfer-grid">
            <thead>
            <tr>
                <th data-column-id="product_id" data-identifier="true" ><?php echo __( 'Product ID', 'woo-book-price' ); ?></th>
                <?php if($_has_openpos): ?>
                    <th data-column-id="barcode" data-identifier="false" data-sortable="false"><?php echo __( 'Barcode', 'woo-book-price' ); ?></th>
                <?php endif; ?>
                <th data-column-id="sku" data-identifier="false"  data-sortable="false"><?php echo __( 'Sku', 'woo-book-price' ); ?></th>
                <th data-column-id="product_name" data-identifier="false" data-sortable="false"><?php echo __( 'Product Name', 'woo-book-price' ); ?></th>
                <th data-column-id="qty" data-sortable="false"><?php echo __( 'Current QTY', 'woo-book-price' ); ?></th>
                <th data-column-id="adjust_qty" data-sortable="false"><?php echo __( 'Transfer Qty', 'woo-book-price' ); ?></th>
                <th data-column-id="view_url" class="text-right" data-sortable="false"></th>
            </tr>
            </thead>
        </table>
    </div>


    <br class="clear">
</div>
<script type="text/javascript">

    (function($) {
        "use strict";

        var files = new Array();
        var book_id = 0;

        var table = $('#op-transfer-grid').DataTable({
            "searching": false,
            "processing": true,
            "serverSide": true,
            ajax: {
                url: "<?php echo admin_url( 'admin-ajax.php?transfer_id='.$current_transfer['transfer_id'] ); ?>",
                type: 'post',
                data: {action: 'op_stock_transfer_product_ajax_list',id: $('input[name="transfer_id"]').val() }
            },
            pageLength : 10
        } );




        $('input#csv-file-import').change(function(event) {
            files = event.target.files;
        });

        $('#download-csv-btn').click(function () {
            $.ajax({
                url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
                type: 'post',
                data: $('#book-form').serialize()+'&action=op_download_product_csv',
                dataType: 'json',
                beforeSend:function(){

                },
                success: function(response){
                    if(response['status'] == 1)
                    {
                        window.location = response['data']['export_file'];
                    }
                }
            });
        });
        $('#import-csv-btn').click(function () {
            var from_warehouse_id = $('select[name="from_warehouse_id"]').val();
            from_warehouse_id = parseInt(from_warehouse_id);

            if(from_warehouse_id > -1 )
            {
                if(files.length > 0)
                {
                    var formData = new FormData();

                    formData.append("action", "op_upload_product_csv");
                    formData.append("transfer_id", $('input[name="transfer_id"]').val());
                    formData.append("from_warehouse_id", from_warehouse_id);

                    formData.append("file", files[0]);

                    $.ajax({
                        url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
                        type: 'post',
                        dataType: 'json',
                        data: formData,
                        contentType: false,
                        processData: false,
                        beforeSend:function(){
                            $('body').addClass('op_loading');
                        },
                        success:function(data){
                            if(data.status == 1)
                            {
                                var transfer_id = data['data']['transfer_id'];
                                if(transfer_id)
                                {
                                    $('input[name="transfer_id"]').val(transfer_id);
                                }
                                table.ajax.url("<?php echo admin_url( 'admin-ajax.php?transfer_id=' ); ?>"+transfer_id).load();


                            }else
                            {
                                alert(data.message);
                            }
                            $('body').removeClass('op_loading');

                        },
                        error:function(){
                            $('body').removeClass('op_loading');
                        }
                    });
                }else {
                    alert("<?php echo __( 'Please choose file', 'woo-book-price' ); ?>");
                }
            }else {
                alert("<?php echo __( 'Please choose from Outlet', 'woo-book-price' ); ?>");
            }


        });
        $('#book-save-btn').click(function () {
            if($('input[name="title"]').val().length < 1)
            {
                alert("<?php echo __( 'Please enter title', 'woo-book-price' ); ?>");
            }else {
                $.ajax({
                    url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
                    type: 'post',
                    data: $('#book-form').serialize()+'&action=op_save_transfer',
                    dataType: 'json',
                    beforeSend:function(){
                        $('body').addClass('op_loading');
                    },
                    success: function(response){
                        $('body').removeClass('op_loading');
                        if(response['status'] == 1)
                        {
                            var transfer_id = response['data']['transfer_id'];
                            window.location = '<?php echo admin_url('admin.php?page=op-stock-transfer&action=edit')?>'+'&id=' + transfer_id;
                        }else {
                            alert(response['message']);
                        }
                    },
                    error:function(){
                        $('body').removeClass('op_loading');
                    }
                });
            }
        });
        $(document).on('click','.save-row',function(){
            var id = $(this).data('id');
            var input_selected = $(document).find('#row-'+id);
            var qty = input_selected.val();
            if(qty.length < 1)
            {
                alert("<?php echo __( 'Please enter qty', 'woo-book-price' ); ?>");
            }else {
                $.ajax({
                    url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
                    type: 'post',
                    data: {action: 'op_update_transfer_product',id: id, qty: qty},
                    dataType: 'json',
                    beforeSend:function(){
                        input_selected.prop('disabled',true);
                    },
                    success: function(response){
                        input_selected.prop('disabled',false);
                    }
                });
            }

        });

        $(document).on('click','.transfer-now',function(){
            var id = $(this).data('id');

            $.ajax({
                url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
                type: 'post',
                data: {action: 'op_update_transfer_send',id: id},
                dataType: 'json',
                beforeSend:function(){


                },
                success: function(response){
                    if(response.status == 1)
                    {
                        window.location = '<?php echo admin_url('admin.php?page=op-stock-transfer')?>';
                    }else {
                        alert(response.message);
                    }
                }
            });


        });

        $(document).on('click','.del-row',function(){
            var id = $(this).data('id');
            var input_selected = $(document).find('#row-'+id);

            $.ajax({
                url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
                type: 'post',
                data: {action: 'op_delete_transfer_product',id: id},
                dataType: 'json',
                beforeSend:function(){
                    input_selected.prop('disabled',true);
                },
                success: function(response){
                    table.ajax.reload();
                }
            });
        });

    })( jQuery );
</script>