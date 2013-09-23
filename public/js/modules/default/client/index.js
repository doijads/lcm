$(function(){                                  
    clientDetails.init();     
    $('.success_message').delay(4000).fadeOut();
});

//Here All the js functions will be releated to Lawyer Only
var clientDetails = {        
    init: function() {                         
        //Search and Display Lawuer        
        $("#searchbutton").click(function() { 
            var userName = $.trim($('#searchname').val()) ;
            var userEmail = $.trim($('#searchemail').val());
            var userType  = $('#role_type').val();             
                       
            var params = {'user_name': userName, 'user_email': userEmail,'user_type':userType};
            
            $.ajax({
                url: '/ajax/get-users/',
                type: 'POST',
                data: params,
                dataType: 'json',
                success: function(response) {
                    if (response.success === true) {                        
                        var $tr = $('#client-data-' + response.data.user_id);
                        var foundTr = false;
                        if ($tr.attr('id') === 'client-data-' + response.data.user_id) {
                            foundTr = true;
                        }
                        if (!foundTr)
                        {
                            $.each(response.data, function(i, elem) {
                                clientDetails.renderData(i, elem);
                            });
                        }
                    } else {                        
                        $("#no-record-tr").remove();
                        $(".search-row").remove();
                        $('#search-table  tbody').append('<tr id="no-record-tr"><td>No matching data found for given search criteria</td></tr>');
                    }

                }
            });
            return true;
                     
        });
    },
    
   deleteClient: function(clientId) {
        var param = {'id': clientId};                        
        $.ajax({
            url: '/ajax/delete-user/',
            type: 'POST',
            data: param,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $("#client_data_"+clientId).remove();
                    jAlert("Record Delete Successfully");
                }
            }
        });                
    },
    
   viewClient:function( clientId ){
        var param = {'id': clientId};                        
        $.ajax({
            url: '/ajax/display-client-modal/',
            type: 'POST',   
            data: param,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                     $("#dialog-form-client").dialog({                 
                        width:600,
                        height:300
                    });
                    
                    $("#dialog-form-client").html( response.data );
                }
            }
        });        
    },
        
   renderData: function(id, data) {
        $("#no-record-tr").remove();                      
        var html =
        '<tr  class="search-row" id="client_data_'+data.user_id+'" style="height:30px;">' +       
        '<td>'+data.name+'</td>' +
        '<td>'+data.email+'</td>' +
        '<td>'+data.mobile_number+'/'+ data.work_phone +'</td>' +
        '<td>'+data.street_line+','+ data.city +'</td>' +
        '<td style="float:right">\n\
            <a href="/client/edit-client/id/'+ data.user_id +'" id="edit_client_'+ data.user_id +'" class="edit-client">Edit</a>&nbsp;&nbsp;\n\
            <a href="javascript:void(0)" id="delete_client_'+ data.user_id +'" class="delete-client">Delete</a>&nbsp;&nbsp;\n\
            <a href="#" id="view_client_'+ data.user_id +'" class="view-client">View</a></td>'+        
        '</tr>';        
        $('#search-table tbody').append(html);
         //append all events.
        this.appendEvents();
        
   },
    
   appendEvents : function() {
        $(".delete-client").on('click',function(){
            var clientId = $(this).attr('id').replace('delete_client_', ''); 
            clientDetails.deleteClient(clientId);
        });
        
        $(".view-client").on('click',function(){
            var clientId = $(this).attr('id').replace('view_client_', '');                       
            clientDetails.viewClient(clientId);
        });        
   }                                  
};

