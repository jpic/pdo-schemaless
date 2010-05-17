<?php
require_once 'PHPUnit/Framework.php';

require_once '../pdo.php';
require_once 'recipeTest.php';

class myTest extends PHPUnit_Framework_TestSuite {
    public function __construct(  ) {
        $pdo = new madPDO( 'mysql:dbname=testdb;host=localhost', 'root' );
        $pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        $this->sharedFixture = $pdo;
        var_dump( "In suite", $this->sharedFixture );
    }

    static public function suite(  ) {
        $suite = new PHPUnit_Framework_TestSuite( 'madPDO' );
        $suite->addTestSuite( new PHPUnit_Framework_TestSuite( 'madPDORecipeTest' ) ); 
        return $suite;
    }
}

?>
