<?php
class Application_Form_CaseRegister extends Zend_Form {
	public function init() {

		$user = new Model_Users();
		$fetchRow = $user->fetchUsersByUserTypes( array( USER_LAWYER, USER_CLIENT ) );
		
		$arrUserRekeyedByUserType = array();
		
		if( true == is_array( $fetchRow ) ) {
			foreach( $fetchRow as $arrUser ) {
				$arrUserRekeyedByUserType[$arrUser['user_type']][$arrUser['id']] = $arrUser['name'];
			}
		}

		$this->addElement( 'select','lawyer_id',
            array(
                    'label'        => 'Lawyer',
                    'value'        => '',
            		'required'	   => true,
                    'multiOptions' => array( ''	=> '- Select a Lawyer -' ) + (array) $arrUserRekeyedByUserType[USER_LAWYER]
                    ) );
		
		$this->addElement( 'select','client_id',
			            array(
			                    'label'        => 'Client',
			                    'value'        => '',
			            		'required'	   => true,
			            		'multiOptions' => array( ''	=> '- Select a Client -' ) + (array) $arrUserRekeyedByUserType[USER_CLIENT]
			                    ) );
		
		$dateOfAllotment = new Zend_Dojo_Form_Element_DateTextBox('date_of_allotment');
		$dateOfAllotment->setLabel('Date of Allotment:');
		$dateOfAllotment->setAttrib( 'class','datepicker' );
		$dateOfAllotment->addValidator(new Zend_Validate_Date());
		$this->addElement($dateOfAllotment);
	
		$dueDate = new Zend_Dojo_Form_Element_DateTextBox('due_date');
		$dueDate->setLabel('Due Date:');
		$dueDate->setAttrib( 'class','datepicker' );
		$dueDate->addValidator(new Zend_Validate_Date());
		$this->addElement($dueDate);
		
		// Add the submit button
		$this->addElement('submit', 'submit', array(
													'ignore'   => true,
													'label'    => 'Register',
											));
	}
}
?>