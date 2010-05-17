<?php
#
#class madPDOTestCase extends PHPUnit_Framework_TestCase {
#    public $initial;
#    public $pdo;
#
#    public function setUp() {
#        $this->pdo = $this->sharedFixture;
#
#        if ( ! $this->pdo instanceof PDO ) {
#            die( 'Where is pdo?' );
#        }
#
#        foreach( explode( ";", $this->initial ) as $sql ) {
#            $sql = trim( $sql );
#
#            if ( !$sql ) {
#                continue;
#            }
#            
#            $this->pdo->prepare( $sql )->execute(  );
#        }
#    }
#
#    public function tearDown(  ) {
#        $this->pdo = null;
#    }
#}
#
?>
