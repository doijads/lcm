//All the comman functions are located here
   $(document).ready(function() {
   		
   	  //Applying calender to all text having datepicker class.
	  $( ".datePicker" ).datepicker({
	     showOn: "button",
	     buttonImage: "/images/calendar.gif",
	     buttonImageOnly: true,
	     dateFormat: 'yy-mm-dd'
	   }).attr( 'readOnly', true );   	 
	   
	   //Applying dataTable to all table having class dataTable
       $('.dataTable').dataTable();
       /*{ we will be calling this for server side loading
       "bProcessing": true,
       "bServerSide": true,
       "sAjaxSource": "../server_side/scripts/server_processing.php"
      }*/
       //back Action
       $("#back").click(function(){           
            window.history.back();           
       });
              
   });
   
  
//var dashboard = {
//    populateData: function(formId,data) {                               
//            $.each(data, function(key, value){  
//            var $ctrl = $('[name='+key+']', formId);  
//            switch($ctrl.attr("type"))  
//            {  
//                case "text" :   
//                case "hidden":  
//                case "textarea":  
//                $ctrl.val(value);   
//                break;   
//                case "radio" : case "checkbox":   
//                $ctrl.each(function(){
//                   if($(this).attr('value') == value) {  $(this).attr("checked",value); } });   
//                break;  
//            }  
//            });  
//     }
//}