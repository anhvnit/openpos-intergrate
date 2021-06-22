<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.js" integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU=" crossorigin="anonymous"></script>
<p>Cashier Name : <?php echo $session['name'];?></p>
<p>Login Time : <?php echo $session['logged_time'];?></p>
<p>your app template at here / html / css / js</p>
<p>Dynamic data get from your site</p>
<a href="javascript:void(0)" id="sample-link">test event</a>
<script type="text/javascript">
(function($) {
    $('#sample-link').click(function(){
        var addItemFired = new CustomEvent("openpos.add.to.cart", {"detail": {
            product : {
                "name": "ACMEe5",
                "id": 0,
                "parent_id": 0,
                "sku": "",
                "barcode": "000000022755",
                "price": 16,
                "price_incl_tax": 20,
                "final_price": 16,
                "manage_stock": false, // true or false
                "qty": 1, // limit qty
                "status": "publish",
                "options": [
                    {
                        'label' : "Radio Label",
                        'option_id' : 1,
                        'type' : 'radio',
                        'require' : true,
                        'options' : [
                            {'value_id' : 1, 'label': 'radio value 1','cost' : 5},
                            {'value_id' : 2, 'label': 'radio value 2','cost' : 2},
                          
                        ]
           
                    }
                ],
                "tax": [
                    {
                    "code": "standard_1",
                    "rate": 21,
                    "shipping": "yes",
                    "compound": "no",
                    "rate_id": 1,
                    "label": "Tax"
                    }
                ],
                "tax_amount": 3,
                "price_included_tax": 1,
                "display_special_price": false,
                "allow_change_price": true,
            }
        } });
        parent.dispatchEvent(addItemFired);
    });

}(jQuery));

</script>