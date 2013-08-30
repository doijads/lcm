<?php
 abstract class mathematics{
 /*** child class must define these methods ***/
 abstract protected function getMessage();
 abstract protected function addTwo($num1);
 public function testfun()
 {
	$var = "This is test fun";
	return $var;
 }
 /**
 *
 * method common to both classes
 *
 **/
 public function showMessage() {
   echo $this->getMessage();
 }
 } /*** end of class ***/
 class myMath extends mathematics{
 /**
 *
 * Prefix to the answer
 *
 * @return string
 *
 **/
 protected function getMessage(){
   return "The anwser is: ";
 }
 /**
 *
 * add two to a number
 *
 * @access public
 *
 * @param $num1 A number to be added to
 *
 * @return int
 *
 **/
 public function addTwo($num1) {
   return $num1+2;
 }
 } /*** end of class ***/
 /*** a new instance of myMath ***/
 $myMath = new myMath;
 echo $myMath->testfun();
 
 /*** show the message ***/
 $myMath->showMessage();
 /*** do the math ***/
 echo $myMath->addTwo(4);
 ?>
