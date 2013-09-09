<?php
class Application_Form_Register extends Zend_Form
{
    public function init() {
		switch( $this->getAttrib( 'strFormType' ) ) {
			case 'lawyer':
			case 'client':
				$this->formLawyer();
			break;
			
			case'case':
				$this->formCase();
				break;
		}
	}
	
	private function formLawyer() {
		
		// Set the method for the display form to POST
        $userRoleType = $this->getAttrib('userRoleType');             
                  
        $this->setMethod('post');
        $this->setAttrib('class', "register");
        $this->setAttrib('id', "register-form");
        
           // Add an email element
        $this->addElement('text', 'name', array(
            'label'      => 'Name:',
            'required'   => true,
            'filters'    => array('StringTrim')           
        ));
        
        if( $userRoleType == 'client' ){
            $this->addElement('text', 'contact_person', array(
                'label'      => 'Contact Person:',
                'required'   => false,
                'filters'    => array('StringTrim')           
            ));
        }
        $this->addElement('text', 'home_phone', array(
            'label'      => 'Home Phone Number:',
            'required'   => false,
            'filters'    => array('StringTrim')           
        ));
                        
         $this->addElement('text', 'work_phone', array(
            'label'      => 'Work Phone Number:',
            'required'   => false,
            'filters'    => array('StringTrim')           
        ));
         
        
        $mobileNumber = new Zend_Form_Element_Text('mobile_number');
        $mobileNumber->setLabel('Mobile Number:');
        $mobileNumber->setRequired(true);
        $mobileNumber->addValidator(new Zend_Validate_Int());        
        $this->addElement($mobileNumber);
        
        // Add an email element
        
        $emailExists = array('users', 'email');
        $email = new Zend_Form_Element_Text('email');
        $email->setLabel('E-mail:');
        $email->addValidator(new Zend_Validate_EmailAddress());
        $email->addValidator('Db_NoRecordExists', true, $emailExists);
        $email->setRequired(true);       
        $this->addElement($email);
               
        $this->addElement('text', 'fax_number', array(
            'label'      => 'Fax Number:',
            'required'   => false,
            'filters'    => array('StringTrim')           
        ));
                  
        $this->addElement('text', 'street_line', array(
            'label'      => 'Address',
            'required'   => false,
            'filters'    => array('StringTrim')           
          ));
                 
        $zipCode = new Zend_Form_Element_Text('postal_code');
        $zipCode->setLabel('Zip Code:');
        $zipCode->addValidator(new Zend_Validate_Int());        
        $this->addElement($zipCode);
              
        $this->addElement('select','city',
            array(
                    'label'        => 'City',
                    'value'        => '-select-',
                    'multiOptions' => array(
                        'pune'    => 'Pune',
                        'mumbai'   => 'Mumbai',
                        'amravati'  => 'Amravati',
                    ),
                )
            );
        
         $this->addElement('select','state',
            array(
                    'label'        => 'State',
                    'value'        => '-select-',
                    'multiOptions' => array(
                        'maharashtra'    => 'Maharashtra'                       
                    ),
                )
            );
         
        $this->addElement('select', 'country', array(
                    'label'        => 'Country',
                    'value'        => '-select-',
                    'multiOptions' => array(
                        'india'    => 'India'                       
                    ),
                )
        );
                                     
        $this->addElement('text', 'pan_card_number', array(
            'label'      => 'Pan Number',
            'required'   => false,
            'filters'    => array('StringTrim')           
          ));
        
        $this->addElement('text', 'IFSC_code', array(
            'label'      => 'IFSC Code',
            'required'   => false,
            'filters'    => array('StringTrim')           
          ));
        
        $this->addElement('text', 'bank_account_number', array(
            'label'      => 'Bank Account Number',
            'required'   => false,
            'filters'    => array('StringTrim')           
          ));
        
         $this->addElement('text', 'service_tax_number', array(
            'label'      => 'Service Tax Number',
            'required'   => false,
            'filters'    => array('StringTrim')           
          ));
        
        // Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Register',
        ));
	}
	
	private function formCase() {
		$this->addElement( 'select','vendor_id',
            array(
                    'label'        => 'Vendor',
                    'value'        => '-select-',
                    'multiOptions' => array(
						''	=> '- Select a Lawyer -',
                        '1' => 'Amit',
                        '2' => 'Sachin',
                        '3' => 'Rohit'
                    ) ) );
		
		$this->addElement( 'select','client_id',
			            array(
			                    'label'        => 'Client',
			                    'value'        => '-select-',
			                    'multiOptions' => array(
									''	=> '- Select a Client -',
			                        '1' => 'Hindustan Petroleum',
			                        '2' => 'Reliance Telecom',
			                        '3' => 'Walmart'
			                    ) ) );
			
        $this->addElement(	'text', 'date_of_allotment', array(
	            			'label'      => 'Allotment Date',
	            			'required'   => false,
	            			'filters'    => array('StringTrim')           
	        ));
	
		$this->addElement(	'text', 'due_date', array(
	            			'label'      => 'Due Date',
							'id'		 => 'datePicker',
	            			'required'   => false,
	            			'filters'    => array('StringTrim')           
	        ));
		$element = new ZendX_JQuery_Form_Element_DatePicker( 'dp1',
		                    array('jQueryParams' => array('defaultDate' => '2007/10/10'))
		                );
		// would internally call to:
		$view->datePicker("dp1", "", array('defaultDate' => '2007/10/10'), array());
		/*$userData = array(
      		'lawyer_id'			=> $formData['lawyer_id'],
        	'client_id' 		=> $formData['client_id'],
        	'date_of_allotment' => $formData['date_of_allotment'],
        	'due_date' 			=> $formData['due_date'],
        	'closed_by' 		=> $formData['closed_by'],
        	'closing_date' 		=> $formData['closing_date']
        );*/
	}
}
?>