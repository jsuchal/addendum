<?php
	/**
	 * Addendum PHP Reflection Annotations
	 * http://code.google.com/p/addendum/
	 *
	 * Copyright (C) 2006 Jan "johno Suchal <johno@jsmf.net>
	
	 * This library is free software; you can redistribute it and/or
	 * modify it under the terms of the GNU Lesser General Public
	 * License as published by the Free Software Foundation; either
	 * version 2.1 of the License, or (at your option) any later version.
	
	 * This library is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
	 * Lesser General Public License for more details.
	
	 * You should have received a copy of the GNU Lesser General Public
	 * License along with this library; if not, write to the Free Software
	 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
	 **/
	
	require_once(dirname(__FILE__).'/annotations/annotation_parser.php');
	
	class Annotation {
		public $value;
		
		public function __construct($data, $isComposite) {
			if($isComposite) {
				$reflection = new ReflectionClass($this);
				foreach($data as $key => $value) {
					if($reflection->hasProperty($key)) {
						$this->$key = $value;
					} else {
						$class = $reflection->getName();
						trigger_error("Property '$key' not defined for annotation '$class'");
					}
				}
			} else {
				$this->value = $data;
			}
		}
	}
	
	class ReflectionAnnotatedClass extends ReflectionClass {
		private $annotations;
		
		public function __construct($class) {
			parent::__construct($class);
			$this->annotations = $this->createParser()->parse(AddendumCompatibility::getDocComment($this));
		}
		
		public function hasAnnotation($annotation) {
			return isset($this->annotations[$annotation]);
		}
		
		public function getAnnotation($annotation) {
			if($this->hasAnnotation($annotation)) {
				return $this->annotations[$annotation];
			}
			return false;
		}
		
		public function getAnnotations() {
			return array_values($this->annotations);
		}
		
		public function getConstructor() {
			return $this->createReflectionAnnotatedMethod(parent::getConstructor());
		}
		
		public function getMethod($name) {
			return $this->createReflectionAnnotatedMethod(parent::getMethod($name));
		}
		
		public function getMethods() {
			$result = array();
			foreach(parent::getMethods() as $method) {
				$result[] = $this->createReflectionAnnotatedMethod($method);
			}
			return $result;
		}
		
		public function getProperty($name) {
			return $this->createReflectionAnnotatedProperty(parent::getProperty($name));
		}
		
		public function getProperties() {
			$result = array();
			foreach(parent::getProperties() as $property) {
				$result[] = $this->createReflectionAnnotatedProperty($property);
			}
			return $result;
		}
		
		public function getInterfaces() {
			$result = array();
			foreach(parent::getInterfaces() as $interface) {
				$result[] = $this->createReflectionAnnotatedClass($interface);
			}
			return $result;
		}
		
		public function getParentClass() {
			$class = parent::getParentClass();
			return $this->createReflectionAnnotatedClass($class);
		}
		
		protected function createParser() {
			return new AnnotationsParser();
		}
		
		private function createReflectionAnnotatedClass($class) {
			return ($class !== false) ? new ReflectionAnnotatedClass($class->getName()) : false;
		}
		
		private function createReflectionAnnotatedMethod($method) {
			return ($method !== null) ? new ReflectionAnnotatedMethod($this->getName(), $method->getName()) : null;
		}
		
		private function createReflectionAnnotatedProperty($property) {
			return ($property !== null) ? new ReflectionAnnotatedProperty($this->getName(), $property->getName()) : null;
		}
	}
	
	class ReflectionAnnotatedMethod extends ReflectionMethod {
		private $annotations;
		
		public function __construct($class, $name) {
			parent::__construct($class, $name);
			$this->annotations = $this->createParser()->parse(AddendumCompatibility::getDocComment($this));
		}
		
		public function hasAnnotation($annotation) {
			return isset($this->annotations[$annotation]);
		}
		
		public function getAnnotation($annotation) {
			if($this->hasAnnotation($annotation)) {
				return $this->annotations[$annotation];
			}
			return false;
		}
		
		public function getAnnotations() {
			return array_values($this->annotations);
		}
		
		public function getDeclaringClass() {
			$class = parent::getDeclaringClass();
			return new ReflectionAnnotatedClass($class->getName());
		}
		
		protected function createParser() {
			return new AnnotationsParser();
		}
	}
	
	class ReflectionAnnotatedProperty extends ReflectionProperty {
		private $annotations;
		
		public function __construct($class, $name) {
			parent::__construct($class, $name);
			$this->annotations = $this->createParser()->parse(AddendumCompatibility::getDocComment($this));
		}
		
		public function hasAnnotation($annotation) {
			return isset($this->annotations[$annotation]);
		}
		
		public function getAnnotation($annotation) {
			if($this->hasAnnotation($annotation)) {
				return $this->annotations[$annotation];
			}
			return false;
		}
		
		public function getAnnotations() {
			return array_values($this->annotations);
		}
		
		public function getDeclaringClass() {
			$class = parent::getDeclaringClass();
			return new ReflectionAnnotatedClass($class->getName());
		}
		
		protected function createParser() {
			return new AnnotationsParser();
		}
	}
	
	class AddendumCompatibility {
		private static $rawMode;
	
		public static function getDocComment($reflection) {
			if(self::checkRawDocCommentParsingNeeded()) {
				$docComment = new DocComment();
				return $docComment->get($reflection);
			} else {
				return $reflection->getDocComment();
			}
		}
		
		/** Raw mode test */
		private static function checkRawDocCommentParsingNeeded() {
			if(self::$rawMode === null) {
				$reflection = new ReflectionClass('AddendumCompatibility');
				$method = $reflection->getMethod('checkRawDocCommentParsingNeeded');
				self::setRawMode($method->getDocComment() === false);
			}
			return self::$rawMode;
		}
		
		public static function setRawMode($enabled = true) {
			if($enabled) {
				require_once(dirname(__FILE__).'/annotations/doc_comment.php');
			}
			self::$rawMode = $enabled;
		}
	}
?>
