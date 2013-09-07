<?php
class Application_Form_Login extends Zend_Form
{
    public function init()
    {
        // Set the method for the display form to POST
        $this->setMethod('post');
        $this->setAction('/auth/login');
 
        // Add an email element
        $this->addElement('text', 'email', array(
            'label'      => 'User Name:',
            'required'   => true,
            'filters'    => array('StringTrim')            
        ));
        
        // Add an email element
        $this->addElement('text', 'password', array(
            'label'      => 'Password:',
            'required'   => true,
            'filters'    => array('StringTrim')            
        ));
        
        $this->addElement('select','user_role',
            array(
                    'label'        => 'I am a:',
                    'value'        => '-select-',
                    'multiOptions' => array(
                        USER_ADMIN    => 'admin',
                        USER_LAWYER   => 'lawyer',
                        USER_CLIENT   => 'client',
                        USER_ACCOUNT  => 'account',
                    ),
                )
        );
                
        // Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Sign up',
        ));
 
    }
    
}


?>

