<?php

class madPDOTestCase extends PHPUnit_Framework_TestCase {
    
    /**
     * A string of mysql queries to run to set up the case. 
     */
    static public $pdo;

    static public function setUpBeforeClass() {
        self::$pdo = new madPDO( 'mysql:dbname=testdb;host=localhost', 'root' );
        self::$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        
        $tables = self::$pdo->query('show tables', PDO::FETCH_COLUMN, 0)->fetchAll();
        foreach( $tables as $table ) {
            self::$pdo->query( "drop table $table" );
        }

        if ( !isset( static::$initial ) ) {
            return;
        }

        foreach( explode( ";", static::$initial ) as $sql ) {
            $sql = trim( $sql );

            if ( !$sql ) {
                continue;
            }
            
            self::$pdo->prepare( $sql )->execute(  );
        }
    }

    public function testSelect( $sql, $expected = null ) {
        $select = self::$pdo->prepare( $sql );
        $select->setFetchMode( PDO::FETCH_ASSOC );
        $select->execute();

        $result = $select->fetchAll(  );

        if ( is_null( $expected ) ) {
            var_export( $result );
            $this->markTestSkipped(  );
            return true;
        }

        $this->assertEquals( $expected, $result );
    }
}

?>
