$(function(){                                  
    lawyerDetails.init();     
    clientDetails.init(); 
});

//Here All the js functions will be releated to Lawyer Only
var lawyerDetails = {
    init: function() { 
        //Delete Lawyer
        $("#delete_lawyer").live('click',function(e){  
            //e.preventDefault();
            var row_id = $(this).attr('data-id'); 
            var uid = row_id.replace('delete-row-','');
            var param = {'id': uid} ;
                        
            $.ajax({
                url: '/ajax/deletelawyers/',
                type: 'POST',
                data: param,
                success: function(response) {
                    if( response.success ){
                         //remove record
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
                url: '/ajax/getlawyers/',
                type: 'POST',
                data: params,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {  
                        var $tr = $('#lawyer-data-' + response.data[0].id );                       
                        var foundTr = false;							
                        if ( $tr.attr('id') === 'lawyer-data-' + response.data[0].id ) {
                            foundTr = true;
                        }                         
                        if( !foundTr )
                        {
                            $.each(response.data, function(i, elem) {                          
                            lawyerDetails.renderData(i,elem);                           
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
        '<tr  id="lawyer-data-'+data.id+'" style="height:30px;">' +       
        '<td>'+data.name+'</td>' +
        '<td>'+data.email+'</td>' +
        '<td>'+data.mobile_number+'/'+ data.work_phone +'</td>' +
        '<td>'+data.street_line+','+ data.city +'</td>' +
        '<td style="float:right"><a href="/lawyer/editlawyer/id/'+ data.id +'" id="edit_lawyer" data-id="edit-row-'+ data.id +'">Edit</a>&nbsp;&nbsp;<a href="javascript:void(0)" id="delete_lawyer" data-id="delete-row-'+ data.id +'">Delete</a></td>'+        
        '</tr>';        
        $('tbody').append(html);
   }                      
};
//Here All the js functions will be releated to Lawyer Only
var clientDetails = {
    init:function(){
        
        
        
    }

}

