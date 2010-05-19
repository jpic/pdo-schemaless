PDO decorator which makes MySQL work normally, without the need if predefined strutures.

## Installing madPDO

Runtime code is maintained against PHP 5.2.6 and PHP 5.2.12.

Get the repo with:

    git clone http://github.com/jpic/pdo-schemaless.git

Or:

    http://github.com/jpic/pdo-schemaless/zipball/master

Then include:

    pdo-schemaless/pdo.php

Then instanciate madPDO, ie:

    $pdo = new madPDO( 'mysql:dbname=testdb;host=localhost', 'root' );

Now MySQL forgot it needed schemas.

If you want an SQL shell to spawn when a query fails, use:
    
    $pdo = new madPDOFramework( 'mysql:dbname=testdb;host=localhost', 'root' );

## Testing madPDO

2 databases are needed, one for vanilla PDO and one for madPDO.

Open test.php and setup the database access settings for $madPdo and $vanillaPdo.

Run "test.php" in the "tests/" subfolder.

### How tests work

Each folder inside the "tests/fixtures" directory is a test. Both databases are
cleared before each test.

"insert.sql" is run with both vanilla pdo and mad pdo, as many times as
$insertIterations is set to.

"schema.sql" is generated from the structure of mad, with create statements for
a vanilla database. If it exists, it is generated again and it is asserted that
there is no difference with the old one.

"data.sql" is generated from the data in the mad database. If it exists,
regressions are checked like for schema.sql.

"select.sql" is run in both vanilla and mad databases, as many times as
$testIterations is set. It is asserted that the result of each select statement
is the same in vanilla database and in mad database. Additionnaly, performances
are calculated.

### Testing a query

Create a folder in "test/fixtures".

In "insert.sql", add as many insert statements as you need.

In "select.sql", add as many select statements to test.

## Credits

[Vertical attribute tables](http://www.igvita.com/2010/03/01/schema-free-mysql-vs-nosql/)
[Originnal SQL tokenizer](http://www.tehuber.com/article.php?story=20081016164856267)
