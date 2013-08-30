$(function(){
    lawyerDetails.init();     
    clientDetails.init();    
});

//Here All the js functions will be releated to Lawyer Only
var lawyerDetails = {
    init: function() {
			
		$(".edit-row").live('click',function(){
				$row_id = $(this).attr('id');					
				$rid = $row_id.replace('edit-row-','');						
				var data = [{"id":"1","name":"sachin","user_type":"1","email":"sachindoijad@gmail.com","street_line":"pune","city":"pune","state":"maharashtra","postal_code":"598989","country":"india","home_phone":"5656","work_phone":"56565","fax_number":"898989","mobile_number":"56565","created_on":"2013-08-28 09:13:56","created_by":"4","user_id":"1","company_name":null,"company_profile":null,"designation":null,"role":null,"role_description":null,"area_of_practice":null,"bank_account_number":"45454","IFSC_code":"5545","service_tax_number":"5454556","pan_card_number":"45454"}] ;
				lawyerDetails.populateData("#register_form",data[0]);					
			
		});
		
		
	
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


//Here All the js functions will be releated to Lawyer Only
var clientDetails = {
    init:function(){
        
        
        
    }

}

