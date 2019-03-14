<div class="wrap op-conten-wrap">
    <h1><?php echo __( 'All Stock Transers', 'woo-book-price' ); ?></h1>
    <form id="op-order-list">
        <div class="row">
            <div class="col-md-4 col-md-offset-8" style="margin-bottom: 15px;">
                <a class="btn btn-primary pull-right" href="<?php echo admin_url('admin.php?page=op-stock-transfer&action=new'); ?>" role="button"><?php echo __( 'New Transfer', 'woo-book-price' ); ?></a>
            </div>
        </div>
        <table id="op-transfer-grid" class="table table-condensed table-hover table-striped op-transfer-grid">
            <thead>
            <tr>
                <th data-column-id="id" data-identifier="true" data-type="numeric"><?php echo __( 'ID', 'woo-book-price' ); ?></th>
                <th data-column-id="title" data-sortable="false"><?php echo __( 'Title', 'woo-book-price' ); ?></th>
                <th data-column-id="from_warehouse_id" data-sortable="false"><?php echo __( 'From Outlet', 'woo-book-price' ); ?></th>
                <th data-column-id="to_warehouse_id" data-identifier="false" data-sortable="false"><?php echo __( 'To Outlet', 'woo-book-price' ); ?></th>
                <th data-column-id="total_qty" data-identifier="false"  data-sortable="false"><?php echo __( 'Total Qty', 'woo-book-price' ); ?></th>

                <th data-column-id="transfer_date" data-sortable="false"><?php echo __( 'Created At', 'woo-book-price' ); ?></th>
                <th data-column-id="created_by" data-sortable="false"><?php echo __( 'Created By', 'woo-book-price' ); ?></th>
                <th data-column-id="transfer_status" data-sortable="false"><?php echo __( 'Status', 'woo-book-price' ); ?></th>
                <th data-column-id="view_url" class="text-right" data-sortable="false"></th>
            </tr>
            </thead>
        </table>
    </form>
    <br class="clear">
</div>
<script type="text/javascript">
    (function($) {
        "use strict";
        var table = $('#op-transfer-grid').DataTable({
            "processing": true,
            "serverSide": true,
            ajax: {
                url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
                type: 'post',
                data: {action: 'op_stock_transfer_ajax_list'}
            },
            pageLength : 10
        } );
        $(document).on('click','.edit-row',function(){
            var id = $(this).data('id');
            var url = "<?php echo admin_url( 'admin.php?page=op-stock-transfer&action=edit' ); ?>"+'&id='+id;
            window.location = url;
        });
        $(document).on('click','.receive-row',function(){
            var id = $(this).data('id');
            var url = "<?php echo admin_url( 'admin.php?page=op-stock-transfer&action=receive' ); ?>"+'&id='+id;
            window.location = url;
        });
        $(document).on('click','.delete-row',function(){
            var id = $(this).data('id');
            var input_selected = $(document).find('#row-'+id);
            if(confirm('Are you sure ?'))
            {
                $.ajax({
                    url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
                    type: 'post',
                    data: {action: 'op_delete_transfer',id: id},
                    dataType: 'json',
                    beforeSend:function(){
                        input_selected.prop('disabled',true);
                    },
                    success: function(response){
                        table.ajax.reload();
                    }
                });
            }

        });

    })( jQuery );
</script>