<?php
	require_once('simpletest/autorun.php');
	require_once(dirname(__FILE__).'/../../annotations.php');
	
	class TestingAnnotation extends Annotation {
		public $optional = 'default';
		public $required;
	}
	
	class TestOfAnnotation extends UnitTestCase {
		public function testConstructorFillsValueOnNonArrayParameters() {
			$annotation = new TestingAnnotation(3.14);
			$this->assertEqual($annotation->value, 3.14);
		}
		
		public function testConstructorsFillsParameters() {
			$annotation = new TestingAnnotation(array('optional' => 1, 'required' => 2));
			$this->assertEqual($annotation->optional, 1);
			$this->assertEqual($annotation->required, 2);
		}
		
		public function testConstructorThrowsErrorOnInvalidParameter() {
			$annotation = new TestingAnnotation(array('unknown' => 1));
			$this->assertError("Property 'unknown' not defined for annotation 'TestingAnnotation'");
		}
		
		public function TODO_testConstructorThrowsErrorWithoutSpecifingRequiredParameters() {
			$annotation = new TestingAnnotation();
			$this->assertError("Property 'required' in annotation 'TestingAnnotation' is required");
		}
	}
?>
