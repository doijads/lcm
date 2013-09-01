//All the comman functions are located here

var dashboard = {
    populateData: function(formId,data) { 
        
           console.log( data )
           
            $.each(data, function(key, value){  
            var $ctrl = $('[name='+key+']', formId);  
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
}