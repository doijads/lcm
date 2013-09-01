<<<<<<< HEAD
$(function(){                                  
    lawyerDetails.init();     
    clientDetails.init(); 
=======
$(function(){
    lawyerDetails.init();     
    clientDetails.init();    
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
});

//Here All the js functions will be releated to Lawyer Only
var lawyerDetails = {
<<<<<<< HEAD
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
=======
    init: function() {
			
		$(".edit-row").live('click',function(){
				$row_id = $(this).attr('id');					
				$rid = $row_id.replace('edit-row-','');						
				var data = [{"id":"1","name":"sachin","user_type":"1","email":"sachindoijad@gmail.com","street_line":"pune","city":"pune","state":"maharashtra","postal_code":"598989","country":"india","home_phone":"5656","work_phone":"56565","fax_number":"898989","mobile_number":"56565","created_on":"2013-08-28 09:13:56","created_by":"4","user_id":"1","company_name":null,"company_profile":null,"designation":null,"role":null,"role_description":null,"area_of_practice":null,"bank_account_number":"45454","IFSC_code":"5545","service_tax_number":"5454556","pan_card_number":"45454"}] ;
				lawyerDetails.populateData("#register_form",data[0]);					
			
		});
		
		
	
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
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
<<<<<<< HEAD
                    }else{
                        console.log("No records found");
=======
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
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
<<<<<<< HEAD
        '<td style="float:right"><a href="editlawyer" id="edit_lawyer" data-id="edit-row-'+ data.id +'">Edit</a>&nbsp;&nbsp;<a href="javascript:void(0)" id="delete_lawyer" data-id="delete-row-'+ data.id +'">Delete</a></td>'+        
        '</tr>';        
        $('tbody').append(html);
   }                      
};
=======
		'<td><a href="#" class="edit-row" id="edit-row-'+data.id+'">Edit</a> &nbsp;&nbsp;<a href="#" class="delete-row" id="delete-row-'+data.id+'">Delete</a></td>' +
        '</tr>';        
        $('tbody').append(html);
   },
	
	populateData: function(formid, data ){
				
		 $.each(data, function(key, value){  
			var $ctrl = $('[name='+key+']', "#register_form");  
													
			switch($ctrl.attr("type"))  
			{  
				case "text" :   
				case "hidden":  
				case "textarea":  
				$ctrl.val(value);   
				break;   
				case "radio" : case "checkbox":   
				$ctrl.each(function(){
				   if($(this).attr('value') == value) {  $(this).attr("checked",value); } });   
				break;  
			}  
		});  
	
		
	}
   
};


>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
//Here All the js functions will be releated to Lawyer Only
var clientDetails = {
    init:function(){
        
        
        
    }

}

