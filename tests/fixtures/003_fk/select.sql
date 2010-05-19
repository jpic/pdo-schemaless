select 
    authors.*
    , recipes.* 
from
    recipes
    left join authors on recipes.author = authors.id
;

select 
    authors.*
    , recipes.* 
from
    recipes
    left join authors on recipes.author = authors.id
where
    authors.name = 'james'
;

select 
    authors.*
    , recipes.* 
from
    recipes
    left join authors on recipes.author = authors.id
where
    authors.name like 'chri%'
;
