<?php
require_once '../pdo.php';

require_once 'case.php';
require_once 'rewriteSelect.php';
require_once 'rewriteInsert.php';
require_once 'm2m.php';

class madPDOSuite extends PHPUnit_Framework_TestSuite {
    static public function suite(  ) {
        $suite = new PHPUnit_Framework_TestSuite( 'madPDO' );
        $suite->addTestSuite( new PHPUnit_Framework_TestSuite( 'madPDOM2MTest' ) ); 
        $suite->addTestSuite( new PHPUnit_Framework_TestSuite( 'madPDORewriteSelectTest' ) ); 
        $suite->addTestSuite( new PHPUnit_Framework_TestSuite( 'madPDORewriteInsertTest' ) ); 
        $suite->addTestSuite( new PHPUnit_Framework_TestSuite( 'madPDOM2MTest' ) ); 
        return $suite;
    }
}

?>
