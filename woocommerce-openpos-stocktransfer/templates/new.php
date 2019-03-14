<div class="wrap op-conten-wrap">
    <div class="row">
        <div class="col-md-12">
            <a class="btn btn-danger pull-left" href="<?php echo admin_url('admin.php?page=op-stock-transfer'); ?>" role="button" style="margin-right: 5px;"><?php echo __('Back','openpos'); ?></a>
        </div>
    </div>
    <h1><?php echo __( 'New Transfer', 'woo-book-price' ); ?></h1>

        <div class="form-container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <form class="form-horizontal" id="book-form">
                        <input type="hidden" name="transfer_id" value="0">
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label"><?php echo __( 'Title', 'woo-book-price' ); ?></label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="book_title" name="title" placeholder="Title">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label"><?php echo __( 'From Outlet', 'woo-book-price' ); ?></label>
                            <div class="col-sm-10">
                                <select class="form-control" name="from_warehouse_id">
                                    <option value="-1"><?php echo __('Please choose Outlet','openpos'); ?></option>
                                    <?php foreach($warehouses as $warehouse): ?>
                                    <option value="<?php echo $warehouse['id']; ?>"><?php echo $warehouse['name']; ?></option>
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
                                        <option value="<?php echo $warehouse['id']; ?>"><?php echo $warehouse['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label"><?php echo __( 'Staffs Allow Receive', 'woo-book-price' ); ?></label>
                            <div class="col-sm-10">
                                <select class="form-control" name="staffs[]" multiple>
                                    <?php foreach($cashiers as $cashier):   ?>
                                        <option value="<?php echo $cashier->ID; ?>"><?php echo $cashier->display_name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="to_date" class="col-sm-2 control-label"><?php echo __( 'Note', 'woo-book-price' ); ?></label>
                            <div class="col-sm-10">
                                <textarea name="note" style="width: 100%;"></textarea>
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
                    </form>
                </div>
            </div>

            <hr>
            <div class="row">
                <div class="col-md-4 col-md-offset-4">
                    <form class="form-inline" id="barcode-scan-frm" >
                        <div class="form-group">

                            <div class="input-group">
                                <div class="input-group-addon">
                                    <?php echo __( 'Barcode', 'woo-book-price' ); ?>
                                </div>
                                <input type="text" class="form-control" id="barcodeScanInput" name="barcodeScanInput" placeholder="Enter barcode to add">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary"><?php echo __( 'Scan', 'woo-book-price' ); ?></button>
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
                url: "<?php echo admin_url( 'admin-ajax.php?transfer_id=0' ); ?>",
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

        $('#barcode-scan-frm').on('submit',function(e){
            e.preventDefault();
            var from_warehouse_id = $('select[name="from_warehouse_id"]').val();
            var transfer_id = $('input[name="transfer_id"]').val();
            var barcode = $('input[name="barcodeScanInput"]').val();
            if(from_warehouse_id > -1 && barcode.length > 0)
            {
                $.ajax({
                    url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
                    type: 'post',
                    data: {action: 'op_scan_barcode',transfer_id: transfer_id,from_warehouse_id: from_warehouse_id, barcode: barcode },
                    dataType: 'json',
                    beforeSend:function(){

                    },
                    success: function(response){
                        $('input[name="barcodeScanInput"]').select();
                        if(response.status == 1)
                        {
                            var transfer_id = response['data']['transfer_id'];
                            console.log(transfer_id);
                            if(transfer_id)
                            {
                                $('input[name="transfer_id"]').val(transfer_id);
                            }
                            table.ajax.url("<?php echo admin_url( 'admin-ajax.php?transfer_id=' ); ?>"+transfer_id).load();

                        }else {
                            alert(response.message);
                        }
                    }
                });
            }else {
                if(from_warehouse_id > -1)
                {
                    alert("<?php echo __( 'Please enter barcode', 'woo-book-price' ); ?>");
                }else {
                    alert("<?php echo __( 'Please choose from Outlet', 'woo-book-price' ); ?>");
                }

            }

        })

    })( jQuery );
</script>