


(function($) {
    $.fn.serializeFormJSON = function () {

      var o = {};
      var a = this.serializeArray();
      $.each(a, function () {
          if (o[this.name]) {
              if (!o[this.name].push) {
                  o[this.name] = [o[this.name]];
              }
              o[this.name].push(this.value || '');
          } else {
              o[this.name] = this.value || '';
          }
      });
      return o;
  };
    
    var process_order = null;
    document.addEventListener("openpos.start.payment", function (e) {
        
        var detail = e['detail'];
        process_order = detail['data'];
        var amount = detail['amount'];
        var method = detail['method'];
       
        if(method.code == 'op_authorize_net_magnetic')
        {
         
            var html_template = '<div id="paypal-loading" >';
           
            html_template += '<div id="paypal-loading-text">';


            html_template += '<p id="paypal-message"></p>';
            html_template += '<form id="paypal-plain-card-info-form">';
            html_template += '<div id="paypal-processing">Processing....</div>';
           
            html_template += '<input type="hidden" name="amount" value="'+amount+'">';
           
            html_template += '<input type="hidden" name="cc_type" value="" id="cc_type">';
            html_template += '<input type="hidden" name="honorific" value="" id="honorific">';
            html_template += '<div id="paypal-plain-card-info" style="display:none;">';

            html_template += '<div id="cc-name"><label>Hold Name</label><input type="text" id="cc_name" name="cc_name" /></div>';
            html_template += '<div id="cc-number"><label>CC Number</label><input type="password" id="cc_num" name="cc_num" autocomplete="off" /></div>';
            html_template += '<div id="cc-exp"><label>Expired Date</label><div class="exp-detail"><input  id="cc_exp_month" type="text" name="cc_exp_month" size="2"/> - <input  id="cc_exp_year" type="text" name="cc_exp_year" size="4" /></div></div>';
            html_template += '<div id="cc-cvv"><label>CVV</label><input size="4" id="cc_cvv" type="password" name="cc_cvv" autocomplete="off" /></div>';
            html_template += '</div>';
            html_template += '<div id="paypal-button-container"><div class="swipe-input-container"><span class="swipe-guide">Please Scan Now</span></div></div>';

            html_template += '<div id="cc-action-change"><label><input type="checkbox" name="is_manual" value="1" />Use manual enter card information</label></div>';
            html_template += '<div id="cc-action"><input type="submit" id="pay-now-btn" value="Pay Now" disabled> <a id="paypal-close-loading-btn" href="javascript:void(0)">Cancel</a></div>';
            html_template += '</form>';

            html_template += '</div>';
            html_template += '</div>';
            $('body').append(html_template);
  
          $.cardswipe({
            firstLineOnly: true,
            success: complete,
            parsers: ["visa", "amex", "mastercard", "discover", "generic"],
            debug: false
          });
        }
        


       
        //$('body').find('input[name="swipe"]').first().focus();

    });

    var complete = function (data) {

        // Is it a payment card?
        if (data.type == "generic")
          return;

        // Copy data fields to form
        $('body').find("#honorific").val(data.honorific);
        $('body').find("#cc_name").val(data.firstName + ' ' + data.lastName);
        $('body').find("#cc_num").val(data.account);
        $('body').find("#cc_exp_month").val(data.expMonth);
        $('body').find("#cc_exp_year").val(data.expYear);
        $('body').find("#cc_type").val(data.type);
        validPay();
        $('body').find('input[name="is_manual"]').first().trigger('click');
		};

    $(document).on('click','input[name="is_manual"]',function(){
      if($(this).prop('checked'))
      {
        
        $('body').find('#paypal-plain-card-info').show();
        $('body').find('#paypal-button-container').hide();
      }else{
        $('body').find('#paypal-plain-card-info').hide();
        $('body').find('#paypal-button-container').show();
        $('body').find('input[name="swipe"]').first().focus();
        
      }
    });

    function validPay(){
      if($('body').find("#cc_num").val().length > 5){
        $('body').find("#pay-now-btn").prop("disabled", false);;
      }
    }
    $('body').on('keyup',"#cc_num",function(){
      validPay();
    });
    
    $(document).on('click','#paypal-close-loading-btn',function(){
        $('body').find('#paypal-loading').remove();
    });
    
    $(document).on('submit','#paypal-plain-card-info-form',function(){
        
        var data = $(this).serializeFormJSON();
      
        $.ajax({
          url: action_url,
          type: 'post',
          dataType: 'json',
          data:{order: JSON.stringify(process_order), data : JSON.stringify(data),action:'op_authorize_net_magnetic'  },
          beforeSend: function(){
            $('body').find("#paypal-message").empty();
            $('body').find('#paypal-loading').addClass('processing');
          },
          success: function(reponse){
            $('body').find('#paypal-loading').removeClass('processing');
            if(reponse.status == 1)
            {
              var transaction_id = reponse.data['ref'];
              var amount = reponse.data['amount'];
              var order_id = reponse.data['order_id'];
              let paymentFired = new CustomEvent("openpos.paid.payment", {
                "detail": {"method": 'op_authorize_net_magnetic',"ref": transaction_id, "order_id": order_id,'message':'Transaction: '+transaction_id,"amount": amount }
              });
              document.dispatchEvent(paymentFired);

              $('body').find('#paypal-loading').remove();

              
              
            }else{
              $('body').find("#paypal-message").text(reponse.message);
            }
            
          }
        });
        return false;
    })

}(jQuery));