<head>
<script type="text/javascript" charset="utf-8" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</head>
<body>
<div class="container-fluid">
  <div class="row" style="padding: 10px 0;border-bottom: solid 1px #ccc;background: #ccc;">
   
    <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3 text-center">
        <form class="form-inline" id="product-frm">
            <div class="form-group">
                <div class="input-group">
                    <input type="text" class="form-control" id="input_keyword" placeholder="Enter Product name , barcode , sku">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>
    
  </div>
  <div class="search-result" style="padding: 10px 0;">
        <?php for($i = 0; $i < 10;$i++):?>
        
        <?php endfor; ?>
  </div>
</div>
<script type="text/javascript">
    $('#product-frm').on('submit',function(){
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php');?>',
            type: 'post',
            data: {action:'op_custom_product_view_search',keyword: $('#input_keyword').val() },
            dataType: 'html',
            success: function(response){
                $('.search-result').html(response);
            }
        });
        return false;
    })
</script>
<style>
    .result-item{
        padding: 10px 5px ;
        border-bottom:  solid 1px #333;
    }
</style>
</body>