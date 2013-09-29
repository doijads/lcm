//All the comman functions are located here
   $(document).ready(function() {
       $( ".datepicker" ).datepicker({
         showOn: "button",
         buttonImage: "/images/calendar.gif",
         buttonImageOnly: true
       });

       $('#example').dataTable();
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