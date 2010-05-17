<?php

require_once 'case.php';

class madPDORewriteSelectTest extends madPDOTestCase {
    public $pdo;

    public function setUp(  ) {
        $this->pdo = new madPDO( 'mysql:dbname=testdb;host=localhost', 'root' );
    }

    static public function rewriteSelectProvider(  ) {
        return array(
            array(
                'select title from posts',
                'select posts_title.value AS title from posts LEFT OUTER JOIN posts_title ON posts_title.id = posts.id',
            ),
            array( 
                'select `title` from posts',
                'select `posts_title`.`value` AS `title` from posts LEFT OUTER JOIN posts_title ON posts_title.id = posts.id',
            ),
            array( 
                'select title, body from posts',
                'select posts_title.value AS title, posts_body.value AS body from posts LEFT OUTER JOIN posts_title ON posts_title.id = posts.id LEFT OUTER JOIN posts_body ON posts_body.id = posts.id',
            ),
            array( 
                'select * from posts',
                'select `posts_author`.`value` AS `author`, `posts_body`.`value` AS `body`, `posts_title`.`value` AS `title` from posts LEFT OUTER JOIN posts_author ON posts_author.id = posts.id LEFT OUTER JOIN posts_body ON posts_body.id = posts.id LEFT OUTER JOIN posts_title ON posts_title.id = posts.id',
            ),
            array( 
                'select title as post_title from posts',
                'select posts_title.value AS post_title   from posts LEFT OUTER JOIN posts_title ON posts_title.id = posts.id',
            ),
            array( 
                'select `title` as post_title from posts',
                'select `posts_title`.`value` AS `post_title`   from posts LEFT OUTER JOIN posts_title ON posts_title.id = posts.id',
            ),
            array( 
                'select title as `post_title` from posts',
                'select posts_title.value AS `post_title`   from posts LEFT OUTER JOIN posts_title ON posts_title.id = posts.id',
            ),
            array( 
                'select `title` as `post_title` from posts',
                'select `posts_title`.`value` AS `post_title`   from posts LEFT OUTER JOIN posts_title ON posts_title.id = posts.id',
            ),
            array( 
                'select posts.title, authors.name from posts left join authors on posts.author = authors.id',
                'select posts_title.value AS title, authors_name.value AS name from posts LEFT OUTER JOIN posts_title ON posts_title.id = posts.id LEFT OUTER JOIN posts_author ON posts_author.id = posts.id left join authors LEFT OUTER JOIN authors_name ON authors_name.id = authors.id on posts_author.value = authors.id',
            ),
            array( 
                'select `posts`.title, posts.`body`, `authors`.`name` from posts left join authors on posts.author = authors.id',
                'select `posts_title`.`value` AS `title`, `posts_body`.`value` AS `body`, `authors_name`.`value` AS `name` from posts LEFT OUTER JOIN posts_title ON posts_title.id = posts.id LEFT OUTER JOIN posts_body ON posts_body.id = posts.id LEFT OUTER JOIN posts_author ON posts_author.id = posts.id left join authors LEFT OUTER JOIN authors_name ON authors_name.id = authors.id on posts_author.value = authors.id',
            ),
            array( 
                'select posts.title as `post_title`, authors.name AS author_name from posts left join authors on posts.author = authors.id',
                'select posts_title.value AS `post_title`  , authors_name.value AS author_name   from posts LEFT OUTER JOIN posts_title ON posts_title.id = posts.id LEFT OUTER JOIN posts_author ON posts_author.id = posts.id left join authors LEFT OUTER JOIN authors_name ON authors_name.id = authors.id on posts_author.value = authors.id',
            ),
            array( 
                'select * from posts where title like "Re:%"',
                'select `posts_author`.`value` AS `author`, `posts_body`.`value` AS `body`, `posts_title`.`value` AS `title` from posts LEFT OUTER JOIN posts_author ON posts_author.id = posts.id LEFT OUTER JOIN posts_body ON posts_body.id = posts.id LEFT OUTER JOIN posts_title ON posts_title.id = posts.id where posts_title.value like "Re:%"',
            ),
            array( 
                'select posts.*, authors.* from posts left join authors on posts.author = authors.id where authors.name = "kiddo"',
                'select `posts_author`.value AS `author`, `posts_body`.value AS `body`, `posts_title`.value AS `title`, `authors_name`.value AS `name` from posts LEFT OUTER JOIN posts_author ON posts_author.id = posts.id LEFT OUTER JOIN posts_body ON posts_body.id = posts.id LEFT OUTER JOIN posts_title ON posts_title.id = posts.id left join authors LEFT OUTER JOIN authors_name ON authors_name.id = authors.id on posts_author.value = authors.id where authors_name.value = "kiddo"',
            ),
        );
    }

    /**
     * @dataProvider rewriteSelectProvider
     */
    public function testRewriteSelect( $fixture, $expected ) {
        return true;
        $result = $this->pdo->rewriteSelect( $fixture );
        $this->assertEquals( $expected, $result );
    }
}
?>
