<?php
class Application_Form_Search extends Zend_Form
{
    public function init()
    {
        // Set the method for the display form to POST
        $this->setMethod('post');
 
        // Add an email element
        $this->addElement('text', 'search-email', array(
            'label'      => 'Users Email:',
            'required'   => true,
            'filters'    => array('StringTrim')            
        ));
        
        // Add an email element
        $this->addElement('text', 'search-name', array(
            'label'      => 'Users Name:',
            'required'   => true,
            'filters'    => array('StringTrim')            
        ));
        
        // Add the submit button
        $this->addElement('button', 'search-button', array(
            'ignore'   => true,
            'label'    => 'Search User',
        ));
 
    }
    
}


?>

