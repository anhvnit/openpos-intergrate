<html lang="en">

<head>
    <title><?php echo $current_transfer['title']; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<main role="main" class="container">
    <div class="row">
        <div class="col-8">
            <h1 class="text-center">Packing Slip</h1>

            <h5 class="text-left" style="padding-top:10px;">Transfer Note</h5>
            <p class="text-left"><?php echo $current_transfer['title']; ?></p>
            <p class="text-left"><?php echo $current_transfer['note']; ?></p>
        </div>
        <div class="col-4 border border-dark">

            <p><h4>Transfer Information</h4></p>
            <p class="lead">Transfer Number: <?php echo '#'.$current_transfer['transfer_id'];?></p>


            <p>From Outlet: <b><?php echo $from_outlet['name'];?></b></p>
            <p>To Outlet: <b><?php echo $to_outlet['name'];?></b></p>
            <p>Received Date: ......../......../..................</p>
        </div>
    </div>
    <hr/>
    <div class="row">
        <div class="col-6" style="padding: 10px;height: 200px;">
            <h5 class="text-center">Sender Sign</h5>
            <div class="border border-dark" style=" height: calc(100% - 30px);width: 100%;"></div>
        </div>
        <div class="col-6" style="padding: 10px;height: 200px;">
            <h5 class="text-center">Receiver Sign</h5>
            <div class="border border-dark" style=" height: calc(100% - 30px);width: 100%;"></div>
        </div>
    </div>
    <hr/>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th scope="col">Item Code</th>
            <th scope="col">Item Name</th>
            <th scope="col">Quantity</th>
        </tr>
        </thead>
        <tbody>
        <?php $counter = 0; foreach($items as $item):  $product = wc_get_product($item['product_id']);$counter += ( 1 * $item['product_qty']); ?>
        <tr>
            <td scope="row"><?php echo $product->get_sku(); ?></th>
            <td><?php echo $product->get_name(); ?></td>
            <td><?php echo 1 * $item['product_qty']; ?></td>
        </tr>
        <?php endforeach; ?>
        <tr>
            <th scope="row" class="text-right" colspan="2">Total</th>
            <td><?php echo $counter; ?></td>
        </tr>
        </tbody>
    </table>
    <div class="row">
        <div class="col-12">
            <div class="card bg-faded">
                <div class="card-header">
                    Transfer Policy
                </div>
                <div class="card-body">
                    <p>To edit content, please goto wp-content/plugins/woocommerce-openpos-stocktransfer/templates/print.php .Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed vel mi sed est imperdiet tempus. Praesent ac ipsum lectus. In vel posuere nulla, eget mattis mi. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Vivamus nec ex sed risus feugiat sagittis. Vivamus egestas id neque auctor vulputate. Donec tincidunt, leo ut malesuada mattis, felis dolor lacinia enim, sit amet fermentum est turpis id augue. Vivamus rutrum aliquam ornare. Proin leo dolor, porta ut libero nec, ultricies tincidunt elit. Vivamus pharetra lacus augue, ut sagittis velit pellentesque vitae.</p>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/js/bootstrap.min.js"></script>


<script type="application/javascript">
    window.print();
</script>

</body>

</html>

