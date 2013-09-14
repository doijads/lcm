$(function(){                                  
    clientDetails.init();     
    $('.success_message').delay(3500).fadeOut();
});

//Here All the js functions will be releated to Lawyer Only
var clientDetails = {
    init: function() { 
        //Delete Client
        $("#delete_client").live('click',function(e){  
            //e.preventDefault();
            var row_id = $(this).attr('data-id'); 
            var uid = row_id.replace('delete-row-','');
            var param = {'id': uid} ;
                        
            $.ajax({
                url: '/ajax/delete-user/',
                type: 'POST',
                data: param,
                dataType: 'json',
                success: function(response) {                                                              
                    if( response.success ){                       
                         $(this).parents('tr').remove();
                         jAlert("Record Delete Successfully");
                    }
                }
            });
            
        });
        
//        $("#edit_lawyer").live('click',function(e){  
//            //e.preventDefault();
//             var row_id = $(this).attr('data-id');
//             var uid = row_id.replace('edit-row-','');
//             var param = {'id': uid} ;
//             $.ajax({
//                url: '/ajax/editlawyers/',
//                type: 'POST',
//                data: param,
//                dataType:'json',
//                success: function(response) {
//                    if (response.success) {
//                        $("input[id='submit']").remove();                         
//                        dashboard.populateData('#register-form',response.data[0]) ;                        
//                    }
//                }
//            });                                     
//        });
                        
        //Search and Display Lawuer        
        $("#searchbutton").click(function() { 
            var userName = $.trim($('#searchname').val()) ;
            var userEmail = $.trim($('#searchemail').val());
            
            var params = {'user_name': userName, 'user_email': userEmail};
            $.ajax({
                url: '/ajax/get-users/',
                type: 'POST',
                data: params,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {  
                                                                        
                        var $tr = $('#client-data-' + response.data.user_id );                       
                        var foundTr = false;							
                        if ( $tr.attr('id') === 'client-data-' + response.data.user_id ) {
                            foundTr = true;
                        }                         
                        if( !foundTr )
                        {
                            $.each(response.data, function(i, elem) {                          
                                clientDetails.renderData(i,elem);                           
                            });
                        }
                    }else{
                        console.log("No records found");
                    }
                }
            });
            return true;
                     
        });
    },    
   renderData: function(id, data) {
        $("#no-record-tr").remove();
                      
        var html =
        '<tr  id="client-data-'+data.user_id+'" style="height:30px;">' +       
        '<td>'+data.name+'</td>' +
        '<td>'+data.email+'</td>' +
        '<td>'+data.mobile_number+'/'+ data.work_phone +'</td>' +
        '<td>'+data.street_line+','+ data.city +'</td>' +
        '<td style="float:right"><a href="/client/edit-client/id/'+ data.user_id +'" id="edit_client" data-id="edit-row-'+ data.user_id +'">Edit</a>&nbsp;&nbsp;<a href="javascript:void(0)" id="delete_client" data-id="delete-row-'+ data.user_id +'">Delete</a></td>'+        
        '</tr>';        
        $('tbody').append(html);
   }                      
};

