<?php
    $dowMap = $this->days;
   
?>
<div class="wrap op-conten-wrap"  style="background: #fff; padding:5px;">
    <h1 style="text-align:center;"><?php echo __( 'Open Times', 'woo-book-price' ); ?></h1>
    
    <br class="clear">
    <div class="pickup-time-container" style="width:800px;margin: 0 auto;">
        <form type="post" action="" id="pickup-frm">
            <input type="hidden" name="action" value="op_save_pickup">
            <div class="form-ctr" style="display:block;padding: 5px 0;margin-bottom: 5px;height:30px;">
                <input type="button" class="save-time-btn" value="Save" style="    float: right;
    background: #FF5722;
    color: #fff;
    padding: 5px 10px;
    border: solid 1px #FF5722;
    border-radius: 5px;
    font-weight: bold;
    text-transform: uppercase;">
            </div>
            <p>(*) Max Order = 0: Unlimited order in current timeslot.</p>
            <?php for($i = 0; $i< 7; $i++): $slots = $this->getSlotByDateNum($i); ?>
            <div class="day-row">
                <div class="day-row-control">
                    <div class="day-label"><?php echo $dowMap[$i]; ?></div>
                    <div class="day-option">
                        <a href="javascript:void(0)" data-row="<?php echo $i; ?>" class="add-slot-btn">Add Slot</a>
                    </div>
                </div>
                <div class="day-row-slots">
                    <?php foreach($slots as $s): ?>
                    <div class="time-slot">
                        <input type="text" value="<?php echo $s['slot_from']; ?>" name="slot[<?php echo $i; ?>][slot_from][]" placeholder="From" class="timepicker" />
                        <input type="text" value="<?php echo $s['slot_to']; ?>" name="slot[<?php echo $i; ?>][slot_to][]" placeholder="To" class="timepicker" />
                        <input type="number" value="<?php echo $s['slot_max']; ?>" size="10" name="slot[<?php echo $i; ?>][slot_max][]" placeholder="Max Order"  />
                        <a href="javascript:void(0)" class="remove-slot" style="color:red;text-decoration:none;">X</a>
                    </div>
                    <?php endforeach; ?>
                    <p class="no-slot-msg" style="display:none;text-align:center;">No slot avaialble. This day seem off day. </p>
                </div>
            </div>
            <?php endfor; ?>
        </form>
    </div>
</div>
<script type="text/javascript">
    (function($) {
        "use strict";
        
        $('.timepicker').timepicker({
            timeFormat: 'HH:mm',
            interval: 60,
            minTime: '1',
            maxTime: '23:00',
            startTime: '00:00',
            dynamic: false,
            dropdown: true,
            scrollbar: true
        });

        function checkSlot(){
            $('.pickup-time-container').find('.day-row').each(function(){
                var numb_slot = $(this).find('.time-slot').length;
                if(numb_slot > 0)
                {
                    $(this).find('.no-slot-msg').hide();
                }else{
                    $(this).find('.no-slot-msg').show();
                }
            });
        }
        checkSlot();
        $('.add-slot-btn').click(function(){
            var div_container = $(this).closest('.day-row').find('.day-row-slots').first();
            var row_id = $(this).data('row');
            var html = '';
            html += '<div class="time-slot">';
            html += '<input type="text" value="" name="slot['+row_id+'][slot_from][]" placeholder="From" class="timepicker" />';
            html += '<input type="text" value="" name="slot['+row_id+'][slot_to][]" placeholder="To" class="timepicker" />';
            html += '<input type="number" value="0" size="10" name="slot['+row_id+'][slot_max][]" placeholder="Max Order"  />';
            html += '<a href="javascript:void(0)" class="remove-slot" style="color:red;text-decoration:none;">X</a>';
            html += '</div>';
            div_container.append(html);
            $('.timepicker').timepicker({
                timeFormat: 'HH:mm',
                interval: 60,
                minTime: '1',
                maxTime: '23:00',
                startTime: '00:00',
                dynamic: false,
                dropdown: true,
                scrollbar: true
            });
            checkSlot();
        });
        $('.pickup-time-container').on('click','.remove-slot',function(){
            $(this).closest('.time-slot').remove();
            checkSlot();
        });

        $('body').on('click','.save-time-btn',function () {
            $.ajax({
                url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
                type: 'post',
                data: $('#pickup-frm').serialize(),
                dataType: 'json',
                beforeSend:function(){

                },
                success: function(response){
                    if(response['status'] == 1)
                    {
                        alert('Success. All data saved');
                    }else{
                        alert('Error. Have errors when try save data. Please check your data again.');
                    }
                }
            });
        });
    })( jQuery );
</script>
<style>
    .day-row{
        padding: 20px 10px;
        border: solid 1px #ccc;
        border-collapse: collapse;
    }
    .day-row:nth-child(even){
        background: #ccc;
    }
    .day-label{
        font-weight: bold;
        float: left;
        display: inline-block;
        text-transform: uppercase;
    }
    .day-option{
        
        float: right;
        display: inline-block;
    }
    .day-option a{
        text-decoration: none;
        border: solid 1px #fff;
        background-color: #fff;
        padding: 5px 10px;
        border-radius: 5px;
        text-transform: uppercase;
        font-weight: bold;
    }
    .day-row-control{
        min-height: 40px;
        padding: 5px;
        background: green;
        color: #fff;
        vertical-align: middle;
        line-height: 30px;
    }
    .day-row-slots{
        min-height: 20px;
        border: solid 1px#FF9800;
        display: block;
        width: 500px;
    
        margin-left: 150px;
        border-radius: 10px;
        padding: 5px;
        margin-top: 15px;
    }
    .time-slot input{
        display: line-block;
        width: 30%;
    }
    .time-slot{
        margin-bottom: 10px;
        position: relative;
        border-bottom: solid 1px red;
        padding-bottom: 6px;
    }
    .day-row-slots .time-slot:last-child{
        border-bottom: solid 1px transparent;
        padding-bottom: 0;
    }
    .remove-slot{
        position: absolute;
        top: calc(50% - 8px);
        right: 5px;
        width: 10px;
        height: 10px;
        text-align: center;
        line-height: 10px;
        font-size: 8px;
        border: solid 1px;
        padding: 2px;
        border-radius: 3px;
    }
</style>