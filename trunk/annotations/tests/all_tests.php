<?php
    require_once('simpletest/unit_tester.php');
    require_once('simpletest/reporter.php');
    
    require_once(dirname(__FILE__).'/acceptance_test.php');
    require_once(dirname(__FILE__).'/annotation_test.php');
    require_once(dirname(__FILE__).'/constrained_annotation_test.php');
    require_once(dirname(__FILE__).'/annotation_parser_test.php');
    require_once(dirname(__FILE__).'/doc_comment_test.php');
    
    class AllTests extends GroupTest {
          function __construct($title = false) {
              parent::__construct($title);
              $path = dirname(__FILE__);
              $this->addTestClass('TestOfAnnotations');
              $this->addTestClass('TestOfAnnotation');
              $this->addTestClass('TestOfConstrainedAnnotation');
              $this->addTestClass('TestOfAnnotationParser');
              $this->addTestClass('TestOfDocComment');
          }
      }
    
    AddendumCompatibility::setRawMode(false);
    $test = new AllTests('All tests in reflection mode');
    $test->run(new HtmlReporter());
    
    AddendumCompatibility::setRawMode(true);
    $test = new AllTests('All tests in raw mode');
    $test->run(new HtmlReporter());
?>
