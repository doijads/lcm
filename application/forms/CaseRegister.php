<?php

class Application_Form_CaseRegister extends Zend_Form {

	public function init() {
		switch( $this->getAttrib( 'formType' ) ) {
			case Model_Cases::CASE_REGISTER:
				$this->loadRegiser();
				break;

			case Model_Cases::CASE_HISTORY:
				$this->loadHistory();
				break;

			case Model_Cases::CASE_DOCUMENT:
				$this->loadDocument();
				break;

			case Model_Cases::CASE_BUDGET:
				$this->loadBudget();
				break;
			
			default:
				echo 'Something rotten';
				break;
		}
	}

	public function loadRegiser() {

		$user = new Model_Users();
		$fetchRow = $user->fetchUsersByUserTypes( array( USER_LAWYER, USER_CLIENT ) );
		
		$arrUserRekeyedByUserType = array();
		
		if( true == is_array( $fetchRow ) ) {
			foreach( $fetchRow as $arrUser ) {
				$arrUserRekeyedByUserType[$arrUser['user_type']][$arrUser['id']] = $arrUser['name'];
			}
		}

		$arrLawyerList = ( true == array_key_exists( USER_LAWYER, $arrUserRekeyedByUserType ) ) ? array( ''	=> '- Select a Lawyer -' ) + $arrUserRekeyedByUserType[USER_LAWYER] : array( ''	=> '- No Lawyer -' );
		
		$this->addElement( 'select','lawyer_id',
			            array(
			                    'label'        => 'Lawyer',
			                    'value'        => '',
			            		'required'	   => true,
			                    'multiOptions' => $arrLawyerList
			                    ) );

		$arrClientList = ( true == array_key_exists( USER_CLIENT, $arrUserRekeyedByUserType ) ) ? array( ''	=> '- Select a Client -' ) + $arrUserRekeyedByUserType[USER_CLIENT] : array( ''	=> '- No Client -' );
		$this->addElement( 'select','client_id',
			            array(
			                    'label'        => 'Client',
			                    'value'        => '',
			            		'required'	   => true,
			            		'multiOptions' => $arrClientList
			                    ) );
		
		$dateOfAllotment = new Zend_Dojo_Form_Element_DateTextBox('date_of_allotment');
		$dateOfAllotment->setLabel('Date of Allotment:');
		$dateOfAllotment->setAttrib( 'class','datePicker' );
		$dateOfAllotment->setAttrib( 'format','Y-m-d' );
		$dateOfAllotment->addValidator(new Zend_Validate_Date());
		$this->addElement($dateOfAllotment);
	
		$dueDate = new Zend_Dojo_Form_Element_DateTextBox('due_date');
		$dueDate->setLabel('Due Date:');
		$dueDate->setAttrib( 'class','datePicker' );
		$dueDate->addValidator(new Zend_Validate_Date());
		$this->addElement($dueDate);
	
		$closingDate = new Zend_Dojo_Form_Element_DateTextBox('closing_date');
		$closingDate->setLabel('Closing Date:');
		$closingDate->setAttrib( 'class','datePicker' );
		$closingDate->addValidator(new Zend_Validate_Date());
		$this->addElement($closingDate);

		// Add the submit button
		$this->addElement('submit', 'submit', array(
													'ignore'   => true,
													'label'    => 'Register'
											));
	}

	private function loadHistory() {

		$hearingDate = new Zend_Dojo_Form_Element_DateTextBox('hearing_date');
		$hearingDate->setLabel('Hearing Date:');
		$hearingDate->setAttrib( 'class','datePicker' );
		$hearingDate->addValidator(new Zend_Validate_Date());
		$this->addElement($hearingDate);

		$nextHearingDate = new Zend_Dojo_Form_Element_DateTextBox('next_hearing_date');
		$nextHearingDate->setLabel('Next Hearing Date:');
		$nextHearingDate->setAttrib( 'class','datePicker' );
		$nextHearingDate->addValidator(new Zend_Validate_Date());
		$this->addElement($nextHearingDate);

		$this->addElement('text', 'judge_name', array(
										            'label'      => 'Judge Name:',
										            'required'   => true,
										            'filters'    => array('StringTrim')           
        ));

		$this->addElement('textarea', 'content', array(
									            'label'      => 'Details:',
									            'required'   => true,
									            'filters'    => array('StringTrim')           
        ));

		// Add the submit button
		$this->addElement('submit', 'submit', array(
													'ignore'   => true,
													'label'    => 'Add Detail'
											));
	}

	private function loadDocument() {
		echo 'case document';
	}

	private function loadBudget() {

		$this->addElement('text', 'amount', array(
										            'label'      => 'Amount:',
										            'required'   => true,
										            'filters'    => array('StringTrim')           
        ));

		$submissionDate = new Zend_Dojo_Form_Element_DateTextBox('submission_date');
		$submissionDate->setLabel('Effected Date:');
		$submissionDate->setAttrib( 'class','datePicker' );
		$submissionDate->addValidator(new Zend_Validate_Date());
		$this->addElement($submissionDate);

		$this->addElement('textarea', 'transaction_details', array(
									            'label'      => 'Details:',
									            'required'   => true,
									            'filters'    => array('StringTrim')           
        ));

		// Add the submit button
		$this->addElement('submit', 'submit', array(
													'ignore'   => true,
													'label'    => 'Add Expense'
											));
	}
}
?>