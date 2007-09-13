<?php
	require_once('simpletest/autorun.php');
	require_once(dirname(__FILE__).'/../../annotations.php');
	
	
	interface DummyInterface {}
	
	class ParentExample {}
	
	/** @FirstAnnotation @SecondAnnotation */
	class Example extends ParentExample implements DummyInterface {
		/** @SecondAnnotation */
		private $exampleProperty;
		
		public function __construct() {}
		
		/** @FirstAnnotation */
		public function exampleMethod() {
		}
	}
	
	class FirstAnnotation extends Annotation {}
	class SecondAnnotation extends Annotation {}
	
	class TestOfAnnotations extends UnitTestCase {
		public function testReflectionAnnotatedClass() {
			$reflection = new ReflectionAnnotatedClass('Example');
			$this->assertTrue($reflection->hasAnnotation('FirstAnnotation'));
			$this->assertTrue($reflection->hasAnnotation('SecondAnnotation'));
			$this->assertFalse($reflection->hasAnnotation('NonExistentAnnotation'));
			$this->assertIsA($reflection->getAnnotation('FirstAnnotation'), 'FirstAnnotation');
			$this->assertIsA($reflection->getAnnotation('SecondAnnotation'), 'SecondAnnotation');
			$annotations = $reflection->getAnnotations();
			$this->assertEqual(count($annotations), 2);
			$this->assertIsA($annotations[0], 'FirstAnnotation');
			$this->assertIsA($annotations[1], 'SecondAnnotation');
			$this->assertFalse($reflection->getAnnotation('NonExistentAnnotation'));
			
			$this->assertIsA($reflection->getConstructor(), 'ReflectionAnnotatedMethod');
			$this->assertIsA($reflection->getMethod('exampleMethod'), 'ReflectionAnnotatedMethod');
			foreach($reflection->getMethods() as $method) {
				$this->assertIsA($method, 'ReflectionAnnotatedMethod');
			}
			
			$this->assertIsA($reflection->getProperty('exampleProperty'), 'ReflectionAnnotatedProperty');
			foreach($reflection->getProperties() as $property) {
				$this->assertIsA($property, 'ReflectionAnnotatedProperty');
			}
			
			foreach($reflection->getInterfaces() as $interface) {
				$this->assertIsA($interface, 'ReflectionAnnotatedClass');
			}
			
			$this->assertIsA($reflection->getParentClass(), 'ReflectionAnnotatedClass');
			
			
		}
		
		public function testReflectionAnnotatedMethod() {
			$reflection = new ReflectionAnnotatedMethod('Example', 'exampleMethod');
			$this->assertTrue($reflection->hasAnnotation('FirstAnnotation'));
			$this->assertFalse($reflection->hasAnnotation('NonExistentAnnotation'));
			$this->assertIsA($reflection->getAnnotation('FirstAnnotation'), 'FirstAnnotation');
			$this->assertFalse($reflection->getAnnotation('NonExistentAnnotation'));
			
			$annotations = $reflection->getAnnotations();
			$this->assertEqual(count($annotations), 1);
			$this->assertIsA($annotations[0], 'FirstAnnotation');
			
			$this->assertIsA($reflection->getDeclaringClass(), 'ReflectionAnnotatedClass');
		}
		
		public function testReflectionAnnotatedProperty() {
			$reflection = new ReflectionAnnotatedProperty('Example', 'exampleProperty');
			$this->assertTrue($reflection->hasAnnotation('SecondAnnotation'));
			$this->assertFalse($reflection->hasAnnotation('FirstAnnotation'));
			$this->assertIsA($reflection->getAnnotation('SecondAnnotation'), 'SecondAnnotation');
			$this->assertFalse($reflection->getAnnotation('NonExistentAnnotation'));
			
			$annotations = $reflection->getAnnotations();
			$this->assertEqual(count($annotations), 1);
			$this->assertIsA($annotations[0], 'SecondAnnotation');
			
			$this->assertIsA($reflection->getDeclaringClass(), 'ReflectionAnnotatedClass');
		}
	}
?>
