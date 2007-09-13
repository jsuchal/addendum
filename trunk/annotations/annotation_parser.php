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
	
	class AnnotationsParser {
		private static $cache = array();
	
		public function parse($string) {
			if(!isset(self::$cache[$string])) {
				self::$cache[$string] = $this->doParsing($string);
			}
			return self::$cache[$string];
		}
		
		protected function doParsing($string) {
			$stream = new StringStream($string);
			$annotations = array();
			while($char = $stream->getFirstCharacter()) {
				if($char == '@') {
					$parser = new AnnotationParser();
					$annotation = $parser->parseStream($stream);
					$annotations[get_class($annotation)] = $annotation;
				} else {
					$stream->forward();
				}
			}
			return $annotations;
		}
	}
	
	class AnnotationParameterParser {
		public function parse($stream) {
			$stream->skipSpaces();
			$isComposite = false;
			if($stream->getFirstCharacters(4) == 'true') {
				$value = true;
				$stream->forward(4);
			} elseif($stream->getFirstCharacters(5) == 'false') {
				$value = false;
				$stream->forward(5);
			} else {
				$char = $stream->getFirstCharacter();
				if(ctype_digit($char) || $char == '-') {
					$parser = new AnnotationNumericParser();
				} elseif($char == '"' || $char == '\'') {
					$parser = new AnnotationStringParser();
				} elseif(ctype_alpha($char)) {
					$isComposite = true;
					$parser = new AnnotationHashPairsParser();
				} elseif($char == '{') {
					$parser = new AnnotationArrayParser();
				} else {
					$parser = new AnnotationDummyParser();
				}
				$value = $parser->parse($stream);
			}
			return array($value, $isComposite);
		}
	}
	
	class AnnotationDummyParser {
		public function parse($stream) {
			$stream->forward();
			return false;
		}
	}
	
	class AnnotationStringParser {
		public function parse($stream) {
			$escapeCharacter = $stream->shift();
			$string = '';
			while(($char = $stream->getFirstCharacter()) !== false) {
				if($stream->getFirstCharacters(2) == '\\'.$escapeCharacter) {
					$char = $escapeCharacter;
					$stream->forward();
				} elseif($char == $escapeCharacter) {
					$stream->forward();
					break;
				}
				$string .= $char;
				$stream->forward();
			}
			return $string;
		}
	}
	
	class AnnotationNumericParser {
		public function parse($stream) {
			$number = '';
			$sign = 1;
			if($stream->getFirstCharacter() == '-') {
				$sign = -1;
				$stream->forward();
			}
			while(($char = $stream->getFirstCharacter()) !== false) {
				if(ctype_digit($char) || $char == '.') {
					$number .= $char;
					$stream->forward();
				} else {
					break;
				}
			}
			if(is_numeric($number)) {
				$number = (float) $number;
				if(round($number) == $number) $number = (int) $number;
				return $sign * $number;
			}
		}
	}
	
	class AnnotationHashPairsParser {
		public function parse($stream) {
			$stream->skipSpaces();
			$key = '';
			while($char = $stream->shift()) {
				if($char == ' ') continue;
				if($char == '=') break;
				$key .= $char;
			}
			$parser = new AnnotationValueParser();
			$value = $parser->parse($stream);
			$result = array($key => $value);
			if($stream->getFirstCharacter() == ',') {
				$stream->forward();
				$result = array_merge($result, $this->parse($stream));
			}
			return $result;
		}
	}
	
	class AnnotationValueParser {
		public function parse($stream) {
			$stream->skipSpaces();
			if($stream->getFirstCharacters(4) == 'true') {
				$stream->forward(4);
				$value = true;
			} elseif($stream->getFirstCharacters(5) == 'false') {
				$stream->forward(5);
				$value = false;
			} else {			
				$char = $stream->getFirstCharacter();
				if(ctype_digit($char) || $char == '-') {
					$parser = new AnnotationNumericParser();
				} elseif($char == '"' || $char == '\'') {
					$parser = new AnnotationStringParser();
				} elseif($char == '{') {
					$parser = new AnnotationArrayParser();
				} else {
					$parser = new AnnotationDummyParser();
				}			
				$value = $parser->parse($stream);
			}
			$stream->skipSpaces();
			return $value;
		}
	}
	
	class AnnotationArrayParser {
		public function parse($stream) {
			$stream->forward();
			$array = array();
			while(1) {
				$stream->skipSpaces();
				$char = $stream->getFirstCharacter();
				if($char == '}') {
					$stream->forward();
					return $array;
				} elseif($char == ',') {
					$stream->forward();
				} else {
					$parser = new AnnotationParameterParser();
					list($value, $isComposite) = $parser->parse($stream);
					if($isComposite) {
						$array = array_merge($array, $value);
					} else {
						$array[] = $value;
					}
				}
			}
			return $array;
		}
	}
	
	class AnnotationParser {
		public function parse($string) {
			$stream = new StringStream($string);
			return $this->parseStream($stream);
		}
	
		public function parseStream($stream) {
			$stream->shift();
			$class = '';
			while(!$stream->isEmpty()) {
				$char = $stream->getFirstCharacter();
				if(ctype_alnum($char)) {
					$class .= $char;
					$stream->forward();
				} else {
					break;
				}
			}
			$parameters = false;
			if($c = $stream->getFirstCharacter() == '(') {
				$stream->forward();
				$parser = new AnnotationParameterParser();
				$parameters = $parser->parse($stream);
				$stream->skipSpaces();
				if($stream->shift() != ')')  {
					trigger_error("Error parsing annotation '".$stream->getString()."' at position ".$stream->getPosition());
				}
			}
			return $this->createAnnotation($class, $parameters);
		}
		
		protected function createAnnotation($class, $parameters) {
			list($value, $isComposite) = $parameters;
			$reflection = new ReflectionClass($class);
			return $reflection->newInstance($value, $isComposite);
		}
	}
	
	class StringStream {
		public $string;
		public $length;
		public $position;
		
		public function __construct($string) {
			$this->string = $string;
			$this->length = strlen($string);
			$this->position = 0;
		}
		
		public function isEmpty() {
			return !$this->hasAtLeast(1);
		}
		
		public function shift() {
			$char = $this->getFirstCharacter();
			$this->position++;
			return $char;
		}
		
		public function forward($steps = 1) {
			$this->position += $steps;
		}
		
		public function getFirstCharacters($length) {
			if($this->hasAtLeast($length)) {
				return substr($this->string, $this->position, $length);
			}
			return false;
		}
		
		public function getFirstCharacter() {
			if($this->hasAtLeast(1)) {
				return $this->string{$this->position};
			}
			return false;
		}
		
		public function skipSpaces() {
			while($char = $this->getFirstCharacter()) {
				if($char != ' ') break;
				$this->forward();
			}
		}
		
		public function getString() {
			return $this->string;
		}
		
		public function getPosition() {
			return $this->position;
		}
		
		private function hasAtLeast($characters) {
			return ($this->position + $characters <= $this->length);
		}
	}
?>
