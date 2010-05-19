select 
    recipes.title
    , authors.name
from 
    recipes
    left join authors on authors.id = recipes.author
where
    authors.name like "james"
;
select 
    recipes.title
    , recipe_categories.recipe
    , recipe_categories.category
from 
    recipe_categories
    left join recipes on recipe_categories.recipe = recipes.id
;
select 
    recipes.title as recipe_title
    , categories.title as category_title
    , recipe_categories.recipe
    , recipe_categories.category
from 
    recipe_categories
    left join recipes on recipe_categories.recipe = recipes.id
    left join categories on recipe_categories.category = categories.id
;
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
    left join authors on recipes.author = authors.id
where
    categories.title like "dri%"
group by recipe_categories.id
;
