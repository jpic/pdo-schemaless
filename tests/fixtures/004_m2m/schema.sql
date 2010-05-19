CREATE TABLE `authors` ( id INT(12) PRIMARY KEY AUTO_INCREMENT, email TEXT, name TEXT ) Engine=InnoDb;
CREATE TABLE `recipes` ( id INT(12) PRIMARY KEY AUTO_INCREMENT, author TEXT, title TEXT ) Engine=InnoDb;
CREATE TABLE `categories` ( id INT(12) PRIMARY KEY AUTO_INCREMENT, title TEXT ) Engine=InnoDb;
CREATE TABLE `recipe_categories` ( id INT(12) PRIMARY KEY AUTO_INCREMENT, recipe TEXT, category TEXT ) Engine=InnoDb;