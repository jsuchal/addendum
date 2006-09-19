<?php
    require_once('simpletest/unit_tester.php');
    require_once('simpletest/reporter.php');
    
    class AllTests extends GroupTest {
          function __construct() {
              parent::__construct('All Tests');
              $path = dirname(__FILE__);
              $this->addTestFile($path.'/acceptance_test.php');
              $this->addTestFile($path.'/annotation_test.php');
              $this->addTestFile($path.'/annotation_parser_test.php');
          }
      }
    
    $test = new AllTests();
    if (SimpleReporter::inCli()) {
        exit ($test->run(new TextReporter()) ? 0 : 1);
    }
    $test->run(new HtmlReporter());
?>
