var case_id = null;
$(document).ready(function() {

    $('td.action').on( 'click', function(){
     	id = this.id;
      div_title = event.target.id;
      if( this.id == '' ){
        var actionElement = div_title.split( '_' );
        div_title = actionElement[0];
        id = actionElement[1];
      }
     	
     	switch(div_title) {
      		case 'View':
              var param = {'id': id };
              $.ajax({
                  url: '/ajax/display-case-modal/',
                  type: 'POST',   
                  data: param,
                  dataType: 'json',
                  success: function(response) {
                      if (response.success) {
                          $('#dialog-div p').html( response.data );
                          $('#dialog-div').dialog({
                             title: 'Case',
                             modal: true,
                             width: 200,
                             height: 200
                          });
                      }
                  },
                  error: function( error ) {
                    alert(id);
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
                              height: 400
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
                              height: 400
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