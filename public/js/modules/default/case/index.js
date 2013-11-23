var case_id = null;
$(document).ready(function() {

    budgetDetails.init();
    
    $('td.action').on( 'click', function(event){
     	id = this.id;
      div_title = event.target.id;
            
      if( this.id == '' ){
        var actionElement = div_title.split( '_' );
        div_title = actionElement[0];
        id = actionElement[1];
      }
     	
     	switch(div_title) {
      		case 'view':
              var param = {'id': id };
              $.ajax({
                  url: '/ajax/display-case-modal/',
                  type: 'POST',   
                  data: param,
                  dataType: 'json',
                  success: function(response) {
                      if (response.success) {                        
                          $('#dialog-div').dialog({
                             title: 'Case Details',
                             modal: true,
                             width: 700,
                             height: 500
                          });
                          
                        $('#dialog-div p').html( response.data );
                      }
                  },
                  error: function( error ) {                    
                      console.log(error);
                  }
              }); 
        		  break;

        case 'lawyer':
              var param = {'id': id };
              $.ajax({
                  url: '/ajax/display-lawyer-modal/',
                  type: 'POST',
                  data: param,
                  dataType: 'json',
                  success: function(response) {
                      if(response.success) {
                          $("#dialog-div").dialog({
                              title: 'View Lawyer',
                              modal: true,
                              width: 500,
                              height: 450
                          });

                          $("#dialog-div p").html(response.data);
                      }
                  }
              });
      		  break;

          case 'client':
              var param = {'id': id };                        
              $.ajax({
                  url: '/ajax/display-client-modal/',
                  type: 'POST',   
                  data: param,
                  dataType: 'json',
                  success: function(response) {
                      if (response.success) {
                           $("#dialog-div").dialog({
                              title: 'View Client',
                              modal: true,
                              width: 500,
                              height:450
                          });

                          $("#dialog-div p").html(response.data);
                      }
                  }
              });     
              break;
              
             case 'vct':
              var param = {'id': id };                        
              $.ajax({
                  url: '/ajax/display-case-transaction-modal/',
                  type: 'POST',   
                  data: param,
                  dataType: 'json',
                  success: function(response) {
                      if (response.success) {
                           $("#dialog-div").dialog({
                              title: 'View Case Transaction',
                              modal: true,
                              width: 700,
                              height:600
                          });

                          $("#dialog-div p").html(response.data);
                      }
                  }
              });     
             break;
                                          
             default:				
      }
     	
    });
});

var budgetDetails = {
    
    init: function(caseId) {                       
    var caseId = $.url().segment(4);
    var feesType = $("#fees_type_id-element, #fees_type_id-label");
    var hourSpent = $("#hours_spent-element, #hours_spent-label");
    var hourAmount = $("#hour_amount-element, #hour_amount-label");   
    hourSpent.hide();
    hourAmount.hide();    
    //changing the fees types
    $("#fees_type_id").change(function(){
        hourSpent.hide();
        hourAmount.hide();  
        //if fees type = Hourly               
        if( $(this).val() == 2 ){
            hourSpent.show();
            hourAmount.show();  
        }        
    });
        
    //changing the transaction types
     $("#transaction_type_id").change(function() {                     
            feesType.hide();
            hourSpent.hide();
            hourAmount.hide();
            $("#sum,#hours_spent,#hour_amount").val('');
            if ($(this).val() == 2) {                             
                feesType.show();
                if( $("#fees_type_id").val() == 2 ){
                    hourSpent.show();
                    hourAmount.show();
                }
                
            }
        });
                
     $(".hsp").each(function() {
            $(this).keyup(function(){
               // $("#sum").attr('disabled','disabled');
                budgetDetails.calculateSum();
            });
      });
    },
    
    calculateSum:function(){
        var sum = 1;
        //iterate through each textboxes and add the values
        $(".hsp").each(function() {
            //add only if the value is number
            if(!isNaN(this.value) && this.value.length!=0) {
                sum *= parseFloat(this.value);
            }
        });
        //.toFixed() method will roundoff the final sum to 2 decimal places
        $("#sum").val(sum.toFixed(2));
    }        
    
}