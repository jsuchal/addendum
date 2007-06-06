<?php
	require_once('simpletest/unit_tester.php');
	require_once('simpletest/reporter.php');
	require_once('simpletest/mock_objects.php');
	
	require_once(dirname(__FILE__).'/../../annotations.php');
	
	class MockAnnotationsParser extends AnnotationsParser {
		public static $parseCount = 0;
		
		protected function doParsing($string) {
			self::$parseCount++;
			return parent::doParsing($string);
		}
	}
	
	class TestAnnotation extends Annotation {
		public $ratio;
		public $message;
	}
	
	class AnotherAnnotation extends Annotation {
	}
	
	class TestOfAnnotationParser extends UnitTestCase {
		public function testBasicAnnotation() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation');
			$this->assertIsA($annotation, 'TestAnnotation');
		}
		
		public function testIntegerValuedAnnotation() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation(2)');
			$this->assertIdentical($annotation->value, 2);
		}
		
		public function testNegativeIntegerValuedAnnotation() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation(-2)');
			$this->assertIdentical($annotation->value, -2);
		}
		
		public function testFloatValuedAnnotation() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation(2.42)');
			$this->assertIdentical($annotation->value, 2.42);
		}
		
		public function testStringValuedAnnotation() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation("Hello")');
			$this->assertIdentical($annotation->value, "Hello");
		}
		
		public function testBooleanValuedAnnotation() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation(true)');
			$this->assertIdentical($annotation->value, true);
			$annotation = $parser->parse('@TestAnnotation(false)');
			$this->assertIdentical($annotation->value, false);
		}
		
		public function testStringValuedWithEscapedQuotesAnnotation() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation("He said: \"johno!\"")');
			$this->assertEqual($annotation->value, 'He said: "johno!"');
		}
		
		public function testStringValuedSingleQuotedAnnotation() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse("@TestAnnotation('Hello')");
			$this->assertEqual($annotation->value, 'Hello');
		}
		
		public function testStringValuedWithEscapedSingleQuotesAnnotation() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse("@TestAnnotation('He said: \'johno!\'')");
			$this->assertEqual($annotation->value, "He said: 'johno!'");
		}
		
		public function testSimpleHashedAnnotation() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation(ratio=4.2)');
			$this->assertEqual($annotation->ratio, 4.2);
		}
		
		public function testMultiHashedAnnotation() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation(ratio=1,message="Wow!")');
			$this->assertEqual($annotation->ratio, 1);
			$this->assertEqual($annotation->message, "Wow!");
		}
		
		public function testMultiHashedAnnotationWithSpace() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation(ratio=1, message="Wow!")');
			$this->assertEqual($annotation->ratio, 1);
			$this->assertEqual($annotation->message, "Wow!");
		}
		public function testMultiHashedAnnotationWithSpaces() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation(ratio=1 , message="Wow!")');
			$this->assertEqual($annotation->ratio, 1);
			$this->assertEqual($annotation->message, "Wow!");
		}
		
		public function testSimpleHashedAnnotationWithSpaces() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation(ratio = 4.2)');
			$this->assertIdentical($annotation->ratio, 4.2);
		}
		
		public function testHashedAnnotationWithTrue() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation(message = true)');
			$this->assertIdentical($annotation->message, true);
		}
		
		public function testHashedAnnotationWithFalse() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation(message = false)');
			$this->assertIdentical($annotation->message, false);
		}
		
		public function testIntegerValuedAnnotationWithSpaces() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation( 2  )');
			$this->assertIdentical($annotation->value, 2);
		}
		
		public function testAnnotationWithNoTrailingBracketThrowsError() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation(2');
			$this->assertError("Error parsing annotation '@TestAnnotation(2' at position 18");
		}
		
		public function testAnnotationsParser() {
			$parser = new AnnotationsParser();
			$block = "
				/**
				 * @TestAnnotation(ratio = 2.5)
				 * @AnotherAnnotation('Hello')
				 **/";
			$annotations = $parser->parse($block);
			$first = $annotations['TestAnnotation'];
			$second = $annotations['AnotherAnnotation'];
			$this->assertIsA($first, 'TestAnnotation');
			$this->assertEqual($first->ratio, 2.5);
			$this->assertIsA($second, 'AnotherAnnotation');
			$this->assertEqual($second->value, 'Hello');
		}
		
		public function testAnnotationsParserCachesResults() {
			$parser = new MockAnnotationsParser();
			$parser->parse('@TestAnnotation');
			$parser = new MockAnnotationsParser();
			$parser->parse('@TestAnnotation');
			$this->assertEqual(MockAnnotationsParser::$parseCount, 1);
			
		}
	}
?>
