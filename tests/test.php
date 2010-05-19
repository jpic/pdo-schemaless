<?php

include '../pdo.php';

function handler( $e ) {
    if ( $e instanceof madPDOException ) {
        var_dump( "FAILED", $e->s->queryString );
    }
}
set_exception_handler( 'handler' );

function exportSchema( $pdo ) {
    $queries = array(  );

    foreach( $pdo->schemalessTables as $tableName => $columns ) {
        $query = "CREATE TABLE `$tableName` ( id {$pdo->idSignature}, ";
        $query.= implode( ' TEXT, ', $columns );
        $query.= " TEXT ) Engine=InnoDb;";
        
        $queries[] = $query;
    }

    return implode( "\n", $queries );
}

function exportData( $pdo ) {
    $queries = array(  );

    foreach( $pdo->schemalessTables as $tableName => $columns ) {
        $rows = $pdo->query( "select * from $tableName", PDO::FETCH_ASSOC );
        foreach( $rows as $row ) {
            $query = "INSERT INTO `$tableName` SET ";
            $parts = array(  );
            
            foreach( $row as $column => $value ) {
                $parts[] = sprintf(
                    "`$column` = \"%s\"",
                    addslashes( $value )
                );
            }

            $query .= implode( ', ', $parts );
            $query .= ";";

            $queries[] = $query;
        }
    }

    return implode( "\n", $queries );
}

function dropTables( $pdo ) {
    $tables = $pdo->query('SHOW TABLES', PDO::FETCH_COLUMN, 0)->fetchAll(  );
    foreach( $tables as $table ) {
        $pdo->query( "DROP TABLE `$table`" );
    }
}

function truncateTables( $pdo ) {
    $tables = $pdo->query('SHOW TABLES', PDO::FETCH_COLUMN, 0)->fetchAll(  );
    foreach( $tables as $table ) {
        $pdo->query( "TRUNCATE TABLE `$table`" );
        $pdo->query( "ALTER TABLE `$table` AUTO_INCREMENT = 1" );
    }
}

function runSqlFile( $path, $pdo ) {
    foreach( explode( ";", file_get_contents( $path ) ) as $sql ) {
        $sql = trim( $sql );

        if ( !$sql ) {
            continue;
        }
    
        $pdo->prepare( $sql )->execute(  ) or die( "Failed: $sql" );
    }
}

function assertSqlFile( $dataFile, $data ) {
    if ( !file_exists( $dataFile ) ) {
        file_put_contents( $dataFile, $data );
        trigger_error( "Created $dataFile" );
    } else {
        $tempFile = tempnam( sys_get_temp_dir(), 'mad' );
        file_put_contents( $tempFile, $data );
        $diff = shell_exec( "diff -u $tempFile $dataFile" );
        assert( "is_null(" . var_export( $diff, true ) . ")" ) or die( $diff );
    }
}

$testIterations = 100;
$insertIterations = 1000;

$madPdo = new madPDOFramework( 'mysql:dbname=mad;host=localhost', 'root' );
$madPdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
dropTables( $madPdo );

$vanillaPdo = new PDO( 'mysql:dbname=vanilla;host=localhost', 'root' );
$vanillaPdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
dropTables( $vanillaPdo );

$tests = glob( dirname( __FILE__ ) . '/fixtures/*' );

echo "Will run insert.sql $insertIterations times\n";
echo "Will run select tests $testIterations times\n";

foreach( $tests as $test ) {
    $testName = substr( $test, strrpos( $test, DIRECTORY_SEPARATOR ) );
    $madPdo->cacheReset(  );

    truncateTables( $madPdo );
    truncateTables( $vanillaPdo );

    runSqlFile( "$test/insert.sql", $madPdo );

    dropTables( $vanillaPdo );

    $schema = exportSchema( $madPdo );
    $schemaFile = "$test/schema.sql";
    assertSqlFile( $schemaFile, $schema );

    runSqlFile( $schemaFile, $vanillaPdo );

    $data = exportData( $madPdo );
    $dataFile = "$test/data.sql";
    assertSqlFile( $dataFile, $data );

    for( $i=1; $i <= $insertIterations; $i++ ) {
        runSqlFile( "$test/insert.sql", $vanillaPdo );
    }

    if ( $insertIterations > 1 ) {
        for( $i=1; $i <= $insertIterations - 1; $i++ ) {
            runSqlFile( "$test/insert.sql", $madPdo );
        }
    }

    $selects = file_get_contents( "$test/select.sql" );

    echo "Test: $testName\n";

    foreach( explode( ";", $selects ) as $select ) {

        if ( !trim( $select ) ) {
            continue;
        }

        echo "Testing:\n$select\n";

        $vanillaQuery = $vanillaPdo->prepare( $select);

        $vanillaStart = microtime( true );

        for( $i = 1; $i <= $testIterations; $i++ ) {
            $vanillaQuery->execute(  );
        }
        $vanillaData = $vanillaQuery->fetchAll( PDO::FETCH_ASSOC );

        $vanillaEnd   = microtime( true );
        $vanillaTime  = $vanillaEnd - $vanillaStart;
    
        $vanillaTempFile = tempnam( sys_get_temp_dir(), 'van' );
        file_put_contents( $vanillaTempFile, var_export( $vanillaData, true ) );
   
        $madQuery = $madPdo->prepare( $select);
        
        $madStart = microtime( true );

        echo "Rewrote to:\n";
        echo $madQuery->queryString;
        echo "\n";

        for( $i = 1; $i <= $testIterations; $i++ ) {
            $madQuery->execute(  );
        }
        $madData = $madQuery->fetchAll( PDO::FETCH_ASSOC );

        $madEnd   = microtime( true );
        $madTime  = $madEnd - $madStart;

        assert ( 'count( $madData ) == count( $vanillaData )' );
        assert ( 'count( $madData ) > 0' );
    
        $madTempFile = tempnam( sys_get_temp_dir(), 'mad' );
        file_put_contents( $madTempFile, var_export( $madData, true ) );
    
        $diff = shell_exec( "diff -u $madTempFile $vanillaTempFile" );
        assert( "is_null(\$diff)" ) or die( "Found diff:\n$diff\n" );
        
        $diffTime = $madTime - $vanillaTime;
        $diffPercent = number_format( ( $madTime / $vanillaTime ) * 100 - 100, 2 );

        echo "\n";

        echo "Row count        " . count( $madData ) . "\n";
        echo "Vanilla time     $vanillaTime\n";
        echo "Rewritten time   $madTime\n";
        echo "Performance loss for $testIterations iterations: $diffTime ($diffPercent%)\n";
        if ( $testIterations != 1 ) {
            echo "Average performance loss for 1 iterations: " . number_format( ( $diffTime / $testIterations ), 8 ) . "\n";
        }

        echo "\n";
    }
}

?>
