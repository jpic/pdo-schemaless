<?php
require_once 'case.php';

class madPDORewriteInsertTest extends madPDOTestCase {
    static public function insertProvider(  ) {
        return array(
            array(
                'insert into authors set name = :name',
                array(
                    'name' => 'james'
                ),
                array(
                  0 => 'CREATE TABLE authors (id INT(12) PRIMARY KEY AUTO_INCREMENT) ENGINE=InnoDb',
                  1 => 'INSERT INTO authors VALUES()',
                  2 => 'CREATE TABLE authors_name (id INT(12), value TEXT, UNIQUE(id)) ENGINE=InnoDb',
                  3 => 'INSERT INTO authors_name VALUES( :id, :name ) ON DUPLICATE KEY UPDATE value = :name',
                ),
            ),
            array(
                'insert into posts set title = :title',
                array( 
                    'title' => 'Schemaless PDO',
                ),
                array(
                    'CREATE TABLE posts (id INT(12) PRIMARY KEY AUTO_INCREMENT) ENGINE=InnoDb',
                    'INSERT INTO posts VALUES()',
                    'CREATE TABLE posts_title (id INT(12), value TEXT, UNIQUE(id)) ENGINE=InnoDb',
                    'INSERT INTO posts_title VALUES( :id, :title ) ON DUPLICATE KEY UPDATE value = :title',
                ),
            ),
            array(
                'insert into `posts` set `title` = :title, author = :author_id',
                array(
                    'title' => 'Update: schemaless PDO rewriten without the flaws!',
                    'author_id' => 1,
                ),
                array (
                  0 => 'INSERT INTO posts VALUES()',
                  1 => 'INSERT INTO posts_title VALUES( :id, :title ) ON DUPLICATE KEY UPDATE value = :title',
                  2 => 'CREATE TABLE posts_author (id INT(12), value TEXT, UNIQUE(id)) ENGINE=InnoDb',
                  3 => 'INSERT INTO posts_author VALUES( :id, :author_id ) ON DUPLICATE KEY UPDATE value = :author_id',
                )
            ),
        );
    }

    /** 
     * @dataProvider insertProvider
     */
    public function testInsert( $insert, $data, $expected = null ) {
        $objects = $this->pdo->prepare( $insert );

        $result = array(  );
        foreach( $objects->objects as $statement ) {
            $result[] = $statement->queryString;
        }

        if ( is_null( $expected ) ) {
            var_export( $result );
        }

        $this->assertEquals( $expected, $result );

        $success = $objects->execute( $data );
        $this->assertTrue( $success, "Statements should be successful" );
    }
}


?>
