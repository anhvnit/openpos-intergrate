<div class="wrap op-conten-wrap">
    <div class="row">
        <div class="col-md-12">
            <a class="btn btn-danger pull-left" href="<?php echo admin_url('admin.php?page=op-stock-transfer'); ?>" role="button" style="margin-right: 5px;"><?php echo __('Back','openpos'); ?></a>
        </div>
    </div>
    <h1><?php echo __( 'Receive Transfer', 'woo-book-price' ); ?></h1>

    <div class="form-container">
        <div class="row">
            <div class="col-md-6 col-md-offset-3" style="background: hsla(0, 0%, 67%, 0.16); padding: 10px; border-radius: 15px; border: solid 1px #999;">
                <div class="row">
                    <div class="col-md-12 pull-right">
                        <a class="btn btn-warning pull-right" target="_blank" href="<?php echo admin_url('admin-ajax.php?action=op_print_transfer&id='.$current_transfer['transfer_id'])?>" role="button" style="margin-right: 5px;"><span class="glyphicon glyphicon-print"></span></a>
                    </div>
                </div>
                <form class="form-horizontal" id="book-form">
                    <input type="hidden" name="transfer_id" value="<?php echo $current_transfer['transfer_id']; ?>">
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-4 control-label"><?php echo __( 'Title', 'woo-book-price' ); ?></label>
                        <div class="col-sm-8">
                            <p style="padding-top: 7px;"><?php echo $current_transfer['title']; ?></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-4 control-label"><?php echo __( 'From Outlet', 'woo-book-price' ); ?>

                        </label>
                        <div class="col-sm-8">
                            <label class="control-label">
                                <?php foreach($warehouses as $warehouse): ?>
                                    <?php echo $warehouse['id'] == $current_transfer['from_warehouse_id'] ? $warehouse['name']:''; ?>
                                <?php endforeach; ?>

                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-4 control-label"><?php echo __( 'To Outlet', 'woo-book-price' ); ?></label>
                        <div class="col-sm-8">
                            <label class="control-label">
                                <?php foreach($warehouses as $warehouse): ?>
                                    <?php echo $warehouse['id'] == $current_transfer['to_warehouse_id'] ?  $warehouse['name']:''; ?>
                                <?php endforeach; ?>

                            </label>

                        </div>
                    </div>


                    <div class="form-group">
                        <label for="to_date" class="col-sm-4 control-label"><?php echo __( 'Note', 'woo-book-price' ); ?></label>
                        <div class="col-sm-8">
                            <p style="padding-top: 7px;"><?php echo $current_transfer['note']; ?></p>
                        </div>
                    </div>
                    <?php if($current_transfer['transfer_status'] == 3):?>
                        <div class="form-group">
                            <label for="to_date" class="col-sm-4 control-label"><?php echo __( 'Received By', 'woo-book-price' ); ?></label>
                            <div class="col-sm-8">
                                <p style="padding-top: 7px;"><?php echo $received_by; ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if($current_transfer['transfer_status'] == 4):?>
                        <div class="form-group">
                            <label for="to_date" class="col-sm-4 control-label"><?php echo __( 'Declined By', 'woo-book-price' ); ?></label>
                            <div class="col-sm-8">
                                <p style="padding-top: 7px;"><?php echo $received_by; ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if($current_transfer['transfer_status'] == 2):?>
                    <div class="row">
                        <div class="col-sm-4"></div>
                        <div class="col-sm-8">
                            <button type="button" id="book-receive-btn" data-action="receive" data-id="<?php echo $current_transfer['transfer_id']; ?>" class="btn transfer-action btn-primary"><?php echo __( 'Receive', 'woo-book-price' ); ?></button>&nbsp;
                            <button type="button" id="book-decline-btn" data-action="decline" data-id="<?php echo $current_transfer['transfer_id']; ?>"  class="btn transfer-action btn-danger" style="margin-right: 5px;"><?php echo __( 'Decline', 'woo-book-price' ); ?></button>&nbsp;

                        </div>
                    </div>
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
                <th data-column-id="adjust_qty" data-sortable="false"><?php echo __( 'Transfer Qty', 'woo-book-price' ); ?></th>
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
                url: "<?php echo admin_url( 'admin-ajax.php?source=receive&transfer_id='.$current_transfer['transfer_id'] ); ?>",
                type: 'post',
                data: {action: 'op_stock_transfer_product_ajax_list',id: $('input[name="transfer_id"]').val() }
            },
            pageLength : 10
        } );





        $(document).on('click','.transfer-action',function(){
            var id = $(this).data('id');
            var action = $(this).data('action');

            $.ajax({
                url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
                type: 'post',
                data: {action: 'op_update_transfer_receive',id: id, action_type: action},
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



    })( jQuery );
</script>