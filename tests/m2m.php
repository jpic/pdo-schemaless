<?php

class madPDOM2MTest extends madPDOTestCase {
    static public $initial = '
insert into recipes set title = "mojito", author = 1;
insert into recipes set title = "tipunch", author = 2;
insert into recipes set title = "pizza", author = 1;
insert into recipes set title = "quiche", author = 2;

insert into authors set name = "james";
insert into authors set name = "christophe";

insert into categories set title = "drinks";
insert into categories set title = "middle";

insert into recipe_categories set recipe = 1, category = 1;
insert into recipe_categories set recipe = 2, category = 1;
insert into recipe_categories set recipe = 3, category = 2;
insert into recipe_categories set recipe = 4, category = 2;
';
    static public function selectProvider(  ) {
        return array(
            array( '
select 
    recipes.title
    , authors.name
from 
    recipes
    left join authors on authors.id = recipes.author
where
    authors.name like "james"
                ',
                array (
                  0 =>
                  array (
                    'title' => 'mojito',
                    'name' => 'james',
                  ),
                  1 =>
                  array (
                    'title' => 'pizza',
                    'name' => 'james',
                  ),
                )
            ),
            array( '
select 
    recipes.title
    , recipe_categories.recipe
    , recipe_categories.category
from 
    recipe_categories
    left join recipes on recipe_categories.recipe = recipes.id
                ',
                array (
                  0 => 
                  array (
                    'title' => 'mojito',
                    'recipe' => '1',
                    'category' => '1',
                  ),
                  1 => 
                  array (
                    'title' => 'tipunch',
                    'recipe' => '2',
                    'category' => '1',
                  ),
                  2 => 
                  array (
                    'title' => 'pizza',
                    'recipe' => '3',
                    'category' => '2',
                  ),
                  3 => 
                  array (
                    'title' => 'quiche',
                    'recipe' => '4',
                    'category' => '2',
                  ),
                )
            ),
            array( '
select 
    recipes.title as recipe_title
    , categories.title as category_title
    , recipe_categories.recipe
    , recipe_categories.category
from 
    recipe_categories
    left join recipes on recipe_categories.recipe = recipes.id
    left join categories on recipe_categories.category = categories.id
                ',
                array (
                  0 => 
                  array (
                    'recipe_title' => 'mojito',
                    'category_title' => 'drinks',
                    'recipe' => '1',
                    'category' => '1',
                  ),
                  1 => 
                  array (
                    'recipe_title' => 'tipunch',
                    'category_title' => 'drinks',
                    'recipe' => '2',
                    'category' => '1',
                  ),
                  2 => 
                  array (
                    'recipe_title' => 'pizza',
                    'category_title' => 'middle',
                    'recipe' => '3',
                    'category' => '2',
                  ),
                  3 => 
                  array (
                    'recipe_title' => 'quiche',
                    'category_title' => 'middle',
                    'recipe' => '4',
                    'category' => '2',
                  ),
                )
            ),
            array( '
select 
    recipes.title as recipe_title
    , categories.title as category_title
    , authors.name as author_name
    , recipe_categories.recipe
    , recipe_categories.category
from 
    recipe_categories
    left join recipes on recipe_categories.recipe = recipes.id
    left join categories on recipe_categories.category = categories.id
    left join authors on recipes.author = recipes.id
where
    categories.title like "dri%"
group by recipe_categories.id
                ',
                array (
                  0 => 
                  array (
                    'recipe_title' => 'mojito',
                    'category_title' => 'drinks',
                    'author_name' => 'james',
                    'recipe' => '1',
                    'category' => '1',
                  ),
                  1 => 
                  array (
                    'recipe_title' => 'tipunch',
                    'category_title' => 'drinks',
                    'author_name' => 'james',
                    'recipe' => '2',
                    'category' => '1',
                  ),
                )
            )
        );
    }

    /**
     * @dataProvider selectProvider
     */
    public function testSelect( $sql, $expected = null ) {
        parent::testSelect( $sql, $expected );
    }
}

?>
