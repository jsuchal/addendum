<?php
	require_once('simpletest/autorun.php');
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
		private function assertAnnotationClass($annotation, $class) {
			$this->assertEqual($annotation[0], $class);
		}
		
		private function assertIdenticalAnnotationValue($annotation, $key, $expected) {
			$this->assertEqual($annotation[1][$key], $expected);
		}
	
		public function testBasicAnnotation() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation');
			$this->assertAnnotationClass($annotation, 'TestAnnotation');
		}
		
		public function testIntegerValuedAnnotation() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation(2)');
			$this->assertIdenticalAnnotationValue($annotation, 'value', 2);
		}
		
		public function testNegativeIntegerValuedAnnotation() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation(-2)');
			$this->assertIdenticalAnnotationValue($annotation, 'value', -2);
		}
		
		public function testFloatValuedAnnotation() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation(2.42)');
			$this->assertIdenticalAnnotationValue($annotation, 'value', 2.42);
		}
		
		public function testStringValuedAnnotation() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation("Hello")');
			$this->assertIdenticalAnnotationValue($annotation, 'value', "Hello");
		}
		
		public function testBooleanValuedAnnotation() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation(true)');
			$this->assertIdenticalAnnotationValue($annotation, 'value', true);
			$annotation = $parser->parse('@TestAnnotation(false)');
			$this->assertIdenticalAnnotationValue($annotation, 'value', false);
		}
		
		public function testStringValuedWithEscapedQuotesAnnotation() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation("He said: \"johno!\"")');
			$this->assertIdenticalAnnotationValue($annotation, 'value', 'He said: "johno!"');
		}
		
		public function testStringValuedSingleQuotedAnnotation() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse("@TestAnnotation('Hello')");
			$this->assertIdenticalAnnotationValue($annotation, 'value', 'Hello');
		}
		
		public function testStringValuedWithEscapedSingleQuotesAnnotation() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse("@TestAnnotation('He said: \'johno!\'')");
			$this->assertIdenticalAnnotationValue($annotation, 'value', "He said: 'johno!'");
		}
		
		public function testSimpleHashedAnnotation() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation(ratio=4.2)');
			$this->assertIdenticalAnnotationValue($annotation, 'ratio', 4.2);
		}
		
		public function testSimpleHashedAnnotationWithNegativeValue() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation(ratio=-4.2)');
			$this->assertIdenticalAnnotationValue($annotation, 'ratio', -4.2);
		}
		
		public function testMultiHashedAnnotation() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation(ratio=1,message="Wow!")');
			$this->assertIdenticalAnnotationValue($annotation, 'ratio', 1);
			$this->assertIdenticalAnnotationValue($annotation, 'message', "Wow!");
		}
		
		public function testMultiHashedAnnotationWithSpace() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation(ratio=1, message="Wow!")');
			$this->assertIdenticalAnnotationValue($annotation, 'ratio', 1);
			$this->assertIdenticalAnnotationValue($annotation, 'message', "Wow!");
		}
		public function testMultiHashedAnnotationWithSpaces() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation(ratio=1 , message="Wow!")');
			$this->assertIdenticalAnnotationValue($annotation, 'ratio', 1);
			$this->assertIdenticalAnnotationValue($annotation, 'message', "Wow!");
		}
		
		public function testSimpleHashedAnnotationWithSpaces() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation(ratio = 4.2)');
			$this->assertIdenticalAnnotationValue($annotation, 'ratio', 4.2);
		}
		
		public function testHashedAnnotationWithTrue() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation(message = true)');
			$this->assertIdenticalAnnotationValue($annotation, 'message', true);
		}
		
		public function testHashedAnnotationWithFalse() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation(message = false)');
			$this->assertIdenticalAnnotationValue($annotation, 'message', false);
		}
		
		public function testIntegerValuedAnnotationWithSpaces() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation( 2  )');
			$this->assertIdenticalAnnotationValue($annotation, 'value', 2);
		}
		
		public function testAnnotationWithNoTrailingBracketThrowsError() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation(2');
			$this->assertError("Error parsing annotation '@TestAnnotation(2' at position 18");
		}
		
		public function testAnnotationWithZeroInString() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation(message = "test 203")');
			$this->assertIdenticalAnnotationValue($annotation, 'message', 'test 203');
		}
		
		public function testAnnotationWithZeroInNumber() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation(ratio = 0.15)');
			$this->assertIdenticalAnnotationValue($annotation, 'ratio', 0.15);
		}
		
		public function testAnnotationWithEmptyArray() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation({})');
			$this->assertIdenticalAnnotationValue($annotation, 'value', array());
		}
		
		public function testAnnotationWithArray() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation({1, 2, 3})');
			$this->assertIdenticalAnnotationValue($annotation, 'value', array(1, 2, 3));
		}
		
		public function testAnnotationWithNestedArray() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation({1, {2, 3}, 4})');
			$this->assertIdenticalAnnotationValue($annotation, 'value', array(1, array(2, 3), 4));
		}
		
		public function testHashedAnnotationWithArray() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation(ratio = {1, 2.5})');
			$this->assertIdenticalAnnotationValue($annotation, 'ratio', array(1, 2.5));
		}
		
		public function testAnnotationWithHashedArray() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation({key=5})');
			$this->assertIdenticalAnnotationValue($annotation, 'value', array('key' => 5));
		}
		
		public function testAnnotationWithBiggerHashedArray() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation({key=5, key2=4})');
			$this->assertIdenticalAnnotationValue($annotation, 'value', array('key' => 5, 'key2' => 4));
		}
		
		public function TODO_testAnnotationWithMixedArray() {
			$parser = new AnnotationParser();
			$annotation = $parser->parse('@TestAnnotation({key=1, 2, key2=3})');
			$this->dump($annotation[1]['value']);
			$this->assertIdenticalAnnotationValue($annotation, 'value', array('key' => 1, 2, 'key2' => 3));
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
			$this->assertAnnotationClass($first, 'TestAnnotation');
			$this->assertIdenticalAnnotationValue($first, 'ratio', 2.5);
			$this->assertAnnotationClass($second, 'AnotherAnnotation');
			$this->assertIdenticalAnnotationValue($second, 'value', 'Hello');
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
