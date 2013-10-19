$(function(){    
    lawyerDetails.init();     
    $('.success_message').delay(3500).fadeOut();
    $(".view-lawyer").on('click',function(){
            var lawyerId = $(this).attr('id').replace('view_lawyer_', '');                       
            lawyerDetails.viewLawyer(lawyerId);            
   });
   
    $(".delete-lawyer").on('click',function(){
            var lawyerId = $(this).attr('id').replace('delete_lawyer_', ''); 
            lawyerDetails.deleteLawyer(lawyerId);
    });
   
  $( "#accordion" ).accordion();
   
});

//Here All the js functions will be releated to Lawyer Only
var lawyerDetails = {
    init: function() {         
  
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
                                
        //Search and Display Lawyer        
        $("#searchbutton").click(function() {             
            var userName  = $.trim($('#searchname').val()) ;
            var userEmail = $.trim($('#searchemail').val());
            var userType  = $('#role_type').val(); 
            
            var params = {'user_name': userName, 'user_email': userEmail,'user_type':userType};
            $.ajax({
                url: '/ajax/get-users/',
                type: 'POST',
                data: params,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var $tr = $('#lawyer-data-' + response.data.user_id);
                        var foundTr = false;
                        if ($tr.attr('id') === 'lawyer-data-' + response.data.user_id) {
                            foundTr = true;
                        }
                        if (!foundTr)
                        {
                            $.each(response.data, function(i, elem) {
                                lawyerDetails.renderData(i, elem);
                            });
                        }
                    } else {
                        $("#no-record-tr").remove();
                        $(".search-row").remove();
                        $('#search-table tbody').append('<tr id="no-record-tr"><td>No matching data found for given search criteria</td></tr>');
                    }

                }
            });
            return true;
                     
        });
    },    
   
    //Delete Lawyer 
    deleteLawyer: function(lawyerId) {
        var param = {'id': lawyerId};                        
        $.ajax({
            url: '/ajax/delete-user/',
            type: 'POST',
            data: param,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $("#lawyer_data_"+lawyerId).remove();
                    jAlert("Record Delete Successfully");
                }
            }
        });
    },
    
    viewLawyer: function(lawyerId) {
        var param = {'id': lawyerId};
        $.ajax({
            url: '/ajax/display-lawyer-modal/',
            type: 'POST',
            data: param,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $("#dialog-form-lawyer").dialog({
                        width: 500,
                        height: 400
                    });

                    $("#dialog-form-lawyer").html(response.data);
                }
            }
        });
    },

   renderData: function(id, data) {              
        $("#no-record-tr").remove();                      
        var html =
        '<tr class="search-row" id="lawyer_data_'+data.user_id+'" style="height:30px;">' +       
        '<td>'+data.name+'</td>' +
        '<td>'+data.email+'</td>' +
        '<td>'+data.mobile_number+'/'+ data.work_phone +'</td>' +
        '<td>'+data.street_line+','+ data.city +'</td>' +
        '<td style="float:right">\n\
               <a href="/lawyer/edit-lawyer/id/'+ data.user_id +'" id="edit_lawyer_'+ data.user_id +'" class="edit-lawyer">Edit</a>&nbsp;&nbsp;\n\
               <a href="javascript:void(0)" id="delete_lawyer_'+ data.user_id +'" class="delete-lawyer">Delete</a>\n\
               <a href="#" id="view_lawyer_'+ data.user_id +'" class="view-lawyer">View</a>\n\
        </td>'+                       
        '</tr>';        
        $('#search-table tbody').append(html);        
        //append all events.
        this.appendEvents();
        
   },    
   appendEvents : function() {
        $(".delete-lawyer").on('click',function(){
            var lawyerId = $(this).attr('id').replace('delete_lawyer_', ''); 
            lawyerDetails.deleteLawyer(lawyerId);
        });
   }
      
};

