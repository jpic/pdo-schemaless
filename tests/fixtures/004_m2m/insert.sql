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
